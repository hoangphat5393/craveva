# B2B Pricing System - Comprehensive Proposal

## Executive Summary

This proposal outlines a comprehensive B2B pricing system upgrade for Craveva that enables:

- **Company-specific pricing tiers** with customizable discounts
- **Volume-based pricing** with automatic tier selection
- **Product/service-specific pricing** rules
- **Corporate pricing** where companies can set pricing for their customers
- **Deal proposal integration** with dynamic pricing
- **Multi-level pricing** (platform → company → customer)

---

## 1. System Architecture Overview

### 1.1 Core Components

```
┌─────────────────────────────────────────────────────────────┐
│                    B2B Pricing System                        │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐      │
│  │ Pricing Tiers│  │ Volume Rules │  │ Product Rules│      │
│  └──────────────┘  └──────────────┘  └──────────────┘      │
│         │                 │                 │               │
│         └─────────────────┼─────────────────┘               │
│                           │                                 │
│                  ┌────────▼────────┐                         │
│                  │ Pricing Engine │                         │
│                  └────────┬───────┘                         │
│                           │                                 │
│         ┌─────────────────┼─────────────────┐               │
│         │                 │                 │               │
│  ┌──────▼──────┐  ┌───────▼──────┐  ┌──────▼──────┐       │
│  │   Deals     │  │   Orders      │  │  Invoices   │       │
│  └─────────────┘  └───────────────┘  └─────────────┘       │
│                                                              │
└─────────────────────────────────────────────────────────────┘
```

### 1.2 Database Schema Design

#### 1.2.1 Pricing Tiers Table

```sql
pricing_tiers
├── id
├── company_id (nullable - platform-wide or company-specific)
├── name (e.g., "Enterprise", "Premium", "Standard")
├── description
├── discount_type (percentage, fixed_amount, override_price)
├── discount_value (decimal)
├── minimum_order_value (decimal, nullable)
├── minimum_quantity (integer, nullable)
├── is_active (boolean)
├── priority (integer - for rule precedence)
├── valid_from (date, nullable)
├── valid_to (date, nullable)
├── applies_to (enum: all, products, services, specific)
└── timestamps
```

#### 1.2.2 Pricing Tier Products/Services Table

```sql
pricing_tier_items
├── id
├── pricing_tier_id
├── product_id (nullable - if applies_to = 'specific')
├── service_id (nullable - if service is separate entity)
├── item_type (enum: product, service, category)
├── category_id (nullable - for category-based pricing)
└── timestamps
```

#### 1.2.3 Volume Discount Rules Table

```sql
volume_discount_rules
├── id
├── company_id (nullable)
├── pricing_tier_id (nullable - can be standalone)
├── name
├── discount_type (percentage, fixed_amount, tiered)
├── minimum_quantity (integer)
├── maximum_quantity (integer, nullable)
├── discount_value (decimal)
├── applies_to_product_id (nullable)
├── applies_to_category_id (nullable)
├── applies_to_type (enum: all, products, services, specific)
├── is_active (boolean)
└── timestamps
```

#### 1.2.4 Company Customer Pricing Table

```sql
company_customer_pricing
├── id
├── company_id (seller company)
├── customer_company_id (buyer company)
├── pricing_tier_id (nullable - can have custom pricing)
├── custom_discount_type (nullable)
├── custom_discount_value (nullable)
├── is_active (boolean)
├── valid_from (date, nullable)
├── valid_to (date, nullable)
└── timestamps
```

#### 1.2.5 Company Customer Product Pricing Table

```sql
company_customer_product_pricing
├── id
├── company_customer_pricing_id
├── product_id
├── custom_price (decimal, nullable)
├── custom_discount_type (nullable)
├── custom_discount_value (nullable)
└── timestamps
```

#### 1.2.6 Deal Proposal Pricing Table

```sql
deal_proposal_pricing
├── id
├── proposal_id
├── pricing_tier_id (nullable)
├── applied_discount_type
├── applied_discount_value
├── volume_discount_applied (boolean)
├── custom_pricing_applied (boolean)
└── timestamps
```

---

## 2. Feature Breakdown

### 2.1 Pricing Tiers Management

**Purpose**: Create and manage pricing tiers that can be applied to companies or customers.

**Features**:

- Create pricing tiers with name, description, and discount rules
- Set discount type: percentage, fixed amount, or override price
- Define minimum order value or quantity thresholds
- Set validity periods (valid_from, valid_to)
- Apply to all products/services, specific items, or categories
- Priority system for rule precedence
- Platform-wide or company-specific tiers

**Use Cases**:

- Platform admin creates "Enterprise Tier" with 20% discount
- Company creates "VIP Customer Tier" with 15% discount for specific customers
- Seasonal pricing tier valid for Q4 only

### 2.2 Product/Service Selection

**Purpose**: Select which products or services a pricing tier applies to.

**Features**:

- Apply tier to all items
- Apply to specific products only
- Apply to specific services only
- Apply to product categories/subcategories
- Apply to service categories
- Exclude specific items from tier

**Use Cases**:

- "Premium Tier" applies to all products except clearance items
- "Service Tier" applies only to services
- "Category Discount" applies to "Electronics" category only

### 2.3 Volume Discount System

**Purpose**: Automatically apply discounts based on quantity or order value.

**Features**:

- Quantity-based volume discounts (e.g., 10+ units = 5% off)
- Value-based volume discounts (e.g., $1000+ order = 10% off)
- Tiered volume discounts (e.g., 10-50 units = 5%, 50-100 = 10%, 100+ = 15%)
- Product-specific volume rules
- Category-based volume rules
- Automatic calculation during order/proposal creation

**Use Cases**:

- Buy 10+ units, get 5% discount
- Order value $5000+, get 15% discount
- Electronics category: 20+ units = 10% discount

### 2.4 Corporate Pricing (Company-to-Company)

**Purpose**: Allow companies to set custom pricing for their customer companies.

**Features**:

- Assign pricing tier to customer company
- Set custom discount for specific customer company
- Set custom product prices for customer company
- Set validity periods for corporate pricing
- Override platform pricing tiers
- View all customer companies and their pricing

**Use Cases**:

- Company A sets 25% discount for Company B (their major client)
- Company A sets custom price of $50 for Product X for Company C
- Company A creates "Partner Pricing" tier for all partner companies

### 2.5 Deal Proposal Integration

**Purpose**: Automatically apply appropriate pricing when creating proposals from deals.

**Features**:

- Auto-detect customer company and apply corporate pricing
- Auto-apply volume discounts based on proposal items
- Show pricing breakdown in proposals
- Allow manual override of auto-applied pricing
- Track which pricing rules were applied
- Display pricing tier name in proposal

**Use Cases**:

- Create proposal from deal → system auto-applies "Enterprise Tier" pricing
- Add products to proposal → volume discount automatically calculated
- Customer views proposal → sees "Corporate Pricing Applied" badge

### 2.6 Company Login Special Pricing

**Purpose**: Show different pricing when company users log in vs. public pricing.

**Features**:

- Detect logged-in company user
- Apply company-specific pricing tier
- Show "Member Pricing" vs "Public Pricing"
- Hide public pricing for logged-in users
- Apply corporate pricing if customer company is set

**Use Cases**:

- Logged-in user from Company B sees 20% discount on all products
- Public user sees regular pricing
- Company B user sees custom pricing set by Company A

---

## 3. Pricing Calculation Logic

### 3.1 Pricing Resolution Order

```
1. Check for Company Customer Product Pricing (most specific)   <-- Client Price (Giá khách hàng cụ thể)
   ↓ (if not found)
2. Check for Company Customer Pricing (corporate pricing)      <-- B2B Contract Price (Giá hợp đồng B2B)
   ↓ (if not found)
3. Check for Volume Discount Rules                             <-- Volume Price (Giá sỉ/số lượng)
   ↓ (if not found)
4. Check for Pricing Tier (company-specific)                   <-- Company Tier Price (Giá hạng thành viên công ty)
   ↓ (if not found)
5. Check for Pricing Tier (platform-wide)                      <-- Platform Tier Price (Giá hạng thành viên sàn)
   ↓ (if not found)
6. Use Base Product Price                                      <-- Market Price (Giá thị trường/niêm yết)
```

### 3.2 Calculation Example

**Scenario**: Company A (seller) selling to Company B (buyer), Product X, Quantity: 25

1. **Base Price**: $100
2. **Company Customer Product Pricing**: None
3. **Company Customer Pricing**: 15% discount for Company B
4. **Volume Discount**: 10+ units = 5% additional discount
5. **Final Calculation**:
    - Apply corporate discount: $100 × 0.85 = $85
    - Apply volume discount: $85 × 0.95 = $80.75
    - Total for 25 units: $80.75 × 25 = $2,018.75

---

## 4. User Interface Requirements

### 4.1 Admin Panel Features

#### 4.1.1 Pricing Tiers Management

- List all pricing tiers (platform + company)
- Create/Edit/Delete pricing tiers
- Assign products/services to tiers
- Set validity periods
- View tier usage statistics

#### 4.1.2 Volume Discount Management

- List all volume discount rules
- Create tiered volume discounts
- Set quantity/value thresholds
- Apply to products, services, or categories

#### 4.1.3 Corporate Pricing Management

- List all company-customer relationships
- Assign pricing tiers to customer companies
- Set custom product prices per customer
- View pricing history

### 4.2 Company Panel Features

#### 4.2.1 My Pricing Tiers

- View assigned pricing tiers
- View applicable discounts
- See which products/services are included

#### 4.2.2 Customer Pricing Management

- Manage pricing for customer companies
- Set custom pricing for customers
- View customer pricing history

#### 4.2.3 Product Catalog with Pricing

- View products with company-specific pricing
- See volume discount indicators
- Compare public vs. member pricing

### 4.3 Deal/Proposal Features

#### 4.3.1 Proposal Creation

- Auto-apply pricing when adding products
- Show pricing breakdown
- Display applied discounts
- Allow manual price override
- Show savings amount

#### 4.3.2 Deal Management

- View applicable pricing tiers for deal
- Preview proposal pricing
- Track pricing changes

---

## 5. API & Integration Points

### 5.1 Pricing Service Class

```php
PricingService::calculatePrice($productId, $companyId, $customerCompanyId, $quantity)
PricingService::getApplicableTiers($companyId, $customerCompanyId)
PricingService::applyVolumeDiscount($items, $companyId)
PricingService::getCorporatePricing($sellerCompanyId, $buyerCompanyId, $productId)
```

### 5.2 Integration Points

- **Order Creation**: Auto-apply pricing when creating orders
- **Proposal Creation**: Auto-apply pricing when creating proposals
- **Cart Calculation**: Real-time pricing in shopping cart
- **Product Display**: Show pricing based on logged-in company
- **Invoice Generation**: Use pricing from order/proposal

---

## 6. Implementation Phases

### Phase 1: Core Pricing Tiers (Weeks 1-2)

- Create pricing_tiers table
- Create pricing_tier_items table
- Build PricingTier model and relationships
- Create admin UI for tier management
- Basic pricing calculation service

### Phase 2: Product/Service Selection (Week 3)

- Implement product/service selection for tiers
- Category-based pricing
- Exclusion rules
- Update pricing calculation logic

### Phase 3: Volume Discounts (Week 4)

- Create volume_discount_rules table
- Build VolumeDiscount model
- Implement tiered volume discounts
- Integrate with pricing calculation

### Phase 4: Corporate Pricing (Weeks 5-6)

- Create company_customer_pricing tables
- Build corporate pricing management UI
- Implement customer company pricing logic
- Update pricing resolution order

### Phase 5: Deal Proposal Integration (Week 7)

- Integrate pricing with Proposal model
- Auto-apply pricing in proposal creation
- Display pricing breakdown in proposals
- Track applied pricing rules

### Phase 6: Company Login Pricing (Week 8)

- Detect logged-in company users
- Show company-specific pricing
- Hide public pricing for members
- Update product display logic

### Phase 7: Testing & Refinement (Weeks 9-10)

- Comprehensive testing
- Performance optimization
- UI/UX improvements
- Documentation

---

## 7. Technical Considerations

### 7.1 Integration Boundaries

- Use existing Catalog v2 endpoints for all marketplace data and pricing reads:
    - `GET /api/products/{id}/catalog` (`routes/api.php:60-128`).
    - `POST /api/products/{id}/bundles/{bundleId}/price` (`routes/api.php:228-383`).
- Protect admin writes and maintenance operations via `auth:sanctum` + `api.auth`:
    - Variant generation `POST /api/products/{id}/variants/generate` (`routes/api.php:227`) with role checks (`routes/api.php:152-155`).
- Company panel pages follow `web` + `auth` under `account/*` with menu inclusion:
    - Sidebar include `resources/views/sections/menu.blade.php:237-239` using `craveva_plugins()` (`app/Helper/start.php:382-395`).
    - Visibility gated by `user_modules()` (`app/Helper/start.php:328-379`) and `module_enabled()` (`app/Helper/start.php:609-627`).

### 7.2 Pricing Metadata Propagation

- Documents (Estimates, Orders, Invoices) carry a single `pricing_metadata` JSON per line item containing:
    - Bundle selection IDs, option item IDs, and final price breakdown.
    - Source computation reference (e.g., `POST /api/.../price` response hash).
- Benefits:
    - Avoids immediate schema changes; keeps auditability and UAT transparency.
    - Enables display of savings and composition in downstream PDFs and UIs.

### 7.1 Performance

- Cache pricing calculations
- Index database tables properly
- Use eager loading for relationships
- Consider Redis for pricing cache

### 7.2 Data Integrity

- Validate pricing rules don't conflict
- Ensure pricing tiers are active and valid
- Check validity periods
- Handle edge cases (expired tiers, inactive products)

### 7.3 Scalability

- Support thousands of pricing tiers
- Handle complex pricing rules efficiently
- Optimize pricing calculation queries
- Consider background jobs for bulk pricing updates

### 7.4 Security

- Restrict pricing tier management to authorized users
- Validate company ownership
- Audit pricing changes
- Protect against pricing manipulation

---

## 8. Migration Strategy

### 8.1 Data Migration

- Migrate existing discount logic to pricing tiers
- Preserve existing proposal/order pricing
- Create default pricing tiers for existing companies

### 8.2 Backward Compatibility

- Maintain existing discount fields in invoices/proposals
- Support both old and new pricing systems during transition
- Gradual migration of existing data

---

## 9. Success Metrics

- **Adoption Rate**: % of companies using pricing tiers
- **Pricing Accuracy**: % of orders with correct pricing
- **Performance**: Pricing calculation time < 100ms
- **User Satisfaction**: Positive feedback on pricing features
- **Revenue Impact**: Increase in B2B sales

---

## 10. Future Enhancements

- **Dynamic Pricing**: AI-based pricing optimization
- **A/B Testing**: Test different pricing strategies
- **Price History**: Track pricing changes over time
- **Bulk Pricing Updates**: Update pricing for multiple products
- **Pricing Analytics**: Dashboard with pricing insights
- **Multi-Currency Pricing**: Different pricing per currency
- **Time-Based Pricing**: Different pricing at different times
- **Geographic Pricing**: Different pricing by location

---

## 11. Risk Mitigation

### 11.1 Technical Risks

- **Complex Pricing Rules**: Use clear priority system
- **Performance Issues**: Implement caching and optimization
- **Data Conflicts**: Validation and conflict resolution

### 11.2 Business Risks

- **Pricing Errors**: Comprehensive testing and validation
- **User Confusion**: Clear UI and documentation
- **Revenue Impact**: Gradual rollout with monitoring

---

## 12. Conclusion

This B2B pricing system will transform Craveva into a powerful B2B marketplace platform, enabling:

- Flexible, customizable pricing for companies
- Automated volume discounts
- Corporate pricing relationships
- Seamless integration with existing deals and proposals
- Scalable architecture for future growth

The phased implementation approach ensures minimal disruption while delivering value incrementally.

---

## Appendix: Database Schema SQL

See separate migration files for complete table definitions.

---

## 13. Maintenance & Recent Fixes (2025-12-29)

### 13.1 TaskBoardController Stability Update

- Replaced usages of company() helper with $this->company to avoid protected property access issues and ensure consistent company scoping across queries and counters.
- Affected logic areas:
    - Waiting approval counts for employee/admin views
    - Duplicate status slug checks scoped per company
    - Default status reassignment on column deletion
- Verified controller remains within AccountBaseController conventions and respects existing middleware, permissions, and date format usage.

### 13.2 Deployment Script Adjustments

- Updated upload_deploy.ps1 to include newly modified files and ensure remote directories exist before upload.
- Added uploads for controller and Blade view paths used by task settings, recurring tasks, calendar, and reports.
- Kept structure non-destructive and additive; no server-side deletions or renames.

### 13.3 Safety & Scope

- No changes to global authentication, middleware, routes, or permission systems.
- No database migrations modified; application logic changes are purely additive and scoped.
- No vendor or framework files touched; changes limited to application controllers and deployment script.

---
