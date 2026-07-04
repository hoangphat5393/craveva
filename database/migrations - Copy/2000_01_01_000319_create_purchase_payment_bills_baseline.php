<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `purchase_payment_bills` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `purchase_vendor_payment_id` int unsigned DEFAULT NULL,
  `purchase_vendor_id` int unsigned DEFAULT NULL,
  `purchase_bill_id` int unsigned DEFAULT NULL,
  `purchase_vendor_credits_id` int unsigned DEFAULT NULL,
  `gateway` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total_paid` decimal(16,2) DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `purchase_payment_bills_purchase_vendor_payment_id_foreign` (`purchase_vendor_payment_id`),
  KEY `purchase_payment_bills_purchase_vendor_id_foreign` (`purchase_vendor_id`),
  KEY `purchase_payment_bills_purchase_bill_id_foreign` (`purchase_bill_id`),
  KEY `purchase_payment_bills_purchase_vendor_credits_id_foreign` (`purchase_vendor_credits_id`),
  CONSTRAINT `purchase_payment_bills_purchase_bill_id_foreign` FOREIGN KEY (`purchase_bill_id`) REFERENCES `purchase_bills` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `purchase_payment_bills_purchase_vendor_credits_id_foreign` FOREIGN KEY (`purchase_vendor_credits_id`) REFERENCES `purchase_vendor_credits` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `purchase_payment_bills_purchase_vendor_id_foreign` FOREIGN KEY (`purchase_vendor_id`) REFERENCES `purchase_vendors` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `purchase_payment_bills_purchase_vendor_payment_id_foreign` FOREIGN KEY (`purchase_vendor_payment_id`) REFERENCES `purchase_vendor_payments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_payment_bills');
    }
};
