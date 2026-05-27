<?php

declare(strict_types=1);

use App\Models\Company;
use App\Models\InvoiceSetting;
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
use Modules\Purchase\Entities\PurchaseSetting;

uses(DatabaseTransactions::class);

/**
 * @return array{company: Company, user: User, userAuth: UserAuth}|null
 */
function companyDocumentTermsFinanceUser(array $modules = ['orders']): ?array
{
    if (! Schema::hasTable('users') || ! Schema::hasTable('module_settings') || ! Schema::hasTable('permissions')) {
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

    foreach ($modules as $moduleName) {
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

    Cache::forget('permission-manage_finance_setting-'.$user->id);
    Cache::forget('user_modules_'.$user->id);

    return ['company' => $company, 'user' => $user->fresh(), 'userAuth' => $userAuth];
}

it('stores sale order terms on invoice settings prefix update', function (): void {
    if (! Schema::hasColumn('invoice_settings', 'order_terms')) {
        test()->markTestSkipped('order_terms column missing.');
    }

    $fix = companyDocumentTermsFinanceUser(['orders']);
    if ($fix === null) {
        return;
    }

    $setting = InvoiceSetting::withoutGlobalScopes()
        ->where('company_id', $fix['company']->id)
        ->first();

    if ($setting === null) {
        test()->markTestSkipped('No invoice setting for company.');
    }

    $terms = 'Sale order terms unique '.uniqid();

    $payload = [
        'order_prefix' => $setting->order_prefix ?? 'SO',
        'order_number_separator' => $setting->order_number_separator ?? '#',
        'order_digit' => $setting->order_digit ?? 3,
        'order_terms' => $terms,
        'proposal_prefix' => $setting->proposal_prefix ?? 'PROP',
        'proposal_number_separator' => $setting->proposal_number_separator ?? '#',
        'proposal_digit' => $setting->proposal_digit ?? 3,
    ];

    ModuleSetting::withoutGlobalScope(CompanyScope::class)->updateOrCreate(
        [
            'company_id' => (int) $fix['company']->id,
            'module_name' => 'invoices',
            'type' => 'admin',
        ],
        [
            'is_allowed' => 1,
            'status' => 'active',
        ],
    );
    Cache::forget('user_modules_'.$fix['user']->id);

    $payload['invoice_prefix'] = $setting->invoice_prefix ?? 'INV';
    $payload['invoice_number_separator'] = $setting->invoice_number_separator ?? '#';
    $payload['invoice_digit'] = $setting->invoice_digit ?? 3;
    $payload['credit_note_prefix'] = $setting->credit_note_prefix ?? 'CN';
    $payload['credit_note_number_separator'] = $setting->credit_note_number_separator ?? '#';
    $payload['credit_note_digit'] = $setting->credit_note_digit ?? 3;

    ModuleSetting::withoutGlobalScope(CompanyScope::class)->updateOrCreate(
        [
            'company_id' => (int) $fix['company']->id,
            'module_name' => 'estimates',
            'type' => 'admin',
        ],
        [
            'is_allowed' => 1,
            'status' => 'active',
        ],
    );
    Cache::forget('user_modules_'.$fix['user']->id);

    $payload['estimate_prefix'] = $setting->estimate_prefix ?? 'EST';
    $payload['estimate_number_separator'] = $setting->estimate_number_separator ?? '#';
    $payload['estimate_digit'] = $setting->estimate_digit ?? 3;
    $payload['estimate_request_prefix'] = $setting->estimate_request_prefix ?? 'ER';
    $payload['estimate_request_number_separator'] = $setting->estimate_request_number_separator ?? '#';
    $payload['estimate_request_digit'] = $setting->estimate_request_digit ?? 3;

    $response = test()->actingAs($fix['userAuth'], 'web')
        ->withSession([
            'company' => $fix['company'],
            'multi_company_selected' => 1,
            'user_company_count' => 1,
        ])
        ->post(route('invoice_settings.update_prefix', $setting->id), $payload);

    $response->assertSuccessful();

    $setting->refresh();
    expect($setting->order_terms)->toBe($terms);
});

it('redirects legacy delivery order settings to purchase settings document terms', function (): void {
    $fix = companyDocumentTermsFinanceUser(['purchase']);
    if ($fix === null) {
        return;
    }

    $permissionId = Permission::query()->where('name', 'view_purchase_setting')->value('id');
    $typeAllId = DB::table('permission_types')->where('name', 'all')->value('id');
    if ($permissionId !== null && $typeAllId !== null) {
        UserPermission::query()->updateOrCreate(
            [
                'user_id' => $fix['user']->id,
                'permission_id' => (int) $permissionId,
            ],
            [
                'permission_type_id' => (int) $typeAllId,
            ],
        );
        Cache::forget('permission-view_purchase_setting-'.$fix['user']->id);
    }

    $response = test()->actingAs($fix['userAuth'], 'web')
        ->withSession([
            'company' => $fix['company'],
            'multi_company_selected' => 1,
            'user_company_count' => 1,
        ])
        ->get(route('delivery-order-settings.index'));

    $response->assertRedirect(route('purchase-settings.index', ['tab' => 'general']).'#document-terms');
});

it('stores sale order terms on sales order settings tab update', function (): void {
    if (! Schema::hasColumn('invoice_settings', 'order_terms')) {
        test()->markTestSkipped('order_terms column missing.');
    }

    $fix = companyDocumentTermsFinanceUser(['orders']);
    if ($fix === null) {
        return;
    }

    $setting = InvoiceSetting::withoutGlobalScopes()
        ->where('company_id', $fix['company']->id)
        ->first();

    if ($setting === null) {
        test()->markTestSkipped('No invoice setting for company.');
    }

    $terms = 'Sale order tab terms '.uniqid();

    $response = test()->actingAs($fix['userAuth'], 'web')
        ->withSession([
            'company' => $fix['company'],
            'multi_company_selected' => 1,
            'user_company_count' => 1,
        ])
        ->post(route('sales-order-settings.update-order-settings', $setting->id), [
            'order_prefix' => $setting->order_prefix ?? 'SO',
            'order_number_separator' => $setting->order_number_separator ?? '#',
            'order_digit' => $setting->order_digit ?? 3,
            'order_terms' => $terms,
        ]);

    $response->assertSuccessful();

    $setting->refresh();
    expect($setting->order_terms)->toBe($terms);
});

it('stores grn terms on purchase settings prefix update', function (): void {
    if (! Schema::hasColumn('purchase_settings', 'grn_terms')) {
        test()->markTestSkipped('grn_terms column missing.');
    }

    $fix = companyDocumentTermsFinanceUser(['purchase']);
    if ($fix === null) {
        return;
    }

    $purchaseSetting = PurchaseSetting::withoutGlobalScopes()
        ->where('company_id', $fix['company']->id)
        ->first();

    if ($purchaseSetting === null) {
        test()->markTestSkipped('No purchase setting for company.');
    }

    $terms = 'GRN terms unique '.uniqid();

    $response = test()->actingAs($fix['userAuth'], 'web')
        ->withSession([
            'company' => $fix['company'],
            'multi_company_selected' => 1,
            'user_company_count' => 1,
        ])
        ->post(route('purchase_settings.update_prefix', $purchaseSetting->id), [
            'purchase_order_prefix' => $purchaseSetting->purchase_order_prefix ?? 'PO',
            'purchase_order_number_seprator' => $purchaseSetting->purchase_order_number_separator ?? '#',
            'purchase_order_digit' => $purchaseSetting->purchase_order_number_digit ?? 3,
            'bill_prefix' => $purchaseSetting->bill_prefix ?? 'BL',
            'bill_number_seprator' => $purchaseSetting->bill_number_separator ?? '#',
            'bill_digit' => $purchaseSetting->bill_number_digit ?? 3,
            'vendor_credit_prefix' => $purchaseSetting->vendor_credit_prefix ?? 'VC',
            'vendor_credit_number_seprator' => $purchaseSetting->vendor_credit_number_seprator ?? '#',
            'vendor_credit_digit' => $purchaseSetting->vendor_credit_number_digit ?? 3,
            'purchase_terms' => $purchaseSetting->purchase_terms,
            'grn_terms' => $terms,
        ]);

    $response->assertSuccessful();

    $purchaseSetting->refresh();
    expect($purchaseSetting->grn_terms)->toBe($terms);
});
