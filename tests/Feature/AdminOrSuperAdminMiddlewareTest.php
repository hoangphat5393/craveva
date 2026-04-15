<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use App\Models\UserAuth;
use Database\Seeders\SuperAdminUsersTableSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminOrSuperAdminMiddlewareTest extends TestCase
{
    use DatabaseTransactions;

    public function test_redirects_to_login_when_user_auth_has_no_linked_user_profile(): void
    {
        $userAuth = new UserAuth;
        $userAuth->email = 'orphan_admin_' . uniqid('', true) . '@example.com';
        $userAuth->password = Hash::make('password');
        $userAuth->save();

        $this->actingAs($userAuth, 'web');

        $response = $this->get(route('superadmin.support-tickets.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_super_admin_with_null_company_id_reaches_support_tickets_when_company_is_in_session(): void
    {
        $company = Company::query()->first();

        if ($company === null) {
            $this->markTestSkipped('No company row in database.');
        }

        $email = 'sa_ticket_' . uniqid('', true) . '@example.com';

        $userAuth = UserAuth::create([
            'email' => $email,
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);

        $user = new User;
        $user->name = 'Super Admin Ticket Test';
        $user->email = $email;
        $user->user_auth_id = $userAuth->id;
        $user->is_superadmin = true;
        $user->status = 'active';
        $user->saveQuietly();

        SuperAdminUsersTableSeeder::superadminRolePermissionAttach($user);

        session(['company' => $company]);

        $this->actingAs($userAuth, 'web');

        $response = $this->get(route('superadmin.support-tickets.index'));

        $this->assertFalse(
            $response->isRedirect() && $response->headers->get('Location') === route('login'),
            'Super admin should not be sent to login when a company workspace is in session.'
        );
    }
}
