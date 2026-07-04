<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `discussion_files` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned DEFAULT NULL,
  `user_id` int unsigned NOT NULL,
  `discussion_id` int unsigned DEFAULT NULL,
  `discussion_reply_id` int unsigned DEFAULT NULL,
  `filename` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `google_url` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hashname` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `size` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dropbox_link` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `external_link_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `discussion_files_company_id_foreign` (`company_id`),
  KEY `discussion_files_user_id_foreign` (`user_id`),
  KEY `discussion_files_discussion_id_foreign` (`discussion_id`),
  KEY `discussion_files_discussion_reply_id_foreign` (`discussion_reply_id`),
  CONSTRAINT `discussion_files_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `discussion_files_discussion_id_foreign` FOREIGN KEY (`discussion_id`) REFERENCES `discussions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `discussion_files_discussion_reply_id_foreign` FOREIGN KEY (`discussion_reply_id`) REFERENCES `discussion_replies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `discussion_files_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('discussion_files');
    }
};
