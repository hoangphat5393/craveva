<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `db_user_mapping` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `db_username` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `company_id` int unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `db_user_mapping_db_username_unique` (`db_username`),
  KEY `db_user_mapping_company_id_foreign` (`company_id`),
  CONSTRAINT `db_user_mapping_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('db_user_mapping');
    }
};
