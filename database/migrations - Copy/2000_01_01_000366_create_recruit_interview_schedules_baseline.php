<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `recruit_interview_schedules` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned DEFAULT NULL,
  `recruit_job_application_id` int unsigned NOT NULL,
  `interview_type` enum('in person','video','phone') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'in person',
  `schedule_date` datetime DEFAULT NULL,
  `status` enum('rejected','hired','pending','canceled','completed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `user_accept_status` enum('accept','refuse','waiting') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'waiting',
  `meeting_id` int DEFAULT NULL,
  `video_type` enum('zoom','other') COLLATE utf8mb4_unicode_ci DEFAULT 'other',
  `remind_type_all` enum('day','hour','minute') COLLATE utf8mb4_unicode_ci NOT NULL,
  `notify_c` tinyint(1) NOT NULL DEFAULT '0',
  `remind_time_all` int DEFAULT NULL,
  `send_reminder_all` tinyint(1) NOT NULL DEFAULT '0',
  `phone` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `other_link` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remarks` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `recruit_interview_stage_id` int unsigned DEFAULT NULL,
  `parent_id` int unsigned DEFAULT NULL,
  `added_by` int unsigned DEFAULT NULL,
  `last_updated_by` int unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `recruit_interview_schedules_parent_id_foreign` (`parent_id`),
  KEY `recruit_interview_schedules_added_by_foreign` (`added_by`),
  KEY `recruit_interview_schedules_last_updated_by_foreign` (`last_updated_by`),
  KEY `recruit_interview_schedules_recruit_job_application_id_foreign` (`recruit_job_application_id`),
  KEY `recruit_interview_schedules_recruit_interview_stage_id_foreign` (`recruit_interview_stage_id`),
  KEY `recruit_interview_schedules_company_id_foreign` (`company_id`),
  CONSTRAINT `recruit_interview_schedules_added_by_foreign` FOREIGN KEY (`added_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `recruit_interview_schedules_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `recruit_interview_schedules_last_updated_by_foreign` FOREIGN KEY (`last_updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `recruit_interview_schedules_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `recruit_interview_schedules` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `recruit_interview_schedules_recruit_interview_stage_id_foreign` FOREIGN KEY (`recruit_interview_stage_id`) REFERENCES `recruit_interview_stages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `recruit_interview_schedules_recruit_job_application_id_foreign` FOREIGN KEY (`recruit_job_application_id`) REFERENCES `recruit_job_applications` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('recruit_interview_schedules');
    }
};
