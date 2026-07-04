<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `recruit_job_custom_answers` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `recruit_job_offer_letter_id` int unsigned DEFAULT NULL,
  `recruit_job_application_id` int unsigned DEFAULT NULL,
  `recruit_job_id` bigint unsigned NOT NULL,
  `recruit_job_question_id` int unsigned NOT NULL,
  `answer` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `filename` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `hashname` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `recruit_job_custom_answers_recruit_job_offer_letter_id_foreign` (`recruit_job_offer_letter_id`),
  KEY `recruit_job_custom_answers_recruit_job_application_id_foreign` (`recruit_job_application_id`),
  KEY `recruit_job_custom_answers_recruit_job_id_foreign` (`recruit_job_id`),
  KEY `recruit_job_custom_answers_recruit_job_question_id_foreign` (`recruit_job_question_id`),
  CONSTRAINT `recruit_job_custom_answers_recruit_job_application_id_foreign` FOREIGN KEY (`recruit_job_application_id`) REFERENCES `recruit_job_applications` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `recruit_job_custom_answers_recruit_job_id_foreign` FOREIGN KEY (`recruit_job_id`) REFERENCES `recruit_jobs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `recruit_job_custom_answers_recruit_job_offer_letter_id_foreign` FOREIGN KEY (`recruit_job_offer_letter_id`) REFERENCES `recruit_job_offer_letter` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `recruit_job_custom_answers_recruit_job_question_id_foreign` FOREIGN KEY (`recruit_job_question_id`) REFERENCES `recruit_custom_questions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('recruit_job_custom_answers');
    }
};
