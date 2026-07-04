<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `theme_settings` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned DEFAULT NULL,
  `panel` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `header_color` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sidebar_color` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sidebar_text_color` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `link_color` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#ffffff',
  `user_css` longtext COLLATE utf8mb4_unicode_ci,
  `sidebar_theme` enum('dark','light') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'dark',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `enable_rounded_theme` tinyint(1) NOT NULL DEFAULT '0',
  `login_background` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `restrict_admin_theme_change` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `theme_settings_company_id_foreign` (`company_id`),
  CONSTRAINT `theme_settings_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('theme_settings');
    }
};
