<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `automate_shifts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL,
  `employee_shift_rotation_id` int unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `automate_shifts_employee_shift_rotation_id_foreign` (`employee_shift_rotation_id`),
  KEY `employee_shift_schedules_user_id_foreign` (`user_id`),
  CONSTRAINT `automate_shifts_employee_shift_rotation_id_foreign` FOREIGN KEY (`employee_shift_rotation_id`) REFERENCES `employee_shift_rotations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `automate_shifts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('automate_shifts');
    }
};
