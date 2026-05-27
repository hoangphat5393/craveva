# Production module — progress report (English)

**Repository:** craveva-staging  
**Module path:** `Modules/Production/`  
**Report date:** 2026-05-15 (refreshed)  
**Audience:** PM / Tech lead / stakeholders tracking Biomixing and B2B production rollout

---

## 1. Executive summary

The **Production** package is a **nwidart** Laravel module with migrations, domain entities, application services, tenant-scoped web routes under `/account/production/*`, Blade UIs for BOMs, production orders, batches, FG quantity policy, and **Pest** coverage for core posting, policy, rework, tenant access, and pilot wiring.

**Pilot / P0 track:** Engineering for MVP-style **Production + Warehouse** flows is **largely complete**. Remaining work is predominantly **human UAT**, **evidence packs** (screenshots, signed matrices, WUP tables), and optional **config or policy tuning** — not greenfield module construction.

**Since the 2026-05-14 revision:** production-order forms now **restrict optional linked sales orders** to commercially **open** rows (`completed` / `canceled` / `refunded` excluded on create; draft edits retain an already-linked closed order for display), enforce the same rule in **Form Request** validation, show **translated order status** beside each SO in the dropdown and on the production order **show** page, ship **module** translation keys for the SO hint (so the UI does not show a raw `production::app.*` key), and add **`tests/Feature/ProductionOrderSalesOrderLinkEligibilityTest.php`**.

**Browser smoke (Simple Browser, local `APP_URL`):** 2026-05-09 — core production URLs load for pilot tenant **Demo Company**. 2026-05-14 — FG policy and batch pilot pages rechecked. Smoke does **not** replace scripted UAT; see §6 and `FUNC_IMPROVE/P0_EXECUTION_LOG.md`.

---

## 2. What is implemented (functional)

| Area                              | Status        | Notes                                                                                                                                                                                                                                         |
| --------------------------------- | ------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Module bootstrap**              | Done          | `module.json`, `ProductionServiceProvider`, translations (`Modules/Production/Resources/lang` + LanguagePack overrides), config merge/publish                                                                                                 |
| **Tenant gating**                 | Done          | `ProductionTenantAccess`; `module_settings` for `production` permissions                                                                                                                                                                      |
| **BOM (FG + components)**         | Done          | CRUD `production.boms.*`; `ProductionBom`, `ProductionBomItem`; Form Requests                                                                                                                                                                 |
| **Production orders**             | Done          | Draft → release → in progress → completed / canceled; RM and FG warehouses; `planned_quantity`; optional **sales order** and **project** links; **SO list filtered to open orders** + **status label** in UI; validation aligned with filter  |
| **BOM snapshot on release**       | Done          | Snapshot lines + planned consumption service; migration `add_production_order_bom_snapshot`                                                                                                                                                   |
| **Production batches**            | Done          | Show, trace, apply planned RM from snapshot (equal split), consumptions, warehouse batch assignment, post RM / FG outputs, post FG receipt, print slip                                                                                        |
| **FG quantity policy**            | Done          | `ProductionCompanyFgPolicy`, `ProductionFgQuantityPolicyService`; settings UI `production.fg-quantity-policy.*`; strict / controlled / flexible (`Modules/Production/Config/config.php`)                                                      |
| **Variance approval (FG output)** | Done (code)   | Config `enforce_variance_approval`; approve variance + post FG paths — **BA UAT sign-off** still open (P0-02)                                                                                                                                 |
| **Rework workflow**               | Done          | `production_rework_orders` lifecycle: store / approve / reject / complete                                                                                                                                                                     |
| **Warehouse integration**         | Advanced      | Batch-aware consumptions; trace deep links; P0-04 / P0-05 / P0-06 surfaces (batch UI, bidirectional trace, stock reconciliation widget)                                                                                                       |
| **Sales DO quality lock**         | Config-driven | `phase2.enforce_quality_lock_sales_do` — can block shipping when linked production is incomplete                                                                                                                                              |
| **Phase 2 shadow (yield / UOM)**  | Scaffold      | DB shadow columns; `yield_uom_shadow_enabled` default **false** until PM + Tech sign-off                                                                                                                                                      |
| **Material shortage summary**     | Done (code)   | Cross-order RM shortage DataTable + drill-down; see `18_PRODUCTION_MATERIAL_SHORTAGE_SUMMARY_PLAN_VI.md`                                                                                                                                      |
| **RM reserve at Release**         | Done (core)   | Reserve on **Release**, release on **Cancel**, consume after all batches post RM; block Release if insufficient available — see `19_PRODUCTION_RM_RESERVE_AT_RELEASE_PLAN_VI.md` + UAT `19_PRODUCTION_RM_RESERVE_AT_RELEASE_TEST_CASES_VI.md` |
| **Legacy URL**                    | Done          | `GET /production{/*}` 301 → `/account/production/orders`                                                                                                                                                                                      |

**Primary route names:** `production.boms.*`, `production.orders.*` (including `release`, `cancel`), `production.batches.show`, `production.batches.trace`, consumption/output/rework endpoints, `production.fg-quantity-policy.*` — see `Modules/Production/Routes/web.php`.

---

## 3. Technical building blocks

- **Services:** `ProductionPostingService`, `ProductionFgQuantityPolicyService`, `ProductionPlannedConsumptionFromSnapshotService` (singletons in `ProductionServiceProvider`).
- **Entities:** `ProductionOrder`, `ProductionBatch`, `ProductionBatchConsumption`, `ProductionBatchOutput`, `ProductionBom`, `ProductionBomItem`, `ProductionOrderBomSnapshotItem`, `ProductionCompanyFgPolicy`, `ProductionReworkOrder`.
- **Controllers:** `ProductionBomController`, `ProductionOrderController`, `ProductionBatchController`, `ProductionFgQuantityPolicySettingController`; shared `HandlesProductionErrors`.
- **Cross-domain:** `App\Models\Order` scope `eligibleForProductionOrderLink()` for open SO listing; closed-status constants shared with Production Form Requests.
- **Views:** BOM CRUD, orders index/create/edit/show, batch show/trace, FG policy screens, layout and sidebar partials.

---

## 4. Automated tests (evidence in repo)

| Test file                                                               | Focus                                                                                                                    |
| ----------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------ |
| `tests/Feature/ProductionRoutesTest.php`                                | Route registration, `/account` prefix, legacy redirect                                                                   |
| `tests/Feature/ProductionBomPersistenceTest.php`                        | BOM persistence                                                                                                          |
| `tests/Feature/ProductionBomAndOrderTenantFlowTest.php`                 | Tenant-scoped HTTP BOM + production order                                                                                |
| `tests/Feature/ProductionPostingServiceTest.php`                        | Posting service behaviour                                                                                                |
| `tests/Feature/ProductionFgQuantityPolicyServiceTest.php`               | FG policy calculations and gates                                                                                         |
| `tests/Feature/ProductionReworkWorkflowTest.php`                        | Rework state machine                                                                                                     |
| `tests/Feature/WarehouseProductBatchRoutesTest.php`                     | Warehouse batch routes (P0 trace surface)                                                                                |
| `tests/Feature/WarehouseReconciliationServiceInventorySnapshotTest.php` | Inventory snapshot vs batch reconciliation (P0-06)                                                                       |
| `tests/Feature/ProductionVarianceApprovalPermissionTest.php`            | P0-02: variance approve — 403 without `edit_production_orders`                                                           |
| `tests/Feature/ProductionOrderSalesOrderLinkEligibilityTest.php`        | Open SO dropdown + reject closed SO on store; status string in create HTML                                               |
| `tests/Feature/P0BiomixingAutomatedEvidenceTest.php`                    | P0-05 / P0-08: trace ↔ warehouse batch Blade wiring + route smoke (Estimate / SO / Invoice / Sales DO / PO / GRN / Bill) |
| `tests/Feature/BiomixingDemoRoutesReadinessTest.php`                    | Named route presence for demo readiness                                                                                  |

**Suggested regression bundle (internal runbook):**

```bash
php artisan test --compact tests/Feature/BiomixingDemoRoutesReadinessTest.php tests/Feature/P0BiomixingAutomatedEvidenceTest.php tests/Feature/ProductionPostingServiceTest.php tests/Feature/ProductionFgQuantityPolicyServiceTest.php tests/Feature/ProductionVarianceApprovalPermissionTest.php tests/Feature/WarehouseReconciliationServiceInventorySnapshotTest.php tests/Feature/WarehouseProductBatchRoutesTest.php tests/Feature/ProductionOrderSalesOrderLinkEligibilityTest.php
```

---

## 5. Configuration highlights

File: `Modules/Production/Config/config.php`

- **`fg_quantity_policy.defaults`:** default `policy_mode = controlled`, tolerances, reason / block toggles for pilot-friendly behaviour.
- **`phase2.enforce_variance_approval`:** variance approval required before FG receipt when enabled.
- **`phase2.enforce_quality_lock_sales_do`:** Sales DO shipping guard when linked production is incomplete.
- **`phase2.yield_uom_shadow_enabled`:** off by default; governance in `FUNC_IMPROVE/P0_SHADOW_YIELD_UOM_GOVERNANCE_ROLLUP_VI.md`.

Per-tenant overrides may use **`production_company_fg_policies`** (pilot chose **config-only** defaults for P0-01; see execution log).

---

## 6. Pilot / P0 rollout snapshot

Source of truth for day-to-day status: **`FUNC_IMPROVE/P0_EXECUTION_LOG.md`**. Snapshot below matches the log as of **2026-05-14** rows (percentages and evidence pointers).

| Task ID | Theme                                   | Status (log)    | Progress | Open follow-up                                                                            |
| ------- | --------------------------------------- | --------------- | -------: | ----------------------------------------------------------------------------------------- |
| P0-01   | FG policy defaults / tenant DB override | **Done**        |      100 | Pilot may later save overrides in Hub FG policy UI                                        |
| P0-02   | Variance approval + role matrix UAT     | **In progress** |       95 | BA UAT signed-off on pilot tenant; role → `edit_production_orders`                        |
| P0-03   | Shadow yield / UOM governance           | **Done**        |      100 | Shadow stays **OFF** until explicit PM + Tech sign-on to enable                           |
| P0-04   | Warehouse batch list / routes           | **Done**        |      100 | Optional filter/export tweaks from pilot                                                  |
| P0-05   | Two-way trace Production ↔ Warehouse    | **In progress** |       97 | Formal QA screenshots + UAT minutes                                                       |
| P0-06   | Stock page reconciliation widget        | **Done**        |      100 | Tune env thresholds if pilot needs stricter warnings                                      |
| P0-07   | WUP evidence refresh                    | **In progress** |       85 | Fill `04_WH_RUNBOOK_UPGRADE_VI.md` §2.1.1 (WUP-01…07)                                     |
| P0-08   | Mini UAT (three core flows)             | **In progress** |       60 | Manual Pass/Fail per `P0_MINI_UAT_CHECKLIST_BIOMIXING_VI.md`; route smoke already in Pest |

**Regression note (log):** on **2026-05-14**, an **82-test** Production + Warehouse + Sales DO bundle was reported green (298 assertions), including `OrderCompletionShippedSalesDoGateTest` in the smoke cluster — re-run before each staging cut if policy changes.

---

## 7. Documentation index (repo)

| Document                                                     | Purpose                                                           |
| ------------------------------------------------------------ | ----------------------------------------------------------------- |
| `FUNC_IMPROVE/BIOMIXING_PLAYBOOK_P0P1_VI.md`                 | Phase 0–1 technical playbook (BOM, order, batch, snapshot, trace) |
| `FUNC_IMPROVE/BIOMIXING_FULL_DEMO_RUNBOOK_VI.md`             | End-to-end demo steps + suggested test commands                   |
| `FUNC_IMPROVE/BIOMIXING_PREP_INDEX_EN.md`                    | English doc index for rollout                                     |
| `FUNC_IMPROVE/BIOMIXING_DEV_PLAN.md`                         | Architecture map (Production vs core app)                         |
| `FUNC_IMPROVE/P0_EXECUTION_LOG.md`                           | Daily P0 task and blocker tracking                                |
| `FUNC_IMPROVE/P0_05_TRACE_BIDIRECTIONAL_UAT_CHECKLIST_EN.md` | P0-05 manual QA (Warehouse ↔ Production)                          |
| `PROJECT BIOMIXING/BIOMIXING_PHASES_1_4_SUMMARY_VI.md`       | Short Phase 1–4 business map (not a screen-by-screen spec)        |
| `PROJECT BIOMIXING/PHASE3_PRODUCTION_QA.mmd`                 | QA-oriented flow (diagram source)                                 |

---

## 8. Known limitations (honest scope)

- **Hard delete production order:** resource uses `except(['destroy'])`; cancellation path exists instead of REST destroy.
- **Approver visibility on every screen:** audit fields exist on variance paths; broader UX polish is backlog.
- **CCP / full HACCP / QC lab / COA / sampling:** **not** in scope for this MVP-focused report; see Biomixing project materials and Phase 3+ diagrams.
- **Auto-create production order from SO:** linking is **manual** (optional FK); auto-observer remains a possible future phase.

---

## 9. Next steps (recommended)

1. Close **P0-02** with BA-signed UAT on the pilot tenant using `P0_VARIANCE_APPROVAL_ROLE_MATRIX_VI.md` and existing Pest evidence.
2. Close **P0-05** with checklist completion and screenshot pack (`P0_05_TRACE_BIDIRECTIONAL_UAT_CHECKLIST_EN.md`).
3. Execute **P0-08** three flows manually and attach Pass/Fail; keep `P0BiomixingAutomatedEvidenceTest` green in CI.
4. Finish **P0-07** WUP table §2.1.1 and reconcile “Done / Partial” vs reality.
5. Run the **§4 regression bundle** (plus any project-specific additions) before each staging deploy.

---

## 10. How to refresh this file

1. Update row-level facts in **`FUNC_IMPROVE/P0_EXECUTION_LOG.md`** when task status changes.
2. Edit **§1**, **§2**, **§4**, and **§6** in this report to match the repo and log.
3. Bump **Report date** at the top.

---

_This file is maintained alongside `FUNC_IMPROVE/P0_EXECUTION_LOG.md` and the codebase; align both when closing P0 items._
