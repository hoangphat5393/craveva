<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

it('embeds unit cost map and native UOM selects on BOM create form', function (): void {
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
        ->get(route('production.boms.create'));

    $response->assertSuccessful();
    $content = $response->getContent();
    expect($content)->toContain('bomUnitCostByProductAndUnit');
    expect($content)->toContain('setRowUnitId');
    expect($content)->toContain('manufacturedProduct');
    expect($content)->toContain('bom-line-unit-select');
    expect($content)->not->toMatch('/bom-line-unit-select[^>]*select-picker/');
    expect($content)->toMatch('/class="form-control height-35 f-14 w-100 bom-line-unit-select"/');
});
