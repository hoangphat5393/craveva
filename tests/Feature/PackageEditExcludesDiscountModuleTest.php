<?php

use App\Models\Module;
use App\Models\Permission;
use App\Models\SuperAdmin\Package;
use App\Models\User;
use App\Models\UserAuth;
use App\Models\UserPermission;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

it('does not render the legacy discount module checkbox on package edit', function () {
    Module::withoutGlobalScopes()->firstOrCreate(
        ['module_name' => 'discount'],
        ['description' => 'Legacy module row (not a selectable package feature)']
    );

    $package = Package::query()->orderBy('id')->first();
    if (! $package) {
        test()->markTestSkipped('No package in database.');
    }

    $userAuth = new UserAuth;
    $userAuth->email = 'package-discount-test@example.com';
    $userAuth->password = bcrypt('password');
    $userAuth->save();

    $user = User::factory()->create([
        'email' => 'package-discount-test@example.com',
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

    $this->actingAs($userAuth);
    $this->get(route('superadmin.super_admin_dashboard'));

    $response = $this->get(route('superadmin.packages.edit', $package->id));

    $response->assertSuccessful();
    $response->assertDontSee('id="discount"', false);
});
