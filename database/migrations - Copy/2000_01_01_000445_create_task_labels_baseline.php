<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `task_labels` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `label_id` int unsigned NOT NULL,
  `task_id` int unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `task_labels_label_id_foreign` (`label_id`),
  KEY `task_tags_task_id_foreign` (`task_id`),
  CONSTRAINT `task_labels_label_id_foreign` FOREIGN KEY (`label_id`) REFERENCES `task_label_list` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `task_tags_task_id_foreign` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('task_labels');
    }
};
