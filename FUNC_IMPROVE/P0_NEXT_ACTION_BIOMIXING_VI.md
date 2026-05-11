# Biomixing - P0 Next Action (tuan nay)

Ngay cap nhat: 2026-05-09 (tiep: P0-05 UAT 2 chieu + P0-07 dien cot UAT + P0-08 chay mini UAT; P0-06 da co nguong config)
Muc tieu: dong cac muc `Mot phan/Chua` trong P0, dua tai lieu va trang thai ve "co the thuc thi ngay".

---

## 1) Pham vi P0 can dong

1. Production FG policy/BOM hardening (con dieu kien rollout).
2. Batch unified cho B2B + Production (Warehouse list + trace + reconciliation UI).
3. Warehouse runbook governance (xac nhan Done/Partial theo bang chung code + test + UAT).

---

## 2) Checklist thuc thi (co owner + DoD + pre-fill trang thai)

| ID    | Viec can lam                                                                                       | Owner de xuat  | Thoi luong | Trang thai hien tai     | Evidence hien co (pre-fill)                                                                                                                                                                                                                                   | Definition of Done                                                   |
| ----- | -------------------------------------------------------------------------------------------------- | -------------- | ---------- | ----------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | -------------------------------------------------------------------- |
| P0-01 | Chot policy mac dinh cho pilot (`controlled`, tolerance, require reason, block/allow) theo company | PM + Tech lead | 0.5 ngay   | **Mot phan**            | Da co default trong `Modules/Production/Config/config.php`: `policy_mode=controlled`, `tolerance_percent=5`, `controlled_require_reason_beyond_tolerance=true`, `controlled_block_beyond_tolerance=false`                                                     | Co bien ban quyet dinh policy + config tenant pilot                  |
| P0-02 | Chot quy trinh approval variance (`approved_by`, `approved_at`) va quyen role                      | PM + BA + Dev  | 1 ngay     | **Mot phan**            | Code: `edit_production_orders`; tai lieu: `FUNC_IMPROVE/P0_VARIANCE_APPROVAL_ROLE_MATRIX_VI.md`                                                                                                                                                               | UAT role allowed/forbidden + sign-off BA map role nghiep vu          |
| P0-03 | Chot governance shadow Yield/UOM (khong bat dai tra neu chua sign-off)                             | PM + Tech lead | 0.5 ngay   | **Mot phan**            | Rollup: `FUNC_IMPROVE/P0_SHADOW_YIELD_UOM_GOVERNANCE_ROLLUP_VI.md` + `production.phase2.yield_uom_shadow_enabled` mac dinh `false`                                                                                                                            | Sign-off tenant pilot + ghi log execution                            |
| P0-04 | Tao backlog implementation `Warehouse Batch List` (inventory-first)                                | Dev lead       | 1 ngay     | **Da lam (MVP UI)**     | Route `warehouse.product-batches.*`, controller `WarehouseProductBatchController`, menu Warehouse; test `WarehouseProductBatchRoutesTest`                                                                                                                     | Filter nang cao / export / doc BA tuy pilot                          |
| P0-05 | Chot trace 2 chieu Warehouse <-> Production (man hinh va deep-link)                                | BA + Dev       | 1 ngay     | **Mot phan**            | Hai chieu MVP: `warehouse.product-batches.show` -> Production; `production.batches.trace` -> link mo `warehouse.product-batches.show` (dieu kien: module Warehouse + `view_warehouse_stock`)                                                                  | Bien ban UAT 2 chieu + anh man hinh                                  |
| P0-06 | Bo sung reconciliation widget UI (khong chi command)                                               | Dev            | 1-2 ngay   | **Da lam (MVP+nguong)** | Widget `warehouse.stock.index`; `WarehouseReconciliationService::inventorySnapshotVsBatchTotals`; `config('warehouse.inventory_reconciliation')` + env `WAREHOUSE_INVENTORY_RECONCILIATION_*`; test `WarehouseReconciliationServiceInventorySnapshotTest.php` | Tenant pilot chot gia tri env + audit log neu van hanh yeu cau       |
| P0-07 | Recheck WUP status theo quy tac bang chung 3 lop (route/service/test/UAT)                          | QA lead + Dev  | 0.5 ngay   | **Mot phan**            | `04_*` co muc **2.1 Bang Evidence WUP (P0-07)** (code/test/UAT cot)                                                                                                                                                                                           | Dien cot UAT theo tung tenant pilot + cap nhat trang thai WUP-08..10 |
| P0-08 | Chay UAT nho cho 3 luong goc: `Estimate->SO`, `SO->DO->Invoice`, `PO->GRN->Bill`                   | QA + BA        | 1 ngay     | **Mot phan**            | Template: `FUNC_IMPROVE/P0_MINI_UAT_CHECKLIST_BIOMIXING_VI.md`                                                                                                                                                                                                | Di day du 3 luong + bien ban Pass/Fail                               |

---

## 3) Thu tu lam trong tuan (khuyen nghi)

1. **Ngay 1:** P0-01, P0-02, P0-03 (chot quy tac truoc khi code tiep).
2. **Ngay 2:** P0-04, P0-05 (thiet ke + chia task).
3. **Ngay 3:** P0-06 + P0-07 (dong bo runbook va reconciliation).
4. **Ngay 4:** P0-08 (UAT va chot trang thai P0 Done/Partial).

### Thu tu toi uu hoa theo trang thai pre-fill

1. **Chot quyet dinh nghiep vu truoc:** P0-01 -> P0-02 -> P0-03.
2. **Dong khoi "thieu UI":** P0-04 -> P0-05 -> P0-06.
3. **Dong bo governance + acceptance:** P0-07 -> P0-08.

---

## 4) Bang chung bat buoc de danh dau "Done"

- **Code evidence:** migration/service/controller/route hoac view da co.
- **Test/UAT evidence:** test pass hoac bien ban UAT co bang chung.
- **Doc evidence:** tai lieu `FUNC_IMPROVE` cap nhat ngay, khong mau thuan voi code.

Neu thieu 1 trong 3 lop tren => giu trang thai `Partial`.

---

## 5) Risk neu bo qua P0 nay

- Mo rong Production nhung policy/approval chua chot => sai nghiep vu.
- Batch B2B va Production tiep tuc lech nhan thuc => kho doi soat.
- Doc noi "Da lam" nhung code/test chua du => rui ro go-live.

---

## 6) Trang thai hien tai (de bat dau)

- P0-01: Chua chot bien ban policy tenant.
- P0-02: Da co action approve variance, chua chot role matrix van hanh.
- P0-03: Da co phan tich shadow, can governance sign-off.
- P0-04: Da co route `/account/warehouse-product-batches` (MVP list + detail).
- P0-05: Hai chieu MVP code (Warehouse detail + Production trace); can bien ban UAT.
- P0-06: Da co widget + nguong so (epsilon + canh bao “material” qua `warning_absolute_delta`); can tenant chot gia tri env neu khac mac dinh.
- P0-07: Da co bang evidence WUP §2.1 trong `04_*`; can dien UAT theo pilot.
- P0-08: Da co template checklist; can chay dot UAT va dien bien ban.

---

## 7) Bang evidence toi thieu can thu thap tiep

- **P0-01:** snapshot cau hinh tenant pilot (mode/tolerance/flags) + quyet dinh PM.
- **P0-02:** matrix role -> action approve variance + test case role forbidden/allowed.
- **P0-04/P0-05/P0-06:** URL man hinh hoac PR link cho Warehouse Batch list, trace 2 chieu, reconciliation widget; voi P0-06 kem snapshot `.env` pilot (`WAREHOUSE_INVENTORY_RECONCILIATION_EQUALITY_EPSILON`, `WAREHOUSE_INVENTORY_RECONCILIATION_WARNING_ABSOLUTE_DELTA`) neu khac mac dinh.
- **P0-07:** bang WUP co cot `Evidence` theo tung ma `WUP-xx`.
- **P0-08:** bien ban UAT mini (3 luong) kem ket qua pass/fail.

---

## 8) P0-01 can lam gi nua neu he thong da de `controlled`?

Khong can code moi cho buoc P0-01. Can dong 4 viec nghiep vu/van hanh:

1. **Xac nhan tenant pilot co override DB hay khong**  
   Kiem tra bang `production_company_fg_policies` (neu co row cho company thi se de override config default).
2. **Chot bo tham so chinh thuc cho pilot**  
   Ghi ro 4 key: `policy_mode`, `tolerance_percent`, `tolerance_absolute`, `controlled_block_beyond_tolerance`.
3. **Chot quy tac escalation lien quan P0-02**  
   Khi vuot tolerance: ai duoc approve, trong bao lau, co duoc post FG truoc approval hay khong.
4. **Chot bang chung quyet dinh**  
   Luu bien ban + screenshot setting page (`/account/production/fg-quantity-policy`) vao log P0.
