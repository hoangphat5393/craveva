<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `purchase_vendor_credits` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned DEFAULT NULL,
  `vendor_id` int unsigned DEFAULT NULL,
  `credit_note_no` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `credit_date` date DEFAULT NULL,
  `currency_id` int unsigned NOT NULL,
  `sub_total` decimal(16,2) NOT NULL,
  `discount` double NOT NULL DEFAULT '0',
  `discount_type` enum('percent','fixed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'percent',
  `total` decimal(16,2) NOT NULL,
  `status` enum('open','closed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
  `hash` text COLLATE utf8mb4_unicode_ci,
  `calculate_tax` enum('after_discount','before_discount') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'after_discount',
  `note` text COLLATE utf8mb4_unicode_ci,
  `send_status` tinyint(1) NOT NULL DEFAULT '1',
  `product_id` int unsigned DEFAULT NULL,
  `bill_id` int unsigned DEFAULT NULL,
  `payment_id` int unsigned DEFAULT NULL,
  `added_by` int unsigned DEFAULT NULL,
  `last_updated_by` int unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `purchase_vendor_credits_company_id_foreign` (`company_id`),
  KEY `purchase_vendor_credits_vendor_id_foreign` (`vendor_id`),
  KEY `purchase_vendor_credits_currency_id_foreign` (`currency_id`),
  KEY `purchase_vendor_credits_product_id_foreign` (`product_id`),
  KEY `purchase_vendor_credits_bill_id_foreign` (`bill_id`),
  KEY `purchase_vendor_credits_payment_id_foreign` (`payment_id`),
  KEY `purchase_vendor_credits_added_by_foreign` (`added_by`),
  KEY `purchase_vendor_credits_last_updated_by_foreign` (`last_updated_by`),
  CONSTRAINT `purchase_vendor_credits_added_by_foreign` FOREIGN KEY (`added_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `purchase_vendor_credits_bill_id_foreign` FOREIGN KEY (`bill_id`) REFERENCES `purchase_bills` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `purchase_vendor_credits_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `purchase_vendor_credits_currency_id_foreign` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `purchase_vendor_credits_last_updated_by_foreign` FOREIGN KEY (`last_updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `purchase_vendor_credits_payment_id_foreign` FOREIGN KEY (`payment_id`) REFERENCES `purchase_vendor_payments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `purchase_vendor_credits_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `purchase_vendor_credits_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `purchase_vendors` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_vendor_credits');
    }
};
