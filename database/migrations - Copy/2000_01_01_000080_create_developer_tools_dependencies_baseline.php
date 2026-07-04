<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `developer_tools_dependencies` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `file_id` bigint unsigned NOT NULL,
  `depends_on_file_id` bigint unsigned NOT NULL,
  `relation_type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `developer_tools_dependencies_file_id_foreign` (`file_id`),
  KEY `developer_tools_dependencies_depends_on_file_id_foreign` (`depends_on_file_id`),
  CONSTRAINT `developer_tools_dependencies_depends_on_file_id_foreign` FOREIGN KEY (`depends_on_file_id`) REFERENCES `developer_tools_files` (`id`) ON DELETE CASCADE,
  CONSTRAINT `developer_tools_dependencies_file_id_foreign` FOREIGN KEY (`file_id`) REFERENCES `developer_tools_files` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('developer_tools_dependencies');
    }
};
