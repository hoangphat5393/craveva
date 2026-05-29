<?php

declare(strict_types=1);

it('exposes split operations menu labels in english', function (): void {
    app()->setLocale('en');

    expect(__('app.menu.procurement'))->toBe('Purchasing')
        ->and(__('app.menu.salesFulfillment'))->toBe('Sales orders')
        ->and(__('app.menu.inventoryWarehouse'))->toBe('Inventory')
        ->and(__('app.menu.productionHub'))->toBe('Production')
        ->and(__('app.menu.sales'))->toBe('Customer Management')
        ->and(__('purchase::app.menu.inventory'))->toBe('Opening stock');
});

it('exposes ux-010 sidebar labels in vietnamese', function (): void {
    app()->setLocale('vi');

    expect(__('app.menu.sales'))->toBe('Quản lý khách hàng')
        ->and(__('purchase::app.menu.inventory'))->toBe('Tồn đầu kỳ');
});

it('purchase sidebar template uses split menu keys instead of single operations accordion', function (): void {
    $path = module_path('Purchase', 'Resources/views/sections/sidebar.blade.php');

    $contents = file_get_contents($path);

    expect($contents)->toContain("__('app.menu.procurement')")
        ->and($contents)->toContain("__('app.menu.salesFulfillment')")
        ->and($contents)->toContain("__('app.menu.inventoryWarehouse')")
        ->and($contents)->toContain("__('app.menu.productionHub')")
        ->and($contents)->not->toContain("__('app.menu.operations')");
});
