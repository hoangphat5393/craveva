<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `grns` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `legacy_delivery_order_id` bigint unsigned DEFAULT NULL,
  `company_id` int unsigned DEFAULT NULL,
  `purchase_order_id` bigint unsigned DEFAULT NULL,
  `type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `grn_number` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `grn_date` date DEFAULT NULL,
  `warehouse_id` bigint unsigned DEFAULT NULL,
  `status` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `inbound_stock_applied` tinyint(1) NOT NULL DEFAULT '0',
  `erp_shipment_reference` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `wms_shipment_reference` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `delivery_fee` decimal(20,4) DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `grns_legacy_unique` (`legacy_delivery_order_id`),
  KEY `grns_company_status_date_idx` (`company_id`,`status`,`grn_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('grns');
    }
};
