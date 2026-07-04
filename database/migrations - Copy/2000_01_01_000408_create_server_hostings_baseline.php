<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `server_hostings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned DEFAULT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `domain_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `hosting_provider` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `provider_url` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `server_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'shared',
  `ip_address` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `server_location` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `disk_space` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bandwidth` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `database_limit` int DEFAULT NULL,
  `email_limit` int DEFAULT NULL,
  `username` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` text COLLATE utf8mb4_unicode_ci,
  `control_panel` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `control_panel_url` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cpanel_url` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `project` int unsigned DEFAULT NULL,
  `client` int unsigned DEFAULT NULL,
  `ftp_host` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ftp_username` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ftp_password` text COLLATE utf8mb4_unicode_ci,
  `database_host` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `database_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `database_username` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `database_password` text COLLATE utf8mb4_unicode_ci,
  `purchase_date` date NOT NULL,
  `renewal_date` date NOT NULL,
  `monthly_cost` decimal(10,2) NOT NULL DEFAULT '0.00',
  `annual_cost` decimal(10,2) NOT NULL DEFAULT '0.00',
  `billing_cycle` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'monthly',
  `status` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `ssl_certificate_info` text COLLATE utf8mb4_unicode_ci,
  `ssl_certificate` tinyint(1) NOT NULL DEFAULT '0',
  `ssl_expiry_date` date DEFAULT NULL,
  `ssl_type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expiry_notification` tinyint(1) NOT NULL DEFAULT '0',
  `notification_days_before` int DEFAULT NULL,
  `notification_time_unit` enum('days','weeks','months') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'days',
  `last_notification_sent` timestamp NULL DEFAULT NULL,
  `assigned_to` int unsigned DEFAULT NULL,
  `created_by` int unsigned DEFAULT NULL,
  `updated_by` int unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `server_hostings_company_id_foreign` (`company_id`),
  KEY `server_hostings_assigned_to_foreign` (`assigned_to`),
  KEY `server_hostings_updated_by_foreign` (`updated_by`),
  KEY `server_hostings_created_by_foreign` (`created_by`),
  CONSTRAINT `server_hostings_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `server_hostings_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `server_hostings_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `server_hostings_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('server_hostings');
    }
};
