# Warehouse / Miaolin — Engineering ↔ PM Alignment Brief (English)

**Purpose:** Single-page reference for PM discussion: **what the UAT pack implies**, **what is already in scope vs missing**, and **decisions we need** before full sign-off.

**Source docs:** `WAREHOUSE_UAT_CHECKLIST_MIAOLIN.md`, gap report + acceptance criteria in the same file; engineering notes in `WAREHOUSE_UAT_PRE_IMPLEMENTATION_ANALYSIS.md`.

---

## 1) Two release meanings (please pick one for this rollout)

| Scope                                       | What “done” means                                                                                                                                                                 | Typical milestone                                                                                   |
| ------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | --------------------------------------------------------------------------------------------------- |
| **Scope A — Warehouse operations**          | Warehouses, manual stock in/out, transfers, movement ledger, purchase inbound (PO _or_ DO, not both), Purchase Inventory absolute sync, permissions, config.                      | **Go** for “we can run warehouse + purchasing stock posting.”                                       |
| **Scope B — Miaolin inventory-aware sales** | Everything in **A**, **plus** sales outbound reduces **warehouse** stock via `StockMovementService`, with **reversal** rules, and **no** unsafe legacy stock mutation on payment. | **Go** for full UAT sign-off as written in the checklist acceptance criteria (“Miaolin readiness”). |

**Engineering note:** The checklist’s **Acceptance Criteria** explicitly requires **sales outbound** for Miaolin readiness. That is **Scope B**. If the business only needs Scope A for now, we should **document a signed scope cut** so QA does not block on B.

---

## 2) What the UAT document already validates vs what is still a product gap

| Area                                                       | UAT expectation                                                   | Engineering status (high level)                                                                             |
| ---------------------------------------------------------- | ----------------------------------------------------------------- | ----------------------------------------------------------------------------------------------------------- |
| Warehouse master, stock adjustment, transfer, movements UI | Exercise flows & data integrity                                   | **In scope** for Scope A; keep testing against checklist.                                                   |
| Inbound from purchase (PO delivered **or** DO received)    | Exactly **one** canonical inbound path per environment            | **Config + process**; risk if **both** flags are enabled in production.                                     |
| Purchase Inventory absolute sync                           | Delta posts movements to match target qty                         | **Implemented** in Purchase + `StockMovementService` path (verify on staging).                              |
| **Sales outbound** (invoice/order/payment)                 | **Critical gap** in checklist: must reduce warehouse stock        | **Not implemented** as end-to-end warehouse outbound from sales; requires Scope B work.                     |
| Legacy payment stock adjustment                            | Checklist flags risk: `PurchaseStockAdjustment` without warehouse | **Exists** in code; needs **decision** when we move to Scope B (disable / replace / feature-flag).          |
| Batch/expiry on manual stock UI                            | Medium gap                                                        | Service supports FEFO; **UI** may not capture batch/expiry on all forms — clarify if mandatory for Miaolin. |

---

## 3) Questions for PM (decisions required)

Please answer **before** we commit a delivery date for **Scope B**:

1. **Sales outbound trigger** — When should stock be deducted?  
   Examples: invoice created, invoice approved, delivery confirmed, shipment completed, etc.

2. **Warehouse selection for sales** — How is warehouse chosen per line?  
   Examples: line-level warehouse, invoice-level warehouse, client default warehouse, company default warehouse.

3. **Reversal policy** — On cancel / return / refund / void invoice, how should stock be restored (full/partial, timing)?

4. **Scope sign-off** — Do we sign off **Scope A only** for this phase, with **Scope B** scheduled separately, or is **full checklist sign-off** required on a fixed date?

5. **Checklist vs product** — Some items (e.g. bulk warehouse actions, Excel import of warehouses) may or may not exist in the current build. Should we treat them as **must-have** or **phase 2**?

6. **Permission naming** — The checklist uses names like `warehouse_view`; the app may use `view_warehouses`, etc. Can QA map to the **actual permission keys** in the admin panel?

---

## 4) Risks if decisions stay open

| Risk                                     | Impact                                                                   |
| ---------------------------------------- | ------------------------------------------------------------------------ |
| Outbound trigger unclear                 | Wrong implementation, heavy rework, incorrect financial vs stock timing. |
| Both inbound flags ON in prod            | **Double-counting** stock; hard to unwind.                               |
| Legacy payment stock + new outbound      | **Double effect** or conflicting stock if both run.                      |
| “Full UAT pass” expected without Scope B | **Schedule slip** or false expectation vs written acceptance criteria.   |

---

## 5) Recommended engineering sequence (for planning)

1. **Stabilize Scope A** on staging: env, permissions, smoke + UAT for B–G + H where applicable.
2. **Lock Scope B decisions** (Section 3).
3. Implement **sales outbound + reversal + legacy payment strategy** with tests and a second UAT round.
4. Final **Go/No-Go** using `WAREHOUSE_UAT_GO_NO_GO_SHEET.md`.

---

## 6) Indicative effort (rough order of magnitude)

**Not a fixed quote.** Depends on: staging readiness, how many UAT defects appear, whether checklist items (bulk/import) exist in the build, and how fast PM answers Section 3.

**“Using Cursor AI”** here means: faster drafting/updating docs, scaffolding tests, and some refactors — **not** a substitute for QA, staging validation, or business decisions. Expect **~10–25%** savings on **pure coding/doc** time at best; **calendar** often limited by UAT cycles and sign-off.

| Scope                                                                                                  | Dev effort (1 backend dev, person-days)                                | QA / UAT (person-days, indicative) | Typical wall-clock (1 dev + part-time QA) |
| ------------------------------------------------------------------------------------------------------ | ---------------------------------------------------------------------- | ---------------------------------- | ----------------------------------------- |
| **A — Warehouse operations**                                                                           | **~3–10** (wider if missing features from checklist, e.g. bulk/import) | **~2–6**                           | **~1–2 weeks** if few surprises           |
| **B — Full Miaolin (A + sales outbound + reversals + legacy payment strategy + invoice stock checks)** | **~10–20** (includes integration + idempotency + regression)           | **~4–10**                          | **~3–5 weeks** after Section 3 is locked  |

**Combined A then B (sequential):** often **~4–7 weeks** wall-clock with normal meetings and one UAT round per scope — **add buffer** if PM decisions slip or regression surface area is large.

---

## 7) One-line ask to PM

> **Please confirm whether this rollout targets Scope A only or full Miaolin sign-off (Scope B), and approve the decisions in Section 3 so engineering can estimate and schedule.**

---

_Prepared for internal alignment. Adjust dates and owners in your project plan as needed._
