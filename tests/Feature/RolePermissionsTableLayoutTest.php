<?php

it('company role permissions ajax view uses full width permission table', function (): void {
    $path = resource_path('views/role-permissions/ajax/permissions.blade.php');

    expect(file_exists($path))->toBeTrue()
        ->and(file_get_contents($path))->toContain('permisison-table')
        ->and(file_get_contents($path))->toContain('w-100');
});

it('super admin role permissions ajax view uses full width permission table', function (): void {
    $path = resource_path('views/super-admin/role-permissions/ajax/permissions.blade.php');

    expect(file_exists($path))->toBeTrue()
        ->and(file_get_contents($path))->toContain('permisison-table')
        ->and(file_get_contents($path))->toContain('w-100');
});
