<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `menu_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `main_menu` longtext COLLATE utf8mb4_unicode_ci,
  `default_main_menu` longtext COLLATE utf8mb4_unicode_ci,
  `setting_menu` longtext COLLATE utf8mb4_unicode_ci,
  `default_setting_menu` longtext COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_settings');
    }
};
