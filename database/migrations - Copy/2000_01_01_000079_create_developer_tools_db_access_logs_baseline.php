<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `developer_tools_db_access_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned NOT NULL,
  `db_username` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `db_database` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `requested_modules` json DEFAULT NULL,
  `allowed_tables` json DEFAULT NULL,
  `allowed_tables_count` int unsigned DEFAULT NULL,
  `created_views_count` int unsigned DEFAULT NULL,
  `duration_ms` int unsigned DEFAULT NULL,
  `status` enum('success','failed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'success',
  `warnings` text COLLATE utf8mb4_unicode_ci,
  `error_message` text COLLATE utf8mb4_unicode_ci,
  `created_by` int unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `developer_tools_db_access_logs_company_id_index` (`company_id`),
  KEY `developer_tools_db_access_logs_db_username_index` (`db_username`),
  KEY `developer_tools_db_access_logs_db_database_index` (`db_database`),
  KEY `developer_tools_db_access_logs_status_index` (`status`),
  KEY `developer_tools_db_access_logs_created_by_index` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('developer_tools_db_access_logs');
    }
};
