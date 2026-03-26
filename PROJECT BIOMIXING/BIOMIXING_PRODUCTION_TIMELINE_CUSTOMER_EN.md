# Craveva ERP — Production Module: Delivery Timeline (Customer-facing)

**Subject:** Estimated calendar from project kickoff to **Hub production go-live** (UAT completed).  
**Reference (internal):** `BIOMIXING_PRODUCTION_DEVELOPMENT_PLAN.md`

This document contains **no internal tooling details** — suitable to share with the customer under NDA as agreed.

---

## 1. Assumptions

| Item                   | Assumption                                                                                                      |
| ---------------------- | --------------------------------------------------------------------------------------------------------------- |
| **Platform**           | Craveva ERP (Laravel/PHP), **multi-warehouse** enabled for your tenant                                          |
| **Delivery**           | Development, integration, **UAT on staging**, then deploy to **Hub**                                            |
| **Team (vendor side)** | Dedicated implementation capacity (development + QA support) + project management                               |
| **Your side**          | Timely access for **requirements confirmation**, **UAT**, and **master data** (products, lots where applicable) |

Solo vs. multiple developers may shift dates slightly; **external commitments** should use the **upper end** of each range unless a fixed date is contractually agreed with contingency.

---

## 2. Estimated time to Hub go-live (main table)

| Scenario                  | What is delivered (summary)                                                                                                                                                                           | **Estimated total calendar** (kickoff → Hub go-live)\* |
| ------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------ |
| **A — MVP Production**    | Recipe/BOM foundation, batch traceability (MVP), production orders/batches, RM consumption and FG receipt by batch; **basic** quality linkage (full HACCP/CCP hard gates may be limited in this wave) | **~10–15 weeks** (~2.5–4 months)                       |
| **B — HACCP-style pilot** | Everything in **A**, plus: configurable **CCP checkpoints**, **receiving QC** / quarantine path, **rework** workflow, **delivery quality lock** aligned with your process                             | **~17–26 weeks** (~4–6.5 months)                       |
| **C — Extended roadmap**  | **B** plus later waves: sampling/COA workflows, optional integrations, PRP/audit-oriented reports, etc. (often split into **phase 2+** releases)                                                      | **~6–9 months** from kickoff (varies by scope)         |

\*Ranges include **development, integration, stabilization, UAT cycle, and Hub deployment buffer** as one delivery window.

**Suggested milestones**

1. **First go-live (Hub):** after **MVP (A)** — pilot with real batch data, limited sites if needed.
2. **Controlled production use:** after **HACCP-style pilot (B)** — when CCP/receiving QC/rework and shipping rules match your SOP.
3. **Further enhancements:** optional releases following **C**.

---

## 3. Phase overview (indicative weeks)

| Phase | Focus                                                                       | Indicative duration |
| ----- | --------------------------------------------------------------------------- | ------------------- |
| **0** | Align terms, pilot flow, technical design (Production ↔ Warehouse ↔ Orders) | 1–2 weeks           |
| **1** | BOM/recipe, batch MVP, production orders, warehouse integration             | 6–10 weeks          |
| **2** | CCP checkpoints, receiving QC, rework, quality lock on shipment             | 5–8 weeks           |
| **3** | Optional: sampling/COA, automation hooks, storage conditions, etc.          | 3–6 weeks           |
| **4** | Optional: PRP logs, audit exports, approvals                                | 3–6 weeks           |

Phases are largely **sequential**; **customer UAT and sign-off** remain on the critical path.

---

## 4. How we recommend committing dates (commercially)

| Approach              | Suggested wording                                                                                         |
| --------------------- | --------------------------------------------------------------------------------------------------------- |
| **Target window**     | “Go-live target **between [date] and [date]** after kickoff, subject to signed scope and UAT acceptance.” |
| **Latest commitment** | “**No later than [date]**” — include **contingency** (e.g. +2 weeks) in the contract if scope is fixed.   |

Avoid committing to a **single calendar day** without a signed scope and change-control process.

---

## 5. Factors that shorten or extend the schedule

| Factor                                                        | Typical effect                                          |
| ------------------------------------------------------------- | ------------------------------------------------------- |
| **Smaller first release** (MVP only)                          | Shorter time to first Hub go-live                       |
| **Defer optional Phase 3 items** (e.g. advanced integrations) | Shorter phase 1 delivery; later phase                   |
| **Scope changes** during build                                | Adds time — handled via change request                  |
| **Delayed UAT or incomplete master data**                     | **Extends** calendar regardless of development progress |

---

## 6. Risks to planning accuracy

- Changes to recipe/BOM or CCP rules after build starts
- Low-quality or late **product / lot master data** at cutover
- Customer UAT availability or Hub deployment constraints

---

## 7. Summary — delivery time at a glance

Use this block for **quick reference** in proposals or emails (same numbers as §2).

| Scenario                  | Typical calendar (kickoff → Hub go-live) | In months (approx.) |
| ------------------------- | ---------------------------------------- | ------------------- |
| **A — MVP Production**    | **10–15 weeks**                          | **~2.5–4 months**   |
| **B — HACCP-style pilot** | **17–26 weeks**                          | **~4–6.5 months**   |
| **C — Extended roadmap**  | **~6–9 months**                          | _(scope-dependent)_ |

**Short rules of thumb**

- **Fastest path to first Hub go-live:** choose **Scenario A** only.
- **Scenario B** = **A + Phase 2** work (CCP, receiving QC, rework, quality lock); plan for roughly **+7–11 weeks** on top of A’s calendar (ranges overlap — use the single B row above for commitments).
- **Scenario C** applies only if you need **later waves** (e.g. sampling/COA, PRP-style logs, deeper integrations). If your needs are **orders and data entry only** (no manufacturing module), **this Production timeline does not apply** — scope and dates should be set separately for Sales/Orders.

**Phase 0–2 (cumulative, indicative):** about **12–20 weeks** from kickoff through a **B-style** release (0 + 1 + 2), aligned with the **B** row when using one continuous plan.

**Phase 3–4 (optional add-ons):** about **+7–13 weeks** extra **only if** those optional capabilities are in scope — not required for A or B.

---

_End of customer-facing timeline._
