# Production module — progress report (English)

**Repository:** craveva-staging  
**Module path:** `Modules/Production/`  
**Report date:** 2026-05-09  
**Audience:** PM / Tech lead / stakeholders tracking Biomixing & B2B production rollout

---

## 1. Executive summary

The **Production** package is no longer an empty scaffold: it ships as a **nwidart module** with migrations, entities, services, tenant-scoped web routes under `/account/production/*`, Blade UIs for BOMs, production orders, batches, FG policy settings, and **automated Pest coverage** for core flows (BOM, orders, posting, rework, FG policy, tenant access).

**Rollout status (pilot / P0 track):** P0-01 closed (**config-only FG defaults**). P0-02 has **automated permission tests**; BA UAT on tenant still pending. P0-03 **closed for pilot** (**shadow OFF** by default; enabling shadow needs explicit sign-off). P0-05 has **written UAT checklist** plus **Pest wiring tests**; formal QA screenshots still pending. P0-08 has **route-name smoke tests** for the three hub flows; manual mini UAT still pending. P0-07 remains manual QA. See §6 and `FUNC_IMPROVE/P0_EXECUTION_LOG.md`.

**Browser smoke (Simple Browser, local `APP_URL`):** 2026-05-09 — login → `/account/production/orders`, `/account/production/boms`, `/account/production/fg-quantity-policy`, `/account/production/orders/18`, `/account/production/batches/12`, `/account/production/batches/12/trace` — pages load without HTTP error (pilot tenant **Demo Company**).

---

## 2. What is implemented (functional)

| Area                              | Status             | Notes                                                                                                                                                                                         |
| --------------------------------- | ------------------ | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Module bootstrap**              | Done               | `module.json`, `ProductionServiceProvider`, translations, config publish hook                                                                                                                 |
| **Tenant gating**                 | Done               | `ProductionTenantAccess` + `module_settings` migration for `production` permissions                                                                                                           |
| **BOM (FG + components)**         | Done               | CRUD: `production.boms.*`, entities `ProductionBom`, `ProductionBomItem`, Form Requests                                                                                                       |
| **Production orders**             | Done               | Resource routes (no destroy), optional link to sales order / project, RM & FG warehouses, `planned_quantity`, statuses: draft → released → in_progress → completed / cancelled                |
| **BOM snapshot on release**       | Done               | Snapshot items + planned consumption from snapshot service; migration `add_production_order_bom_snapshot`                                                                                     |
| **Production batches**            | Done               | Batch show, trace page, apply planned from BOM snapshot, consumptions, assign warehouse batch, post consumptions, outputs, post FG receipt                                                    |
| **FG quantity policy**            | Done               | Company policy entity + `ProductionFgQuantityPolicyService`; settings UI `production.fg-quantity-policy.*`; modes strict / controlled / flexible (see `Modules/Production/Config/config.php`) |
| **Variance approval (FG output)** | Done (code)        | Phase 2 config `enforce_variance_approval`; routes `outputs.approve-variance`, `outputs.post-fg-receipt` — **pilot UAT / role matrix** still tracked in P0                                    |
| **Rework workflow**               | Done               | `production_rework_orders` + state transitions: store / approve / reject / complete                                                                                                           |
| **Warehouse integration**         | Partial → advanced | Consumptions tie to warehouse batches; trace links; P0-04/05/06 items (batch UI, two-way trace, reconciliation widget) — see §6                                                               |
| **Sales DO quality lock**         | Config-driven      | `phase2.enforce_quality_lock_sales_do` (default true in config) — blocks shipping when linked production is incomplete                                                                        |
| **Phase 2 shadow (yield / UOM)**  | Scaffold           | DB shadow columns migration; `yield_uom_shadow_enabled` default **false** until business sign-off                                                                                             |
| **Legacy URL**                    | Done               | `GET /production{/*}` 301 → `/account/production/orders`                                                                                                                                      |

**Primary routes (names):** `production.boms.*`, `production.orders.*` (+ `release`, `cancel`), `production.batches.show`, `production.batches.trace`, consumptions/outputs/rework endpoints, `production.fg-quantity-policy.*` — see `Modules/Production/Routes/web.php`.

---

## 3. Technical building blocks

- **Services:** `ProductionPostingService`, `ProductionFgQuantityPolicyService`, `ProductionPlannedConsumptionFromSnapshotService` (registered singletons in service provider).
- **Entities:** `ProductionOrder`, `ProductionBatch`, `ProductionBatchConsumption`, `ProductionBatchOutput`, `ProductionBom`, `ProductionBomItem`, `ProductionOrderBomSnapshotItem`, `ProductionCompanyFgPolicy`, `ProductionReworkOrder`.
- **Controllers:** `ProductionBomController`, `ProductionOrderController`, `ProductionBatchController`, `ProductionFgQuantityPolicySettingController`; shared `HandlesProductionErrors`.
- **Views:** `Resources/views/` — BOM index/create/edit/show, orders index/create/edit/show, batch show/trace, FG policy index, layout + setting sidebar partials.

---

## 4. Automated tests (evidence in repo)

| Test file                                                               | Focus                                                                                                        |
| ----------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------ |
| `tests/Feature/ProductionRoutesTest.php`                                | Route registration + `/account` prefix + legacy redirect                                                     |
| `tests/Feature/ProductionBomPersistenceTest.php`                        | BOM persistence                                                                                              |
| `tests/Feature/ProductionBomAndOrderTenantFlowTest.php`                 | Tenant-scoped BOM + order flow                                                                               |
| `tests/Feature/ProductionPostingServiceTest.php`                        | Posting service behaviour                                                                                    |
| `tests/Feature/ProductionFgQuantityPolicyServiceTest.php`               | FG policy calculations / gates                                                                               |
| `tests/Feature/ProductionReworkWorkflowTest.php`                        | Rework state machine                                                                                         |
| `tests/Feature/WarehouseProductBatchRoutesTest.php`                     | Warehouse batch routes (P0 trace surface)                                                                    |
| `tests/Feature/WarehouseReconciliationServiceInventorySnapshotTest.php` | Snapshot vs batch reconciliation (P0-06)                                                                     |
| `tests/Feature/ProductionVarianceApprovalPermissionTest.php`            | P0-02: FG variance approve — **403** without `edit_production_orders`; success when retained                 |
| `tests/Feature/P0BiomixingAutomatedEvidenceTest.php`                    | P0-05/P0-08: Blade wiring trace ↔ warehouse batch + route smoke for Estimate/SO/Invoice/Sales DO/PO/GRN/Bill |

**Suggested regression bundle (from internal runbook):**  
`php artisan test --compact tests/Feature/BiomixingDemoRoutesReadinessTest.php tests/Feature/P0BiomixingAutomatedEvidenceTest.php tests/Feature/ProductionPostingServiceTest.php tests/Feature/ProductionFgQuantityPolicyServiceTest.php tests/Feature/ProductionVarianceApprovalPermissionTest.php tests/Feature/WarehouseReconciliationServiceInventorySnapshotTest.php tests/Feature/WarehouseProductBatchRoutesTest.php`

---

## 5. Configuration highlights

File: `Modules/Production/Config/config.php`

- **`fg_quantity_policy.defaults`:** default `policy_mode = controlled`, tolerances, reason/block toggles for pilot-friendly behaviour.
- **`phase2.enforce_variance_approval`:** variance must be approved before FG receipt when enabled.
- **`phase2.enforce_quality_lock_sales_do`:** DO shipping guard vs incomplete production on linked SO.
- **`phase2.yield_uom_shadow_enabled`:** off by default; governance in `FUNC_IMPROVE/P0_SHADOW_YIELD_UOM_GOVERNANCE_ROLLUP_VI.md`.

Per-tenant overrides can live in **`production_company_fg_policies`** (see P0-01 in execution log).

---

## 6. Pilot / P0 rollout snapshot (from `P0_EXECUTION_LOG.md`)

| Task ID | Theme                                   | Log status (as of last table row)                                                                                                      |
| ------- | --------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------- |
| P0-01   | FG policy defaults / tenant DB override | **Done** — Approach 1: config defaults only (no `production_company_fg_policies` row required); see `P0_EXECUTION_LOG.md` (2026-05-14) |
| P0-02   | Variance approval + role matrix UAT     | In progress (~95%); Pest `ProductionVarianceApprovalPermissionTest.php`; BA UAT on tenant pending                                      |
| P0-03   | Shadow yield/UOM governance             | **Done** (pilot OFF); enable shadow only after explicit PM + Tech sign-off + config change                                             |
| P0-04   | Warehouse batch list / routes           | **Done**                                                                                                                               |
| P0-05   | Two-way trace Production ↔ Warehouse    | In progress (~97%); checklist + **Pest** `P0BiomixingAutomatedEvidenceTest.php` — formal QA screenshots still pending                  |
| P0-06   | Stock page reconciliation widget        | **Done**                                                                                                                               |
| P0-07   | WUP evidence refresh                    | In progress (~85%); runbook §2.1 + **§2.1.1** mẫu điền UAT WUP-01…07                                                                   |
| P0-08   | Mini UAT (3 core flows)                 | In progress (~55%); route smoke in `P0BiomixingAutomatedEvidenceTest.php` — manual Pass/Fail still required                            |

**Interpretation:** core **Production + Warehouse** engineering for MVP-style pilot is largely **landed**; remaining work is **governance, UAT sign-off**, optional **tenant-specific policy DB rows** (P0-01 explicitly chose **config-only defaults** for pilot), and optional **shadow** analytics — not “missing module code” in the same sense as early 2026 scaffold notes.

---

## 7. Documentation index (repo)

| Document                                                     | Purpose                                                           |
| ------------------------------------------------------------ | ----------------------------------------------------------------- |
| `FUNC_IMPROVE/BIOMIXING_PLAYBOOK_P0P1_VI.md`                 | Phase 0–1 technical playbook (BOM, order, batch, snapshot, trace) |
| `FUNC_IMPROVE/BIOMIXING_FULL_DEMO_RUNBOOK_VI.md`             | End-to-end demo steps + test command bundle                       |
| `FUNC_IMPROVE/BIOMIXING_PREP_INDEX_EN.md`                    | English doc index for rollout                                     |
| `FUNC_IMPROVE/BIOMIXING_DEV_PLAN.md`                         | Architecture map (Production vs core app)                         |
| `FUNC_IMPROVE/P0_EXECUTION_LOG.md`                           | Daily P0 task / blocker tracking                                  |
| `FUNC_IMPROVE/P0_05_TRACE_BIDIRECTIONAL_UAT_CHECKLIST_EN.md` | P0-05 manual QA steps (Warehouse ↔ Production)                    |
| `PROJECT BIOMIXING/PHASE3_PRODUCTION_QA.mmd`                 | QA-oriented flow (diagram source)                                 |

---

## 8. Known limitations (honest scope)

- **Destroy production order:** resource explicitly `except(['destroy'])` — cancellation path exists; hard delete not exposed as REST destroy.
- **Who approved what on UI:** internal audit fields exist on outputs/variance paths; broader “approver visibility” on every screen is a **UX** topic outside this module summary.
- **CCP / full HACCP / QC lab:** not claimed as complete in this MVP-focused report; Phase 3+ items live in Biomixing project materials.

---

## 9. Next steps (recommended)

1. **P0-01:** closed (config-only FG defaults). **P0-02:** BA UAT on tenant + role mapping; code evidence: `ProductionVarianceApprovalPermissionTest.php`. **P0-03:** **Done** for pilot (shadow **OFF**); turning **ON** still needs PM + Tech sign-off.
2. Finish **P0-05** formal UAT (screenshots + checklist) — Dev wiring guard: `P0BiomixingAutomatedEvidenceTest.php`.
3. Run **P0-08** mini UAT and attach pass/fail record — route smoke already in `P0BiomixingAutomatedEvidenceTest.php`.
4. Keep regression bundle green before each staging deploy.

---

_This report is generated from the current repository state and `P0_EXECUTION_LOG.md`; update the log and this file when milestones change._
