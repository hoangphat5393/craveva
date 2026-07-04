<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `purchase_vendor_payments` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned NOT NULL,
  `purchase_vendor_id` int unsigned NOT NULL,
  `payment_date` date DEFAULT NULL,
  `vendor_credit_id` int unsigned DEFAULT NULL,
  `bank_account_id` int unsigned DEFAULT NULL,
  `received_payment` double NOT NULL,
  `status` enum('complete','pending','failed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'complete',
  `excess_payment` double NOT NULL,
  `paid_on` datetime DEFAULT NULL,
  `notify_vendor` tinyint(1) DEFAULT '0',
  `internal_note` text COLLATE utf8mb4_unicode_ci,
  `added_by` int unsigned DEFAULT NULL,
  `last_updated_by` int unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `purchase_vendor_payments_company_id_foreign` (`company_id`),
  KEY `purchase_vendor_payments_purchase_vendor_id_foreign` (`purchase_vendor_id`),
  KEY `purchase_vendor_payments_bank_account_id_foreign` (`bank_account_id`),
  KEY `purchase_vendor_payments_added_by_foreign` (`added_by`),
  KEY `purchase_vendor_payments_last_updated_by_foreign` (`last_updated_by`),
  KEY `purchase_vendor_credits_id_foreign` (`vendor_credit_id`),
  KEY `purchase_vendor_payments_paid_on_index` (`paid_on`),
  CONSTRAINT `purchase_vendor_payments_added_by_foreign` FOREIGN KEY (`added_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `purchase_vendor_payments_bank_account_id_foreign` FOREIGN KEY (`bank_account_id`) REFERENCES `bank_accounts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `purchase_vendor_payments_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `purchase_vendor_payments_last_updated_by_foreign` FOREIGN KEY (`last_updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `purchase_vendor_payments_purchase_vendor_id_foreign` FOREIGN KEY (`purchase_vendor_id`) REFERENCES `purchase_vendors` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_vendor_payments');
    }
};
