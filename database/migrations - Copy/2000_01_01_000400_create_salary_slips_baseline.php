<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `salary_slips` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned DEFAULT NULL,
  `currency_id` int unsigned DEFAULT NULL,
  `user_id` int unsigned NOT NULL,
  `salary_group_id` bigint unsigned DEFAULT NULL,
  `basic_salary` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `net_salary` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `month` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `year` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `paid_on` date DEFAULT NULL,
  `status` enum('generated','review','locked','paid') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'generated',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `salary_json` text COLLATE utf8mb4_unicode_ci,
  `extra_json` text COLLATE utf8mb4_unicode_ci,
  `expense_claims` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `pay_days` int NOT NULL,
  `salary_payment_method_id` bigint unsigned DEFAULT NULL,
  `tds` decimal(16,2) NOT NULL,
  `monthly_salary` decimal(16,2) NOT NULL,
  `gross_salary` decimal(16,2) NOT NULL,
  `total_deductions` decimal(16,2) NOT NULL,
  `added_by` int unsigned DEFAULT NULL,
  `last_updated_by` int unsigned DEFAULT NULL,
  `salary_from` datetime DEFAULT NULL,
  `salary_to` datetime DEFAULT NULL,
  `payroll_cycle_id` bigint unsigned DEFAULT NULL,
  `fixed_allowance` double NOT NULL DEFAULT '0',
  `expenses_created` tinyint(1) NOT NULL DEFAULT '0',
  `expense_id` int unsigned DEFAULT NULL,
  `additional_earning_json` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `salary_slips_user_id_foreign` (`user_id`),
  KEY `salary_slips_salary_group_id_foreign` (`salary_group_id`),
  KEY `salary_slips_salary_payment_method_id_foreign` (`salary_payment_method_id`),
  KEY `salary_slips_added_by_foreign` (`added_by`),
  KEY `salary_slips_last_updated_by_foreign` (`last_updated_by`),
  KEY `salary_slips_company_id_foreign` (`company_id`),
  KEY `salary_slips_payroll_cycle_id_foreign` (`payroll_cycle_id`),
  KEY `salary_slips_currency_id_foreign` (`currency_id`),
  KEY `salary_slips_expense_id_foreign` (`expense_id`),
  CONSTRAINT `salary_slips_added_by_foreign` FOREIGN KEY (`added_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `salary_slips_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `salary_slips_currency_id_foreign` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `salary_slips_expense_id_foreign` FOREIGN KEY (`expense_id`) REFERENCES `expenses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `salary_slips_last_updated_by_foreign` FOREIGN KEY (`last_updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `salary_slips_payroll_cycle_id_foreign` FOREIGN KEY (`payroll_cycle_id`) REFERENCES `payroll_cycles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `salary_slips_salary_group_id_foreign` FOREIGN KEY (`salary_group_id`) REFERENCES `salary_groups` (`id`) ON DELETE CASCADE,
  CONSTRAINT `salary_slips_salary_payment_method_id_foreign` FOREIGN KEY (`salary_payment_method_id`) REFERENCES `salary_payment_methods` (`id`) ON DELETE SET NULL,
  CONSTRAINT `salary_slips_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_slips');
    }
};
