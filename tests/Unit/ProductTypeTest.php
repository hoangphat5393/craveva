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

it('defines cost-only purchase pricing for production inputs and packaging', function (): void {
    expect(ProductType::costOnlyPurchasePricingValues())->toBe([
        'raw_material',
        'semi_finished',
        'packaging',
    ]);
});

it('defines sell-only purchase pricing for service', function (): void {
    expect(ProductType::sellOnlyPurchasePricingValues())->toBe(['service']);
});

it('hides selling price and uses cost UOM column for cost-only product types', function (): void {
    expect(ProductType::hidesSellingPriceOnPurchaseForm('raw_material'))->toBeTrue();
    expect(ProductType::uomPriceColumnUsesCost('raw_material'))->toBeTrue();
    expect(ProductType::hidesSellingPriceOnPurchaseForm('semi_finished'))->toBeTrue();
    expect(ProductType::uomPriceColumnUsesCost('semi_finished'))->toBeTrue();
    expect(ProductType::hidesSellingPriceOnPurchaseForm('packaging'))->toBeTrue();
    expect(ProductType::uomPriceColumnUsesCost('packaging'))->toBeTrue();
    expect(ProductType::hidesSellingPriceOnPurchaseForm('goods'))->toBeFalse();
    expect(ProductType::hidesSellingPriceOnPurchaseForm('service'))->toBeFalse();
});

it('hides cost price on purchase form only for service', function (): void {
    expect(ProductType::hidesCostPriceOnPurchaseForm('service'))->toBeTrue();
    expect(ProductType::hidesCostPriceOnPurchaseForm('goods'))->toBeFalse();
    expect(ProductType::hidesCostPriceOnPurchaseForm('packaging'))->toBeFalse();
    expect(ProductType::hidesCostPriceOnPurchaseForm('raw_material'))->toBeFalse();
});
