<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `purchase_inventory_histories` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned DEFAULT NULL,
  `inventory_id` int unsigned DEFAULT NULL,
  `user_id` int unsigned DEFAULT NULL,
  `product_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `net_quantity` decimal(16,2) DEFAULT NULL,
  `quantity_adjustment` int DEFAULT '0',
  `changed_value` decimal(16,2) DEFAULT '0.00',
  `adjusted_value` decimal(16,2) DEFAULT '0.00',
  `purchase_inventory_files_id` int unsigned DEFAULT NULL,
  `label` text COLLATE utf8mb4_unicode_ci,
  `details` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `purchase_inventory_histories_company_id_foreign` (`company_id`),
  KEY `purchase_inventory_histories_inventory_id_foreign` (`inventory_id`),
  KEY `purchase_inventory_histories_user_id_foreign` (`user_id`),
  KEY `purchase_inventory_histories_purchase_inventory_files_id_foreign` (`purchase_inventory_files_id`),
  CONSTRAINT `purchase_inventory_histories_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `purchase_inventory_histories_inventory_id_foreign` FOREIGN KEY (`inventory_id`) REFERENCES `purchase_inventory_adjustment` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `purchase_inventory_histories_purchase_inventory_files_id_foreign` FOREIGN KEY (`purchase_inventory_files_id`) REFERENCES `purchase_inventory_files` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `purchase_inventory_histories_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_inventory_histories');
    }
};
