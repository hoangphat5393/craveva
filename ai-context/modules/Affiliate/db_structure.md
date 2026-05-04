# DB_STRUCTURE

- Generated at: 2026-05-04T05:35:06+00:00

## Tables (from module migrations)

### affiliate_global_settings

- Columns: license_type, notify_update, purchase_code, purchased_on, supported_until
- Migrations: 2024_05_07_045046_create_affiliate_global_settings_table.php

### affiliate_payouts

- Columns: affiliate_id, amount_requested, balance, memo, note, other_payment_method, paid_at, payment_method, status, transaction_id
- Migrations: 2024_05_09_090347_create_payouts_table.php

### affiliate_referrals

- Columns: affiliate_id, commissions, company_id, user_agent
- Migrations: 2024_05_08_080837_create_referrals_table.php

### affiliate_settings

- Columns: commission_cap, commission_enabled, commission_type, minimum_payout, payout_time, payout_type
- Migrations: 2024_05_07_063115_create_affiliate_settings_table.php

### affiliates

- Columns: balance, referral_code, status, user_id
- Migrations: 2024_05_08_070003_create_affiliates_table.php

## Entities (table + casts)

- Modules/Affiliate/Entities/Affiliate.php
- Modules/Affiliate/Entities/AffiliateGlobalSetting.php
- Modules/Affiliate/Entities/AffiliateSetting.php
- Modules/Affiliate/Entities/Payout.php (table=affiliate_payouts)
- Modules/Affiliate/Entities/Referral.php (table=affiliate_referrals)
- Modules/Affiliate/Entities/User.php
