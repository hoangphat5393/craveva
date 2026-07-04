<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `order_import_rows` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint unsigned NOT NULL,
  `order_id` bigint unsigned DEFAULT NULL,
  `order_item_id` bigint unsigned DEFAULT NULL,
  `source_hash` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `shipment_date` date DEFAULT NULL,
  `customer_code` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_sku` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `net_sales_volume` decimal(30,6) DEFAULT NULL,
  `net_sales_amount` decimal(30,6) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_import_rows_company_hash_unique` (`company_id`,`source_hash`),
  KEY `order_import_rows_company_id_index` (`company_id`),
  KEY `order_import_rows_order_id_index` (`order_id`),
  KEY `order_import_rows_order_item_id_index` (`order_item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('order_import_rows');
    }
};
