<?php

declare(strict_types=1);

namespace Modules\Warehouse\Services;

use App\Enums\ProductType;
use App\Models\Product;
use Illuminate\Support\Facades\Schema;
use Modules\Warehouse\Entities\ProductUnitConversion;

class ProductUnitPriceResolver
{
    /**
     * Selling price for one unit of the given UOM (base or alternate).
     */
    public function resolveSellingPrice(int $companyId, int $productId, ?int $unitId): ?float
    {
        $product = Product::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->where('id', $productId)
            ->first(['id', 'price', 'unit_id']);

        if ($product === null) {
            return null;
        }

        $basePrice = $product->price !== null && $product->price !== ''
            ? (float) $product->price
            : null;

        if ($basePrice === null) {
            return null;
        }

        $baseUnitId = (int) ($product->unit_id ?? 0);
        $targetUnitId = $unitId !== null && $unitId > 0 ? $unitId : $baseUnitId;

        if ($baseUnitId <= 0 || $targetUnitId <= 0 || $targetUnitId === $baseUnitId) {
            return $basePrice;
        }

        if (! Schema::hasTable('product_unit_conversions')) {
            return $basePrice;
        }

        $row = ProductUnitConversion::query()
            ->where('company_id', $companyId)
            ->where('product_id', $productId)
            ->where('unit_id', $targetUnitId)
            ->first(['selling_price', 'factor_to_base']);

        if ($row === null) {
            return $basePrice;
        }

        if ($row->selling_price !== null) {
            return (float) $row->selling_price;
        }

        $factor = (float) $row->factor_to_base;

        if ($factor <= 0) {
            return $basePrice;
        }

        return round($basePrice * $factor, 4);
    }

    /**
     * Purchase / cost price for one unit of the given UOM (uses purchase_price, else selling price).
     */
    public function resolvePurchasePrice(int $companyId, int $productId, ?int $unitId): ?float
    {
        $product = Product::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->where('id', $productId)
            ->first(['id', 'type', 'price', 'purchase_price', 'unit_id']);

        if ($product === null) {
            return null;
        }

        $costOnlyProduct = ProductType::hidesSellingPriceOnPurchaseForm($product->type);

        $basePrice = $product->purchase_price !== null && $product->purchase_price !== ''
            ? (float) $product->purchase_price
            : null;

        if (! $costOnlyProduct && ($basePrice === null || $basePrice <= 0)) {
            $basePrice = $product->price !== null && $product->price !== ''
                ? (float) $product->price
                : null;
        }

        if ($basePrice === null || $basePrice <= 0) {
            return null;
        }

        $baseUnitId = (int) ($product->unit_id ?? 0);
        $targetUnitId = $unitId !== null && $unitId > 0 ? $unitId : $baseUnitId;

        if ($baseUnitId <= 0 || $targetUnitId <= 0 || $targetUnitId === $baseUnitId) {
            return $basePrice;
        }

        if (! Schema::hasTable('product_unit_conversions')) {
            return $basePrice;
        }

        $row = ProductUnitConversion::query()
            ->where('company_id', $companyId)
            ->where('product_id', $productId)
            ->where('unit_id', $targetUnitId)
            ->first($this->conversionPriceColumns());

        if ($row === null) {
            return $basePrice;
        }

        $explicitCost = $this->columnValue($row, 'cost_price');
        if ($explicitCost !== null) {
            return $explicitCost;
        }

        if (! $costOnlyProduct && $row->selling_price !== null) {
            return (float) $row->selling_price;
        }

        $factor = (float) $row->factor_to_base;

        if ($factor <= 0) {
            return $basePrice;
        }

        return round($basePrice * $factor, 4);
    }

    /**
     * @return list<string>
     */
    private function conversionPriceColumns(): array
    {
        $columns = ['selling_price', 'factor_to_base'];

        if (Schema::hasColumn('product_unit_conversions', 'cost_price')) {
            $columns[] = 'cost_price';
        }

        return $columns;
    }

    private function columnValue(ProductUnitConversion $row, string $column): ?float
    {
        if (! Schema::hasColumn('product_unit_conversions', $column)) {
            return null;
        }

        $value = $row->{$column};

        return $value !== null && $value !== '' ? (float) $value : null;
    }
}
