<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `biolink_settings` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `biolink_id` int unsigned DEFAULT NULL,
  `theme` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Gradienta',
  `theme_color` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `custom_color_one` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `custom_color_two` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `favicon` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `font` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'arial',
  `block_space` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'medium',
  `block_hover_animation` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'none',
  `verified_badge` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'none',
  `display_branding` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `branding_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `branding_url` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `branding_text_color` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `protection_password` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_sensitive` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `page_title` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `meta_description` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `meta_keywords` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `custom_css` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `custom_js` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `biolink_settings_biolink_id_foreign` (`biolink_id`),
  CONSTRAINT `biolink_settings_biolink_id_foreign` FOREIGN KEY (`biolink_id`) REFERENCES `biolinks` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('biolink_settings');
    }
};
