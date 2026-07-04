<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `purchase_item_taxes` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `purchase_order_id` int unsigned DEFAULT NULL,
  `purchase_item_id` int unsigned DEFAULT NULL,
  `tax_id` int unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `purchase_item_taxes_purchase_order_id_foreign` (`purchase_order_id`),
  KEY `purchase_item_taxes_purchase_item_id_foreign` (`purchase_item_id`),
  KEY `purchase_item_taxes_tax_id_foreign` (`tax_id`),
  CONSTRAINT `purchase_item_taxes_purchase_item_id_foreign` FOREIGN KEY (`purchase_item_id`) REFERENCES `purchase_items` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `purchase_item_taxes_purchase_order_id_foreign` FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `purchase_item_taxes_tax_id_foreign` FOREIGN KEY (`tax_id`) REFERENCES `taxes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_item_taxes');
    }
};
