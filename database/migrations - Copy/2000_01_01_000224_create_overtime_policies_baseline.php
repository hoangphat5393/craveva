<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `overtime_policies` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned DEFAULT NULL,
  `pay_code_id` bigint unsigned NOT NULL,
  `working_days` tinyint(1) NOT NULL DEFAULT '0',
  `name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `week_end` tinyint(1) NOT NULL DEFAULT '0',
  `holiday` tinyint(1) NOT NULL DEFAULT '0',
  `request_before_days` int DEFAULT NULL,
  `allow_reporting_manager` tinyint(1) NOT NULL DEFAULT '0',
  `allow_roles` text COLLATE utf8mb4_unicode_ci,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `overtime_policies_company_id_foreign` (`company_id`),
  KEY `overtime_policies_pay_code_id_foreign` (`pay_code_id`),
  CONSTRAINT `overtime_policies_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `overtime_policies_pay_code_id_foreign` FOREIGN KEY (`pay_code_id`) REFERENCES `pay_codes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('overtime_policies');
    }
};
