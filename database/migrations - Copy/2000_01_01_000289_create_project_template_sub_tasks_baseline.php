<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `project_template_sub_tasks` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `project_template_task_id` int unsigned NOT NULL,
  `title` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_date` datetime DEFAULT NULL,
  `due_date` datetime DEFAULT NULL,
  `status` enum('incomplete','complete') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'incomplete',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `project_template_sub_tasks_project_template_task_id_foreign` (`project_template_task_id`),
  CONSTRAINT `project_template_sub_tasks_project_template_task_id_foreign` FOREIGN KEY (`project_template_task_id`) REFERENCES `project_template_tasks` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('project_template_sub_tasks');
    }
};
