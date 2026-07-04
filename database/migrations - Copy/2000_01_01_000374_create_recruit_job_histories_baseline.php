<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `recruit_job_histories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `recruit_job_id` bigint unsigned DEFAULT NULL,
  `recruit_job_application_id` int unsigned DEFAULT NULL,
  `recruit_job_offer_letter_id` int unsigned DEFAULT NULL,
  `recruit_interview_schedule_id` int unsigned DEFAULT NULL,
  `user_id` int unsigned NOT NULL,
  `details` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `recruit_job_application_status_id` int unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `recruit_job_histories_user_id_foreign` (`user_id`),
  KEY `recruit_job_histories_recruit_job_id_foreign` (`recruit_job_id`),
  KEY `recruit_job_histories_recruit_job_application_id_foreign` (`recruit_job_application_id`),
  KEY `recruit_job_histories_recruit_job_offer_letter_id_foreign` (`recruit_job_offer_letter_id`),
  KEY `recruit_job_histories_recruit_interview_schedule_id_foreign` (`recruit_interview_schedule_id`),
  KEY `recruit_job_histories_recruit_job_application_status_id_foreign` (`recruit_job_application_status_id`),
  CONSTRAINT `recruit_job_histories_recruit_interview_schedule_id_foreign` FOREIGN KEY (`recruit_interview_schedule_id`) REFERENCES `recruit_interview_schedules` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `recruit_job_histories_recruit_job_application_id_foreign` FOREIGN KEY (`recruit_job_application_id`) REFERENCES `recruit_job_applications` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `recruit_job_histories_recruit_job_application_status_id_foreign` FOREIGN KEY (`recruit_job_application_status_id`) REFERENCES `recruit_application_status` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `recruit_job_histories_recruit_job_id_foreign` FOREIGN KEY (`recruit_job_id`) REFERENCES `recruit_jobs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `recruit_job_histories_recruit_job_offer_letter_id_foreign` FOREIGN KEY (`recruit_job_offer_letter_id`) REFERENCES `recruit_job_offer_letter` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `recruit_job_histories_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('recruit_job_histories');
    }
};
