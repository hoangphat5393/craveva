<?php

declare(strict_types=1);

it('exposes split people sidebar labels in english', function (): void {
    app()->setLocale('en');

    expect(__('app.menu.humanResources'))->toBe('Human Resources')
        ->and(__('app.menu.payrollSidebar'))->toBe('Payroll');
});

it('exposes split people sidebar labels in vietnamese', function (): void {
    app()->setLocale('vi');

    expect(__('app.menu.humanResources'))->toBe('Quản lý nhân sự')
        ->and(__('app.menu.payrollSidebar'))->toBe('Bảng lương');
});

it('people sidebar accordions partial uses split l1 menu keys', function (): void {
    $path = resource_path('views/sections/partials/people-sidebar-accordions.blade.php');

    $contents = file_get_contents($path);

    expect($contents)->toContain("__('app.menu.humanResources')")
        ->and($contents)->toContain("__('app.menu.payrollSidebar')")
        ->and($contents)->not->toContain('x-sidebar-menu-group')
        ->and($contents)->not->toContain("__('app.menu.people')");
});

it('main menu includes people sidebar accordions partial', function (): void {
    $path = resource_path('views/sections/menu.blade.php');

    $contents = file_get_contents($path);

    expect($contents)->toContain("@include('sections.partials.people-sidebar-accordions')")
        ->and($contents)->not->toContain('people-sidebar-menu-items');
});

it('human resources partial includes performance items at end', function (): void {
    $path = resource_path('views/sections/partials/human-resources-sidebar-menu-items.blade.php');

    $contents = file_get_contents($path);

    expect($contents)->toContain('performance-dashboard.index')
        ->and($contents)->toContain('showPeoplePerformanceGroup');
});
