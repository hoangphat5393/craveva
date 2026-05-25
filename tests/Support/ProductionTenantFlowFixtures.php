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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Warehouse\Entities\Warehouse;

/**
 * Shared fixtures for Production tenant HTTP / permission tests (real DB + DatabaseTransactions).
 *
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

    $fg = Product::withoutGlobalScopes()
        ->where('company_id', $companyId)
        ->forBomOutput()
        ->orderBy('id')
        ->first(['id', 'name']);

    $rm = Product::withoutGlobalScopes()
        ->where('company_id', $companyId)
        ->forBomComponents()
        ->orderBy('id')
        ->first(['id', 'name']);

    if ($fg === null || $rm === null) {
        test()->markTestSkipped('Need at least one finished goods (type=goods) and one BOM component product (raw/semi/packaging) in company.');

        return null;
    }

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

/**
 * @param  array<int, array{data: string, name?: string, searchable?: bool, orderable?: bool}>  $columns
 * @param  array<string, mixed>  $extra
 * @return array<string, mixed>
 */
function productionDatatableRequest(array $columns, array $extra = [], int $orderColumn = 0, string $orderDir = 'desc'): array
{
    return array_merge([
        'draw' => 1,
        'start' => 0,
        'length' => 10,
        'columns' => array_map(static function (array $column): array {
            return [
                'data' => $column['data'],
                'name' => $column['name'] ?? '',
                'searchable' => ($column['searchable'] ?? true) ? 'true' : 'false',
                'orderable' => ($column['orderable'] ?? true) ? 'true' : 'false',
                'search' => ['value' => '', 'regex' => 'false'],
            ];
        }, $columns),
        'order' => [
            ['column' => $orderColumn, 'dir' => $orderDir],
        ],
        'search' => ['value' => '', 'regex' => 'false'],
    ], $extra);
}
