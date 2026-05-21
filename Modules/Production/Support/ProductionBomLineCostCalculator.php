<?php

declare(strict_types=1);

namespace Modules\Production\Support;

use App\Models\Product;
use Illuminate\Support\Collection;
use Modules\Production\Entities\ProductionBom;
use Modules\Warehouse\Services\ProductUnitPriceResolver;

final class ProductionBomLineCostCalculator
{
    public function __construct(
        private readonly ProductUnitPriceResolver $priceResolver,
    ) {}

    public function unitPurchasePrice(int $companyId, int $productId, ?int $unitId): ?float
    {
        return $this->priceResolver->resolvePurchasePrice($companyId, $productId, $unitId);
    }

    public function extendedCost(float $quantity, float $wastePercent, ?float $unitPrice): ?float
    {
        if ($unitPrice === null || $quantity <= 0) {
            return null;
        }

        $wasteMultiplier = 1 + (max(0.0, $wastePercent) / 100);

        return round($quantity * $wasteMultiplier * $unitPrice, 4);
    }

    /**
     * @param  Collection<int, Product>  $componentProducts
     * @return array<string, float|null>
     */
    public function buildUnitCostMap(Collection $componentProducts, int $companyId): array
    {
        $map = [];

        foreach ($componentProducts as $product) {
            $productId = (int) $product->id;
            $unitId = $product->unit_id !== null ? (int) $product->unit_id : null;
            $map[(string) $productId] = $this->unitPurchasePrice($companyId, $productId, $unitId);
        }

        return $map;
    }

    /**
     * @param  array{component_product_id?: int|string|null, quantity?: float|string|null, waste_percent?: float|string|null, unit_id?: int|string|null}  $line
     * @return array{unit_cost: ?float, line_total: ?float}
     */
    public function lineCostFromInput(array $line, int $companyId, ?int $unitId = null): array
    {
        $productId = (int) ($line['component_product_id'] ?? 0);
        if ($productId <= 0) {
            return ['unit_cost' => null, 'line_total' => null];
        }

        if ($unitId === null && filled($line['unit_id'] ?? null)) {
            $unitId = (int) $line['unit_id'];
        }

        $unitCost = $this->unitPurchasePrice($companyId, $productId, $unitId);
        $quantity = (float) ($line['quantity'] ?? 0);
        $wastePercent = (float) ($line['waste_percent'] ?? 0);

        return [
            'unit_cost' => $unitCost,
            'line_total' => $this->extendedCost($quantity, $wastePercent, $unitCost),
        ];
    }

    /**
     * @return array{lines: list<array{unit_cost: ?float, line_total: ?float}>, total: ?float}
     */
    public function summarizeSavedLines(ProductionBom $bom, int $companyId): array
    {
        $lineSummaries = [];
        $total = 0.0;
        $hasAny = false;

        foreach ($bom->items as $item) {
            $unitId = $item->unit_id !== null
                ? (int) $item->unit_id
                : ($item->componentProduct?->unit_id !== null ? (int) $item->componentProduct->unit_id : null);

            $costs = $this->lineCostFromInput([
                'component_product_id' => $item->component_product_id,
                'quantity' => $item->quantity,
                'waste_percent' => $item->waste_percent ?? 0,
            ], $companyId, $unitId);

            $lineSummaries[] = $costs;

            if ($costs['line_total'] !== null) {
                $total += $costs['line_total'];
                $hasAny = true;
            }
        }

        return [
            'lines' => $lineSummaries,
            'total' => $hasAny ? round($total, 4) : null,
        ];
    }
}
