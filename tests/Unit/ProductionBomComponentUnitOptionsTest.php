<?php

declare(strict_types=1);

use App\Models\Product;
use Illuminate\Support\Collection;
use Modules\Production\Support\ProductionBomComponentUnitOptions;
use Modules\Warehouse\Services\ProductSellableUnitsService;
use Modules\Warehouse\Services\ProductUnitPriceResolver;

it('reports allowed units from sellable units service', function (): void {
    $sellable = Mockery::mock(ProductSellableUnitsService::class);
    $sellable->shouldReceive('sellableUnits')
        ->with(1, 10, false)
        ->andReturn([
            ['unit_id' => 5, 'label' => 'Kg', 'is_base' => true, 'factor_to_base' => 1.0],
            ['unit_id' => 8, 'label' => 'Box', 'is_base' => false, 'factor_to_base' => 12.0],
        ]);

    $resolver = Mockery::mock(ProductUnitPriceResolver::class);

    $options = new ProductionBomComponentUnitOptions($sellable, $resolver);

    expect($options->isAllowedUnit(1, 10, 8))->toBeTrue();
    expect($options->isAllowedUnit(1, 10, 99))->toBeFalse();
    expect($options->defaultUnitIdForProduct(1, 10))->toBe(5);
});

it('builds nested unit cost map', function (): void {
    $sellable = Mockery::mock(ProductSellableUnitsService::class);
    $sellable->shouldReceive('sellableUnits')
        ->with(1, 10, false)
        ->andReturn([
            ['unit_id' => 5, 'label' => 'Kg', 'is_base' => true, 'factor_to_base' => 1.0],
            ['unit_id' => 8, 'label' => 'Box', 'is_base' => false, 'factor_to_base' => 12.0],
        ]);

    $resolver = Mockery::mock(ProductUnitPriceResolver::class);
    $resolver->shouldReceive('resolvePurchasePrice')->with(1, 10, 5)->andReturn(10.0);
    $resolver->shouldReceive('resolvePurchasePrice')->with(1, 10, 8)->andReturn(120.0);

    $product = new Product;
    $product->id = 10;

    $map = (new ProductionBomComponentUnitOptions($sellable, $resolver))
        ->unitCostByProductAndUnit(Collection::make([$product]), 1);

    expect($map)->toBe([
        '10' => [
            '5' => 10.0,
            '8' => 120.0,
        ],
    ]);
});
