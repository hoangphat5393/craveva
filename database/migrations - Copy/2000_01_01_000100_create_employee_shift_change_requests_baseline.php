<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `employee_shift_change_requests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned DEFAULT NULL,
  `shift_schedule_id` bigint unsigned NOT NULL,
  `employee_shift_id` bigint unsigned NOT NULL,
  `status` enum('waiting','accepted','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'waiting',
  `reason` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_shift_change_requests_company_id_foreign` (`company_id`),
  KEY `employee_shift_change_requests_shift_schedule_id_foreign` (`shift_schedule_id`),
  KEY `employee_shift_change_requests_employee_shift_id_foreign` (`employee_shift_id`),
  CONSTRAINT `employee_shift_change_requests_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `employee_shift_change_requests_employee_shift_id_foreign` FOREIGN KEY (`employee_shift_id`) REFERENCES `employee_shifts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `employee_shift_change_requests_shift_schedule_id_foreign` FOREIGN KEY (`shift_schedule_id`) REFERENCES `employee_shift_schedules` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_shift_change_requests');
    }
};
