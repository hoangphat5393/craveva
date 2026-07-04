<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `recruit_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned DEFAULT NULL,
  `title` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `recruit_job_type_id` bigint unsigned DEFAULT NULL,
  `job_description` longtext COLLATE utf8mb4_unicode_ci,
  `total_positions` int NOT NULL,
  `remaining_openings` int NOT NULL,
  `department_id` int unsigned DEFAULT NULL,
  `recruiter_id` int unsigned DEFAULT NULL,
  `job_type` enum('part time','full time') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'full time',
  `start_date` datetime NOT NULL,
  `end_date` datetime DEFAULT NULL,
  `status` enum('open','closed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
  `currency_id` int unsigned DEFAULT NULL,
  `recruit_job_category_id` int unsigned DEFAULT NULL,
  `recruit_job_sub_category_id` int unsigned DEFAULT NULL,
  `remote_job` enum('yes','no') COLLATE utf8mb4_unicode_ci DEFAULT 'no',
  `disclose_salary` enum('yes','no') COLLATE utf8mb4_unicode_ci DEFAULT 'no',
  `meta_details` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_photo_require` tinyint(1) NOT NULL DEFAULT '0',
  `is_resume_require` tinyint(1) NOT NULL DEFAULT '0',
  `is_dob_require` tinyint(1) NOT NULL DEFAULT '0',
  `is_gender_require` tinyint(1) NOT NULL DEFAULT '0',
  `recruit_work_experience_id` bigint unsigned DEFAULT NULL,
  `pay_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_amount` double NOT NULL,
  `end_amount` double DEFAULT NULL,
  `pay_according` enum('hour','day','week','month','year') COLLATE utf8mb4_unicode_ci NOT NULL,
  `added_by` int unsigned DEFAULT NULL,
  `last_updated_by` int unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `is_currentctc_require` tinyint(1) NOT NULL DEFAULT '0',
  `is_expectedctc_require` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `recruit_jobs_added_by_foreign` (`added_by`),
  KEY `recruit_jobs_last_updated_by_foreign` (`last_updated_by`),
  KEY `recruit_jobs_currency_id_foreign` (`currency_id`),
  KEY `recruit_jobs_company_id_foreign` (`company_id`),
  KEY `recruit_jobs_recruit_work_experience_id_foreign` (`recruit_work_experience_id`),
  KEY `recruit_jobs_department_id_foreign` (`department_id`),
  KEY `recruit_jobs_recruit_job_category_id_foreign` (`recruit_job_category_id`),
  KEY `recruit_jobs_recruit_job_sub_category_id_foreign` (`recruit_job_sub_category_id`),
  KEY `recruit_jobs_recruit_job_type_id_foreign` (`recruit_job_type_id`),
  KEY `recruit_jobs_recruiter_id_foreign` (`recruiter_id`),
  CONSTRAINT `recruit_jobs_added_by_foreign` FOREIGN KEY (`added_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `recruit_jobs_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `recruit_jobs_currency_id_foreign` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `recruit_jobs_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `teams` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `recruit_jobs_last_updated_by_foreign` FOREIGN KEY (`last_updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `recruit_jobs_recruit_job_category_id_foreign` FOREIGN KEY (`recruit_job_category_id`) REFERENCES `recruit_job_categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `recruit_jobs_recruit_job_sub_category_id_foreign` FOREIGN KEY (`recruit_job_sub_category_id`) REFERENCES `recruit_job_sub_categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `recruit_jobs_recruit_job_type_id_foreign` FOREIGN KEY (`recruit_job_type_id`) REFERENCES `recruit_job_types` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `recruit_jobs_recruit_work_experience_id_foreign` FOREIGN KEY (`recruit_work_experience_id`) REFERENCES `recruit_work_experiences` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `recruit_jobs_recruiter_id_foreign` FOREIGN KEY (`recruiter_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('recruit_jobs');
    }
};
