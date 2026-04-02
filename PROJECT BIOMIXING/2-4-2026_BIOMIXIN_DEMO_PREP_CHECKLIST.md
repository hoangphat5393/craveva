# BiomiXin Demo Prep Checklist (Data + Files Request)

**Audience:** English-speaking PMs and stakeholders (primary language of this document is **English**).

---

## Project status (read first)

|                        |                                                                                                                                                                                                                                                             |
| ---------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Phase**              | **Demo preparation only** — loading sample/legacy data and rehearsing a scripted walkthrough in Craveva Hub/staging.                                                                                                                                        |
| **Not in scope (yet)** | **No full Production/MES module implementation** is assumed here; this checklist does **not** mean the Biomixing production ERP sub-project has moved to build or go-live. “Production” in the demo is simulated via **Projects / task packs** where noted. |
| **After demo**         | A separate roadmap (e.g. `BIOMIXING_PRODUCTION_DEVELOPMENT_PLAN.md`) applies for real **module development**, timelines, and Hub go-live.                                                                                                                   |

---

## How to read this document: **CORE** vs **SUPPLEMENTARY**

| Label             | Meaning                                                                                                                                                                                                                                                                                                    |
| ----------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **CORE**          | Minimum data/files **BiomiXin must send** for a credible scripted demo (masters + one end-to-end “Story Pack”).                                                                                                                                                                                            |
| **SUPPLEMENTARY** | **Extra** deliverables beyond CORE: optional operational/AI files (**B** / **C**) and **reference** process docs (**§1a**, optional **S8**). They improve realism but **do not** replace A1–A6 masters. Omitting them only reduces realism; omitting CORE (especially **A6**) breaks the end-to-end story. |

**New subsections vs original draft:** **§1a** (R1/R2), **S8**, **Quick reference**, **CORE**/**SUPPLEMENTARY** legend, **Project status**, and **Appendix** were added in the **2026-04** revision. Rows **B1–B5** and **C1–C3** largely **already existed** in the first draft — we **re-labeled** and **linked** them to the flowchart/QA gap analysis. Details: **Appendix**.

---

## Scope

Prepare a credible “legacy data → workable in Craveva ERP” demo aligned to the Biomixing proposal (LINE → ERP → Production Handover → Inventory Shortage → Compliance/Delivery).

**Assumption (current Hub ERP):** We demo using existing backbone modules (Sales/Orders/Projects/Tasks, Purchase, Warehouse) and simulate “production” via **Project task packs** — **not** a full MES/Production module (**see Project status** above).

---

## Quick reference (CORE vs SUPPLEMENTARY)

| Tier                                                                                | Contents                                                                                                                                                                          |
| ----------------------------------------------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **CORE — minimum meaningful demo**                                                  | **A1–A5** (masters + inventory snapshot) **+ A6 Story Pack**. A1–A5 **alone** are **not** enough for a full walkthrough (no single real order story).                             |
| **SUPPLEMENTARY — stronger match to proposal + shop floor (e.g. manual mix 250KG)** | **B3** (real QA checklist) and optionally **B1, B2, B4, B5**; **§1a R1/R2** shop flowchart / SOP reference — **do not** replace master files; they help map tasks and compliance. |
| **SUPPLEMENTARY — optional AI demo (later)**                                        | **C1–C3** (training material for AI scenarios).                                                                                                                                   |

---

## 1) Must-have files — **CORE**

Preferred format: Excel/CSV (Google Sheet OK). PDFs/images OK inside the Story Pack (A6).

**Note:** **A1–A5** = **foundation** data for ERP import. **A6** = **one complete case** (zip) — **required** to avoid a “slides-only” demo. **A3/A4** support cold storage / lot inventory but **do not** replace on-line inspection/CCP steps; those usually appear in **A6 (S5)** and **B3 (SUPPLEMENTARY)**.

|  ID | What BiomiXin provides                                      | Preferred format   | Requested filename (example)  | Used for demo (proposal section) | Minimum acceptance                                                  |
| --: | ----------------------------------------------------------- | ------------------ | ----------------------------- | -------------------------------- | ------------------------------------------------------------------- |
|  A1 | Customer / Distributor master                               | .xlsx / .csv       | `01_customers.xlsx`           | Order Intake + ERP backbone      | customer_code, customer_name, country, contact                      |
|  A2 | Product/SKU master (FG + RM if possible)                    | .xlsx / .csv       | `02_products_sku.xlsx`        | ERP backbone + shortage check    | sku_code, name, UOM, category, pack_size, active                    |
|  A3 | Warehouse + Location list (incl. temp room)                 | .xlsx / .csv       | `03_warehouse_locations.xlsx` | Inventory & Shortage Agent       | warehouse_name, location_code/name, temp_flag (Y/N)                 |
|  A4 | Inventory snapshot (on-hand)                                | .xlsx / .csv       | `04_inventory_snapshot.xlsx`  | Inventory & Shortage Agent       | sku_code, qty, warehouse/location, batch/lot, expiry_date (if used) |
|  A5 | Supplier/Vendor master                                      | .xlsx / .csv       | `05_suppliers.xlsx`           | Purchase request draft           | vendor_code, vendor_name, lead_time_days (if known), MOQ (if known) |
|  A6 | One complete end-to-end **Story Pack** (**most important**) | .zip (mixed files) | `00_story_pack_order001.zip`  | End-to-end walkthrough           | See §2 (S1–S7); optional S8 in §2                                   |

---

## 1a) Reference documents (shop flow) — **SUPPLEMENTARY** _(not a substitute for A1–A5)_

These are **not** master-data files for import. They are **process maps** so PM/dev can align the demo script (task order, handover, QA gates) with BiomiXin’s actual shop floor.

|  ID | What BiomiXin provides                                                      | Example (repo / filename)                                                                         | Role                                                                                         |
| --: | --------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------- | -------------------------------------------------------------------------------------------- |
|  R1 | Processing / manufacturing flowchart (manual mixing 250KG or current pilot) | e.g. `加工流程圖2025.09.08(手動混合250KG)ppt-20260323173932.html` (open in browser) or PDF export | Map **Project task packs**, step order, where **compliance** / inspection sits vs LINE → ERP |
|  R2 | Official SOP excerpt for the same flow (if different from R1)               | PDF                                                                                               | Reduce ambiguity in demo Q&A                                                                 |

**Delivery:** Put **R1** inside the A6 zip **or** email separately. **CORE** data files **A1–A6** per §1 are still required.

---

## 2) Story Pack contents (A6) — **CORE** (+ one **SUPPLEMENTARY** optional row)

Single case we replay in demo (avoid “theory-only”).

| Item | What BiomiXin provides                                                                     | Format             | Why it matters                                   | CORE / SUPPLEMENTARY         |
| ---: | ------------------------------------------------------------------------------------------ | ------------------ | ------------------------------------------------ | ---------------------------- |
|   S1 | One real LINE order thread (text + screenshots)                                            | .png/.jpg + text   | Order Intake + missing-field narrative           | **CORE**                     |
|   S2 | Confirmed order (customer, SKU, qty, delivery date, urgency)                               | .xlsx/.csv         | Structured confirmed order                       | **CORE**                     |
|   S3 | Recipe / formula for this order (Excel OK)                                                 | .xlsx/.pdf         | BOM vs stock / shortage                          | **CORE**                     |
|   S4 | Raw material lots/batches used or planned                                                  | .xlsx/.csv         | Batch / expiry / traceability                    | **CORE**                     |
|   S5 | Physical inspection result (pass/fail) + NG reason if fail                                 | .xlsx/.pdf         | Compliance gate                                  | **CORE**                     |
|   S6 | Lab test / COA (if applicable)                                                             | .pdf               | “Quality certs” before shipping (proposal)       | **CORE**                     |
|   S7 | Packaging + FG result + shipment/delivery record                                           | .xlsx/.pdf         | Close the loop to fulfillment                    | **CORE**                     |
|   S8 | Shop-floor flowchart snapshot for this case (or link) — same as **§1a R1** if already sent | .png / .pdf / link | Align task order with real 250KG (or pilot) flow | **SUPPLEMENTARY** (optional) |

---

## 3) Strongly recommended — **SUPPLEMENTARY**

Improves alignment with the proposal and operational realism (shortage → PO, fulfillment, QA, NG rules).

|  ID | What BiomiXin provides                                     | Format          | Requested filename (example)         | Used for demo                                      | Minimum acceptance              |
| --: | ---------------------------------------------------------- | --------------- | ------------------------------------ | -------------------------------------------------- | ------------------------------- |
|  B1 | PO + receipt/GRN samples (last 1–3 months)                 | .xlsx/.csv/.pdf | `06_po_grn_samples.zip`              | Shortage → purchase                                | 3–5 examples                    |
|  B2 | Sales orders + shipment/delivery samples (last 1–3 months) | .xlsx/.csv/.pdf | `07_sales_delivery_samples.zip`      | Fulfillment & settlement                           | 3–5 examples                    |
|  B3 | QA checklist template (labeling / mixing / QA)             | .pdf/.xlsx      | `08_qa_checklist_template.pdf`       | Compliance & Delivery; **maps to flowchart steps** | **Real** template (not generic) |
|  B4 | NG disposition rules (rework vs reject/return)             | .doc/.xlsx      | `09_ng_rules.xlsx`                   | Avoid vague Q&A                                    | Bullet list OK                  |
|  B5 | Procurement constraints for top 10 raw materials           | .xlsx           | `10_rm_procurement_constraints.xlsx` | Credible shortage                                  | Lead time + MOQ                 |

---

## 4) Optional — **SUPPLEMENTARY** (AI agent demo with real training material)

|  ID | What BiomiXin provides                                    | Format    | Requested filename (example) | Used for demo                 |
| --: | --------------------------------------------------------- | --------- | ---------------------------- | ----------------------------- |
|  C1 | Historical recipe PDFs + approvals                        | .pdf      | `11_recipe_history_pdfs.zip` | Sales AI / “3-day wait” story |
|  C2 | Approval emails / internal notes                          | .eml/.pdf | `12_approval_emails.zip`     | AI context                    |
|  C3 | Product technical sheets (storage, dosage, compatibility) | .pdf      | `13_product_tech_sheets.zip` | Omni-channel answers          |

---

## 5) Timeline estimate (1 dev, current Hub ERP)

Planning ranges for a **credible demo** only — **not** a full Production module build (**see Project status**).

| Demo type                       | What it includes                                                                                                                                      | Expected prep after receiving A1–A6  |
| ------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------ |
| Type A (recommended)            | Import/mapping + batch/expiry visibility + shortage + project/task handover + QA attachments (**clearer shop alignment if B3 + §1a R1 are provided**) | ~3–5 working days                    |
| Type B (light automation)       | Type A + richer handover PDF + basic delivery-release check                                                                                           | ~1–2 weeks                           |
| Type C (real production system) | True BOM/versioning + batch record + CCP gating                                                                                                       | Multi-week build (**not** demo prep) |

**Main risk:** messy master data (duplicate SKUs, missing UOM, inconsistent batch/expiry).

---

## 6) Dev verification (internal — before promising dates)

Confirm on the target branch/staging:

| Area                      | What to verify             | Where (code hints)                                                                |
| ------------------------- | -------------------------- | --------------------------------------------------------------------------------- |
| Warehouse batch/stock     | Batch + expiry visible     | `Modules/Warehouse/Entities/*Batch.php`, `Modules/Warehouse/Database/Migrations/` |
| Warehouse import          | Import UI + jobs           | `Modules/Warehouse/Imports/WarehouseImport.php`, `ImportWarehouseChunkJob.php`    |
| Purchase inventory import | Receiving/inventory import | `Modules/Purchase/Imports/InventoryImport.php`, `ImportInventoryJob.php`          |
| Projects/tasks            | Project + task pack        | Core `app/` Projects/Tasks                                                        |
| Delivery/shipment         | Close demo with shipment   | e.g. `Modules/Purchase` SalesShipment-related flows                               |

---

## 7) Suggested one-line ask to BiomiXin (English)

Please send **A1–A6** (CSV/Excel) including **one Story Pack zip (A6)** with **S1–S7**. **Strongly add B3** and **B1/B2/B4/B5** where available. Attach or link the **shop flowchart** (**§1a R1**, e.g. manual 250KG process) so we can align task packs and compliance with your floor. We will demonstrate: **Order Intake → Validation → Production Handover → Inventory Shortage → Compliance/Delivery readiness**, consistent with the Biomixing proposal and your documented process. **Note:** this is **demo data prep** only; full Production module delivery follows a separate roadmap.

---

## Appendix — Alignment with the gap analysis (what was “missing” vs what was already in the draft)

This answers: _Did we only add labels, or did we actually put the analyzed gaps into the MD?_

| Finding from analysis                                                                        | Already in the first checklist draft?    | Written into this document as                                 |
| -------------------------------------------------------------------------------------------- | ---------------------------------------- | ------------------------------------------------------------- |
| **A1–A5 without A6** is not enough for a real end-to-end story                               | A6 existed but easy to miss vs “5 files” | **Quick reference**, **§1 note**, **§7** stress A6 + S1–S7    |
| **Shop flowchart** (e.g. 250KG HTML) is not a master import; use for task/compliance mapping | Not explicit                             | **§1a R1/R2** + **S8** optional + cross-links                 |
| **A3/A4** don’t replace on-line inspection / CCP narrative                                   | Not explicit                             | **§1 note** (points to **S5** + **B3**)                       |
| **B3** especially important to match flowchart QA steps                                      | B3 existed as a row                      | Same row, **emphasis** “maps to flowchart”, **real** template |
| **B1, B2, B4, B5** for operational realism                                                   | Already in draft                         | Unchanged list; grouped under **SUPPLEMENTARY** with B3       |
| **C1–C3** for optional AI demo                                                               | Already in draft                         | **§4** tagged **SUPPLEMENTARY**                               |
| **Demo prep ≠ Production module build**                                                      | Not in draft                             | **Project status** block                                      |
| **English** for PM                                                                           | Mixed / Vietnamese summary               | Full pass **English** + **Audience** line                     |

**So:** the **analysis-driven additions** are mainly **§1a**, **S8**, the **CORE vs A1-only** explanations, **Project status**, **flowchart/B3 linkage**, and **English**. Rows **B1–B5** and **C1–C3** were **not new inventions** from the analysis — they were **already** in the original checklist; we **kept** them and **clarified** how they relate to the “gaps” (especially B3 + R1).

---

## Revision note

| Date    | Change                                                                                                                                                                                                                     |
| ------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| 2026-04 | English PM-facing pass; added **Project status** (demo prep vs implementation); explicit **CORE** / **SUPPLEMENTARY** labels; **§1a**, **S8**, and gap-analysis **Appendix**; flowchart cross-links; strengthened B3 note. |
