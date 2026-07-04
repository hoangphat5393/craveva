<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `track_devices` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `device_uuid` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `device_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `device_hijacked_at` timestamp NULL DEFAULT NULL,
  `data` text COLLATE utf8mb4_unicode_ci,
  `is_rogue_device` tinyint(1) NOT NULL DEFAULT '0',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `track_devices_device_uuid_unique` (`device_uuid`),
  KEY `track_devices_device_type_index` (`device_type`),
  KEY `track_devices_ip_index` (`ip`),
  KEY `track_devices_is_rogue_device_index` (`is_rogue_device`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('track_devices');
    }
};
