<?php

use App\Models\Permission;
use App\Models\ThemeSetting;
use App\Models\User;
use App\Models\UserAuth;
use App\Models\UserPermission;
use App\Scopes\CompanyScope;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

it('creates superadmin theme_settings row when missing and front theme update succeeds', function () {
    if (! isCraveva()) {
        test()->markTestSkipped('Front theme routes require Craveva.');
    }

    $userAuth = UserAuth::create([
        'email' => 'front_theme_test_' . uniqid('', true) . '@example.com',
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

    ThemeSetting::withoutGlobalScope(CompanyScope::class)->where('panel', 'superadmin')->delete();

    session(['user' => $user]);

    $response = $this->actingAs($userAuth)->put(route('superadmin.front-settings.front_theme_update'), [
        'theme' => 0,
        'setup_homepage' => 'default',
        'default_language' => 'en',
        'primary_color' => '#453130',
        'homepage_background' => 'default',
        'background_color' => '#CDDCDC',
        'logo_background_color' => '#FFFFFF',
    ]);

    $response->assertSuccessful();

    expect(
        ThemeSetting::withoutGlobalScope(CompanyScope::class)
            ->where('panel', 'superadmin')
            ->exists()
    )->toBeTrue();
});
