<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `purchase_vendor_items` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `credit_id` int unsigned DEFAULT NULL,
  `item_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `item_summary` text COLLATE utf8mb4_unicode_ci,
  `type` enum('item','discount','tax') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'item',
  `quantity` decimal(16,2) NOT NULL,
  `unit_price` decimal(16,2) NOT NULL,
  `amount` decimal(16,2) NOT NULL,
  `taxes` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_id` int unsigned DEFAULT NULL,
  `unit_id` bigint unsigned DEFAULT NULL,
  `hsn_sac_code` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `warehouse_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `purchase_vendor_items_credit_id_foreign` (`credit_id`),
  KEY `purchase_vendor_items_product_id_foreign` (`product_id`),
  KEY `purchase_vendor_items_unit_id_foreign` (`unit_id`),
  CONSTRAINT `purchase_vendor_items_credit_id_foreign` FOREIGN KEY (`credit_id`) REFERENCES `purchase_vendor_credits` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `purchase_vendor_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `purchase_vendor_items_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `unit_types` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_vendor_items');
    }
};
