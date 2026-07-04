<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `payments` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned DEFAULT NULL,
  `project_id` int unsigned DEFAULT NULL,
  `invoice_id` int unsigned DEFAULT NULL,
  `order_id` bigint unsigned DEFAULT NULL,
  `credit_notes_id` int unsigned DEFAULT NULL,
  `amount` decimal(30,2) NOT NULL,
  `gateway` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `transaction_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `currency_id` int unsigned DEFAULT NULL,
  `default_currency_id` int unsigned DEFAULT NULL,
  `exchange_rate` double DEFAULT NULL,
  `plan_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `event_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('complete','pending','failed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `paid_on` datetime DEFAULT NULL,
  `remarks` text COLLATE utf8mb4_unicode_ci,
  `offline_method_id` int unsigned DEFAULT NULL,
  `bill` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `added_by` int unsigned DEFAULT NULL,
  `last_updated_by` int unsigned DEFAULT NULL,
  `payment_gateway_response` text COLLATE utf8mb4_unicode_ci COMMENT 'null = success',
  `payload_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `bank_account_id` int unsigned DEFAULT NULL,
  `quickbooks_payment_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payments_plan_id_unique` (`plan_id`),
  UNIQUE KEY `payments_event_id_company_id_unique` (`event_id`,`company_id`),
  UNIQUE KEY `payments_transaction_id_company_id_unique` (`transaction_id`,`company_id`),
  KEY `payments_company_id_foreign` (`company_id`),
  KEY `payments_project_id_foreign` (`project_id`),
  KEY `payments_invoice_id_foreign` (`invoice_id`),
  KEY `payments_order_id_foreign` (`order_id`),
  KEY `payments_credit_notes_id_foreign` (`credit_notes_id`),
  KEY `payments_currency_id_foreign` (`currency_id`),
  KEY `payments_paid_on_index` (`paid_on`),
  KEY `payments_offline_method_id_foreign` (`offline_method_id`),
  KEY `payments_added_by_foreign` (`added_by`),
  KEY `payments_last_updated_by_foreign` (`last_updated_by`),
  KEY `payments_bank_account_id_foreign` (`bank_account_id`),
  KEY `payments_default_currency_id_foreign` (`default_currency_id`),
  CONSTRAINT `payments_added_by_foreign` FOREIGN KEY (`added_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `payments_bank_account_id_foreign` FOREIGN KEY (`bank_account_id`) REFERENCES `bank_accounts` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `payments_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `payments_credit_notes_id_foreign` FOREIGN KEY (`credit_notes_id`) REFERENCES `credit_notes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `payments_currency_id_foreign` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `payments_default_currency_id_foreign` FOREIGN KEY (`default_currency_id`) REFERENCES `currencies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `payments_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `payments_last_updated_by_foreign` FOREIGN KEY (`last_updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `payments_offline_method_id_foreign` FOREIGN KEY (`offline_method_id`) REFERENCES `offline_payment_methods` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `payments_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `payments_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
