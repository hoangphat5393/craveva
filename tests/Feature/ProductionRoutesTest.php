<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

it('registers core production routes', function (): void {
    $names = [
        'production.boms.index',
        'production.boms.create',
        'production.boms.store',
        'production.orders.index',
        'production.orders.create',
        'production.orders.store',
        'production.orders.show',
        'production.orders.release',
        'production.batches.show',
        'production.batches.trace',
        'production.fg-quantity-policy.index',
    ];

    foreach ($names as $name) {
        expect(Route::has($name))->toBeTrue("Missing route name: {$name}");
    }
});

it('scopes production urls under account prefix', function (): void {
    expect(parse_url(route('production.orders.index'), PHP_URL_PATH))->toContain('/account/production/orders');
    expect(parse_url(route('production.boms.create'), PHP_URL_PATH))->toContain('/account/production/boms/create');
});

it('redirects legacy GET /production to production orders index', function (): void {
    $this->get('/production')->assertRedirect('/account/production/orders');
});

it('redirects guest away from authenticated production bom index', function (): void {
    $response = $this->get(route('production.boms.index'));

    expect($response->status())->toBeIn([302, 401]);
});
