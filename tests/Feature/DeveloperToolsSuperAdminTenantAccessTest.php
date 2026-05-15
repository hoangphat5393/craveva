<?php

use App\Models\Company;
use App\Models\User;
use App\Models\UserAuth;
use App\Observers\CompanyObserver;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

it('allows superadmin to pass developertools gate when tenant company has module enabled', function () {
    $superadmin = User::withoutGlobalScopes()->where('is_superadmin', 1)->where('status', 'active')->first();
    if (! $superadmin || ! $superadmin->user_auth_id) {
        test()->markTestSkipped('No active superadmin user in database.');
    }

    $company = Company::withoutGlobalScopes()->orderBy('id')->first();
    if (! $company || ! $company->package) {
        test()->markTestSkipped('No company with package.');
    }

    $names = CompanyObserver::packageModuleNamesFromJson($company->package->module_in_package ?? '[]');
    if (! in_array('developertools', $names, true)) {
        test()->markTestSkipped('First company package does not include developertools.');
    }

    $userAuth = UserAuth::find($superadmin->user_auth_id);
    if (! $userAuth) {
        test()->markTestSkipped('Superadmin UserAuth missing.');
    }

    $this->actingAs($userAuth);
    session(['company' => $company]);
    session(['user' => $superadmin]);

    expect(user_can_access_developertools_module())->toBeTrue();
});

it('shows tenant app name in sidebar when superadmin has session company on developertools', function () {
    $superadmin = User::withoutGlobalScopes()->where('is_superadmin', 1)->where('status', 'active')->first();
    if (! $superadmin || ! $superadmin->user_auth_id) {
        test()->markTestSkipped('No active superadmin user in database.');
    }

    $company = Company::withoutGlobalScopes()->orderBy('id')->first();
    if (! $company) {
        test()->markTestSkipped('No company in database.');
    }

    $userAuth = UserAuth::find($superadmin->user_auth_id);
    if (! $userAuth) {
        test()->markTestSkipped('Superadmin UserAuth missing.');
    }

    $expectedTitle = (is_string($company->app_name) && $company->app_name !== '')
        ? $company->app_name
        : $company->company_name;

    $this->actingAs($userAuth);
    session(['company' => $company]);
    session(['user' => $superadmin]);

    $response = $this->get(route('developertools.index'));
    $response->assertSuccessful();
    $response->assertSee($expectedTitle, false);
});
