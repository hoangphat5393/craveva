<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

it('renders production order and bom indexes with the shared datatable mechanism', function (): void {
    $fix = productionTenantFlowFixtures();
    if ($fix === null) {
        return;
    }

    $session = [
        'company' => $fix['company'],
        'multi_company_selected' => 1,
        'user_company_count' => 1,
    ];

    $ordersContent = $this->actingAs($fix['userAuth'], 'web')
        ->withSession($session)
        ->get(route('production.orders.index'))
        ->assertSuccessful()
        ->getContent();

    expect($ordersContent)->toContain('window.LaravelDataTables["production-orders-table"]');
    expect($ordersContent)->toContain("$('#production-orders-table').on('preXhr.dt'");
    expect($ordersContent)->toContain('id="production-orders-table"');
    expect($ordersContent)->toContain(__('production::app.newOrder'));
    expect($ordersContent)->toContain(__('production::app.status'));
    expect($ordersContent)->not->toContain('production-list-footer');

    $bomsContent = $this->actingAs($fix['userAuth'], 'web')
        ->withSession($session)
        ->get(route('production.boms.index'))
        ->assertSuccessful()
        ->getContent();

    expect($bomsContent)->toContain('window.LaravelDataTables["production-boms-table"]');
    expect($bomsContent)->toContain("$('#production-boms-table').on('preXhr.dt'");
    expect($bomsContent)->toContain('id="production-boms-table"');
    expect($bomsContent)->toContain(__('production::app.newBom'));
    expect($bomsContent)->toContain(__('modules.invoices.unitType'));
});

it('returns datatable json for production order and bom ajax requests', function (): void {
    $fix = productionTenantFlowFixtures();
    if ($fix === null) {
        return;
    }

    $session = [
        'company' => $fix['company'],
        'multi_company_selected' => 1,
        'user_company_count' => 1,
    ];

    $headers = [
        'X-Requested-With' => 'XMLHttpRequest',
        'Accept' => 'application/json',
    ];

    $ordersResponse = $this->actingAs($fix['userAuth'], 'web')
        ->withSession($session)
        ->withHeaders($headers)
        ->get(route('production.orders.index', productionDatatableRequest([
            ['data' => 'id', 'name' => 'production_orders.id'],
            ['data' => 'output_product_name', 'name' => 'output_products.name'],
            ['data' => 'fg_unit_type', 'name' => 'output_unit_types.unit_type'],
            ['data' => 'bom_label', 'name' => 'boms.code', 'searchable' => false, 'orderable' => false],
            ['data' => 'planned_quantity', 'name' => 'production_orders.planned_quantity'],
            ['data' => 'status', 'name' => 'production_orders.status'],
            ['data' => 'action', 'searchable' => false, 'orderable' => false],
        ], [
            'status' => 'all',
            'searchText' => '',
        ])));

    $ordersResponse->assertSuccessful();
    $ordersResponse->assertJsonStructure([
        'draw',
        'recordsTotal',
        'recordsFiltered',
        'data',
    ]);

    $bomsResponse = $this->actingAs($fix['userAuth'], 'web')
        ->withSession($session)
        ->withHeaders($headers)
        ->get(route('production.boms.index', productionDatatableRequest([
            ['data' => 'id', 'name' => 'production_boms.id'],
            ['data' => 'output_product_name', 'name' => 'output_products.name'],
            ['data' => 'fg_unit_type', 'name' => 'output_unit_types.unit_type'],
            ['data' => 'version', 'name' => 'production_boms.version'],
            ['data' => 'code', 'name' => 'production_boms.code'],
            ['data' => 'items_count', 'name' => 'items_count', 'searchable' => false],
            ['data' => 'is_default', 'name' => 'production_boms.is_default', 'searchable' => false],
            ['data' => 'action', 'searchable' => false, 'orderable' => false],
        ], [
            'output_product_id' => '',
            'searchText' => '',
        ])));

    $bomsResponse->assertSuccessful();
    $bomsResponse->assertJsonStructure([
        'draw',
        'recordsTotal',
        'recordsFiltered',
        'data',
    ]);
});
