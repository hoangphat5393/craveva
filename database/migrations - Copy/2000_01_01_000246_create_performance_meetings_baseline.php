<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `performance_meetings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned DEFAULT NULL,
  `parent_id` bigint unsigned DEFAULT NULL,
  `objective_id` bigint unsigned DEFAULT NULL,
  `start_date_time` datetime NOT NULL,
  `end_date_time` datetime NOT NULL,
  `repeat` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `repeat_every` enum('day','week','month','year') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `repeat_cycles` int DEFAULT NULL,
  `repeat_type` enum('after','on') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `until_date` date DEFAULT NULL,
  `meeting_for` int unsigned NOT NULL,
  `meeting_by` int unsigned NOT NULL,
  `added_by` int unsigned NOT NULL,
  `status` enum('pending','completed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `completed_on` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `performance_meetings_company_id_foreign` (`company_id`),
  KEY `performance_meetings_parent_id_foreign` (`parent_id`),
  KEY `performance_meetings_objective_id_foreign` (`objective_id`),
  KEY `performance_meetings_meeting_for_foreign` (`meeting_for`),
  KEY `performance_meetings_meeting_by_foreign` (`meeting_by`),
  KEY `performance_meetings_added_by_foreign` (`added_by`),
  CONSTRAINT `performance_meetings_added_by_foreign` FOREIGN KEY (`added_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `performance_meetings_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `performance_meetings_meeting_by_foreign` FOREIGN KEY (`meeting_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `performance_meetings_meeting_for_foreign` FOREIGN KEY (`meeting_for`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `performance_meetings_objective_id_foreign` FOREIGN KEY (`objective_id`) REFERENCES `objectives` (`id`) ON DELETE CASCADE,
  CONSTRAINT `performance_meetings_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `performance_meetings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('performance_meetings');
    }
};
