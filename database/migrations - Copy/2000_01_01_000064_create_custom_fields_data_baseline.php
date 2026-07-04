<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `custom_fields_data` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `custom_field_id` int unsigned NOT NULL,
  `model_id` int unsigned NOT NULL,
  `model` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `value` varchar(10000) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `custom_fields_data_custom_field_id_foreign` (`custom_field_id`),
  KEY `custom_fields_data_model_index` (`model`),
  KEY `cfd_model_modelid_fieldid_idx` (`model`(100),`model_id`,`custom_field_id`),
  CONSTRAINT `custom_fields_data_custom_field_id_foreign` FOREIGN KEY (`custom_field_id`) REFERENCES `custom_fields` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_fields_data');
    }
};
