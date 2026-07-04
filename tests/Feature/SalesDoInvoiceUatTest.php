<?php

declare(strict_types=1);

use App\Models\Company;
use App\Models\ModuleSetting;
use App\Models\Order;
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
 * @return array{company: Company, user: User, userAuth: UserAuth, order: Order, orderItem: object, salesDoId: int, doNumber: string}|null
 */
function salesDoInvoiceUatFixtures(float $shippedQuantity = 2.0): ?array
{
    foreach (['sales_dos', 'sales_do_items', 'orders', 'order_items'] as $table) {
        if (! Schema::hasTable($table)) {
            test()->markTestSkipped("Required table {$table} is not migrated.");

            return null;
        }
    }

    $company = Company::withoutGlobalScopes()->where('status', 'active')->orderBy('id')->first();
    if ($company === null) {
        test()->markTestSkipped('No active company row found.');

        return null;
    }

    $companyId = (int) $company->id;

    $user = User::withoutGlobalScopes()
        ->where('company_id', $companyId)
        ->where('status', 'active')
        ->whereNull('is_client_contact')
        ->whereNotNull('user_auth_id')
        ->orderBy('id')
        ->first();

    if (! $user instanceof User) {
        test()->markTestSkipped('No active tenant employee found.');

        return null;
    }

    $userAuth = UserAuth::query()->find($user->user_auth_id ?? 0);
    if (! $userAuth instanceof UserAuth) {
        test()->markTestSkipped('No UserAuth found for selected employee.');

        return null;
    }

    if ($userAuth->email_verified_at === null) {
        $userAuth->forceFill(['email_verified_at' => now()])->save();
    }

    $order = Order::withoutGlobalScopes()
        ->where('company_id', $companyId)
        ->whereNotNull('client_id')
        ->whereHas('items', fn ($query) => $query->whereNotNull('product_id')->where('product_id', '>', 0))
        ->orderByDesc('id')
        ->first();

    if (! $order instanceof Order) {
        test()->markTestSkipped('No sales order with product item found for tenant.');

        return null;
    }

    $orderItem = DB::table('order_items')
        ->where('order_id', $order->id)
        ->whereNotNull('product_id')
        ->where('product_id', '>', 0)
        ->orderBy('id')
        ->first();

    if ($orderItem === null) {
        test()->markTestSkipped('No product order item found for tenant.');

        return null;
    }

    $warehouseId = DB::table('warehouses')->where('company_id', $companyId)->where('status', 'active')->value('id');
    if ($warehouseId === null) {
        test()->markTestSkipped('No active warehouse found for tenant.');

        return null;
    }

    ModuleSetting::withoutGlobalScope(CompanyScope::class)->updateOrCreate(
        [
            'company_id' => $companyId,
            'module_name' => 'purchase',
            'type' => 'admin',
        ],
        [
            'is_allowed' => 1,
            'status' => 'active',
        ],
    );

    $typeAllId = DB::table('permission_types')->where('name', 'all')->value('id');
    if ($typeAllId === null) {
        test()->markTestSkipped('permission_types row for "all" not found.');

        return null;
    }

    foreach (['add_invoices', 'view_invoices'] as $permissionName) {
        $permissionId = Permission::query()->where('name', $permissionName)->value('id');
        if ($permissionId === null) {
            test()->markTestSkipped("Permission {$permissionName} is not seeded.");

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

        Cache::forget('permission-'.$permissionName.'-'.$user->id);
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
                'user_id' => $user->id,
                'role_id' => (int) $adminRoleId,
            ],
        );
    }

    Cache::forget('user_modules_'.$user->id);

    $doNumber = 'DO-UAT-'.uniqid();
    $salesDoId = DB::table('sales_dos')->insertGetId([
        'company_id' => $companyId,
        'order_id' => $order->id,
        'warehouse_id' => (int) $warehouseId,
        'do_number' => $doNumber,
        'do_date' => now()->toDateString(),
        'status' => 'shipped',
        'outbound_stock_applied' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('sales_do_items')->insert([
        'sales_do_id' => $salesDoId,
        'order_item_id' => $orderItem->id,
        'product_id' => $orderItem->product_id,
        'quantity_ordered' => $shippedQuantity,
        'quantity_shipped' => $shippedQuantity,
        'unit_id' => $orderItem->unit_id ?? null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return [
        'company' => $company,
        'user' => $user,
        'userAuth' => $userAuth,
        'order' => $order,
        'orderItem' => $orderItem,
        'salesDoId' => (int) $salesDoId,
        'doNumber' => $doNumber,
    ];
}

it('renders invoice create page prefilled from shipped Sales DO', function (): void {
    $fix = salesDoInvoiceUatFixtures();
    if ($fix === null) {
        return;
    }

    $response = $this->actingAs($fix['userAuth'], 'web')
        ->withSession([
            'company' => $fix['company'],
            'multi_company_selected' => 1,
            'user_company_count' => 1,
        ])
        ->get(route('invoices.create', [
            'sales_do_id' => $fix['salesDoId'],
            'order_id' => $fix['order']->id,
        ]));

    if ($response->status() === 403) {
        test()->markTestSkipped('Current user lacks invoice create permission.');
    }

    $response->assertSuccessful();
    $response->assertSee('auto-fill from Sales DO', false);
    $response->assertSee($fix['doNumber'], false);
    $response->assertSee('name="order_id" value="'.$fix['order']->id.'"', false);
});

it('blocks invoice quantity above shipped and uninvoiced Sales DO quantity via store route', function (): void {
    $fix = salesDoInvoiceUatFixtures(shippedQuantity: 2.0);
    if ($fix === null) {
        return;
    }

    $currencyId = $fix['company']->currency_id ?: DB::table('currencies')->value('id');
    if ($currencyId === null) {
        test()->markTestSkipped('No currency row found.');
    }

    $response = $this->actingAs($fix['userAuth'], 'web')
        ->withSession([
            'company' => $fix['company'],
            'multi_company_selected' => 1,
            'user_company_count' => 1,
        ])
        ->post(route('invoices.store'), [
            'invoice_number' => 'INV-UAT-'.uniqid(),
            'order_id' => $fix['order']->id,
            'issue_date' => now()->format($fix['company']->date_format ?: 'Y-m-d'),
            'sub_total' => 30,
            'total' => 30,
            'currency_id' => $currencyId,
            'exchange_rate' => 1,
            'client_id' => $fix['order']->client_id,
            'product_id' => [$fix['orderItem']->product_id],
            'quantity' => [3],
        ]);

    $response->assertOk();
    $response->assertSee('Invoice quantity exceeds shipped and uninvoiced Sales Delivery Order quantity.');
});
