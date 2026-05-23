<?php

namespace Modules\Production\Services;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Modules\Production\Entities\ProductionBatch;
use Modules\Production\Entities\ProductionBatchConsumption;
use Modules\Production\Entities\ProductionBatchOutput;
use Modules\Production\Entities\ProductionBom;
use Modules\Production\Entities\ProductionOrder;
use Modules\Production\Entities\ProductionOrderBomSnapshotItem;
use Modules\Purchase\Services\ProductionFgInventoryLedgerSync;
use Modules\Warehouse\Entities\WarehouseProductBatch;
use Modules\Warehouse\Services\StockMovementService;
use Modules\Warehouse\Services\WarehouseUnitConversionService;

/**
 * Orchestrates RM consumption and FG receipt for Production MVP (Phase 1).
 * Stock truth remains in {@see StockMovementService}; this class only builds payloads and updates Production rows.
 */
class ProductionPostingService
{
    public function __construct(
        protected StockMovementService $stockMovementService,
        protected ProductionFgQuantityPolicyService $fgQuantityPolicy,
        protected WarehouseUnitConversionService $unitConversionService,
        protected ProductionFgInventoryLedgerSync $fgInventoryLedgerSync,
    ) {}

    public function releaseOrder(ProductionOrder $order): void
    {
        if ($order->status !== ProductionOrder::STATUS_DRAFT) {
            throw new InvalidArgumentException(__('production::app.onlyDraftReleasable'));
        }

        DB::transaction(function () use ($order): void {
            $this->syncBomSnapshotForReleasedOrder($order);
            $order->status = ProductionOrder::STATUS_RELEASED;
            $order->released_at = now();
            $order->save();
        });
    }

    /**
     * Freezes BOM component quantities against the {@see ProductionOrder::planned_quantity} at release when a BOM exists.
     */
    protected function syncBomSnapshotForReleasedOrder(ProductionOrder $order): void
    {
        $order->bomSnapshotItems()->delete();

        if ($order->production_bom_id === null) {
            $order->bom_snapshot_at = null;
            $order->bom_snapshot_planned_quantity = null;

            return;
        }

        $bom = ProductionBom::with('items')->find((int) $order->production_bom_id);
        if ($bom === null || $bom->items->isEmpty()) {
            $order->bom_snapshot_at = null;
            $order->bom_snapshot_planned_quantity = null;

            return;
        }

        $plannedQty = (float) $order->planned_quantity;
        $shadowEnabled = (bool) config('production.phase2.yield_uom_shadow_enabled', false);

        foreach ($bom->items as $index => $item) {
            $quantityPerFgUnit = (float) $item->quantity;
            $unitId = $item->unit_id !== null ? (int) $item->unit_id : null;

            $yieldFactor = null;
            $quantityPerFgUnitBaseShadow = null;

            if ($shadowEnabled) {
                $yieldFactor = $this->normalizeYieldFactor($item->yield_factor);
                $baseQuantityRaw = $this->unitConversionService->convertToBase(
                    (int) $order->company_id,
                    (int) $item->component_product_id,
                    $quantityPerFgUnit,
                    $unitId,
                );
                $quantityPerFgUnitBaseShadow = $baseQuantityRaw / $yieldFactor;
            }

            ProductionOrderBomSnapshotItem::query()->create([
                'company_id' => $order->company_id,
                'production_order_id' => $order->id,
                'component_product_id' => (int) $item->component_product_id,
                'quantity_per_fg_unit' => $quantityPerFgUnit,
                'waste_percent' => max(0.0, (float) ($item->waste_percent ?? 0)),
                'unit_id' => $unitId,
                'yield_factor' => $yieldFactor,
                'quantity_per_fg_unit_base_shadow' => $quantityPerFgUnitBaseShadow,
                'sort_order' => (int) ($item->sort_order ?? $index),
            ]);
        }

        $order->bom_snapshot_at = now();
        $order->bom_snapshot_planned_quantity = $plannedQty;
    }

    protected function normalizeYieldFactor(mixed $value): float
    {
        $yieldFactor = (float) ($value ?? 1.0);

        if ($yieldFactor <= 0) {
            return 1.0;
        }

        return $yieldFactor;
    }

    /**
     * Cancels a draft order (no stock impact) or a released order before any consumption/FG posting.
     */
    public function cancelOrder(ProductionOrder $order): void
    {
        if ($order->status === ProductionOrder::STATUS_CANCELLED) {
            return;
        }

        if ($order->status === ProductionOrder::STATUS_COMPLETED) {
            throw new InvalidArgumentException(__('production::app.completedCannotCancel'));
        }

        if ($order->status === ProductionOrder::STATUS_IN_PROGRESS) {
            throw new InvalidArgumentException(__('production::app.cannotCancelInProgress'));
        }

        if ($order->status === ProductionOrder::STATUS_RELEASED) {
            $batchIds = $order->batches()->pluck('id');
            $hasPostedConsumptions = $order->batches()->whereNotNull('posted_consumptions_at')->exists();
            if ($hasPostedConsumptions) {
                throw new InvalidArgumentException(__('production::app.cannotCancelRmPosted'));
            }

            if ($batchIds->isNotEmpty()) {
                $hasPostedFg = ProductionBatchOutput::query()
                    ->whereIn('production_batch_id', $batchIds)
                    ->whereNotNull('posted_at')
                    ->exists();
                if ($hasPostedFg) {
                    throw new InvalidArgumentException(__('production::app.cannotCancelFgPosted'));
                }
            }
        }

        $order->status = ProductionOrder::STATUS_CANCELLED;
        $order->save();
    }

    /**
     * Posts RM outbound lines for a batch. Idempotent per consumption line via {@see StockMovementService} idempotency_key.
     *
     * @throws InvalidArgumentException
     */
    public function postConsumptionsForBatch(ProductionBatch $batch): void
    {
        if ($batch->posted_consumptions_at !== null) {
            return;
        }

        $order = $batch->order;
        if (! in_array($order->status, [ProductionOrder::STATUS_RELEASED, ProductionOrder::STATUS_IN_PROGRESS], true)) {
            throw new InvalidArgumentException(__('production::app.orderMustBeReleasedForConsumptions'));
        }

        $batch->loadMissing(['consumptions.warehouseProductBatch']);

        if ($batch->consumptions->isEmpty()) {
            throw new InvalidArgumentException(__('production::app.postRawMaterialUsageRequiresLines'));
        }

        DB::transaction(function () use ($batch, $order): void {
            foreach ($batch->consumptions as $consumption) {
                $this->postSingleConsumption($batch, $consumption, (int) $order->company_id, (int) $order->rm_warehouse_id);
            }

            $batch->posted_consumptions_at = now();
            $batch->save();

            if ($order->status === ProductionOrder::STATUS_RELEASED) {
                $order->status = ProductionOrder::STATUS_IN_PROGRESS;
                $order->save();
            }
        });
    }

    /**
     * Posts FG inbound for a prepared {@see ProductionBatchOutput} row (posted_at still null).
     *
     * When a batch has multiple output lines, {@see ProductionBatch::$posted_receipt_at} and
     * {@see ProductionBatch::$completed_at} are set only after every output row is posted.
     *
     * @throws InvalidArgumentException
     */
    public function postFinishedGoodsReceipt(ProductionBatchOutput $output): void
    {
        if ($output->posted_at !== null) {
            return;
        }

        $batch = $output->batch()->with('order')->first();
        if ($batch === null || $batch->posted_consumptions_at === null) {
            throw new InvalidArgumentException(__('production::app.postRmBeforeFgReceipt'));
        }

        $order = $batch->order;
        if ($order === null) {
            throw new InvalidArgumentException(__('production::app.missingOrderOnBatch'));
        }

        $companyId = (int) $order->company_id;
        if ($companyId <= 0) {
            throw new InvalidArgumentException(__('production::app.missingCompanyOnOrder'));
        }

        $registeredFgTotal = $this->fgQuantityPolicy->registeredFgTotalForOrder($order);

        $this->fgQuantityPolicy->assertProjectedTotalsAllowedForOrder(
            $order,
            $registeredFgTotal,
            $output->variance_reason,
        );

        if (
            $this->fgQuantityPolicy->outputRequiresVarianceApproval($order, $output)
            && ($output->approved_by === null || $output->approved_at === null)
        ) {
            throw new InvalidArgumentException(__('production::app.fgVarianceApprovalRequired'));
        }

        $payload = [
            'company_id' => $companyId,
            'warehouse_id' => (int) $output->warehouse_id,
            'product_id' => (int) $output->output_product_id,
            'quantity' => (float) $output->quantity,
            'batch_number' => $output->batch_number,
            'expiry_date' => $output->expiration_date?->format('Y-m-d'),
            'manufacturing_date' => $output->manufacturing_date?->format('Y-m-d'),
            'reference_type' => ProductionBatch::class,
            'reference_id' => (int) $batch->id,
            'idempotency_key' => 'production-fg-receipt:' . $output->id,
        ];

        DB::transaction(function () use ($payload, $output, $batch, $order): void {
            $this->stockMovementService->recordInbound($payload);

            $output->posted_at = now();
            $output->save();

            $this->fgInventoryLedgerSync->ensureLedgerLineAfterFgReceipt($output->fresh());

            $batch->refresh();
            $hasUnpostedOutputs = $batch->outputs()->whereNull('posted_at')->exists();

            if (! $hasUnpostedOutputs) {
                $batch->posted_receipt_at = now();
                $batch->completed_at = now();
                $batch->save();
            }

            $hasPendingBatches = $order->batches()
                ->where(function ($query): void {
                    $query->whereNull('posted_receipt_at')
                        ->orWhereNull('completed_at');
                })
                ->exists();

            if ($hasPendingBatches) {
                if ($order->status !== ProductionOrder::STATUS_IN_PROGRESS) {
                    $order->status = ProductionOrder::STATUS_IN_PROGRESS;
                    $order->save();
                }

                return;
            }

            $order->status = ProductionOrder::STATUS_COMPLETED;
            $order->completed_at = now();
            $order->save();
        });
    }

    protected function postSingleConsumption(ProductionBatch $batch, ProductionBatchConsumption $consumption, int $companyId, int $rmWarehouseId): void
    {
        $qtyEntered = (float) ($consumption->actual_quantity ?? $consumption->planned_quantity);
        if ($qtyEntered <= 0) {
            throw new InvalidArgumentException(__('production::app.consumptionQtyMustBePositive'));
        }

        $productId = (int) $consumption->component_product_id;
        $unitId = $consumption->unit_id !== null ? (int) $consumption->unit_id : null;
        $qtyBase = $this->unitConversionService->convertToBase(
            $companyId,
            $productId,
            $qtyEntered,
            $unitId,
        );

        $allocations = $this->resolveWarehouseBatchAllocationsForConsumption(
            $consumption,
            $companyId,
            $rmWarehouseId,
            $qtyBase,
            $consumption->warehouse_product_batch_id !== null ? (int) $consumption->warehouse_product_batch_id : null,
        );

        if ($allocations === []) {
            throw new InvalidArgumentException(__('production::app.consumptionRequiresWarehouseBatch'));
        }

        if ((int) $consumption->warehouse_product_batch_id !== (int) $allocations[0]['batch_id']) {
            $consumption->warehouse_product_batch_id = (int) $allocations[0]['batch_id'];
            $consumption->save();
        }

        foreach ($allocations as $index => $allocation) {
            $payload = [
                'company_id' => $companyId,
                'warehouse_id' => $rmWarehouseId,
                'product_id' => (int) $consumption->component_product_id,
                'quantity' => (float) $allocation['quantity'],
                'batch_id' => (int) $allocation['batch_id'],
                'reference_type' => ProductionBatch::class,
                'reference_id' => (int) $batch->id,
                'idempotency_key' => 'production-consume:' . $consumption->id . ':' . $index,
            ];

            $this->stockMovementService->recordOutbound($payload);
        }
    }

    /**
     * @return array<int, array{batch_id:int, quantity:float}>
     */
    protected function resolveWarehouseBatchAllocationsForConsumption(
        ProductionBatchConsumption $consumption,
        int $companyId,
        int $rmWarehouseId,
        float $requiredQty,
        ?int $preferredBatchId = null
    ): array {
        $allocations = [];
        $remaining = $requiredQty;

        if ($preferredBatchId !== null) {
            $preferred = WarehouseProductBatch::query()
                ->whereKey($preferredBatchId)
                ->where('company_id', $companyId)
                ->where('warehouse_id', $rmWarehouseId)
                ->where('product_id', (int) $consumption->component_product_id)
                ->first(['id', 'quantity', 'reserved_quantity']);

            if ($preferred !== null && $remaining > 0) {
                $preferredAvailable = max(0.0, (float) $preferred->quantity - (float) $preferred->reserved_quantity);
                if ($preferredAvailable > 0) {
                    $take = min($remaining, $preferredAvailable);
                    $allocations[] = [
                        'batch_id' => (int) $preferred->id,
                        'quantity' => (float) $take,
                    ];
                    $remaining -= $take;
                }
            }
        }

        if ($remaining <= 0.0000001) {
            return $allocations;
        }

        $candidates = WarehouseProductBatch::query()
            ->where('company_id', $companyId)
            ->where('warehouse_id', $rmWarehouseId)
            ->where('product_id', (int) $consumption->component_product_id)
            ->where('quantity', '>', 0)
            ->when($preferredBatchId !== null, fn($query) => $query->where('id', '!=', (int) $preferredBatchId))
            ->orderByDesc('quantity')
            ->orderBy('id')
            ->get(['id', 'quantity', 'reserved_quantity']);

        foreach ($candidates as $candidate) {
            if ($remaining <= 0.0000001) {
                break;
            }

            $available = max(0.0, (float) $candidate->quantity - (float) $candidate->reserved_quantity);
            if ($available <= 0) {
                continue;
            }

            $take = min($remaining, $available);
            $allocations[] = [
                'batch_id' => (int) $candidate->id,
                'quantity' => (float) $take,
            ];
            $remaining -= $take;
        }

        if ($remaining > 0.0000001) {
            return [];
        }

        return $allocations;
    }
}
