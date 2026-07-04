<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `production_orders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned DEFAULT NULL,
  `status` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `output_product_id` int unsigned NOT NULL,
  `production_bom_id` bigint unsigned DEFAULT NULL,
  `rm_warehouse_id` bigint unsigned NOT NULL,
  `fg_warehouse_id` bigint unsigned NOT NULL,
  `planned_quantity` decimal(15,4) NOT NULL,
  `sales_order_id` bigint unsigned DEFAULT NULL,
  `project_id` bigint unsigned DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `released_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `bom_snapshot_at` timestamp NULL DEFAULT NULL,
  `bom_snapshot_planned_quantity` decimal(15,4) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `production_orders_output_product_id_foreign` (`output_product_id`),
  KEY `production_orders_production_bom_id_foreign` (`production_bom_id`),
  KEY `production_orders_rm_warehouse_id_foreign` (`rm_warehouse_id`),
  KEY `production_orders_fg_warehouse_id_foreign` (`fg_warehouse_id`),
  KEY `production_orders_company_status_idx` (`company_id`,`status`),
  CONSTRAINT `production_orders_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `production_orders_fg_warehouse_id_foreign` FOREIGN KEY (`fg_warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `production_orders_output_product_id_foreign` FOREIGN KEY (`output_product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `production_orders_production_bom_id_foreign` FOREIGN KEY (`production_bom_id`) REFERENCES `production_boms` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `production_orders_rm_warehouse_id_foreign` FOREIGN KEY (`rm_warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('production_orders');
    }
};
