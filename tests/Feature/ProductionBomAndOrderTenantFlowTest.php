<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Production\Entities\ProductionBom;
use Modules\Production\Entities\ProductionOrder;

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
        ->assertSee(__('modules.invoices.unitType'), false);

    $this->actingAs($fix['userAuth'], 'web')
        ->withSession($session)
        ->get(route('production.orders.show', $order))
        ->assertSuccessful()
        ->assertSee(__('modules.invoices.unitType'), false);
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
        ->assertSee(__('modules.invoices.unitType'), false);
});
