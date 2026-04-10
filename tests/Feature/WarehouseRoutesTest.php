<?php

it('generates warehouse routes under the account prefix', function () {
    expect(parse_url(route('warehouse.index'), PHP_URL_PATH))->toBe('/account/warehouse');
    expect(parse_url(route('warehouse.stock.index'), PHP_URL_PATH))->toBe('/account/warehouse-stock');
    expect(parse_url(route('warehouse.movements.index'), PHP_URL_PATH))->toBe('/account/warehouse-movements');
    expect(parse_url(route('warehouse.company-flow-settings.index'), PHP_URL_PATH))
        ->toBe('/account/warehouse/company-flow-settings');
});

it('redirects legacy GET /warehouse to /account/warehouse', function () {
    $this->get('/warehouse')->assertRedirect('/account/warehouse');
});

it('redirects legacy GET /warehouse/create to /account/warehouse/create', function () {
    $this->get('/warehouse/create')->assertRedirect('/account/warehouse/create');
});

it('redirects legacy warehouse-movements preserving query string', function () {
    $this->get('/warehouse-movements?warehouse_id=5')
        ->assertRedirect('/account/warehouse-movements?warehouse_id=5');
});
