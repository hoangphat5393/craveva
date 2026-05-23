<?php

use App\Models\Product;
use Modules\Production\Support\ProductionProductSelectLabel;

it('formats manufactured product label with sku', function () {
    $product = new Product([
        'id' => 10,
        'name' => 'Bánh kem',
        'sku' => 'banhkem2',
    ]);

    expect(ProductionProductSelectLabel::forProduct($product))->toBe('Bánh kem (banhkem2)');
});

it('formats manufactured product label without sku', function () {
    $product = new Product([
        'id' => 11,
        'name' => 'COMPUTER',
        'sku' => null,
    ]);

    expect(ProductionProductSelectLabel::forProduct($product))->toBe('COMPUTER');
});

it('appends sku when name is empty', function () {
    $product = new Product([
        'id' => 99,
        'name' => '',
        'sku' => 'X-1',
    ]);

    $label = ProductionProductSelectLabel::forProduct($product);

    expect($label)->toEndWith('(X-1)')
        ->and($label)->not->toBe('(X-1)');
});
