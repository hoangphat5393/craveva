<?php

namespace App\Http\Controllers\Payment;

use App\Helper\Reply;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Traits\MakePaymentTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Stripe\Stripe;

class StripeController extends Controller
{
    use MakePaymentTrait;

    /**
     * Create a new controller instance.
     *
     * Không gọi DB / Eloquent có cast `encrypted` ở đây — `route:list` và CLI khởi tạo controller
     * để đọc middleware. Stripe key được set trong từng action qua `configureStripeForInvoice()`.
     */
    public function __construct()
    {
        parent::__construct();

        $this->pageTitle = __('app.stripe');
    }

    /**
     * Ưu tiên secret theo company của invoice; fallback `Config` (cashier) — đã set bởi CustomConfigProvider.
     */
    private function configureStripeForInvoice(Invoice $invoice): void
    {
        try {
            $credentials = $invoice->company?->paymentGatewayCredentials;

            if ($credentials !== null) {
                $secret = $credentials->stripe_mode === 'test'
                    ? $credentials->test_stripe_secret
                    : $credentials->live_stripe_secret;
                if (is_string($secret) && $secret !== '') {
                    Stripe::setApiKey($secret);

                    return;
                }
            }
        } catch (\Throwable $e) {
            Log::debug('StripeController: company Stripe credentials unavailable, using cashier config', [
                'invoice_id' => $invoice->id,
                'message' => $e->getMessage(),
            ]);
        }

        $fallback = Config::get('cashier.secret');
        if (is_string($fallback) && $fallback !== '') {
            Stripe::setApiKey($fallback);
        }
    }

    /**
     * Store a details of payment with paypal.
     *
     * @return \Illuminate\Http\Response
     */
    public function paymentWithStripe(Request $request, $id)
    {
        $redirectRoute = 'invoices.show';
        $invoice = Invoice::with('company.paymentGatewayCredentials')->findOrFail($id);
        $param = 'invoice';
        $paymentIntentId = $request->paymentIntentId;

        if (isset($request->type) && $request->type == 'order') {
            $redirectRoute = 'orders.show';
            $param = 'order';
            $invoice = Invoice::with('company.paymentGatewayCredentials')
                ->where('order_id', $id)
                ->latest()
                ->firstOrFail();
        }

        $this->configureStripeForInvoice($invoice);

        $this->makePayment('Stripe', $invoice->amountDue(), $invoice, $paymentIntentId, 'complete');
        $invoice->status = 'paid';
        $invoice->save();

        return $this->makeStripePayment($redirectRoute, $id, $param);
    }

    public function paymentWithStripePublic(Request $request, $hash)
    {
        $redirectRoute = 'front.invoice';
        $paymentIntentId = $request->paymentIntentId;

        $invoice = Invoice::with('company.paymentGatewayCredentials')->where('hash', $hash)->firstOrFail();

        $this->configureStripeForInvoice($invoice);

        $this->makePayment('Stripe', $invoice->amountDue(), $invoice, $paymentIntentId, 'complete');
        $invoice->status = 'paid';
        $invoice->save();

        return $this->makeStripePayment($redirectRoute, $hash, 'hash');
    }

    private function makeStripePayment($redirectRoute, $id, $param = null)
    {
        $param = $param ?? 'invoice';
        $signedUrl = URL::temporarySignedRoute($redirectRoute, now()->addDays(\App\Models\GlobalSetting::SIGNED_ROUTE_EXPIRY), [$param => $id]);
        Session::put('success', __('messages.paymentSuccessful'));

        return Reply::redirect($signedUrl, __('messages.paymentSuccessful'));
    }
}
