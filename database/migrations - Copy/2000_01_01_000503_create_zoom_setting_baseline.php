<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `zoom_setting` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned DEFAULT NULL,
  `api_key` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `secret_key` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `meeting_app` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'in_app',
  `secret_token` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `account_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meeting_client_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meeting_client_secret` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `zoom_setting_company_id_foreign` (`company_id`),
  CONSTRAINT `zoom_setting_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('zoom_setting');
    }
};
