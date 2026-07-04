<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `salary_group_components` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned DEFAULT NULL,
  `salary_group_id` bigint unsigned NOT NULL,
  `salary_component_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `salary_group_components_salary_group_id_foreign` (`salary_group_id`),
  KEY `salary_group_components_salary_component_id_foreign` (`salary_component_id`),
  KEY `salary_group_components_company_id_foreign` (`company_id`),
  CONSTRAINT `salary_group_components_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `salary_group_components_salary_component_id_foreign` FOREIGN KEY (`salary_component_id`) REFERENCES `salary_components` (`id`) ON DELETE CASCADE,
  CONSTRAINT `salary_group_components_salary_group_id_foreign` FOREIGN KEY (`salary_group_id`) REFERENCES `salary_groups` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_group_components');
    }
};
