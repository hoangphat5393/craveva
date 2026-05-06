<?php

declare(strict_types=1);

use App\Models\Company;
use App\Models\ModuleSetting;
use App\Models\Permission;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use App\Models\UserAuth;
use App\Models\UserPermission;
use App\Scopes\CompanyScope;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Production\Entities\ProductionBom;
use Modules\Production\Entities\ProductionOrder;
use Modules\Warehouse\Entities\Warehouse;

uses(DatabaseTransactions::class);

/**
 * @return array{company: Company, userAuth: UserAuth, user: User, fg: Product, rm: Product, rmWarehouse: Warehouse, fgWarehouse: Warehouse}|null
 */
function productionTenantFlowFixtures(): ?array
{
    if (! Schema::hasTable('production_orders') || ! Schema::hasTable('production_boms')) {
        test()->markTestSkipped('Production tables are not migrated.');

        return null;
    }

    $company = Company::withoutGlobalScopes()->where('status', 'active')->orderBy('id')->first();

    if ($company === null) {
        test()->markTestSkipped('No active company row in DB for tenant flow test.');

        return null;
    }

    $companyId = (int) $company->id;

    $goods = Product::withoutGlobalScopes()
        ->where('company_id', $companyId)
        ->where('type', 'goods')
        ->orderBy('id')
        ->limit(5)
        ->get(['id', 'name']);

    if ($goods->count() < 2) {
        test()->markTestSkipped('Need at least two goods products in company for BOM + FG/RM fixtures.');

        return null;
    }

    [$fg, $rm] = [$goods->get(0), $goods->get(1)];
    if ((int) $fg->id === (int) $rm->id) {
        test()->markTestSkipped('Distinct FG and RM product rows required.');

        return null;
    }

    /** @var User|null $user */
    $user = User::withoutGlobalScopes()
        ->where('company_id', $companyId)
        ->where('status', 'active')
        ->whereNull('is_client_contact')
        ->whereNotNull('user_auth_id')
        ->orderBy('id')
        ->first();

    if ($user === null) {
        test()->markTestSkipped('No active employee user linked to company for tenant flow test.');

        return null;
    }

    /** @var UserAuth|null $userAuth */
    $userAuth = UserAuth::query()->find($user->user_auth_id ?? 0);
    if ($userAuth === null) {
        test()->markTestSkipped('Missing UserAuth for selected user.');

        return null;
    }

    if ($userAuth->email_verified_at === null) {
        $userAuth->forceFill(['email_verified_at' => now()]);
        $userAuth->save();
    }

    $rmWarehouse = Warehouse::withoutGlobalScopes()
        ->where('company_id', $companyId)
        ->where('status', 'active')
        ->orderBy('id')
        ->first();

    $fgWarehouse = Warehouse::withoutGlobalScopes()
        ->where('company_id', $companyId)
        ->where('status', 'active')
        ->orderByDesc('id')
        ->first();

    if ($rmWarehouse === null || $fgWarehouse === null) {
        test()->markTestSkipped('Need at least one active warehouse in company.');

        return null;
    }

    ModuleSetting::withoutGlobalScope(CompanyScope::class)->updateOrCreate(
        [
            'company_id' => $companyId,
            'module_name' => 'production',
            'type' => 'admin',
        ],
        [
            'is_allowed' => 1,
            'status' => 'active',
        ],
    );

    $permissionNames = [
        'view_production_orders',
        'add_production_orders',
        'edit_production_orders',
    ];

    $typeAllId = DB::table('permission_types')->where('name', 'all')->value('id');
    if ($typeAllId === null) {
        test()->markTestSkipped('permission_types row for "all" not found.');

        return null;
    }

    foreach ($permissionNames as $permissionName) {
        $permissionId = Permission::query()->where('name', $permissionName)->value('id');
        if ($permissionId === null) {
            test()->markTestSkipped("Production permission {$permissionName} not seeded.");

            return null;
        }

        UserPermission::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'permission_id' => $permissionId,
            ],
            [
                'permission_type_id' => (int) $typeAllId,
            ],
        );
    }

    $adminRoleId = Role::withoutGlobalScope(CompanyScope::class)
        ->where('company_id', $companyId)
        ->where('name', 'admin')
        ->value('id');

    if ($adminRoleId !== null) {
        DB::table('role_user')->updateOrInsert(
            [
                'user_id' => $user->id,
                'role_id' => (int) $adminRoleId,
            ],
            [
                'role_id' => (int) $adminRoleId,
                'user_id' => $user->id,
            ],
        );
    }

    foreach ($permissionNames as $permissionName) {
        Cache::forget('permission-' . $permissionName . '-' . $user->id);
    }

    Cache::forget('user_modules_' . $user->id);

    return [
        'company' => $company,
        'userAuth' => $userAuth,
        'user' => $user,
        'fg' => $fg,
        'rm' => $rm,
        'rmWarehouse' => $rmWarehouse,
        'fgWarehouse' => $fgWarehouse,
    ];
}

it('creates BOM and draft production order over HTTP like a signed-in tenant browser', function (): void {
    $fix = productionTenantFlowFixtures();
    if ($fix === null) {
        return;
    }

    $version = 't-http-' . uniqid('', true);

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
        'output_product_id' => (int) $fix['fg']->id,
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
        ->where('output_product_id', (int) $fix['fg']->id)
        ->where('planned_quantity', 100)
        ->orderByDesc('id')
        ->first();

    expect($order)->not->toBeNull();
    expect($order->status)->toBe(ProductionOrder::STATUS_DRAFT);
});
