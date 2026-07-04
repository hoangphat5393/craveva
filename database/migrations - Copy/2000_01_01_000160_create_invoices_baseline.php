<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `invoices` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned DEFAULT NULL,
  `project_id` int unsigned DEFAULT NULL,
  `client_id` int unsigned DEFAULT NULL,
  `order_id` bigint unsigned DEFAULT NULL,
  `invoice_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `original_invoice_number` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `issue_date` date NOT NULL,
  `due_date` date NOT NULL,
  `sub_total` decimal(30,2) NOT NULL,
  `discount` decimal(30,2) NOT NULL DEFAULT '0.00',
  `discount_type` enum('percent','fixed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'percent',
  `total` decimal(30,2) NOT NULL,
  `currency_id` int unsigned DEFAULT NULL,
  `default_currency_id` int unsigned DEFAULT NULL,
  `exchange_rate` double DEFAULT NULL,
  `status` enum('paid','unpaid','partial','canceled','draft','pending-confirmation') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unpaid',
  `recurring` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `billing_cycle` int DEFAULT NULL,
  `billing_interval` int DEFAULT NULL,
  `billing_frequency` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_original_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `credit_note` tinyint(1) NOT NULL DEFAULT '0',
  `show_shipping_address` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `estimate_id` int unsigned DEFAULT NULL,
  `send_status` tinyint(1) NOT NULL DEFAULT '1',
  `due_amount` decimal(30,2) NOT NULL DEFAULT '0.00',
  `parent_id` int unsigned DEFAULT NULL,
  `invoice_recurring_id` bigint unsigned DEFAULT NULL,
  `created_by` int unsigned DEFAULT NULL,
  `added_by` int unsigned DEFAULT NULL,
  `last_updated_by` int unsigned DEFAULT NULL,
  `hash` text COLLATE utf8mb4_unicode_ci,
  `calculate_tax` enum('after_discount','before_discount') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'after_discount',
  `company_address_id` bigint unsigned DEFAULT NULL,
  `event_id` text COLLATE utf8mb4_unicode_ci,
  `custom_invoice_number` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_status` enum('1','0') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `offline_method_id` int unsigned DEFAULT NULL,
  `transaction_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `invoice_payment_id` bigint unsigned DEFAULT NULL,
  `gateway` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `bank_account_id` int unsigned DEFAULT NULL,
  `last_viewed` timestamp NULL DEFAULT NULL,
  `ip_address` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quickbooks_invoice_id` int DEFAULT NULL,
  `is_timelog_invoice` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoices_invoice_number_company_id_unique` (`invoice_number`,`company_id`),
  UNIQUE KEY `invoices_transaction_id_unique` (`transaction_id`),
  KEY `invoices_company_id_foreign` (`company_id`),
  KEY `invoices_project_id_foreign` (`project_id`),
  KEY `invoices_client_id_foreign` (`client_id`),
  KEY `invoices_order_id_foreign` (`order_id`),
  KEY `invoices_due_date_index` (`due_date`),
  KEY `invoices_currency_id_foreign` (`currency_id`),
  KEY `invoices_estimate_id_foreign` (`estimate_id`),
  KEY `invoices_parent_id_foreign` (`parent_id`),
  KEY `invoices_invoice_recurring_id_foreign` (`invoice_recurring_id`),
  KEY `invoices_created_by_foreign` (`created_by`),
  KEY `invoices_added_by_foreign` (`added_by`),
  KEY `invoices_last_updated_by_foreign` (`last_updated_by`),
  KEY `invoices_company_address_id_foreign` (`company_address_id`),
  KEY `invoices_bank_account_id_foreign` (`bank_account_id`),
  KEY `invoices_default_currency_id_foreign` (`default_currency_id`),
  KEY `payments_offline_method_id_foreign` (`offline_method_id`),
  KEY `invoices_invoice_payment_id_foreign` (`invoice_payment_id`),
  CONSTRAINT `invoices_added_by_foreign` FOREIGN KEY (`added_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `invoices_bank_account_id_foreign` FOREIGN KEY (`bank_account_id`) REFERENCES `bank_accounts` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `invoices_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `invoices_company_address_id_foreign` FOREIGN KEY (`company_address_id`) REFERENCES `company_addresses` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `invoices_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `invoices_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `invoices_currency_id_foreign` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `invoices_default_currency_id_foreign` FOREIGN KEY (`default_currency_id`) REFERENCES `currencies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `invoices_estimate_id_foreign` FOREIGN KEY (`estimate_id`) REFERENCES `estimates` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `invoices_invoice_payment_id_foreign` FOREIGN KEY (`invoice_payment_id`) REFERENCES `invoice_payment_details` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `invoices_invoice_recurring_id_foreign` FOREIGN KEY (`invoice_recurring_id`) REFERENCES `invoice_recurring` (`id`) ON DELETE SET NULL,
  CONSTRAINT `invoices_last_updated_by_foreign` FOREIGN KEY (`last_updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `invoices_offline_method_id_foreign` FOREIGN KEY (`offline_method_id`) REFERENCES `offline_payment_methods` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `invoices_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `invoices_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `invoices_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
