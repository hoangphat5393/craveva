<?php

use App\Models\GlobalSetting;
use App\Models\Company;
use App\Models\SmtpSetting;
use App\Models\PaymentGatewayCredentials;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "--- START RESETTING ENCRYPTED FIELDS ---\n";

// 1. GlobalSetting
echo "1. Processing GlobalSetting...\n";
$globalSettings = GlobalSetting::all();
foreach ($globalSettings as $setting) {
    // Update directly via DB to bypass Eloquent casting
    DB::table('global_settings')->where('id', $setting->id)->update([
        'google_map_key' => null,
        'google_client_secret' => null,
        // Add other potential encrypted fields if needed
        'purchase_code' => null,
    ]);
    echo "   -> Reset encrypted fields for GlobalSetting ID: {$setting->id}\n";
}

// 2. Company
echo "2. Processing Company...\n";
// Check if Company has encrypted fields (based on common patterns)
// Usually companies table doesn't have encrypted fields by default in some versions,
// but we check for payment keys just in case they are there.
// Based on migration files, some keys were moved to global_settings.

// 3. SmtpSetting
echo "3. Processing SmtpSetting...\n";
$smtpSettings = SmtpSetting::all();
foreach ($smtpSettings as $smtp) {
    DB::table('smtp_settings')->where('id', $smtp->id)->update([
        'mail_password' => null, // This is usually encrypted
    ]);
    echo "   -> Reset mail_password for SmtpSetting ID: {$smtp->id}\n";
}

// 4. PaymentGatewayCredentials
echo "4. Processing PaymentGatewayCredentials...\n";
if (Schema::hasTable('payment_gateway_credentials')) {
    $credentials = PaymentGatewayCredentials::all();
    foreach ($credentials as $cred) {
        DB::table('payment_gateway_credentials')->where('id', $cred->id)->update([
            'live_stripe_secret' => null,
            'live_razorpay_secret' => null,
            'sandbox_paypal_secret' => null,
            'test_stripe_secret' => null,
            'test_razorpay_secret' => null,
            'test_stripe_webhook_secret' => null,
            'paystack_secret' => null,
            'test_paystack_secret' => null,
            'mollie_api_key' => null,
            'payfast_merchant_key' => null,
            'authorize_transaction_key' => null,
            'square_access_token' => null,
            'test_flutterwave_secret' => null,
            'live_flutterwave_key' => null,
            'test_payfast_merchant_key' => null,
        ]);
        echo "   -> Reset secrets for PaymentGatewayCredentials ID: {$cred->id}\n";
    }
}

echo "--- DONE ---\n";
