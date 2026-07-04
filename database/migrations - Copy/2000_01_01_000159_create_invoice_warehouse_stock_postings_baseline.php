<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `invoice_warehouse_stock_postings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned DEFAULT NULL,
  `invoice_id` int unsigned NOT NULL,
  `invoice_item_id` int unsigned NOT NULL,
  `warehouse_id` bigint unsigned NOT NULL,
  `product_id` int unsigned NOT NULL,
  `quantity` decimal(15,4) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `iwsp_invoice_item_unique` (`invoice_id`,`invoice_item_id`),
  KEY `iwsp_invoice_company_idx` (`invoice_id`,`company_id`),
  KEY `invoice_warehouse_stock_postings_invoice_item_id_foreign` (`invoice_item_id`),
  KEY `invoice_warehouse_stock_postings_company_id_index` (`company_id`),
  CONSTRAINT `invoice_warehouse_stock_postings_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE,
  CONSTRAINT `invoice_warehouse_stock_postings_invoice_item_id_foreign` FOREIGN KEY (`invoice_item_id`) REFERENCES `invoice_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_warehouse_stock_postings');
    }
};
