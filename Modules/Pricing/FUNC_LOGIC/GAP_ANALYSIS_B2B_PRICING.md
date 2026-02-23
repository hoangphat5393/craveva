# B2B Pricing System - Gap Analysis Report

This document compares the current implementation of the Pricing Module against the requirements defined in `FUNC_DEVELOPMENT/B2B_PRICING_SYSTEM_PROPOSAL.md`.

## 1. Database Schema Analysis

### 1.1 `pricing_tiers` Table

- **Status**: ⚠️ Partial Match
- **Missing Fields**:
    - `discount_type` (percentage, fixed_amount) - _Currently logic relies on items, but global tier discount was proposed._
    - `discount_value`
    - `minimum_order_value`
    - `minimum_quantity`
    - `priority` (Critical for resolving multiple applicable tiers)
    - `valid_from` / `valid_to` (Critical for seasonal/limited time pricing)
    - `applies_to` (enum: all, products, services)

### 1.2 `volume_discount_rules` Table

- **Status**: ✅ Good Match
- **Notes**: Schema implementation aligns well with proposal.

### 1.3 `deal_proposal_pricing` Table

- **Status**: ✅ Good Match
- **Notes**: Schema implementation aligns well with proposal.

### 1.4 `company_customer_pricing` Table

- **Status**: ✅ Good Match
- **Notes**: Migration `2026_01_07_000007` exists.

---

## 2. Logic & Service Implementation Analysis

### 2.1 Pricing Resolution Order (`PricingService.php`)

**Requirement**:

1.  Company Customer Product Pricing
2.  Company Customer Pricing (Corporate)
3.  Volume Discount
4.  Pricing Tier (Company)
5.  Pricing Tier (Platform)
6.  Base Price

**Current Implementation**:

1.  Client Product Pricing (`client_product_pricing`)
2.  Pricing Tier Item (`pricing_tier_items`)
3.  Base Price

**Gaps**:

- ❌ **Corporate Pricing Logic Missing**: The `calculate()` method does not check `CompanyCustomerPricing` or `CompanyCustomerProductPricing`. There is a separate method `getCorporatePricing` but it is not integrated into the main flow.
- ❌ **Volume Discount Missing**: `applyVolumeDiscount` exists but is not part of the standard unit price calculation or final price determination flow in `calculate()`.
- ❌ **Priority Handling**: No logic to handle overlapping tiers based on `priority` column (since column is missing).

### 2.2 Proposal Integration

**Requirement**: "Auto-apply pricing when creating proposals"
**Status**: ❌ Missing
**Analysis**:

- There is no evidence of listeners or observers hooking into Proposal creation.
- The `PricingService` has no method specifically designed to process a Proposal's items and return the calculated totals with discounts applied.

---

## 3. Recommendations & Next Steps

1.  **Update `pricing_tiers` Table**:
    - Add missing columns: `priority`, `valid_from`, `valid_to`.
    - Decide if "Global Tier Discount" (e.g. Flat 10% off everything) is needed. If so, add `discount_type` and `discount_value`.

2.  **Refactor `PricingService::calculate`**:
    - Integrate `getCorporatePricing` logic into the main calculation flow.
    - Add `VolumeDiscountService` integration for quantity-based checks.

3.  **Implement Proposal Hook**:
    - Create an Observer for `Proposal` or a specific Service method `calculateProposal(Proposal $proposal)` that iterates items and applies the pricing engine.

4.  **UI/UX**:
    - Verify Admin UI exists for managing these new tables (Volume Rules, Corporate Pricing).
