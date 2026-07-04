<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `project_labels` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `label_id` int unsigned NOT NULL,
  `project_id` int unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `project_labels_label_id_foreign` (`label_id`),
  KEY `project_tags_project_id_foreign` (`project_id`),
  CONSTRAINT `project_labels_label_id_foreign` FOREIGN KEY (`label_id`) REFERENCES `project_label_list` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `project_tags_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('project_labels');
    }
};
