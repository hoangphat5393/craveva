<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `purchase_vendor_notes` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned DEFAULT NULL,
  `purchase_vendor_id` int unsigned NOT NULL,
  `note_title` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `note_type` tinyint(1) NOT NULL DEFAULT '0',
  `note_details` text COLLATE utf8mb4_unicode_ci,
  `ask_password` tinyint(1) NOT NULL DEFAULT '0',
  `member_id` int unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `purchase_vendor_notes_purchase_vendor_id_foreign` (`purchase_vendor_id`),
  KEY `purchase_vendor_notes_member_id_foreign` (`member_id`),
  CONSTRAINT `purchase_vendor_notes_member_id_foreign` FOREIGN KEY (`member_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `purchase_vendor_notes_purchase_vendor_id_foreign` FOREIGN KEY (`purchase_vendor_id`) REFERENCES `purchase_vendors` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_vendor_notes');
    }
};
