<?php

use App\Models\Product;
use App\Models\UnitType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Modules\Purchase\Entities\PurchaseProduct;
use Modules\Warehouse\Entities\ProductUnitConversion;
use Modules\Warehouse\Services\ProductUnitConversionSyncService;
use Modules\Warehouse\Services\ProductUnitPriceResolver;
use Modules\Warehouse\Services\WarehouseUnitConversionService;

beforeEach(function () {
    if (! Schema::hasTable('product_unit_conversions')) {
        $this->markTestSkipped('product_unit_conversions table not available');
    }
});

it('syncs alternate units with selling price and resolves price', function () {
    $companyId = (int) (company()->id ?? 1);

    $baseUnit = UnitType::query()->first();
    if ($baseUnit === null) {
        $baseUnit = UnitType::create([
            'company_id' => $companyId,
            'unit_type' => 'Kilogram Test',
            'default' => 0,
        ]);
    }

    $altUnit = UnitType::query()->where('id', '!=', $baseUnit->id)->first();
    if ($altUnit === null) {
        $altUnit = UnitType::create([
            'company_id' => $companyId,
            'unit_type' => 'Pack Test',
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
            'unit_id' => (int) $altUnit->id,
            'factor_to_base' => 12,
            'selling_price' => null,
            'for_sale' => true,
            'sort_order' => 0,
        ],
    ]);

    expect(ProductUnitConversion::query()
        ->where('product_id', $product->id)
        ->where('unit_id', $altUnit->id)
        ->value('factor_to_base'))->toEqual(12.0);

    $resolved = app(ProductUnitPriceResolver::class)->resolveSellingPrice(
        $companyId,
        (int) $product->id,
        (int) $altUnit->id,
    );

    expect($resolved)->toEqual(1200.0);

    $baseQty = app(WarehouseUnitConversionService::class)->convertToBase(
        $companyId,
        (int) $product->id,
        2.0,
        (int) $altUnit->id,
    );

    expect($baseQty)->toEqual(24.0);
});

it('rejects duplicate alternate unit ids from request', function () {
    $companyId = (int) (company()->id ?? 1);
    $baseUnit = UnitType::query()->firstOrFail();
    $altUnit = UnitType::query()->where('id', '!=', $baseUnit->id)->firstOrFail();

    $product = Product::factory()->create([
        'company_id' => $companyId,
        'unit_id' => $baseUnit->id,
        'price' => 50,
    ]);

    $request = Request::create('/', 'POST', [
        'unit_conversion_unit_id' => [(int) $altUnit->id, (int) $altUnit->id],
        'unit_conversion_factor' => [1, 2],
        'unit_conversion_selling_price' => [null, null],
        'unit_conversion_for_sale' => ['1', '0'],
    ]);

    $sync = app(ProductUnitConversionSyncService::class);

    expect(fn () => $sync->parseRowsFromRequest($request, (int) $baseUnit->id))
        ->toThrow(InvalidArgumentException::class);
});

it('syncFromRequest accepts PurchaseProduct from purchase module', function () {
    $companyId = (int) (company()->id ?? 1);

    $baseUnit = UnitType::query()->where('company_id', $companyId)->first()
        ?? UnitType::create(['company_id' => $companyId, 'unit_type' => 'Base UOM Test', 'default' => 0]);

    $altUnit = UnitType::query()
        ->where('company_id', $companyId)
        ->where('id', '!=', $baseUnit->id)
        ->first()
        ?? UnitType::create(['company_id' => $companyId, 'unit_type' => 'Alt UOM Test', 'default' => 0]);

    $core = Product::factory()->create([
        'company_id' => $companyId,
        'unit_id' => $baseUnit->id,
        'price' => 20,
    ]);

    $purchaseProduct = PurchaseProduct::query()->findOrFail($core->id);

    $request = Request::create('/', 'POST', [
        'unit_type' => $baseUnit->id,
        'unit_conversion_unit_id' => [(int) $altUnit->id],
        'unit_conversion_factor' => [6],
        'unit_conversion_selling_price' => [120],
        'unit_conversion_for_sale' => ['1'],
    ]);

    app(ProductUnitConversionSyncService::class)->syncFromRequest($purchaseProduct, $request);

    expect(ProductUnitConversion::query()
        ->where('company_id', $companyId)
        ->where('product_id', $core->id)
        ->count())->toBe(1);
});
