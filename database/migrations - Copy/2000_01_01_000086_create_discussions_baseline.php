<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `discussions` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned DEFAULT NULL,
  `discussion_category_id` int unsigned DEFAULT '1',
  `project_id` int unsigned DEFAULT NULL,
  `title` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `color` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT '#232629',
  `user_id` int unsigned NOT NULL,
  `pinned` tinyint(1) NOT NULL DEFAULT '0',
  `closed` tinyint(1) NOT NULL DEFAULT '0',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `last_reply_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `best_answer_id` int unsigned DEFAULT NULL,
  `last_reply_by_id` int unsigned DEFAULT NULL,
  `added_by` int unsigned DEFAULT NULL,
  `last_updated_by` int unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `discussions_discussion_category_id_foreign` (`discussion_category_id`),
  KEY `discussions_project_id_foreign` (`project_id`),
  KEY `discussions_user_id_foreign` (`user_id`),
  KEY `discussions_best_answer_id_foreign` (`best_answer_id`),
  KEY `discussions_last_reply_by_id_foreign` (`last_reply_by_id`),
  KEY `discussions_added_by_foreign` (`added_by`),
  KEY `discussions_last_updated_by_foreign` (`last_updated_by`),
  KEY `discussions_company_id_foreign` (`company_id`),
  CONSTRAINT `discussions_added_by_foreign` FOREIGN KEY (`added_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `discussions_best_answer_id_foreign` FOREIGN KEY (`best_answer_id`) REFERENCES `discussion_replies` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `discussions_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `discussions_discussion_category_id_foreign` FOREIGN KEY (`discussion_category_id`) REFERENCES `discussion_categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `discussions_last_reply_by_id_foreign` FOREIGN KEY (`last_reply_by_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `discussions_last_updated_by_foreign` FOREIGN KEY (`last_updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `discussions_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `discussions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('discussions');
    }
};
