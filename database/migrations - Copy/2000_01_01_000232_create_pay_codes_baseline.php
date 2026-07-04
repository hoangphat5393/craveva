<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `pay_codes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned DEFAULT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `code` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `time` decimal(20,2) DEFAULT NULL,
  `fixed` tinyint(1) NOT NULL DEFAULT '0',
  `fixed_amount` decimal(20,2) DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `regular_fixed_amount` decimal(8,2) NOT NULL DEFAULT '0.00',
  `regular_time_rate` decimal(8,2) NOT NULL DEFAULT '1.00',
  `weekend_fixed_amount` decimal(8,2) NOT NULL DEFAULT '0.00',
  `weekend_time_rate` decimal(8,2) NOT NULL DEFAULT '1.50',
  `holiday_fixed_amount` decimal(8,2) NOT NULL DEFAULT '0.00',
  `holiday_time_rate` decimal(8,2) NOT NULL DEFAULT '2.00',
  `day_off_fixed_amount` decimal(8,2) NOT NULL DEFAULT '0.00',
  `day_off_time_rate` decimal(8,2) NOT NULL DEFAULT '1.75',
  PRIMARY KEY (`id`),
  KEY `pay_codes_company_id_foreign` (`company_id`),
  CONSTRAINT `pay_codes_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('pay_codes');
    }
};
