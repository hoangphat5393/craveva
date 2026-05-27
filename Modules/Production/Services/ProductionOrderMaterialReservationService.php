<?php

declare(strict_types=1);

namespace Modules\Production\Services;

use InvalidArgumentException;
use Modules\Production\Entities\ProductionOrder;
use Modules\Warehouse\Entities\StockReservation;
use Modules\Warehouse\Entities\WarehouseProductBatch;
use Modules\Warehouse\Services\StockReservationService;

/**
 * Soft-reserves raw materials for a production order at release via {@see StockReservationService}.
 */
class ProductionOrderMaterialReservationService
{
    private const FLOAT_TOLERANCE = 0.0000001;

    public function __construct(
        private readonly StockReservationService $stockReservationService,
        private readonly ProductionOrderMaterialRequirementsSummary $materialRequirementsSummary,
    ) {}

    public function assertCanReserve(ProductionOrder $order): void
    {
        $warehouseId = (int) ($order->rm_warehouse_id ?? 0);
        if ($warehouseId <= 0) {
            return;
        }

        $rows = $this->materialRequirementsSummary->demandRowsForOrder($order);
        if ($rows === []) {
            return;
        }

        $productIds = array_map(
            static fn(array $row): int => (int) $row['component_product_id'],
            $rows,
        );

        $available = $this->materialRequirementsSummary->availableQuantityMapForWarehouse(
            $warehouseId,
            $productIds,
            (int) $order->company_id,
        );

        foreach ($rows as $row) {
            $productId = (int) $row['component_product_id'];
            $required = (float) $row['total_required'];
            $avail = (float) ($available[$productId] ?? 0.0);

            if ($required > $avail + self::FLOAT_TOLERANCE) {
                throw new InvalidArgumentException(__('production::app.insufficientRmToReserve', [
                    'material' => (string) $row['component_name'],
                    'required' => $this->formatQty($required),
                    'available' => $this->formatQty($avail),
                ]));
            }
        }
    }

    public function syncForOrder(ProductionOrder $order): void
    {
        $this->releaseForOrder($order);

        $warehouseId = (int) ($order->rm_warehouse_id ?? 0);
        if ($warehouseId <= 0) {
            return;
        }

        $rows = $this->materialRequirementsSummary->demandRowsForOrder($order);
        if ($rows === []) {
            return;
        }

        $companyId = (int) $order->company_id;

        foreach ($rows as $row) {
            $productId = (int) $row['component_product_id'];
            $required = (float) $row['total_required'];

            if ($required <= self::FLOAT_TOLERANCE) {
                continue;
            }

            $allocations = $this->allocateBatchQuantities($companyId, $warehouseId, $productId, $required);

            if ($allocations === []) {
                throw new InvalidArgumentException(__('production::app.insufficientRmToReserve', [
                    'material' => (string) $row['component_name'],
                    'required' => $this->formatQty($required),
                    'available' => '0',
                ]));
            }

            foreach ($allocations as $allocation) {
                $batch = WarehouseProductBatch::query()->find((int) $allocation['batch_id']);
                if ($batch === null) {
                    continue;
                }

                $expiry = $batch->expiration_date;
                $expiryStr = $expiry instanceof \DateTimeInterface ? $expiry->format('Y-m-d') : $expiry;

                $this->stockReservationService->reserve([
                    'company_id' => $companyId,
                    'warehouse_id' => $warehouseId,
                    'product_id' => $productId,
                    'quantity' => (float) $allocation['quantity'],
                    'batch_id' => (int) $batch->id,
                    'batch_number' => $batch->batch_number,
                    'expiry_date' => $expiryStr,
                    'reference_type' => ProductionOrder::class,
                    'reference_id' => (int) $order->id,
                ]);
            }
        }
    }

    public function releaseForOrder(ProductionOrder $order): void
    {
        $this->stockReservationService->releaseByReference(ProductionOrder::class, (int) $order->id);
    }

    public function consumeForOrder(ProductionOrder $order): void
    {
        $this->stockReservationService->consumeByReference(ProductionOrder::class, (int) $order->id);
    }

    public function consumeForProduct(ProductionOrder $order, int $productId, float $quantityBase): void
    {
        if ($quantityBase <= self::FLOAT_TOLERANCE) {
            return;
        }

        $remaining = $quantityBase;

        $reservations = StockReservation::query()
            ->where('reference_type', ProductionOrder::class)
            ->where('reference_id', (int) $order->id)
            ->where('product_id', $productId)
            ->where('status', 'active')
            ->orderBy('id')
            ->get();

        foreach ($reservations as $reservation) {
            if ($remaining <= self::FLOAT_TOLERANCE) {
                break;
            }

            $rowQty = (float) $reservation->reserved_quantity;
            if ($rowQty <= self::FLOAT_TOLERANCE) {
                continue;
            }

            if ($rowQty <= $remaining + self::FLOAT_TOLERANCE) {
                $this->stockReservationService->consume($reservation);
                $remaining -= $rowQty;

                continue;
            }

            $this->stockReservationService->release($reservation);
            $remainder = $rowQty - $remaining;
            $remaining = 0.0;

            if ($remainder > self::FLOAT_TOLERANCE) {
                $expiry = $reservation->expiration_date;
                $expiryStr = $expiry instanceof \DateTimeInterface ? $expiry->format('Y-m-d') : $expiry;

                $this->stockReservationService->reserve([
                    'company_id' => (int) ($reservation->company_id ?? $order->company_id),
                    'warehouse_id' => (int) $reservation->warehouse_id,
                    'product_id' => $productId,
                    'quantity' => $remainder,
                    'batch_number' => $reservation->batch_number,
                    'expiry_date' => $expiryStr,
                    'reference_type' => ProductionOrder::class,
                    'reference_id' => (int) $order->id,
                ]);
            }

            break;
        }
    }

    public function orderHasPendingConsumptionBatches(ProductionOrder $order): bool
    {
        return $order->batches()->whereNull('posted_consumptions_at')->exists();
    }

    /**
     * @return list<array{batch_id: int, quantity: float}>
     */
    protected function allocateBatchQuantities(
        int $companyId,
        int $rmWarehouseId,
        int $productId,
        float $requiredQty,
        ?int $preferredBatchId = null,
    ): array {
        $allocations = [];
        $remaining = $requiredQty;

        if ($preferredBatchId !== null) {
            $preferred = WarehouseProductBatch::query()
                ->whereKey($preferredBatchId)
                ->where('company_id', $companyId)
                ->where('warehouse_id', $rmWarehouseId)
                ->where('product_id', $productId)
                ->first(['id', 'quantity', 'reserved_quantity']);

            if ($preferred !== null && $remaining > self::FLOAT_TOLERANCE) {
                $preferredAvailable = max(0.0, (float) $preferred->quantity - (float) $preferred->reserved_quantity);
                if ($preferredAvailable > self::FLOAT_TOLERANCE) {
                    $take = min($remaining, $preferredAvailable);
                    $allocations[] = [
                        'batch_id' => (int) $preferred->id,
                        'quantity' => (float) $take,
                    ];
                    $remaining -= $take;
                }
            }
        }

        if ($remaining <= self::FLOAT_TOLERANCE) {
            return $allocations;
        }

        $candidates = WarehouseProductBatch::query()
            ->where('company_id', $companyId)
            ->where('warehouse_id', $rmWarehouseId)
            ->where('product_id', $productId)
            ->where('quantity', '>', 0)
            ->when($preferredBatchId !== null, fn($query) => $query->where('id', '!=', (int) $preferredBatchId))
            ->orderByDesc('quantity')
            ->orderBy('id')
            ->get(['id', 'quantity', 'reserved_quantity']);

        foreach ($candidates as $candidate) {
            if ($remaining <= self::FLOAT_TOLERANCE) {
                break;
            }

            $available = max(0.0, (float) $candidate->quantity - (float) $candidate->reserved_quantity);
            if ($available <= self::FLOAT_TOLERANCE) {
                continue;
            }

            $take = min($remaining, $available);
            $allocations[] = [
                'batch_id' => (int) $candidate->id,
                'quantity' => (float) $take,
            ];
            $remaining -= $take;
        }

        if ($remaining > self::FLOAT_TOLERANCE) {
            return [];
        }

        return $allocations;
    }

    protected function formatQty(float $value): string
    {
        return rtrim(rtrim(number_format($value, 4, '.', ''), '0'), '.') ?: '0';
    }
}
