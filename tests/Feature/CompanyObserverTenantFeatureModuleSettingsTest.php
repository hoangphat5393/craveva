<?php

use App\Models\Company;
use App\Models\ModuleSetting;
use App\Models\SuperAdmin\Package;
use App\Observers\CompanyObserver;
use App\Scopes\CompanyScope;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

it('keeps tenant feature modules allowed after package resync', function () {
    $company = Company::query()->whereNotNull('package_id')->orderBy('id')->first();
    if (! $company) {
        test()->markTestSkipped('No company with package_id found.');
    }

    $package = Package::query()->find($company->package_id);
    if (! $package) {
        test()->markTestSkipped('Company package not found.');
    }

    $packageModules = CompanyObserver::packageModuleNamesFromJson($package->module_in_package);
    if (in_array('estimates_phase1_review', $packageModules, true)) {
        test()->markTestSkipped('Package already includes estimates_phase1_review, cannot validate exclusion flow.');
    }

    ModuleSetting::withoutGlobalScope(CompanyScope::class)->updateOrCreate(
        [
            'company_id' => $company->id,
            'module_name' => 'estimates_phase1_review',
            'type' => 'admin',
        ],
        [
            'status' => 'deactive',
            'is_allowed' => 0,
        ],
    );

    ModuleSetting::withoutGlobalScope(CompanyScope::class)->updateOrCreate(
        [
            'company_id' => $company->id,
            'module_name' => 'estimates_phase1_review',
            'type' => 'employee',
        ],
        [
            'status' => 'deactive',
            'is_allowed' => 0,
        ],
    );

    (new CompanyObserver)->updateModuleSettings($company);

    $adminRow = ModuleSetting::withoutGlobalScope(CompanyScope::class)
        ->where('company_id', $company->id)
        ->where('module_name', 'estimates_phase1_review')
        ->where('type', 'admin')
        ->first();

    $employeeRow = ModuleSetting::withoutGlobalScope(CompanyScope::class)
        ->where('company_id', $company->id)
        ->where('module_name', 'estimates_phase1_review')
        ->where('type', 'employee')
        ->first();

    expect($adminRow)->not->toBeNull();
    expect($employeeRow)->not->toBeNull();
    expect((int) $adminRow->is_allowed)->toBe(1);
    expect((int) $employeeRow->is_allowed)->toBe(1);
    expect($adminRow->status)->toBe('deactive');
    expect($employeeRow->status)->toBe('deactive');
});
