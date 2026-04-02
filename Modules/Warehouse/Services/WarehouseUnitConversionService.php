<?php

namespace Modules\Warehouse\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Schema;
use Modules\Warehouse\Entities\ProductUnitConversion;
use Modules\Warehouse\Exceptions\WarehouseBusinessException;

class WarehouseUnitConversionService
{
    public function convertToBase(
        int $companyId,
        int $productId,
        float $quantity,
        ?int $fromUnitId
    ): float {
        $baseUnitId = $this->baseUnitId($productId);
        if ($baseUnitId <= 0 || $fromUnitId === null || $fromUnitId <= 0 || $fromUnitId === $baseUnitId) {
            return $quantity;
        }

        if (! Schema::hasTable('product_unit_conversions')) {
            return $quantity;
        }

        $factor = ProductUnitConversion::query()
            ->where('company_id', $companyId)
            ->where('product_id', $productId)
            ->where('unit_id', $fromUnitId)
            ->value('factor_to_base');

        if ($factor === null) {
            if ((bool) config('warehouse.strict_unit_conversion', false)) {
                throw new WarehouseBusinessException(__('warehouse::app.err_missing_unit_conversion', [
                    'product_id' => $productId,
                    'unit_id' => $fromUnitId,
                ]));
            }

            return $quantity;
        }

        return $quantity * (float) $factor;
    }

    public function baseUnitId(int $productId): int
    {
        if (! Schema::hasTable('products') || ! Schema::hasColumn('products', 'unit_id')) {
            return 0;
        }

        return (int) (Product::withoutGlobalScopes()->where('id', $productId)->value('unit_id') ?? 0);
    }
}
