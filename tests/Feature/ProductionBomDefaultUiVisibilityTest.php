<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

it('hides default-for-manufactured-product checkbox on bom form when ui flag is off', function (): void {
    config(['production.ui.show_bom_default_for_manufactured_product_ui' => false]);

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
        ->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Accept' => 'application/json',
        ])
        ->get(route('production.boms.create'));

    $response->assertSuccessful();
    $html = (string) $response->json('html');

    expect($html)->toContain('name="is_default" value="0"')
        ->and($html)->not->toContain(__('production::app.bomDefaultForManufacturedProduct'));
});
