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
use Modules\Warehouse\Services\StockMovementService;

/**
 * Orchestrates RM consumption and FG receipt for Production MVP (Phase 1).
 * Stock truth remains in {@see StockMovementService}; this class only builds payloads and updates Production rows.
 */
class ProductionPostingService
{
    public function __construct(
        protected StockMovementService $stockMovementService,
        protected ProductionFgQuantityPolicyService $fgQuantityPolicy,
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

        foreach ($bom->items as $index => $item) {
            ProductionOrderBomSnapshotItem::query()->create([
                'company_id' => $order->company_id,
                'production_order_id' => $order->id,
                'component_product_id' => (int) $item->component_product_id,
                'quantity_per_fg_unit' => (float) $item->quantity,
                'sort_order' => (int) ($item->sort_order ?? $index),
            ]);
        }

        $order->bom_snapshot_at = now();
        $order->bom_snapshot_planned_quantity = $plannedQty;
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
            throw new InvalidArgumentException(__('production::app.postConsumptionRequiresLines'));
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

            $batch->posted_receipt_at = now();
            $batch->completed_at = now();
            $batch->save();

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
        if ($consumption->warehouse_product_batch_id === null) {
            throw new InvalidArgumentException(__('production::app.consumptionRequiresWarehouseBatch'));
        }

        $qty = $consumption->actual_quantity ?? $consumption->planned_quantity;
        if ($qty <= 0) {
            throw new InvalidArgumentException(__('production::app.consumptionQtyMustBePositive'));
        }

        $payload = [
            'company_id' => $companyId,
            'warehouse_id' => $rmWarehouseId,
            'product_id' => (int) $consumption->component_product_id,
            'quantity' => (float) $qty,
            'batch_id' => (int) $consumption->warehouse_product_batch_id,
            'reference_type' => ProductionBatch::class,
            'reference_id' => (int) $batch->id,
            'idempotency_key' => 'production-consume:' . $consumption->id,
        ];

        $this->stockMovementService->recordOutbound($payload);
    }
}
