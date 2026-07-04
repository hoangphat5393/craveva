<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `front_details` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `get_started_show` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'yes',
  `sign_in_show` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'yes',
  `address` text COLLATE utf8mb4_unicode_ci,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `social_links` text COLLATE utf8mb4_unicode_ci,
  `primary_color` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `custom_css` longtext COLLATE utf8mb4_unicode_ci,
  `custom_css_theme_two` longtext COLLATE utf8mb4_unicode_ci,
  `locale` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT 'en',
  `contact_html` longtext COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `homepage_background` enum('default','color','image','image_and_color') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'default',
  `background_color` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT '#CDDCDC',
  `background_image` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('front_details');
    }
};
