<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `employee_monthly_salaries` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned DEFAULT NULL,
  `user_id` int unsigned NOT NULL,
  `annual_salary` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `basic_salary` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fixed_allowance` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `basic_value_type` enum('fixed','ctc_percent') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` enum('initial','increment','decrement') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'initial',
  `date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `allow_generate_payroll` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'yes',
  `effective_annual_salary` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `effective_monthly_salary` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_monthly_salaries_user_id_foreign` (`user_id`),
  KEY `employee_monthly_salaries_company_id_foreign` (`company_id`),
  CONSTRAINT `employee_monthly_salaries_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `employee_monthly_salaries_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_monthly_salaries');
    }
};
