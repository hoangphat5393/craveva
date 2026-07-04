<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `stripe_setting` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `api_key` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `api_secret` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `webhook_key` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `paypal_client_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `paypal_secret` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `paypal_status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'inactive',
  `stripe_status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'inactive',
  `razorpay_key` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `razorpay_secret` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `razorpay_webhook_secret` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `razorpay_status` enum('active','deactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'deactive',
  `paypal_mode` enum('sandbox','live') COLLATE utf8mb4_unicode_ci NOT NULL,
  `paystack_client_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `paystack_secret` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `paystack_status` enum('active','inactive') COLLATE utf8mb4_unicode_ci DEFAULT 'inactive',
  `paystack_merchant_email` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `paystack_payment_url` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT 'https://api.paystack.co',
  `mollie_api_key` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mollie_status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'inactive',
  `authorize_api_login_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `authorize_transaction_key` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `authorize_signature_key` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `authorize_environment` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `authorize_status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'inactive',
  `payfast_key` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payfast_secret` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payfast_status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'inactive',
  `payfast_salt_passphrase` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payfast_mode` enum('sandbox','live') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'sandbox',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('stripe_setting');
    }
};
