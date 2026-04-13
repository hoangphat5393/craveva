<?php

use App\Models\GlobalSetting;
use App\Models\Permission;
use App\Models\User;
use App\Models\UserAuth;
use App\Models\UserPermission;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

it('persists ai workspace fields on app settings update', function () {
    $userAuth = new UserAuth;
    $userAuth->email = 'ai-workspace-test@example.com';
    $userAuth->password = bcrypt('password');
    $userAuth->save();

    $user = User::factory()->create([
        'email' => 'ai-workspace-test@example.com',
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

    $global = GlobalSetting::first();
    if (! $global) {
        test()->markTestSkipped('No global_settings row.');
    }

    $backup = [
        'ai_workspace_agent_id' => $global->ai_workspace_agent_id,
        'ai_workspace_api_base' => $global->ai_workspace_api_base,
        'ai_workspace_api_key' => $global->ai_workspace_api_key,
    ];

    $this->actingAs($userAuth);

    $agentId = '69ccc35e7d0ece6ff702487b';
    $apiBase = 'https://ai.craveva.com';

    $response = $this->put(route('app-settings.update', $global->id), [
        'page' => 'ai-workspace-setting',
        'ai_workspace_agent_id' => $agentId,
        'ai_workspace_api_base' => $apiBase,
        'ai_workspace_api_key' => 'test-api-key-value',
    ]);

    $response->assertSuccessful();

    $global->refresh();
    expect($global->ai_workspace_agent_id)->toBe($agentId);
    expect($global->ai_workspace_api_base)->toBe($apiBase);
    expect($global->ai_workspace_api_key)->toBe('test-api-key-value');

    $global->update($backup);
    cache()->forget('global_setting');
});

it('rejects invalid ai workspace agent id', function () {
    $userAuth = new UserAuth;
    $userAuth->email = 'ai-workspace-invalid@example.com';
    $userAuth->password = bcrypt('password');
    $userAuth->save();

    $user = User::factory()->create([
        'email' => 'ai-workspace-invalid@example.com',
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

    $global = GlobalSetting::first();
    if (! $global) {
        test()->markTestSkipped('No global_settings row.');
    }

    $this->actingAs($userAuth);

    $response = $this->put(route('app-settings.update', $global->id), [
        'page' => 'ai-workspace-setting',
        'ai_workspace_agent_id' => 'not-a-valid-objectid',
        'ai_workspace_api_base' => 'https://ai.craveva.com',
    ]);

    $response->assertSessionHasErrors(['ai_workspace_agent_id']);
});
