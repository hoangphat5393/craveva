<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `project_template_tasks` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `heading` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` mediumtext COLLATE utf8mb4_unicode_ci,
  `project_template_id` int unsigned NOT NULL,
  `milestone_id` int unsigned DEFAULT NULL,
  `priority` enum('low','medium','high') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'medium',
  `task_labels` text COLLATE utf8mb4_unicode_ci,
  `project_template_task_category_id` int unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `project_template_tasks_project_template_id_foreign` (`project_template_id`),
  KEY `project_template_tasks_project_template_task_category_id_foreign` (`project_template_task_category_id`),
  KEY `tasks_milestone_id_foreign` (`milestone_id`),
  CONSTRAINT `project_template_tasks_milestone_id_foreign` FOREIGN KEY (`milestone_id`) REFERENCES `project_template_milestone` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `project_template_tasks_project_template_id_foreign` FOREIGN KEY (`project_template_id`) REFERENCES `project_templates` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `project_template_tasks_project_template_task_category_id_foreign` FOREIGN KEY (`project_template_task_category_id`) REFERENCES `task_category` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('project_template_tasks');
    }
};
