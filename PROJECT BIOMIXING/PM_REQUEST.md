<!-- Converted from PM request.rtf via pandoc for AI/dev reading. Source RTF unchanged. -->

# PM request — Biomixing (readable copy)

_Nguồn gốc: [`PM request.rtf`](./PM%20request.rtf) · Chuyển sang Markdown: 2026-05-20_

---

**ERP Manufacturing & BOM Flow Requirements for Craveva**

Version: Draft for Dev Team Prepared by: Gary Date: 15 May 2026

**Objective**

Build a proper manufacturing flow in Craveva ERP for:

Sales Order → Production Order → BOM Consumption → Inventory Update →
Delivery Order → Invoice

Example use case:

Customer:

- Oldtown White Coffee

Product:

- Custom 3-in-1 Coffee 150g

Recipe:

- Sugar: 50g

- Creamer: 30g

- Arabica Coffee: 70g

Order Quantity:

- 3000 packs

**Current Problem**

Current BOM screen is showing:

- ALL products mixed together

- No separation between:
    - Finished Goods

    - Raw Materials

    - Packaging

    - Services

This creates:

- poor UX

- wrong BOM selection

- manufacturing confusion

- inventory inaccuracies

**Correct ERP Manufacturing Architecture**

**1. Product Master Must Be Centralized**

All items should first be created in:

Operations → Products

BOM should NOT create products.

BOM should only reference products.

**Recommended Product Types**

Need a proper product_type field.

**Suggested Product Types**

---

**Product Type** **Purpose**

raw_material Ingredients
finished_goods Sellable manufactured products
semi_finished Intermediate products
packaging Packaging materials

service Non-stock services

---

**Example Product Setup**

**Raw Materials**

---

**Product Name** **Product Type**

Sugar raw_material
Creamer raw_material

Arabica Coffee raw_material

---

**Finished Goods**

---

**Product Name** **Product Type**
Oldtown White Coffee Custom 3-in-1 150g finished_goods

---

**BOM Concept**

BOM = Manufacturing recipe/formula.

Definition:

What materials are required to produce 1 finished product unit.

**Example BOM**

**Finished Good**

Oldtown White Coffee Custom 3-in-1 150g

**Output**

1 Pack

**Components**

---

**Component** **Qty** **UOM**

Sugar 50 g
Creamer 30 g

Arabica Coffee 70 g

---

Total:

- 150g

**Recommended BOM Screen Design**

**BOM Header**

---

**Field** **Example**

BOM Code BOM-COF-001
Finished Good Oldtown White Coffee Custom 3-in-1 150g
BOM Version V1
Output Qty 1
UOM Pack

Status Active

---

**BOM Components Table**

---

**Component** **Qty** **UOM** **Waste %**

Sugar 50 g 0
Creamer 30 g 0

Arabica Coffee 70 g 0

---

**Critical UX Improvement Needed**

**Current Issue**

Current BOM dropdown:

- shows ALL products

- very hard to select

- impossible to scale

**Required Dropdown Filtering**

**Finished Good Dropdown**

Must only show:

product_type = \'finished_goods\'

**BOM Components Dropdown**

Must only show:

product_type IN (

\'raw_material\',

\'semi_finished\',

\'packaging\'

)

**Production Order Flow**

**Example Production Order**

Customer orders:

- 3000 packs

ERP should automatically calculate:

---

**Material** **Formula**

Sugar 50g × 3000
Creamer 30g × 3000

Arabica Coffee 70g × 3000

---

**Expected Results**

---

**Material** **Total Required**

Sugar 150kg
Creamer 90kg

Arabica Coffee 210kg

---

**Inventory Logic**

**Upon Production Completion**

System should:

**Deduct Raw Materials**

---

**Item** **Qty**

Sugar -150kg
Creamer -90kg

Arabica Coffee -210kg

---

**Increase Finished Goods**

---

**Item** **Qty**
Oldtown White Coffee Custom 3-in-1 150g +3000 packs

---

**Production Order Screen Improvements**

Current screen issue observed:

\"No BOM is linked to this order.\"

This is correct behavior.

However improvements needed:

**Recommended Production Order Fields**

---

**Field** **Purpose**

Production Order No tracking
Finished Good manufactured item
BOM Linked selected BOM
Planned Qty target qty
Produced Qty actual qty
Raw Material Warehouse consumption warehouse
Finished Goods Warehouse FG destination
Batch Code traceability

Status Draft / Released / In Progress / Completed

---

**Recommended Status Flow**

**Sales Order**

Draft → Confirmed

**Production Order**

Draft

→ Released

→ In Production

→ Completed

→ Closed

**Delivery Order**

Pending

→ Picking

→ Delivered

**Invoice**

Draft

→ Sent

→ Paid

**Recommended End-to-End ERP Flow**

**Step 1**

Create Customer

Sales → Clients

**Step 2**

Create Raw Materials

Operations → Products

Examples:

- Sugar

- Creamer

- Arabica Coffee

**Step 3**

Create Finished Product

Operations → Products

Example:

- Oldtown White Coffee Custom 3-in-1 150g

**Step 4**

Create BOM

Production → Bill of Materials

Link:

- Finished product

- Raw materials

**Step 5**

Create Sales Order

Operations → Sales Orders

Customer:

- Oldtown White Coffee

Qty:

- 3000 packs

**Step 6**

Generate Production Order

ERP should:

- link BOM

- auto calculate materials

- reserve stock

**Step 7**

Complete Production

ERP should:

- deduct raw materials

- add finished goods inventory

**Step 8**

Generate Delivery Order

Operations → Sales Delivery Order

**Step 9**

Generate Invoice

Finance → Invoices

**Additional Recommendations**

**1. Add UOM Conversion**

Need support for:

- g

- kg

- pack

- carton

Example:

- 1000g = 1kg

**2. Batch Traceability**

Need:

- batch code

- manufacturing date

- expiry date

Especially important for:

- food manufacturing

- FMCG

- coffee products

**3. BOM Versioning**

Need:

- V1

- V2

- archived BOM

Useful when recipe changes.

**4. Packaging BOM Support**

Need support for:

- sachet

- pouch

- carton

- labels

1 field how do pack FG

Example:

---

**Component** **Type**

Sugar Raw Material
Coffee Raw Material
Sachet Packaging Packaging

Carton Box Packaging

---

**Final Recommendation**

Core ERP design should follow:

Products Master

├── Raw Materials

├── Packaging

├── Semi Finished Goods

└── Finished Goods

BOM

└── Recipe / Formula Layer

Production Order

└── Manufacturing Execution Layer

Inventory

└── Stock Movement Layer

Sales Order

└── Commercial Layer

**Priority Changes Required**

**High Priority**

- Product type classification

- BOM dropdown filtering

- Proper BOM structure

- Inventory consumption logic

- Production auto-calculation

**Medium Priority**

- Batch tracking

- BOM versioning

- UOM conversion

- Packaging support

**Expected Outcome**

After implementation:

- cleaner BOM UX

- scalable manufacturing ERP

- proper inventory traceability

- automated material planning

- proper production accounting

- usable FMCG manufacturing flow
