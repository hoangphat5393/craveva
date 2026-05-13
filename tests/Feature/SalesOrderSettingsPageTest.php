<?php

declare(strict_types=1);

use App\Models\ClientDetails;
use App\Models\Company;
use App\Models\ModuleSetting;
use App\Models\Permission;
use App\Models\Product;
use App\Models\User;
use App\Models\UserAuth;
use App\Models\UserPermission;
use App\Scopes\CompanyScope;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

uses(DatabaseTransactions::class);

it('registers the sales order settings routes', function (): void {
    expect(Route::has('sales-order-settings.index'))->toBeTrue();
    expect(Route::has('sales-order-settings.regenerate-webhook-secret'))->toBeTrue();
    expect(Route::has('sales-order-settings.update-integration-permissions'))->toBeTrue();
});

/**
 * @return array{company: Company, user: User, userAuth: UserAuth}|null
 */
function salesOrderSettingsFinanceUser(): ?array
{
    if (! Schema::hasTable('users') || ! Schema::hasTable('module_settings') || ! Schema::hasTable('permissions')) {
        test()->markTestSkipped('Required tables are missing for sales order settings test.');

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

    $permissionId = Permission::query()->where('name', 'manage_finance_setting')->value('id');
    $typeAllId = DB::table('permission_types')->where('name', 'all')->value('id');
    if ($permissionId === null || $typeAllId === null) {
        test()->markTestSkipped('manage_finance_setting permission seed missing.');

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

    ModuleSetting::withoutGlobalScope(CompanyScope::class)->updateOrCreate(
        [
            'company_id' => (int) $company->id,
            'module_name' => 'orders',
            'type' => 'admin',
        ],
        [
            'is_allowed' => 1,
            'status' => 'active',
        ],
    );

    Cache::forget('permission-manage_finance_setting-' . $user->id);
    Cache::forget('user_modules_' . $user->id);

    return ['company' => $company, 'user' => $user->fresh(), 'userAuth' => $userAuth];
}

it('shows company id on sales order settings for authorized user', function (): void {
    $fix = salesOrderSettingsFinanceUser();
    if ($fix === null) {
        return;
    }

    if (Schema::hasColumn('companies', 'ai_order_webhook_secret')) {
        Company::withoutGlobalScopes()->where('id', $fix['company']->id)->update([
            'ai_order_webhook_secret' => 'test-secret-sales-order-settings-page',
        ]);
    }

    $userIdsInCompany = User::withoutGlobalScopes()->where('company_id', $fix['company']->id)->pluck('id');

    $expectedClientCode = 'YOUR_CLIENT_CODE';
    if (Schema::hasTable('client_details') && $userIdsInCompany->isNotEmpty()) {
        $foundClientCode = ClientDetails::withoutGlobalScopes()
            ->whereNotNull('client_code')
            ->where('client_code', '!=', '')
            ->where(function ($query) use ($fix, $userIdsInCompany): void {
                $query->where('client_details.company_id', $fix['company']->id)
                    ->orWhereIn('client_details.user_id', $userIdsInCompany);
            })
            ->orderBy('client_details.id')
            ->value('client_code');
        if (is_string($foundClientCode) && trim($foundClientCode) !== '') {
            $expectedClientCode = trim($foundClientCode);
        }
    }

    $expectedItemName = 'Exact product name from catalog';
    if (Schema::hasTable('products')) {
        $foundProductName = Product::withoutGlobalScopes()
            ->where('company_id', $fix['company']->id)
            ->whereNotNull('name')
            ->where('name', '!=', '')
            ->orderBy('id')
            ->value('name');
        if (is_string($foundProductName) && $foundProductName !== '') {
            $expectedItemName = $foundProductName;
        }
    }

    $response = $this->actingAs($fix['userAuth'], 'web')
        ->withSession([
            'company' => $fix['company'],
            'multi_company_selected' => 1,
            'user_company_count' => 1,
        ])
        ->get(route('sales-order-settings.index'));

    $response->assertOk();
    $response->assertSee((string) $fix['company']->id, false);
    $response->assertSee('btn-copy', false);
    $response->assertSee(__('modules.orders.apiIntegrationIntro'), false);
    $response->assertSee(__('modules.orders.apiBaseUrl'), false);
    if (Schema::hasColumn('companies', 'ai_order_webhook_secret')) {
        $response->assertSee(__('modules.orders.apiRestMethodsTitle'), false);
        $response->assertSee(__('modules.orders.apiRestMethodPanelsHint'), false);
        $response->assertSee(__('modules.orders.apiRestPostmanManualNote'), false);
        $response->assertSee(__('modules.orders.apiIntegrationRestOnlyFooter'), false);
        $response->assertSee('YOUR_ORDER_ID', false);
        $response->assertSee('ai_rest_copy_post', false);
        $response->assertSee('ai_rest_tpl_post', false);
        $response->assertSee('ai_rest_curl_post', false);
        $response->assertSee('curl -X POST', false);
        $response->assertSee('Content-Type: application/json', false);
        $response->assertSee('&quot;company_id&quot;', false);
        $response->assertSee('&quot;client_code&quot;', false);
        $response->assertSee($expectedItemName, false);
        $response->assertSee($expectedClientCode, false);
        $response->assertSee('X-AI-Webhook-Secret: test-secret-sales-order-settings-page', false);
        $response->assertSee(__('modules.orders.apiRestPostmanSectionHeaders'), false);
    }
    if ($fix['company']->company_name !== '') {
        $response->assertSee($fix['company']->company_name, false);
    }
});

it('redirects guests from sales order settings', function (): void {
    $this->get(route('sales-order-settings.index'))->assertRedirect(route('login'));
});

it('returns forbidden for employee without manage finance setting', function (): void {
    if (! Schema::hasTable('users') || ! Schema::hasTable('permissions')) {
        test()->markTestSkipped('Required tables missing.');

        return;
    }

    $company = Company::withoutGlobalScopes()->where('status', 'active')->orderBy('id')->first();
    if ($company === null) {
        test()->markTestSkipped('No active company.');

        return;
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

        return;
    }

    $userAuth = UserAuth::query()->find($user->user_auth_id ?? 0);
    if ($userAuth === null) {
        test()->markTestSkipped('No UserAuth for employee.');

        return;
    }

    if ($userAuth->email_verified_at === null) {
        $userAuth->forceFill(['email_verified_at' => now()])->save();
    }

    $permissionId = Permission::query()->where('name', 'manage_finance_setting')->value('id');
    if ($permissionId === null) {
        test()->markTestSkipped('manage_finance_setting permission missing.');

        return;
    }

    UserPermission::query()->where('user_id', $user->id)->where('permission_id', (int) $permissionId)->delete();
    ModuleSetting::withoutGlobalScope(CompanyScope::class)->updateOrCreate(
        [
            'company_id' => (int) $company->id,
            'module_name' => 'orders',
            'type' => 'admin',
        ],
        [
            'is_allowed' => 1,
            'status' => 'active',
        ],
    );

    Cache::forget('permission-manage_finance_setting-' . $user->id);
    Cache::forget('user_modules_' . $user->id);

    $response = $this->actingAs($userAuth, 'web')
        ->withSession([
            'company' => $company,
            'multi_company_selected' => 1,
            'user_company_count' => 1,
        ])
        ->get(route('sales-order-settings.index'));

    $response->assertForbidden();
});

it('regenerates company ai order webhook secret', function (): void {
    if (! Schema::hasColumn('companies', 'ai_order_webhook_secret')) {
        test()->markTestSkipped('companies.ai_order_webhook_secret column missing; run migrations.');

        return;
    }

    $fix = salesOrderSettingsFinanceUser();
    if ($fix === null) {
        return;
    }

    Company::withoutGlobalScopes()->where('id', $fix['company']->id)->update(['ai_order_webhook_secret' => null]);

    $this->actingAs($fix['userAuth'], 'web')
        ->withSession([
            'company' => $fix['company'],
            'multi_company_selected' => 1,
            'user_company_count' => 1,
        ])
        ->post(route('sales-order-settings.regenerate-webhook-secret'))
        ->assertRedirect(route('sales-order-settings.index'));

    $secret = Company::withoutGlobalScopes()->where('id', $fix['company']->id)->value('ai_order_webhook_secret');
    expect($secret)->toBeString()->not->toBe('');
});
