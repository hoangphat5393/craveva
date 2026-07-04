<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `gdpr_settings` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `enable_gdpr` tinyint(1) NOT NULL DEFAULT '0',
  `show_customer_area` tinyint(1) NOT NULL DEFAULT '0',
  `show_customer_footer` tinyint(1) NOT NULL DEFAULT '0',
  `top_information_block` longtext COLLATE utf8mb4_unicode_ci,
  `enable_export` tinyint(1) NOT NULL DEFAULT '0',
  `data_removal` tinyint(1) NOT NULL DEFAULT '0',
  `lead_removal_public_form` tinyint(1) NOT NULL DEFAULT '0',
  `terms_customer_footer` tinyint(1) NOT NULL DEFAULT '0',
  `terms` longtext COLLATE utf8mb4_unicode_ci,
  `policy` longtext COLLATE utf8mb4_unicode_ci,
  `public_lead_edit` tinyint(1) NOT NULL DEFAULT '0',
  `consent_customer` tinyint(1) NOT NULL DEFAULT '0',
  `consent_leads` tinyint(1) NOT NULL DEFAULT '0',
  `consent_block` longtext COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('gdpr_settings');
    }
};
