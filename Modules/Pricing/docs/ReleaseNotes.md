# Release Notes - Pricing Module Update

## Features
- **Contract Pricing Date Range**: Added `start_date` and `end_date` support for Client Product Pricing.
- **Overlap Validation**: Implemented logic to prevent overlapping pricing periods for the same client and product.
- **UI Enhancements**: Added date pickers to Create and Edit forms for Contract Pricing.
- **API Updates**: Updated `store` and `update` endpoints to handle date fields and enforce validation.

## Database Changes
- Added `start_date` (DATETIME) and `end_date` (DATETIME) columns to `client_product_pricing` table.
- Existing records backfilled with `start_date = created_at` and `end_date = 2099-12-31`.

## Backward Compatibility
- Existing pricing records remain valid with an indefinite end date (2099).
- New records require a start date, while end date is optional.
