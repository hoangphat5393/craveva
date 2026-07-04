<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `employee_variable_salaries` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `monthly_salary_id` bigint unsigned NOT NULL,
  `variable_component_id` bigint unsigned NOT NULL,
  `variable_value` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_variable_salaries_monthly_salary_id_foreign` (`monthly_salary_id`),
  KEY `employee_variable_salaries_variable_component_id_foreign` (`variable_component_id`),
  CONSTRAINT `employee_variable_salaries_monthly_salary_id_foreign` FOREIGN KEY (`monthly_salary_id`) REFERENCES `employee_monthly_salaries` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employee_variable_salaries_variable_component_id_foreign` FOREIGN KEY (`variable_component_id`) REFERENCES `salary_components` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_variable_salaries');
    }
};
