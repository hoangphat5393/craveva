<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `server_domains` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned DEFAULT NULL,
  `domain_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `domain_provider` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `provider_url` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `domain_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'com',
  `registrar` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `registrar_url` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `registrar_username` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `registrar_password` text COLLATE utf8mb4_unicode_ci,
  `registrar_status` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `project_id` int unsigned DEFAULT NULL,
  `client_id` int unsigned DEFAULT NULL,
  `registration_date` date NOT NULL,
  `expiry_date` date NOT NULL,
  `renewal_date` date DEFAULT NULL,
  `username` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` text COLLATE utf8mb4_unicode_ci,
  `annual_cost` decimal(10,2) NOT NULL DEFAULT '0.00',
  `billing_cycle` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'annually',
  `status` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `dns_provider` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dns_status` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nameservers` text COLLATE utf8mb4_unicode_ci,
  `dns_records` text COLLATE utf8mb4_unicode_ci,
  `whois_protection` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'disabled',
  `auto_renewal` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'disabled',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `hosting_id` bigint unsigned DEFAULT NULL,
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
  KEY `server_domains_company_id_foreign` (`company_id`),
  KEY `server_domains_hosting_id_foreign` (`hosting_id`),
  KEY `server_domains_assigned_to_foreign` (`assigned_to`),
  KEY `server_domains_updated_by_foreign` (`updated_by`),
  KEY `server_domains_project_id_foreign` (`project_id`),
  KEY `server_domains_client_id_foreign` (`client_id`),
  KEY `server_domains_created_by_foreign` (`created_by`),
  CONSTRAINT `server_domains_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `server_domains_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `server_domains_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `server_domains_hosting_id_foreign` FOREIGN KEY (`hosting_id`) REFERENCES `server_hostings` (`id`) ON DELETE SET NULL,
  CONSTRAINT `server_domains_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE SET NULL,
  CONSTRAINT `server_domains_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('server_domains');
    }
};
