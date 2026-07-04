<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `shift_rotation_sequences` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `employee_shift_rotation_id` int unsigned DEFAULT NULL,
  `employee_shift_id` bigint unsigned DEFAULT NULL,
  `sequence` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `shift_rotation_sequences_employee_shift_rotation_id_foreign` (`employee_shift_rotation_id`),
  KEY `shift_rotation_sequences_employee_shift_id_foreign` (`employee_shift_id`),
  CONSTRAINT `shift_rotation_sequences_employee_shift_id_foreign` FOREIGN KEY (`employee_shift_id`) REFERENCES `employee_shifts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `shift_rotation_sequences_employee_shift_rotation_id_foreign` FOREIGN KEY (`employee_shift_rotation_id`) REFERENCES `employee_shift_rotations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('shift_rotation_sequences');
    }
};
