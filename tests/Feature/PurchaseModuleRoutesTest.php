<?php

use Illuminate\Support\Facades\Route;

it('registers core Purchase module web route names', function () {
    $names = [
        'purchase-products.index',
        'purchase-order.index',
        'bills.index',
        'vendors.index',
        'vendor-payments.index',
        'vendor-credits.index',
        'purchase-inventory.index',
        'grn.index',
        'sales-do.index',
        'delivery-orders.index',
        'sales-shipments.index',
        'reports.index',
        'vendor-cateogory.index',
    ];

    foreach ($names as $name) {
        expect(Route::has($name))->toBeTrue("Missing route: {$name}");
    }
});
