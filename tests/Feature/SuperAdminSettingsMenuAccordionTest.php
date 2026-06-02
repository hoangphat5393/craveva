<?php

declare(strict_types=1);

use App\Models\Permission;
use App\Models\User;
use App\Models\UserAuth;
use App\Models\UserPermission;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

uses(DatabaseTransactions::class);

/**
 * @return array{user: User, userAuth: UserAuth}|null
 */
function superAdminSettingsMenuTestUser(): ?array
{
    $userAuth = UserAuth::query()->whereHas('users', function ($query) {
        $query->where('is_superadmin', 1)->where('status', 'active');
    })->first();

    if ($userAuth === null) {
        test()->markTestSkipped('No superadmin UserAuth found.');

        return null;
    }

    $user = User::withoutGlobalScopes()
        ->where('user_auth_id', $userAuth->id)
        ->where('is_superadmin', 1)
        ->where('status', 'active')
        ->first();

    if ($user === null) {
        test()->markTestSkipped('No active superadmin user found.');

        return null;
    }

    return ['user' => $user, 'userAuth' => $userAuth];
}

it('renders superadmin settings sidebar with accordion groups', function (): void {
    $fixture = superAdminSettingsMenuTestUser();
    if ($fixture === null) {
        return;
    }

    app()->setLocale('en');

    $response = test()->actingAs($fixture['userAuth'])
        ->get(route('security-settings.index'));

    $response->assertSuccessful();
    $response->assertSee('settings-menu-accordion', false);
    $response->assertSee('accordionItemHeading', false);
    $response->assertSee(__('app.menu.settingsMenuGroupPersonal'), false);
    $response->assertSee(__('app.menu.settingsMenuGroupSystem'), false);
    $response->assertSee(__('app.menu.securitySettings'), false);
    $response->assertSee('openSettingsMenuAccordionForActiveItem', false);
});

it('uses company-aligned roles label in superadmin settings sidebar when permission granted', function (): void {
    $fixture = superAdminSettingsMenuTestUser();
    if ($fixture === null) {
        return;
    }

    $permission = Permission::query()->where('name', 'manage_superadmin_permission_settings')->first();
    if ($permission === null) {
        test()->markTestSkipped('manage_superadmin_permission_settings permission not found.');

        return;
    }

    $typeAllId = DB::table('permission_types')->where('name', 'all')->value('id');
    if ($typeAllId === null) {
        test()->markTestSkipped('permission_types seed missing.');

        return;
    }

    UserPermission::query()->updateOrCreate(
        [
            'user_id' => $fixture['user']->id,
            'permission_id' => (int) $permission->id,
        ],
        [
            'permission_type_id' => (int) $typeAllId,
        ],
    );

    app()->setLocale('en');

    $response = test()->actingAs($fixture['userAuth'])
        ->get(route('superadmin.settings.superadmin-permissions.index'));

    $response->assertSuccessful();
    $response->assertSee(__('app.menu.rolesPermission'), false);
    $response->assertDontSee(__('superadmin.superadminRoleAndPermission'), false);
});

it('does not show company-only developer tools links in superadmin settings sidebar', function (): void {
    $fixture = superAdminSettingsMenuTestUser();
    if ($fixture === null) {
        return;
    }

    app()->setLocale('en');

    $response = test()->actingAs($fixture['userAuth'])
        ->get(route('security-settings.index'));

    $response->assertSuccessful();

    if (Route::has('developertools.index')) {
        $response->assertDontSee((string) route('developertools.index'), false);
    }

    if (Route::has('developertools.codemap')) {
        $response->assertDontSee((string) route('developertools.codemap'), false);
    }
});
