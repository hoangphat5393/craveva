<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

it('registers warehouse product batch routes', function (): void {
    expect(Route::has('warehouse.product-batches.index'))->toBeTrue();
    expect(Route::has('warehouse.product-batches.show'))->toBeTrue();
});

it('scopes warehouse product batch urls under account prefix', function (): void {
    expect(parse_url(route('warehouse.product-batches.index'), PHP_URL_PATH))->toContain('/account/warehouse-product-batches');
});

it('redirects guest away from warehouse product batch index', function (): void {
    $this->get(route('warehouse.product-batches.index'))->assertRedirect();
});
