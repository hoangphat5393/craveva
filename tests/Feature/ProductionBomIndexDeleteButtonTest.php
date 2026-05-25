<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Production\Entities\ProductionBom;

uses(DatabaseTransactions::class);

it('shows delete control on BOM index for BOMs not linked to production orders', function (): void {
    $fix = productionTenantFlowFixtures();
    if ($fix === null) {
        return;
    }

    $bom = ProductionBom::query()
        ->where('company_id', (int) $fix['company']->id)
        ->doesntHave('productionOrders')
        ->orderByDesc('id')
        ->first();

    if ($bom === null) {
        test()->markTestSkipped('No deletable BOM in company for index delete button test.');

        return;
    }

    $response = $this->actingAs($fix['userAuth'], 'web')
        ->withSession([
            'company' => $fix['company'],
            'multi_company_selected' => 1,
            'user_company_count' => 1,
        ])
        ->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Accept' => 'application/json',
        ])
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
            'searchText' => (string) ($bom->code ?: $bom->version ?: $bom->id),
        ])));

    $response->assertSuccessful();
    expect(json_encode($response->json('data')))->toContain(route('production.boms.destroy', $bom));
    expect(json_encode($response->json('data')))->toContain(__('app.delete'));
    expect(json_encode($response->json('data')))->toContain('fa fa-trash');
});
