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

it('returns 422 json with client_id error when storing estimate without client', function (): void {
    if (! Schema::hasTable('estimates')) {
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

    Cache::forget('permission-add_estimates-'.$user->id);
    Cache::forget('user_modules_'.$user->id);

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
        ->postJson(route('estimates.store', ['type' => 'save']), [
            'estimate_number' => 'EST-VAL-'.uniqid('', true),
            'client_id' => '',
            'valid_till' => now()->addDays(7)->format($company->date_format),
            'sub_total' => 100,
            'total' => 100,
            'currency_id' => $currencyId,
            'discount_value' => 0,
            'discount_type' => 'percent',
            'calculate_tax' => 'after_discount',
        ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['client_id']);
});
