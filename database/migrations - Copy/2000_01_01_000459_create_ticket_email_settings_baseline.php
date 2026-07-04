<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `ticket_email_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned DEFAULT NULL,
  `mail_username` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mail_password` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mail_from_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mail_from_email` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `imap_host` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `imap_port` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `imap_encryption` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint(1) NOT NULL,
  `verified` tinyint(1) NOT NULL,
  `sync_interval` int NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ticket_email_settings_company_id_foreign` (`company_id`),
  CONSTRAINT `ticket_email_settings_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_email_settings');
    }
};
