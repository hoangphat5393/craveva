<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `purchase_inventory_adjustment` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned DEFAULT NULL,
  `reason_id` int unsigned DEFAULT NULL,
  `type` enum('quantity','value') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'quantity',
  `date` date DEFAULT NULL,
  `warehouse_id` bigint unsigned DEFAULT NULL,
  `added_by` int unsigned DEFAULT NULL,
  `default_image` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `purchase_inventory_adjustment_reason_id_foreign` (`reason_id`),
  KEY `purchase_inventory_adjustment_warehouse_id_foreign` (`warehouse_id`),
  KEY `purchase_inventory_adjustment_added_by_foreign` (`added_by`),
  KEY `pia_company_created_idx` (`company_id`,`created_at`),
  CONSTRAINT `purchase_inventory_adjustment_added_by_foreign` FOREIGN KEY (`added_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `purchase_inventory_adjustment_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `purchase_inventory_adjustment_reason_id_foreign` FOREIGN KEY (`reason_id`) REFERENCES `purchase_stock_adjustment_reasons` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `purchase_inventory_adjustment_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_inventory_adjustment');
    }
};
