<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `performance_meeting_actions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `meeting_id` bigint unsigned NOT NULL,
  `action_point` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `added_by` int unsigned NOT NULL,
  `is_actioned` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `performance_meeting_actions_meeting_id_foreign` (`meeting_id`),
  KEY `performance_meeting_actions_added_by_foreign` (`added_by`),
  CONSTRAINT `performance_meeting_actions_added_by_foreign` FOREIGN KEY (`added_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `performance_meeting_actions_meeting_id_foreign` FOREIGN KEY (`meeting_id`) REFERENCES `performance_meetings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('performance_meeting_actions');
    }
};
