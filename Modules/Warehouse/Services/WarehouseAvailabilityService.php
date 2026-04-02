<?php

namespace Modules\Warehouse\Services;

use Modules\Warehouse\Entities\Warehouse;
use Modules\Warehouse\Entities\WarehouseProductBatch;

class WarehouseAvailabilityService
{
    public function __construct(
        protected WarehouseFlowPolicyService $flowPolicy
    ) {}

    /**
     * @param  array<int>  $warehouseIds
     * @return array{
     *   product_id: int,
     *   company_id: int,
     *   total_on_hand: float,
     *   total_reserved: float,
     *   total_available: float,
     *   total_sellable: float,
     *   sellable_yes_no: string,
     *   warehouses: array<int, array<string, mixed>>
     * }
     */
    public function availabilityByProduct(int $companyId, int $productId, array $warehouseIds = []): array
    {
        $warehouseQuery = Warehouse::query()
            ->where('company_id', $companyId)
            ->where('status', 'active');

        if ($warehouseIds !== []) {
            $warehouseQuery->whereIn('id', $warehouseIds);
        }

        $warehouses = $warehouseQuery->get(['id', 'name', 'warehouse_type']);
        if ($warehouses->isEmpty()) {
            return [
                'product_id' => $productId,
                'company_id' => $companyId,
                'total_on_hand' => 0.0,
                'total_reserved' => 0.0,
                'total_available' => 0.0,
                'total_sellable' => 0.0,
                'sellable_yes_no' => 'NO',
                'warehouses' => [],
            ];
        }

        $batchRows = WarehouseProductBatch::query()
            ->where('company_id', $companyId)
            ->where('product_id', $productId)
            ->whereIn('warehouse_id', $warehouses->pluck('id')->all())
            ->selectRaw('warehouse_id, SUM(quantity) as on_hand, SUM(reserved_quantity) as reserved')
            ->groupBy('warehouse_id')
            ->get()
            ->keyBy('warehouse_id');

        $resultRows = [];
        $totalOnHand = 0.0;
        $totalReserved = 0.0;
        $totalAvailable = 0.0;
        $totalSellable = 0.0;

        foreach ($warehouses as $warehouse) {
            $aggregated = $batchRows->get($warehouse->id);
            $onHand = (float) ($aggregated->on_hand ?? 0);
            $reserved = (float) ($aggregated->reserved ?? 0);
            $available = max(0.0, $onHand - $reserved);
            $sellable = $this->flowPolicy->isSellableWarehouseType($warehouse->warehouse_type) ? $available : 0.0;

            $totalOnHand += $onHand;
            $totalReserved += $reserved;
            $totalAvailable += $available;
            $totalSellable += $sellable;

            $resultRows[] = [
                'warehouse_id' => (int) $warehouse->id,
                'warehouse_name' => $warehouse->name,
                'warehouse_type' => (string) ($warehouse->warehouse_type ?? 'normal'),
                'on_hand' => $onHand,
                'reserved' => $reserved,
                'available' => $available,
                'sellable' => $sellable,
            ];
        }

        return [
            'product_id' => $productId,
            'company_id' => $companyId,
            'total_on_hand' => $totalOnHand,
            'total_reserved' => $totalReserved,
            'total_available' => $totalAvailable,
            'total_sellable' => $totalSellable,
            'sellable_yes_no' => $totalSellable > 0 ? 'YES' : 'NO',
            'warehouses' => $resultRows,
        ];
    }
}
