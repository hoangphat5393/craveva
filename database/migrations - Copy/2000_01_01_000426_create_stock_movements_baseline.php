<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `stock_movements` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint unsigned DEFAULT NULL,
  `product_id` bigint unsigned DEFAULT NULL,
  `delivery_order_item_id` bigint unsigned DEFAULT NULL,
  `movement_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `warehouse_from_id` bigint unsigned DEFAULT NULL,
  `warehouse_to_id` bigint unsigned DEFAULT NULL,
  `warehouse_location_from_id` bigint unsigned DEFAULT NULL,
  `warehouse_location_to_id` bigint unsigned DEFAULT NULL,
  `batch_number` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `quantity` decimal(15,4) NOT NULL,
  `unit_id` bigint unsigned DEFAULT NULL,
  `fefo_fifo_rule` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference_type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference_id` bigint unsigned DEFAULT NULL,
  `idempotency_key` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `stock_movements_company_type_created_idx` (`company_id`,`movement_type`,`created_at`),
  KEY `stock_movements_wh_from_to_product_idx` (`warehouse_from_id`,`warehouse_to_id`,`product_id`),
  KEY `stock_movements_product_batch_expiry_idx` (`product_id`,`batch_number`,`expiry_date`),
  KEY `stock_movements_reference_idx` (`reference_type`,`reference_id`),
  KEY `stock_movement_company_idempotency_idx` (`company_id`,`idempotency_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
