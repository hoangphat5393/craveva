<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `e_invoice_company_settings` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned DEFAULT NULL,
  `electronic_address` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `electronic_address_scheme` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `e_invoice_company_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `e_invoice_company_id_scheme` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `e_invoice_company_settings_company_id_foreign` (`company_id`),
  CONSTRAINT `e_invoice_company_settings_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('e_invoice_company_settings');
    }
};
