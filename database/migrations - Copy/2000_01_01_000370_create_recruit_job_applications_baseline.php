<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `recruit_job_applications` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned DEFAULT NULL,
  `full_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total_experience` enum('fresher','0-1','1-2','2-3','3-4','4-5','5-6','6-7','7-8','8-9','9-10','10-11','11-12','12-13','13-14','over-15') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `current_location` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `current_ctc` double DEFAULT NULL,
  `currenct_ctc_rate` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expected_ctc` double DEFAULT NULL,
  `expected_ctc_rate` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notice_period` enum('15','30','45','60','75','90','over-90') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `photo` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `resume` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `column_priority` int DEFAULT NULL,
  `remark` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rejection_remark` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cover_letter` mediumtext COLLATE utf8mb4_unicode_ci,
  `job_type` enum('part time','full time','internship') COLLATE utf8mb4_unicode_ci DEFAULT 'full time',
  `recruit_job_id` bigint unsigned NOT NULL,
  `recruit_application_status_id` int unsigned DEFAULT NULL,
  `location_id` bigint unsigned NOT NULL,
  `application_sources` enum('careerWebsite','addedByUser') COLLATE utf8mb4_unicode_ci NOT NULL,
  `application_source_id` int unsigned DEFAULT NULL,
  `recruit_job_file_id` int unsigned DEFAULT NULL,
  `added_by` int unsigned DEFAULT NULL,
  `last_updated_by` int unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `send_email` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `recruit_job_applications_location_id_foreign` (`location_id`),
  KEY `recruit_job_applications_added_by_foreign` (`added_by`),
  KEY `recruit_job_applications_last_updated_by_foreign` (`last_updated_by`),
  KEY `recruit_job_applications_recruit_job_id_foreign` (`recruit_job_id`),
  KEY `recruit_job_applications_recruit_application_status_id_foreign` (`recruit_application_status_id`),
  KEY `recruit_job_applications_recruit_job_file_id_foreign` (`recruit_job_file_id`),
  KEY `recruit_job_applications_company_id_foreign` (`company_id`),
  KEY `recruit_job_applications_application_source_id_foreign` (`application_source_id`),
  CONSTRAINT `recruit_job_applications_added_by_foreign` FOREIGN KEY (`added_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `recruit_job_applications_application_source_id_foreign` FOREIGN KEY (`application_source_id`) REFERENCES `application_sources` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `recruit_job_applications_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `recruit_job_applications_last_updated_by_foreign` FOREIGN KEY (`last_updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `recruit_job_applications_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `company_addresses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `recruit_job_applications_recruit_application_status_id_foreign` FOREIGN KEY (`recruit_application_status_id`) REFERENCES `recruit_application_status` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `recruit_job_applications_recruit_job_file_id_foreign` FOREIGN KEY (`recruit_job_file_id`) REFERENCES `recruit_job_files` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `recruit_job_applications_recruit_job_id_foreign` FOREIGN KEY (`recruit_job_id`) REFERENCES `recruit_jobs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('recruit_job_applications');
    }
};
