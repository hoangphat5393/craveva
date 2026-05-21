<?php

declare(strict_types=1);

namespace Modules\Warehouse\Services;

use App\Models\Product;
use App\Models\UnitType;
use Illuminate\Support\Facades\Schema;
use Modules\Warehouse\Entities\ProductUnitConversion;

class ProductSellableUnitsService
{
    public function __construct(
        protected ProductUnitPriceResolver $priceResolver
    ) {}

    /**
     * @return list<array{unit_id:int, label:string, is_base:bool, factor_to_base:float}>
     */
    public function sellableUnits(int $companyId, int $productId, bool $forSaleOnly = true): array
    {
        $product = Product::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->where('id', $productId)
            ->first(['id', 'unit_id']);

        if ($product === null) {
            return [];
        }

        $baseUnitId = (int) ($product->unit_id ?? 0);
        $units = [];

        if ($baseUnitId > 0) {
            $label = UnitType::query()->where('id', $baseUnitId)->value('unit_type');
            $units[$baseUnitId] = [
                'unit_id' => $baseUnitId,
                'label' => $label ? ucwords((string) $label) : (string) $baseUnitId,
                'is_base' => true,
                'factor_to_base' => 1.0,
            ];
        }

        if (! Schema::hasTable('product_unit_conversions')) {
            return array_values($units);
        }

        $conversions = ProductUnitConversion::query()
            ->where('company_id', $companyId)
            ->where('product_id', $productId)
            ->when(
                $forSaleOnly && Schema::hasColumn('product_unit_conversions', 'for_sale'),
                fn ($q) => $q->where('for_sale', true)
            )
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get(['unit_id', 'factor_to_base']);

        foreach ($conversions as $conversion) {
            $unitId = (int) $conversion->unit_id;
            if ($unitId <= 0 || $unitId === $baseUnitId) {
                continue;
            }

            $label = UnitType::query()->where('id', $unitId)->value('unit_type');
            $units[$unitId] = [
                'unit_id' => $unitId,
                'label' => $label ? ucwords((string) $label) : (string) $unitId,
                'is_base' => false,
                'factor_to_base' => (float) $conversion->factor_to_base,
            ];
        }

        return array_values($units);
    }

    /**
     * @return list<array{unit_id:int, label:string, is_base:bool, factor_to_base:float, unit_price:float}>
     */
    public function sellableUnitsWithPrices(int $companyId, int $productId, bool $forSaleOnly = true): array
    {
        $rows = $this->sellableUnits($companyId, $productId, $forSaleOnly);

        foreach ($rows as $index => $row) {
            $price = $this->priceResolver->resolveSellingPrice(
                $companyId,
                $productId,
                (int) $row['unit_id'],
            );
            $rows[$index]['unit_price'] = $price ?? 0.0;
        }

        return $rows;
    }
}
