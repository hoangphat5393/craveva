<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `asset_lending_history` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned DEFAULT NULL,
  `asset_id` bigint unsigned NOT NULL,
  `user_id` int unsigned NOT NULL,
  `lender_id` int unsigned NOT NULL,
  `returner_id` int unsigned DEFAULT NULL,
  `date_given` datetime NOT NULL,
  `return_date` datetime DEFAULT NULL,
  `date_of_return` datetime DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `asset_lending_history_asset_id_foreign` (`asset_id`),
  KEY `asset_lending_history_user_id_foreign` (`user_id`),
  KEY `asset_lending_history_lender_id_foreign` (`lender_id`),
  KEY `asset_lending_history_returner_id_foreign` (`returner_id`),
  KEY `asset_lending_history_company_id_foreign` (`company_id`),
  CONSTRAINT `asset_lending_history_asset_id_foreign` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `asset_lending_history_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `asset_lending_history_lender_id_foreign` FOREIGN KEY (`lender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `asset_lending_history_returner_id_foreign` FOREIGN KEY (`returner_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `asset_lending_history_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_lending_history');
    }
};
