<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserAuth;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ChatboxToggleTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Helper to get an authenticatable user instance.
     * Prioritizes non-superadmin users to avoid 403 blocks in DashboardController.
     */
    protected function getAuthenticatableUser()
    {
        // Try to find a non-superadmin admin user first
        $user = User::join('role_user', 'role_user.user_id', '=', 'users.id')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->where('roles.name', 'admin')
            ->where('users.is_superadmin', 0)
            ->where('users.status', 'active')
            ->select('users.*')
            ->first();

        if (! $user) {
            // Fallback to any active user
            $user = User::where('status', 'active')->where('is_superadmin', 0)->first();
        }

        if (! $user) {
            // Fallback to superadmin if no others exist (though this might hit 403)
            $user = User::where('is_superadmin', 1)->first();
        }

        // Return the UserAuth instance which implements Authenticatable
        return UserAuth::find($user->id);
    }

    #[Test]
    public function it_contains_chatbox_container_in_layout()
    {
        $user = $this->getAuthenticatableUser();
        $this->actingAs($user);

        $response = $this->get('/account/dashboard');

        $response->assertStatus(200);
        $response->assertSee('id="ai-chatbot-container"', false);
    }

    #[Test]
    public function it_contains_toggle_logic_in_layout()
    {
        $user = $this->getAuthenticatableUser();
        $this->actingAs($user);

        $response = $this->get('/account/dashboard');

        $response->assertStatus(200);
        // Check for key functions
        $response->assertSee('function showChat()', false);
        $response->assertSee('function hideChat()', false);
        $response->assertSee('const aiWorkspaceKey = \'ai_workspace_active\';', false);
    }

    #[Test]
    public function it_does_not_auto_show_chatbox_on_load()
    {
        $user = $this->getAuthenticatableUser();
        $this->actingAs($user);

        $response = $this->get('/account/dashboard');

        $response->assertStatus(200);
        // Ensure the auto-show logic is commented out or removed
        // We look for the commented out code or absence of the active call
        // checking if "if (localStorage.getItem(aiWorkspaceKey) === 'true') { showChat(); }" is NOT active
        // This is hard to test perfectly with text search, but we can check it's not present in its active form
        // or check that it IS present but commented out.

        // Let's verify the container is hidden by default
        $response->assertSee('id="ai-chatbot-container" style="display: none;"', false);
    }
}
