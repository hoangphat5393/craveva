<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `recruit_interview_evaluations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned DEFAULT NULL,
  `recruit_recommendation_status_id` int unsigned DEFAULT NULL,
  `recruit_interview_schedule_id` int unsigned DEFAULT NULL,
  `recruit_interview_stage_id` int unsigned DEFAULT NULL,
  `recruit_job_application_id` int unsigned DEFAULT NULL,
  `submitted_by` int unsigned DEFAULT NULL,
  `details` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `recruit_interview_evaluations_submitted_by_foreign` (`submitted_by`),
  KEY `rie_recruit_recommendation_status_id_foreign` (`recruit_recommendation_status_id`),
  KEY `riev_recruit_interview_schedule_id_foreign` (`recruit_interview_schedule_id`),
  KEY `rie_recruit_interview_stage_id_foreign` (`recruit_interview_stage_id`),
  KEY `rie_recruit_job_application_id_foreign` (`recruit_job_application_id`),
  KEY `recruit_interview_evaluations_company_id_foreign` (`company_id`),
  CONSTRAINT `recruit_interview_evaluations_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `recruit_interview_evaluations_submitted_by_foreign` FOREIGN KEY (`submitted_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `rie_recruit_interview_stage_id_foreign` FOREIGN KEY (`recruit_interview_stage_id`) REFERENCES `recruit_interview_stages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `rie_recruit_job_application_id_foreign` FOREIGN KEY (`recruit_job_application_id`) REFERENCES `recruit_job_applications` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `rie_recruit_recommendation_status_id_foreign` FOREIGN KEY (`recruit_recommendation_status_id`) REFERENCES `recruit_recommendation_statuses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `riev_recruit_interview_schedule_id_foreign` FOREIGN KEY (`recruit_interview_schedule_id`) REFERENCES `recruit_interview_schedules` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('recruit_interview_evaluations');
    }
};
