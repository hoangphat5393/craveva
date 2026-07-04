<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `front_menu_buttons` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `home` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'home',
  `feature` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'feature',
  `price` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'price',
  `contact` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'contact',
  `get_start` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'get_start',
  `login` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'login',
  `contact_submit` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'contact_submit',
  `language_setting_id` int unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `front_menu_buttons_language_setting_id_foreign` (`language_setting_id`),
  CONSTRAINT `front_menu_buttons_language_setting_id_foreign` FOREIGN KEY (`language_setting_id`) REFERENCES `language_settings` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('front_menu_buttons');
    }
};
