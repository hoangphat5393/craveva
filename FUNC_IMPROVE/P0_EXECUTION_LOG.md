# P0 Execution Log - Biomixing

Ngay tao: 2026-05-09  
Lien ket ke hoach: `FUNC_IMPROVE/P0_NEXT_ACTION_BIOMIXING_VI.md`

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

| Date       | Task ID | Owner          | Status      | Progress % | Evidence                                                                                                                                                                                                                                                                                                                                                                | Blocker                                                                      | Next action                                           |
| ---------- | ------- | -------------- | ----------- | ---------: | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ---------------------------------------------------------------------------- | ----------------------------------------------------- |
| 2026-05-09 | P0-01   | PM + Tech Lead | In progress |         60 | `Modules/Production/Config/config.php` (default controlled: 5% / require_reason=true / block=false)                                                                                                                                                                                                                                                                     | Chua xac nhan tenant pilot co override DB (`production_company_fg_policies`) | Chot bien ban policy tenant + screenshot setting page |
| 2026-05-09 | P0-02   | Dev + BA       | In progress |         75 | `FUNC_IMPROVE/P0_VARIANCE_APPROVAL_ROLE_MATRIX_VI.md` + code gate `edit_production_orders`                                                                                                                                                                                                                                                                              | Chua UAT signed-off nhom vai tro thuc te                                     | Chot mapping role -> permission trong tenant pilot    |
| 2026-05-09 | P0-03   | PM + Tech Lead | In progress |         55 | `FUNC_IMPROVE/P0_SHADOW_YIELD_UOM_GOVERNANCE_ROLLUP_VI.md` + `config production.phase2`                                                                                                                                                                                                                                                                                 | Chua sign-off tenant pilot bat shadow                                        | Ky ten tren rollup + log tenant                       |
| 2026-05-09 | P0-04   | Dev            | Done        |        100 | `warehouse.product-batches.*`, `WarehouseProductBatchController`, `tests/Feature/WarehouseProductBatchRoutesTest.php`                                                                                                                                                                                                                                                   |                                                                              | Tuy chinh filter/export neu pilot yeu cau             |
| 2026-05-09 | P0-05   | Dev + BA       | In progress |         90 | `production.batches.trace` -> link `warehouse.product-batches.show`; nguoc lai tu warehouse detail (truoc do)                                                                                                                                                                                                                                                           | Chua bien ban UAT chinh thuc                                                 | Dien checklist + screenshot QA                        |
| 2026-05-09 | P0-06   | Dev            | Done        |        100 | Widget `warehouse.stock.index`; `WarehouseReconciliationService::inventorySnapshotVsBatchTotals`; `Modules/Warehouse/Config/config.php` -> `inventory_reconciliation` (env `WAREHOUSE_INVENTORY_RECONCILIATION_EQUALITY_EPSILON`, `WAREHOUSE_INVENTORY_RECONCILIATION_WARNING_ABSOLUTE_DELTA`); `tests/Feature/WarehouseReconciliationServiceInventorySnapshotTest.php` |                                                                              | Pilot chot gia tri env; tuy chon audit log neu can    |
| 2026-05-09 | P0-07   | QA + Dev       | In progress |         80 | `04_WH_RUNBOOK_UPGRADE_VI.md` muc 2.1 bang Evidence WUP                                                                                                                                                                                                                                                                                                 | Cot UAT trong bang con trong cho tung tenant                                 | Workshop 30 phut dien UAT/reference                   |
| 2026-05-09 | P0-08   | QA + BA        | In progress |         40 | `FUNC_IMPROVE/P0_MINI_UAT_CHECKLIST_BIOMIXING_VI.md`                                                                                                                                                                                                                                                                                                                    | Chua co ket qua Pass/Fail thuc te                                            | Chon ngay chay 3 luong + attach bien ban              |

---

## 3) Chi tiet tung task (DoD checklist)

### P0-01 - Chot policy mac dinh pilot

- [ ] Chot `policy_mode`
- [ ] Chot `tolerance_percent` / `tolerance_absolute`
- [ ] Chot rule `require_reason` / `block_beyond_tolerance`
- [ ] Co bien ban quyet dinh PM + Tech Lead

### P0-02 - Chot approval variance + role

- [ ] Chot role matrix approve variance
- [ ] Chot flow approve/reject tren UI
- [ ] Co test/UAT role allowed-forbidden
- [ ] Co tai lieu role matrix cap nhat

### P0-03 - Governance shadow Yield/UOM

- [ ] Chot tenant nao duoc bat shadow
- [ ] Chot dieu kien bat/tat flag
- [ ] Chot rollback note
- [ ] Co sign-off PM + Tech Lead

### P0-04 - Warehouse Batch List backlog

- [ ] Co task route/controller/view/filter
- [ ] Co acceptance criteria
- [ ] Co estimate effort
- [ ] Co owner implementation

### P0-05 - Trace 2 chieu Warehouse <-> Production

- [ ] Chot field trace bat buoc
- [ ] Chot deep-link chieu Production -> Warehouse
- [ ] Chot deep-link chieu Warehouse -> Production
- [ ] Co mock luong click cho QA/BA

### P0-06 - Reconciliation widget UI

- [x] Chot vi tri widget
- [x] Chot cong thuc doi chieu
- [x] Chot rule canh bao (epsilon + nguong “material” qua config/env)
- [x] Co test data cho service/widget inputs (`WarehouseReconciliationServiceInventorySnapshotTest`)

### P0-07 - Recheck WUP bang evidence 3 lop

- [x] Cap nhat WUP table co cot Evidence (muc 2.1 trong `04_*`)
- [x] Moi WUP (01-07) co dong evidence code/test trong bang
- [ ] Moi WUP co test/UAT evidence (neu applicable) — dien cot UAT khi pilot
- [ ] Dong bo trang thai Done/Partial khop thuc te

### P0-08 - Mini UAT 3 luong goc

- [ ] Estimate -> SO
- [ ] SO -> DO -> Invoice
- [ ] PO -> GRN -> Bill
- [ ] Co bien ban pass/fail + issue severity (dung template `P0_MINI_UAT_CHECKLIST_BIOMIXING_VI.md`)

---

## 4) Daily standup format (copy nhanh)

`[TaskID] [Status] - Hom qua da lam gi | Hom nay lam gi | Blocker | Evidence`

Vi du:

`[P0-02] [In progress] - Da draft role matrix | Chot voi BA | Cho PM approve role override | FUNC_IMPROVE/P0_VARIANCE_APPROVAL_ROLE_MATRIX_VI.md`
