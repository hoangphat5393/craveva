<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `goal_types` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned DEFAULT NULL,
  `type` enum('individual','department','company') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'individual',
  `view_by_owner` tinyint(1) NOT NULL DEFAULT '0',
  `manage_by_owner` tinyint(1) NOT NULL DEFAULT '0',
  `view_by_manager` tinyint(1) NOT NULL DEFAULT '0',
  `manage_by_manager` tinyint(1) NOT NULL DEFAULT '0',
  `view_by_roles` text COLLATE utf8mb4_unicode_ci,
  `manage_by_roles` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `goal_types_company_id_foreign` (`company_id`),
  CONSTRAINT `goal_types_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('goal_types');
    }
};
