<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `products` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned DEFAULT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `wholesale_price` double DEFAULT NULL,
  `price_per_box` double DEFAULT NULL,
  `employee_price` double DEFAULT NULL,
  `shelf_life_days` int unsigned DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `storage_condition` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `certification` text COLLATE utf8mb4_unicode_ci,
  `purchase_price` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cost_from_bom` tinyint(1) NOT NULL DEFAULT '0',
  `track_inventory` enum('1','0') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `inventory_type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sales_description` longtext COLLATE utf8mb4_unicode_ci,
  `purchase_description` longtext COLLATE utf8mb4_unicode_ci,
  `opening_stock` int DEFAULT NULL,
  `rate_per_unit` double DEFAULT NULL,
  `taxes` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `allow_purchase` tinyint(1) NOT NULL DEFAULT '0',
  `downloadable` tinyint(1) NOT NULL DEFAULT '0',
  `downloadable_file` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `specification` text COLLATE utf8mb4_unicode_ci,
  `product_source` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `brand` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_grade` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category_id` bigint unsigned DEFAULT NULL,
  `sub_category_id` bigint unsigned DEFAULT NULL,
  `added_by` int unsigned DEFAULT NULL,
  `last_updated_by` int unsigned DEFAULT NULL,
  `hsn_sac_code` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sku` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `default_image` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT 'goods',
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `unit_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `products_company_id_foreign` (`company_id`),
  KEY `products_category_id_foreign` (`category_id`),
  KEY `products_sub_category_id_foreign` (`sub_category_id`),
  KEY `products_added_by_foreign` (`added_by`),
  KEY `products_last_updated_by_foreign` (`last_updated_by`),
  KEY `products_unit_id_foreign` (`unit_id`),
  CONSTRAINT `products_added_by_foreign` FOREIGN KEY (`added_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `product_category` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `products_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `products_last_updated_by_foreign` FOREIGN KEY (`last_updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `products_sub_category_id_foreign` FOREIGN KEY (`sub_category_id`) REFERENCES `product_sub_category` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `products_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `unit_types` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
