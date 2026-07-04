<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `cyber_securities` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `max_retries` int NOT NULL DEFAULT '3',
  `email` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lockout_time` int NOT NULL DEFAULT '2',
  `max_lockouts` int NOT NULL DEFAULT '3',
  `extended_lockout_time` int NOT NULL DEFAULT '1',
  `reset_retries` int NOT NULL DEFAULT '24',
  `alert_after_lockouts` int NOT NULL DEFAULT '2',
  `user_timeout` int NOT NULL DEFAULT '10',
  `ip_check` tinyint(1) NOT NULL DEFAULT '0',
  `ip` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `unique_session` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('cyber_securities');
    }
};
