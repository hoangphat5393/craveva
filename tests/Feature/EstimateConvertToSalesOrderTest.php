<?php

declare(strict_types=1);

use App\Models\Company;
use App\Models\Estimate;
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
 * @return array{company: Company, user: User, userAuth: UserAuth, estimate: Estimate}|null
 */
function estimateConvertFixtures(): ?array
{
    if (! Schema::hasTable('estimates') || ! Schema::hasTable('orders') || ! Schema::hasTable('order_items')) {
        test()->markTestSkipped('Estimate/order tables are not migrated.');

        return null;
    }

    if (! Schema::hasColumn('orders', 'estimate_id')) {
        test()->markTestSkipped('orders.estimate_id column is not migrated in this environment.');

        return null;
    }

    $company = Company::withoutGlobalScopes()->where('status', 'active')->orderBy('id')->first();

    if ($company === null) {
        test()->markTestSkipped('No active company row found.');

        return null;
    }

    $companyId = (int) $company->id;

    /** @var User|null $user */
    $user = User::withoutGlobalScopes()
        ->where('company_id', $companyId)
        ->where('status', 'active')
        ->whereNull('is_client_contact')
        ->whereNotNull('user_auth_id')
        ->orderBy('id')
        ->first();

    if ($user === null) {
        test()->markTestSkipped('No active tenant employee found.');

        return null;
    }

    /** @var UserAuth|null $userAuth */
    $userAuth = UserAuth::query()->find($user->user_auth_id ?? 0);
    if ($userAuth === null) {
        test()->markTestSkipped('No UserAuth found for selected employee.');

        return null;
    }

    if ($userAuth->email_verified_at === null) {
        $userAuth->forceFill(['email_verified_at' => now()])->save();
    }

    $estimate = Estimate::withoutGlobalScopes()
        ->where('company_id', $companyId)
        ->where('status', 'waiting')
        ->whereDoesntHave('orders')
        ->whereHas('items')
        ->orderByDesc('id')
        ->first();

    if (! $estimate instanceof Estimate) {
        test()->markTestSkipped('No convertible estimate with items found for tenant.');

        return null;
    }

    $estimate->forceFill([
        'president_review_status' => Estimate::INTERNAL_REVIEW_APPROVED,
        'vp_pricing_review_status' => Estimate::INTERNAL_REVIEW_APPROVED,
    ])->save();

    ModuleSetting::withoutGlobalScope(CompanyScope::class)->updateOrCreate(
        [
            'company_id' => $companyId,
            'module_name' => 'estimates_phase1_review',
            'type' => 'admin',
        ],
        [
            'is_allowed' => 1,
            'status' => 'active',
        ],
    );

    $permissionId = Permission::query()->where('name', 'add_order')->value('id');
    $typeAllId = DB::table('permission_types')->where('name', 'all')->value('id');

    if ($permissionId === null || $typeAllId === null) {
        test()->markTestSkipped('Required order permission seeds are missing.');

        return null;
    }

    UserPermission::query()->updateOrCreate(
        [
            'user_id' => $user->id,
            'permission_id' => (int) $permissionId,
        ],
        [
            'permission_type_id' => (int) $typeAllId,
        ]
    );

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

    Cache::forget('permission-add_order-'.$user->id);
    Cache::forget('user_modules_'.$user->id);

    return [
        'company' => $company,
        'user' => $user,
        'userAuth' => $userAuth,
        'estimate' => $estimate->fresh(['items', 'orders']),
    ];
}

it('converts an approved estimate to sales order and copies items', function (): void {
    $fix = estimateConvertFixtures();
    if ($fix === null) {
        return;
    }

    $estimate = $fix['estimate'];
    $expectedItemCount = $estimate->items->count();

    $response = $this->actingAs($fix['userAuth'], 'web')
        ->withSession([
            'company' => $fix['company'],
            'multi_company_selected' => 1,
            'user_company_count' => 1,
        ])
        ->post(route('estimates.convert_to_sales_order', $estimate->id), [
            '_token' => csrf_token(),
        ]);

    $response->assertOk();
    $response->assertJsonPath('status', 'success');

    $order = Order::query()
        ->where('estimate_id', $estimate->id)
        ->orderByDesc('id')
        ->first();

    expect($order)->not->toBeNull();
    expect((int) $order->client_id)->toBe((int) $estimate->client_id);
    expect((int) $order->currency_id)->toBe((int) $estimate->currency_id);

    $copiedItemCount = DB::table('order_items')->where('order_id', $order->id)->count();
    expect($copiedItemCount)->toBe($expectedItemCount);
});
