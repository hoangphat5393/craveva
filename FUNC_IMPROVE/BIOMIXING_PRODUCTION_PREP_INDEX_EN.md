# Biomixing / Production rollout — documentation index (English)

**Purpose:** Single entry to find **implementation / rollout** docs. Non-markdown assets (diagrams, PDF proposal, images) and **PM / demo / business narrative** Markdown files stay in **`PROJECT BIOMIXING/`**.

| Location                 | Contents                                                                                                                  |
| ------------------------ | ------------------------------------------------------------------------------------------------------------------------- |
| **`FUNC_IMPROVE/`**      | Markdown for **technical implementation**: baseline, roadmap, playbook, gaps, mapping to tech.                            |
| **`PROJECT BIOMIXING/`** | **Mermaid/HTML diagrams**, proposal PDF, demo scripts, PM one-pager, business context notes, marketing-style proposal MD. |

**Why this split (2026-05-09):** Implementation Markdown lives in **`FUNC_IMPROVE/`** (same level as numbered backlog `01_`–`11_` and `P0_*`), with a **`BIOMIXING_*` filename prefix** — no `BIOMIXING/` subfolder. Product/diagram collateral remains in **`PROJECT BIOMIXING/`**.

**Why this exists (historical):** Several Biomixing technical docs pre-date major **SO / PO / Sales DO / Invoice / Warehouse** work. Read **platform baseline** in `FUNC_LOGIC` first, then Biomixing-specific gaps below.

**Last updated:** 2026-05-09 (flat `FUNC_IMPROVE/` layout; Biomixing implementation files not in a subfolder)

---

## Read first (platform baseline — 2026)

| Topic                                             | Document                                                          |
| ------------------------------------------------- | ----------------------------------------------------------------- |
| **SO / Sales DO / Invoice / Warehouse QA state**  | `FUNC_LOGIC/ERP_SO_PO_DO_INVOICE_WAREHOUSE_QA_VERIFICATION_VI.md` |
| **End-to-end PO · DO · SO · Invoice · Warehouse** | `FUNC_LOGIC/QUY_TRINH_PO_DO_SO_INVOICE_WAREHOUSE_VI.md`           |
| **Warehouse doc hub**                             | `FUNC_LOGIC/WAREHOUSE_INDEX.md`                                   |
| **E2E UAT checklist (buy / sell / stock)**        | `FUNC_LOGIC/UAT_CHECKLIST_MUA_BAN_KHO_E2E_VI.md`                  |

---

## `FUNC_IMPROVE/` — implementation (Markdown)

Paths below are relative to repo root.

| Document                                                                       | Use                                                                                                                           |
| ------------------------------------------------------------------------------ | ----------------------------------------------------------------------------------------------------------------------------- |
| **`FUNC_IMPROVE/BIOMIXING_PRODUCTION_BASELINE_AND_PREP_2026_VI.md`**           | **Start here** — baseline: what is already built vs what Production must add.                                                 |
| **`FUNC_IMPROVE/BIOMIXING_DOC_STALE_AUDIT_AND_REPLACEMENTS_2026_VI.md`**       | Which older files are outdated and what to read instead.                                                                      |
| **`FUNC_IMPROVE/BIOMIXING_PRODUCTION_DEVELOPMENT_PLAN.md`**                    | Full roadmap (Phases 0–4), architecture, estimates.                                                                           |
| **`FUNC_IMPROVE/BIOMIXING_PRODUCTION_FLOW_CONCEPTS_VI.md`**                    | **Concepts & stock flow** — RM/FG, consume vs receive FG, shared PO & DO, reserve/ship.                                       |
| **`FUNC_IMPROVE/BIOMIXING_PRODUCTION_IMPLEMENTATION_PLAYBOOK_PHASE0_1_VI.md`** | **Pre-coding playbook** — Phase 0–1 MVP: ERD migration order, state machine, warehouse integration spikes, milestones, tests. |
| **`FUNC_IMPROVE/BIOMIXING_PRODUCTION_PROTOTYPE_PLAN_VI.md`**                   | Prototype scope & duration.                                                                                                   |
| **`FUNC_IMPROVE/BIOMIXING_PRODUCTION_DOMAIN_INTEGRATION.md`**                  | Domain integration view.                                                                                                      |
| **`FUNC_IMPROVE/BIOMIXING_FLOW_CRACEVA_GAP.md`**                               | Shop flow vs ERP — process mapping; read with baseline §3.                                                                    |
| **`FUNC_IMPROVE/BIOMIXING_GAP_ANALYSIS.md`**                                   | Gap analysis; read with **`BIOMIXING_PRODUCTION_BASELINE_AND_PREP_2026_VI.md`** for platform truth.                           |
| **`FUNC_IMPROVE/BIOMIXING_PROPOSAL_TO_TECH_MAPPING_VI.md`**                    | Maps proposal themes to technical scope.                                                                                      |
| **`FUNC_IMPROVE/AUDIT_PROJECT_BIOMIXING_MIGRATION_2026_VI.md`**                | What lives in which folder (audit).                                                                                           |

---

## `PROJECT BIOMIXING/` — diagrams, proposal, PM, demo

| Type          | Examples                                                                                                                                                                            |
| ------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Diagrams      | `PHASE1_QUOTATION_FLOW_DIAGRAM.mmd`, `PHASE1_TO_3_END_TO_END_FLOW.mmd`, `PHASE2_PLANNING_PREPRODUCTION.mmd`, `PHASE3_PRODUCTION_QA.mmd`, rendered `.html`, `FULL_FLOW_DIAGRAM.html` |
| Proposal      | `2-4-2026_Biomixing_Proposal_CravevaERP_Formatted.pdf`, `BIOMIXING_PROPOSAL_REVISED.md`                                                                                             |
| PM / business | `PHASE_BUSINESS_CONTEXT_AND_APPROVAL_NOTES_VI.md`, `BIOMIXING_PHASE1_MANAGEMENT_ONEPAGER_VI.md`, `PHASE1_2_BUSINESS_FLOW_PM_VI.md`                                                  |
| Demo          | `2-4-2026_BIOMIXIN_DEMO_PREP_CHECKLIST.md`, `BIOMIXING_DEMO_SCRIPT.md`                                                                                                              |

See also root **`PROJECT BIOMIXING/README.md`**.

---

## Still missing (Production module — unchanged intent)

- BOM / recipe versioning
- Production order & batch record
- RM consumption → FG receipt linked to batches
- CCP gates, rework, receiving QC, sampling/COA (per development plan phases)

---

_Outdated marketing-only content may remain in proposal PDFs/MD; technical scope should follow `BIOMIXING_PRODUCTION_DEVELOPMENT_PLAN.md` and baseline above._
