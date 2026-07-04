<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `key_results` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned DEFAULT NULL,
  `objective_id` bigint unsigned NOT NULL,
  `title` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `metrics_id` int unsigned DEFAULT NULL,
  `target_value` decimal(16,2) DEFAULT NULL,
  `current_value` decimal(16,2) DEFAULT NULL,
  `original_current_value` double DEFAULT NULL,
  `key_percentage` double DEFAULT NULL,
  `last_check_in` date DEFAULT NULL,
  `next_check_in` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `key_results_company_id_foreign` (`company_id`),
  KEY `key_results_objective_id_foreign` (`objective_id`),
  KEY `key_results_metrics_id_foreign` (`metrics_id`),
  CONSTRAINT `key_results_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `key_results_objective_id_foreign` FOREIGN KEY (`objective_id`) REFERENCES `objectives` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('key_results');
    }
};
