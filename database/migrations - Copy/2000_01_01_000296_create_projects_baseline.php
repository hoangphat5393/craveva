<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `projects` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned DEFAULT NULL,
  `project_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `project_short_code` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `project_summary` longtext COLLATE utf8mb4_unicode_ci,
  `project_admin` int unsigned DEFAULT NULL,
  `start_date` date NOT NULL,
  `deadline` date DEFAULT NULL,
  `notes` longtext COLLATE utf8mb4_unicode_ci,
  `category_id` int unsigned DEFAULT NULL,
  `client_id` int unsigned DEFAULT NULL,
  `team_id` int unsigned DEFAULT NULL,
  `feedback` mediumtext COLLATE utf8mb4_unicode_ci,
  `manual_timelog` enum('enable','disable') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'disable',
  `client_view_task` enum('enable','disable') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'disable',
  `allow_client_notification` enum('enable','disable') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'disable',
  `completion_percent` tinyint NOT NULL,
  `calculate_task_progress` enum('manual','task_completion','project_total_time','project_deadline') COLLATE utf8mb4_unicode_ci DEFAULT 'manual',
  `project_budget` decimal(30,2) DEFAULT NULL,
  `currency_id` int unsigned DEFAULT NULL,
  `hours_allocated` decimal(8,2) DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `added_by` int unsigned DEFAULT NULL,
  `last_updated_by` int unsigned DEFAULT NULL,
  `hash` text COLLATE utf8mb4_unicode_ci,
  `public` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `enable_miroboard` tinyint(1) NOT NULL DEFAULT '0',
  `miro_board_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `client_access` tinyint(1) NOT NULL DEFAULT '0',
  `public_taskboard` enum('enable','disable') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'enable',
  `public_gantt_chart` enum('enable','disable') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'enable',
  `need_approval_by_admin` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `projects_company_id_foreign` (`company_id`),
  KEY `projects_project_admin_foreign` (`project_admin`),
  KEY `projects_category_id_foreign` (`category_id`),
  KEY `projects_client_id_foreign` (`client_id`),
  KEY `projects_team_id_foreign` (`team_id`),
  KEY `projects_currency_id_foreign` (`currency_id`),
  KEY `projects_added_by_foreign` (`added_by`),
  KEY `projects_last_updated_by_foreign` (`last_updated_by`),
  KEY `projects_deleted_at_index` (`deleted_at`),
  CONSTRAINT `projects_added_by_foreign` FOREIGN KEY (`added_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `projects_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `project_category` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `projects_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `projects_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `projects_currency_id_foreign` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `projects_last_updated_by_foreign` FOREIGN KEY (`last_updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `projects_project_admin_foreign` FOREIGN KEY (`project_admin`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `projects_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
