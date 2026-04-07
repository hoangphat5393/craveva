<?php

use App\Models\ModuleSetting;
use App\Models\SuperAdmin\Package;
use App\Observers\CompanyObserver;
use App\Scopes\CompanyScope;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;

uses(DatabaseTransactions::class);

it('packages modules activate resyncs module_settings when module already in package json', function () {
    $package = Package::with('companies')->orderBy('id')->first();
    if (! $package || $package->companies->isEmpty()) {
        test()->markTestSkipped('No package with companies in database.');
    }

    $names = CompanyObserver::packageModuleNamesFromJson($package->module_in_package);
    if (! in_array('developertools', $names, true)) {
        test()->markTestSkipped('First package does not include developertools in module_in_package.');
    }

    $company = $package->companies->first();

    ModuleSetting::withoutGlobalScope(CompanyScope::class)
        ->where('company_id', $company->id)
        ->where('module_name', 'developertools')
        ->where('type', 'admin')
        ->update(['is_allowed' => 0, 'status' => 'deactive']);

    Artisan::call('packages:modules', [
        'action' => 'activate',
        '--module' => 'developertools',
        '--package' => (string) $package->id,
    ]);

    $row = ModuleSetting::withoutGlobalScope(CompanyScope::class)
        ->where('company_id', $company->id)
        ->where('module_name', 'developertools')
        ->where('type', 'admin')
        ->first();

    expect($row)->not->toBeNull();
    expect((int) $row->is_allowed)->toBe(1);
    expect($row->status)->toBe('active');
});
