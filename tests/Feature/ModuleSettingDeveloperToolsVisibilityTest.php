<?php

use App\Models\ModuleSetting;
use App\Scopes\CompanyScope;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;

uses(DatabaseTransactions::class);

it('includes developertools in admin module settings when is_allowed is zero', function () {
    $companyId = DB::table('companies')->value('id');

    if (! $companyId) {
        test()->markTestSkipped('No company row in database.');
    }

    ModuleSetting::withoutGlobalScope(CompanyScope::class)
        ->where('company_id', $companyId)
        ->where('module_name', 'developertools')
        ->where('type', 'admin')
        ->delete();

    ModuleSetting::withoutGlobalScope(CompanyScope::class)->create([
        'company_id' => $companyId,
        'module_name' => 'developertools',
        'type' => 'admin',
        'status' => 'deactive',
        'is_allowed' => 0,
    ]);

    $list = ModuleSetting::forTenantModuleSettingsIndex((int) $companyId, 'admin');

    expect($list->where('module_name', 'developertools')->first())->not->toBeNull();
});
