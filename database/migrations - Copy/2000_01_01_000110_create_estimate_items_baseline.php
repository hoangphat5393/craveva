<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `estimate_items` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `estimate_id` int unsigned NOT NULL,
  `item_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `item_summary` text COLLATE utf8mb4_unicode_ci,
  `type` enum('item','discount','tax') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'item',
  `quantity` decimal(30,2) NOT NULL,
  `unit_price` decimal(30,2) NOT NULL,
  `amount` decimal(30,2) NOT NULL,
  `taxes` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hsn_sac_code` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `unit_id` bigint unsigned DEFAULT NULL,
  `product_id` int unsigned DEFAULT NULL,
  `field_order` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `estimate_items_estimate_id_foreign` (`estimate_id`),
  KEY `estimate_items_unit_id_foreign` (`unit_id`),
  KEY `estimate_items_product_id_foreign` (`product_id`),
  CONSTRAINT `estimate_items_estimate_id_foreign` FOREIGN KEY (`estimate_id`) REFERENCES `estimates` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `estimate_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `estimate_items_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `unit_types` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('estimate_items');
    }
};
