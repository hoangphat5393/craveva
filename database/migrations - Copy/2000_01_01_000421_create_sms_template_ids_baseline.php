<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `sms_template_ids` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `sms_setting_slug` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `msg91_flow_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `whatsapp_template_sid` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_template_ids');
    }
};
