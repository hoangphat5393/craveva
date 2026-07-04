<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `product_unit_conversions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned DEFAULT NULL,
  `product_id` int unsigned NOT NULL,
  `unit_id` bigint unsigned NOT NULL,
  `factor_to_base` decimal(20,8) NOT NULL DEFAULT '1.00000000',
  `selling_price` decimal(20,4) DEFAULT NULL,
  `cost_price` decimal(20,4) DEFAULT NULL,
  `for_sale` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` smallint unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `puc_company_product_unit_unique` (`company_id`,`product_id`,`unit_id`),
  KEY `puc_company_product_idx` (`company_id`,`product_id`),
  KEY `product_unit_conversions_company_id_index` (`company_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('product_unit_conversions');
    }
};
