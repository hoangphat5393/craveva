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
});

it('treats only service as non-stockable', function (): void {
    expect(ProductType::isStockable('goods'))->toBeTrue();
    expect(ProductType::isStockable('raw_material'))->toBeTrue();
    expect(ProductType::isStockable('service'))->toBeFalse();
});
