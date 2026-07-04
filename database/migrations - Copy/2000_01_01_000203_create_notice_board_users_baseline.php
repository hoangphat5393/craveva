<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `notice_board_users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `notice_id` int unsigned NOT NULL,
  `type` enum('employee','client') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'employee',
  `user_id` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `notice_views_notice_id_foreign` (`notice_id`),
  KEY `notice_views_user_id_foreign` (`user_id`),
  CONSTRAINT `notice_board_users_notice_id_foreign` FOREIGN KEY (`notice_id`) REFERENCES `notices` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `notice_board_users_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('notice_board_users');
    }
};
