<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Production\Entities\ProductionBom;
use Modules\Production\Entities\ProductionBomItem;
use Modules\Production\Entities\ProductionOrder;
use Modules\Warehouse\Entities\Warehouse;
use Modules\Warehouse\Entities\WarehouseProductStock;

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
    expect($ordersContent)->toContain(__('production::app.materialShortageSummary'));
    expect($ordersContent)->toContain(route('production.material-shortages.index'));
    expect($ordersContent)->toContain(__('production::app.status'));
    expect($ordersContent)->toContain(__('production::app.materialAvailabilityShortColumn'));
    expect($ordersContent)->toContain('openRightModal');
    expect($ordersContent)->toContain('redirect_url=');
    expect($ordersContent)->not->toContain('production-list-footer');
    expect(strpos($ordersContent, __('production::app.newOrder')))->toBeLessThan(strpos($ordersContent, __('production::app.materialShortageSummary')));

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
    expect($bomsContent)->toContain('openRightModal');
    expect($bomsContent)->toContain('redirect_url=');
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
            ['data' => 'planned_quantity', 'name' => 'production_orders.planned_quantity'],
            ['data' => 'fg_unit_type', 'name' => 'output_unit_types.unit_type'],
            ['data' => 'bom_label', 'name' => 'boms.code', 'searchable' => false, 'orderable' => false],
            ['data' => 'material_availability', 'searchable' => false, 'orderable' => false],
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
            ['data' => 'action', 'searchable' => false, 'orderable' => false],
        ], [
            'unit_type_id' => 'all',
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

it('shows sufficient and insufficient material badges in the separate production order stock status column', function (): void {
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

    $shortfallWarehouse = $fix['fgWarehouse'];

    if ((int) $shortfallWarehouse->id === (int) $fix['rmWarehouse']->id) {
        $shortfallWarehouse = Warehouse::query()->create([
            'company_id' => (int) $fix['company']->id,
            'name' => 'RM shortfall test warehouse',
            'code' => 'RM-SHORT-'.uniqid(),
            'warehouse_type' => 'normal',
            'status' => 'active',
        ]);
    }

    $bom = ProductionBom::query()->create([
        'company_id' => (int) $fix['company']->id,
        'output_product_id' => (int) $fix['fg']->id,
        'version' => 'rm-flag-'.uniqid(),
        'code' => 'rm-flag-code',
        'is_default' => false,
        'created_by' => $fix['user']->id,
        'updated_by' => $fix['user']->id,
    ]);

    ProductionBomItem::query()->create([
        'company_id' => (int) $fix['company']->id,
        'production_bom_id' => $bom->id,
        'component_product_id' => (int) $fix['rm']->id,
        'quantity' => 2,
        'sort_order' => 0,
    ]);

    WarehouseProductStock::query()->updateOrCreate(
        [
            'warehouse_id' => (int) $fix['rmWarehouse']->id,
            'product_id' => (int) $fix['rm']->id,
        ],
        [
            'quantity' => 1000,
        ],
    );

    $enoughOrder = ProductionOrder::query()->create([
        'company_id' => (int) $fix['company']->id,
        'output_product_id' => (int) $fix['fg']->id,
        'production_bom_id' => $bom->id,
        'rm_warehouse_id' => (int) $fix['rmWarehouse']->id,
        'fg_warehouse_id' => (int) $fix['fgWarehouse']->id,
        'planned_quantity' => 10,
        'status' => ProductionOrder::STATUS_DRAFT,
    ]);

    $shortfallOrder = ProductionOrder::query()->create([
        'company_id' => (int) $fix['company']->id,
        'output_product_id' => (int) $fix['fg']->id,
        'production_bom_id' => $bom->id,
        'rm_warehouse_id' => (int) $shortfallWarehouse->id,
        'fg_warehouse_id' => (int) $fix['fgWarehouse']->id,
        'planned_quantity' => 10,
        'status' => ProductionOrder::STATUS_DRAFT,
    ]);

    $datatableResponse = $this->actingAs($fix['userAuth'], 'web')
        ->withSession($session)
        ->withHeaders($headers)
        ->get(route('production.orders.index', productionDatatableRequest([
            ['data' => 'id', 'name' => 'production_orders.id'],
            ['data' => 'output_product_name', 'name' => 'output_products.name'],
            ['data' => 'planned_quantity', 'name' => 'production_orders.planned_quantity'],
            ['data' => 'fg_unit_type', 'name' => 'output_unit_types.unit_type'],
            ['data' => 'bom_label', 'name' => 'boms.code', 'searchable' => false, 'orderable' => false],
            ['data' => 'material_availability', 'searchable' => false, 'orderable' => false],
            ['data' => 'status', 'name' => 'production_orders.status'],
            ['data' => 'action', 'searchable' => false, 'orderable' => false],
        ], [
            'status' => 'all',
            'searchText' => 'rm-flag-code',
        ])));

    $datatableResponse->assertSuccessful();

    $rows = collect($datatableResponse->json('data'))->keyBy('id');
    $enoughStatusHtml = (string) data_get($rows->get($enoughOrder->id), 'status', '');
    $shortfallStatusHtml = (string) data_get($rows->get($shortfallOrder->id), 'status', '');
    $enoughMaterialAvailabilityHtml = (string) data_get($rows->get($enoughOrder->id), 'material_availability', '');
    $shortfallMaterialAvailabilityHtml = (string) data_get($rows->get($shortfallOrder->id), 'material_availability', '');

    expect($enoughStatusHtml)->not->toContain(__('production::app.materialAvailabilityLabels.sufficient'));
    expect($shortfallStatusHtml)->not->toContain(__('production::app.materialAvailabilityLabels.insufficient'));
    expect($enoughMaterialAvailabilityHtml)->toContain(__('production::app.materialAvailabilityLabels.sufficient'));
    expect($enoughMaterialAvailabilityHtml)->toContain('badge-success');
    expect($enoughMaterialAvailabilityHtml)->toContain(__('production::app.materialAvailabilityLabels.sufficient'));
    expect($shortfallMaterialAvailabilityHtml)->toContain(__('production::app.materialAvailabilityLabels.insufficient'));
    expect($shortfallMaterialAvailabilityHtml)->toContain('badge-danger');
    expect($shortfallMaterialAvailabilityHtml)->toContain(__('production::app.materialAvailabilityLabels.insufficient'));
});

it('returns ajax modal payloads for production order and bom create and edit screens', function (): void {
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

    $bom = ProductionBom::query()->create([
        'company_id' => (int) $fix['company']->id,
        'output_product_id' => (int) $fix['fg']->id,
        'version' => 'modal-test-'.uniqid(),
        'code' => 'modal-bom',
        'is_default' => false,
        'created_by' => $fix['user']->id,
        'updated_by' => $fix['user']->id,
    ]);

    $editableBom = ProductionBom::query()->create([
        'company_id' => (int) $fix['company']->id,
        'output_product_id' => (int) $fix['fg']->id,
        'version' => 'editable-modal-test-'.uniqid(),
        'code' => 'editable-modal-bom',
        'is_default' => false,
        'created_by' => $fix['user']->id,
        'updated_by' => $fix['user']->id,
    ]);

    ProductionBomItem::query()->create([
        'company_id' => (int) $fix['company']->id,
        'production_bom_id' => $bom->id,
        'component_product_id' => (int) $fix['rm']->id,
        'quantity' => 1,
        'sort_order' => 0,
    ]);

    ProductionBomItem::query()->create([
        'company_id' => (int) $fix['company']->id,
        'production_bom_id' => $editableBom->id,
        'component_product_id' => (int) $fix['rm']->id,
        'quantity' => 1,
        'sort_order' => 0,
    ]);

    $order = ProductionOrder::query()->create([
        'company_id' => (int) $fix['company']->id,
        'output_product_id' => (int) $fix['fg']->id,
        'production_bom_id' => $bom->id,
        'rm_warehouse_id' => (int) $fix['rmWarehouse']->id,
        'fg_warehouse_id' => (int) $fix['fgWarehouse']->id,
        'planned_quantity' => 10,
        'status' => ProductionOrder::STATUS_DRAFT,
    ]);

    $orderCreateResponse = $this->actingAs($fix['userAuth'], 'web')
        ->withSession($session)
        ->withHeaders($headers)
        ->get(route('production.orders.create', ['redirect_url' => route('production.orders.index')]));

    $orderCreateResponse->assertSuccessful();
    expect((string) $orderCreateResponse->json('html'))->toContain('save-production-order-form');
    expect((string) $orderCreateResponse->json('html'))->toContain('save-production-order-button');

    $orderEditResponse = $this->actingAs($fix['userAuth'], 'web')
        ->withSession($session)
        ->withHeaders($headers)
        ->get(route('production.orders.edit', [$order, 'redirect_url' => route('production.orders.index')]));

    $orderEditResponse->assertSuccessful();
    expect((string) $orderEditResponse->json('html'))->toContain('update-production-order-form');
    expect((string) $orderEditResponse->json('html'))->toContain('update-production-order-button');

    $bomCreateResponse = $this->actingAs($fix['userAuth'], 'web')
        ->withSession($session)
        ->withHeaders($headers)
        ->get(route('production.boms.create', ['redirect_url' => route('production.boms.index')]));

    $bomCreateResponse->assertSuccessful();
    expect((string) $bomCreateResponse->json('html'))->toContain('save-production-bom-form');
    expect((string) $bomCreateResponse->json('html'))->toContain('save-production-bom-button');

    $doubleEncodedRedirect = urlencode(urlencode(route('production.boms.index')));
    $bomCreateModalResponse = $this->actingAs($fix['userAuth'], 'web')
        ->withSession($session)
        ->withHeaders($headers)
        ->get(route('production.boms.create', ['redirectUrl' => $doubleEncodedRedirect]));

    $bomCreateModalResponse->assertSuccessful();
    $bomCreateHtml = (string) $bomCreateModalResponse->json('html');
    expect($bomCreateHtml)->toContain(route('production.boms.index'));
    expect($bomCreateHtml)->not->toContain('%3A%2F%2F');

    $bomEditResponse = $this->actingAs($fix['userAuth'], 'web')
        ->withSession($session)
        ->withHeaders($headers)
        ->get(route('production.boms.edit', [$editableBom, 'redirect_url' => route('production.boms.index')]));

    $bomEditResponse->assertSuccessful();
    expect((string) $bomEditResponse->json('html'))->toContain('update-production-bom-form');
    expect((string) $bomEditResponse->json('html'))->toContain('update-production-bom-button');
});
