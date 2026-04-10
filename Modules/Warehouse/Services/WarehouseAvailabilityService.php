<?php

namespace Modules\Warehouse\Services;

use Modules\Warehouse\Entities\Warehouse;
use Modules\Warehouse\Entities\WarehouseProductBatch;
use Modules\Warehouse\Exceptions\WarehouseBusinessException;

class WarehouseAvailabilityService
{
    public function __construct(
        protected WarehouseFlowPolicyService $flowPolicy,
        protected WarehouseUnitConversionService $unitConversion
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

    /**
     * Validate AI / webhook order lines against sellable stock (same rules as availability API).
     * Lines without product_id are skipped. Quantities are converted to base unit when unit_id is provided.
     *
     * @param  array<int, array<string, mixed>>  $items
     * @param  array<int>  $warehouseIds  Empty = all active warehouses for the company.
     *
     * @throws WarehouseBusinessException
     */
    public function validateAiOrderWebhookItems(int $companyId, array $items, array $warehouseIds = []): void
    {
        $byProduct = [];

        foreach ($items as $item) {
            $productId = isset($item['product_id']) ? (int) $item['product_id'] : 0;
            if ($productId <= 0) {
                continue;
            }

            $qty = (float) ($item['quantity'] ?? 0);
            $unitId = isset($item['unit_id']) ? (int) $item['unit_id'] : 0;

            $baseQty = $this->unitConversion->convertToBase(
                $companyId,
                $productId,
                $qty,
                $unitId > 0 ? $unitId : null
            );

            $byProduct[$productId] = ($byProduct[$productId] ?? 0) + $baseQty;
        }

        if ($byProduct === []) {
            return;
        }

        foreach ($byProduct as $productId => $neededBase) {
            $snapshot = $this->availabilityByProduct($companyId, $productId, $warehouseIds);
            $sellable = (float) $snapshot['total_sellable'];

            if ($sellable + 1e-9 < $neededBase) {
                throw new WarehouseBusinessException(
                    __('warehouse::app.err_ai_order_insufficient_sellable', [
                        'product_id' => $productId,
                        'needed' => $neededBase,
                        'sellable' => $sellable,
                    ]),
                    [
                        'company_id' => $companyId,
                        'product_id' => $productId,
                        'needed_base' => $neededBase,
                        'sellable' => $sellable,
                        'availability' => $snapshot,
                    ]
                );
            }
        }
    }
}
