<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `volume_discount_rules` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned DEFAULT NULL,
  `pricing_tier_id` bigint unsigned DEFAULT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `discount_type` enum('percentage','fixed_amount','tiered') COLLATE utf8mb4_unicode_ci NOT NULL,
  `minimum_quantity` int unsigned NOT NULL,
  `maximum_quantity` int unsigned DEFAULT NULL,
  `discount_value` decimal(15,4) DEFAULT NULL,
  `applies_to_product_id` int unsigned DEFAULT NULL,
  `applies_to_category_id` bigint unsigned DEFAULT NULL,
  `applies_to_type` enum('all','products','services','specific') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'all',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `volume_discount_rules_company_id_index` (`company_id`),
  KEY `volume_discount_rules_pricing_tier_id_index` (`pricing_tier_id`),
  KEY `volume_discount_rules_applies_to_product_id_index` (`applies_to_product_id`),
  KEY `volume_discount_rules_applies_to_category_id_index` (`applies_to_category_id`),
  CONSTRAINT `volume_discount_rules_applies_to_category_id_foreign` FOREIGN KEY (`applies_to_category_id`) REFERENCES `product_category` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `volume_discount_rules_applies_to_product_id_foreign` FOREIGN KEY (`applies_to_product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `volume_discount_rules_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `volume_discount_rules_pricing_tier_id_foreign` FOREIGN KEY (`pricing_tier_id`) REFERENCES `pricing_tiers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('volume_discount_rules');
    }
};
