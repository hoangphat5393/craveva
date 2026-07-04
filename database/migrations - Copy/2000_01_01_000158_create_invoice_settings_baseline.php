<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `invoice_settings` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned DEFAULT NULL,
  `invoice_prefix` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `invoice_number_separator` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#',
  `invoice_digit` int unsigned NOT NULL DEFAULT '3',
  `estimate_prefix` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'EST',
  `estimate_number_separator` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#',
  `estimate_digit` int unsigned NOT NULL DEFAULT '3',
  `credit_note_prefix` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'CN',
  `credit_note_number_separator` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#',
  `credit_note_digit` int unsigned NOT NULL DEFAULT '3',
  `contract_prefix` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'CONT',
  `contract_number_separator` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#',
  `estimate_request_prefix` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ESTRQ',
  `estimate_request_number_separator` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#',
  `estimate_request_digit` int NOT NULL DEFAULT '3',
  `contract_digit` int unsigned NOT NULL DEFAULT '3',
  `order_prefix` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ODR',
  `order_number_separator` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#',
  `order_digit` int unsigned NOT NULL DEFAULT '3',
  `proposal_prefix` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Proposal',
  `proposal_number_separator` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#',
  `proposal_digit` int NOT NULL DEFAULT '3',
  `template` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `due_after` int NOT NULL,
  `invoice_terms` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `order_terms` text COLLATE utf8mb4_unicode_ci,
  `other_info` text COLLATE utf8mb4_unicode_ci,
  `estimate_terms` text COLLATE utf8mb4_unicode_ci,
  `phase1_min_gross_margin_percent` decimal(8,2) DEFAULT NULL,
  `gst_number` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `show_gst` enum('yes','no') COLLATE utf8mb4_unicode_ci DEFAULT 'no',
  `logo` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hsn_sac_code_show` tinyint(1) NOT NULL DEFAULT '0',
  `locale` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT 'en',
  `send_reminder` int NOT NULL DEFAULT '0',
  `reminder` enum('after','every') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `send_reminder_after` int NOT NULL DEFAULT '0',
  `tax_calculation_msg` tinyint(1) NOT NULL DEFAULT '0',
  `show_status` tinyint(1) NOT NULL DEFAULT '1',
  `authorised_signatory` tinyint(1) NOT NULL DEFAULT '0',
  `authorised_signatory_signature` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `show_project` int NOT NULL DEFAULT '0',
  `show_client_name` enum('yes','no') COLLATE utf8mb4_unicode_ci DEFAULT 'no',
  `show_client_email` enum('yes','no') COLLATE utf8mb4_unicode_ci DEFAULT 'no',
  `show_client_phone` enum('yes','no') COLLATE utf8mb4_unicode_ci DEFAULT 'no',
  `show_client_company_address` enum('yes','no') COLLATE utf8mb4_unicode_ci DEFAULT 'no',
  `show_client_company_name` enum('yes','no') COLLATE utf8mb4_unicode_ci DEFAULT 'no',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `invoice_settings_company_id_foreign` (`company_id`),
  CONSTRAINT `invoice_settings_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_settings');
    }
};
