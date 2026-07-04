<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `rotation_automate_log` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned DEFAULT NULL,
  `user_id` int unsigned NOT NULL,
  `employee_shift_rotation_id` int unsigned DEFAULT NULL,
  `employee_shift_id` bigint unsigned DEFAULT NULL,
  `sequence` int DEFAULT NULL,
  `cron_run_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `rotation_automate_log_company_id_foreign` (`company_id`),
  KEY `rotation_automate_log_employee_shift_rotation_id_foreign` (`employee_shift_rotation_id`),
  KEY `rotation_automate_log_employee_shift_id_foreign` (`employee_shift_id`),
  KEY `employee_shift_schedules_user_id_foreign` (`user_id`),
  CONSTRAINT `rotation_automate_log_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `rotation_automate_log_employee_shift_id_foreign` FOREIGN KEY (`employee_shift_id`) REFERENCES `employee_shifts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `rotation_automate_log_employee_shift_rotation_id_foreign` FOREIGN KEY (`employee_shift_rotation_id`) REFERENCES `employee_shift_rotations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `rotation_automate_log_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('rotation_automate_log');
    }
};
