<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `pusher_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `pusher_app_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pusher_app_key` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pusher_app_secret` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pusher_cluster` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `force_tls` tinyint(1) NOT NULL,
  `status` tinyint(1) NOT NULL,
  `taskboard` tinyint(1) NOT NULL DEFAULT '1',
  `messages` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('pusher_settings');
    }
};
