<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `payroll_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned DEFAULT NULL,
  `tds_salary` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tds_status` tinyint(1) NOT NULL,
  `finance_month` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '04',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `extra_fields` text COLLATE utf8mb4_unicode_ci,
  `semi_monthly_start` int DEFAULT '1',
  `semi_monthly_end` int DEFAULT '30',
  `currency_id` int unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payroll_settings_company_id_foreign` (`company_id`),
  KEY `payroll_settings_currency_id_foreign` (`currency_id`),
  CONSTRAINT `payroll_settings_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `payroll_settings_currency_id_foreign` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_settings');
    }
};
