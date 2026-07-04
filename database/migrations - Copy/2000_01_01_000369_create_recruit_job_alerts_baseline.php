<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `recruit_job_alerts` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned DEFAULT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `recruit_work_experience_id` bigint unsigned DEFAULT NULL,
  `recruit_job_type_id` bigint unsigned DEFAULT NULL,
  `recruit_job_category_id` int unsigned NOT NULL,
  `location_id` bigint unsigned DEFAULT NULL,
  `status` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `hashname` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `recruit_job_alerts_company_id_foreign` (`company_id`),
  KEY `recruit_job_alerts_recruit_work_experience_id_foreign` (`recruit_work_experience_id`),
  KEY `recruit_job_alerts_recruit_job_type_id_foreign` (`recruit_job_type_id`),
  KEY `recruit_job_alerts_recruit_job_category_id_foreign` (`recruit_job_category_id`),
  KEY `recruit_job_alerts_location_id_foreign` (`location_id`),
  CONSTRAINT `recruit_job_alerts_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `recruit_job_alerts_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `company_addresses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `recruit_job_alerts_recruit_job_category_id_foreign` FOREIGN KEY (`recruit_job_category_id`) REFERENCES `recruit_job_categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `recruit_job_alerts_recruit_job_type_id_foreign` FOREIGN KEY (`recruit_job_type_id`) REFERENCES `recruit_job_types` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `recruit_job_alerts_recruit_work_experience_id_foreign` FOREIGN KEY (`recruit_work_experience_id`) REFERENCES `recruit_work_experiences` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('recruit_job_alerts');
    }
};
