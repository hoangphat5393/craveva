<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\User;
use App\Models\UserAuth;
use App\Models\UserPermission;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SuperAdminDashboardTest extends TestCase
{
    use DatabaseTransactions;

    public function test_super_admin_can_access_dashboard_and_see_sidebar_items()
    {
        $this->withoutExceptionHandling();

        // 1. Create UserAuth (Authenticatable)
        $userAuth = new UserAuth;
        $userAuth->email = 'superadmintest@example.com';
        $userAuth->password = bcrypt('password');
        $userAuth->save();

        // 2. Create Super Admin User linked to UserAuth
        $user = User::factory()->create([
            'email' => 'superadmintest@example.com',
            'user_auth_id' => $userAuth->id,
            'is_superadmin' => 1,
            'login' => 'enable',
            'status' => 'active',
        ]);

        // 3. Assign Super Admin Permissions
        // Replicating the logic from fix_superadmin_perms.php
        $superadminPermissions = Permission::whereHas('module', function ($query) {
            $query->withoutGlobalScopes()->where('is_superadmin', '1');
        })->get();

        if ($superadminPermissions->isEmpty()) {
            $this->markTestSkipped('Super Admin permissions not found in DB. Migrations might not have run.');
        }

        foreach ($superadminPermissions as $permission) {
            UserPermission::create([
                'user_id' => $user->id,
                'permission_id' => $permission->id,
                'permission_type_id' => 4, // Allow
            ]);
        }

        // 4. Login as UserAuth
        $this->actingAs($userAuth);

        // 5. Access Dashboard
        $response = $this->get(route('superadmin.super_admin_dashboard'));

        // 6. Assertions
        $response->assertStatus(200);

        // Check for sidebar items links
        $response->assertSee(route('superadmin.companies.index'));
        $response->assertSee(route('superadmin.packages.index'));
    }
}
