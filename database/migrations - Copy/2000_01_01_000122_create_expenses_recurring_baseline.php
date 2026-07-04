<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `expenses_recurring` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned DEFAULT NULL,
  `category_id` bigint unsigned DEFAULT NULL,
  `currency_id` int unsigned DEFAULT NULL,
  `project_id` int unsigned DEFAULT NULL,
  `user_id` int unsigned DEFAULT NULL,
  `created_by` int unsigned DEFAULT NULL,
  `item_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `day_of_month` int DEFAULT '1',
  `day_of_week` int DEFAULT '1',
  `payment_method` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rotation` enum('monthly','weekly','bi-weekly','quarterly','half-yearly','annually','daily') COLLATE utf8mb4_unicode_ci NOT NULL,
  `billing_cycle` int DEFAULT NULL,
  `issue_date` date NOT NULL,
  `next_expense_date` date DEFAULT NULL,
  `unlimited_recurring` tinyint(1) NOT NULL DEFAULT '0',
  `price` decimal(30,2) NOT NULL,
  `bill` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `description` text COLLATE utf8mb4_unicode_ci,
  `added_by` int unsigned DEFAULT NULL,
  `last_updated_by` int unsigned DEFAULT NULL,
  `purchase_from` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `immediate_expense` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `bank_account_id` int unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `expenses_recurring_company_id_foreign` (`company_id`),
  KEY `expenses_recurring_category_id_foreign` (`category_id`),
  KEY `expenses_recurring_currency_id_foreign` (`currency_id`),
  KEY `expenses_recurring_project_id_foreign` (`project_id`),
  KEY `expenses_recurring_user_id_foreign` (`user_id`),
  KEY `expenses_recurring_created_by_foreign` (`created_by`),
  KEY `expenses_recurring_added_by_foreign` (`added_by`),
  KEY `expenses_recurring_last_updated_by_foreign` (`last_updated_by`),
  KEY `expenses_recurring_bank_account_id_foreign` (`bank_account_id`),
  CONSTRAINT `expenses_recurring_added_by_foreign` FOREIGN KEY (`added_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `expenses_recurring_bank_account_id_foreign` FOREIGN KEY (`bank_account_id`) REFERENCES `bank_accounts` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `expenses_recurring_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `expenses_category` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `expenses_recurring_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `expenses_recurring_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `expenses_recurring_currency_id_foreign` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `expenses_recurring_last_updated_by_foreign` FOREIGN KEY (`last_updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `expenses_recurring_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `expenses_recurring_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses_recurring');
    }
};
