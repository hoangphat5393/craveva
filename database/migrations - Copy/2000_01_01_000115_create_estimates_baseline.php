<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `estimates` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned DEFAULT NULL,
  `client_id` int unsigned NOT NULL,
  `estimate_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `original_estimate_number` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `valid_till` date NOT NULL,
  `recipe_moq` int unsigned DEFAULT NULL,
  `recipe_packaging` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `recipe_oem_sku` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sub_total` decimal(30,2) NOT NULL,
  `discount` decimal(30,2) NOT NULL DEFAULT '0.00',
  `discount_type` enum('percent','fixed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'percent',
  `total` decimal(30,2) NOT NULL,
  `currency_id` int unsigned DEFAULT NULL,
  `status` enum('declined','accepted','waiting','sent','draft','canceled','revision_required') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'waiting',
  `president_review_status` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `president_reviewed_by` int unsigned DEFAULT NULL,
  `president_reviewed_at` timestamp NULL DEFAULT NULL,
  `president_review_note` text COLLATE utf8mb4_unicode_ci,
  `vp_pricing_review_status` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vp_pricing_reviewed_by` int unsigned DEFAULT NULL,
  `vp_pricing_reviewed_at` timestamp NULL DEFAULT NULL,
  `vp_pricing_review_note` text COLLATE utf8mb4_unicode_ci,
  `note` mediumtext COLLATE utf8mb4_unicode_ci,
  `description` longtext COLLATE utf8mb4_unicode_ci,
  `send_status` tinyint(1) NOT NULL DEFAULT '1',
  `added_by` int unsigned DEFAULT NULL,
  `last_updated_by` int unsigned DEFAULT NULL,
  `hash` text COLLATE utf8mb4_unicode_ci,
  `calculate_tax` enum('after_discount','before_discount') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'after_discount',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `last_viewed` timestamp NULL DEFAULT NULL,
  `ip_address` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estimate_request_id` bigint unsigned DEFAULT NULL,
  `project_id` int unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `estimates_estimate_number_company_id_unique` (`estimate_number`,`company_id`),
  KEY `estimates_company_id_foreign` (`company_id`),
  KEY `estimates_client_id_foreign` (`client_id`),
  KEY `estimates_currency_id_foreign` (`currency_id`),
  KEY `estimates_added_by_foreign` (`added_by`),
  KEY `estimates_last_updated_by_foreign` (`last_updated_by`),
  KEY `estimates_estimate_request_id_foreign` (`estimate_request_id`),
  KEY `estimates_project_id_foreign` (`project_id`),
  CONSTRAINT `estimates_added_by_foreign` FOREIGN KEY (`added_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `estimates_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `estimates_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `estimates_currency_id_foreign` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `estimates_estimate_request_id_foreign` FOREIGN KEY (`estimate_request_id`) REFERENCES `estimate_requests` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `estimates_last_updated_by_foreign` FOREIGN KEY (`last_updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `estimates_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('estimates');
    }
};
