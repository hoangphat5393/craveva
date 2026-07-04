<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `objective_owners` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `objective_id` bigint unsigned NOT NULL,
  `owner_id` int unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `objective_owners_objective_id_foreign` (`objective_id`),
  KEY `objective_owners_owner_id_foreign` (`owner_id`),
  CONSTRAINT `objective_owners_objective_id_foreign` FOREIGN KEY (`objective_id`) REFERENCES `objectives` (`id`) ON DELETE CASCADE,
  CONSTRAINT `objective_owners_owner_id_foreign` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('objective_owners');
    }
};
