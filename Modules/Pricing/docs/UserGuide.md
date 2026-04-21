# Contract Pricing User Guide

## Overview

Contract Pricing allows you to set specific prices or discounts for a client on a specific product for a defined period. This is useful for seasonal promotions, contract-based pricing, or temporary overrides.

## Managing Contract Pricing

### Adding a New Contract Price

1. Navigate to **Pricing > Contract Pricing**.
2. Click on **Add Contract Pricing**.
3. Select the **Client** and **Product**.
4. Enter the **Start Date** and **End Date**.
5. Set the **Custom Price** OR **Discount** (Type and Value).
6. Click **Save**.

### Editing a Contract Price

1. Navigate to **Pricing > Contract Pricing**.
2. Click the **Edit** icon (pencil) next to the record you want to modify.
3. Update the fields as necessary (Dates, Price, Discount).
4. Click **Update**.

### Date Validation Rules

- **Start Date** is required and must be today or in the future.
- **End Date** is optional. If provided, it must be after the Start Date.
- **No Overlaps**: You cannot create two pricing records for the same client and product that have overlapping dates. For example, if you have a price set for Jan 1 - Jan 31, you cannot create another one for Jan 15 - Feb 15. You must choose a different period (e.g., Feb 1 - Feb 28).

### Priority

When calculating the price for a product:

1. **Contract Pricing** (this feature) has the HIGHEST priority if the current date falls within the Start and End dates.
2. If no valid Contract Pricing is found, the system checks for other rules (Corporate Pricing, Tiers, etc.).

## Tier Pricing Clarification (Important)

This section clarifies common confusion when using **Pricing > Pricing Tier Rules**:

- **Tier-level Discount** (on Tier edit page): default discount for all products in the tier.
- **Product Rule** (Add Product Rule in tier details): product-specific override for one SKU in the same tier.

### How the engine applies tier discounts

For a product assigned to a tier:

1. If a **Product Rule** exists for that SKU, the engine uses Product Rule.
2. Otherwise, it uses the **Tier-level Discount**.
3. It does **not** add both discounts together.

After Stage 1 unit price is resolved, **Volume Discount** (if matched by quantity) is applied as final adjustment.
