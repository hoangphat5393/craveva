<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `warehouse_company_flow_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned NOT NULL,
  `allow_negative_stock` tinyint(1) NOT NULL DEFAULT '0',
  `strict_unit_conversion` tinyint(1) NOT NULL DEFAULT '0',
  `inbound_from_purchase_order_delivered` tinyint(1) NOT NULL DEFAULT '1',
  `inbound_from_delivery_order_received` tinyint(1) NOT NULL DEFAULT '0',
  `sales_outbound_enabled` tinyint(1) NOT NULL DEFAULT '1',
  `sales_outbound_mode` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'shipment',
  `ai_order_webhook_check_stock` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `warehouse_company_flow_settings_company_id_unique` (`company_id`),
  CONSTRAINT `warehouse_company_flow_settings_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouse_company_flow_settings');
    }
};
