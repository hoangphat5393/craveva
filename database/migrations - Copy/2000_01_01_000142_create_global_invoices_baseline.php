<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `global_invoices` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned DEFAULT NULL,
  `currency_id` bigint unsigned DEFAULT NULL,
  `package_id` bigint unsigned DEFAULT NULL,
  `global_subscription_id` int unsigned DEFAULT NULL,
  `offline_method_id` int unsigned DEFAULT NULL,
  `m_payment_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pf_payment_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payfast_plan` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `signature` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `token` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `transaction_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `package_type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sub_total` double DEFAULT NULL,
  `total` double DEFAULT NULL,
  `billing_frequency` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `billing_interval` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `recurring` enum('yes','no') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `plan_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `event_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `order_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subscription_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `invoice_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` double DEFAULT NULL,
  `stripe_invoice_number` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pay_date` datetime DEFAULT NULL,
  `next_pay_date` datetime DEFAULT NULL,
  `gateway_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `global_invoices_company_id_foreign` (`company_id`),
  KEY `global_invoices_currency_id_foreign` (`currency_id`),
  KEY `global_invoices_package_id_foreign` (`package_id`),
  KEY `global_invoices_global_subscription_id_foreign` (`global_subscription_id`),
  KEY `global_invoices_offline_method_id_foreign` (`offline_method_id`),
  CONSTRAINT `global_invoices_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `global_invoices_currency_id_foreign` FOREIGN KEY (`currency_id`) REFERENCES `global_currencies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `global_invoices_global_subscription_id_foreign` FOREIGN KEY (`global_subscription_id`) REFERENCES `global_subscriptions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `global_invoices_offline_method_id_foreign` FOREIGN KEY (`offline_method_id`) REFERENCES `offline_payment_methods` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `global_invoices_package_id_foreign` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('global_invoices');
    }
};
