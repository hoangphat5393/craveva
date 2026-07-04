<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `contracts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `contract_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `original_contract_number` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `company_id` int unsigned DEFAULT NULL,
  `client_id` int unsigned NOT NULL,
  `project_id` int unsigned DEFAULT NULL,
  `subject` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `original_amount` decimal(15,2) NOT NULL,
  `contract_type_id` bigint unsigned DEFAULT NULL,
  `start_date` date NOT NULL,
  `original_start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `original_end_date` date DEFAULT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci,
  `contract_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alternate_address` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contract_note` text COLLATE utf8mb4_unicode_ci,
  `cell` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `office` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `postal_code` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contract_detail` longtext COLLATE utf8mb4_unicode_ci,
  `added_by` int unsigned DEFAULT NULL,
  `last_updated_by` int unsigned DEFAULT NULL,
  `hash` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `currency_id` int unsigned DEFAULT NULL,
  `event_id` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `company_sign` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sign_date` datetime DEFAULT NULL,
  `sign_by` int unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `contracts_company_id_foreign` (`company_id`),
  KEY `contracts_client_id_foreign` (`client_id`),
  KEY `contracts_contract_type_id_foreign` (`contract_type_id`),
  KEY `contracts_added_by_foreign` (`added_by`),
  KEY `contracts_last_updated_by_foreign` (`last_updated_by`),
  KEY `contracts_currency_id_foreign` (`currency_id`),
  KEY `contracts_project_id_foreign` (`project_id`),
  KEY `contracts_sign_by_foreign` (`sign_by`),
  CONSTRAINT `contracts_added_by_foreign` FOREIGN KEY (`added_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `contracts_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `contracts_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `contracts_contract_type_id_foreign` FOREIGN KEY (`contract_type_id`) REFERENCES `contract_types` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `contracts_currency_id_foreign` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `contracts_last_updated_by_foreign` FOREIGN KEY (`last_updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `contracts_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `contracts_sign_by_foreign` FOREIGN KEY (`sign_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
