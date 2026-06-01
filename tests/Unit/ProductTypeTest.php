<?php

declare(strict_types=1);

use App\Enums\ProductType;
use App\Models\Product;

it('defines five product types with goods as finished goods', function (): void {
    expect(ProductType::values())->toBe([
        'goods',
        'service',
        'raw_material',
        'semi_finished',
        'packaging',
    ]);
});

it('scopes finished goods and bom components on product query', function (): void {
    expect(Product::forBomOutput()->toSql())->toContain('`type` =');
    expect(Product::forBomComponents()->toSql())->toContain('`type` in');
    expect(Product::forBomRawMaterials()->toSql())->toContain('`type` =');
});

it('defines production BOM raw material type values', function (): void {
    expect(ProductType::bomRawMaterialValues())->toBe(['raw_material']);
});

it('allows alternate unit conversions only for raw material and semi finished', function (): void {
    expect(ProductType::alternateUnitConversionValues())->toBe(['raw_material', 'semi_finished']);
    expect(ProductType::supportsAlternateUnitConversions('raw_material'))->toBeTrue();
    expect(ProductType::supportsAlternateUnitConversions('semi_finished'))->toBeTrue();
    expect(ProductType::supportsAlternateUnitConversions('goods'))->toBeFalse();
    expect(ProductType::supportsAlternateUnitConversions('packaging'))->toBeFalse();
    expect(ProductType::supportsAlternateUnitConversions('service'))->toBeFalse();
    expect(ProductType::supportsAlternateUnitConversions(null))->toBeFalse();
});

it('treats only service as non-stockable', function (): void {
    expect(ProductType::isStockable('goods'))->toBeTrue();
    expect(ProductType::isStockable('raw_material'))->toBeTrue();
    expect(ProductType::isStockable('service'))->toBeFalse();
});
