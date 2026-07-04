<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `webhooks_requests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned DEFAULT NULL,
  `webhooks_setting_id` bigint unsigned DEFAULT NULL,
  `headers_key` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `headers_value` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `request_type` enum('headers','body') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'headers',
  `body_key` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `body_value` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `webhooks_requests_company_id_foreign` (`company_id`),
  KEY `webhooks_requests_webhooks_setting_id_foreign` (`webhooks_setting_id`),
  CONSTRAINT `webhooks_requests_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `webhooks_requests_webhooks_setting_id_foreign` FOREIGN KEY (`webhooks_setting_id`) REFERENCES `webhooks_settings` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('webhooks_requests');
    }
};
