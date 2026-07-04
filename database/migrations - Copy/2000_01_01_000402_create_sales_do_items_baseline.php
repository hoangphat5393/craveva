<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `sales_do_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `sales_do_id` bigint unsigned NOT NULL,
  `legacy_sales_shipment_item_id` bigint unsigned DEFAULT NULL,
  `order_item_id` bigint unsigned NOT NULL,
  `product_id` int unsigned DEFAULT NULL,
  `quantity_ordered` decimal(20,4) NOT NULL DEFAULT '0.0000',
  `quantity_shipped` decimal(20,4) NOT NULL DEFAULT '0.0000',
  `unit_id` bigint unsigned DEFAULT NULL,
  `warehouse_batch_id` bigint unsigned DEFAULT NULL,
  `batch_number` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expiration_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sales_do_items_legacy_unique` (`legacy_sales_shipment_item_id`),
  KEY `sales_do_items_do_item_idx` (`sales_do_id`,`order_item_id`),
  KEY `sales_do_items_do_batch_idx` (`sales_do_id`,`warehouse_batch_id`),
  CONSTRAINT `sales_do_items_sales_do_id_foreign` FOREIGN KEY (`sales_do_id`) REFERENCES `sales_dos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_do_items');
    }
};
