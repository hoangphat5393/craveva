<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `orders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `original_order_number` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `company_id` int unsigned DEFAULT NULL,
  `client_id` int unsigned DEFAULT NULL,
  `project_id` int unsigned DEFAULT NULL,
  `estimate_id` bigint unsigned DEFAULT NULL,
  `order_date` date NOT NULL,
  `sub_total` decimal(30,2) NOT NULL,
  `discount` decimal(30,2) NOT NULL DEFAULT '0.00',
  `discount_type` enum('percent','fixed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'percent',
  `total` decimal(30,2) NOT NULL,
  `status` enum('pending','on-hold','failed','processing','completed','canceled','refunded') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `currency_id` int unsigned DEFAULT NULL,
  `show_shipping_address` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `note` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `added_by` int unsigned DEFAULT NULL,
  `last_updated_by` int unsigned DEFAULT NULL,
  `company_address_id` bigint unsigned DEFAULT NULL,
  `custom_order_number` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `due_amount` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `orders_company_id_foreign` (`company_id`),
  KEY `orders_client_id_foreign` (`client_id`),
  KEY `orders_currency_id_foreign` (`currency_id`),
  KEY `orders_added_by_foreign` (`added_by`),
  KEY `orders_last_updated_by_foreign` (`last_updated_by`),
  KEY `orders_company_address_id_foreign` (`company_address_id`),
  KEY `orders_project_id_foreign` (`project_id`),
  KEY `orders_estimate_id_index` (`estimate_id`),
  CONSTRAINT `orders_added_by_foreign` FOREIGN KEY (`added_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `orders_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `orders_company_address_id_foreign` FOREIGN KEY (`company_address_id`) REFERENCES `company_addresses` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `orders_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `orders_currency_id_foreign` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `orders_last_updated_by_foreign` FOREIGN KEY (`last_updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `orders_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
