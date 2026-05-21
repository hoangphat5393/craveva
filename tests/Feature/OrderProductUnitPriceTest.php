<?php

use App\Models\Product;
use App\Models\UnitType;
use App\Support\OrderProductUnitPrice;
use Illuminate\Support\Facades\Schema;
use Modules\Warehouse\Services\ProductSellableUnitsService;
use Modules\Warehouse\Services\ProductUnitConversionSyncService;

beforeEach(function () {
    if (! Schema::hasTable('product_unit_conversions')) {
        $this->markTestSkipped('product_unit_conversions table not available');
    }
});

it('lists sellable units and prices for order line', function () {
    $companyId = (int) (company()->id ?? 1);

    $baseUnit = UnitType::query()->first();
    if ($baseUnit === null) {
        $baseUnit = UnitType::create([
            'company_id' => $companyId,
            'unit_type' => 'Kg Order Test',
            'default' => 0,
        ]);
    }

    $packUnit = UnitType::query()->where('id', '!=', $baseUnit->id)->first();
    if ($packUnit === null) {
        $packUnit = UnitType::create([
            'company_id' => $companyId,
            'unit_type' => 'Pack Order Test',
            'default' => 0,
        ]);
    }

    $product = Product::factory()->create([
        'company_id' => $companyId,
        'unit_id' => $baseUnit->id,
        'price' => 100,
    ]);

    app(ProductUnitConversionSyncService::class)->sync($product, [
        [
            'unit_id' => (int) $packUnit->id,
            'factor_to_base' => 10,
            'selling_price' => null,
            'for_sale' => true,
            'sort_order' => 0,
        ],
    ]);

    $units = app(ProductSellableUnitsService::class)->sellableUnits($companyId, (int) $product->id);

    expect($units)->toHaveCount(2);

    $packPrice = OrderProductUnitPrice::formatForOrder($product, (int) $packUnit->id, null, null);

    expect((float) $packPrice)->toEqual(1000.0);
});
