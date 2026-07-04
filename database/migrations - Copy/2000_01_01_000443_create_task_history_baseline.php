<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `task_history` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `task_id` int unsigned NOT NULL,
  `sub_task_id` int unsigned DEFAULT NULL,
  `user_id` int unsigned NOT NULL,
  `details` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `board_column_id` int unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `task_history_task_id_foreign` (`task_id`),
  KEY `task_history_sub_task_id_foreign` (`sub_task_id`),
  KEY `task_history_user_id_foreign` (`user_id`),
  KEY `task_history_board_column_id_foreign` (`board_column_id`),
  CONSTRAINT `task_history_board_column_id_foreign` FOREIGN KEY (`board_column_id`) REFERENCES `taskboard_columns` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `task_history_sub_task_id_foreign` FOREIGN KEY (`sub_task_id`) REFERENCES `sub_tasks` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `task_history_task_id_foreign` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `task_history_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('task_history');
    }
};
