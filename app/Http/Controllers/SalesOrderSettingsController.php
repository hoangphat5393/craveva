<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Contracts\View\View;

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
        $secret = (string) config('app.ai_order_webhook_secret', '');
        $baseUrl = rtrim((string) config('app.url'), '/');
        $webhookUrl = $secret !== '' ? $baseUrl . '/ai-order-webhook/' . $secret : null;

        $this->aiOrderWebhookBaseUrl = $baseUrl;
        $this->aiOrderWebhookUrl = $webhookUrl;
        $this->aiOrderWebhookSecretConfigured = $secret !== '';
        $this->aiOrderWebhookHeaderLine = $secret !== '' ? 'X-AI-Webhook-Secret: ' . $secret : '';

        $activeCompany = company();
        $companyId = $activeCompany instanceof Company ? (int) $activeCompany->id : 0;
        $this->aiOrderWebhookCompanyId = $companyId;
        $this->aiOrderWebhookCompanyName = $activeCompany instanceof Company ? (string) ($activeCompany->company_name ?? '') : '';

        $this->aiOrderWebhookCurlExample = null;
        if ($webhookUrl !== null && $secret !== '' && $companyId > 0) {
            $headerSecret = 'X-AI-Webhook-Secret: ' . $secret;
            $lines = [
                'curl -X POST ' . escapeshellarg($webhookUrl) . ' \\',
                '  -H ' . escapeshellarg('Accept: application/json') . ' \\',
                '  -H ' . escapeshellarg($headerSecret) . ' \\',
                '  -d ' . escapeshellarg('company_id=' . $companyId) . ' \\',
                '  -d ' . escapeshellarg('client_id=YOUR_CLIENT_ID') . ' \\',
                '  -d ' . escapeshellarg('external_event_id=example-event-001') . ' \\',
                '  -d ' . escapeshellarg('items[0][item_name]=Example line') . ' \\',
                '  -d ' . escapeshellarg('items[0][quantity]=1') . ' \\',
                '  -d ' . escapeshellarg('items[0][unit_price]=0'),
            ];
            $this->aiOrderWebhookCurlExample = implode("\n", $lines);
        }

        return view('sales-order-settings.index', $this->data);
    }
}
