<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `grn_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `grn_id` bigint unsigned NOT NULL,
  `legacy_delivery_order_item_id` bigint unsigned DEFAULT NULL,
  `purchase_item_id` bigint unsigned DEFAULT NULL,
  `product_id` bigint unsigned DEFAULT NULL,
  `batch_number` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `picking_rule_applied` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `qc_status` varchar(16) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `qc_reviewed_by` bigint unsigned DEFAULT NULL,
  `qc_reviewed_at` timestamp NULL DEFAULT NULL,
  `quantity_ordered` decimal(20,4) NOT NULL DEFAULT '0.0000',
  `quantity_received` decimal(20,4) NOT NULL DEFAULT '0.0000',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `grn_items_legacy_unique` (`legacy_delivery_order_item_id`),
  KEY `grn_items_grn_item_idx` (`grn_id`,`purchase_item_id`),
  CONSTRAINT `grn_items_grn_id_foreign` FOREIGN KEY (`grn_id`) REFERENCES `grns` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('grn_items');
    }
};
