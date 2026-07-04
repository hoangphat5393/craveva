<?php

namespace Modules\Pricing\Tests\Unit;

use App\Models\Company;
use App\Models\Product;
use App\Models\User;
use App\Models\UserAuth;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Pricing\Entities\ClientProductPricing;
use Tests\TestCase;

class ContractPricingTest extends TestCase
{
    use DatabaseTransactions;

    protected $company;

    protected $client;

    protected $product;

    protected function setUp(): void
    {
        parent::setUp();

        // Create Company
        $this->company = new Company;
        $this->company->company_name = 'Test Company';
        $this->company->company_email = 'test@example.com';
        $this->company->date_format = 'Y-m-d';
        $this->company->save();

        // Create Client
        $clientEmail = 'client_'.uniqid().'@example.com';
        $clientAuth = UserAuth::create([
            'email' => $clientEmail,
            'password' => bcrypt('123456'),
            'email_verified_at' => now(),
        ]);

        $this->client = User::factory()->create([
            'company_id' => $this->company->id,
            'user_auth_id' => $clientAuth->id,
            'email' => $clientEmail,
        ]);

        // Create Product
        $this->product = Product::factory()->create([
            'company_id' => $this->company->id,
            'price' => 100,
        ]);

        // Login as admin
        $adminEmail = 'admin_'.uniqid().'@example.com';
        $adminAuth = UserAuth::create([
            'email' => $adminEmail,
            'password' => bcrypt('123456'),
            'email_verified_at' => now(),
        ]);

        $admin = User::factory()->create([
            'company_id' => $this->company->id,
            'user_auth_id' => $adminAuth->id,
            'email' => $adminEmail,
        ]);

        // Mock permission
        // Since we can't easily set up full RBAC in unit test without seeding roles,
        // we might need to mock the permission check or ensure the user has the 'admin' role which usually has all permissions.
        // For now, let's try actingAs and see if we hit 403.

        $this->actingAs($adminAuth);
    }

    public function test_product_id_is_required()
    {
        $response = $this->post(route('pricing.client_pricing.store'), [
            'client_id' => $this->client->id,
            'start_date' => now()->format($this->company->date_format),
            'end_date' => now()->addMonth()->format($this->company->date_format),
        ]);

        $response->assertSessionHasErrors('product_id');
    }

    public function test_start_date_is_required()
    {
        $response = $this->post(route('pricing.client_pricing.store'), [
            'client_id' => $this->client->id,
            'product_id' => $this->product->id,
            'end_date' => now()->addMonth()->format($this->company->date_format),
        ]);

        $response->assertSessionHasErrors('start_date');
    }

    public function test_start_date_must_be_today_or_future()
    {
        $response = $this->post(route('pricing.client_pricing.store'), [
            'client_id' => $this->client->id,
            'product_id' => $this->product->id,
            'start_date' => now()->subDay()->format($this->company->date_format),
        ]);

        $response->assertSessionHasErrors('start_date');
    }

    public function test_end_date_must_be_after_start_date()
    {
        $response = $this->post(route('pricing.client_pricing.store'), [
            'client_id' => $this->client->id,
            'product_id' => $this->product->id,
            'start_date' => now()->addDay()->format($this->company->date_format),
            'end_date' => now()->format($this->company->date_format), // Earlier than start_date
        ]);

        $response->assertSessionHasErrors('end_date');
    }

    public function test_can_create_contract_pricing_with_valid_dates()
    {
        $response = $this->post(route('pricing.client_pricing.store'), [
            'client_id' => $this->client->id,
            'product_id' => $this->product->id,
            'start_date' => now()->format($this->company->date_format),
            'end_date' => now()->addMonth()->format($this->company->date_format),
            'custom_price' => 90,
        ]);

        $response->assertStatus(200);
    }

    public function test_cannot_create_overlapping_contract_pricing()
    {
        // Create first pricing: Next month 1st to 30th
        $start = now()->addMonth()->startOfMonth();
        $end = now()->addMonth()->endOfMonth();

        ClientProductPricing::create([
            'company_id' => $this->company->id,
            'client_id' => $this->client->id,
            'product_id' => $this->product->id,
            'start_date' => $start,
            'end_date' => $end,
            'is_active' => true,
        ]);

        // Try to create overlapping: Middle of next month
        $response = $this->post(route('pricing.client_pricing.store'), [
            'client_id' => $this->client->id,
            'product_id' => $this->product->id,
            'start_date' => $start->addDays(5)->format($this->company->date_format),
            'end_date' => $end->subDays(5)->format($this->company->date_format),
        ]);

        $response->assertJson(['status' => 'fail']);
    }

    public function test_can_create_non_overlapping_contract_pricing()
    {
        // Create first pricing: Next month 1st to 15th
        $start1 = now()->addMonth()->startOfMonth();
        $end1 = now()->addMonth()->startOfMonth()->addDays(14);

        ClientProductPricing::create([
            'company_id' => $this->company->id,
            'client_id' => $this->client->id,
            'product_id' => $this->product->id,
            'start_date' => $start1,
            'end_date' => $end1,
            'is_active' => true,
        ]);

        // Try to create non-overlapping: Next month 16th to end
        $start2 = now()->addMonth()->startOfMonth()->addDays(15);
        $end2 = now()->addMonth()->endOfMonth();

        $response = $this->post(route('pricing.client_pricing.store'), [
            'client_id' => $this->client->id,
            'product_id' => $this->product->id,
            'start_date' => $start2->format($this->company->date_format),
            'end_date' => $end2->format($this->company->date_format),
        ]);

        $response->assertStatus(200);
    }

    public function test_inactive_contract_pricing_does_not_block_overlapping_new_contract_pricing()
    {
        $start = now()->addMonth()->startOfMonth();
        $end = now()->addMonth()->endOfMonth();

        ClientProductPricing::create([
            'company_id' => $this->company->id,
            'client_id' => $this->client->id,
            'product_id' => $this->product->id,
            'start_date' => $start,
            'end_date' => $end,
            'is_active' => false,
        ]);

        $response = $this->post(route('pricing.client_pricing.store'), [
            'client_id' => $this->client->id,
            'product_id' => $this->product->id,
            'start_date' => $start->copy()->addDays(5)->format($this->company->date_format),
            'end_date' => $end->copy()->subDays(5)->format($this->company->date_format),
            'custom_price' => 90,
        ]);

        $response->assertStatus(200);
    }

    public function test_bulk_action_rejects_invalid_row_ids()
    {
        $response = $this->post(route('pricing.client_pricing.apply_quick_action'), [
            'action_type' => 'change-status',
            'row_ids' => 'abc,def',
            'status' => 'active',
        ]);

        $response->assertSessionHasErrors('row_ids');
    }

    public function test_change_status_updates_client_product_pricing(): void
    {
        $pricing = ClientProductPricing::create([
            'company_id' => $this->company->id,
            'client_id' => $this->client->id,
            'product_id' => $this->product->id,
            'custom_price' => 800,
            'discount_type' => null,
            'discount_value' => null,
            'start_date' => now()->addDay(),
            'end_date' => now()->addMonths(2),
            'is_active' => true,
        ]);

        $response = $this->post(route('pricing.client_pricing.change_status'), [
            'id' => $pricing->id,
            'status' => 'inactive',
        ]);

        $response->assertStatus(200);
        $this->assertFalse((bool) $pricing->fresh()->is_active);
    }
}
