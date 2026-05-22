<?php

use App\Models\GlobalSetting;
use App\Models\User;
use App\Models\UserAuth;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

it('renders ai workspace page with widget loader when configured', function () {
    $userAuth = UserAuth::query()->whereHas('users', function ($query) {
        $query->where('is_superadmin', 1)->where('status', 'active');
    })->first();

    if (! $userAuth) {
        test()->markTestSkipped('No superadmin UserAuth found.');
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

    $global->update([
        'ai_workspace_agent_id' => '69ccc35e7d0ece6ff702487b',
        'ai_workspace_api_base' => 'https://ai.craveva.com',
        'ai_workspace_api_key' => null,
    ]);
    cache()->forget('global_setting');

    try {
        $user = User::where('user_auth_id', $userAuth->id)->first();
        if ($user) {
            session(['user' => $user]);
        }

        $this->actingAs($userAuth);

        $response = $this->get(route('ai-workspace.index'));

        $response->assertSuccessful();
        $response->assertSee('id="ai-workspace-page-root"', false);
        $response->assertSee('data-ai-workspace-page', false);
        $response->assertSee('69ccc35e7d0ece6ff702487b', false);
        $response->assertSee('widget.js', false);
    } finally {
        $global->update($backup);
        cache()->forget('global_setting');
    }
});

it('returns 404 when ai workspace is not configured', function () {
    $userAuth = UserAuth::query()->whereHas('users', function ($query) {
        $query->where('is_superadmin', 1)->where('status', 'active');
    })->first();

    if (! $userAuth) {
        test()->markTestSkipped('No superadmin UserAuth found.');
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

    $global->update([
        'ai_workspace_agent_id' => null,
        'ai_workspace_api_base' => null,
        'ai_workspace_api_key' => null,
    ]);
    cache()->forget('global_setting');

    try {
        $this->actingAs($userAuth);

        $this->get(route('ai-workspace.index'))->assertNotFound();
    } finally {
        $global->update($backup);
        cache()->forget('global_setting');
    }
});
