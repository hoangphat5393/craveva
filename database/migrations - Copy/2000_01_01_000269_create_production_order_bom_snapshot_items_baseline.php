<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `production_order_bom_snapshot_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned DEFAULT NULL,
  `production_order_id` bigint unsigned NOT NULL,
  `component_product_id` int unsigned NOT NULL,
  `quantity_per_fg_unit` decimal(15,4) NOT NULL,
  `waste_percent` decimal(8,4) NOT NULL DEFAULT '0.0000',
  `sort_order` int unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `production_order_bom_snapshot_items_company_id_foreign` (`company_id`),
  KEY `production_order_bom_snapshot_items_component_product_id_foreign` (`component_product_id`),
  KEY `production_order_bom_snap_items_order_sort_idx` (`production_order_id`,`sort_order`),
  CONSTRAINT `production_order_bom_snapshot_items_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `production_order_bom_snapshot_items_component_product_id_foreign` FOREIGN KEY (`component_product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `production_order_bom_snapshot_items_production_order_id_foreign` FOREIGN KEY (`production_order_id`) REFERENCES `production_orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('production_order_bom_snapshot_items');
    }
};
