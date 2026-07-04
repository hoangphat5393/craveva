<?php

declare(strict_types=1);

namespace Modules\Production\Services;

use Illuminate\Support\Collection;
use Modules\Production\Entities\ProductionBom;
use Modules\Production\Entities\ProductionOrder;
use Modules\Warehouse\Entities\WarehouseProductBatch;
use Modules\Warehouse\Entities\WarehouseProductStock;
use Modules\Warehouse\Services\WarehouseUnitConversionService;

/**
 * Planned raw-material totals for a production order (BOM qty × planned FG qty).
 *
 * @phpstan-type RequirementRow array{
 *     component_product_id: int,
 *     component_name: string,
 *     quantity_per_fg_unit: float,
 *     waste_percent: float,
 *     total_required: float,
 *     unit_label: string|null,
 *     unit_label_base: string|null,
 *     reserved_in_rm_warehouse: float|null,
 *     available_in_rm_warehouse: float|null,
 *     shortfall: float|null,
 * }
 */
class ProductionOrderMaterialRequirementsSummary
{
    public function __construct(
        protected WarehouseUnitConversionService $unitConversionService
    ) {}

    public function shortfallStateForOrder(ProductionOrder $order): ?bool
    {
        if ($order->rm_warehouse_id === null || ! class_exists(WarehouseProductStock::class)) {
            return null;
        }

        $rows = $this->forOrder($order);

        if ($rows === []) {
            return null;
        }

        return $this->hasShortfall($rows);
    }

    /**
     * @return list<RequirementRow>
     */
    public function forOrder(ProductionOrder $order): array
    {
        $rows = $this->demandRowsForOrder($order);
        if ($rows === []) {
            return [];
        }

        $availableByProduct = $this->availableQuantityByProductInWarehouse(
            (int) $order->company_id,
            $order->rm_warehouse_id !== null ? (int) $order->rm_warehouse_id : null,
            array_values(array_unique(array_map(
                static fn (array $row): int => (int) $row['component_product_id'],
                $rows,
            ))),
        );
        $reservedByProduct = $this->reservedQuantityByProductInWarehouse(
            $order->rm_warehouse_id !== null ? (int) $order->rm_warehouse_id : null,
            array_values(array_unique(array_map(
                static fn (array $row): int => (int) $row['component_product_id'],
                $rows,
            ))),
        );

        foreach ($rows as $index => $row) {
            $productId = (int) $row['component_product_id'];
            $available = $availableByProduct[$productId] ?? null;
            $reserved = $reservedByProduct[$productId] ?? null;
            $shortfall = null;

            if ($available !== null && (float) $row['total_required'] > $available + 0.0000001) {
                $shortfall = round((float) $row['total_required'] - $available, 6);
            }

            $rows[$index]['reserved_in_rm_warehouse'] = $reserved;
            $rows[$index]['available_in_rm_warehouse'] = $available;
            $rows[$index]['shortfall'] = $shortfall;
        }

        return $rows;
    }

    /**
     * @return list<RequirementRow>
     */
    public function demandRowsForOrder(ProductionOrder $order): array
    {
        $plannedFg = (float) $order->planned_quantity;
        if ($plannedFg <= 0.0000001) {
            return [];
        }

        $components = $this->resolveComponents($order);
        if ($components->isEmpty()) {
            return [];
        }

        $companyId = (int) $order->company_id;
        $rows = [];

        foreach ($components as $component) {
            $productId = (int) $component['component_product_id'];
            $perFg = (float) $component['quantity_per_fg_unit'];
            $lineUnitId = $component['unit_id'] !== null ? (int) $component['unit_id'] : null;
            $perFgBase = $this->unitConversionService->convertToBase(
                $companyId,
                $productId,
                $perFg,
                $lineUnitId,
            );
            $wastePercent = max(0.0, (float) ($component['waste_percent'] ?? 0));

            $rows[] = [
                'component_product_id' => $productId,
                'component_name' => (string) $component['component_name'],
                'quantity_per_fg_unit' => $perFg,
                'waste_percent' => $wastePercent,
                'total_required' => round($perFgBase * $plannedFg * (1 + ($wastePercent / 100)), 6),
                'unit_label' => $component['unit_label'],
                'unit_label_base' => $component['unit_label_base'] ?? $component['unit_label'],
                'reserved_in_rm_warehouse' => null,
                'available_in_rm_warehouse' => null,
                'shortfall' => null,
            ];
        }

        return $rows;
    }

    public function hasShortfall(array $rows): bool
    {
        foreach ($rows as $row) {
            if (($row['shortfall'] ?? null) !== null && (float) $row['shortfall'] > 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  list<int>  $productIds
     * @return array<int, float>
     */
    public function availableQuantityMapForWarehouse(?int $warehouseId, array $productIds, ?int $companyId = null): array
    {
        return $this->availableQuantityByProductInWarehouse(
            $companyId !== null ? (int) $companyId : (int) company()->id,
            $warehouseId,
            $productIds,
        );
    }

    /**
     * @return Collection<int, array{component_product_id: int, component_name: string, quantity_per_fg_unit: float, waste_percent: float, unit_id: int|null, unit_label: string|null, unit_label_base: string|null}>
     */
    protected function resolveComponents(ProductionOrder $order): Collection
    {
        if ($order->bom_snapshot_at !== null) {
            $order->loadMissing(['bomSnapshotItems.componentProduct.unit', 'bomSnapshotItems.unit']);

            return $order->bomSnapshotItems->map(static function ($line): array {
                return [
                    'component_product_id' => (int) $line->component_product_id,
                    'component_name' => (string) ($line->componentProduct?->name ?? $line->component_product_id),
                    'quantity_per_fg_unit' => (float) $line->quantity_per_fg_unit,
                    'waste_percent' => max(0.0, (float) ($line->waste_percent ?? 0)),
                    'unit_id' => $line->unit_id !== null ? (int) $line->unit_id : null,
                    'unit_label' => $line->unit?->unit_type ?? $line->componentProduct?->unit?->unit_type,
                    'unit_label_base' => $line->componentProduct?->unit?->unit_type,
                ];
            });
        }

        if ($order->production_bom_id === null) {
            return collect();
        }

        $order->loadMissing(['bom.items.componentProduct.unit']);

        if ($order->bom === null || $order->bom->items->isEmpty()) {
            $bom = ProductionBom::query()
                ->with(['items.componentProduct.unit'])
                ->whereKey((int) $order->production_bom_id)
                ->first();

            if ($bom === null) {
                return collect();
            }

            return $bom->items->map(static function ($item): array {
                return [
                    'component_product_id' => (int) $item->component_product_id,
                    'component_name' => (string) ($item->componentProduct?->name ?? $item->component_product_id),
                    'quantity_per_fg_unit' => (float) $item->quantity,
                    'waste_percent' => max(0.0, (float) ($item->waste_percent ?? 0)),
                    'unit_id' => $item->unit_id !== null ? (int) $item->unit_id : null,
                    'unit_label' => $item->unit?->unit_type ?? $item->componentProduct?->unit?->unit_type,
                    'unit_label_base' => $item->componentProduct?->unit?->unit_type,
                ];
            });
        }

        return $order->bom->items->map(static function ($item): array {
            return [
                'component_product_id' => (int) $item->component_product_id,
                'component_name' => (string) ($item->componentProduct?->name ?? $item->component_product_id),
                'quantity_per_fg_unit' => (float) $item->quantity,
                'waste_percent' => max(0.0, (float) ($item->waste_percent ?? 0)),
                'unit_id' => $item->unit_id !== null ? (int) $item->unit_id : null,
                'unit_label' => $item->unit?->unit_type ?? $item->componentProduct?->unit?->unit_type,
                'unit_label_base' => $item->componentProduct?->unit?->unit_type,
            ];
        });
    }

    /**
     * @param  list<int>  $productIds
     * @return array<int, float>
     */
    protected function availableQuantityByProductInWarehouse(int $companyId, ?int $warehouseId, array $productIds): array
    {
        if ($warehouseId === null || $productIds === []) {
            return [];
        }

        if (! class_exists(WarehouseProductStock::class)) {
            return [];
        }

        $stocks = WarehouseProductStock::query()
            ->where('warehouse_id', $warehouseId)
            ->whereIn('product_id', $productIds)
            ->get(['warehouse_id', 'product_id', 'quantity']);

        if ($stocks->isEmpty()) {
            return array_fill_keys($productIds, 0.0);
        }

        $reservedByKey = WarehouseProductBatch::query()
            ->selectRaw('warehouse_id, product_id, SUM(reserved_quantity) as reserved')
            ->where('warehouse_id', $warehouseId)
            ->whereIn('product_id', $productIds)
            ->groupBy('warehouse_id', 'product_id')
            ->get()
            ->keyBy(static fn ($row): string => $row->warehouse_id.':'.$row->product_id);

        $available = [];

        foreach ($productIds as $productId) {
            $stock = $stocks->firstWhere('product_id', $productId);
            $onHand = $stock !== null ? (float) $stock->quantity : 0.0;
            $reserved = (float) ($reservedByKey->get($warehouseId.':'.$productId)->reserved ?? 0);
            $available[$productId] = max(0.0, $onHand - $reserved);
        }

        return $available;
    }

    /**
     * @param  list<int>  $productIds
     * @return array<int, float>
     */
    protected function reservedQuantityByProductInWarehouse(?int $warehouseId, array $productIds): array
    {
        if ($warehouseId === null || $productIds === []) {
            return [];
        }

        $reserved = WarehouseProductBatch::query()
            ->selectRaw('product_id, SUM(reserved_quantity) as reserved')
            ->where('warehouse_id', $warehouseId)
            ->whereIn('product_id', $productIds)
            ->groupBy('product_id')
            ->get()
            ->keyBy('product_id');

        $result = [];
        foreach ($productIds as $productId) {
            $result[$productId] = (float) ($reserved->get($productId)->reserved ?? 0);
        }

        return $result;
    }

    /**
     * Draft preview for production order create/edit (BOM master, not release snapshot).
     *
     * @return list<RequirementRow>
     */
    public function previewForBom(
        int $companyId,
        int $productionBomId,
        float $plannedQuantity,
        ?int $rmWarehouseId = null,
    ): array {
        if ($plannedQuantity <= 0.0000001) {
            return [];
        }

        $bom = ProductionBom::query()
            ->with(['items.componentProduct.unit', 'items.unit'])
            ->where(function ($query) use ($companyId): void {
                $query->whereNull('company_id')->orWhere('company_id', $companyId);
            })
            ->find($productionBomId);

        if ($bom === null || $bom->items->isEmpty()) {
            return [];
        }

        $order = new ProductionOrder([
            'company_id' => $companyId,
            'production_bom_id' => $productionBomId,
            'planned_quantity' => $plannedQuantity,
            'rm_warehouse_id' => $rmWarehouseId,
        ]);
        $order->setRelation('bom', $bom);

        return $this->forOrder($order);
    }
}
