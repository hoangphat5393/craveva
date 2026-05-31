<?php

use App\Models\Permission;
use App\Models\User;
use App\Models\UserAuth;
use App\Models\UserPermission;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

it('renders craveva ai settings page with workspace and assistant tabs', function () {
    $userAuth = new UserAuth;
    $userAuth->email = 'craveva-ai-settings-test@example.com';
    $userAuth->password = bcrypt('password');
    $userAuth->save();

    $user = User::factory()->create([
        'email' => 'craveva-ai-settings-test@example.com',
        'user_auth_id' => $userAuth->id,
        'is_superadmin' => 1,
        'login' => 'enable',
        'status' => 'active',
    ]);

    $superadminPermissions = Permission::whereHas('module', function ($query) {
        $query->withoutGlobalScopes()->where('is_superadmin', '1');
    })->where('name', 'manage_superadmin_app_settings')->get();

    if ($superadminPermissions->isEmpty()) {
        test()->markTestSkipped('manage_superadmin_app_settings permission not found.');
    }

    foreach ($superadminPermissions as $permission) {
        UserPermission::create([
            'user_id' => $user->id,
            'permission_id' => $permission->id,
            'permission_type_id' => 4,
        ]);
    }

    $this->actingAs($userAuth);

    $response = $this->get(route('craveva-ai-settings.index'));

    $response->assertSuccessful();
    $response->assertSee(__('app.menu.cravevaAi'), false);
    $response->assertSee('ai-workspace-setting', false);
    $response->assertSee('ai-assistant-widget-setting', false);
    $response->assertDontSee('file-upload-setting', false);
});

it('redirects legacy app settings ai tabs to craveva ai settings', function () {
    $userAuth = UserAuth::query()->whereHas('users', function ($query) {
        $query->where('is_superadmin', 1)->where('status', 'active');
    })->first();

    if (! $userAuth) {
        test()->markTestSkipped('No superadmin UserAuth found.');
    }

    $this->actingAs($userAuth);

    $this->get(route('app-settings.index', ['tab' => 'ai-workspace-setting']))
        ->assertRedirect(route('craveva-ai-settings.index', ['tab' => 'ai-workspace-setting']));
});

it('shows single craveva ai item in superadmin settings sidebar', function () {
    $userAuth = UserAuth::query()->whereHas('users', function ($query) {
        $query->where('is_superadmin', 1)->where('status', 'active');
    })->first();

    if (! $userAuth) {
        test()->markTestSkipped('No superadmin UserAuth found.');
    }

    $this->actingAs($userAuth);

    $response = $this->get(route('craveva-ai-settings.index'));

    $response->assertSuccessful();
    $response->assertSee(route('craveva-ai-settings.index'), false);
    $response->assertSee(__('app.menu.cravevaAi'), false);
    $response->assertSee('nav-item nav-link f-15 ai-workspace-setting', false);
    $response->assertSee('nav-item nav-link f-15 ai-assistant-widget-setting', false);
    $response->assertSee('id="ai_workspace_embed_code"', false);
    $response->assertSee('textarea', false);
    $response->assertSee('Embed JavaScript', false);

    $this->get(route('craveva-ai-settings.index', ['tab' => 'ai-assistant-widget-setting']))
        ->assertSuccessful()
        ->assertSee('id="ai_assistant_widget_embed_code"', false);
});
