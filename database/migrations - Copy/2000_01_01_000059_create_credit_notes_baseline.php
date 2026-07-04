<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `credit_notes` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned DEFAULT NULL,
  `project_id` int unsigned DEFAULT NULL,
  `client_id` int unsigned DEFAULT NULL,
  `cn_number` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `original_credit_note_number` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `invoice_id` int unsigned DEFAULT NULL,
  `issue_date` date NOT NULL,
  `due_date` date NOT NULL,
  `discount` decimal(30,2) NOT NULL DEFAULT '0.00',
  `discount_type` enum('percent','fixed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'percent',
  `sub_total` decimal(30,2) NOT NULL,
  `total` decimal(30,2) NOT NULL,
  `adjustment_amount` decimal(30,2) DEFAULT NULL,
  `currency_id` int unsigned DEFAULT NULL,
  `status` enum('closed','open') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
  `recurring` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `billing_frequency` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `billing_interval` int DEFAULT NULL,
  `billing_cycle` int DEFAULT NULL,
  `file` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_original_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `added_by` int unsigned DEFAULT NULL,
  `last_updated_by` int unsigned DEFAULT NULL,
  `calculate_tax` enum('after_discount','before_discount') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'after_discount',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `credit_notes_company_id_foreign` (`company_id`),
  KEY `credit_notes_project_id_foreign` (`project_id`),
  KEY `credit_notes_client_id_foreign` (`client_id`),
  KEY `credit_notes_currency_id_foreign` (`currency_id`),
  KEY `credit_notes_added_by_foreign` (`added_by`),
  KEY `credit_notes_last_updated_by_foreign` (`last_updated_by`),
  CONSTRAINT `credit_notes_added_by_foreign` FOREIGN KEY (`added_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `credit_notes_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `credit_notes_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `credit_notes_currency_id_foreign` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `credit_notes_last_updated_by_foreign` FOREIGN KEY (`last_updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `credit_notes_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_notes');
    }
};
