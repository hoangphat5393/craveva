<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `affiliate_settings` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `commission_enabled` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'yes',
  `payout_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'on signup',
  `payout_time` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `commission_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'fixed',
  `commission_cap` int unsigned NOT NULL DEFAULT '0',
  `minimum_payout` int unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('affiliate_settings');
    }
};
