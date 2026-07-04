<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `employee_leave_quotas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL,
  `leave_type_id` int unsigned NOT NULL,
  `no_of_leaves` double NOT NULL,
  `leaves_used` double NOT NULL DEFAULT '0',
  `leaves_remaining` double NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `carry_forward_status` text COLLATE utf8mb4_unicode_ci,
  `leave_type_impact` tinyint(1) NOT NULL DEFAULT '0',
  `overutilised_leaves` double NOT NULL DEFAULT '0',
  `unused_leaves` double NOT NULL DEFAULT '0',
  `carry_forward_leaves` double NOT NULL DEFAULT '0',
  `carry_forward_applied` double NOT NULL DEFAULT '0',
  `leaves_to_reimburse` int NOT NULL DEFAULT '0',
  `leaves_actually_reimbursed` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `employee_leave_quotas_user_id_foreign` (`user_id`),
  KEY `employee_leave_quotas_leave_type_id_foreign` (`leave_type_id`),
  CONSTRAINT `employee_leave_quotas_leave_type_id_foreign` FOREIGN KEY (`leave_type_id`) REFERENCES `leave_types` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `employee_leave_quotas_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_leave_quotas');
    }
};
