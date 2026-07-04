<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `deal_proposal_pricing` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `proposal_id` int unsigned NOT NULL,
  `pricing_tier_id` bigint unsigned DEFAULT NULL,
  `applied_discount_type` enum('percentage','fixed_amount','override_price') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `applied_discount_value` decimal(15,4) DEFAULT NULL,
  `volume_discount_applied` tinyint(1) NOT NULL DEFAULT '0',
  `custom_pricing_applied` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `deal_proposal_pricing_proposal_id_index` (`proposal_id`),
  KEY `deal_proposal_pricing_pricing_tier_id_index` (`pricing_tier_id`),
  CONSTRAINT `deal_proposal_pricing_pricing_tier_id_foreign` FOREIGN KEY (`pricing_tier_id`) REFERENCES `pricing_tiers` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `deal_proposal_pricing_proposal_id_foreign` FOREIGN KEY (`proposal_id`) REFERENCES `proposals` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('deal_proposal_pricing');
    }
};
