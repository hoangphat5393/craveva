# Biomixing System Analysis & Gap Report

**Client:** Biomixing (Agri-tech/Biotech)
**Platform:** Craveva ERP + Craveva AI
**Date:** 2026-02-13
**Status:** DRAFT

---

## Table of Contents

1.  [Executive Summary](#1-executive-summary)
2.  [Module Inventory (Current State)](#2-module-inventory-current-state)
    - [Core Modules](#core-modules)
    - [Extension Modules](#extension-modules)
3.  [Gap Analysis (Missing Requirements)](#3-gap-analysis-missing-requirements)
    - [Critical Priorities](#critical-priorities)
    - [High Priorities](#high-priorities)
    - [Medium Priorities](#medium-priorities)
4.  [End-to-End Process Flow](#4-end-to-end-process-flow)
    - [Phase 1: Input & Recipe Review](#phase-1-input--recipe-review)
    - [Phase 2: Planning & Sourcing](#phase-2-planning--sourcing)
    - [Phase 3: Production](#phase-3-production)
    - [Phase 4: Quality & Delivery](#phase-4-quality--delivery)
5.  [Technical Implementation Plan](#5-technical-implementation-plan)

---

## 1. Executive Summary

This document outlines the technical requirements to adapt the standard Craveva ERP for Biomixing, a biotech manufacturer. The primary challenge is bridging the gap between standard "Order Management" and their specific "Recipe-based Production" workflow, while integrating the **Sales AI Agent** and **Analytic AI Agent** for real-time decision support.

---

## 2. Module Inventory (Current State)

The following modules are currently installed and functional in `hub.craveva.com`.

### Core Modules

_Foundational logic located in `app/Http/Controllers/`._

| Module          | Functionality                     | Key Dependencies  | Integration Status                                        |
| :-------------- | :-------------------------------- | :---------------- | :-------------------------------------------------------- |
| **Sales (CRM)** | Leads, Clients, Estimates, Deals. | `Client`, `Lead`  | **Ready.** Needs Custom Fields for "Recipe".              |
| **Projects**    | Tasks, Milestones, Time Logs.     | `Project`, `Task` | **Ready.** Will serve as the "Production Order" engine.   |
| **Finance**     | Invoices, Payments, Expenses.     | `Invoice`, `Bank` | **Ready.** Standard billing flow applies.                 |
| **Product**     | Product definitions, Pricing.     | `Product`         | **Partial.** Lacks "BOM" (Ingredients) structure.         |
| **HR/Core**     | Employees, User Roles.            | `User`, `Role`    | **Ready.** Roles map to President, Factory Director, etc. |

### Extension Modules

_Located in `Modules/` directory._

| Module        | Functionality               | Key Entities              | Integration Status                                    |
| :------------ | :-------------------------- | :------------------------ | :---------------------------------------------------- |
| **Purchase**  | Vendor Management, POs.     | `PurchaseOrder`, `Vendor` | **Ready.** Essential for raw material sourcing.       |
| **Warehouse** | Stock Tracking, Transfers.  | `Warehouse`, `Stock`      | **Partial.** Needs Batch/Expiry tracking for biotech. |
| **Pricing**   | Complex pricing structures. | `Pricing`                 | **Optional.** May support margin simulation.          |
| **Asset**     | Equipment tracking.         | `Asset`                   | **Optional.** Can track mixing machines.              |

---

## 3. Gap Analysis (Missing Requirements)

The following components must be developed or configured to meet the proposal's promises.

### Critical Priorities

_Must be addressed before the Pilot._

| Feature                  | Description                        | Technical Requirement                                                                                                                           |
| :----------------------- | :--------------------------------- | :---------------------------------------------------------------------------------------------------------------------------------------------- |
| **AI Integration Layer** | APIs for Agents to query ERP data. | **New Endpoints:** `GET /api/v1/ai/estimates/history` (Sales Agent), `GET /api/v1/ai/inventory/check` (Analytic Agent).                         |
| **Batch Tracking**       | Traceability for biotech safety.   | **Schema Update:** Add `batch_number` (string) and `expiry_date` (date) to `warehouse_product_stock` table.                                     |
| **Recipe Management**    | Defining the "Formula".            | **New Feature:** Extend `Product` entity to support `hasMany` relationship with `Product` (Ingredients) → a simple **Bill of Materials (BOM)**. |

### High Priorities

_Required for the "Unified Workflow" experience._

| Feature          | Description                        | Technical Requirement                                                                                                                 |
| :--------------- | :--------------------------------- | :------------------------------------------------------------------------------------------------------------------------------------ |
| **Quality Lock** | Prevent shipping unverified goods. | **Validation Rule:** Hook into `DeliveryNoteController::store`. If linked `Project Task: Quality Check` != "Completed", throw error.  |
| **Auto-Project** | Convert Order to Production Plan.  | **Observer/Event:** `OrderCreated` event listener that auto-creates a `Project` with a template set of Tasks (Weighing, Mixing, Lab). |

### Medium Priorities

_Enhancements for better UX._

| Feature                | Description                       | Technical Requirement                                                                              |
| :--------------------- | :-------------------------------- | :------------------------------------------------------------------------------------------------- |
| **Storage Conditions** | "Keep Frozen at -20°C".           | **Custom Field:** Add `storage_condition` (dropdown) to `purchase_products` and `products` tables. |
| **Approve via Email**  | President approves without login. | **Notification:** Send "Approve" button in email that hits a signed URL endpoint.                  |

---

## 4. End-to-End Process Flow

### Phase 1: Input & Recipe Review

1.  **Input:** Distributor (Siam Shrimp) requests "Custom Probiotic Mix".
2.  **ERP Action:** Sales Rep creates **Lead** → **Draft Estimate**.
3.  **AI Action (Sales Agent):** Query: _"Show last 3 approved recipes for Siam Shrimp."_
    - _Input:_ Client ID.
    - _Output:_ List of previous Estimates with Margin %.
4.  **Decision:** President reviews.
    - _If Approved:_ Status = `Accepted` → Convert to **Order**.

### Phase 2: Planning & Sourcing

5.  **ERP Action:** **Order** conversion triggers **Project** creation (e.g., "Prj-001: 500kg Mix").
6.  **AI Action (Analytic Agent):** Query: _"Check stock for BOM against Order Qty."_
    - _Input:_ Product ID (Recipe) + Qty.
    - _Output:_ "Sufficient" or "Shortage: Missing 50kg Base A".
7.  **Automation:** If Shortage → System drafts **Purchase Order** in `Purchase` module.

### Phase 3: Production

8.  **ERP Action:** Factory Director views **Project Tasks**:
    - Task 1: **Weighing** (Input: Batch # of Raw Material used).
    - Task 2: **Mixing** (Time Log: Machine usage).
    - Task 3: **Lab Testing** (Upload: PDF Cert).
9.  **Data Transformation:** Inventory (Raw Material) decremented; Inventory (Finished Good) incremented.

### Phase 4: Quality & Delivery

10. **Checkpoint:** Lab Manager marks "Quality Check" task as **Completed**.
11. **System Logic:** `DeliveryNote` creation is now **Unlocked**.
12. **ERP Action:** Logistics generates **Delivery Note** (includes Batch #).
13. **Output:** Goods shipped.
14. **Finance:** Delivery Note → **Invoice** → Payment.

---

## 5. Technical Implementation Plan

1.  **Database Migrations:**
    - `2026_02_14_000000_add_batch_tracking_to_warehouse.php`
    - `2026_02_14_000001_create_product_bom_table.php`
2.  **API Development:**
    - Create `Modules/AiIntegration/` (New Module?) or add routes to `api.php`.
3.  **Workflow Logic:**
    - Implement `OrderObserver` for Project creation.
    - Implement `DeliveryNoteRequest` validation for Quality Lock.

---

**Tags:** #Biomixing #GapAnalysis #TechnicalSpecs #CravevaERP #AIIntegration
