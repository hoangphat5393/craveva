<?php

declare(strict_types=1);

use App\Models\Product;
use Illuminate\Support\Collection;
use Modules\Production\Support\ProductionBomLineCostCalculator;
use Modules\Warehouse\Services\ProductUnitPriceResolver;

it('computes extended cost with waste percent', function (): void {
    $calculator = new ProductionBomLineCostCalculator(new ProductUnitPriceResolver);

    expect($calculator->extendedCost(10, 10, 20.0))->toEqual(220.0);
    expect($calculator->extendedCost(1, 0, 15.5))->toEqual(15.5);
});

it('returns null extended cost when quantity or unit price is invalid', function (): void {
    $calculator = new ProductionBomLineCostCalculator(new ProductUnitPriceResolver);

    expect($calculator->extendedCost(0, 0, 10.0))->toBeNull();
    expect($calculator->extendedCost(5, 0, null))->toBeNull();
});

it('uses unit_id from line input for purchase price', function (): void {
    $resolver = Mockery::mock(ProductUnitPriceResolver::class);
    $resolver->shouldReceive('resolvePurchasePrice')
        ->with(1, 10, 8)
        ->once()
        ->andReturn(3.5);

    $calculator = new ProductionBomLineCostCalculator($resolver);

    $result = $calculator->lineCostFromInput([
        'component_product_id' => 10,
        'unit_id' => 8,
        'quantity' => 2,
        'waste_percent' => 0,
    ], 1);

    expect($result['unit_cost'])->toEqual(3.5);
    expect($result['line_total'])->toEqual(7.0);
});

it('returns null line cost when component is not selected', function (): void {
    $calculator = new ProductionBomLineCostCalculator(new ProductUnitPriceResolver);

    $result = $calculator->lineCostFromInput([
        'component_product_id' => '',
        'quantity' => 1,
        'waste_percent' => 0,
    ], 1);

    expect($result['unit_cost'])->toBeNull();
    expect($result['line_total'])->toBeNull();
});

it('builds unit cost map from component products', function (): void {
    $resolver = Mockery::mock(ProductUnitPriceResolver::class);
    $resolver->shouldReceive('resolvePurchasePrice')
        ->with(1, 10, 5)
        ->once()
        ->andReturn(12.5);

    $calculator = new ProductionBomLineCostCalculator($resolver);

    $product = new Product;
    $product->id = 10;
    $product->unit_id = 5;

    $map = $calculator->buildUnitCostMap(Collection::make([$product]), 1);

    expect($map)->toBe(['10' => 12.5]);
});
