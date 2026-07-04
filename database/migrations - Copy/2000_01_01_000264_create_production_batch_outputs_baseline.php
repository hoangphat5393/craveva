<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `production_batch_outputs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned DEFAULT NULL,
  `production_batch_id` bigint unsigned NOT NULL,
  `output_product_id` int unsigned NOT NULL,
  `quantity` decimal(15,4) NOT NULL,
  `batch_number` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration_date` date DEFAULT NULL,
  `manufacturing_date` date DEFAULT NULL,
  `warehouse_id` bigint unsigned NOT NULL,
  `posted_at` timestamp NULL DEFAULT NULL,
  `variance_reason` text COLLATE utf8mb4_unicode_ci,
  `variance_from_planned_total` decimal(15,4) DEFAULT NULL,
  `variance_from_planned_percent` decimal(15,4) DEFAULT NULL,
  `approved_by` bigint unsigned DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `production_batch_outputs_company_id_foreign` (`company_id`),
  KEY `production_batch_outputs_output_product_id_foreign` (`output_product_id`),
  KEY `production_batch_outputs_warehouse_id_foreign` (`warehouse_id`),
  KEY `production_batch_outputs_batch_idx` (`production_batch_id`),
  CONSTRAINT `production_batch_outputs_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `production_batch_outputs_output_product_id_foreign` FOREIGN KEY (`output_product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `production_batch_outputs_production_batch_id_foreign` FOREIGN KEY (`production_batch_id`) REFERENCES `production_batches` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `production_batch_outputs_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('production_batch_outputs');
    }
};
