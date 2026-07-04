<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `affiliate_payouts` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `affiliate_id` int unsigned NOT NULL,
  `balance` decimal(30,2) NOT NULL,
  `amount_requested` decimal(30,2) NOT NULL,
  `status` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `payment_method` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `other_payment_method` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `paid_at` timestamp NULL DEFAULT NULL,
  `transaction_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `memo` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `affiliate_payouts_affiliate_id_foreign` (`affiliate_id`),
  CONSTRAINT `affiliate_payouts_affiliate_id_foreign` FOREIGN KEY (`affiliate_id`) REFERENCES `affiliates` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('affiliate_payouts');
    }
};
