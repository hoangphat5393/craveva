<?php

declare(strict_types=1);

namespace App\Services\Estimates;

use Illuminate\Support\Facades\Schema;
use Modules\Production\Entities\ProductionBom;
use Modules\Production\Support\ProductionBomLineCostCalculator;

final class EstimateProductionBomCopier
{
    public static function moduleAvailable(): bool
    {
        return class_exists(ProductionBom::class)
            && Schema::hasTable('production_boms')
            && Schema::hasTable('production_bom_items');
    }

    /**
     * @return array{error: string|null, lines: list<array<string, mixed>>}
     */
    public function linesForEstimateForm(int $productionBomId, int $companyId): array
    {
        if (! self::moduleAvailable()) {
            return ['error' => __('modules.estimates.productionBomModuleUnavailable'), 'lines' => []];
        }

        $bom = ProductionBom::query()
            ->with(['items.componentProduct.unit'])
            ->where('company_id', $companyId)
            ->find($productionBomId);

        if ($bom === null) {
            return ['error' => __('messages.itemNotFound'), 'lines' => []];
        }

        $lines = [];
        $costCalculator = app(ProductionBomLineCostCalculator::class);

        foreach ($bom->items as $item) {
            $product = $item->componentProduct;
            $quantity = round((float) $item->quantity, 4);

            if ($quantity <= 0) {
                continue;
            }

            $unitId = $item->unit_id !== null
                ? (int) $item->unit_id
                : ($product?->unit_id !== null ? (int) $product->unit_id : null);

            $costs = $costCalculator->lineCostFromInput([
                'component_product_id' => $item->component_product_id,
                'unit_id' => $unitId,
                'quantity' => $quantity,
                'waste_percent' => $item->waste_percent ?? 0,
            ], $companyId, $unitId);

            $unitCost = $costs['unit_cost'] !== null ? round((float) $costs['unit_cost'], 4) : 0.0;

            $materialName = trim((string) ($product?->name ?? ''));

            if ($materialName === '') {
                continue;
            }

            $lines[] = [
                'id' => null,
                'product_id' => $product?->id,
                'material_name' => $materialName,
                'quantity' => $quantity,
                'unit_id' => $unitId,
                'unit_cost' => $unitCost,
                'line_total' => round($quantity * $unitCost, 4),
                'notes' => null,
            ];
        }

        if ($lines === []) {
            return ['error' => __('modules.estimates.productionBomNoLines'), 'lines' => []];
        }

        return ['error' => null, 'lines' => $lines];
    }
}
