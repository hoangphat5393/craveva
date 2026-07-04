<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `ticket_activities` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `ticket_id` int unsigned NOT NULL,
  `user_id` int unsigned NOT NULL,
  `assigned_to` int unsigned DEFAULT NULL,
  `channel_id` int unsigned DEFAULT NULL,
  `group_id` int unsigned DEFAULT NULL,
  `type_id` int unsigned DEFAULT NULL,
  `status` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
  `priority` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'medium',
  `type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'create',
  `content` longtext COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ticket_activities_ticket_id_foreign` (`ticket_id`),
  KEY `ticket_activities_user_id_foreign` (`user_id`),
  KEY `ticket_activities_assigned_to_foreign` (`assigned_to`),
  KEY `ticket_activities_channel_id_foreign` (`channel_id`),
  KEY `ticket_activities_group_id_foreign` (`group_id`),
  KEY `ticket_activities_type_id_foreign` (`type_id`),
  CONSTRAINT `ticket_activities_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ticket_activities_channel_id_foreign` FOREIGN KEY (`channel_id`) REFERENCES `ticket_channels` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ticket_activities_group_id_foreign` FOREIGN KEY (`group_id`) REFERENCES `ticket_groups` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ticket_activities_ticket_id_foreign` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ticket_activities_type_id_foreign` FOREIGN KEY (`type_id`) REFERENCES `ticket_types` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ticket_activities_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_activities');
    }
};
