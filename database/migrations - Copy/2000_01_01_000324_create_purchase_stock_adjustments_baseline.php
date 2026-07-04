<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `purchase_stock_adjustments` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned DEFAULT NULL,
  `inventory_id` int unsigned DEFAULT NULL,
  `product_id` int unsigned DEFAULT NULL,
  `warehouse_id` bigint unsigned DEFAULT NULL,
  `batch_number` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reason_id` int unsigned DEFAULT NULL,
  `type` enum('quantity','value') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'quantity',
  `date` date DEFAULT NULL,
  `reference_number` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `net_quantity` decimal(16,2) DEFAULT NULL,
  `reserved_quantity` decimal(15,4) DEFAULT '0.0000',
  `quantity_adjustment` int DEFAULT '0',
  `description` text COLLATE utf8mb4_unicode_ci,
  `manufacturing_date` date DEFAULT NULL,
  `expiration_date` date DEFAULT NULL,
  `status` enum('draft','converted') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `changed_value` decimal(16,2) DEFAULT '0.00',
  `adjusted_value` decimal(16,2) DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `psa_inventory_product_idx` (`inventory_id`,`product_id`),
  KEY `purchase_stock_adjustments_company_id_foreign` (`company_id`),
  KEY `purchase_stock_adjustments_product_id_foreign` (`product_id`),
  KEY `purchase_stock_adjustments_reason_id_foreign` (`reason_id`),
  KEY `purchase_stock_adjustments_warehouse_id_foreign` (`warehouse_id`),
  CONSTRAINT `purchase_stock_adjustments_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `purchase_stock_adjustments_inventory_id_foreign` FOREIGN KEY (`inventory_id`) REFERENCES `purchase_inventory_adjustment` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `purchase_stock_adjustments_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `purchase_stock_adjustments_reason_id_foreign` FOREIGN KEY (`reason_id`) REFERENCES `purchase_stock_adjustment_reasons` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `purchase_stock_adjustments_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_stock_adjustments');
    }
};
