<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `recruit_salary_structures` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned DEFAULT NULL,
  `recruit_job_application_id` int unsigned DEFAULT NULL,
  `recruit_job_offer_letter_id` int unsigned DEFAULT NULL,
  `salary_json` text COLLATE utf8mb4_unicode_ci,
  `annual_salary` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `basic_salary` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `basic_value_type` enum('fixed','ctc_percent') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `fixed_allowance` double NOT NULL DEFAULT '0',
  `date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `recruit_salary_structures_company_id_foreign` (`company_id`),
  KEY `recruit_salary_structures_recruit_job_application_id_foreign` (`recruit_job_application_id`),
  KEY `recruit_salary_structures_recruit_job_offer_letter_id_foreign` (`recruit_job_offer_letter_id`),
  CONSTRAINT `recruit_salary_structures_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `recruit_salary_structures_recruit_job_application_id_foreign` FOREIGN KEY (`recruit_job_application_id`) REFERENCES `recruit_job_applications` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `recruit_salary_structures_recruit_job_offer_letter_id_foreign` FOREIGN KEY (`recruit_job_offer_letter_id`) REFERENCES `recruit_job_offer_letter` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('recruit_salary_structures');
    }
};
