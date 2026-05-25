<?php

declare(strict_types=1);

use App\Models\Company;
use App\Models\ModuleSetting;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\UserAuth;
use App\Models\UserPermission;
use App\Scopes\CompanyScope;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

uses(DatabaseTransactions::class);

/**
 * @return array{company: Company, user: User, userAuth: UserAuth}|null
 */
function warehouseIndexUiFixtures(): ?array
{
    if (! Schema::hasTable('users') || ! Schema::hasTable('module_settings') || ! Schema::hasTable('permissions')) {
        test()->markTestSkipped('Required tables are missing for warehouse index UI test.');

        return null;
    }

    $company = Company::withoutGlobalScopes()->where('status', 'active')->orderBy('id')->first();
    if ($company === null) {
        test()->markTestSkipped('No active company.');

        return null;
    }

    $user = User::withoutGlobalScopes()
        ->where('company_id', $company->id)
        ->where('status', 'active')
        ->whereNull('is_client_contact')
        ->whereNotNull('user_auth_id')
        ->orderBy('id')
        ->first();

    if ($user === null) {
        test()->markTestSkipped('No active employee user.');

        return null;
    }

    $userAuth = UserAuth::query()->find($user->user_auth_id ?? 0);
    if ($userAuth === null) {
        test()->markTestSkipped('No UserAuth for employee.');

        return null;
    }

    if ($userAuth->email_verified_at === null) {
        $userAuth->forceFill(['email_verified_at' => now()])->save();
    }

    $typeAllId = DB::table('permission_types')->where('name', 'all')->value('id');
    if ($typeAllId === null) {
        test()->markTestSkipped('permission_types row for "all" not found.');

        return null;
    }

    $permissionNames = [
        'view_warehouses',
        'add_warehouses',
        'edit_warehouses',
        'delete_warehouses',
        'view_warehouse_stock',
        'add_warehouse_stock',
        'manage_warehouse_transfer',
    ];

    foreach ($permissionNames as $permissionName) {
        $permissionId = Permission::query()->where('name', $permissionName)->value('id');
        if ($permissionId === null) {
            test()->markTestSkipped("Warehouse permission {$permissionName} not seeded.");

            return null;
        }

        UserPermission::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'permission_id' => (int) $permissionId,
            ],
            [
                'permission_type_id' => (int) $typeAllId,
            ],
        );
    }

    ModuleSetting::withoutGlobalScope(CompanyScope::class)->updateOrCreate(
        [
            'company_id' => (int) $company->id,
            'module_name' => 'warehouse',
            'type' => 'admin',
        ],
        [
            'is_allowed' => 1,
            'status' => 'active',
        ],
    );

    $adminRoleId = Role::withoutGlobalScope(CompanyScope::class)
        ->where('company_id', (int) $company->id)
        ->where('name', 'admin')
        ->value('id');

    if ($adminRoleId !== null) {
        DB::table('role_user')->updateOrInsert(
            [
                'user_id' => $user->id,
                'role_id' => (int) $adminRoleId,
            ],
            [
                'user_id' => $user->id,
                'role_id' => (int) $adminRoleId,
            ],
        );
    }

    foreach ($permissionNames as $permissionName) {
        Cache::forget('permission-' . $permissionName . '-' . $user->id);
        Cache::forget('permission-id-' . $permissionName . '-' . $user->id);
    }

    Cache::forget('user_modules_' . $user->id);

    return ['company' => $company, 'user' => $user->fresh(), 'userAuth' => $userAuth];
}

/**
 * @param  array<int, array{data: string, name?: string, searchable?: bool, orderable?: bool}>  $columns
 * @param  array<string, mixed>  $extra
 * @return array<string, mixed>
 */
function warehouseDatatableRequest(array $columns, array $extra = [], int $orderColumn = 1, string $orderDir = 'desc'): array
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

it('renders warehouse index with the shared datatable mechanism', function (): void {
    $fix = warehouseIndexUiFixtures();
    if ($fix === null) {
        return;
    }

    $response = $this->actingAs($fix['userAuth'], 'web')
        ->withSession([
            'company' => $fix['company'],
            'multi_company_selected' => 1,
            'user_company_count' => 1,
        ])
        ->get(route('warehouse.index'));

    $response->assertSuccessful();

    $content = $response->getContent();

    expect($content)->toContain('window.LaravelDataTables["warehouse-table"]');
    expect($content)->toContain("$('#warehouse-table').on('preXhr.dt'");
    expect($content)->toContain('buttons.colVis.min.js');
    expect($content)->toContain('id="warehouse-table"');
    expect($content)->toContain(__('warehouse::app.importWarehouses'));
    expect($content)->toContain(__('warehouse::app.changeWarehouseStatus'));
    expect($content)->toContain(__('warehouse::app.statusLabel'));
    expect($content)->not->toContain('warehouse-sort-link');
    expect($content)->not->toContain('warehouse-footer');
});

it('renders warehouse ajax create form with translated status and concise type labels', function (): void {
    app()->setLocale('en');

    $content = view('warehouse::ajax.create')->render();

    expect($content)->toContain('Warehouse Status');
    expect($content)->not->toContain('warehouse::app.statusLabel');
    expect($content)->toContain('Standard');
    expect($content)->toContain('Locked');
    expect($content)->toContain('Scrap');
    expect($content)->toContain('Transit');
    expect($content)->not->toContain('Standard Warehouse');
    expect($content)->not->toContain('Locked Warehouse');
    expect($content)->not->toContain('Scrap Warehouse');
    expect($content)->not->toContain('Transit Warehouse');
});

it('returns datatable json for warehouse index ajax requests', function (): void {
    $fix = warehouseIndexUiFixtures();
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
        ->get(route('warehouse.index', [
            'draw' => 1,
            'start' => 0,
            'length' => 10,
            'columns' => [
                ['data' => 'check', 'name' => '', 'searchable' => 'false', 'orderable' => 'false', 'search' => ['value' => '', 'regex' => 'false']],
                ['data' => 'DT_RowIndex', 'name' => '', 'searchable' => 'false', 'orderable' => 'false', 'search' => ['value' => '', 'regex' => 'false']],
                ['data' => 'id', 'name' => 'warehouses.id', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false']],
                ['data' => 'name', 'name' => 'warehouses.name', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false']],
                ['data' => 'code', 'name' => 'warehouses.code', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false']],
                ['data' => 'address', 'name' => 'warehouses.address', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false']],
                ['data' => 'warehouse_type', 'name' => 'warehouses.warehouse_type', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false']],
                ['data' => 'status', 'name' => 'warehouses.status', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false']],
                ['data' => 'is_default', 'name' => 'warehouses.is_default', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false']],
                ['data' => 'action', 'name' => '', 'searchable' => 'false', 'orderable' => 'false', 'search' => ['value' => '', 'regex' => 'false']],
            ],
            'order' => [
                ['column' => 2, 'dir' => 'desc'],
            ],
            'search' => ['value' => '', 'regex' => 'false'],
            'status' => 'all',
            'searchText' => '',
        ]));

    $response->assertSuccessful();
    $response->assertJsonStructure([
        'draw',
        'recordsTotal',
        'recordsFiltered',
        'data',
    ]);
});

it('renders warehouse stock and movements with the shared datatable mechanism', function (): void {
    $fix = warehouseIndexUiFixtures();
    if ($fix === null) {
        return;
    }

    $session = [
        'company' => $fix['company'],
        'multi_company_selected' => 1,
        'user_company_count' => 1,
    ];

    $stockContent = $this->actingAs($fix['userAuth'], 'web')
        ->withSession($session)
        ->get(route('warehouse.stock.index'))
        ->assertSuccessful()
        ->getContent();

    expect($stockContent)->toContain('window.LaravelDataTables["warehouse-stock-table"]');
    expect($stockContent)->toContain("$('#warehouse-stock-table').on('preXhr.dt'");
    expect($stockContent)->toContain('warehouse-stock-reset-filters');
    expect($stockContent)->toContain('id="warehouse-stock-table"');
    expect($stockContent)->not->toContain('warehouse-footer');
    expect($stockContent)->toContain(__('warehouse::app.adjustStockAction'));
    expect($stockContent)->toContain(__('warehouse::app.transferStock'));
    expect($stockContent)->toContain(__('warehouse::app.stockBatches'));
    expect($stockContent)->toContain(route('warehouse.transfer.create'));
    expect($stockContent)->toContain(route('warehouse.product-batches.index'));
    expect(substr_count($stockContent, route('warehouse.transfer.create')))->toBe(1);
    expect(substr_count($stockContent, route('warehouse.product-batches.index')))->toBe(1);
    expect(substr_count($stockContent, route('warehouse.movements.index')))->toBe(1);

    $movementsContent = $this->actingAs($fix['userAuth'], 'web')
        ->withSession($session)
        ->get(route('warehouse.movements.index'))
        ->assertSuccessful()
        ->getContent();

    expect($movementsContent)->toContain('window.LaravelDataTables["warehouse-movements-table"]');
    expect($movementsContent)->toContain("$('#warehouse-movements-table').on('preXhr.dt'");
    expect($movementsContent)->toContain('warehouse-movements-reset-filters');
    expect($movementsContent)->toContain('id="warehouse-movements-table"');
    expect($movementsContent)->toContain(__('warehouse::app.movementType'));
    expect($movementsContent)->not->toContain('warehouse-footer');
});

it('renders warehouse product batches with the shared datatable mechanism', function (): void {
    $fix = warehouseIndexUiFixtures();
    if ($fix === null) {
        return;
    }

    $response = $this->actingAs($fix['userAuth'], 'web')
        ->withSession([
            'company' => $fix['company'],
            'multi_company_selected' => 1,
            'user_company_count' => 1,
        ])
        ->get(route('warehouse.product-batches.index'));

    $response->assertSuccessful();

    $content = $response->getContent();

    expect($content)->toContain('window.LaravelDataTables["warehouse-product-batches-table"]');
    expect($content)->toContain("$('#warehouse-product-batches-table').on('preXhr.dt'");
    expect($content)->toContain('warehouse-product-batches-reset-filters');
    expect($content)->toContain('id="warehouse-product-batches-table"');
    expect($content)->toContain(__('warehouse::app.backToWarehouseStock'));
    expect($content)->not->toContain('warehouse-footer');
});

it('returns datatable json for warehouse stock ajax requests', function (): void {
    $fix = warehouseIndexUiFixtures();
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
        ->get(route('warehouse.stock.index', warehouseDatatableRequest([
            ['data' => 'DT_RowIndex', 'searchable' => false, 'orderable' => false],
            ['data' => 'id', 'name' => 'warehouse_product_stock.id'],
            ['data' => 'product_label', 'name' => 'product.name', 'searchable' => false, 'orderable' => false],
            ['data' => 'warehouse_label', 'name' => 'warehouse.name', 'searchable' => false, 'orderable' => false],
            ['data' => 'warehouse_type_label', 'name' => 'warehouse.warehouse_type', 'searchable' => false, 'orderable' => false],
            ['data' => 'quantity', 'name' => 'warehouse_product_stock.quantity'],
            ['data' => 'reserved_quantity_display', 'name' => 'reserved_quantity'],
            ['data' => 'available_quantity_display', 'name' => 'available_quantity_display', 'searchable' => false, 'orderable' => false],
            ['data' => 'sellable_quantity_display', 'name' => 'sellable_quantity_display', 'searchable' => false, 'orderable' => false],
            ['data' => 'updated_at', 'name' => 'warehouse_product_stock.updated_at'],
        ], [
            'warehouse_id' => '',
            'searchText' => '',
        ], 9)));

    $response->assertSuccessful();
    $response->assertJsonStructure([
        'draw',
        'recordsTotal',
        'recordsFiltered',
        'data',
    ]);
});

it('returns datatable json for warehouse movements ajax requests', function (): void {
    $fix = warehouseIndexUiFixtures();
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
        ->get(route('warehouse.movements.index', warehouseDatatableRequest([
            ['data' => 'DT_RowIndex', 'searchable' => false, 'orderable' => false],
            ['data' => 'id', 'name' => 'stock_movements.id'],
            ['data' => 'created_at', 'name' => 'stock_movements.created_at'],
            ['data' => 'movement_type', 'name' => 'stock_movements.movement_type'],
            ['data' => 'product_label', 'name' => 'product.name', 'searchable' => false, 'orderable' => false],
            ['data' => 'warehouse_from_label', 'name' => 'warehouseFrom.name', 'searchable' => false, 'orderable' => false],
            ['data' => 'warehouse_to_label', 'name' => 'warehouseTo.name', 'searchable' => false, 'orderable' => false],
            ['data' => 'quantity', 'name' => 'stock_movements.quantity'],
            ['data' => 'batch_number', 'name' => 'stock_movements.batch_number'],
            ['data' => 'reference_label', 'name' => 'stock_movements.reference_type', 'searchable' => false, 'orderable' => false],
        ], [
            'warehouse_id' => '',
            'movement_type' => '',
            'searchText' => '',
        ], 2)));

    $response->assertSuccessful();
    $response->assertJsonStructure([
        'draw',
        'recordsTotal',
        'recordsFiltered',
        'data',
    ]);
});

it('returns datatable json for warehouse product batches ajax requests', function (): void {
    $fix = warehouseIndexUiFixtures();
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
        ->get(route('warehouse.product-batches.index', warehouseDatatableRequest([
            ['data' => 'DT_RowIndex', 'searchable' => false, 'orderable' => false],
            ['data' => 'id', 'name' => 'warehouse_product_batches.id'],
            ['data' => 'batch_label', 'name' => 'warehouse_product_batches.batch_number'],
            ['data' => 'product_label', 'name' => 'product.name', 'searchable' => false, 'orderable' => false],
            ['data' => 'warehouse_label', 'name' => 'warehouse.name', 'searchable' => false, 'orderable' => false],
            ['data' => 'quantity', 'name' => 'warehouse_product_batches.quantity'],
            ['data' => 'reserved_quantity', 'name' => 'warehouse_product_batches.reserved_quantity'],
            ['data' => 'action', 'name' => '', 'searchable' => false, 'orderable' => false],
        ], [
            'warehouse_id' => '',
            'searchText' => '',
        ], 1)));

    $response->assertSuccessful();
    $response->assertJsonStructure([
        'draw',
        'recordsTotal',
        'recordsFiltered',
        'data',
    ]);
});
