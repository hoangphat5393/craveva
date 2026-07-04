<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `social_auth_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `facebook_client_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `facebook_secret_id` text COLLATE utf8mb4_unicode_ci,
  `facebook_status` enum('enable','disable') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'disable',
  `google_client_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `google_secret_id` text COLLATE utf8mb4_unicode_ci,
  `google_status` enum('enable','disable') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'disable',
  `twitter_client_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `twitter_secret_id` text COLLATE utf8mb4_unicode_ci,
  `twitter_status` enum('enable','disable') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'disable',
  `linkedin_client_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `linkedin_secret_id` text COLLATE utf8mb4_unicode_ci,
  `linkedin_status` enum('enable','disable') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'disable',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('social_auth_settings');
    }
};
