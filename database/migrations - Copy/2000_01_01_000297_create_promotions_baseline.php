<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `promotions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned DEFAULT NULL,
  `employee_id` int unsigned DEFAULT NULL,
  `date` date DEFAULT NULL,
  `previous_designation_id` bigint unsigned DEFAULT NULL,
  `current_designation_id` bigint unsigned DEFAULT NULL,
  `send_notification` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `previous_department_id` int unsigned DEFAULT NULL,
  `current_department_id` int unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `is_send` tinyint NOT NULL DEFAULT '1',
  `promotion` tinyint NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `promotions_company_id_foreign` (`company_id`),
  KEY `promotions_employee_id_foreign` (`employee_id`),
  KEY `promotions_previous_designation_id_foreign` (`previous_designation_id`),
  KEY `promotions_current_designation_id_foreign` (`current_designation_id`),
  KEY `promotions_previous_department_id_foreign` (`previous_department_id`),
  KEY `promotions_current_department_id_foreign` (`current_department_id`),
  CONSTRAINT `promotions_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `promotions_current_department_id_foreign` FOREIGN KEY (`current_department_id`) REFERENCES `teams` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `promotions_current_designation_id_foreign` FOREIGN KEY (`current_designation_id`) REFERENCES `designations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `promotions_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `promotions_previous_department_id_foreign` FOREIGN KEY (`previous_department_id`) REFERENCES `teams` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `promotions_previous_designation_id_foreign` FOREIGN KEY (`previous_designation_id`) REFERENCES `designations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
