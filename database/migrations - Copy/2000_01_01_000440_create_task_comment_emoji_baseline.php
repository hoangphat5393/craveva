<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `task_comment_emoji` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned DEFAULT NULL,
  `comment_id` int unsigned DEFAULT NULL,
  `emoji_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `task_comment_emoji_user_id_foreign` (`user_id`),
  KEY `task_comment_emoji_comment_id_foreign` (`comment_id`),
  CONSTRAINT `task_comment_emoji_comment_id_foreign` FOREIGN KEY (`comment_id`) REFERENCES `task_comments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `task_comment_emoji_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('task_comment_emoji');
    }
};
