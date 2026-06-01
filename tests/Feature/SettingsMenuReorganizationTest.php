<?php

declare(strict_types=1);

use App\Models\Company;
use App\Models\ModuleSetting;
use App\Models\Permission;
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
function settingsMenuFinanceAdminUser(): ?array
{
    if (! Schema::hasTable('users') || ! Schema::hasTable('permissions')) {
        test()->markTestSkipped('Required tables are missing.');

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

    $permissions = [
        'manage_finance_setting',
        'manage_company_setting',
        'view_purchase_setting',
    ];

    $typeAllId = DB::table('permission_types')->where('name', 'all')->value('id');
    if ($typeAllId === null) {
        test()->markTestSkipped('permission_types seed missing.');

        return null;
    }

    foreach ($permissions as $permissionName) {
        $permissionId = Permission::query()->where('name', $permissionName)->value('id');
        if ($permissionId === null) {
            continue;
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

    foreach (['orders', 'purchase', 'invoices'] as $moduleName) {
        ModuleSetting::withoutGlobalScope(CompanyScope::class)->updateOrCreate(
            [
                'company_id' => (int) $company->id,
                'module_name' => $moduleName,
                'type' => 'admin',
            ],
            [
                'is_allowed' => 1,
                'status' => 'active',
            ],
        );
    }

    Cache::forget('user_modules_'.$user->id);

    return ['company' => $company, 'user' => $user->fresh(), 'userAuth' => $userAuth];
}

it('renders grouped settings menu with sales items before procurement items', function (): void {
    $fix = settingsMenuFinanceAdminUser();
    if ($fix === null) {
        return;
    }

    app()->setLocale('en');

    $response = test()->actingAs($fix['userAuth'], 'web')
        ->withSession([
            'company' => $fix['company'],
            'multi_company_selected' => 1,
            'user_company_count' => 1,
        ])
        ->get(route('company-settings.index'));

    $response->assertSuccessful();
    $response->assertSee('settings-menu-accordion', false);
    $response->assertSee('accordionItemHeading', false);
    $response->assertSee(__('app.menu.settingsMenuGroupSales'));
    $response->assertSee(__('app.menu.settingsMenuGroupProcurement'));
    $response->assertSee(__('app.menu.financeSettings'));
    $response->assertSee(__('app.menu.saleOrderSettings'));
    $response->assertSee(__('purchase::app.menu.purchaseSettings'));

    $html = $response->getContent();
    $saleOrderPos = strpos($html, (string) route('sales-order-settings.index'));
    $purchasePos = strpos($html, (string) route('purchase-settings.index'));

    if ($saleOrderPos !== false && $purchasePos !== false) {
        expect($saleOrderPos)->toBeLessThan($purchasePos);
    }
});

it('uses invoice and estimate label instead of finance settings in english', function (): void {
    app()->setLocale('en');

    expect(__('app.menu.financeSettings'))->toBe('Invoices & Estimates');
});

it('renders billing as a top-level settings item without an admin accordion', function (): void {
    if (! Schema::hasTable('role_user') || ! Schema::hasTable('roles')) {
        test()->markTestSkipped('Role tables are missing.');

        return;
    }

    $adminRoleId = DB::table('roles')->where('name', 'admin')->value('id');
    if ($adminRoleId === null) {
        test()->markTestSkipped('Admin role missing.');

        return;
    }

    $superadminRoleId = DB::table('roles')->where('name', 'superadmin')->value('id');

    $userIdQuery = DB::table('role_user')->where('role_id', $adminRoleId);
    if ($superadminRoleId !== null) {
        $userIdQuery->whereNotIn('user_id', DB::table('role_user')->where('role_id', $superadminRoleId)->select('user_id'));
    }

    $userId = $userIdQuery->value('user_id');
    if ($userId === null) {
        test()->markTestSkipped('No company admin without superadmin role.');

        return;
    }

    $user = User::withoutGlobalScopes()->find($userId);
    if ($user === null || $user->company_id === null) {
        test()->markTestSkipped('Admin user not found.');

        return;
    }

    $company = Company::withoutGlobalScopes()->find($user->company_id);
    $userAuth = UserAuth::query()->find($user->user_auth_id ?? 0);
    if ($company === null || $userAuth === null) {
        test()->markTestSkipped('Admin user company or auth missing.');

        return;
    }

    if ($userAuth->email_verified_at === null) {
        $userAuth->forceFill(['email_verified_at' => now()])->save();
    }

    app()->setLocale('en');

    $response = test()->actingAs($userAuth, 'web')
        ->withSession([
            'company' => $company,
            'multi_company_selected' => 1,
            'user_company_count' => 1,
        ])
        ->get(route('billing.index'));

    if ($response->status() === 403) {
        test()->markTestSkipped('Billing route forbidden for selected admin user.');

        return;
    }

    $response->assertSuccessful();

    $html = (string) $response->getContent();
    $billingUrl = (string) route('billing.index');

    $dom = new DOMDocument;
    @$dom->loadHTML($html);
    $xpath = new DOMXPath($dom);
    $billingLinks = $xpath->query('//*[@id="settingsMenu"]//a[contains(@class, "accordionItemHeading") and contains(@href, "billing")]');

    expect($billingLinks->length)->toBeGreaterThan(0);

    $billingListItem = $billingLinks->item(0)?->parentNode;
    while ($billingListItem !== null && $billingListItem->nodeName !== 'li') {
        $billingListItem = $billingListItem->parentNode;
    }

    expect($billingListItem)->not->toBeNull();
    expect($billingListItem->getAttribute('class'))->toContain('settings-menu-single-link');
    expect($billingLinks->item(0)?->getAttribute('class'))->toContain('settings-sidebar-heading');
});
