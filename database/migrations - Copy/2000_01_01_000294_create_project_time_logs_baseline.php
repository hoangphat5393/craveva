<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `project_time_logs` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned DEFAULT NULL,
  `project_id` int unsigned DEFAULT NULL,
  `task_id` int unsigned DEFAULT NULL,
  `user_id` int unsigned NOT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime DEFAULT NULL,
  `memo` text COLLATE utf8mb4_unicode_ci,
  `total_hours` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total_minutes` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `edited_by_user` int unsigned DEFAULT NULL,
  `hourly_rate` int NOT NULL,
  `earnings` decimal(16,2) DEFAULT NULL,
  `approved` tinyint(1) NOT NULL DEFAULT '1',
  `approved_by` int unsigned DEFAULT NULL,
  `invoice_id` int unsigned DEFAULT NULL,
  `added_by` int unsigned DEFAULT NULL,
  `last_updated_by` int unsigned DEFAULT NULL,
  `total_break_minutes` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `weekly_timesheet_id` bigint unsigned DEFAULT NULL,
  `rejected` tinyint(1) NOT NULL DEFAULT '0',
  `rejected_by` int unsigned DEFAULT NULL,
  `rejected_at` datetime DEFAULT NULL,
  `reject_reason` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `project_time_logs_company_id_foreign` (`company_id`),
  KEY `project_time_logs_project_id_foreign` (`project_id`),
  KEY `project_time_logs_task_id_foreign` (`task_id`),
  KEY `project_time_logs_user_id_foreign` (`user_id`),
  KEY `project_time_logs_start_time_index` (`start_time`),
  KEY `project_time_logs_end_time_index` (`end_time`),
  KEY `project_time_logs_edited_by_user_foreign` (`edited_by_user`),
  KEY `project_time_logs_approved_by_foreign` (`approved_by`),
  KEY `project_time_logs_invoice_id_foreign` (`invoice_id`),
  KEY `project_time_logs_added_by_foreign` (`added_by`),
  KEY `project_time_logs_last_updated_by_foreign` (`last_updated_by`),
  KEY `project_time_logs_weekly_timesheet_id_foreign` (`weekly_timesheet_id`),
  KEY `project_time_logs_rejected_by_foreign` (`rejected_by`),
  CONSTRAINT `project_time_logs_added_by_foreign` FOREIGN KEY (`added_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `project_time_logs_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `project_time_logs_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `project_time_logs_edited_by_user_foreign` FOREIGN KEY (`edited_by_user`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `project_time_logs_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `project_time_logs_last_updated_by_foreign` FOREIGN KEY (`last_updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `project_time_logs_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `project_time_logs_rejected_by_foreign` FOREIGN KEY (`rejected_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `project_time_logs_task_id_foreign` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `project_time_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `project_time_logs_weekly_timesheet_id_foreign` FOREIGN KEY (`weekly_timesheet_id`) REFERENCES `weekly_timesheets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('project_time_logs');
    }
};
