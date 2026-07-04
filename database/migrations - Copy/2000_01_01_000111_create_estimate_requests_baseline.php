<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `estimate_requests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `original_request_number` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estimate_request_number` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `client_id` int unsigned NOT NULL,
  `company_id` int unsigned DEFAULT NULL,
  `estimate_id` int unsigned DEFAULT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `estimated_budget` decimal(16,2) NOT NULL,
  `project_id` int unsigned DEFAULT NULL,
  `early_requirement` text COLLATE utf8mb4_unicode_ci,
  `currency_id` int unsigned DEFAULT NULL,
  `status` enum('pending','rejected','accepted','in process') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `reason` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `added_by` int unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `estimate_requests_client_id_foreign` (`client_id`),
  KEY `estimate_requests_company_id_foreign` (`company_id`),
  KEY `estimate_requests_estimate_id_foreign` (`estimate_id`),
  KEY `estimate_requests_project_id_foreign` (`project_id`),
  KEY `estimate_requests_currency_id_foreign` (`currency_id`),
  KEY `estimate_requests_added_by_foreign` (`added_by`),
  CONSTRAINT `estimate_requests_added_by_foreign` FOREIGN KEY (`added_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `estimate_requests_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `estimate_requests_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `estimate_requests_currency_id_foreign` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `estimate_requests_estimate_id_foreign` FOREIGN KEY (`estimate_id`) REFERENCES `estimates` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `estimate_requests_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('estimate_requests');
    }
};
