<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `sales_dos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `legacy_sales_shipment_id` bigint unsigned DEFAULT NULL,
  `company_id` int unsigned NOT NULL,
  `order_id` bigint unsigned NOT NULL,
  `warehouse_id` bigint unsigned NOT NULL,
  `do_number` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `do_date` date NOT NULL,
  `status` enum('draft','confirmed','shipped','delivered','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `outbound_stock_applied` tinyint(1) NOT NULL DEFAULT '0',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sales_dos_company_number_unique` (`company_id`,`do_number`),
  UNIQUE KEY `sales_dos_legacy_unique` (`legacy_sales_shipment_id`),
  KEY `sales_dos_company_status_date_idx` (`company_id`,`status`,`do_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_dos');
    }
};
