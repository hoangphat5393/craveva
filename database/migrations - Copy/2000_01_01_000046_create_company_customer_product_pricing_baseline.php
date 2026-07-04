<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `company_customer_product_pricing` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_customer_pricing_id` bigint unsigned NOT NULL,
  `product_id` int unsigned NOT NULL,
  `custom_price` decimal(15,4) DEFAULT NULL,
  `custom_discount_type` enum('percentage','fixed_amount') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `custom_discount_value` decimal(15,4) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ccpp_unique_pricing_product` (`company_customer_pricing_id`,`product_id`),
  KEY `company_customer_product_pricing_product_id_foreign` (`product_id`),
  CONSTRAINT `ccpp_cc_pricing_id_fk` FOREIGN KEY (`company_customer_pricing_id`) REFERENCES `company_customer_pricing` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `company_customer_product_pricing_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('company_customer_product_pricing');
    }
};
