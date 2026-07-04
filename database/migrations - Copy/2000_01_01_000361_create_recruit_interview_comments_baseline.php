<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `recruit_interview_comments` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `recruit_interview_schedule_id` int unsigned NOT NULL,
  `user_id` int unsigned NOT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci,
  `candidate_comment` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `recruit_interview_comments_user_id_foreign` (`user_id`),
  KEY `recruit_interview_comments_recruit_interview_schedule_id_foreign` (`recruit_interview_schedule_id`),
  CONSTRAINT `recruit_interview_comments_recruit_interview_schedule_id_foreign` FOREIGN KEY (`recruit_interview_schedule_id`) REFERENCES `recruit_interview_schedules` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `recruit_interview_comments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('recruit_interview_comments');
    }
};
