<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `objectives` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `project_id` int unsigned DEFAULT NULL,
  `company_id` int unsigned DEFAULT NULL,
  `title` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `goal_type` bigint unsigned NOT NULL,
  `department_id` int unsigned DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `priority` enum('low','medium','high') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'low',
  `check_in_frequency` enum('daily','weekly','bi-weekly','monthly','quarterly') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'weekly',
  `schedule_on` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rotation_date` int DEFAULT NULL,
  `send_check_in_reminder` tinyint(1) NOT NULL DEFAULT '1',
  `created_by` int unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `objectives_company_id_foreign` (`company_id`),
  KEY `objectives_goal_type_foreign` (`goal_type`),
  KEY `objectives_department_id_foreign` (`department_id`),
  KEY `objectives_users_id_foreign` (`created_by`),
  KEY `objectives_project_id_foreign` (`project_id`),
  CONSTRAINT `objectives_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `objectives_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `objectives_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `teams` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `objectives_goal_type_foreign` FOREIGN KEY (`goal_type`) REFERENCES `goal_types` (`id`) ON DELETE CASCADE,
  CONSTRAINT `objectives_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('objectives');
    }
};
