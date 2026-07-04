<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `recruit_selected_salary_components` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned DEFAULT NULL,
  `rss_id` int unsigned DEFAULT NULL,
  `salary_component_id` int unsigned DEFAULT NULL,
  `component_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `component_type` enum('earning','deduction') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `component_value` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `value_type` enum('fixed','percent','basic_percent','variable') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'variable',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `recruit_selected_salary_components_company_id_foreign` (`company_id`),
  KEY `recruit_selected_salary_components_rss_id_foreign` (`rss_id`),
  CONSTRAINT `recruit_selected_salary_components_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `recruit_selected_salary_components_rss_id_foreign` FOREIGN KEY (`rss_id`) REFERENCES `recruit_salary_structures` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('recruit_selected_salary_components');
    }
};
