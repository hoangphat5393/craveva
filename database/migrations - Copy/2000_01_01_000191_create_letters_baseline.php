<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `letters` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned NOT NULL,
  `user_id` int unsigned DEFAULT NULL,
  `template_id` int unsigned NOT NULL,
  `creator_id` int unsigned DEFAULT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `top` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `right` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `left` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `bottom` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `letters_company_id_foreign` (`company_id`),
  KEY `letters_user_id_foreign` (`user_id`),
  KEY `letters_template_id_foreign` (`template_id`),
  KEY `letters_creator_id_foreign` (`creator_id`),
  CONSTRAINT `letters_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `letters_creator_id_foreign` FOREIGN KEY (`creator_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `letters_template_id_foreign` FOREIGN KEY (`template_id`) REFERENCES `letter_templates` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `letters_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('letters');
    }
};
