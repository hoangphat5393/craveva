<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `mention_users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `task_comment_id` int unsigned DEFAULT NULL,
  `task_note_id` int unsigned DEFAULT NULL,
  `task_id` int unsigned DEFAULT NULL,
  `project_id` int unsigned DEFAULT NULL,
  `project_note_id` int unsigned DEFAULT NULL,
  `discussion_id` int unsigned DEFAULT NULL,
  `ticket_id` int unsigned DEFAULT NULL,
  `event_id` int unsigned DEFAULT NULL,
  `user_chat_id` int unsigned DEFAULT NULL,
  `user_id` int unsigned DEFAULT NULL,
  `discussion_reply_id` int unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mention_users_task_comment_id_foreign` (`task_comment_id`),
  KEY `mention_users_task_note_id_foreign` (`task_note_id`),
  KEY `mention_users_task_id_foreign` (`task_id`),
  KEY `mention_users_project_id_foreign` (`project_id`),
  KEY `mention_users_project_note_id_foreign` (`project_note_id`),
  KEY `mention_users_discussion_id_foreign` (`discussion_id`),
  KEY `mention_users_user_id_foreign` (`user_id`),
  KEY `mention_users_discussion_reply_id_foreign` (`discussion_reply_id`),
  KEY `mention_users_ticket_id_foreign` (`ticket_id`),
  KEY `mention_users_event_id_foreign` (`event_id`),
  KEY `mention_users_user_chat_id_foreign` (`user_chat_id`),
  CONSTRAINT `mention_users_discussion_id_foreign` FOREIGN KEY (`discussion_id`) REFERENCES `discussions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `mention_users_discussion_reply_id_foreign` FOREIGN KEY (`discussion_reply_id`) REFERENCES `discussion_replies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `mention_users_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `mention_users_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `mention_users_project_note_id_foreign` FOREIGN KEY (`project_note_id`) REFERENCES `project_notes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `mention_users_task_comment_id_foreign` FOREIGN KEY (`task_comment_id`) REFERENCES `task_comments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `mention_users_task_id_foreign` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `mention_users_task_note_id_foreign` FOREIGN KEY (`task_note_id`) REFERENCES `task_notes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `mention_users_ticket_id_foreign` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `mention_users_user_chat_id_foreign` FOREIGN KEY (`user_chat_id`) REFERENCES `users_chat` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `mention_users_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('mention_users');
    }
};
