# Biomixing ERP Demo Script: "From Order to Delivery" (ERP + AI Split View)

**Client:** Biomixing (Agri-tech/Biotech)
**Focus:** Sustainable Farming Solutions (EHPurge, Probiotics, Feed Additives)
**Scenario:** A Distributor orders 500kg of **EHPurge**. We demonstrate the synergy between **Craveva ERP (hub.craveva.com)** for operations and **AI Agents (ai.craveva.com)** for intelligence.

**Rehearsal note (2026 Hub):** When demoing **Delivery Order → Ship**, align with live behaviour: **confirm** reserves stock; **ship** consumes by **warehouse + product line + batch + expiry** where applicable. See `FUNC_LOGIC/ERP_SO_PO_DO_INV_WH_QA_VI.md` and `BIOMIXING_BASELINE_PREP_2026_VI.md` §3.

---

## **Phase 1: Order Intake & Recipe Approval**

**Goal:** Establish the order with correct pricing and recipe.

### **Step 1: Receive Business Order**

- **Platform: ERP System (hub.craveva.com)**
    - **Module:** Leads / Estimates
    - **Action:** Create a new **Lead/Deal** for "Siam Shrimp Distributors".
    - **Input:** Record "Custom Recipe Required" in the description.

### **Step 2: Recipe Review (President)**

- **Platform: ERP System (hub.craveva.com)**
    - **Module:** Estimates
    - **Action:** Sales Agent creates a Draft Estimate. Adds product "Custom Probiotic Mix".
    - **Action:** Sets status to "Waiting Approval".
- **Platform: Sales AI Agent (ai.craveva.com)**
    - **Action:** President asks: _"Show me the last 3 approved formulations for Siam Shrimp Distributors and their success rates."_
    - **Result:** AI retrieves historical recipe data & client feedback to aid the approval decision.

### **Step 3: Cost & Pricing (Vice President)**

- **Platform: Analytic AI Agent (ai.craveva.com)**
    - **Action:** VP asks: _"Simulate margin for Probiotic Mix if raw material costs increase by 5% next month."_
    - **Result:** AI predicts the margin impact, helping the VP set a safe selling price.
- **Platform: ERP System (hub.craveva.com)**
    - **Module:** Estimates
    - **Action:** VP edits the Estimate. Adds raw material costs ($30/kg) and sets Selling Price ($50/kg).
    - **Action:** Approves the Estimate.

### **Step 4: Client Confirmation**

- **Platform: ERP System (hub.craveva.com)**
    - **Module:** Sales Orders
    - **Action:** Convert the approved Estimate to a **Sales Order**.
    - **Result:** **"Order Established"**.

---

## **Phase 2: Project & Production Planning**

**Goal:** Schedule production based on the established order.

### **Step 1: Create Project Request Form**

- **Platform: ERP System (hub.craveva.com)**
    - **Module:** Work > Projects
    - **Action:** Create New Project: **"Production Order #PO-1001 - EHPurge"**.
    - **Input:** Link to Client "Siam Shrimp Distributors", set Deadline (10 days).

### **Step 2: Production Scheduling (Factory Director)**

- **Platform: Analytic AI Agent (ai.craveva.com)**
    - **Action:** Factory Director asks: _"Do we have enough 'Probiotic Base A' for 500kg of EHPurge? If not, when is the next shipment arriving?"_
    - **Result:** AI checks ERP inventory and Purchase Orders, returning a simple "Yes/No" with dates.
- **Platform: ERP System (hub.craveva.com)**
    - **Module:** Projects / Tasks
    - **Action:** Based on AI advice, create Tasks:
        1.  "Confirm Raw Materials"
        2.  "Print Labels (Batch #2025-BX)"

### **Step 3: Product Details Confirmation**

- **Platform: ERP System (hub.craveva.com)**
    - **Module:** Projects / Tasks
    - **Action:** Business Assistant updates Task: "Confirm Packaging Specs & Shipping Address".

---

## **Phase 3: Production & Warehouse**

**Goal:** Produce and Pack.

### **Step 1: Execute Production**

- **Platform: ERP System (hub.craveva.com)**
    - **Module:** Projects
    - **Action:** Move Project Status to **In Progress**.
    - **Action:** Mark tasks as **Completed** (Ingredient Weighing, Mixing/Processing, Packaging).

### **Step 2: Output & Quality Check**

- **Platform: Analytic AI Agent (ai.craveva.com)**
    - **Action:** Quality Manager asks: _"Retrieve the quality certificate for Batch #2025-BX and compare it to the client's required specs."_
    - **Result:** AI validates the batch against the stored client requirements.
- **Platform: ERP System (hub.craveva.com)**
    - **Module:** Projects
    - **Action:** Mark Project as **Finished/Completed**.

---

## **Phase 4: Fulfillment**

**Goal:** Ship to the customer with compliance.

### **Step 1: Pick, Pack & Label**

- **Platform: ERP System (hub.craveva.com)**
    - **Module:** Inventory / Sales
    - **Action:** Warehouse Manager scans **Batch #2025-BX** during picking.
    - **Action:** Generate **Packing List** and **Shipping Label**.

### **Step 2: Logistics & Compliance Check**

- **Platform: Analytic AI Agent (ai.craveva.com)**
    - **Action:** Logistics Manager asks: _"Verify storage temperature requirements for EHPurge shipment to Thailand and suggest best carrier."_
    - **Result:** AI confirms "Keep below 25°C" and suggests "DHL Cold Chain" based on route.

### **Step 3: Dispatch**

- **Platform: ERP System (hub.craveva.com)**
    - **Module:** Sales > Orders
    - **Action:** Create **Delivery Note** and mark Order Status as **Shipped**.
    - **Result:** System emails Tracking Number to client.

---

## **Phase 5: Finance**

**Goal:** Settle accounts and analyze profit.

### **Step 1: Invoicing**

- **Platform: ERP System (hub.craveva.com)**
    - **Module:** Invoices
    - **Action:** Convert Sales Order to **Invoice**. Send to Client.

### **Step 2: Profitability Analysis**

- **Platform: Analytic AI Agent (ai.craveva.com)**
    - **Action:** Finance Officer asks: _"Generate a profitability report for Order #PO-1001 including overheads and raw material fluctuations."_
    - **Result:** AI aggregates data from Sales, Purchase, and Expenses modules to show true net profit.

---

## **Summary Table**

| Phase               | ERP System (hub.craveva.com)                   | AI Agent (ai.craveva.com)                                                                    |
| :------------------ | :--------------------------------------------- | :------------------------------------------------------------------------------------------- |
| **1. Order Intake** | Create Lead, Create Estimate, Convert to Order | **Sales Agent:** Retrieve historical recipes<br>**Analytic Agent:** Simulate pricing margins |
| **2. Planning**     | Create Project, Assign Tasks                   | **Analytic Agent:** Check inventory sufficiency & PO dates                                   |
| **3. Production**   | Update Task Status, Complete Project           | **Analytic Agent:** Validate Quality Certs vs Client Specs                                   |
| **4. Fulfillment**  | Pick/Pack, Generate Docs, Dispatch             | **Analytic Agent:** Check Storage/Route                                                      |
| **5. Finance**      | Create Invoice, Record Payments                | **Analytic Agent:** Real-time Profitability Report                                           |
