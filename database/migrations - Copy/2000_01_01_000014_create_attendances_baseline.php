<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `attendances` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned DEFAULT NULL,
  `user_id` int unsigned NOT NULL,
  `location_id` bigint unsigned DEFAULT NULL,
  `clock_in_time` datetime NOT NULL,
  `clock_out_time` datetime DEFAULT NULL,
  `clock_in_type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `clock_out_type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `auto_clock_out` tinyint(1) NOT NULL DEFAULT '0',
  `clock_in_ip` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `clock_out_ip` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `working_from` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `late` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `half_day` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL,
  `half_day_type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `added_by` int unsigned DEFAULT NULL,
  `last_updated_by` int unsigned DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `shift_start_time` datetime DEFAULT NULL,
  `shift_end_time` datetime DEFAULT NULL,
  `employee_shift_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `work_from_type` enum('home','office','other') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'other',
  `overwrite_attendance` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `clock_out_time_work_from_type` enum('home','office','other') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `clock_out_time_location_id` bigint unsigned DEFAULT NULL,
  `clock_out_time_working_from` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `attendances_company_id_foreign` (`company_id`),
  KEY `attendances_user_id_foreign` (`user_id`),
  KEY `attendances_location_id_foreign` (`location_id`),
  KEY `attendances_clock_in_time_index` (`clock_in_time`),
  KEY `attendances_clock_out_time_index` (`clock_out_time`),
  KEY `attendances_added_by_foreign` (`added_by`),
  KEY `attendances_last_updated_by_foreign` (`last_updated_by`),
  KEY `attendances_employee_shift_id_foreign` (`employee_shift_id`),
  KEY `attendances_clock_out_time_location_id_foreign` (`clock_out_time_location_id`),
  CONSTRAINT `attendances_added_by_foreign` FOREIGN KEY (`added_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `attendances_clock_out_time_location_id_foreign` FOREIGN KEY (`clock_out_time_location_id`) REFERENCES `company_addresses` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `attendances_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `attendances_employee_shift_id_foreign` FOREIGN KEY (`employee_shift_id`) REFERENCES `employee_shifts` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `attendances_last_updated_by_foreign` FOREIGN KEY (`last_updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `attendances_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `company_addresses` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `attendances_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
