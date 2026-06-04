<?php

declare(strict_types=1);

use App\Enums\ProductType;
use App\Models\Company;
use App\Models\Product;
use App\Models\UnitType;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;
use Modules\Warehouse\Entities\ProductUnitConversion;
use Modules\Warehouse\Services\ProductUnitPriceResolver;

uses(DatabaseTransactions::class);

it('uses alternate uom cost_price for raw material bom costing not selling_price', function (): void {
    if (! Schema::hasTable('product_unit_conversions') || ! Schema::hasColumn('product_unit_conversions', 'cost_price')) {
        test()->markTestSkipped('product_unit_conversions.cost_price not available');
    }

    $company = Company::withoutGlobalScopes()->where('status', 'active')->orderBy('id')->first();
    if ($company === null) {
        test()->markTestSkipped('No active company.');
    }

    $baseUnit = UnitType::query()->firstOrFail();
    $altUnit = UnitType::query()->where('id', '!=', $baseUnit->id)->firstOrFail();

    $product = Product::factory()->create([
        'company_id' => $company->id,
        'name' => 'RM UOM cost test '.uniqid(),
        'type' => ProductType::RawMaterial->value,
        'unit_id' => $baseUnit->id,
        'purchase_price' => 10,
        'price' => 999,
        'purchase_information' => 1,
    ]);

    ProductUnitConversion::query()->create([
        'company_id' => $company->id,
        'product_id' => $product->id,
        'unit_id' => $altUnit->id,
        'factor_to_base' => 3,
        'selling_price' => 888,
        'cost_price' => 30,
        'for_sale' => false,
        'sort_order' => 0,
    ]);

    $resolved = app(ProductUnitPriceResolver::class)->resolvePurchasePrice(
        (int) $company->id,
        (int) $product->id,
        (int) $altUnit->id,
    );

    expect($resolved)->toEqual(30.0);
});

it('does not fall back to selling price on uom row for cost-only product types', function (): void {
    if (! Schema::hasTable('product_unit_conversions') || ! Schema::hasColumn('product_unit_conversions', 'cost_price')) {
        test()->markTestSkipped('product_unit_conversions.cost_price not available');
    }

    $company = Company::withoutGlobalScopes()->where('status', 'active')->orderBy('id')->first();
    if ($company === null) {
        test()->markTestSkipped('No active company.');
    }

    $baseUnit = UnitType::query()->firstOrFail();
    $altUnit = UnitType::query()->where('id', '!=', $baseUnit->id)->firstOrFail();

    $product = Product::factory()->create([
        'company_id' => $company->id,
        'name' => 'RM UOM factor cost test '.uniqid(),
        'type' => ProductType::RawMaterial->value,
        'unit_id' => $baseUnit->id,
        'purchase_price' => 10,
        'price' => 500,
        'purchase_information' => 1,
    ]);

    ProductUnitConversion::query()->create([
        'company_id' => $company->id,
        'product_id' => $product->id,
        'unit_id' => $altUnit->id,
        'factor_to_base' => 2,
        'selling_price' => 200,
        'cost_price' => null,
        'for_sale' => false,
        'sort_order' => 0,
    ]);

    $resolved = app(ProductUnitPriceResolver::class)->resolvePurchasePrice(
        (int) $company->id,
        (int) $product->id,
        (int) $altUnit->id,
    );

    expect($resolved)->toEqual(20.0);
});
