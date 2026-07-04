<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `attendance_settings` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned DEFAULT NULL,
  `auto_clock_in` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `auto_clock_in_location` enum('office','home') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'office',
  `office_start_time` time NOT NULL,
  `office_end_time` time NOT NULL,
  `halfday_mark_time` time DEFAULT NULL,
  `late_mark_duration` tinyint NOT NULL,
  `clockin_in_day` int NOT NULL DEFAULT '1',
  `employee_clock_in_out` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'yes',
  `office_open_days` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '[1,2,3,4,5]',
  `ip_address` text COLLATE utf8mb4_unicode_ci,
  `radius` int DEFAULT NULL,
  `radius_check` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `ip_check` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `alert_after` int DEFAULT NULL,
  `alert_after_status` tinyint(1) NOT NULL DEFAULT '1',
  `save_current_location` tinyint(1) NOT NULL DEFAULT '0',
  `default_employee_shift` bigint unsigned DEFAULT '1',
  `week_start_from` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1',
  `allow_shift_change` tinyint(1) NOT NULL DEFAULT '1',
  `show_clock_in_button` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `monthly_report` tinyint(1) NOT NULL DEFAULT '0',
  `monthly_report_roles` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `qr_enable` enum('1','0') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `attendance_settings_company_id_foreign` (`company_id`),
  KEY `attendance_settings_default_employee_shift_foreign` (`default_employee_shift`),
  CONSTRAINT `attendance_settings_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `attendance_settings_default_employee_shift_foreign` FOREIGN KEY (`default_employee_shift`) REFERENCES `employee_shifts` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_settings');
    }
};
