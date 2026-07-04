<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `purchase_vendor_credit_histories` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned DEFAULT NULL,
  `purchase_credit_id` int unsigned DEFAULT NULL,
  `purchase_vendor_id` int unsigned DEFAULT NULL,
  `amount` int DEFAULT NULL,
  `user_id` int unsigned DEFAULT NULL,
  `label` text COLLATE utf8mb4_unicode_ci,
  `details` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `purchase_vendor_credit_histories_company_id_foreign` (`company_id`),
  KEY `purchase_vendor_credit_histories_purchase_credit_id_foreign` (`purchase_credit_id`),
  KEY `purchase_vendor_credit_histories_purchase_vendor_id_foreign` (`purchase_vendor_id`),
  KEY `purchase_vendor_credit_histories_user_id_foreign` (`user_id`),
  CONSTRAINT `purchase_vendor_credit_histories_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `purchase_vendor_credit_histories_purchase_credit_id_foreign` FOREIGN KEY (`purchase_credit_id`) REFERENCES `purchase_vendor_credits` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `purchase_vendor_credit_histories_purchase_vendor_id_foreign` FOREIGN KEY (`purchase_vendor_id`) REFERENCES `purchase_vendors` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `purchase_vendor_credit_histories_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_vendor_credit_histories');
    }
};
