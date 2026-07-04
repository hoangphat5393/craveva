<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `project_time_log_breaks` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned DEFAULT NULL,
  `project_time_log_id` int unsigned DEFAULT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime DEFAULT NULL,
  `reason` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_hours` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total_minutes` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `added_by` int unsigned DEFAULT NULL,
  `last_updated_by` int unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `project_time_log_breaks_company_id_foreign` (`company_id`),
  KEY `project_time_log_breaks_project_time_log_id_foreign` (`project_time_log_id`),
  KEY `project_time_log_breaks_start_time_index` (`start_time`),
  KEY `project_time_log_breaks_end_time_index` (`end_time`),
  KEY `project_time_log_breaks_added_by_foreign` (`added_by`),
  KEY `project_time_log_breaks_last_updated_by_foreign` (`last_updated_by`),
  CONSTRAINT `project_time_log_breaks_added_by_foreign` FOREIGN KEY (`added_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `project_time_log_breaks_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `project_time_log_breaks_last_updated_by_foreign` FOREIGN KEY (`last_updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `project_time_log_breaks_project_time_log_id_foreign` FOREIGN KEY (`project_time_log_id`) REFERENCES `project_time_logs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('project_time_log_breaks');
    }
};
