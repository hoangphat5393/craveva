<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\UserAuth;
use Illuminate\Foundation\Testing\RefreshDatabase;

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

        if (!$userAuth) {
            $this->markTestSkipped('No valid UserAuth found in database to test with.');
        }

        // Force user into session to ensure helpers work correctly
        $user = User::where('user_auth_id', $userAuth->id)->first();
        if ($user) {
            session(['user' => $user]);
        }

        $this->actingAs($userAuth);

        // 2. Visit the dashboard (or a page where menu is visible)
        $response = $this->get(route('dashboard'));

        // 3. Assert Response OK
        $response->assertStatus(200);

        // 4. Assert Menu Item exists
        // Searching for the ID we added: id="ai-workspace-menu-item"
        $response->assertSee('id="ai-workspace-menu-item"', false);
    }

    /**
     * Test that the Chatbox container and custom CSS are present in the layout.
     *
     * @return void
     */
    public function test_chatbox_assets_are_loaded()
    {
        $userAuth = $this->getAuthenticatableUser();

        if (!$userAuth) {
            $this->markTestSkipped('No valid UserAuth found in database to test with.');
        }

        $this->actingAs($userAuth);

        $response = $this->get(route('dashboard'));

        // 1. Assert Chatbox Container Div exists and is hidden by default
        $response->assertSee('id="ai-chatbot-container" style="display: none;"', false);

        // 2. Assert Custom CSS is linked with version parameter
        // <link href="{{ asset('css/app-custom.css') }}?v=..." rel="stylesheet">
        $response->assertSee('css/app-custom.css');
        $response->assertSee('?v=');

        // 3. Assert the External Widget Script is NOT loaded by default (Lazy Load verification)
        // It should NOT be present as a <script src="..."> tag in the initial HTML
        $response->assertDontSee('<script src="https://ai.craveva.com/api/v1/agents/6989954407fe94d489fecbf5/widget.js"', false);
    }
}
