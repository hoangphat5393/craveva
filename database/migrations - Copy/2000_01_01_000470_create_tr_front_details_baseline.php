<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `tr_front_details` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `language_setting_id` int unsigned DEFAULT NULL,
  `header_title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `header_description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `feature_title` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `feature_description` text COLLATE utf8mb4_unicode_ci,
  `price_title` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price_description` text COLLATE utf8mb4_unicode_ci,
  `task_management_title` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `task_management_detail` text COLLATE utf8mb4_unicode_ci,
  `manage_bills_title` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `manage_bills_detail` text COLLATE utf8mb4_unicode_ci,
  `teamates_title` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `teamates_detail` text COLLATE utf8mb4_unicode_ci,
  `favourite_apps_title` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `favourite_apps_detail` text COLLATE utf8mb4_unicode_ci,
  `cta_title` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cta_detail` text COLLATE utf8mb4_unicode_ci,
  `client_title` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `client_detail` text COLLATE utf8mb4_unicode_ci,
  `testimonial_title` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `testimonial_detail` text COLLATE utf8mb4_unicode_ci,
  `faq_title` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `faq_detail` text COLLATE utf8mb4_unicode_ci,
  `footer_copyright_text` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tr_front_details_language_setting_id_foreign` (`language_setting_id`),
  CONSTRAINT `tr_front_details_language_setting_id_foreign` FOREIGN KEY (`language_setting_id`) REFERENCES `language_settings` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('tr_front_details');
    }
};
