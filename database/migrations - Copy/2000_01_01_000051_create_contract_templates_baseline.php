<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `contract_templates` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `contract_template_number` bigint DEFAULT NULL,
  `company_id` int unsigned DEFAULT NULL,
  `subject` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci,
  `amount` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contract_type_id` bigint unsigned NOT NULL,
  `currency_id` int unsigned DEFAULT NULL,
  `contract_detail` longtext COLLATE utf8mb4_unicode_ci,
  `added_by` int NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `contract_templates_company_id_foreign` (`company_id`),
  KEY `contract_templates_contract_type_id_foreign` (`contract_type_id`),
  KEY `contract_templates_currency_id_foreign` (`currency_id`),
  CONSTRAINT `contract_templates_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `contract_templates_contract_type_id_foreign` FOREIGN KEY (`contract_type_id`) REFERENCES `contract_types` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `contract_templates_currency_id_foreign` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_templates');
    }
};
