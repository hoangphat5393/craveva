<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `purchase_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned DEFAULT NULL,
  `purchase_order_prefix` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PO',
  `purchase_order_number_separator` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#',
  `purchase_order_number_digit` int NOT NULL DEFAULT '3',
  `bill_prefix` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PO',
  `bill_number_separator` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#',
  `bill_number_digit` int NOT NULL DEFAULT '3',
  `vendor_credit_prefix` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'VC',
  `vendor_credit_number_seprator` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#',
  `vendor_credit_number_digit` int NOT NULL DEFAULT '3',
  `purchase_code` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `purchase_terms` text COLLATE utf8mb4_unicode_ci,
  `grn_terms` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_settings');
    }
};
