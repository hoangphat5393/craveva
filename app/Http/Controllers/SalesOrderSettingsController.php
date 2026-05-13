<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateSalesOrderAiIntegrationPermissionsRequest;
use App\Models\ClientDetails;
use App\Models\Company;
use App\Models\Product;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

class SalesOrderSettingsController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();

        $this->pageTitle = 'modules.orders.saleOrderSettings';
        $this->activeSettingMenu = 'sales_order_settings';

        $this->middleware(function ($request, $next) {
            abort_403(! (user()->permission('manage_finance_setting') == 'all' && in_array('orders', user_modules())));

            return $next($request);
        });
    }

    public function index(): View
    {
        $baseUrl = rtrim((string) config('app.url'), '/');
        $globalSecret = (string) config('app.ai_order_webhook_secret', '');

        $activeCompany = company();
        $companyId = $activeCompany instanceof Company ? (int) $activeCompany->id : 0;
        $this->aiOrderWebhookCompanyId = $companyId;
        $this->aiOrderWebhookCompanyName = $activeCompany instanceof Company ? (string) ($activeCompany->company_name ?? '') : '';

        $companyRow = $companyId > 0
            ? Company::withoutGlobalScopes()->select([
                'id',
                'company_name',
                'ai_order_webhook_secret',
                'ai_order_integration_allow_create',
                'ai_order_integration_allow_read',
                'ai_order_integration_allow_update',
                'ai_order_integration_allow_delete',
            ])->find($companyId)
            : null;

        $this->aiOrderIntegrationAllowCreate = $companyRow !== null ? (bool) $companyRow->ai_order_integration_allow_create : true;
        $this->aiOrderIntegrationAllowRead = $companyRow !== null ? (bool) $companyRow->ai_order_integration_allow_read : false;
        $this->aiOrderIntegrationAllowUpdate = $companyRow !== null ? (bool) $companyRow->ai_order_integration_allow_update : false;
        $this->aiOrderIntegrationAllowDelete = $companyRow !== null ? (bool) $companyRow->ai_order_integration_allow_delete : false;

        $companySecret = ($companyRow !== null && $companyRow->ai_order_webhook_secret !== null && $companyRow->ai_order_webhook_secret !== '')
            ? (string) $companyRow->ai_order_webhook_secret
            : '';

        $this->aiOrderRestOrdersUrl = null;
        if ($companySecret !== '') {
            $this->aiOrderRestOrdersUrl = Route::has('api.integrations.orders.store')
                ? route('api.integrations.orders.store', [], true)
                : $baseUrl.'/api/integrations/orders';
        }
        $this->aiOrderRestPostmanExamplePost = null;
        $this->aiOrderRestPostmanExampleGet = null;
        $this->aiOrderRestPostmanExamplePatch = null;
        $this->aiOrderRestPostmanExamplePut = null;
        $this->aiOrderRestPostmanExampleDelete = null;
        $this->aiOrderRestCurlExamplePost = null;
        $this->aiOrderRestCurlExampleGet = null;
        $this->aiOrderRestCurlExamplePatch = null;
        $this->aiOrderRestCurlExamplePut = null;
        $this->aiOrderRestCurlExampleDelete = null;

        $this->aiOrderWebhookSecretConfigured = $companySecret !== '';
        $this->aiOrderGlobalSecretConfigured = $globalSecret !== '';

        $this->aiOrderWebhookBaseUrl = $baseUrl;

        $exampleAiOrderPostPayload = null;
        if ($companyId > 0 && $this->aiOrderRestOrdersUrl !== null) {
            $exampleAiOrderPostPayload = $this->buildAiOrderExamplePostPayload($companyId);
        }

        if ($this->aiOrderRestOrdersUrl !== null && $companySecret !== '' && $companyId > 0 && $exampleAiOrderPostPayload !== null) {
            $restCollection = $this->aiOrderRestOrdersUrl;
            $restOne = Route::has('api.integrations.orders.show')
                ? route('api.integrations.orders.show', ['orderId' => 'YOUR_ORDER_ID'], true)
                : $restCollection.'/YOUR_ORDER_ID';

            $postPayload = $exampleAiOrderPostPayload;
            $updatePayload = [
                'status' => 'processing',
                'note' => 'Optional note from integration',
            ];

            $jsonPrettyFlags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT;

            $postBodyPretty = json_encode($postPayload, $jsonPrettyFlags);
            $updateBodyPretty = json_encode($updatePayload, $jsonPrettyFlags);

            $this->aiOrderRestPostmanExamplePost = $this->buildAiOrderRestPostmanTemplate('POST', $restCollection, $companySecret, $postBodyPretty);
            $this->aiOrderRestPostmanExampleGet = $this->buildAiOrderRestPostmanTemplate('GET', $restOne, $companySecret, null);
            $this->aiOrderRestPostmanExamplePatch = $this->buildAiOrderRestPostmanTemplate('PATCH', $restOne, $companySecret, $updateBodyPretty);
            $this->aiOrderRestPostmanExamplePut = $this->buildAiOrderRestPostmanTemplate('PUT', $restOne, $companySecret, $updateBodyPretty);
            $this->aiOrderRestPostmanExampleDelete = $this->buildAiOrderRestPostmanTemplate('DELETE', $restOne, $companySecret, null);

            $this->aiOrderRestCurlExamplePost = $this->buildAiOrderRestCurlExample('POST', $restCollection, $companySecret, $postBodyPretty);
            $this->aiOrderRestCurlExampleGet = $this->buildAiOrderRestCurlExample('GET', $restOne, $companySecret, null);
            $this->aiOrderRestCurlExamplePatch = $this->buildAiOrderRestCurlExample('PATCH', $restOne, $companySecret, $updateBodyPretty);
            $this->aiOrderRestCurlExamplePut = $this->buildAiOrderRestCurlExample('PUT', $restOne, $companySecret, $updateBodyPretty);
            $this->aiOrderRestCurlExampleDelete = $this->buildAiOrderRestCurlExample('DELETE', $restOne, $companySecret, null);
        }

        return view('sales-order-settings.index', $this->data);
    }

    public function regenerateWebhookSecret(): RedirectResponse
    {
        $activeCompany = company();
        if (! $activeCompany instanceof Company) {
            abort(403);
        }

        $newSecret = bin2hex(random_bytes(32));

        Company::withoutGlobalScopes()
            ->where('id', $activeCompany->id)
            ->update(['ai_order_webhook_secret' => $newSecret]);

        session(['company' => Company::withoutGlobalScopes()->findOrFail($activeCompany->id)]);

        return redirect()
            ->route('sales-order-settings.index')
            ->with('success', __('modules.orders.apiSecretRegenerated'));
    }

    public function updateIntegrationPermissions(UpdateSalesOrderAiIntegrationPermissionsRequest $request): RedirectResponse
    {
        $activeCompany = company();
        if (! $activeCompany instanceof Company) {
            abort(403);
        }

        Company::withoutGlobalScopes()
            ->where('id', $activeCompany->id)
            ->update([
                'ai_order_integration_allow_create' => $request->boolean('ai_order_integration_allow_create'),
                'ai_order_integration_allow_read' => $request->boolean('ai_order_integration_allow_read'),
                'ai_order_integration_allow_update' => $request->boolean('ai_order_integration_allow_update'),
                'ai_order_integration_allow_delete' => $request->boolean('ai_order_integration_allow_delete'),
            ]);

        session(['company' => Company::withoutGlobalScopes()->findOrFail($activeCompany->id)]);

        return redirect()
            ->route('sales-order-settings.index')
            ->with('success', __('modules.orders.apiCrudSaved'));
    }

    /**
     * @return array{company_id: int, client_code: string, external_event_id: string, check_stock: bool, items: array<int, array{item_name: string, quantity: int, unit_price: float|int, sku?: string}>}
     */
    private function buildAiOrderExamplePostPayload(int $companyId): array
    {
        $defaultClientCode = 'YOUR_CLIENT_CODE';
        $defaultItemName = 'Exact product name from catalog';

        $clientCode = $defaultClientCode;
        if ($companyId > 0 && Schema::hasTable('client_details') && Schema::hasTable('users')) {
            $userIdsInCompany = User::withoutGlobalScopes()
                ->where('company_id', $companyId)
                ->pluck('id');
            if ($userIdsInCompany->isNotEmpty()) {
                $foundCode = ClientDetails::withoutGlobalScopes()
                    ->whereNotNull('client_code')
                    ->where('client_code', '!=', '')
                    ->where(function ($query) use ($companyId, $userIdsInCompany): void {
                        $query->where('client_details.company_id', $companyId)
                            ->orWhereIn('client_details.user_id', $userIdsInCompany);
                    })
                    ->orderBy('client_details.id')
                    ->value('client_code');
                if (is_string($foundCode) && trim($foundCode) !== '') {
                    $clientCode = trim($foundCode);
                }
            }
        }

        $itemName = $defaultItemName;
        $unitPrice = 0;
        $itemSku = null;
        if ($companyId > 0 && Schema::hasTable('products')) {
            $product = Product::withoutGlobalScopes()
                ->where('company_id', $companyId)
                ->whereNotNull('name')
                ->where('name', '!=', '')
                ->orderBy('id')
                ->first(['name', 'price', 'sku']);
            if ($product !== null) {
                $itemName = (string) $product->name;
                $unitPrice = $this->exampleUnitPriceFromProductPrice($product->price);
                $skuRaw = $product->sku ?? null;
                if (is_string($skuRaw) && trim($skuRaw) !== '') {
                    $itemSku = trim($skuRaw);
                }
            }
        }

        $line = [
            'item_name' => $itemName,
            'quantity' => 1,
            'unit_price' => $unitPrice,
        ];
        if ($itemSku !== null) {
            $line['sku'] = $itemSku;
        }

        return [
            'company_id' => $companyId,
            'client_code' => (string) $clientCode,
            'external_event_id' => 'example-event-001',
            'check_stock' => false,
            'items' => [$line],
        ];
    }

    private function exampleUnitPriceFromProductPrice(mixed $price): float|int
    {
        if (is_int($price) || is_float($price)) {
            return $price == (int) $price ? (int) $price : (float) $price;
        }
        if (is_string($price)) {
            $normalized = str_replace([',', ' '], '', $price);
            if (is_numeric($normalized)) {
                $float = (float) $normalized;

                return abs($float - (int) $float) < 0.000001 ? (int) $float : $float;
            }
        }

        return 0;
    }

    private function buildAiOrderRestPostmanTemplate(string $httpMethod, string $fullUrl, string $webhookSecret, ?string $jsonBodyPretty): string
    {
        $lines = [
            $httpMethod.' '.$fullUrl,
            '',
            __('modules.orders.apiRestPostmanSectionHeaders'),
            'Accept: application/json',
            'X-AI-Webhook-Secret: '.$webhookSecret,
        ];

        if ($jsonBodyPretty !== null) {
            $lines[] = 'Content-Type: application/json';
            $lines[] = '';
            $lines[] = __('modules.orders.apiRestPostmanSectionBody');
            $lines[] = $jsonBodyPretty;
        }

        return implode("\n", $lines);
    }

    /**
     * Bash-style curl example: single-quoted words so JSON keeps double quotes (valid RFC JSON).
     * Avoids PHP {@see escapeshellarg()} on Windows where wrapping/escaping can confuse copy-paste into Postman.
     */
    private function buildAiOrderRestCurlExample(string $httpMethod, string $url, string $webhookSecret, ?string $jsonBodyPretty): string
    {
        $parts = [
            'curl -X '.$httpMethod.' '.$this->curlWordSingleQuoted($url),
            '  -H '.$this->curlWordSingleQuoted('Accept: application/json'),
            '  -H '.$this->curlWordSingleQuoted('X-AI-Webhook-Secret: '.$webhookSecret),
        ];

        if ($jsonBodyPretty !== null) {
            $jsonCompact = $this->normalizeAiOrderExampleJsonString($jsonBodyPretty);
            $parts[] = '  -H '.$this->curlWordSingleQuoted('Content-Type: application/json');
            $parts[] = '  -d '.$this->curlWordSingleQuoted($jsonCompact);
        }

        return implode(" \\\n", $parts);
    }

    /**
     * Wrap a string in bash single quotes; internal `'` becomes `'\''`.
     */
    private function curlWordSingleQuoted(string $value): string
    {
        return "'".str_replace("'", "'\\''", $value)."'";
    }

    /**
     * Re-encode pretty JSON to a single-line canonical body for curl `-d` (always valid JSON with quoted keys).
     */
    private function normalizeAiOrderExampleJsonString(string $json): string
    {
        $decoded = json_decode($json, true);
        if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decoded)) {
            return $json;
        }

        $encoded = json_encode($decoded, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return $encoded !== false ? $encoded : $json;
    }
}
