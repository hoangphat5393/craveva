<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `client_details` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned DEFAULT NULL,
  `user_id` int unsigned NOT NULL,
  `company_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `client_code` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pricing_tier_id` bigint unsigned DEFAULT NULL,
  `default_warehouse_id` bigint unsigned DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `shipping_address` text COLLATE utf8mb4_unicode_ci,
  `postal_code` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `office` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `website` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `linkedin` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `facebook` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `twitter` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `skype` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tax_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gst_number` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category_id` bigint unsigned DEFAULT NULL,
  `sub_category_id` bigint unsigned DEFAULT NULL,
  `added_by` int unsigned DEFAULT NULL,
  `last_updated_by` int unsigned DEFAULT NULL,
  `company_logo` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `country_id` int unsigned DEFAULT NULL,
  `quickbooks_client_id` int DEFAULT NULL,
  `electronic_address` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `electronic_address_scheme` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_terms` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_grade` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `channel_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `business_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `business_closure_date` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `client_details_user_id_unique` (`user_id`),
  UNIQUE KEY `client_details_company_client_code_unique` (`company_id`,`client_code`),
  KEY `client_details_user_id_foreign` (`user_id`),
  KEY `client_details_category_id_foreign` (`category_id`),
  KEY `client_details_sub_category_id_foreign` (`sub_category_id`),
  KEY `client_details_added_by_foreign` (`added_by`),
  KEY `client_details_last_updated_by_foreign` (`last_updated_by`),
  KEY `client_details_country_id_foreign` (`country_id`),
  KEY `client_details_pricing_tier_id_foreign` (`pricing_tier_id`),
  KEY `client_details_default_warehouse_idx` (`default_warehouse_id`),
  CONSTRAINT `client_details_added_by_foreign` FOREIGN KEY (`added_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `client_details_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `client_categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `client_details_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `client_details_country_id_foreign` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `client_details_default_warehouse_fk` FOREIGN KEY (`default_warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `client_details_last_updated_by_foreign` FOREIGN KEY (`last_updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `client_details_pricing_tier_id_foreign` FOREIGN KEY (`pricing_tier_id`) REFERENCES `pricing_tiers` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `client_details_sub_category_id_foreign` FOREIGN KEY (`sub_category_id`) REFERENCES `client_sub_categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `client_details_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('client_details');
    }
};
