<?php

declare(strict_types=1);

it('company menu blade has no disabled false conditionals', function (): void {
    $path = resource_path('views/sections/menu.blade.php');

    $contents = file_get_contents($path);

    expect($contents)->not->toMatch('/@if\s*\(\s*false\s*\)/')
        ->and($contents)->not->toContain('&& false')
        ->and($contents)->not->toContain('false &&');
});

it('purchase sidebar uses distinct procurement and production icons', function (): void {
    $path = module_path('Purchase', 'Resources/views/sections/sidebar.blade.php');

    $contents = file_get_contents($path);

    expect($contents)->toContain('icon="truck"')
        ->and($contents)->toContain('icon="box-seam"')
        ->and($contents)->not->toContain('$walletIconPath');
});

it('payroll sidebar accordion does not reuse wallet icon', function (): void {
    $path = resource_path('views/sections/partials/people-sidebar-accordions.blade.php');

    $contents = file_get_contents($path);

    expect($contents)->toContain('icon="cash-coin"')
        ->and($contents)->toContain('icon="people"')
        ->and($contents)->not->toContain('icon="wallet"');
});
