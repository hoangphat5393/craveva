<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `pricing_tier_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `pricing_tier_id` bigint unsigned NOT NULL,
  `product_id` int unsigned NOT NULL,
  `discount_type` enum('percentage','fixed','specific_price') COLLATE utf8mb4_unicode_ci NOT NULL,
  `discount_value` decimal(15,2) NOT NULL DEFAULT '0.00',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pricing_tier_items_pricing_tier_id_product_id_unique` (`pricing_tier_id`,`product_id`),
  KEY `pricing_tier_items_product_id_foreign` (`product_id`),
  CONSTRAINT `pricing_tier_items_pricing_tier_id_foreign` FOREIGN KEY (`pricing_tier_id`) REFERENCES `pricing_tiers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `pricing_tier_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('pricing_tier_items');
    }
};
