<?php

declare(strict_types=1);

namespace Modules\Production\Services;

use Illuminate\Support\Collection;
use Modules\Production\Entities\ProductionBom;
use Modules\Production\Entities\ProductionOrder;
use Modules\Warehouse\Entities\WarehouseProductBatch;
use Modules\Warehouse\Entities\WarehouseProductStock;

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
 *     available_in_rm_warehouse: float|null,
 *     shortfall: float|null,
 * }
 */
class ProductionOrderMaterialRequirementsSummary
{
    /**
     * @return list<RequirementRow>
     */
    public function forOrder(ProductionOrder $order): array
    {
        $plannedFg = (float) $order->planned_quantity;
        if ($plannedFg <= 0.0000001) {
            return [];
        }

        $components = $this->resolveComponents($order);
        if ($components->isEmpty()) {
            return [];
        }

        $availableByProduct = $this->availableQuantityByProductInWarehouse(
            (int) $order->company_id,
            $order->rm_warehouse_id !== null ? (int) $order->rm_warehouse_id : null,
            $components->pluck('component_product_id')->map(static fn ($id): int => (int) $id)->all(),
        );

        $rows = [];

        foreach ($components as $component) {
            $perFg = (float) $component['quantity_per_fg_unit'];
            $wastePercent = max(0.0, (float) ($component['waste_percent'] ?? 0));
            $totalRequired = round($perFg * $plannedFg * (1 + ($wastePercent / 100)), 6);
            $productId = (int) $component['component_product_id'];
            $available = $availableByProduct[$productId] ?? null;
            $shortfall = null;

            if ($available !== null && $totalRequired > $available + 0.0000001) {
                $shortfall = round($totalRequired - $available, 6);
            }

            $rows[] = [
                'component_product_id' => $productId,
                'component_name' => (string) $component['component_name'],
                'quantity_per_fg_unit' => $perFg,
                'waste_percent' => $wastePercent,
                'total_required' => $totalRequired,
                'unit_label' => $component['unit_label'],
                'available_in_rm_warehouse' => $available,
                'shortfall' => $shortfall,
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
     * @return Collection<int, array{component_product_id: int, component_name: string, quantity_per_fg_unit: float, waste_percent: float, unit_label: string|null}>
     */
    protected function resolveComponents(ProductionOrder $order): Collection
    {
        if ($order->bom_snapshot_at !== null) {
            $order->loadMissing(['bomSnapshotItems.componentProduct.unit']);

            return $order->bomSnapshotItems->map(static function ($line): array {
                return [
                    'component_product_id' => (int) $line->component_product_id,
                    'component_name' => (string) ($line->componentProduct?->name ?? $line->component_product_id),
                    'quantity_per_fg_unit' => (float) $line->quantity_per_fg_unit,
                    'waste_percent' => max(0.0, (float) ($line->waste_percent ?? 0)),
                    'unit_label' => $line->componentProduct?->unit?->unit_type,
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
                    'unit_label' => $item->componentProduct?->unit?->unit_type,
                ];
            });
        }

        return $order->bom->items->map(static function ($item): array {
            return [
                'component_product_id' => (int) $item->component_product_id,
                'component_name' => (string) ($item->componentProduct?->name ?? $item->component_product_id),
                'quantity_per_fg_unit' => (float) $item->quantity,
                'waste_percent' => max(0.0, (float) ($item->waste_percent ?? 0)),
                'unit_label' => $item->componentProduct?->unit?->unit_type,
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
}
