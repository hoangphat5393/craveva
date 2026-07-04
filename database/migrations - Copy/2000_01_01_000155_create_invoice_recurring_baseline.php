<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `invoice_recurring` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned DEFAULT NULL,
  `currency_id` int unsigned DEFAULT NULL,
  `project_id` int unsigned DEFAULT NULL,
  `client_id` int unsigned DEFAULT NULL,
  `user_id` int unsigned DEFAULT NULL,
  `created_by` int unsigned DEFAULT NULL,
  `issue_date` date NOT NULL,
  `next_invoice_date` date DEFAULT NULL,
  `due_date` date NOT NULL,
  `sub_total` decimal(30,2) NOT NULL DEFAULT '0.00',
  `total` decimal(30,2) NOT NULL DEFAULT '0.00',
  `discount` decimal(30,2) NOT NULL DEFAULT '0.00',
  `discount_type` enum('percent','fixed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'percent',
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `file` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_original_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `show_shipping_address` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `day_of_month` int DEFAULT '1',
  `day_of_week` int DEFAULT '1',
  `payment_method` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rotation` enum('monthly','weekly','bi-weekly','quarterly','half-yearly','annually','daily') COLLATE utf8mb4_unicode_ci NOT NULL,
  `billing_cycle` int DEFAULT NULL,
  `client_can_stop` tinyint(1) NOT NULL DEFAULT '1',
  `unlimited_recurring` tinyint(1) NOT NULL DEFAULT '0',
  `deleted_at` datetime DEFAULT NULL,
  `shipping_address` text COLLATE utf8mb4_unicode_ci,
  `added_by` int unsigned DEFAULT NULL,
  `last_updated_by` int unsigned DEFAULT NULL,
  `calculate_tax` enum('after_discount','before_discount') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'after_discount',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `immediate_invoice` tinyint(1) NOT NULL DEFAULT '0',
  `bank_account_id` int unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `invoice_recurring_company_id_foreign` (`company_id`),
  KEY `invoice_recurring_currency_id_foreign` (`currency_id`),
  KEY `invoice_recurring_project_id_foreign` (`project_id`),
  KEY `invoice_recurring_client_id_foreign` (`client_id`),
  KEY `invoice_recurring_user_id_foreign` (`user_id`),
  KEY `invoice_recurring_created_by_foreign` (`created_by`),
  KEY `invoice_recurring_added_by_foreign` (`added_by`),
  KEY `invoice_recurring_last_updated_by_foreign` (`last_updated_by`),
  KEY `invoice_recurring_bank_account_id_foreign` (`bank_account_id`),
  CONSTRAINT `invoice_recurring_added_by_foreign` FOREIGN KEY (`added_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `invoice_recurring_bank_account_id_foreign` FOREIGN KEY (`bank_account_id`) REFERENCES `bank_accounts` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `invoice_recurring_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `invoice_recurring_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `invoice_recurring_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `invoice_recurring_currency_id_foreign` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `invoice_recurring_last_updated_by_foreign` FOREIGN KEY (`last_updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `invoice_recurring_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `invoice_recurring_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_recurring');
    }
};
