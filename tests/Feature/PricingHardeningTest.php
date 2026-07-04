<?php

use App\Models\ClientDetails;
use App\Models\Company;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Pricing\Entities\ClientProductPricing;
use Modules\Pricing\Entities\CompanyCustomerPricing;
use Modules\Pricing\Entities\PricingTier;
use Modules\Pricing\Entities\PricingTierItem;
use Modules\Pricing\Entities\VolumeDiscountRule;
use Modules\Pricing\Jobs\ImportClientProductPricingJob;
use Modules\Pricing\Services\PricingService;
use Modules\Pricing\Services\VolumeDiscountService;

uses(DatabaseTransactions::class);

function pricingHardeningCompany(): Company
{
    $company = new Company;
    $company->company_name = 'Pricing Hardening '.uniqid();
    $company->company_email = 'pricing_'.uniqid().'@example.com';
    $company->date_format = 'Y-m-d';
    $company->save();

    return $company;
}

it('does not leak company volume discount rules without explicit company context', function (): void {
    $otherCompany = pricingHardeningCompany();

    VolumeDiscountRule::create([
        'company_id' => $otherCompany->id,
        'name' => 'Other tenant discount',
        'discount_type' => 'percentage',
        'minimum_quantity' => 1,
        'discount_value' => 90,
        'applies_to_type' => 'all',
        'is_active' => true,
    ]);

    $result = app(VolumeDiscountService::class)->calculate([
        ['product_id' => 1, 'price' => 100, 'quantity' => 2],
    ]);

    expect($result['value'])->toBe(0);
});

it('uses only platform and matching company volume discount rules', function (): void {
    $otherCompany = pricingHardeningCompany();
    $currentCompany = pricingHardeningCompany();

    VolumeDiscountRule::create([
        'company_id' => $otherCompany->id,
        'name' => 'Other tenant better threshold',
        'discount_type' => 'percentage',
        'minimum_quantity' => 10,
        'discount_value' => 90,
        'applies_to_type' => 'all',
        'is_active' => true,
    ]);

    VolumeDiscountRule::create([
        'company_id' => $currentCompany->id,
        'name' => 'Current tenant discount',
        'discount_type' => 'percentage',
        'minimum_quantity' => 1,
        'discount_value' => 10,
        'applies_to_type' => 'all',
        'is_active' => true,
    ]);

    $result = app(VolumeDiscountService::class)->calculate([
        ['product_id' => 1, 'price' => 100, 'quantity' => 10],
    ], $currentCompany->id);

    expect($result['value'])->toBe(100.0);
});

it('ignores inactive-by-date corporate contract pricing', function (): void {
    $company = pricingHardeningCompany();
    $client = User::factory()->create(['company_id' => $company->id]);
    $product = Product::factory()->create(['company_id' => $company->id, 'price' => 100]);

    CompanyCustomerPricing::create([
        'company_id' => $company->id,
        'client_id' => $client->id,
        'custom_discount_type' => 'percentage',
        'custom_discount_value' => 50,
        'valid_from' => now()->addDay()->toDateString(),
        'valid_to' => now()->addDays(10)->toDateString(),
        'is_active' => true,
    ]);

    $result = app(PricingService::class)->calculate($product->id, $client->id, 1);

    expect($result['applied'])->toBe('base_price')
        ->and($result['unit_price'])->toBe(100.0);
});

it('applies active corporate contract pricing by date', function (): void {
    $company = pricingHardeningCompany();
    $client = User::factory()->create(['company_id' => $company->id]);
    $product = Product::factory()->create(['company_id' => $company->id, 'price' => 100]);

    CompanyCustomerPricing::create([
        'company_id' => $company->id,
        'client_id' => $client->id,
        'custom_discount_type' => 'percentage',
        'custom_discount_value' => 10,
        'valid_from' => now()->subDay()->toDateString(),
        'valid_to' => now()->addDay()->toDateString(),
        'is_active' => true,
    ]);

    $result = app(PricingService::class)->calculate($product->id, $client->id, 1);

    expect($result['applied'])->toBe('company_customer_pricing')
        ->and($result['unit_price'])->toBe(90.0);
});

it('treats zero corporate discount as an explicit contract pricing value', function (): void {
    $company = pricingHardeningCompany();
    $client = User::factory()->create(['company_id' => $company->id]);
    $product = Product::factory()->create(['company_id' => $company->id, 'price' => 100]);

    CompanyCustomerPricing::create([
        'company_id' => $company->id,
        'client_id' => $client->id,
        'custom_discount_type' => 'percentage',
        'custom_discount_value' => 0,
        'valid_from' => now()->subDay()->toDateString(),
        'valid_to' => now()->addDay()->toDateString(),
        'is_active' => true,
    ]);

    $result = app(PricingService::class)->calculate($product->id, $client->id, 1);

    expect($result['applied'])->toBe('company_customer_pricing')
        ->and($result['unit_price'])->toBe(100.0);
});

it('applies active corporate tier item pricing without changing pricing priority', function (): void {
    $company = pricingHardeningCompany();
    $client = User::factory()->create(['company_id' => $company->id]);
    $product = Product::factory()->create(['company_id' => $company->id, 'price' => 100]);

    $tier = PricingTier::create([
        'company_id' => $company->id,
        'name' => 'Corporate Tier '.uniqid(),
        'discount_type' => 'percentage',
        'discount_value' => 5,
        'priority' => 1,
        'valid_from' => now()->subDay()->toDateString(),
        'valid_to' => now()->addDay()->toDateString(),
        'is_active' => true,
    ]);

    PricingTierItem::create([
        'pricing_tier_id' => $tier->id,
        'product_id' => $product->id,
        'discount_type' => 'percentage',
        'discount_value' => 20,
        'is_active' => true,
    ]);

    CompanyCustomerPricing::create([
        'company_id' => $company->id,
        'client_id' => $client->id,
        'pricing_tier_id' => $tier->id,
        'valid_from' => now()->subDay()->toDateString(),
        'valid_to' => now()->addDay()->toDateString(),
        'is_active' => true,
    ]);

    $result = app(PricingService::class)->calculate($product->id, $client->id, 1);

    expect($result['applied'])->toBe('corporate_pricing_tier_item')
        ->and($result['unit_price'])->toBe(80.0);
});

it('rejects legacy calculatePrice entry point', function (): void {
    app(PricingService::class)->calculatePrice(1, 1, 1);
})->throws(BadMethodCallException::class);

it('imports client product pricing by explicit date range', function (): void {
    $company = pricingHardeningCompany();
    $client = User::factory()->create(['company_id' => $company->id]);
    ClientDetails::create([
        'company_id' => $company->id,
        'user_id' => $client->id,
        'company_name' => 'Pricing Client',
        'client_code' => 'PRC-'.uniqid(),
    ]);
    $client->refresh();

    $product = Product::factory()->create([
        'company_id' => $company->id,
        'sku' => 'PRC-'.uniqid(),
        'price' => 100,
    ]);

    $columns = ['customer_code', 'email', 'product_sku', 'custom_price', 'discount_type', 'discount_value', 'start_date', 'end_date'];
    $rowA = [$client->clientDetails->client_code, null, $product->sku, 80, null, null, '2026-07-01', '2026-07-31'];
    $rowB = [$client->clientDetails->client_code, null, $product->sku, 70, null, null, '2026-08-01', '2026-08-31'];
    $rowAUpdate = [$client->clientDetails->client_code, null, $product->sku, 75, null, null, '2026-07-01', '2026-07-31'];

    (new ImportClientProductPricingJob($rowA, $columns, $company))->handle();
    (new ImportClientProductPricingJob($rowB, $columns, $company))->handle();
    (new ImportClientProductPricingJob($rowAUpdate, $columns, $company))->handle();

    $rows = ClientProductPricing::withoutGlobalScopes()
        ->where('company_id', $company->id)
        ->where('client_id', $client->id)
        ->where('product_id', $product->id)
        ->orderBy('start_date')
        ->get();

    expect($rows)->toHaveCount(2)
        ->and((float) $rows[0]->custom_price)->toBe(75.0)
        ->and((float) $rows[1]->custom_price)->toBe(70.0);
});
