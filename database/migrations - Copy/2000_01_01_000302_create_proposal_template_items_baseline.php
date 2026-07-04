<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `proposal_template_items` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned DEFAULT NULL,
  `proposal_template_id` bigint unsigned NOT NULL,
  `hsn_sac_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `item_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('item','discount','tax') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'item',
  `quantity` decimal(30,2) NOT NULL,
  `unit_price` decimal(30,2) NOT NULL,
  `amount` decimal(30,2) NOT NULL,
  `item_summary` text COLLATE utf8mb4_unicode_ci,
  `taxes` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `unit_id` bigint unsigned DEFAULT NULL,
  `product_id` int unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `proposal_template_items_company_id_foreign` (`company_id`),
  KEY `proposal_template_items_proposal_template_id_foreign` (`proposal_template_id`),
  KEY `proposal_template_items_unit_id_foreign` (`unit_id`),
  KEY `proposal_template_items_product_id_foreign` (`product_id`),
  CONSTRAINT `proposal_template_items_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `proposal_template_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `proposal_template_items_proposal_template_id_foreign` FOREIGN KEY (`proposal_template_id`) REFERENCES `proposal_templates` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `proposal_template_items_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `unit_types` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('proposal_template_items');
    }
};
