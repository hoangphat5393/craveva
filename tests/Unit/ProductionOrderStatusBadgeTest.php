<?php

use Modules\Production\Entities\ProductionOrder;
use Modules\Production\Support\ProductionOrderStatusBadge;

it('maps production order status to bootstrap badge variants', function (string $status, string $variant) {
    expect(ProductionOrderStatusBadge::variant($status))->toBe($variant);
})->with([
    [ProductionOrder::STATUS_DRAFT, 'secondary'],
    [ProductionOrder::STATUS_RELEASED, 'info'],
    [ProductionOrder::STATUS_IN_PROGRESS, 'warning'],
    [ProductionOrder::STATUS_COMPLETED, 'success'],
    [ProductionOrder::STATUS_CANCELLED, 'danger'],
]);

it('renders status badge html with escaped label', function () {
    $html = ProductionOrderStatusBadge::html(ProductionOrder::STATUS_COMPLETED);

    expect($html)->toContain('badge badge-success')
        ->and($html)->toContain('<span');
});
