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
        ->get(route('production.boms.index'));

    $response->assertSuccessful();
    $response->assertSee(route('production.boms.destroy', $bom), false);
    $response->assertSee(__('app.delete'), false);
    $response->assertSee('fa-trash', false);
});
