<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

/**
 * Non-UI evidence for P0 Biomixing: bidirectional trace wiring (P0-05) and
 * core hub route names touched by the P0-08 mini-UAT template (manual steps still required).
 */
it('keeps production trace blade wired to warehouse product batch detail', function (): void {
    $trace = (string) file_get_contents(base_path('Modules/Production/Resources/views/batches/trace.blade.php'));
    $warehouseShow = (string) file_get_contents(base_path('Modules/Warehouse/Resources/views/product-batches/show.blade.php'));

    expect($trace)->toContain("route('warehouse.product-batches.show'")
        ->and($warehouseShow)->toContain("route('production.batches.trace'");
});

it('registers p0 mini uat hub route names for estimate order sales do invoice po grn bill', function (): void {
    $names = [
        'estimates.index',
        'estimates.convert_to_sales_order',
        'orders.index',
        'orders.make_invoice',
        'invoices.index',
        'invoices.create',
        'sales-do.index',
        'sales-do.confirm',
        'sales-do.ship',
        'purchase-order.index',
        'grn.index',
        'grn.changeStatus',
        'bills.index',
        'bills.create',
    ];

    foreach ($names as $name) {
        expect(Route::has($name))->toBeTrue("Missing route name: {$name}");
    }
});
