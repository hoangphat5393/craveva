<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Production\Entities\ProductionBom;
use Modules\Production\Entities\ProductionOrder;
use Modules\Production\Services\ProductionPostingService;
use Modules\Warehouse\Entities\WarehouseProductBatch;
use Modules\Warehouse\Entities\WarehouseProductStock;

uses(DatabaseTransactions::class);

it('creates BOM and draft production order over HTTP like a signed-in tenant browser', function (): void {
    $fix = productionTenantFlowFixtures();
    if ($fix === null) {
        return;
    }

    $version = 't-http-'.uniqid('', true);

    $this->actingAs($fix['userAuth'], 'web')
        ->withSession([
            'company' => $fix['company'],
            'multi_company_selected' => 1,
            'user_company_count' => 1,
        ])
        ->get(route('production.boms.create'))
        ->assertSuccessful();

    $bomResponse = $this->post(route('production.boms.store'), [
        '_token' => csrf_token(),
        'output_product_id' => (int) $fix['fg']->id,
        'version' => $version,
        'code' => 'http-test',
        'effective_from' => null,
        'effective_to' => null,
        'is_default' => '1',
        'notes' => null,
        'items' => [
            [
                'component_product_id' => (int) $fix['rm']->id,
                'quantity' => 0.5,
            ],
        ],
    ]);

    $bomResponse->assertRedirect();
    $bom = ProductionBom::query()
        ->where('company_id', (int) $fix['company']->id)
        ->where('version', $version)
        ->first();

    expect($bom)->not->toBeNull();

    $this->get(route('production.orders.create'))->assertSuccessful();

    $orderResponse = $this->post(route('production.orders.store'), [
        '_token' => csrf_token(),
        // UI locks FG to BOM output; backend also normalizes when a BOM is selected.
        'output_product_id' => (int) $fix['rm']->id,
        'production_bom_id' => (int) $bom->id,
        'rm_warehouse_id' => (int) $fix['rmWarehouse']->id,
        'fg_warehouse_id' => (int) $fix['fgWarehouse']->id,
        'planned_quantity' => 100,
        'sales_order_id' => null,
        'project_id' => null,
    ]);

    $orderResponse->assertRedirect();

    /** @var ProductionOrder|null $order */
    $order = ProductionOrder::query()
        ->where('company_id', (int) $fix['company']->id)
        ->where('planned_quantity', 100)
        ->orderByDesc('id')
        ->first();

    expect($order)->not->toBeNull();
    expect((int) $order->output_product_id)->toBe((int) $fix['fg']->id);
    expect($order->status)->toBe(ProductionOrder::STATUS_DRAFT);

    $session = [
        'company' => $fix['company'],
        'multi_company_selected' => 1,
        'user_company_count' => 1,
    ];

    $this->actingAs($fix['userAuth'], 'web')
        ->withSession($session)
        ->get(route('production.orders.index'))
        ->assertSuccessful()
        ->assertSee(__('modules.invoices.unitType'), false)
        ->assertSee(__('production::app.materialAvailabilityShortColumn'), false)
        ->assertSee('production-orders-table', false);

    $datatableResponse = $this->actingAs($fix['userAuth'], 'web')
        ->withSession($session)
        ->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Accept' => 'application/json',
        ])
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
            'searchText' => 'http-test',
        ])));

    $datatableResponse->assertSuccessful();
    $matchingRow = collect($datatableResponse->json('data'))
        ->first(fn (array $row): bool => str_contains((string) ($row['bom_label'] ?? ''), 'http-test'));

    expect($matchingRow)->not->toBeNull();
    expect((string) ($matchingRow['bom_label'] ?? ''))->toContain('http-test');
    expect((string) ($matchingRow['bom_label'] ?? ''))->toContain($version);

    $this->actingAs($fix['userAuth'], 'web')
        ->withSession($session)
        ->get(route('production.orders.show', $order))
        ->assertSuccessful()
        ->assertSee(__('production::app.materialShortage'), false)
        ->assertSee(__('modules.invoices.unitType'), false);
});

it('rejects a BOM line when the component matches the manufactured product', function (): void {
    $fix = productionTenantFlowFixtures();
    if ($fix === null) {
        return;
    }

    $response = $this->actingAs($fix['userAuth'], 'web')
        ->withSession([
            'company' => $fix['company'],
            'multi_company_selected' => 1,
            'user_company_count' => 1,
        ])
        ->from(route('production.boms.create'))
        ->post(route('production.boms.store'), [
            '_token' => csrf_token(),
            'output_product_id' => (int) $fix['fg']->id,
            'version' => 'fg-as-rm-'.uniqid('', true),
            'code' => 'fg-as-rm',
            'effective_from' => null,
            'effective_to' => null,
            'is_default' => '0',
            'notes' => null,
            'items' => [
                [
                    'component_product_id' => (int) $fix['fg']->id,
                    'quantity' => 1,
                ],
            ],
        ]);

    $response->assertRedirect(route('production.boms.create'));
    $response->assertSessionHasErrors('items.0.component_product_id');
});

it('shows reserved raw material quantity on the production order detail material table', function (): void {
    $fix = productionTenantFlowFixtures();
    if ($fix === null) {
        return;
    }

    $session = [
        'company' => $fix['company'],
        'multi_company_selected' => 1,
        'user_company_count' => 1,
    ];

    WarehouseProductStock::query()->updateOrCreate(
        [
            'warehouse_id' => (int) $fix['rmWarehouse']->id,
            'product_id' => (int) $fix['rm']->id,
        ],
        [
            'quantity' => 10000,
        ],
    );

    WarehouseProductBatch::query()->create([
        'company_id' => (int) $fix['company']->id,
        'warehouse_id' => (int) $fix['rmWarehouse']->id,
        'product_id' => (int) $fix['rm']->id,
        'batch_number' => 'RM-RES-UI-'.uniqid(),
        'quantity' => 10000,
        'reserved_quantity' => 0,
    ]);

    $bom = ProductionBom::query()->create([
        'company_id' => (int) $fix['company']->id,
        'output_product_id' => (int) $fix['fg']->id,
        'version' => 'reserved-ui-'.uniqid('', true),
        'code' => 'reserved-ui',
        'is_default' => false,
        'created_by' => $fix['user']->id,
        'updated_by' => $fix['user']->id,
    ]);

    $bom->items()->create([
        'company_id' => (int) $fix['company']->id,
        'component_product_id' => (int) $fix['rm']->id,
        'quantity' => 1,
        'sort_order' => 0,
    ]);

    $order = ProductionOrder::query()->create([
        'company_id' => (int) $fix['company']->id,
        'status' => ProductionOrder::STATUS_DRAFT,
        'output_product_id' => (int) $fix['fg']->id,
        'production_bom_id' => (int) $bom->id,
        'rm_warehouse_id' => (int) $fix['rmWarehouse']->id,
        'fg_warehouse_id' => (int) $fix['fgWarehouse']->id,
        'planned_quantity' => 10,
    ]);

    app(ProductionPostingService::class)->releaseOrder($order);

    $this->actingAs($fix['userAuth'], 'web')
        ->withSession($session)
        ->get(route('production.orders.show', $order))
        ->assertSuccessful()
        ->assertSee(__('production::app.materialReservedInRawMaterialWarehouse'), false)
        ->assertSee('10', false)
        ->assertSee(__('production::app.materialAvailableInRawMaterialWarehouse'), false);
});

it('lists BOM index with unit type column for finished good', function (): void {
    $fix = productionTenantFlowFixtures();
    if ($fix === null) {
        return;
    }

    $this->actingAs($fix['userAuth'], 'web')
        ->withSession([
            'company' => $fix['company'],
            'multi_company_selected' => 1,
            'user_company_count' => 1,
        ])
        ->get(route('production.boms.index'))
        ->assertSuccessful()
        ->assertSee(__('modules.invoices.unitType'), false)
        ->assertSee('production-boms-table', false);
});
