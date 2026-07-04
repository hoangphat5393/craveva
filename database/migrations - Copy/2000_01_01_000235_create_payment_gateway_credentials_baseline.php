<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `payment_gateway_credentials` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned DEFAULT NULL,
  `paypal_client_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `paypal_secret` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `paypal_status` enum('active','deactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'deactive',
  `live_stripe_client_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `live_stripe_secret` text COLLATE utf8mb4_unicode_ci,
  `live_stripe_webhook_secret` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `stripe_status` enum('active','deactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'deactive',
  `live_razorpay_key` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `live_razorpay_secret` text COLLATE utf8mb4_unicode_ci,
  `live_razorpay_webhook_secret` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `razorpay_status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'inactive',
  `paypal_mode` enum('sandbox','live') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'sandbox',
  `sandbox_paypal_client_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sandbox_paypal_secret` text COLLATE utf8mb4_unicode_ci,
  `test_stripe_client_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `test_stripe_secret` text COLLATE utf8mb4_unicode_ci,
  `test_stripe_webhook_secret` text COLLATE utf8mb4_unicode_ci,
  `test_razorpay_key` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `test_razorpay_secret` text COLLATE utf8mb4_unicode_ci,
  `test_razorpay_webhook_secret` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `stripe_mode` enum('test','live') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'test',
  `razorpay_mode` enum('test','live') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'test',
  `paystack_key` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `paystack_secret` text COLLATE utf8mb4_unicode_ci,
  `paystack_merchant_email` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `paystack_status` enum('active','deactive') COLLATE utf8mb4_unicode_ci DEFAULT 'deactive',
  `paystack_mode` enum('sandbox','live') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'sandbox',
  `test_paystack_key` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `test_paystack_secret` text COLLATE utf8mb4_unicode_ci,
  `test_paystack_merchant_email` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `paystack_payment_url` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT 'https://api.paystack.co',
  `mollie_api_key` text COLLATE utf8mb4_unicode_ci,
  `mollie_status` enum('active','deactive') COLLATE utf8mb4_unicode_ci DEFAULT 'deactive',
  `payfast_merchant_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payfast_merchant_key` text COLLATE utf8mb4_unicode_ci,
  `payfast_passphrase` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payfast_mode` enum('sandbox','live') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'sandbox',
  `payfast_status` enum('active','deactive') COLLATE utf8mb4_unicode_ci DEFAULT 'deactive',
  `authorize_api_login_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `authorize_transaction_key` text COLLATE utf8mb4_unicode_ci,
  `authorize_environment` enum('sandbox','live') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'sandbox',
  `authorize_status` enum('active','deactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'deactive',
  `square_application_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `square_access_token` text COLLATE utf8mb4_unicode_ci,
  `square_location_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `square_environment` enum('sandbox','production') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'sandbox',
  `square_status` enum('active','deactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'deactive',
  `flutterwave_status` enum('active','deactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'deactive',
  `flutterwave_mode` enum('sandbox','live') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'sandbox',
  `test_flutterwave_key` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `test_flutterwave_secret` text COLLATE utf8mb4_unicode_ci,
  `test_flutterwave_hash` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `live_flutterwave_key` text COLLATE utf8mb4_unicode_ci,
  `live_flutterwave_secret` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `live_flutterwave_hash` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `flutterwave_webhook_secret_hash` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `test_payfast_merchant_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `test_payfast_merchant_key` text COLLATE utf8mb4_unicode_ci,
  `test_payfast_passphrase` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payment_gateway_credentials_company_id_foreign` (`company_id`),
  CONSTRAINT `payment_gateway_credentials_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_gateway_credentials');
    }
};
