<?php

declare(strict_types=1);

namespace App\Services\Estimates;

use App\Models\Product;
use Illuminate\Support\Facades\Schema;
use Modules\Production\Entities\ProductionBom;

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

        foreach ($bom->items as $item) {
            $product = $item->componentProduct;
            $quantity = round((float) $item->quantity, 4);

            if ($quantity <= 0) {
                continue;
            }

            $unitCost = 0.0;

            if ($product instanceof Product) {
                $purchase = $product->purchase_price;
                if ($purchase !== null && $purchase !== '' && is_numeric($purchase)) {
                    $unitCost = round((float) $purchase, 4);
                }
            }

            $materialName = trim((string) ($product?->name ?? ''));

            if ($materialName === '') {
                continue;
            }

            $lines[] = [
                'id' => null,
                'product_id' => $product?->id,
                'material_name' => $materialName,
                'quantity' => $quantity,
                'unit_id' => $item->unit_id ?? $product?->unit_id,
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
