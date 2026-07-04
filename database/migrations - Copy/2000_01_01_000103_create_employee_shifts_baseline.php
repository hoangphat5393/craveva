<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `employee_shifts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned DEFAULT NULL,
  `shift_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `shift_short_code` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `shift_type` enum('strict','flexible') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'strict',
  `flexible_total_hours` float DEFAULT NULL,
  `flexible_auto_clockout` float DEFAULT NULL,
  `flexible_half_day_hours` float DEFAULT NULL,
  `color` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `office_start_time` time NOT NULL,
  `office_end_time` time NOT NULL,
  `auto_clock_out_time` int NOT NULL DEFAULT '1',
  `halfday_mark_time` time DEFAULT NULL,
  `late_mark_duration` tinyint NOT NULL,
  `clockin_in_day` tinyint NOT NULL,
  `office_open_days` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `early_clock_in` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_shifts_company_id_foreign` (`company_id`),
  CONSTRAINT `employee_shifts_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_shifts');
    }
};
