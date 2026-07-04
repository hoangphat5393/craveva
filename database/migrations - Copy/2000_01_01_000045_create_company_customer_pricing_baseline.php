<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `company_customer_pricing` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned NOT NULL,
  `client_id` int unsigned DEFAULT NULL,
  `pricing_tier_id` bigint unsigned DEFAULT NULL,
  `custom_discount_type` enum('percentage','fixed_amount') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `custom_discount_value` decimal(15,4) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `valid_from` date DEFAULT NULL,
  `valid_to` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `company_customer_pricing_company_id_client_id_unique` (`company_id`,`client_id`),
  KEY `company_customer_pricing_company_id_index` (`company_id`),
  KEY `company_customer_pricing_customer_company_id_index` (`client_id`),
  KEY `company_customer_pricing_pricing_tier_id_index` (`pricing_tier_id`),
  CONSTRAINT `company_customer_pricing_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `company_customer_pricing_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `company_customer_pricing_pricing_tier_id_foreign` FOREIGN KEY (`pricing_tier_id`) REFERENCES `pricing_tiers` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('company_customer_pricing');
    }
};
