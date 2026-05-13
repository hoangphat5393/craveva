<?php

declare(strict_types=1);

use App\Models\ClientDetails;
use App\Models\Company;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;

uses(DatabaseTransactions::class);

/**
 * @return array{name: string, sku: string}
 */
function aiWebhookCreateProductLine(Company $company): array
{
    if (! Schema::hasTable('products')) {
        test()->markTestSkipped('products table missing.');
    }

    $name = 'AI Webhook Line '.uniqid('', true);
    $sku = 'WH-SKU-'.substr(str_replace('.', '', uniqid('', true)), 0, 12);

    try {
        Product::withoutGlobalScopes()->forceCreate([
            'company_id' => $company->id,
            'name' => $name,
            'price' => '1',
            'description' => 'fixture',
            'allow_purchase' => 1,
            'sku' => $sku,
        ]);
    } catch (Throwable $e) {
        test()->markTestSkipped('Could not insert product fixture: '.$e->getMessage());
    }

    return ['name' => $name, 'sku' => $sku];
}

it('returns 422 when body company_id does not match company for per-company REST secret', function (): void {
    if (! Schema::hasColumn('companies', 'ai_order_webhook_secret') || ! Schema::hasColumn('companies', 'ai_order_integration_allow_create')) {
        test()->markTestSkipped('Required company integration columns missing; run migrations.');

        return;
    }

    if (! Schema::hasTable('client_details')) {
        test()->markTestSkipped('client_details table missing.');

        return;
    }

    $companies = Company::withoutGlobalScopes()
        ->where('status', 'active')
        ->orderBy('id')
        ->limit(2)
        ->get();

    if ($companies->count() < 2) {
        test()->markTestSkipped('Need at least two active companies for cross-company mismatch test.');

        return;
    }

    /** @var Company $companyA */
    $companyA = $companies->first();
    /** @var Company $companyB */
    $companyB = $companies->last();

    $userIdsB = User::withoutGlobalScopes()->where('company_id', $companyB->id)->pluck('id');

    $detailB = ClientDetails::withoutGlobalScopes()
        ->whereIn('user_id', $userIdsB)
        ->whereNotNull('client_code')
        ->where('client_code', '!=', '')
        ->first();

    if ($detailB === null) {
        test()->markTestSkipped('No client_details.client_code for second company.');

        return;
    }

    $userB = User::withoutGlobalScopes()
        ->where('id', $detailB->user_id)
        ->where('company_id', $companyB->id)
        ->where('status', 'active')
        ->first();

    if ($userB === null) {
        test()->markTestSkipped('Client user for second company not active.');

        return;
    }

    $line = aiWebhookCreateProductLine($companyB);

    $secret = bin2hex(random_bytes(32));

    Company::withoutGlobalScopes()->where('id', $companyA->id)->update([
        'ai_order_webhook_secret' => $secret,
        'ai_order_integration_allow_create' => true,
    ]);

    $response = $this->postJson('/api/integrations/orders', [
        'company_id' => $companyB->id,
        'client_code' => $detailB->client_code,
        'external_event_id' => 'mismatch-test-'.uniqid('', true),
        'items' => [
            [
                'item_name' => $line['name'],
                'quantity' => 1,
                'unit_price' => 0,
            ],
        ],
    ], [
        'X-AI-Webhook-Secret' => $secret,
        'Accept' => 'application/json',
    ]);

    $response->assertUnprocessable();
    $response->assertJsonPath('message', 'company_id must match the company for this integration secret.');
});

it('returns 422 with guidance when client_code is unknown for the company', function (): void {
    if (! Schema::hasColumn('companies', 'ai_order_webhook_secret') || ! Schema::hasColumn('companies', 'ai_order_integration_allow_create')) {
        test()->markTestSkipped('Required company integration columns missing; run migrations.');

        return;
    }

    $company = Company::withoutGlobalScopes()->where('status', 'active')->orderBy('id')->first();
    if ($company === null) {
        test()->markTestSkipped('No active company.');

        return;
    }

    $line = aiWebhookCreateProductLine($company);

    $secret = bin2hex(random_bytes(32));
    Company::withoutGlobalScopes()->where('id', $company->id)->update([
        'ai_order_webhook_secret' => $secret,
        'ai_order_integration_allow_create' => true,
    ]);

    App::setLocale('en');

    $response = $this->postJson('/api/integrations/orders', [
        'company_id' => $company->id,
        'client_code' => '__NO_SUCH_CLIENT_CODE__',
        'external_event_id' => 'client-invalid-'.uniqid('', true),
        'items' => [
            [
                'item_name' => $line['name'],
                'quantity' => 1,
                'unit_price' => 0,
            ],
        ],
    ], [
        'X-AI-Webhook-Secret' => $secret,
        'Accept' => 'application/json',
    ]);

    $response->assertUnprocessable();
    $response->assertJsonPath('error.details.client_code.0', __('modules.orders.apiWebhookClientCodeInvalid'));
});

it('accepts client_id only when user belongs to the same company', function (): void {
    if (! Schema::hasColumn('companies', 'ai_order_webhook_secret') || ! Schema::hasColumn('companies', 'ai_order_integration_allow_create')) {
        test()->markTestSkipped('Required company integration columns missing; run migrations.');

        return;
    }

    $company = Company::withoutGlobalScopes()->where('status', 'active')->orderBy('id')->first();
    if ($company === null) {
        test()->markTestSkipped('No active company.');

        return;
    }

    $user = User::withoutGlobalScopes()
        ->where('company_id', $company->id)
        ->where('status', 'active')
        ->orderBy('id')
        ->first();

    if ($user === null) {
        test()->markTestSkipped('No active user for company.');

        return;
    }

    $line = aiWebhookCreateProductLine($company);

    $secret = bin2hex(random_bytes(32));
    Company::withoutGlobalScopes()->where('id', $company->id)->update([
        'ai_order_webhook_secret' => $secret,
        'ai_order_integration_allow_create' => true,
    ]);

    $response = $this->postJson('/api/integrations/orders', [
        'company_id' => $company->id,
        'client_id' => $user->id,
        'external_event_id' => 'client-id-only-'.uniqid('', true),
        'check_stock' => false,
        'items' => [
            [
                'item_name' => $line['name'],
                'quantity' => 1,
                'unit_price' => 0,
            ],
        ],
    ], [
        'X-AI-Webhook-Secret' => $secret,
        'Accept' => 'application/json',
    ]);

    $response->assertCreated();
});

it('accepts client_code only and persists client_id on the order', function (): void {
    if (! Schema::hasColumn('companies', 'ai_order_webhook_secret') || ! Schema::hasColumn('companies', 'ai_order_integration_allow_create')) {
        test()->markTestSkipped('Required company integration columns missing; run migrations.');

        return;
    }

    if (! Schema::hasTable('client_details')) {
        test()->markTestSkipped('client_details table missing.');

        return;
    }

    $company = Company::withoutGlobalScopes()->where('status', 'active')->orderBy('id')->first();
    if ($company === null) {
        test()->markTestSkipped('No active company.');

        return;
    }

    $detail = ClientDetails::withoutGlobalScopes()
        ->where('company_id', $company->id)
        ->whereNotNull('client_code')
        ->where('client_code', '!=', '')
        ->first();

    if ($detail === null) {
        test()->markTestSkipped('No client_details.client_code for company.');

        return;
    }

    $clientUser = User::withoutGlobalScopes()
        ->where('id', $detail->user_id)
        ->where('company_id', $company->id)
        ->where('status', 'active')
        ->first();

    if ($clientUser === null) {
        test()->markTestSkipped('Client user not active for company.');

        return;
    }

    $line = aiWebhookCreateProductLine($company);

    $secret = bin2hex(random_bytes(32));
    Company::withoutGlobalScopes()->where('id', $company->id)->update([
        'ai_order_webhook_secret' => $secret,
        'ai_order_integration_allow_create' => true,
    ]);

    $externalEventId = 'client-code-only-'.uniqid('', true);

    $response = $this->postJson('/api/integrations/orders', [
        'company_id' => $company->id,
        'client_code' => $detail->client_code,
        'external_event_id' => $externalEventId,
        'check_stock' => false,
        'items' => [
            [
                'item_name' => $line['name'],
                'quantity' => 1,
                'unit_price' => 0,
            ],
        ],
    ], [
        'X-AI-Webhook-Secret' => $secret,
        'Accept' => 'application/json',
    ]);

    $response->assertCreated();
    $orderId = (int) $response->json('data.order_id');
    expect($orderId)->toBeGreaterThan(0);
    expect(Order::withoutGlobalScopes()->find($orderId)?->client_id)->toBe($clientUser->id);
});

it('returns 422 when item_name and sku do not match any product', function (): void {
    if (! Schema::hasColumn('companies', 'ai_order_webhook_secret') || ! Schema::hasColumn('companies', 'ai_order_integration_allow_create')) {
        test()->markTestSkipped('Required company integration columns missing; run migrations.');

        return;
    }

    $company = Company::withoutGlobalScopes()->where('status', 'active')->orderBy('id')->first();
    if ($company === null) {
        test()->markTestSkipped('No active company.');

        return;
    }

    $user = User::withoutGlobalScopes()
        ->where('company_id', $company->id)
        ->where('status', 'active')
        ->orderBy('id')
        ->first();

    if ($user === null) {
        test()->markTestSkipped('No active user for company.');

        return;
    }

    $secret = bin2hex(random_bytes(32));
    Company::withoutGlobalScopes()->where('id', $company->id)->update([
        'ai_order_webhook_secret' => $secret,
        'ai_order_integration_allow_create' => true,
    ]);

    App::setLocale('en');

    $response = $this->postJson('/api/integrations/orders', [
        'company_id' => $company->id,
        'client_id' => $user->id,
        'external_event_id' => 'no-product-'.uniqid('', true),
        'check_stock' => false,
        'items' => [
            [
                'item_name' => '__NO_SUCH_PRODUCT_NAME__',
                'sku' => '__NO_SUCH_SKU__',
                'quantity' => 1,
                'unit_price' => 0,
            ],
        ],
    ], [
        'X-AI-Webhook-Secret' => $secret,
        'Accept' => 'application/json',
    ]);

    $response->assertUnprocessable();
    $details = $response->json('error.details');
    expect($details['items.0.item_name'][0] ?? null)->toBe(__('modules.orders.apiWebhookProductNotFound'));
});

it('resolves product by sku when item_name does not match', function (): void {
    if (! Schema::hasColumn('companies', 'ai_order_webhook_secret') || ! Schema::hasColumn('companies', 'ai_order_integration_allow_create')) {
        test()->markTestSkipped('Required company integration columns missing; run migrations.');

        return;
    }

    $company = Company::withoutGlobalScopes()->where('status', 'active')->orderBy('id')->first();
    if ($company === null) {
        test()->markTestSkipped('No active company.');

        return;
    }

    $user = User::withoutGlobalScopes()
        ->where('company_id', $company->id)
        ->where('status', 'active')
        ->orderBy('id')
        ->first();

    if ($user === null) {
        test()->markTestSkipped('No active user for company.');

        return;
    }

    $line = aiWebhookCreateProductLine($company);

    $secret = bin2hex(random_bytes(32));
    Company::withoutGlobalScopes()->where('id', $company->id)->update([
        'ai_order_webhook_secret' => $secret,
        'ai_order_integration_allow_create' => true,
    ]);

    $response = $this->postJson('/api/integrations/orders', [
        'company_id' => $company->id,
        'client_id' => $user->id,
        'external_event_id' => 'sku-resolve-'.uniqid('', true),
        'check_stock' => false,
        'items' => [
            [
                'item_name' => 'Wrong display name for webhook',
                'sku' => $line['sku'],
                'quantity' => 1,
                'unit_price' => 0,
            ],
        ],
    ], [
        'X-AI-Webhook-Secret' => $secret,
        'Accept' => 'application/json',
    ]);

    $response->assertCreated();
    $product = Product::withoutGlobalScopes()->where('company_id', $company->id)->where('sku', $line['sku'])->first();
    expect($product)->not->toBeNull();
    $orderId = (int) $response->json('data.order_id');
    $order = Order::withoutGlobalScopes()->with('items')->find($orderId);
    expect($order?->items->first()?->product_id)->toBe($product->id);
});
