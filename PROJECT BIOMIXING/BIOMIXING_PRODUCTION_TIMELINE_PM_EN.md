# Craveva ERP — Production Module: Timeline Summary (for PM)

**Context:** Laravel/PHP multi-tenant ERP; multi-warehouse already available.  
**Reference:** `BIOMIXING_PRODUCTION_DEVELOPMENT_PLAN.md` (full Vietnamese report). **Platform baseline (2026):** `BIOMIXING_PRODUCTION_BASELINE_AND_PREP_2026_VI.md`, `FUNC_LOGIC/ERP_SO_PO_DO_INVOICE_WAREHOUSE_QA_VERIFICATION_VI.md`.

**Assumptions:** 1–2 backend developers (FT) + ~0.5 QA + PM; Hub go-live after UAT on staging; no parallel major projects.

---

## Table 1 — Go-live scenarios & calendar (wall-clock to Hub)

| Scenario                          | Scope (Hub go-live)                                                                                                 | Dev + integration (est.)  | UAT / fix / deploy (buffer) | **Total to go-live**                                |
| --------------------------------- | ------------------------------------------------------------------------------------------------------------------- | ------------------------- | --------------------------- | --------------------------------------------------- |
| **MVP Production**                | Phases 0 + 1: BOM, batch MVP, production order/batch, RM/FG stock movements by batch (CCP gates not fully enforced) | ~8–12 weeks               | +2–3 weeks                  | **~10–15 weeks** (~2.5–4 months)                    |
| **HACCP-ready (Biomixing pilot)** | Phases 0 + 1 + 2: add CCP checkpoints, receiving QC, rework, delivery quality lock                                  | ~14–22 weeks              | +3–4 weeks                  | **~17–26 weeks** (~4–6.5 months)                    |
| **Full roadmap (§4)**             | Phases 3–4 on top (sampling/COA, auto project, PRP, audit export, etc.)                                             | +7–13 weeks after Phase 2 | +2–4 weeks                  | **~6–9 months** from kickoff (advanced feature set) |

**Suggested Hub milestones**

1. **Go-live 1:** After MVP — internal / single-site pilot, real batch data.
2. **Go-live 2:** After Phase 2 — “official” production module for HACCP-style gate + inbound QC.
3. **Go-live 3 (optional):** Phases 3–4 — sampling/COA, PRP logs, audit exports (as required).

---

## Table 2 — Phase duration (calendar weeks)

| Phase | Content (summary)                                                                 | Duration (est.) |
| ----- | --------------------------------------------------------------------------------- | --------------- |
| **0** | Domain terms, pilot flow, ERD Production ↔ Warehouse ↔ Order                      | 1–2 weeks       |
| **1** | Recipe/BOM, batch traceability MVP, production order/batch, warehouse integration | 6–10 weeks      |
| **2** | CCP checkpoints, receiving QC, rework, quality lock on delivery                   | 5–8 weeks       |
| **3** | Sampling + COA, auto project, storage/location fields                             | 3–6 weeks       |
| **4** | PRP logs, audit export, email approval, etc.                                      | 3–6 weeks       |

**Multi-warehouse already in place:** saves roughly **1–2 weeks** vs. “greenfield” warehouse integration; Phases 1–2 remain heavy (new BOM, production batch, CCP logic).

**Main schedule risks:** scope changes to BOM/CCP; messy product master data; missing shop-floor specs; Hub tenant data migration for legacy lots.

---

_End of PM summary. Align with `BIOMIXING_PRODUCTION_DEVELOPMENT_PLAN.md` for architecture and gap details._
