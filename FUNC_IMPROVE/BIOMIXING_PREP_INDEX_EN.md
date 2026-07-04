# Biomixing / Production rollout — documentation index (English)

**Purpose:** Single entry to find **implementation / rollout** docs. Non-markdown assets (diagrams, PDF proposal, images) and **PM / demo / business narrative** Markdown files stay in **`PROJECT BIOMIXING/`**.

| Location                 | Contents                                                                                                                  |
| ------------------------ | ------------------------------------------------------------------------------------------------------------------------- |
| **`FUNC_IMPROVE/`**      | Markdown for **technical implementation**: baseline, roadmap, playbook, gaps, mapping to tech.                            |
| **`PROJECT BIOMIXING/`** | **Mermaid/HTML diagrams**, proposal PDF, demo scripts, PM one-pager, business context notes, marketing-style proposal MD. |

**Why this split (2026-05-09):** Implementation Markdown lives in **`FUNC_IMPROVE/`** (same level as numbered backlog `01_`–`11_` and `P0_*`), with a **`BIOMIXING_*` filename prefix** — no `BIOMIXING/` subfolder. Product/diagram collateral remains in **`PROJECT BIOMIXING/`**.

**Why this exists (historical):** Several Biomixing technical docs pre-date major **SO / PO / Sales DO / Invoice / Warehouse** work. Read **platform baseline** in `FUNC_LOGIC` first, then Biomixing-specific gaps below.

**Last updated:** 2026-05-24 (doc sync manifest + full process audit)

**Vietnamese — all testing / UAT entry points in one place:** `FUNC_IMPROVE/BIOMIXING_UAT_AND_TEST_GUIDE.md`

---

## Read first (platform baseline — 2026)

| Topic                                             | Document                                                |
| ------------------------------------------------- | ------------------------------------------------------- |
| **SO / Sales DO / Invoice / Warehouse QA state**  | `FUNC_LOGIC/SALES_FULFILLMENT_QA_CHECKLIST.md`               |
| **End-to-end PO · DO · SO · Invoice · Warehouse** | `FUNC_LOGIC/SALES_BUSINESS.md` |
| **Warehouse doc hub**                             | `FUNC_LOGIC/WAREHOUSE_INDEX.md`                         |
| **E2E UAT checklist (buy / sell / stock)**        | `FUNC_LOGIC/SALES_PURCHASE_WAREHOUSE_UAT_CHECKLIST.md`        |

---

## `FUNC_IMPROVE/` — implementation (Markdown)

Paths below are relative to repo root.

| Document                                                 | Use                                                                                                                             |
| -------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------- |
| **`FUNC_IMPROVE/P0_QA_BA_MASTER_TEST_CASE_TABLE.md`** | **QA/BA one-pass** — master table: P0-01,02,03,05,06,08 + P0-07 WUP-01…07; fill Pass/Fail + evidence then update execution log. |
| **`FUNC_IMPROVE/BIOMIXING_BUSINESS_FLOW_LIVE.md`**    | **LIVE SSOT** — standard business flows; update on every business-logic change.                                                 |
| **`FUNC_IMPROVE/BIOMIXING_DOC_HUB.md`**               | **Doc hub** — living doc map, matrix, maintainer rules.                                                                         |
| **`FUNC_IMPROVE/BIOMIXING_UAT_AND_TEST_GUIDE.md`**    | **Vietnamese hub** — where to run UAT / which runbooks + `php artisan test` bundles (Biomixing pilot).                          |
| **`FUNC_LOGIC/PRODUCTION_BUSINESS.md`**        | **Production operations SSOT** — lifecycle, reserve, UOM post RM, FG ledger (§3).                                               |
| **`FUNC_IMPROVE/BIOMIXING_LOCAL_DEV_SETUP.md`**       | **Local dev** — migrate, Mix build, module notes; do server deploy only after local UAT.                                        |
| **`FUNC_IMPROVE/P0_BIOMIXING_NEXT_STEPS.md`**         | **Ordered P0 queue** (next QA/BA/PM steps after P0-01 Done).                                                                    |
| **`FUNC_IMPROVE/BIOMIXING_GAP_STATUS.md`**            | **Status vs code** — Phase 1 & 2, P0 items, what is done vs backlog.                                                            |
| **`FUNC_LOGIC/PRODUCTION_BUSINESS.md`**        | **Production operations SSOT** — lifecycle, reserve at release, shortage scope.                                                 |
| **`FUNC_IMPROVE/BIOMIXING_FLOW_CONCEPTS.md`**         | **Concepts & stock flow** — RM/FG, consume vs receive FG, shared PO & DO, reserve/ship.                                         |
| **`FUNC_IMPROVE/LEGACY_ARCHIVE.md`**                     | Retired plans/audits (pass 1–2 cleanup) and where to read instead.                                                              |

---

## `PROJECT BIOMIXING/` — diagrams, proposal, PM, demo

| Type          | Examples                                                                                                                                                                            |
| ------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Diagrams      | `PHASE1_QUOTATION_FLOW_DIAGRAM.mmd`, `PHASE1_TO_3_END_TO_END_FLOW.mmd`, `PHASE2_PLANNING_PREPRODUCTION.mmd`, `PHASE3_PRODUCTION_QA.mmd`, rendered `.html`, `FULL_FLOW_DIAGRAM.html` |
| Proposal      | `2-4-2026_Biomixing_Proposal_CravevaERP_Formatted.pdf`, `BIOMIXING_PROPOSAL_REVISED.md`                                                                                             |
| PM / business | `PHASE_BUSINESS_CONTEXT_EXAMPLE.md`, `BIOMIXING_PHASES_1_4_SUMMARY.md`, `PM_YEU_CAU_TONG_HOP.md`                                                                              |
| Demo          | `BIOMIXING_FULL_DEMO_RUNBOOK.md` (§ Phụ lục A), `BIOMIXING_DEMO_SCRIPT.md`                                                                                                       |

See also root **`PROJECT BIOMIXING/README.md`**.

---

## Production module — scope note

**Implemented in repo (MVP / pilot track):** BOM CRUD, production orders, batches, BOM snapshot on release, RM reserve at release, RM consumption / FG output posting, FG quantity policy + variance approval path, rework workflow, warehouse batch list + trace links, reconciliation widget — see `BIOMIXING_GAP_STATUS.md`, `FUNC_LOGIC/PRODUCTION_BUSINESS.md`, and `BIOMIXING_FULL_DEMO_RUNBOOK.md`.

**Still roadmap / not full product (examples):** deeper CCP/HACCP automation, extended QC lab workflows, proposal-only AI overlays — see `BIOMIXING_GAP_STATUS.md`.

---

_Outdated marketing-only content may remain in proposal PDFs/MD; technical scope should follow `BIOMIXING_GAP_STATUS.md` and `BIOMIXING_BUSINESS_FLOW_LIVE.md`._
