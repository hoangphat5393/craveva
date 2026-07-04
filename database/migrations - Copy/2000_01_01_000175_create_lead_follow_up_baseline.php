<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `lead_follow_up` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `deal_id` bigint unsigned DEFAULT NULL,
  `remark` longtext COLLATE utf8mb4_unicode_ci,
  `next_follow_up_date` datetime DEFAULT NULL,
  `added_by` int unsigned DEFAULT NULL,
  `last_updated_by` int unsigned DEFAULT NULL,
  `event_id` text COLLATE utf8mb4_unicode_ci,
  `send_reminder` enum('yes','no') COLLATE utf8mb4_unicode_ci DEFAULT 'no',
  `remind_time` text COLLATE utf8mb4_unicode_ci,
  `remind_type` enum('minute','hour','day') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lead_follow_up_added_by_foreign` (`added_by`),
  KEY `lead_follow_up_last_updated_by_foreign` (`last_updated_by`),
  KEY `lead_follow_up_deal_id_foreign` (`deal_id`),
  CONSTRAINT `lead_follow_up_added_by_foreign` FOREIGN KEY (`added_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `lead_follow_up_deal_id_foreign` FOREIGN KEY (`deal_id`) REFERENCES `deals` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `lead_follow_up_last_updated_by_foreign` FOREIGN KEY (`last_updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_follow_up');
    }
};
