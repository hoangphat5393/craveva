<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `tasks` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `task_short_code` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `company_id` int unsigned DEFAULT NULL,
  `heading` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci,
  `due_date` datetime DEFAULT NULL,
  `start_date` datetime DEFAULT NULL,
  `project_id` int unsigned DEFAULT NULL,
  `task_category_id` int unsigned DEFAULT NULL,
  `priority` enum('low','medium','high') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'medium',
  `status` enum('incomplete','completed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'incomplete',
  `board_column_id` int unsigned DEFAULT '1',
  `column_priority` int NOT NULL,
  `completed_on` datetime DEFAULT NULL,
  `created_by` int unsigned DEFAULT NULL,
  `recurring_task_id` int unsigned DEFAULT NULL,
  `dependent_task_id` int unsigned DEFAULT NULL,
  `milestone_id` int unsigned DEFAULT NULL,
  `is_private` tinyint(1) NOT NULL DEFAULT '0',
  `billable` tinyint(1) NOT NULL DEFAULT '1',
  `estimate_hours` int NOT NULL,
  `estimate_minutes` int NOT NULL,
  `added_by` int unsigned DEFAULT NULL,
  `last_updated_by` int unsigned DEFAULT NULL,
  `hash` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `repeat` tinyint(1) NOT NULL DEFAULT '0',
  `repeat_complete` tinyint(1) NOT NULL DEFAULT '0',
  `repeat_count` int DEFAULT NULL,
  `repeat_type` enum('day','week','month','year') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'day',
  `repeat_cycles` int DEFAULT NULL,
  `event_id` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `approval_send` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `tasks_company_id_foreign` (`company_id`),
  KEY `tasks_due_date_index` (`due_date`),
  KEY `tasks_project_id_foreign` (`project_id`),
  KEY `tasks_task_category_id_foreign` (`task_category_id`),
  KEY `tasks_board_column_id_foreign` (`board_column_id`),
  KEY `tasks_created_by_foreign` (`created_by`),
  KEY `tasks_recurring_task_id_foreign` (`recurring_task_id`),
  KEY `tasks_dependent_task_id_foreign` (`dependent_task_id`),
  KEY `tasks_milestone_id_foreign` (`milestone_id`),
  KEY `tasks_added_by_foreign` (`added_by`),
  KEY `tasks_last_updated_by_foreign` (`last_updated_by`),
  KEY `tasks_deleted_at_index` (`deleted_at`),
  CONSTRAINT `tasks_added_by_foreign` FOREIGN KEY (`added_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `tasks_board_column_id_foreign` FOREIGN KEY (`board_column_id`) REFERENCES `taskboard_columns` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `tasks_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `tasks_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `tasks_dependent_task_id_foreign` FOREIGN KEY (`dependent_task_id`) REFERENCES `tasks` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `tasks_last_updated_by_foreign` FOREIGN KEY (`last_updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `tasks_milestone_id_foreign` FOREIGN KEY (`milestone_id`) REFERENCES `project_milestones` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `tasks_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `tasks_recurring_task_id_foreign` FOREIGN KEY (`recurring_task_id`) REFERENCES `tasks` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `tasks_task_category_id_foreign` FOREIGN KEY (`task_category_id`) REFERENCES `task_category` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
