<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `purchase_orders` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned DEFAULT NULL,
  `purchase_order_number` int DEFAULT NULL,
  `vendor_id` int unsigned DEFAULT NULL,
  `bank_account_id` int unsigned DEFAULT NULL,
  `address_id` bigint unsigned DEFAULT NULL,
  `warehouse_id` bigint unsigned DEFAULT NULL,
  `currency_id` int unsigned DEFAULT NULL,
  `default_currency_id` int unsigned DEFAULT NULL,
  `exchange_rate` decimal(16,2) DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `expected_delivery_date` date DEFAULT NULL,
  `discount` decimal(16,2) NOT NULL DEFAULT '0.00',
  `sub_total` decimal(16,2) NOT NULL DEFAULT '0.00',
  `total` decimal(16,2) NOT NULL DEFAULT '0.00',
  `discount_type` enum('percent','fixed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'percent',
  `send_status` tinyint(1) DEFAULT '0',
  `purchase_status` enum('draft','open','issued','accepted','rejected','canceled','closed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
  `billed_status` enum('billed','unbilled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unbilled',
  `delivery_status` enum('delivered','delivery_failed','in_transaction','not_started') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'not_started',
  `calculate_tax` enum('after_discount','before_discount') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'after_discount',
  `added_by` int unsigned DEFAULT NULL,
  `last_updated_by` int unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `purchase_orders_company_id_foreign` (`company_id`),
  KEY `purchase_orders_vendor_id_foreign` (`vendor_id`),
  KEY `purchase_orders_bank_account_id_foreign` (`bank_account_id`),
  KEY `purchase_orders_address_id_foreign` (`address_id`),
  KEY `purchase_orders_currency_id_foreign` (`currency_id`),
  KEY `purchase_orders_default_currency_id_foreign` (`default_currency_id`),
  KEY `purchase_orders_added_by_foreign` (`added_by`),
  KEY `purchase_orders_last_updated_by_foreign` (`last_updated_by`),
  KEY `purchase_orders_warehouse_id_index` (`warehouse_id`),
  CONSTRAINT `purchase_orders_added_by_foreign` FOREIGN KEY (`added_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `purchase_orders_address_id_foreign` FOREIGN KEY (`address_id`) REFERENCES `company_addresses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `purchase_orders_bank_account_id_foreign` FOREIGN KEY (`bank_account_id`) REFERENCES `bank_accounts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `purchase_orders_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `purchase_orders_currency_id_foreign` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `purchase_orders_default_currency_id_foreign` FOREIGN KEY (`default_currency_id`) REFERENCES `currencies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `purchase_orders_last_updated_by_foreign` FOREIGN KEY (`last_updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `purchase_orders_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `purchase_vendors` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `purchase_orders_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
