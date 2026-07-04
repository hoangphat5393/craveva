<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `warehouse_product_batches` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned DEFAULT NULL,
  `warehouse_id` bigint unsigned NOT NULL,
  `product_id` int unsigned NOT NULL,
  `batch_number` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expiration_date` date DEFAULT NULL,
  `manufacturing_date` date DEFAULT NULL,
  `quantity` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `reserved_quantity` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `wpb_company_wh_product_batch_exp_unique` (`company_id`,`warehouse_id`,`product_id`,`batch_number`,`expiration_date`),
  KEY `warehouse_product_batches_product_id_foreign` (`product_id`),
  KEY `wpb_wh_product_idx` (`warehouse_id`,`product_id`),
  KEY `wpb_lookup_idx` (`company_id`,`warehouse_id`,`product_id`,`batch_number`,`expiration_date`),
  CONSTRAINT `warehouse_product_batches_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `warehouse_product_batches_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `warehouse_product_batches_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouse_product_batches');
    }
};
