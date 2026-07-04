<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `packages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `currency_id` bigint unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(16,2) DEFAULT NULL,
  `description` varchar(1000) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `max_storage_size` int NOT NULL,
  `max_file_size` int unsigned NOT NULL DEFAULT '0',
  `annual_price` double DEFAULT NULL,
  `monthly_price` double DEFAULT NULL,
  `billing_cycle` tinyint unsigned NOT NULL DEFAULT '0',
  `max_employees` int unsigned NOT NULL DEFAULT '0',
  `sort` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `module_in_package` varchar(1000) COLLATE utf8mb4_unicode_ci NOT NULL,
  `stripe_annual_plan_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `stripe_monthly_plan_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `razorpay_annual_plan_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `razorpay_monthly_plan_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `default` enum('yes','no','trial','lifetime') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `paystack_monthly_plan_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `paystack_annual_plan_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_private` tinyint(1) NOT NULL,
  `storage_unit` enum('gb','mb') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'mb',
  `is_recommended` tinyint(1) NOT NULL DEFAULT '0',
  `is_free` tinyint(1) NOT NULL DEFAULT '0',
  `is_auto_renew` tinyint(1) NOT NULL DEFAULT '0',
  `monthly_status` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT '1',
  `annual_status` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT '1',
  `package` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `packages_currency_id_foreign` (`currency_id`),
  CONSTRAINT `packages_currency_id_foreign` FOREIGN KEY (`currency_id`) REFERENCES `global_currencies` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};
