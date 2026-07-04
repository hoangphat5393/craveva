<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `check_ins` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned DEFAULT NULL,
  `key_result_id` bigint unsigned NOT NULL,
  `progress_update` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `current_value` double DEFAULT NULL,
  `objective_percentage` decimal(8,2) NOT NULL,
  `confidence_level` enum('low','medium','high') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'low',
  `barriers` text COLLATE utf8mb4_unicode_ci,
  `check_in_date` datetime DEFAULT NULL,
  `check_in_by` int unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `check_ins_company_id_foreign` (`company_id`),
  KEY `check_ins_key_result_id_foreign` (`key_result_id`),
  KEY `check_ins_users_id_foreign` (`check_in_by`),
  CONSTRAINT `check_ins_check_in_by_foreign` FOREIGN KEY (`check_in_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `check_ins_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `check_ins_key_result_id_foreign` FOREIGN KEY (`key_result_id`) REFERENCES `key_results` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('check_ins');
    }
};
