<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `user_zoom_meeting` (
  `user_id` int unsigned NOT NULL,
  `zoom_meeting_id` bigint unsigned NOT NULL,
  KEY `user_zoom_meeting_user_id_foreign` (`user_id`),
  KEY `user_zoom_meeting_zoom_meeting_id_foreign` (`zoom_meeting_id`),
  CONSTRAINT `user_zoom_meeting_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `user_zoom_meeting_zoom_meeting_id_foreign` FOREIGN KEY (`zoom_meeting_id`) REFERENCES `zoom_meetings` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('user_zoom_meeting');
    }
};
