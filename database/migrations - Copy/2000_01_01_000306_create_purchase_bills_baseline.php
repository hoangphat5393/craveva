<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `purchase_bills` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned DEFAULT NULL,
  `purchase_bill_number` int NOT NULL,
  `purchase_vendor_id` int unsigned NOT NULL,
  `bill_date` date DEFAULT NULL,
  `purchase_order_id` int unsigned DEFAULT NULL,
  `discount` decimal(16,2) NOT NULL DEFAULT '0.00',
  `sub_total` decimal(16,2) NOT NULL DEFAULT '0.00',
  `total` decimal(16,2) NOT NULL DEFAULT '0.00',
  `discount_type` enum('percent','fixed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'percent',
  `status` enum('draft','open','paid','partially_paid') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `note` text COLLATE utf8mb4_unicode_ci,
  `credit_note` tinyint(1) NOT NULL DEFAULT '0',
  `due_amount` decimal(8,2) NOT NULL DEFAULT '0.00',
  `added_by` int unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `purchase_bills_company_id_foreign` (`company_id`),
  KEY `purchase_bills_purchase_vendor_id_foreign` (`purchase_vendor_id`),
  KEY `purchase_bills_purchase_order_id_foreign` (`purchase_order_id`),
  KEY `purchase_bills_added_by_foreign` (`added_by`),
  CONSTRAINT `purchase_bills_added_by_foreign` FOREIGN KEY (`added_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `purchase_bills_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `purchase_bills_purchase_order_id_foreign` FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `purchase_bills_purchase_vendor_id_foreign` FOREIGN KEY (`purchase_vendor_id`) REFERENCES `purchase_vendors` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_bills');
    }
};
