<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `database_backup_cron_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'inactive',
  `hour_of_day` time DEFAULT NULL,
  `backup_after_days` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `delete_backup_after_days` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('database_backup_cron_settings');
    }
};
