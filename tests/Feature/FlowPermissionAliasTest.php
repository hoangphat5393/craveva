<?php

use Modules\Purchase\Support\FlowPermission;

if (! class_exists('FlowPermissionFakeUser')) {
    class FlowPermissionFakeUser
    {
        public function __construct(private array $permissions) {}

        public function permission($name)
        {
            return $this->permissions[$name] ?? 'none';
        }
    }
}

beforeEach(function () {
    config()->set('purchase.permission_aliases', [
        'sales_do' => [
            'view' => ['new' => 'view_sales_do', 'legacy' => 'view_sales_shipment'],
        ],
        'grn' => [
            'view' => ['new' => 'view_grn', 'legacy' => 'view_purchase_order'],
        ],
    ]);
    session()->start();
});

afterEach(function () {
    session()->forget('user');
});

it('allows alias by legacy permission before cutover', function () {
    config()->set('purchase.do_grn_cutover_enabled', false);

    session(['user' => new FlowPermissionFakeUser([
        'view_sales_do' => 'none',
        'view_sales_shipment' => 'all',
    ])]);

    expect(FlowPermission::allowsAlias('sales_do.view'))->toBeTrue();
});

it('requires new permission after cutover', function () {
    config()->set('purchase.do_grn_cutover_enabled', true);

    session(['user' => new FlowPermissionFakeUser([
        'view_sales_do' => 'none',
        'view_sales_shipment' => 'all',
    ])]);

    expect(FlowPermission::allowsAlias('sales_do.view'))->toBeFalse();
});

it('accepts new permission in both modes', function () {
    session(['user' => new FlowPermissionFakeUser([
        'view_grn' => 'all',
    ])]);

    config()->set('purchase.do_grn_cutover_enabled', false);
    expect(FlowPermission::allowsAlias('grn.view'))->toBeTrue();

    config()->set('purchase.do_grn_cutover_enabled', true);
    expect(FlowPermission::allowsAlias('grn.view'))->toBeTrue();
});

it('denies when alias key is missing', function () {
    config()->set('purchase.do_grn_cutover_enabled', false);
    session(['user' => new FlowPermissionFakeUser([
        'anything' => 'all',
    ])]);

    expect(FlowPermission::allowsAlias('unknown.alias'))->toBeFalse();
});
