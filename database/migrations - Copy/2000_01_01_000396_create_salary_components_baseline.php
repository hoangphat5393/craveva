<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `salary_components` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned DEFAULT NULL,
  `component_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `component_type` enum('earning','deduction') COLLATE utf8mb4_unicode_ci NOT NULL,
  `component_value` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value_type` enum('fixed','percent','basic_percent','variable') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `weekly_value` double NOT NULL DEFAULT '0',
  `biweekly_value` double NOT NULL DEFAULT '0',
  `semimonthly_value` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `salary_components_company_id_foreign` (`company_id`),
  CONSTRAINT `salary_components_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_components');
    }
};
