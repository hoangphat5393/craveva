<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `features` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `language_setting_id` int unsigned DEFAULT NULL,
  `title` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci,
  `image` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `icon` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` enum('image','icon','task','bills','team','apps') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'image',
  `front_feature_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `features_language_setting_id_foreign` (`language_setting_id`),
  KEY `features_front_feature_id_foreign` (`front_feature_id`),
  CONSTRAINT `features_front_feature_id_foreign` FOREIGN KEY (`front_feature_id`) REFERENCES `front_features` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `features_language_setting_id_foreign` FOREIGN KEY (`language_setting_id`) REFERENCES `language_settings` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('features');
    }
};
