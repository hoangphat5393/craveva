<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `zoom_meetings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned DEFAULT NULL,
  `meeting_id` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` int unsigned NOT NULL,
  `meeting_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `label_color` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` mediumtext COLLATE utf8mb4_unicode_ci,
  `start_date_time` datetime NOT NULL,
  `end_date_time` datetime NOT NULL,
  `repeat` tinyint(1) NOT NULL DEFAULT '0',
  `repeat_every` int DEFAULT NULL,
  `repeat_cycles` int DEFAULT NULL,
  `repeat_type` enum('day','week','month','year') COLLATE utf8mb4_unicode_ci NOT NULL,
  `send_reminder` tinyint(1) NOT NULL DEFAULT '0',
  `remind_time` int DEFAULT NULL,
  `remind_type` enum('day','hour','minute') COLLATE utf8mb4_unicode_ci NOT NULL,
  `host_video` tinyint(1) NOT NULL DEFAULT '0',
  `participant_video` tinyint(1) NOT NULL DEFAULT '0',
  `start_link` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `join_link` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('waiting','live','canceled','finished') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'waiting',
  `project_id` int unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `password` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `source_meeting_id` bigint unsigned DEFAULT NULL,
  `occurrence_id` bigint DEFAULT NULL,
  `occurrence_order` int DEFAULT NULL,
  `category_id` bigint unsigned DEFAULT NULL,
  `added_by` int unsigned DEFAULT NULL,
  `last_updated_by` int unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `zoom_meetings_created_by_foreign` (`created_by`),
  KEY `zoom_meetings_project_id_foreign` (`project_id`),
  KEY `zoom_meetings_source_meeting_id_foreign` (`source_meeting_id`),
  KEY `zoom_meetings_category_id_foreign` (`category_id`),
  KEY `zoom_meetings_added_by_foreign` (`added_by`),
  KEY `zoom_meetings_last_updated_by_foreign` (`last_updated_by`),
  KEY `zoom_meetings_company_id_foreign` (`company_id`),
  CONSTRAINT `zoom_meetings_added_by_foreign` FOREIGN KEY (`added_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `zoom_meetings_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `zoom_categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `zoom_meetings_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `zoom_meetings_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `zoom_meetings_last_updated_by_foreign` FOREIGN KEY (`last_updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `zoom_meetings_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `zoom_meetings_source_meeting_id_foreign` FOREIGN KEY (`source_meeting_id`) REFERENCES `zoom_meetings` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('zoom_meetings');
    }
};
