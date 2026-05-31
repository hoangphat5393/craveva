<?php

use App\Models\GlobalSetting;
use App\Models\Permission;
use App\Models\User;
use App\Models\UserAuth;
use App\Models\UserPermission;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

it('persists ai assistant widget embed code on craveva ai settings update', function () {
    $userAuth = new UserAuth;
    $userAuth->email = 'ai-assistant-widget-test@example.com';
    $userAuth->password = bcrypt('password');
    $userAuth->save();

    $user = User::factory()->create([
        'email' => 'ai-assistant-widget-test@example.com',
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
        'ai_assistant_widget_embed_code' => $global->ai_assistant_widget_embed_code,
    ];

    $this->actingAs($userAuth);

    $embedCode = '<script>window.aiAssistantWidgetTest = true;</script>';

    $response = $this->put(route('craveva-ai-settings.update', $global->id), [
        'page' => 'ai-assistant-widget-setting',
        'ai_assistant_widget_embed_code' => $embedCode,
    ]);

    $response->assertSuccessful();

    $global->refresh();
    expect($global->ai_assistant_widget_embed_code)->toBe($embedCode);

    $global->update($backup);
    cache()->forget('global_setting');
});
