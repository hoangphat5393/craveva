<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `sales_history_lines` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint unsigned NOT NULL,
  `sales_history_id` bigint unsigned DEFAULT NULL,
  `shipment_date` date NOT NULL,
  `client_id` bigint unsigned NOT NULL COMMENT 'users.id',
  `client_details_id` bigint unsigned DEFAULT NULL,
  `product_id` bigint unsigned NOT NULL,
  `quantity` decimal(30,6) NOT NULL,
  `quantity_abs` decimal(30,6) NOT NULL,
  `amount` decimal(30,6) DEFAULT NULL,
  `unit_price` decimal(30,6) DEFAULT NULL,
  `is_return` tinyint(1) NOT NULL DEFAULT '0',
  `currency_id` bigint unsigned DEFAULT NULL,
  `source_sheet_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `source_row_hash` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `net_sales_volume_raw` decimal(30,6) DEFAULT NULL,
  `net_sales_amount_raw` decimal(30,6) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sales_history_lines_company_hash_unique` (`company_id`,`source_row_hash`),
  KEY `sales_history_lines_company_id_shipment_date_index` (`company_id`,`shipment_date`),
  KEY `sales_history_lines_company_id_product_id_index` (`company_id`,`product_id`),
  KEY `sales_history_lines_company_id_index` (`company_id`),
  KEY `sales_history_lines_sales_history_id_index` (`sales_history_id`),
  KEY `sales_history_lines_client_id_index` (`client_id`),
  KEY `sales_history_lines_client_details_id_index` (`client_details_id`),
  KEY `sales_history_lines_product_id_index` (`product_id`),
  KEY `sales_history_lines_currency_id_index` (`currency_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_history_lines');
    }
};
