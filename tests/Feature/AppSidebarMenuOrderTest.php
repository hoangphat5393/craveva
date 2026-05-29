<?php

declare(strict_types=1);

it('places customer management after operations block in company menu', function (): void {
    $path = resource_path('views/sections/menu.blade.php');

    $contents = file_get_contents($path);

    $operationsPos = strpos($contents, "@includeIf('purchase::sections.sidebar')");
    $customerManagementPos = strpos($contents, "@include('sections.partials.customer-management-sidebar-accordion')");

    expect($operationsPos)->not->toBeFalse()
        ->and($customerManagementPos)->not->toBeFalse()
        ->and($operationsPos)->toBeLessThan($customerManagementPos);
});

it('customer management partial uses sales menu key', function (): void {
    $path = resource_path('views/sections/partials/customer-management-sidebar-accordion.blade.php');

    $contents = file_get_contents($path);

    expect($contents)->toContain("__('app.menu.sales')")
        ->and($contents)->toContain('lead-contact.index');
});
