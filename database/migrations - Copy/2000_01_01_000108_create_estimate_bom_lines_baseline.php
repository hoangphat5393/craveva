<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `estimate_bom_lines` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned NOT NULL,
  `estimate_id` int unsigned NOT NULL,
  `product_id` int unsigned DEFAULT NULL,
  `material_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` decimal(16,4) NOT NULL,
  `unit_id` int unsigned DEFAULT NULL,
  `unit_cost` decimal(16,4) NOT NULL DEFAULT '0.0000',
  `line_total` decimal(16,4) NOT NULL DEFAULT '0.0000',
  `sort_order` smallint unsigned NOT NULL DEFAULT '0',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `estimate_bom_lines_company_id_index` (`company_id`),
  KEY `estimate_bom_lines_estimate_id_index` (`estimate_id`),
  KEY `estimate_bom_lines_product_id_index` (`product_id`),
  KEY `estimate_bom_lines_unit_id_index` (`unit_id`),
  CONSTRAINT `estimate_bom_lines_estimate_id_foreign` FOREIGN KEY (`estimate_id`) REFERENCES `estimates` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('estimate_bom_lines');
    }
};
