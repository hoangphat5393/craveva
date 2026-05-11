<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

/**
 * Guards the Biomixing “full demo” wiring: named routes exist for Production + Warehouse paths used in runbook.
 */
it('registers biomixing demo production routes', function (): void {
    $names = [
        'production.fg-quantity-policy.index',
        'production.fg-quantity-policy.update',
        'production.boms.index',
        'production.boms.create',
        'production.boms.store',
        'production.boms.show',
        'production.boms.edit',
        'production.boms.update',
        'production.boms.destroy',
        'production.orders.index',
        'production.orders.create',
        'production.orders.store',
        'production.orders.show',
        'production.orders.edit',
        'production.orders.update',
        'production.orders.release',
        'production.orders.cancel',
        'production.batches.show',
        'production.batches.trace',
        'production.batches.apply-planned-from-bom-snapshot',
        'production.batches.consumptions.store',
        'production.batches.consumptions.assign-warehouse-batch',
        'production.batches.post-consumptions',
        'production.batches.outputs.store',
        'production.outputs.approve-variance',
        'production.outputs.post-fg-receipt',
        'production.batches.rework-orders.store',
        'production.batches.rework-orders.approve',
        'production.batches.rework-orders.reject',
        'production.batches.rework-orders.complete',
    ];

    foreach ($names as $name) {
        expect(Route::has($name))->toBeTrue("Missing route name: {$name}");
    }
});

it('registers biomixing demo warehouse routes', function (): void {
    $names = [
        'warehouse.index',
        'warehouse.stock.index',
        'warehouse.movements.index',
        'warehouse.product-batches.index',
        'warehouse.product-batches.show',
    ];

    foreach ($names as $name) {
        expect(Route::has($name))->toBeTrue("Missing route name: {$name}");
    }
});

it('scopes biomixing demo urls under account prefix', function (): void {
    expect(parse_url(route('production.orders.index'), PHP_URL_PATH))->toContain('/account/production/orders');
    expect(parse_url(route('warehouse.stock.index'), PHP_URL_PATH))->toContain('/account/warehouse-stock');
    expect(parse_url(route('warehouse.product-batches.index'), PHP_URL_PATH))->toContain('/account/warehouse-product-batches');
});
