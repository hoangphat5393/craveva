<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `production_rework_orders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned NOT NULL,
  `source_production_batch_id` bigint unsigned NOT NULL,
  `rework_production_order_id` bigint unsigned DEFAULT NULL,
  `requested_quantity` decimal(20,4) NOT NULL,
  `approved_quantity` decimal(20,4) DEFAULT NULL,
  `status` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'requested',
  `reason` text COLLATE utf8mb4_unicode_ci,
  `decision_note` text COLLATE utf8mb4_unicode_ci,
  `requested_by` int unsigned DEFAULT NULL,
  `approved_by` int unsigned DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `production_rework_orders_company_status_idx` (`company_id`,`status`),
  KEY `production_rework_orders_source_batch_idx` (`source_production_batch_id`),
  KEY `production_rework_orders_target_order_idx` (`rework_production_order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('production_rework_orders');
    }
};
