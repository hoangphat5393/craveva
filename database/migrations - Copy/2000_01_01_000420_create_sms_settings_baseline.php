<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `sms_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `account_sid` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `auth_token` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `from_number` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint(1) NOT NULL,
  `whatapp_from_number` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `whatsapp_status` tinyint(1) NOT NULL,
  `nexmo_api_key` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nexmo_api_secret` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nexmo_from_number` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nexmo_status` tinyint(1) NOT NULL,
  `msg91_auth_key` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `msg91_from` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `msg91_status` tinyint(1) NOT NULL,
  `purchase_code` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `supported_until` timestamp NULL DEFAULT NULL,
  `purchased_on` timestamp NULL DEFAULT NULL,
  `license_type` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notify_update` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `added_by` int unsigned DEFAULT NULL,
  `last_updated_by` int unsigned DEFAULT NULL,
  `telegram_status` tinyint(1) NOT NULL DEFAULT '0',
  `telegram_bot_token` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telegram_bot_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sms_settings_added_by_foreign` (`added_by`),
  KEY `sms_settings_last_updated_by_foreign` (`last_updated_by`),
  CONSTRAINT `sms_settings_added_by_foreign` FOREIGN KEY (`added_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `sms_settings_last_updated_by_foreign` FOREIGN KEY (`last_updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_settings');
    }
};
