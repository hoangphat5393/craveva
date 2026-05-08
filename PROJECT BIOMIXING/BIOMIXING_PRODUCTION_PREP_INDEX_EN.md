# Biomixing / Production rollout — documentation index (English)

**Why this exists:** Several `PROJECT BIOMIXING/*` files pre-date major **SO / PO / Sales DO / Invoice / Warehouse** work (multi-warehouse, `warehouse_product_batches`, Sales DO batch identity, reservations, canonical inbound). Use this index to read **current truth** first, then Biomixing-specific gaps.

**Last updated:** 2026-04 (added Phase 0–1 implementation playbook link)

---

## Read first (platform baseline — 2026)

| Topic                                             | Document                                                          |
| ------------------------------------------------- | ----------------------------------------------------------------- |
| **SO / Sales DO / Invoice / Warehouse QA state**  | `FUNC_LOGIC/ERP_SO_PO_DO_INVOICE_WAREHOUSE_QA_VERIFICATION_VI.md` |
| **End-to-end PO · DO · SO · Invoice · Warehouse** | `FUNC_LOGIC/QUY_TRINH_PO_DO_SO_INVOICE_WAREHOUSE_VI.md`           |
| **Warehouse doc hub**                             | `FUNC_LOGIC/WAREHOUSE_INDEX.md`                                   |
| **E2E UAT checklist (buy / sell / stock)**        | `FUNC_LOGIC/UAT_CHECKLIST_MUA_BAN_KHO_E2E_VI.md`                  |

---

## Biomixing folder — what to use

| Document                                                          | Use                                                                                                                                                                           |
| ----------------------------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **`BIOMIXING_PRODUCTION_BASELINE_AND_PREP_2026_VI.md`**           | **Start here** — Vietnamese baseline: what is already built vs what Production must add.                                                                                      |
| **`BIOMIXING_DOC_STALE_AUDIT_AND_REPLACEMENTS_2026_VI.md`**       | Which older files are outdated and what to read instead.                                                                                                                      |
| **`BIOMIXING_PRODUCTION_DEVELOPMENT_PLAN.md`**                    | Full roadmap (Phases 0–4), architecture, estimates.                                                                                                                           |
| **`BIOMIXING_PRODUCTION_FLOW_CONCEPTS_VI.md`**                    | **Concepts & stock flow** — RM/FG, consume vs receive FG, shared PO & DO, reserve/ship (Vietnamese onboarding).                                                               |
| **`BIOMIXING_PRODUCTION_IMPLEMENTATION_PLAYBOOK_PHASE0_1_VI.md`** | **Pre-coding playbook** — Phase 0–1 MVP: ERD migration order, state machine, Warehouse integration spikes, milestones, tests (Vietnamese).                                    |
| **`BIOMIXING_PRODUCTION_PROTOTYPE_PLAN_VI.md`**                   | Prototype scope & duration (Vietnamese).                                                                                                                                      |
| **`BIOMIXING_FLOW_CRACEVA_GAP.md`**                               | Step-by-step shop flow vs ERP — still valid for **process** mapping; warehouse column partly superseded by baseline doc §3.                                                   |
| **`BIOMIXING_GAP_ANALYSIS.md`**                                   | Draft 2026-02 + **Extension Warehouse + Critical Batch rows updated 2026-04**; read with notice + **`BIOMIXING_PRODUCTION_BASELINE_AND_PREP_2026_VI.md`** for platform truth. |
| **Demo data**                                                     | `2-4-2026_BIOMIXIN_DEMO_PREP_CHECKLIST.md`, `BIOMIXING_DEMO_SCRIPT.md` (rehearsal note)                                                                                       |

---

## Still missing (Production module — unchanged intent)

- BOM / recipe versioning
- Production order & batch record
- RM consumption → FG receipt linked to batches
- CCP gates, rework, receiving QC, sampling/COA (per development plan phases)

---

_Outdated marketing-only content may remain in proposal PDFs/MD; technical scope should follow this index and `BIOMIXING_PRODUCTION_DEVELOPMENT_PLAN.md`._
