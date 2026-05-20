Yes. In ERP, a Bill of Materials (BOM) is essentially the recipe or manufacturing formula for a finished product.

For your coffee example:

## Finished Product

This is what you sell to customer.

Example:

* Product Name: `Oldtown White Coffee Custom 3-in-1 150g`
* SKU: `OTWC-3IN1-150G`
* Unit: `Pack`

This product is usually created under:

* Operations → Products

---

# What BOM Actually Means

The BOM defines:

> “To produce 1 unit of finished product, what raw materials are required and how much?”

For your case:

| Finished Product                        | Qty Produced |
| --------------------------------------- | ------------ |
| Oldtown White Coffee Custom 3-in-1 150g | 1 Pack       |

BOM Components:

| Raw Material   | Qty |
| -------------- | --- |
| Sugar          | 50g |
| Creamer        | 30g |
| Arabica Coffee | 70g |

Total = 150g

So yes:

* Sugar
* Creamer
* Coffee Arabica

are your raw ingredients/components.

---

# Correct ERP Structure

This is the normal ERP manufacturing hierarchy:

## 1. Create Raw Materials

Location:

* Operations → Products

You create:

* Sugar
* Creamer
* Arabica Coffee

Product Type:

* Raw Material
* Inventory Item
* Stock Item

These products:

* can be stocked
* purchased
* consumed in production

---

## 2. Create Finished Product

Also in:

* Operations → Products

Create:

* Oldtown White Coffee Custom 3-in-1 150g

Product Type:

* Finished Goods
* Manufactured Item

---

## 3. Create BOM

Location:

* Production → Bill of Materials

The BOM page should show something like:

| Field         | Example                                 |
| ------------- | --------------------------------------- |
| Finished Good | Oldtown White Coffee Custom 3-in-1 150g |
| BOM Version   | V1                                      |
| Output Qty    | 1 Pack                                  |
| UOM           | Pack                                    |

Then component table:

| Component      | Qty | UOM |
| -------------- | --- | --- |
| Sugar          | 50  | g   |
| Creamer        | 30  | g   |
| Arabica Coffee | 70  | g   |

Optional fields:

* Scrap %
* Loss %
* Production notes
* Batch size
* Production time

---

# Why Your Dev Said “Add in Products”

Your dev is correct.

Because BOM itself should NOT create raw ingredients.

ERP logic is:

```text
Products Master
    ├── Raw Materials
    ├── Semi Finished Goods
    └── Finished Goods
```

BOM only references existing products.

So:

* Sugar must already exist as Product
* Creamer must already exist as Product
* Coffee Arabica must already exist as Product

Then BOM links them together.

---

# What Should Show in BOM Dropdown

When adding BOM:

“Finished Good” dropdown should only show:

* manufactured products
* finished goods

“Components” dropdown should show:

* raw materials
* inventory items

Your current screen is showing ALL products mixed together, which is not ideal UX.

Better ERP behavior would be:
## Finished Good Dropdown

Filter:

```text
product_type = finished_goods
```

## Components Dropdown

Filter:

```text
product_type = raw_material
```

---

# Recommended ERP Product Types

Your Products module should ideally support:

| Product Type   | Purpose          |
| -------------- | ---------------- |
| Raw Material   | ingredients      |
| Finished Goods | sellable product |
| Semi Finished  | intermediate mix |
| Packaging      | bags, boxes      |
| Service        | non-stock        |

---

# How Production Flow Works

## Sales Order

Customer orders:

* 3000 packs

---

## Production Order

ERP calculates required materials automatically:

| Material       | Formula    |
| -------------- | ---------- |
| Sugar          | 50g × 3000 |
| Creamer        | 30g × 3000 |
| Arabica Coffee | 70g × 3000 |

Result:

| Material | Total |
| -------- | ----- |
| Sugar    | 150kg |
| Creamer  | 90kg  |
| Coffee   | 210kg |

---

## Inventory Deduction

When production completed:

* raw materials decrease
* finished goods increase

---

## Delivery Order

Deliver finished goods to customer.

---

## Invoice

Generate customer invoice.

---

# Recommended UI Structure

## Products Module

Should contain:

### Raw Materials

* Sugar
* Creamer
* Coffee Arabica

### Finished Goods

* Oldtown White Coffee Custom 3-in-1 150g

---

## BOM Screen

Should show:

```text
Finished Good:
[Oldtown White Coffee Custom 3-in-1 150g]

Output Qty:
[1]

Components:
------------------------------------------------
| Product          | Qty | UOM |
------------------------------------------------
| Sugar            | 50  | g   |
| Creamer          | 30  | g   |
| Arabica Coffee   | 70  | g   |
------------------------------------------------
```

---

# Bottom Line

Your dev’s architecture is correct:

* Raw ingredients belong in Products master
* BOM references those products
* BOM is the manufacturing recipe
* Production order consumes raw materials
* Inventory updates automatically

What should improve:

* product type separation
* filtered dropdowns
* cleaner BOM UX
* manufacturing-focused fields in BOM screen
Yes, “Quantity per FG” is awkward ERP wording.

What they probably mean is:

> Quantity required per Finished Good

or

> Quantity used to produce 1 Finished Good unit

For normal users, this label is confusing because:

* “FG” is internal manufacturing jargon
* non-production users won’t understand it
* it feels technical and incomplete

---

# Better Naming Options

Recommended:

| Better Label               | Why Better             |
| -------------------------- | ---------------------- |
| Qty Per Unit               | simplest               |
| Quantity Per Unit          | very clear             |
| Usage Per Unit             | manufacturing friendly |
| Material Qty Per Product   | descriptive            |
| Required Quantity          | easy for operators     |
| Qty for 1 Finished Product | most explicit          |

---

For your coffee BOM:

| Component      | Qty Per Unit |
| -------------- | ------------ |
| Sugar          | 50g          |
| Creamer        | 30g          |
| Arabica Coffee | 70g          |

That is much more understandable than:

```text
Quantity per FG
```

---

# What FG Means

FG = Finished Goods

Manufacturing ERP teams use:

* RM = Raw Material
* WIP = Work In Progress
* FG = Finished Goods

But exposing these abbreviations in UI is usually bad UX unless users are warehouse/manufacturing specialists.

---

# Recommended UI Improvement

Instead of:

```text
Quantity per FG
```

Use:

```text
Quantity Per Unit
```

or

```text
Required Qty
```

This makes the BOM screen understandable instantly for:

* sales team
* procurement
* management
* operators
* customers during demo

without manufacturing training.
Bottom line: the labels look like developer/internal ERP terms, not user-facing business language.

The issue is not the function. The issue is wording.

## Main Problems

### 1. Too much internal abbreviation

`RM` means Raw Material, but normal users may not know that.

Better:

* `RM Consumptions` → `Raw Material Usage`
* `Component (RM)` → `Raw Material`
* `RM warehouse batch` → `Raw Material Batch`

---

### 2. “Consumption” sounds too technical

In manufacturing ERP, “consume” means raw material is deducted from inventory.

But for users, “consumption line” sounds strange.

Better:

* `RM Consumptions` → `Raw Materials Used`
* `Add consumption line` → `Add Raw Material Used`
* `Planned consumption` → `Planned Quantity`
* `Posted at (RM)` → `Raw Materials Deducted At`

---

### 3. Labels are written from system logic, not user workflow

The system is thinking:

```text
consume RM → produce FG → post inventory
```

But the user thinks:

```text
what material did we use → how much finished goods did we produce → update stock
```

So the screen should use business/action language.

---

## Recommended Label Changes

| Current Label                | Better Label              |
| ---------------------------- | ------------------------- |
| RM consumptions              | Raw Materials Used        |
| Component (RM)               | Raw Material              |
| Warehouse batch ID           | Batch No.                 |
| Planned consumption          | Planned Qty               |
| Planned consumption (shadow) | Planned Qty Reference     |
| RM warehouse batch           | Raw Material Batch        |
| Add consumption line         | Add Raw Material          |
| Posted at (RM)               | Raw Materials Deducted At |
| Posted at (FG)               | Finished Goods Added At   |
| Request rework               | Send for Rework           |
| Quantity per FG              | Qty Per Unit              |

---

## What This Page Should Mean

This page is for a production batch.

It should clearly show:

1. Which production batch is being made
2. Which raw materials are planned
3. Which raw material batches were used
4. Whether raw materials have been deducted from inventory
5. Whether finished goods have been added to inventory
6. Whether rework is needed

Current wording makes it feel like a database/admin page instead of a production screen.

## Recommendation

Ask dev to avoid abbreviations like `RM`, `FG`, and technical words like `consumption` in frontend labels.

Use:

* Raw Material
* Finished Product
* Quantity Used
* Batch No.
* Deduct Stock
* Add Finished Goods to Stock

That will make the ERP much easier for sales, warehouse, and production users to understand.


Yes — your dev is mostly correct.

In most ERP systems:

```text
Estimate = Quotation = Sales Quotation = Proposal
```

Different ERP systems use different naming:

* Odoo → Quotation
* SAP → Sales Quotation
* Oracle → Estimate / Quote
* SME ERP → Quotation

So your Phase 1 flow:

```text
Sales Rep: Create Estimate
```

can directly map to:

```text
Create Quotation
```

inside your ERP.

You do NOT necessarily need a separate “Estimate” module.

---

# But Your Current ERP Is Still Missing Important Workflow Logic

Right now your Quotation module is only:

* create quotation
* send
* convert to sales order

But your Phase 1 process needs:

* approval workflow
* AI recipe validation
* rejection loop
* status tracking
* review stages

That is the real missing part.

---

# What Phase 1 Actually Represents

Your image is NOT just quotation creation.

It is:

## Order Intake + Recipe Approval Workflow

This is a controlled approval process before sales order creation.

---

# Current Flow Breakdown

## Step 1 — Client Request

Customer asks:

> “I want custom 3-in-1 coffee recipe”

ERP needs:

* client
* inquiry
* request intake

---

## Step 2 — Sales Rep Create Estimate

Sales creates quotation/estimate.

ERP should capture:

* customer
* requested formula
* qty
* target pricing
* special requirements

Current ERP already has:
✅ Quotation module

---

## Step 3 — AI Agent Check Recipe History

This is NOT standard quotation logic.

This is:

* recipe duplication check
* existing BOM lookup
* historical pricing
* manufacturing feasibility

ERP needs:

* BOM history search
* formulation database
* AI recommendation layer

Currently likely missing.

---

## Step 4 — President Review

Approval workflow.

ERP needs:

* approval status
* approver assignment
* approve/reject action
* comments

Currently missing from quotation workflow.

---

## Step 5 — VP Pricing Review

Second approval layer.

ERP needs:

* multi-level approval routing
* pricing approval rules
* margin validation

Currently missing.

---

## Step 6 — Convert to Sales Order

This already exists.

Your quotation page already has:

```text
Convert to Sales Order
```

So this part is correct.

---

# What ERP Needs To Fully Support Phase 1

## 1. Quotation / Estimate Module

You already have this.

Should contain:

| Field        | Example                              |
| ------------ | ------------------------------------ |
| Customer     | Oldtown White Coffee                 |
| Product Type | Custom OEM                           |
| Recipe       | 50g sugar / 30g creamer / 70g coffee |
| Qty          | 3000                                 |
| Target Price | RM/S$                                |
| Notes        | OEM custom blend                     |

---

# 2. Recipe Formulation Section

VERY IMPORTANT.

Need:

| Feature                 | Purpose                    |
| ----------------------- | -------------------------- |
| Recipe Version          | formula tracking           |
| BOM linkage             | production                 |
| Existing formula search | avoid duplicate            |
| AI recipe matching      | recommend previous recipes |
| Cost simulation         | profitability              |

Without this:

* sales creates random recipes
* production mismatch happens
* costing inaccurate

---

# 3. Approval Workflow Engine

Critical missing feature.

Need statuses like:

| Status                     | Meaning            |
| -------------------------- | ------------------ |
| Draft                      | sales preparing    |
| Pending AI Review          | AI checking        |
| Pending President Approval | management review  |
| Pending Pricing Approval   | pricing validation |
| Approved                   | ready for SO       |
| Rejected                   | return to sales    |
| Converted                  | SO created         |

---

# 4. Approval Action Buttons

Inside quotation page:

| Button            | Role        |
| ----------------- | ----------- |
| Submit for Review | Sales       |
| Approve           | President   |
| Reject            | President   |
| Approve Pricing   | VP Pricing  |
| Return to Sales   | VP          |
| Convert to SO     | Sales/Admin |

---

# 5. Approval Timeline / Audit Trail

VERY IMPORTANT for food manufacturing.

Need:

```text
05/15 10:00 Sales created quotation
05/15 10:15 AI checked similar recipe
05/15 11:00 President approved
05/15 11:30 VP Pricing approved
05/15 11:45 Converted to Sales Order
```

Current ERP page lacks workflow visibility.

---

# 6. Recipe History Search

Your image mentions:

```text
AI Agent : Check Recipe History
```

ERP should support:

* search similar BOM
* previous customer formulas
* duplicate ingredient detection
* previous costing

Example:

```text
Found similar recipe:
OTWC-2025-014
Margin: 22%
Previous MOQ: 5000
```

This is very useful for sales.

---

# What Should Happen Technically

## Recommended ERP Architecture

```text
Quotation
    ↓
Recipe Review
    ↓
BOM Validation
    ↓
Approval Workflow
    ↓
Pricing Approval
    ↓
Sales Order
```

NOT:

```text
Quotation → Sales Order directly
```

because custom manufacturing needs approval control.

---

# Your Current ERP Gap

From the current quotation page:

You already have:
✅ Create quotation
✅ Convert to sales order
✅ Client linkage

But missing:
❌ Approval workflow
❌ Recipe validation
❌ AI recipe history
❌ Multi-stage approval
❌ Pricing review stage
❌ Rejection loop
❌ Workflow status visibility

---

# Recommendation

Do NOT create separate:

```text
Estimate Module
```

Instead:

## Upgrade Existing Quotation Module Into:

```text
Custom Manufacturing Quotation Workflow
```

This is cleaner architecture.

---

# Best UI Naming

Instead of:

```text
Quotation
```

Better:

```text
Sales Estimate
```

or:

```text
OEM Quotation
```
because your process is manufacturing-driven, not simple trading.

---

# Best Final Workflow

## Phase 1

Order Intake & Recipe Review

```text
Client Request
    ↓
Create Estimate
    ↓
AI Recipe Validation
    ↓
Management Approval
    ↓
Pricing Approval
    ↓
Convert to Sales Order
```

---

# Then Phase 2 Should Be

```text
Sales Order
    ↓
Production Order
    ↓
Material Planning
    ↓
Production Batch
    ↓
FG Completion
```

---

# Then Phase 3

```text
Delivery Order
    ↓
Invoice
    ↓
Payment
```

Your ERP structure is already close.

Main missing piece:

* workflow orchestration
* approval engine
* manufacturing quotation intelligence
* better naming/UX language


Ignore the AI Part
That page is supposed to be the:

# “Quotation Detail / Estimate Detail” Page

It is the operational workspace where:

* sales creates the customer estimate
* recipe/BOM gets attached
* approvals happen
* costing is reviewed
* quotation converts into production-ready sales order

Right now your page probably behaves like a normal trading quotation.

But your flow diagram shows a manufacturing quotation workflow.

That means the page should evolve into:

```text
OEM Manufacturing Estimate Workspace
```

---

# What BOM Lines Mean

BOM = Bill of Materials

BOM lines are the individual ingredients/materials/components required to produce the product.

For your coffee manufacturing example:

| Ingredient     | Qty | Unit |
| -------------- | --- | ---- |
| Coffee Powder  | 20  | g    |
| Sugar          | 50  | g    |
| Creamer        | 30  | g    |
| Packaging Film | 1   | pcs  |

These rows are called:

```text
BOM Lines
```

---

# In ERP Context

The BOM lines section is where:

* recipe formulation lives
* costing comes from
* production knows what to manufacture

Without BOM lines:

* quotation is only commercial
* production cannot execute accurately

---

# Example of What Your Page SHOULD Show

# Section 1 — Customer Info

| Field        | Example      |
| ------------ | ------------ |
| Customer     | ABC Coffee   |
| Request Date | 15 May 2026  |
| Sales Rep    | Gary         |
| Estimate No  | EST-2026-001 |

---

# Section 2 — Product / Recipe Summary

| Field         | Example          |
| ------------- | ---------------- |
| Product Name  | OEM White Coffee |
| SKU           | OEM-WC-001       |
| Packaging     | Sachet           |
| Target Weight | 30g              |
| MOQ           | 10,000           |

---

# Section 3 — BOM Lines (VERY IMPORTANT)

Example:

| Material       | Category     | Qty   | Unit Cost | Total |
| -------------- | ------------ | ----- | --------- | ----- |
| Coffee Powder  | Raw Material | 20g   | 0.05      | 1.00  |
| Sugar          | Raw Material | 50g   | 0.01      | 0.50  |
| Creamer        | Raw Material | 30g   | 0.03      | 0.90  |
| Packaging Film | Packaging    | 1 pcs | 0.20      | 0.20  |

ERP auto-calculates:

* material cost
* production cost
* estimated margin

---

# Why BOM Lines Matter

## 1. Costing Accuracy

Sales price depends on:

```text
Ingredient Cost + Packaging + Production + Margin
```

Without BOM:

* sales may underquote
* profit margin unknown

---

## 2. Production Readiness

Once approved:

```text
Quotation → Sales Order → Production Order
```

Production uses BOM lines directly.

---

## 3. Recipe History Search

Your AI step:

```text
AI Agent : Check Recipe History
```

works against BOM lines.

Example:

```text
This BOM is 92% similar to Recipe BOM-2025-113
```

---

# In Your Workflow

Your BOM lines are likely the core object being reviewed.

Because management is NOT approving just price.

They are approving:

* formulation
* ingredient usage
* manufacturability
* profitability

---

# Difference Between Trading ERP vs Manufacturing ERP

## Trading ERP

Quotation lines:

| Product | Qty | Price |
| ------- | --- | ----- |
| Laptop  | 5   | 1000  |

Simple.

---

## Manufacturing ERP (Your Case)

Quotation contains:

* recipe
* BOM
* costing
* formulation
* packaging
* approvals

Much more complex.

---

# Your Current ERP Page Likely Missing

Based on your flow, the page should additionally show:

| Needed Section    | Purpose             |
| ----------------- | ------------------- |
| BOM Lines         | recipe detail       |
| Estimated Cost    | profitability       |
| AI Similar Recipe | duplication check   |
| Approval Status   | workflow            |
| Margin Analysis   | pricing validation  |
| Review Notes      | management comments |
| Revision History  | audit trail         |

---

# Recommended Page Structure

## TOP

Commercial Information

```text
Customer
Estimate No
Sales Rep
Status
Approval Stage
```

---

## MIDDLE

Recipe & BOM

```text
Product
Packaging
Ingredients
Material Cost
Yield
MOQ
```

---

## RIGHT PANEL

Workflow

```text
AI Check Status
President Approval
VP Pricing Approval
Comments
Reject Reason
```

---

## BOTTOM

Financial Summary

```text
Total Cost
Target Margin
Selling Price
Estimated Profit
```

---

# Important Architectural Point

Your BOM lines should NOT only exist in Production Module.

They should already appear during quotation stage.

Why?

Because:

```text
Pricing depends on recipe cost.
```

If BOM only appears after sales order:

* approvals become meaningless
* pricing may be wrong
* no feasibility validation

---

# Simple Explanation You Can Give Your Dev

```text
This page is no longer just a quotation page.

It is an OEM estimate + recipe approval workspace.

BOM lines represent the recipe ingredients/materials required for manufacturing and are needed for costing, approval, AI recipe matching, and production conversion.
```