<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `credit_note_items` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `credit_note_id` int unsigned NOT NULL,
  `item_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('item','discount','tax') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'item',
  `quantity` int NOT NULL,
  `unit_price` decimal(30,2) NOT NULL,
  `amount` decimal(30,2) NOT NULL,
  `taxes` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hsn_sac_code` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `item_summary` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `unit_id` bigint unsigned DEFAULT NULL,
  `product_id` int unsigned DEFAULT NULL,
  `warehouse_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `credit_note_items_credit_note_id_foreign` (`credit_note_id`),
  KEY `credit_note_items_unit_id_foreign` (`unit_id`),
  KEY `credit_note_items_product_id_foreign` (`product_id`),
  CONSTRAINT `credit_note_items_credit_note_id_foreign` FOREIGN KEY (`credit_note_id`) REFERENCES `credit_notes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `credit_note_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `credit_note_items_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `unit_types` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_note_items');
    }
};
