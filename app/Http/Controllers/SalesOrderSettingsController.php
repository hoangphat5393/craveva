<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

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
            ? Company::withoutGlobalScopes()->select(['id', 'company_name', 'ai_order_webhook_secret'])->find($companyId)
            : null;

        $companySecret = ($companyRow !== null && $companyRow->ai_order_webhook_secret !== null && $companyRow->ai_order_webhook_secret !== '')
            ? (string) $companyRow->ai_order_webhook_secret
            : '';

        $effectiveSecret = $companySecret !== '' ? $companySecret : $globalSecret;

        $this->aiOrderWebhookCompanySecretConfigured = $companySecret !== '';
        $this->aiOrderWebhookLegacyGlobalConfigured = $globalSecret !== '';
        $this->aiOrderWebhookUsingLegacyGlobalFallback = $companySecret === '' && $globalSecret !== '';
        $this->aiOrderWebhookSecretConfigured = $effectiveSecret !== '';

        $this->aiOrderWebhookBaseUrl = $baseUrl;
        $this->aiOrderWebhookUrl = $effectiveSecret !== '' ? $baseUrl.'/ai-order-webhook/'.$effectiveSecret : null;
        $this->aiOrderWebhookHeaderLine = $effectiveSecret !== '' ? 'X-AI-Webhook-Secret: '.$effectiveSecret : '';

        $this->aiOrderWebhookCurlExample = null;
        if ($this->aiOrderWebhookUrl !== null && $effectiveSecret !== '' && $companyId > 0) {
            $headerSecret = 'X-AI-Webhook-Secret: '.$effectiveSecret;
            $lines = [
                'curl -X POST '.escapeshellarg($this->aiOrderWebhookUrl).' \\',
                '  -H '.escapeshellarg('Accept: application/json').' \\',
                '  -H '.escapeshellarg($headerSecret).' \\',
                '  -d '.escapeshellarg('company_id='.$companyId).' \\',
                '  -d '.escapeshellarg('client_code=YOUR_CLIENT_CODE').' \\',
                '  -d '.escapeshellarg('external_event_id=example-event-001').' \\',
                '  -d '.escapeshellarg('items[0][item_name]=Example line').' \\',
                '  -d '.escapeshellarg('items[0][quantity]=1').' \\',
                '  -d '.escapeshellarg('items[0][unit_price]=0'),
            ];
            $this->aiOrderWebhookCurlExample = implode("\n", $lines);
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
}
