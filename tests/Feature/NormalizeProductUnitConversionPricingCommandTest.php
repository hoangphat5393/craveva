<?php

declare(strict_types=1);

use App\Models\Product;
use App\Models\UnitType;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Modules\Warehouse\Entities\ProductUnitConversion;

it('normalizes uom pricing via artisan command', function (): void {
    if (! Schema::hasTable('product_unit_conversions') || ! Schema::hasColumn('product_unit_conversions', 'cost_price')) {
        $this->markTestSkipped('product_unit_conversions.cost_price not available');
    }

    $companyId = (int) (company()->id ?? 1);
    $baseUnit = UnitType::query()->firstOrFail();
    $altUnit = UnitType::query()->where('id', '!=', $baseUnit->id)->firstOrFail();

    $product = Product::factory()->create([
        'company_id' => $companyId,
        'unit_id' => $baseUnit->id,
        'type' => 'raw_material',
        'purchase_price' => 10,
    ]);

    ProductUnitConversion::query()->create([
        'company_id' => $companyId,
        'product_id' => $product->id,
        'unit_id' => $altUnit->id,
        'factor_to_base' => 12,
        'selling_price' => 99.5,
        'cost_price' => null,
        'for_sale' => true,
        'sort_order' => 0,
    ]);

    Artisan::call('product-unit-conversions:normalize-uom-pricing', [
        '--force' => true,
        '--no-interaction' => true,
    ]);

    $row = ProductUnitConversion::query()
        ->where('product_id', $product->id)
        ->where('unit_id', $altUnit->id)
        ->first();

    expect($row)->not->toBeNull();
    expect((float) $row->cost_price)->toEqual(99.5);
    expect($row->selling_price)->toBeNull();
    expect($row->for_sale)->toBeFalse();
});
