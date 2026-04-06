<?php

use App\Models\Company;
use App\Models\CompanyAddress;
use App\Models\Permission;
use App\Models\User;
use App\Models\UserAuth;
use App\Models\UserPermission;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

it('super admin company edit succeeds when company has no default address', function () {
    $userAuth = UserAuth::create([
        'email' => 'sa_edit_test_'.uniqid('', true).'@example.com',
        'password' => bcrypt('password'),
    ]);

    $user = User::factory()->create([
        'email' => $userAuth->email,
        'user_auth_id' => $userAuth->id,
        'is_superadmin' => 1,
        'login' => 'enable',
        'status' => 'active',
    ]);

    $superadminPermissions = Permission::whereHas('module', function ($query) {
        $query->withoutGlobalScopes()->where('is_superadmin', '1');
    })->get();

    if ($superadminPermissions->isEmpty()) {
        test()->markTestSkipped('Super Admin permissions not found in DB.');
    }

    foreach ($superadminPermissions as $permission) {
        UserPermission::create([
            'user_id' => $user->id,
            'permission_id' => $permission->id,
            'permission_type_id' => 4,
        ]);
    }

    $company = Company::withoutGlobalScopes()
        ->whereNotNull('currency_id')
        ->first();

    if (! $company) {
        test()->markTestSkipped('No company with currency found.');
    }

    CompanyAddress::where('company_id', $company->id)->delete();

    session(['user' => $user]);

    $response = $this->actingAs($userAuth)
        ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
        ->get(route('superadmin.companies.edit', $company->id));

    $response->assertSuccessful();
    expect($response->json('status'))->toBe('success');
});
