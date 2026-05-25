# P0 Execution Log - Biomixing

Ngay tao: 2026-05-09  
Lien ket ke hoach: `FUNC_IMPROVE/P0_NEXT_ACTION_BIOMIXING_VI.md`  
**Bảng test case QA/BA một lượt (P0):** `FUNC_IMPROVE/P0_QA_BA_MASTER_TEST_CASE_TABLE_VI.md`

**Buoc tiep theo (hang doi):** `FUNC_IMPROVE/P0_BIOMIXING_NEXT_STEPS_VI.md`  
**Audit tong quy trinh theo phase (2026-05-24):** `FUNC_IMPROVE/BIOMIXING_FULL_PROCESS_AUDIT_2026_05_VI.md`

---

## 1) Quy uoc cap nhat

- Moi task P0 cap nhat toi thieu 1 lan/ngay.
- Trang thai dung 1 trong 4 gia tri:
    - `Not started`
    - `In progress`
    - `Blocked`
    - `Done`
- `Evidence` phai la URL, path file, test output, hoac screenshot note.
- Neu `Blocked`, bat buoc co `Blocker` + `Next action`.

---

## 2) Bang theo doi tong hop

| Date       | Task ID | Owner          | Status      | Progress % | Evidence                                                                                                                                                                                                                                                                                                                                                                  | Blocker                                                 | Next action                                                               |
| ---------- | ------- | -------------- | ----------- | ---------: | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------- | ------------------------------------------------------------------------- |
| 2026-05-14 | P0-01   | PM + Tech Lead | Done        |        100 | **Phuong an 1:** pilot dung `production.fg_quantity_policy.defaults` trong `Modules/Production/Config/config.php` (controlled / 5% / require_reason beyond tolerance / block_beyond_tolerance=false). **Khong** bat buoc dong `production_company_fg_policies`; sau nay co the chuyen phuong an 2 qua `/account/production/fg-quantity-policy`. Xem muc 3 P0-01 duoi day. |                                                         | Khi pilot doi so: luu policy tren Hub hoac cap nhat default file + deploy |
| 2026-05-14 | P0-02   | Dev + BA       | In progress |         95 | `P0_VARIANCE_APPROVAL_ROLE_MATRIX_VI.md` + gate `edit_production_orders` + Pest `tests/Feature/ProductionVarianceApprovalPermissionTest.php`                                                                                                                                                                                                                              | Chua UAT signed-off nhom vai tro thuc tren tenant pilot | BA gan role -> `edit_production_orders`; bien ban UAT allow/forbidden     |
| 2026-05-09 | P0-03   | PM + Tech Lead | Done        |        100 | Rollup `P0_SHADOW_YIELD_UOM_GOVERNANCE_ROLLUP_VI.md` + mac dinh `yield_uom_shadow_enabled=false` (pilot **OFF**). **Bat shadow sau nay** = sign-off PM + Tech + cap nhat config tenant + log.                                                                                                                                                                             |                                                         | Chi khi PM yeu cau bat: lap ke hoach sign-off + flag ON + log             |
| 2026-05-09 | P0-04   | Dev            | Done        |        100 | `warehouse.product-batches.*`, `WarehouseProductBatchController`, `tests/Feature/WarehouseProductBatchRoutesTest.php`                                                                                                                                                                                                                                                     |                                                         | Tuy chinh filter/export neu pilot yeu cau                                 |
| 2026-05-24 | P0-05   | Dev + BA       | In progress |         85 | **Live demo 2026-05-24:** `P0_MINI_UAT_CHECKLIST` — trace batch/14 P→W Pass (7 Open warehouse batch links). TC-P0-05-01..03 = P; W→P chua chay. Pest smoke 7 passed.                                                                                                                                                                                                      | Chua screenshot + W→P + chu ky BA                       | BA: trace W→P + screenshot; ky checklist                                  |
| 2026-05-09 | P0-06   | Dev            | Done        |        100 | Widget `warehouse.stock.index`; `WarehouseReconciliationService::inventorySnapshotVsBatchTotals`; `Modules/Warehouse/Config/config.php` -> `inventory_reconciliation` (env `WAREHOUSE_INVENTORY_RECONCILIATION_EQUALITY_EPSILON`, `WAREHOUSE_INVENTORY_RECONCILIATION_WARNING_ABSOLUTE_DELTA`); `tests/Feature/WarehouseReconciliationServiceInventorySnapshotTest.php`   |                                                         | Pilot chot gia tri env; tuy chon audit log neu can                        |
| 2026-05-14 | P0-07   | QA + Dev       | In progress |         85 | `04_WH_RUNBOOK_UPGRADE_VI.md` §2.1 + **§2.1.1** (mau dien cot UAT WUP-01..07)                                                                                                                                                                                                                                                                                             | Dien bang 2.1.1 + tom tat vao cot UAT bang 2.1          | QA lead dien sau khi chay runbook §1/§6                                   |
| 2026-05-24 | P0-08   | QA + BA        | In progress |         75 | **`P0_MINI_UAT_CHECKLIST_BIOMIXING_VI.md` dien 2026-05-24:** Luong **E Pass** (order 32, batch 14, Inventory Bánh kem); A/B/C/D **Partial smoke**; Pest 7 passed. Master table `P0_QA_BA_MASTER_TEST_CASE_TABLE` cap nhat P/Partial.                                                                                                                                      | Chua full A2-A4, B2-B4, C2-C4, D2-D3; chua chu ky BA/PM | BA chay buoc «Chua chay» + ky pilot                                       |

---

## 3) Chi tiet tung task (DoD checklist)

### P0-01 - Chot policy mac dinh pilot

**Quyet dinh (2026-05-14) — Phuong an 1:** Tenant pilot **khong** tao ban ghi override trong `production_company_fg_policies`. He thong lay gia tri tu `Modules/Production/Config/config.php` -> `fg_quantity_policy.defaults` (va `ProductionFgQuantityPolicyService` fallback khi khong co dong DB).

- [x] Chot `policy_mode` — **controlled** (theo file config hien tai)
- [x] Chot `tolerance_percent` / `tolerance_absolute` — **5.0** / **0.0**
- [x] Chot rule `require_reason` / `block_beyond_tolerance` — **controlled_require_reason_beyond_tolerance = true**; **controlled_block_beyond_tolerance = false**
- [x] Bien ban quyet dinh: dong cap nhat bang + checklist nay (PM + Tech Lead dong y phuong an 1)

**English (for handoff):** Pilot FG policy follows **repo config defaults only** (no per-company DB row). Switch to per-tenant overrides later via Hub **FG quantity policy** settings if needed.

### P0-02 - Chot approval variance + role

- [x] Chot role matrix approve variance (map ky thuat: `edit_production_orders` — xem `P0_VARIANCE_APPROVAL_ROLE_MATRIX_VI.md`)
- [x] Chot flow approve/reject tren UI (route `production.outputs.approve-variance`)
- [x] Co test automated role allowed/forbidden — `tests/Feature/ProductionVarianceApprovalPermissionTest.php`
- [ ] Co tai lieu role matrix cap nhat + **UAT BA** signed-off tren tenant pilot (van con)

### P0-03 - Governance shadow Yield/UOM

**Mac dinh pilot (2026-05-14):** `yield_uom_shadow_enabled` = **false** trong `Modules/Production/Config/config.php` — **khong** bat shadow cho den khi co sign-off PM + Tech Lead tren rollup.

- [x] Chot tenant nao duoc bat shadow — **pilot: khong bat**
- [x] Chot dieu kien bat/tat flag — chi bat sau sign-off; rollback = dat lai `false` + deploy
- [x] Chot rollback note — tat flag + xoa cache config neu can
- [x] Bien ban pilot **OFF** (mac dinh repo + log P0 2026-05-09) — **khong** can sign-off rieng de giu OFF
- [ ] Co sign-off PM + Tech Lead **chi khi** doi y **bat** shadow (van trong khi OFF)

### P0-04 - Warehouse Batch List backlog

- [x] Co task route/controller/view/filter — MVP: `warehouse.product-batches.*`
- [x] Co acceptance criteria — list + detail pilot
- [x] Co estimate effort — da ghi trong bang P0 goc
- [x] Co owner implementation — Done (xem bang tong hop)

### P0-05 - Trace 2 chieu Warehouse <-> Production

- [x] Chot deep-link chieu Production -> Warehouse (xem checklist `P0_05_TRACE_BIDIRECTIONAL_UAT_CHECKLIST_EN.md` §A)
- [x] Chot deep-link chieu Warehouse -> Production (checklist §B)
- [x] Co mock luong click cho QA/BA — checklist tren
- [x] Co **Pest** wiring trace ↔ warehouse batch (`tests/Feature/P0BiomixingAutomatedEvidenceTest.php`)
- [ ] Co bien ban UAT + screenshot (QA dien)

### P0-06 - Reconciliation widget UI

- [x] Chot vi tri widget
- [x] Chot cong thuc doi chieu
- [x] Chot rule canh bao (epsilon + nguong “material” qua config/env)
- [x] Co test data cho service/widget inputs (`WarehouseReconciliationServiceInventorySnapshotTest`)

### P0-07 - Recheck WUP bang evidence 3 lop

- [x] Cap nhat WUP table co cot Evidence (muc 2.1 trong `04_*`)
- [x] Moi WUP (01-07) co dong evidence code/test trong bang
- [x] **Mau dien cot UAT** — `04_WH_RUNBOOK_UPGRADE_VI.md` **§2.1.1** (bang WUP-01..07: ngay, tester, Pass/Fail, link)
- [ ] Moi WUP co test/UAT evidence (neu applicable) — dien bang 2.1.1 + tom tat vao cot UAT bang 2.1
- [ ] Dong bo trang thai Done/Partial khop thuc te

### P0-08 - Mini UAT 3 luong goc

- [x] Smoke route names (Dev) — `tests/Feature/P0BiomixingAutomatedEvidenceTest.php`
- [ ] Estimate -> SO
- [ ] SO -> DO -> Invoice
- [ ] PO -> GRN -> Bill
- [ ] Co bien ban pass/fail + issue severity (dung template `P0_MINI_UAT_CHECKLIST_BIOMIXING_VI.md`)

---

## 4) Daily standup format (copy nhanh)

`[TaskID] [Status] - Hom qua da lam gi | Hom nay lam gi | Blocker | Evidence`

Vi du:

`[P0-02] [In progress] - Da draft role matrix | Chot voi BA | Cho PM approve role override | FUNC_IMPROVE/P0_VARIANCE_APPROVAL_ROLE_MATRIX_VI.md`

---

## 5) Kiểm thử tự động bổ sung (2026-05-09)

- **`tests/Feature/P0BiomixingAutomatedEvidenceTest.php`:** giữ wiring hai chiều P0-05 (Blade `trace.blade.php` ↔ `product-batches/show.blade.php`) và smoke tên route cho các bước neo của P0-08 (Estimate/SO/Invoice/Sales DO/PO/GRN/Bill). **Không** thay thế biên bản UAT có người.
