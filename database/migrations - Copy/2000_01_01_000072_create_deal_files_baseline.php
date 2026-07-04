<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `deal_files` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `deal_id` bigint unsigned DEFAULT NULL,
  `user_id` int unsigned NOT NULL,
  `filename` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `hashname` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `size` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `google_url` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dropbox_link` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `added_by` int unsigned DEFAULT NULL,
  `last_updated_by` int unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lead_files_user_id_foreign` (`user_id`),
  KEY `lead_files_added_by_foreign` (`added_by`),
  KEY `lead_files_last_updated_by_foreign` (`last_updated_by`),
  KEY `deal_files_deal_id_foreign` (`deal_id`),
  CONSTRAINT `deal_files_deal_id_foreign` FOREIGN KEY (`deal_id`) REFERENCES `deals` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `lead_files_added_by_foreign` FOREIGN KEY (`added_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `lead_files_last_updated_by_foreign` FOREIGN KEY (`last_updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `lead_files_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('deal_files');
    }
};
