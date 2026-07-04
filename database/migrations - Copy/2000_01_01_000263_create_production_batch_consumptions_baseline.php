<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `production_batch_consumptions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned DEFAULT NULL,
  `production_batch_id` bigint unsigned NOT NULL,
  `component_product_id` int unsigned NOT NULL,
  `warehouse_product_batch_id` bigint unsigned DEFAULT NULL,
  `planned_quantity` decimal(15,4) NOT NULL,
  `planned_quantity_shadow` decimal(20,6) DEFAULT NULL,
  `shadow_basis` json DEFAULT NULL,
  `actual_quantity` decimal(15,4) DEFAULT NULL,
  `unit_id` bigint unsigned DEFAULT NULL,
  `line_order` int unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `production_batch_consumptions_company_id_foreign` (`company_id`),
  KEY `production_batch_consumptions_component_product_id_foreign` (`component_product_id`),
  KEY `production_batch_consumptions_warehouse_product_batch_id_foreign` (`warehouse_product_batch_id`),
  KEY `production_batch_consumptions_batch_idx` (`production_batch_id`),
  CONSTRAINT `production_batch_consumptions_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `production_batch_consumptions_component_product_id_foreign` FOREIGN KEY (`component_product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `production_batch_consumptions_production_batch_id_foreign` FOREIGN KEY (`production_batch_id`) REFERENCES `production_batches` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `production_batch_consumptions_warehouse_product_batch_id_foreign` FOREIGN KEY (`warehouse_product_batch_id`) REFERENCES `warehouse_product_batches` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('production_batch_consumptions');
    }
};
