<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `proposals` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `proposal_number` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `original_proposal_number` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `company_id` int unsigned DEFAULT NULL,
  `deal_id` bigint unsigned DEFAULT NULL,
  `valid_till` date NOT NULL,
  `sub_total` decimal(30,2) NOT NULL,
  `total` decimal(30,2) NOT NULL,
  `currency_id` int unsigned DEFAULT NULL,
  `discount_type` enum('percent','fixed') COLLATE utf8mb4_unicode_ci NOT NULL,
  `discount` decimal(30,2) NOT NULL,
  `invoice_convert` tinyint(1) NOT NULL DEFAULT '0',
  `status` enum('declined','accepted','waiting') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'waiting',
  `note` mediumtext COLLATE utf8mb4_unicode_ci,
  `description` longtext COLLATE utf8mb4_unicode_ci,
  `client_comment` text COLLATE utf8mb4_unicode_ci,
  `signature_approval` tinyint(1) NOT NULL DEFAULT '1',
  `added_by` int unsigned DEFAULT NULL,
  `last_updated_by` int unsigned DEFAULT NULL,
  `hash` text COLLATE utf8mb4_unicode_ci,
  `calculate_tax` enum('after_discount','before_discount') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'after_discount',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `last_viewed` timestamp NULL DEFAULT NULL,
  `ip_address` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `send_status` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `proposals_company_id_foreign` (`company_id`),
  KEY `proposals_currency_id_foreign` (`currency_id`),
  KEY `proposals_added_by_foreign` (`added_by`),
  KEY `proposals_last_updated_by_foreign` (`last_updated_by`),
  KEY `proposals_deal_id_foreign` (`deal_id`),
  CONSTRAINT `proposals_added_by_foreign` FOREIGN KEY (`added_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `proposals_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `proposals_currency_id_foreign` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `proposals_deal_id_foreign` FOREIGN KEY (`deal_id`) REFERENCES `deals` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `proposals_last_updated_by_foreign` FOREIGN KEY (`last_updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('proposals');
    }
};
