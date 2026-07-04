<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `purpose_consent_users` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `client_id` int unsigned NOT NULL,
  `purpose_consent_id` int unsigned NOT NULL,
  `status` enum('agree','disagree') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'agree',
  `ip` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_by_id` int unsigned NOT NULL,
  `additional_description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `purpose_consent_users_client_id_foreign` (`client_id`),
  KEY `purpose_consent_users_purpose_consent_id_foreign` (`purpose_consent_id`),
  KEY `purpose_consent_users_updated_by_id_foreign` (`updated_by_id`),
  CONSTRAINT `purpose_consent_users_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `purpose_consent_users_purpose_consent_id_foreign` FOREIGN KEY (`purpose_consent_id`) REFERENCES `purpose_consent` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `purpose_consent_users_updated_by_id_foreign` FOREIGN KEY (`updated_by_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('purpose_consent_users');
    }
};
