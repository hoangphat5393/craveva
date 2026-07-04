<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `employee_details` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned DEFAULT NULL,
  `user_id` int unsigned NOT NULL,
  `employee_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `hourly_rate` double DEFAULT NULL,
  `slack_username` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `department_id` int unsigned DEFAULT NULL,
  `designation_id` bigint unsigned DEFAULT NULL,
  `joining_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_date` date DEFAULT NULL,
  `added_by` int unsigned DEFAULT NULL,
  `last_updated_by` int unsigned DEFAULT NULL,
  `attendance_reminder` date DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `calendar_view` text COLLATE utf8mb4_unicode_ci,
  `about_me` text COLLATE utf8mb4_unicode_ci,
  `reporting_to` int unsigned DEFAULT NULL,
  `contract_end_date` date DEFAULT NULL,
  `internship_end_date` date DEFAULT NULL,
  `employment_type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `marriage_anniversary_date` date DEFAULT NULL,
  `marital_status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'single',
  `notice_period_end_date` date DEFAULT NULL,
  `notice_period_start_date` date DEFAULT NULL,
  `probation_end_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `onboard_completed` tinyint(1) NOT NULL DEFAULT '0',
  `offboard_completed` tinyint(1) NOT NULL DEFAULT '0',
  `onboarding_status` enum('old','new') COLLATE utf8mb4_unicode_ci NOT NULL,
  `company_address_id` bigint unsigned DEFAULT NULL,
  `overtime_hourly_rate` decimal(16,2) DEFAULT NULL COMMENT 'This field is only for overtime calculation',
  PRIMARY KEY (`id`),
  UNIQUE KEY `employee_details_employee_id_company_id_unique` (`employee_id`,`company_id`),
  UNIQUE KEY `employee_details_slack_username_company_id_unique` (`slack_username`,`company_id`),
  KEY `employee_details_company_id_foreign` (`company_id`),
  KEY `employee_details_user_id_foreign` (`user_id`),
  KEY `employee_details_department_id_foreign` (`department_id`),
  KEY `employee_details_designation_id_foreign` (`designation_id`),
  KEY `employee_details_added_by_foreign` (`added_by`),
  KEY `employee_details_last_updated_by_foreign` (`last_updated_by`),
  KEY `employee_details_reporting_to_foreign` (`reporting_to`),
  KEY `employee_details_company_address_id_foreign` (`company_address_id`),
  CONSTRAINT `employee_details_added_by_foreign` FOREIGN KEY (`added_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `employee_details_company_address_id_foreign` FOREIGN KEY (`company_address_id`) REFERENCES `company_addresses` (`id`) ON DELETE SET NULL,
  CONSTRAINT `employee_details_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `employee_details_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `teams` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `employee_details_designation_id_foreign` FOREIGN KEY (`designation_id`) REFERENCES `designations` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `employee_details_last_updated_by_foreign` FOREIGN KEY (`last_updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `employee_details_reporting_to_foreign` FOREIGN KEY (`reporting_to`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `employee_details_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_details');
    }
};
