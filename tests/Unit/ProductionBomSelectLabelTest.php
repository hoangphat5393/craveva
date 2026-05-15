<?php

declare(strict_types=1);

use App\Models\Product;
use Modules\Production\Entities\ProductionBom;

it('formats bom select label as finished good name and code', function (): void {
    $product = new Product;
    $product->id = 42;
    $product->name = '3 in 1 Classic';

    $bom = new ProductionBom;
    $bom->id = 9;
    $bom->output_product_id = 42;
    $bom->code = 'bio-uat-20260507-a';
    $bom->version = 't-http-69fb7d5fb25466.71659882';
    $bom->setRelation('outputProduct', $product);

    expect($bom->labelForSelect())->toBe('3 in 1 Classic — bio-uat-20260507-a');
});

it('falls back to bom id when code is empty', function (): void {
    $product = new Product;
    $product->id = 1;
    $product->name = 'Arabica Coffee';

    $bom = new ProductionBom;
    $bom->id = 6;
    $bom->output_product_id = 1;
    $bom->code = '';
    $bom->setRelation('outputProduct', $product);

    expect($bom->labelForSelect())->toBe('Arabica Coffee (BOM #6)');
});
