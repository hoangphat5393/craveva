<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `bank_transactions` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned DEFAULT NULL,
  `bank_account_id` int unsigned DEFAULT NULL,
  `payment_id` int unsigned DEFAULT NULL,
  `invoice_id` int unsigned DEFAULT NULL,
  `expense_id` int unsigned DEFAULT NULL,
  `amount` decimal(30,2) DEFAULT NULL,
  `type` enum('Cr','Dr') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Cr',
  `added_by` int unsigned DEFAULT NULL,
  `last_updated_by` int unsigned DEFAULT NULL,
  `memo` text COLLATE utf8mb4_unicode_ci,
  `transaction_relation` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `transaction_related_to` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title` text COLLATE utf8mb4_unicode_ci,
  `transaction_date` date DEFAULT NULL,
  `bank_balance` decimal(30,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `purchase_payment_id` int unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `bank_transactions_company_id_foreign` (`company_id`),
  KEY `bank_transactions_bank_account_id_foreign` (`bank_account_id`),
  KEY `bank_transactions_payment_id_foreign` (`payment_id`),
  KEY `bank_transactions_invoice_id_foreign` (`invoice_id`),
  KEY `bank_transactions_expense_id_foreign` (`expense_id`),
  KEY `bank_transactions_added_by_foreign` (`added_by`),
  KEY `bank_transactions_last_updated_by_foreign` (`last_updated_by`),
  KEY `bank_transactions_purchase_payment_id_foreign` (`purchase_payment_id`),
  CONSTRAINT `bank_transactions_added_by_foreign` FOREIGN KEY (`added_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `bank_transactions_bank_account_id_foreign` FOREIGN KEY (`bank_account_id`) REFERENCES `bank_accounts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `bank_transactions_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `bank_transactions_expense_id_foreign` FOREIGN KEY (`expense_id`) REFERENCES `expenses` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `bank_transactions_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `bank_transactions_last_updated_by_foreign` FOREIGN KEY (`last_updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `bank_transactions_payment_id_foreign` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `bank_transactions_purchase_payment_id_foreign` FOREIGN KEY (`purchase_payment_id`) REFERENCES `purchase_vendor_payments` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_transactions');
    }
};
