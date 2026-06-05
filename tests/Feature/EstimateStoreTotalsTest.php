<?php

declare(strict_types=1);

use App\Models\Company;
use App\Models\Currency;
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

it('persists sub_total and total from line items instead of incorrect request totals', function (): void {
    if (! Schema::hasTable('estimates') || ! Schema::hasTable('estimate_items')) {
        test()->markTestSkipped('Estimate tables are not migrated.');

        return;
    }

    $company = Company::withoutGlobalScopes()->where('status', 'active')->orderBy('id')->first();
    if (! $company instanceof Company) {
        test()->markTestSkipped('No active company row found.');

        return;
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

        return;
    }

    $userAuth = UserAuth::query()->find($user->user_auth_id ?? 0);
    if (! $userAuth instanceof UserAuth) {
        test()->markTestSkipped('No UserAuth found for selected employee.');

        return;
    }

    if ($userAuth->email_verified_at === null) {
        $userAuth->forceFill(['email_verified_at' => now()])->save();
    }

    $permissionId = Permission::query()->where('name', 'add_estimates')->value('id');
    $typeAllId = DB::table('permission_types')->where('name', 'all')->value('id');
    if ($permissionId === null || $typeAllId === null) {
        test()->markTestSkipped('Required estimate permission seeds are missing.');

        return;
    }

    UserPermission::query()->updateOrCreate(
        ['user_id' => $user->id, 'permission_id' => (int) $permissionId],
        ['permission_type_id' => (int) $typeAllId]
    );

    Cache::forget('permission-add_estimates-' . $user->id);
    Cache::forget('user_modules_' . $user->id);

    $clientId = User::withoutGlobalScopes()
        ->where('users.company_id', $companyId)
        ->where('client_details.company_id', $companyId)
        ->join('client_details', 'client_details.user_id', '=', 'users.id')
        ->value('users.id');
    if ($clientId === null) {
        test()->markTestSkipped('No client found for selected company.');

        return;
    }

    $currencyId = Currency::withoutGlobalScope(CompanyScope::class)
        ->where('company_id', $companyId)
        ->orWhereNull('company_id')
        ->orderBy('id')
        ->value('id');
    if ($currencyId === null) {
        test()->markTestSkipped('No currency found.');

        return;
    }

    $response = $this->actingAs($userAuth, 'web')
        ->withSession([
            'company' => $company,
            'multi_company_selected' => 1,
            'user_company_count' => 1,
        ])
        ->post(route('estimates.store', ['type' => 'draft']), [
            '_token' => csrf_token(),
            'estimate_number' => 'EST-TOTALS-' . now()->format('YmdHis'),
            'client_id' => $clientId,
            'valid_till' => now()->addDays(7)->format($company->date_format),
            'sub_total' => 100,
            'total' => 100,
            'currency_id' => $currencyId,
            'discount_value' => 0,
            'discount_type' => 'percent',
            'calculate_tax' => 'after_discount',
            'item_name' => ['Line A', 'Line B'],
            'item_summary' => ['', ''],
            'quantity' => ['1', '1'],
            'cost_per_item' => ['100', '200'],
            'amount' => ['100', '200'],
            'unit_id' => ['', ''],
            'product_id' => ['', ''],
            'taxes' => [[], []],
            'redirect_url' => route('estimates.index'),
        ]);

    $response->assertOk();
    $response->assertJsonPath('status', 'success');

    $savedEstimate = DB::table('estimates')->latest('id')->first();
    expect($savedEstimate)->not->toBeNull();
    expect((float) $savedEstimate->sub_total)->toBe(300.0);
    expect((float) $savedEstimate->total)->toBe(300.0);
    expect($savedEstimate->calculate_tax)->toBe('after_discount');

    expect(DB::table('estimate_items')->where('estimate_id', $savedEstimate->id)->count())->toBe(2);
});
