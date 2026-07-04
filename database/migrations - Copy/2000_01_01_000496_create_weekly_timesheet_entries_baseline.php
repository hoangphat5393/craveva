<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `weekly_timesheet_entries` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned NOT NULL,
  `weekly_timesheet_id` bigint unsigned NOT NULL,
  `task_id` int unsigned DEFAULT NULL,
  `date` date NOT NULL,
  `hours` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `weekly_timesheet_entries_company_id_foreign` (`company_id`),
  KEY `weekly_timesheet_entries_weekly_timesheet_id_foreign` (`weekly_timesheet_id`),
  KEY `weekly_timesheet_entries_task_id_foreign` (`task_id`),
  CONSTRAINT `weekly_timesheet_entries_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `weekly_timesheet_entries_task_id_foreign` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `weekly_timesheet_entries_weekly_timesheet_id_foreign` FOREIGN KEY (`weekly_timesheet_id`) REFERENCES `weekly_timesheets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('weekly_timesheet_entries');
    }
};
