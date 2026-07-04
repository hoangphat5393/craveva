<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `developer_tools_credentials` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned NOT NULL,
  `db_username` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `db_host` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '127.0.0.1',
  `db_port` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '3306',
  `db_database` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `allowed_modules` json DEFAULT NULL,
  `allowed_tables` json DEFAULT NULL,
  `created_views_count` int unsigned DEFAULT NULL,
  `generation_duration_ms` int unsigned DEFAULT NULL,
  `last_generated_at` timestamp NULL DEFAULT NULL,
  `last_generation_warnings` longtext COLLATE utf8mb4_unicode_ci,
  `created_by` int unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `developer_tools_credentials_company_id_foreign` (`company_id`),
  KEY `developer_tools_credentials_created_by_foreign` (`created_by`),
  CONSTRAINT `developer_tools_credentials_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `developer_tools_credentials_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('developer_tools_credentials');
    }
};
