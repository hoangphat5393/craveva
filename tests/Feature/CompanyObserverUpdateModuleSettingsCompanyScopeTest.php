<?php

use App\Models\Company;
use App\Models\ModuleSetting;
use App\Models\SuperAdmin\Package;
use App\Models\User;
use App\Models\UserAuth;
use App\Observers\CompanyObserver;
use App\Scopes\CompanyScope;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

it('updateModuleSettings updates the passed company when session company differs', function () {
    $companies = Company::query()->whereNotNull('package_id')->orderBy('id')->limit(2)->get();
    if ($companies->count() < 2) {
        test()->markTestSkipped('Need at least 2 companies with package_id.');
    }

    $sessionCompany = $companies->first();
    $targetCompany = $companies->last();

    $package = Package::query()->find($targetCompany->package_id);
    if (! $package) {
        test()->markTestSkipped('Target company has no package.');
    }

    $names = CompanyObserver::packageModuleNamesFromJson($package->module_in_package);
    if (! in_array('developertools', $names, true)) {
        test()->markTestSkipped('Target company package must list developertools in module_in_package.');
    }

    $user = User::query()
        ->where('company_id', $sessionCompany->id)
        ->whereNotNull('user_auth_id')
        ->first();

    if (! $user) {
        test()->markTestSkipped('No user linked to first company.');
    }

    $userAuth = UserAuth::query()->find($user->user_auth_id);
    if (! $userAuth) {
        test()->markTestSkipped('No user_auth for user.');
    }

    ModuleSetting::withoutGlobalScope(CompanyScope::class)
        ->where('company_id', $targetCompany->id)
        ->where('module_name', 'developertools')
        ->where('type', 'admin')
        ->update(['is_allowed' => 0, 'status' => 'deactive']);

    $this->actingAs($userAuth);
    session(['company' => Company::query()->find($sessionCompany->id)]);

    (new CompanyObserver)->updateModuleSettings($targetCompany);

    $row = ModuleSetting::withoutGlobalScope(CompanyScope::class)
        ->where('company_id', $targetCompany->id)
        ->where('module_name', 'developertools')
        ->where('type', 'admin')
        ->first();

    expect($row)->not->toBeNull();
    expect((int) $row->is_allowed)->toBe(1);
    expect($row->status)->toBe('active');
});
