<?php

declare(strict_types=1);

use App\Models\Company;
use App\Models\ModuleSetting;
use App\Models\Permission;
use App\Models\UserAuth;
use App\Models\UserPermission;
use App\Scopes\CompanyScope;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Modules\Production\Entities\ProductionBom;
use Modules\Production\Entities\ProductionBomItem;

uses(DatabaseTransactions::class);

/**
 * @return array{userAuth: UserAuth, company: Company, bom: ProductionBom}|null
 */
function estimateProductionBomLinesContext(): ?array
{
    $fix = productionTenantFlowFixtures();
    if ($fix === null) {
        return null;
    }

    $company = $fix['company'];
    $user = $fix['user'];

    foreach (['add_estimates', 'view_estimates'] as $permissionName) {
        $permissionId = Permission::query()->where('name', $permissionName)->value('id');
        $typeAllId = DB::table('permission_types')->where('name', 'all')->value('id');
        if ($permissionId === null || $typeAllId === null) {
            test()->markTestSkipped("Missing permission: {$permissionName}.");

            return null;
        }

        UserPermission::query()->updateOrCreate(
            ['user_id' => $user->id, 'permission_id' => (int) $permissionId],
            ['permission_type_id' => (int) $typeAllId],
        );
        Cache::forget('permission-' . $permissionName . '-' . $user->id);
    }

    ModuleSetting::withoutGlobalScope(CompanyScope::class)->updateOrCreate(
        [
            'company_id' => $company->id,
            'module_name' => 'estimates_phase1_review',
            'type' => 'admin',
        ],
        [
            'is_allowed' => 1,
            'status' => 'active',
        ],
    );

    Cache::flush();

    $bom = ProductionBom::query()->create([
        'company_id' => (int) $company->id,
        'output_product_id' => (int) $fix['fg']->id,
        'version' => 'estimate-bom-' . uniqid(),
        'code' => 'EST-BOM',
        'is_default' => false,
        'created_by' => $user->id,
        'updated_by' => $user->id,
    ]);

    ProductionBomItem::query()->create([
        'company_id' => (int) $company->id,
        'production_bom_id' => $bom->id,
        'component_product_id' => (int) $fix['rm']->id,
        'quantity' => 2.5,
        'sort_order' => 0,
    ]);

    return [
        'userAuth' => $fix['userAuth'],
        'company' => $company,
        'bom' => $bom,
    ];
}

it('returns json lines when loading production bom for estimate form', function (): void {
    $ctx = estimateProductionBomLinesContext();
    if ($ctx === null) {
        return;
    }

    $session = [
        'company' => $ctx['company'],
        'multi_company_selected' => 1,
        'user_company_count' => 1,
    ];

    $headers = [
        'X-Requested-With' => 'XMLHttpRequest',
        'Accept' => 'application/json',
    ];

    $response = test()->actingAs($ctx['userAuth'], 'web')
        ->withSession($session)
        ->withHeaders($headers)
        ->getJson(route('estimates.production_bom_lines', ['bom' => $ctx['bom']->id]));

    $response->assertSuccessful();
    $response->assertJsonPath('status', 'success');
    $response->assertJsonStructure([
        'status',
        'lines' => [
            [
                'product_id',
                'material_name',
                'quantity',
                'unit_cost',
            ],
        ],
    ]);

    expect((float) $response->json('lines.0.quantity'))->toBe(2.5);
});

it('includes load production bom controls on estimate create when phase1 is enabled', function (): void {
    $ctx = estimateProductionBomLinesContext();
    if ($ctx === null) {
        return;
    }

    $session = [
        'company' => $ctx['company'],
        'multi_company_selected' => 1,
        'user_company_count' => 1,
    ];

    $content = test()->actingAs($ctx['userAuth'], 'web')
        ->withSession($session)
        ->get(route('estimates.create'))
        ->assertSuccessful()
        ->getContent();

    expect($content)->toContain('estimate-copy-production-bom-btn');
    expect($content)->toContain('estimate_production_bom_id');
    expect($content)->toContain(route('estimates.production_bom_lines', ['bom' => ':id']));
});
