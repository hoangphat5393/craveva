<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `recruit_job_offer_letter` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned DEFAULT NULL,
  `recruit_job_application_id` int unsigned DEFAULT NULL,
  `recruit_job_id` bigint unsigned DEFAULT NULL,
  `employee_id` int unsigned DEFAULT NULL,
  `job_expire` date NOT NULL,
  `expected_joining_date` date NOT NULL,
  `comp_amount` double DEFAULT NULL,
  `status` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pay_according` enum('hour','day','week','month','year') COLLATE utf8mb4_unicode_ci NOT NULL,
  `sign_require` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `add_structure` int NOT NULL DEFAULT '0',
  `sign_image` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `decline_reason` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hash` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ip_address` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `offer_accept_at` timestamp NULL DEFAULT NULL,
  `added_by` int unsigned DEFAULT NULL,
  `last_updated_by` int unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `recruit_job_offer_letter_added_by_foreign` (`added_by`),
  KEY `recruit_job_offer_letter_last_updated_by_foreign` (`last_updated_by`),
  KEY `recruit_job_offer_letter_employee_id_foreign` (`employee_id`),
  KEY `recruit_job_offer_letter_recruit_job_application_id_foreign` (`recruit_job_application_id`),
  KEY `recruit_job_offer_letter_recruit_job_id_foreign` (`recruit_job_id`),
  KEY `recruit_job_offer_letter_company_id_foreign` (`company_id`),
  CONSTRAINT `recruit_job_offer_letter_added_by_foreign` FOREIGN KEY (`added_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `recruit_job_offer_letter_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `recruit_job_offer_letter_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `recruit_job_offer_letter_last_updated_by_foreign` FOREIGN KEY (`last_updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `recruit_job_offer_letter_recruit_job_application_id_foreign` FOREIGN KEY (`recruit_job_application_id`) REFERENCES `recruit_job_applications` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `recruit_job_offer_letter_recruit_job_id_foreign` FOREIGN KEY (`recruit_job_id`) REFERENCES `recruit_jobs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('recruit_job_offer_letter');
    }
};
