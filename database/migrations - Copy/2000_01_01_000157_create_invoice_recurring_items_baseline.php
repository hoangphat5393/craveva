<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `invoice_recurring_items` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `invoice_recurring_id` bigint unsigned NOT NULL,
  `item_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` decimal(30,2) NOT NULL,
  `unit_price` decimal(30,2) NOT NULL,
  `amount` decimal(30,2) NOT NULL,
  `taxes` text COLLATE utf8mb4_unicode_ci,
  `type` enum('item','discount','tax') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'item',
  `item_summary` text COLLATE utf8mb4_unicode_ci,
  `hsn_sac_code` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `unit_id` bigint unsigned DEFAULT NULL,
  `product_id` int unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `invoice_recurring_items_invoice_recurring_id_foreign` (`invoice_recurring_id`),
  KEY `invoice_recurring_items_unit_id_foreign` (`unit_id`),
  KEY `invoice_recurring_items_product_id_foreign` (`product_id`),
  CONSTRAINT `invoice_recurring_items_invoice_recurring_id_foreign` FOREIGN KEY (`invoice_recurring_id`) REFERENCES `invoice_recurring` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `invoice_recurring_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `invoice_recurring_items_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `unit_types` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_recurring_items');
    }
};
