<?php

declare(strict_types=1);

use App\Models\ClientDetails;
use App\Models\Company;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

uses(DatabaseTransactions::class);

it('registers named REST routes for AI order integrations', function (): void {
    expect(Route::has('api.integrations.orders.store'))->toBeTrue();
    expect(Route::has('api.integrations.orders.show'))->toBeTrue();
    expect(Route::has('api.integrations.orders.destroy'))->toBeTrue();
});

it('exposes a no-auth GET probe at api/integrations/__route_probe', function (): void {
    expect(Route::has('api.integrations.route_probe'))->toBeTrue();

    $this->getJson('/api/integrations/__route_probe')
        ->assertOk()
        ->assertJsonPath('ok', true);
});

/**
 * @return array{name: string, sku: string}
 */
function aiRestIntegrationCreateProductLine(Company $company): array
{
    if (! Schema::hasTable('products')) {
        test()->markTestSkipped('products table missing.');
    }

    $name = 'AI REST Line '.uniqid('', true);
    $sku = 'REST-SKU-'.substr(str_replace('.', '', uniqid('', true)), 0, 12);

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

it('returns 401 for REST when token matches only global env secret', function (): void {
    if (! Schema::hasColumn('companies', 'ai_order_integration_allow_read')) {
        test()->markTestSkipped('Integration permission columns missing; run migrations.');

        return;
    }

    $previousGlobal = config('app.ai_order_webhook_secret');

    try {
        config(['app.ai_order_webhook_secret' => 'global-only-secret-token']);

        $response = $this->postJson('/api/integrations/orders', [], [
            'Authorization' => 'Bearer global-only-secret-token',
            'Accept' => 'application/json',
        ]);

        $response->assertUnauthorized();
        $response->assertJsonPath('code', 'INTEGRATION_REST_REQUIRES_COMPANY_SECRET');
    } finally {
        config(['app.ai_order_webhook_secret' => $previousGlobal]);
    }
});

it('returns 403 for GET when read integration is disabled', function (): void {
    if (! Schema::hasColumn('companies', 'ai_order_webhook_secret') || ! Schema::hasColumn('companies', 'ai_order_integration_allow_read')) {
        test()->markTestSkipped('Required columns missing; run migrations.');

        return;
    }

    $company = Company::withoutGlobalScopes()->where('status', 'active')->orderBy('id')->first();
    if ($company === null) {
        test()->markTestSkipped('No active company.');

        return;
    }

    $secret = bin2hex(random_bytes(32));

    Company::withoutGlobalScopes()->where('id', $company->id)->update([
        'ai_order_webhook_secret' => $secret,
        'ai_order_integration_allow_create' => true,
        'ai_order_integration_allow_read' => false,
        'ai_order_integration_allow_update' => false,
        'ai_order_integration_allow_delete' => false,
    ]);

    $response = $this->getJson('/api/integrations/orders/1', [
        'X-AI-Webhook-Secret' => $secret,
        'Accept' => 'application/json',
    ]);

    $response->assertForbidden();
    $response->assertJsonPath('code', 'INTEGRATION_METHOD_DISABLED');
});

it('creates an order via REST POST when create is enabled', function (): void {
    if (! Schema::hasColumn('companies', 'ai_order_webhook_secret') || ! Schema::hasColumn('companies', 'ai_order_integration_allow_create')) {
        test()->markTestSkipped('Required columns missing; run migrations.');

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

    $userIds = User::withoutGlobalScopes()->where('company_id', $company->id)->pluck('id');
    $detail = ClientDetails::withoutGlobalScopes()
        ->whereIn('user_id', $userIds)
        ->whereNotNull('client_code')
        ->where('client_code', '!=', '')
        ->first();

    if ($detail === null) {
        test()->markTestSkipped('No client_details.client_code for company.');

        return;
    }

    $user = User::withoutGlobalScopes()
        ->where('id', $detail->user_id)
        ->where('company_id', $company->id)
        ->where('status', 'active')
        ->first();

    if ($user === null) {
        test()->markTestSkipped('Client user not active.');

        return;
    }

    $line = aiRestIntegrationCreateProductLine($company);
    $secret = bin2hex(random_bytes(32));

    Company::withoutGlobalScopes()->where('id', $company->id)->update([
        'ai_order_webhook_secret' => $secret,
        'ai_order_integration_allow_create' => true,
        'ai_order_integration_allow_read' => true,
        'ai_order_integration_allow_update' => true,
        'ai_order_integration_allow_delete' => true,
    ]);

    $response = $this->postJson('/api/integrations/orders', [
        'company_id' => $company->id,
        'client_code' => $detail->client_code,
        'external_event_id' => 'rest-create-'.uniqid('', true),
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
    $response->assertJsonPath('status', 'success');
    expect($response->json('data.order_id'))->toBeInt();
});

it('returns 403 for legacy webhook POST when create is disabled', function (): void {
    if (! Schema::hasColumn('companies', 'ai_order_webhook_secret') || ! Schema::hasColumn('companies', 'ai_order_integration_allow_create')) {
        test()->markTestSkipped('Required columns missing; run migrations.');

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

    $userIds = User::withoutGlobalScopes()->where('company_id', $company->id)->pluck('id');
    $detail = ClientDetails::withoutGlobalScopes()
        ->whereIn('user_id', $userIds)
        ->whereNotNull('client_code')
        ->where('client_code', '!=', '')
        ->first();

    if ($detail === null) {
        test()->markTestSkipped('No client_details.client_code for company.');

        return;
    }

    $line = aiRestIntegrationCreateProductLine($company);
    $secret = bin2hex(random_bytes(32));

    Company::withoutGlobalScopes()->where('id', $company->id)->update([
        'ai_order_webhook_secret' => $secret,
        'ai_order_integration_allow_create' => false,
        'ai_order_integration_allow_read' => false,
        'ai_order_integration_allow_update' => false,
        'ai_order_integration_allow_delete' => false,
    ]);

    $response = $this->postJson('/ai-order-webhook/'.$secret, [
        'company_id' => $company->id,
        'client_code' => $detail->client_code,
        'external_event_id' => 'legacy-blocked-'.uniqid('', true),
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

    $response->assertForbidden();
    $response->assertJsonPath('code', 'INTEGRATION_METHOD_DISABLED');
});
