# DB_STRUCTURE

- Generated at: 2026-05-04T05:35:06+00:00

## Tables (from module migrations)

### client_product_pricing

- Columns: client_id, company_id, custom_price, discount_type, discount_value, is_active, product_id
- Migrations: 2026_01_07_000003_create_client_product_pricing_table.php

### company_customer_pricing

- Columns: company_id, custom_discount_type, custom_discount_value, customer_company_id, is_active, pricing_tier_id, valid_from, valid_to
- Migrations: 2026_01_07_000007_create_company_customer_pricing_table.php

### company_customer_product_pricing

- Columns: company_customer_pricing_id, custom_discount_type, custom_discount_value, custom_price, product_id
- Migrations: 2026_01_07_000008_create_company_customer_product_pricing_table.php

### deal_proposal_pricing

- Columns: applied_discount_type, applied_discount_value, custom_pricing_applied, pricing_tier_id, proposal_id, volume_discount_applied
- Migrations: 2026_01_07_000009_create_deal_proposal_pricing_table.php

### pricing_tier_items

- Columns: discount_type, discount_value, is_active, pricing_tier_id, product_id
- Migrations: 2026_01_07_000002_create_pricing_tier_items_table.php

### pricing_tiers

- Columns: company_id, description, is_active, name
- Migrations: 2026_01_07_000001_create_pricing_tiers_table.php

### volume_discount_rules

- Columns: applies_to_category_id, applies_to_product_id, applies_to_type, company_id, discount_type, discount_value, is_active, maximum_quantity, minimum_quantity, name, pricing_tier_id
- Migrations: 2026_01_07_000006_create_volume_discount_rules_table.php

## Entities (table + casts)

- Modules/Pricing/Entities/ClientProductPricing.php (table=client_product_pricing)
- Modules/Pricing/Entities/CompanyCustomerPricing.php (table=company_customer_pricing)
- Modules/Pricing/Entities/CompanyCustomerProductPricing.php (table=company_customer_product_pricing)
- Modules/Pricing/Entities/DealProposalPricing.php (table=deal_proposal_pricing)
- Modules/Pricing/Entities/PricingTier.php (table=pricing_tiers)
- Modules/Pricing/Entities/PricingTierItem.php (table=pricing_tier_items)
- Modules/Pricing/Entities/VolumeDiscountRule.php (table=volume_discount_rules)
