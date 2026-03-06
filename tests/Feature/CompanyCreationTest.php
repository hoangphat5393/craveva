<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Currency;
use App\Models\Permission;
use App\Models\Role;
use App\Models\SuperAdmin\GlobalCurrency;
use App\Models\User;
use App\Models\UserPermission;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CompanyCreationTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    public function test_superadmin_can_create_company_and_verify_constraints()
    {
        // 1. Arrange: Authenticate as SuperAdmin
        $superAdmin = User::withoutGlobalScopes()->where('is_superadmin', 1)->first();

        if (! $superAdmin) {
            // Create a temporary superadmin for the test
            $superAdmin = $this->createSuperAdmin();
        }

        $userAuth = \App\Models\UserAuth::find($superAdmin->user_auth_id);
        if (! $userAuth) {
            // Fix missing UserAuth if superadmin exists but auth is missing (edge case)
            $userAuth = \App\Models\UserAuth::create([
                'email' => $superAdmin->email,
                'password' => bcrypt('password'),
            ]);
            $superAdmin->user_auth_id = $userAuth->id;
            $superAdmin->saveQuietly();
        }

        $globalCurrency = GlobalCurrency::first();
        if (! $globalCurrency) {
            $this->markTestSkipped('No global currency found.');
        }

        $companyEmail = 'test_company_'.time().'@example.com';
        $adminEmail = 'test_admin_'.time().'@example.com';

        $companyData = [
            'company_name' => 'Test Company '.time(),
            'company_email' => $companyEmail,
            'status' => 'active',
            'name' => 'Test Admin',
            'email' => $adminEmail,
            'currency_id' => $globalCurrency->id,
            'timezone' => 'Asia/Ho_Chi_Minh',
            'locale' => 'en',
        ];

        // 2. Act: Post to store route
        // Simulate logged in user in session as well, as helpers rely on it
        session(['user' => $superAdmin]);

        $response = $this->actingAs($userAuth)
            ->post(route('superadmin.companies.store'), $companyData);

        // 3. Assert: Check response and database
        // The controller uses Reply::redirect which returns a JSON with 200 OK
        if ($response->status() !== 200) {
            dump($response->exception ? $response->exception->getMessage() : 'No exception message');
            dump($response->exception ? $response->exception->getFile().':'.$response->exception->getLine() : 'No exception location');
            // dump($response->getContent()); // Too verbose
        }
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'action' => 'redirect',
            ]);

        // Assert Company Created
        $company = Company::where('company_email', $companyEmail)->first();
        $this->assertNotNull($company, 'Company was not created in database.');

        // Assert Currency Created and Assigned
        $currency = Currency::withoutGlobalScopes()->where('company_id', $company->id)->first();
        $this->assertNotNull($currency, 'Currency was not created for the company.');
        $this->assertEquals($globalCurrency->currency_code, $currency->currency_code, 'Currency code mismatch.');
        $this->assertEquals($company->currency_id, $currency->id, 'Company currency_id does not match created currency id.');

        // Assert Admin User Created
        $adminUser = User::withoutGlobalScopes()->where('email', $adminEmail)->where('company_id', $company->id)->first();
        if (! $adminUser) {
            dump('Admin Email searched: '.$adminEmail);
            dump('Company ID searched: '.$company->id);
            dump('All Users found:', User::withoutGlobalScopes()->where('email', $adminEmail)->get()->toArray());
        }
        $this->assertNotNull($adminUser, 'Admin user was not created.');

        // Assert Roles
        $this->assertTrue($adminUser->roles()->withoutGlobalScopes()->where('name', 'admin')->exists(), 'User does not have admin role.');

        // Assert Permissions (basic check)
        // Checking if user has any permission attached
        $this->assertGreaterThan(0, $adminUser->permissionTypes()->count(), 'Admin user has no permissions assigned.');

        // 4. Cleanup
        // Delete company and related data
        // Using forceDelete if soft deletes are enabled to fully clean up, or just delete.
        // Company::destroy($company->id) handles related data via observers usually.
        // But for safety in test, let's use the controller's destroy logic or just delete the company.

        $company->delete();
        // Users are usually cascaded or soft deleted.
        $adminUser->delete();
    }

    private function createSuperAdmin()
    {
        $faker = \Faker\Factory::create();
        $email = 'superadmin_'.time().'@example.com';

        $superadmin = new User;
        $superadmin->name = $faker->name;
        $superadmin->email = $email;
        $superadmin->is_superadmin = 1;
        $superadmin->save();

        $userAuth = \App\Models\UserAuth::create([
            'email' => $email,
            'password' => bcrypt('123456'),
            'email_verified_at' => now(),
        ]);

        $superadmin->user_auth_id = $userAuth->id;
        $superadmin->saveQuietly();

        // Attach SuperAdmin Role
        $superadminRole = Role::withoutGlobalScopes([\App\Scopes\CompanyScope::class])
            ->whereNull('company_id')
            ->where('name', 'superadmin')
            ->first();

        if ($superadminRole) {
            $superadmin->roles()->attach($superadminRole->id);
        }

        // Attach permissions
        $permissions = Permission::whereIn('name', ['add_companies', 'view_companies', 'edit_companies', 'delete_companies'])->get();
        foreach ($permissions as $permission) {
            UserPermission::create([
                'user_id' => $superadmin->id,
                'permission_id' => $permission->id,
                'permission_type_id' => 4, // 'all' permission type
            ]);
        }

        return $superadmin;
    }
}
