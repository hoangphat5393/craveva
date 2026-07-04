<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `recruit_interview_histories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `recruit_interview_schedule_id` int unsigned DEFAULT NULL,
  `user_id` int unsigned NOT NULL,
  `recruit_interview_file_id` int unsigned DEFAULT NULL,
  `details` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `recruit_interview_histories_user_id_foreign` (`user_id`),
  KEY `rih_recruit_interview_schedule_id_foreign` (`recruit_interview_schedule_id`),
  KEY `recruit_interview_histories_recruit_interview_file_id_foreign` (`recruit_interview_file_id`),
  CONSTRAINT `recruit_interview_histories_recruit_interview_file_id_foreign` FOREIGN KEY (`recruit_interview_file_id`) REFERENCES `recruit_interview_files` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `recruit_interview_histories_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `rih_recruit_interview_schedule_id_foreign` FOREIGN KEY (`recruit_interview_schedule_id`) REFERENCES `recruit_interview_schedules` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('recruit_interview_histories');
    }
};
