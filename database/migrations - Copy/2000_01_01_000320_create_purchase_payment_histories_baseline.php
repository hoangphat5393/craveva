<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `purchase_payment_histories` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned DEFAULT NULL,
  `purchase_vendor_id` int unsigned DEFAULT NULL,
  `purchase_payment_id` int unsigned DEFAULT NULL,
  `purchase_order_id` int unsigned DEFAULT NULL,
  `purchase_order` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `purchase_bill_id` int unsigned DEFAULT NULL,
  `amount` decimal(16,2) DEFAULT '0.00',
  `user_id` int unsigned DEFAULT NULL,
  `details` text COLLATE utf8mb4_unicode_ci,
  `label` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `purchase_payment_histories_company_id_foreign` (`company_id`),
  KEY `purchase_payment_histories_purchase_vendor_id_foreign` (`purchase_vendor_id`),
  KEY `purchase_payment_histories_purchase_payment_id_foreign` (`purchase_payment_id`),
  KEY `purchase_payment_histories_purchase_order_id_foreign` (`purchase_order_id`),
  KEY `purchase_payment_histories_purchase_bill_id_foreign` (`purchase_bill_id`),
  KEY `purchase_payment_histories_user_id_foreign` (`user_id`),
  CONSTRAINT `purchase_payment_histories_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `purchase_payment_histories_purchase_bill_id_foreign` FOREIGN KEY (`purchase_bill_id`) REFERENCES `purchase_bills` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `purchase_payment_histories_purchase_order_id_foreign` FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `purchase_payment_histories_purchase_payment_id_foreign` FOREIGN KEY (`purchase_payment_id`) REFERENCES `purchase_vendor_payments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `purchase_payment_histories_purchase_vendor_id_foreign` FOREIGN KEY (`purchase_vendor_id`) REFERENCES `purchase_vendors` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `purchase_payment_histories_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_payment_histories');
    }
};
