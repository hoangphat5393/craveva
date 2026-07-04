<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `production_company_fg_policies` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned NOT NULL,
  `policy_mode` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'controlled',
  `tolerance_percent` decimal(10,4) NOT NULL DEFAULT '5.0000',
  `tolerance_absolute` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `controlled_require_reason_beyond_tolerance` tinyint(1) NOT NULL DEFAULT '1',
  `controlled_block_beyond_tolerance` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `production_company_fg_policies_company_id_unique` (`company_id`),
  CONSTRAINT `production_company_fg_policies_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('production_company_fg_policies');
    }
};
