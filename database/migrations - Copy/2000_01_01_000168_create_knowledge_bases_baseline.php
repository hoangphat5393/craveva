<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `knowledge_bases` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned DEFAULT NULL,
  `to` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'employee',
  `heading` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category_id` int unsigned DEFAULT NULL,
  `description` mediumtext COLLATE utf8mb4_unicode_ci,
  `added_by` int unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `knowledge_bases_company_id_foreign` (`company_id`),
  KEY `knowledge_bases_category_id_foreign` (`category_id`),
  CONSTRAINT `knowledge_bases_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `knowledge_categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `knowledge_bases_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('knowledge_bases');
    }
};
