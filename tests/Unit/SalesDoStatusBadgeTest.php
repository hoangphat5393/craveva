<?php

declare(strict_types=1);

use Modules\Purchase\Support\SalesDoStatusBadge;

it('maps sales do statuses to bootstrap badge classes', function (): void {
    expect(SalesDoStatusBadge::badgeClass('draft'))->toBe('badge-warning')
        ->and(SalesDoStatusBadge::badgeClass('confirmed'))->toBe('badge-info')
        ->and(SalesDoStatusBadge::badgeClass('shipped'))->toBe('badge-success')
        ->and(SalesDoStatusBadge::badgeClass('delivered'))->toBe('badge-success')
        ->and(SalesDoStatusBadge::badgeClass('cancelled'))->toBe('badge-danger');
});

it('renders sales do status as badge html without circle icon', function (): void {
    app()->setLocale('en');

    $html = SalesDoStatusBadge::html('confirmed');

    expect($html)->toContain('badge badge-info')
        ->and($html)->toContain('Confirmed')
        ->and($html)->not->toContain('fa-circle');
});
