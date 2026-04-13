<?php

namespace Tests\Feature;

use App\Models\GlobalSetting;
use App\Models\User;
use App\Models\UserAuth;
use Tests\TestCase;

class ChatboxTest extends TestCase
{
    // Note: We don't use RefreshDatabase here to avoid wiping the existing dev database.
    // In a CI environment, we would use it.

    /**
     * Helper to get an authenticatable user.
     */
    protected function getAuthenticatableUser()
    {
        // 1. Try to find an ACTIVE admin user (most likely to have dashboard access)
        try {
            $user = User::where('status', 'active')
                ->where('is_superadmin', 0) // Dashboard often has different route for superadmin
                ->whereHas('roles', function ($q) {
                    $q->where('name', 'admin');
                })
                ->whereNotNull('user_auth_id')
                ->first();

            if ($user && $user->userAuth) {
                return $user->userAuth;
            }
        } catch (\Exception $e) {
            // Role table might not exist or relationship issue
        }

        // 2. Try to find any ACTIVE non-superadmin user
        $user = User::where('status', 'active')
            ->where('is_superadmin', 0)
            ->whereNotNull('user_auth_id')
            ->first();

        if ($user && $user->userAuth) {
            return $user->userAuth;
        }

        // 3. Try to find an ACTIVE superadmin user (fallback)
        $user = User::where('status', 'active')
            ->where('is_superadmin', 1)
            ->whereNotNull('user_auth_id')
            ->first();

        if ($user && $user->userAuth) {
            return $user->userAuth;
        }

        // 4. Last resort: any user with auth
        $user = User::whereNotNull('user_auth_id')->first();
        if ($user && $user->userAuth) {
            return $user->userAuth;
        }

        return null;
    }

    /**
     * Test that the AI Workspace menu item renders for a logged-in user.
     *
     * @return void
     */
    public function test_ai_workspace_menu_item_is_visible()
    {
        // 1. Get an authenticatable user (UserAuth)
        $userAuth = $this->getAuthenticatableUser();

        if (! $userAuth) {
            $this->markTestSkipped('No valid UserAuth found in database to test with.');
        }

        $global = GlobalSetting::first();
        if (! $global) {
            $this->markTestSkipped('No global_settings row found.');
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
            // Force user into session to ensure helpers work correctly
            $user = User::where('user_auth_id', $userAuth->id)->first();
            if ($user) {
                session(['user' => $user]);
            }

            $this->actingAs($userAuth);

            $response = $this->get(route('dashboard'));

            $response->assertStatus(200);
            $response->assertSee('id="ai-workspace-menu-item"', false);
        } finally {
            $global->update($backup);
            cache()->forget('global_setting');
        }
    }

    /**
     * Test that the Chatbox container and custom CSS are present in the layout.
     *
     * @return void
     */
    public function test_chatbox_assets_are_loaded()
    {
        $userAuth = $this->getAuthenticatableUser();

        if (! $userAuth) {
            $this->markTestSkipped('No valid UserAuth found in database to test with.');
        }

        $global = GlobalSetting::first();
        if (! $global) {
            $this->markTestSkipped('No global_settings row found.');
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
            $this->actingAs($userAuth);

            $response = $this->get(route('dashboard'));

            $response->assertSee('id="ai-chatbot-container" style="display: none;"', false);

            $response->assertSee('css/app-custom.css');
            $response->assertSee('?v=');

            $response->assertDontSee('<script src="https://ai.craveva.com/api/v1/agents/', false);
        } finally {
            $global->update($backup);
            cache()->forget('global_setting');
        }
    }
}
