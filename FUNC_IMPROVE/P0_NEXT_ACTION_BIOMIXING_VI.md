# Biomixing - P0 Next Action (tuan nay)

Ngay cap nhat: 2026-05-09 (hang doi buoc tiep theo: `P0_BIOMIXING_NEXT_STEPS_VI.md`)
Muc tieu: dong cac muc `Mot phan/Chua` trong P0, dua tai lieu va trang thai ve "co the thuc thi ngay".

**Bang test QA/BA (mot luot):** `FUNC_IMPROVE/P0_QA_BA_MASTER_TEST_CASE_TABLE_VI.md`

**Go-live / server:** Chi sau khi UAT + smoke tren **local** (hoac staging rieng team) xong theo `BIOMIXING_UAT_AND_TEST_GUIDE_VI.md` va `BIOMIXING_LOCAL_DEV_SETUP_VI.md` — khong uu tien trien khai production server trong dot P0 nay.

---

## 1) Pham vi P0 can dong

1. Production FG policy/BOM hardening (con dieu kien rollout).
2. Batch unified cho B2B + Production (Warehouse list + trace + reconciliation UI).
3. Warehouse runbook governance (xac nhan Done/Partial theo bang chung code + test + UAT).

---

## 2) Checklist thuc thi (co owner + DoD + pre-fill trang thai)

| ID    | Viec can lam                                                                                       | Owner de xuat  | Thoi luong | Trang thai hien tai     | Evidence hien co (pre-fill)                                                                                                                                                                                                                                   | Definition of Done                                                   |
| ----- | -------------------------------------------------------------------------------------------------- | -------------- | ---------- | ----------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | -------------------------------------------------------------------- |
| P0-01 | Chot policy mac dinh cho pilot (`controlled`, tolerance, require reason, block/allow) theo company | PM + Tech lead | 0.5 ngay   | **Done**                | Bien ban: `P0_EXECUTION_LOG.md` (2026-05-14) — phuong an 1: chi `config.php` defaults; khong bat buoc `production_company_fg_policies`                                                                                                                        | Da chot + ghi log                                                    |
| P0-02 | Chot quy trinh approval variance (`approved_by`, `approved_at`) va quyen role                      | PM + BA + Dev  | 1 ngay     | **Mot phan**            | Matrix + Pest `ProductionVarianceApprovalPermissionTest.php`; **con** UAT BA tenant                                                                                                                                                                           | UAT role allowed/forbidden + sign-off BA map role nghiep vu          |
| P0-03 | Chot governance shadow Yield/UOM (khong bat dai tra neu chua sign-off)                             | PM + Tech lead | 0.5 ngay   | **Done**                | Rollup + `yield_uom_shadow_enabled=false` mac dinh; log P0-03 (pilot OFF) 2026-05-09                                                                                                                                                                          | Sign-off **chi khi** bat shadow; pilot OFF khong can them            |
| P0-04 | Tao backlog implementation `Warehouse Batch List` (inventory-first)                                | Dev lead       | 1 ngay     | **Da lam (MVP UI)**     | Route `warehouse.product-batches.*`, controller `WarehouseProductBatchController`, menu Warehouse; test `WarehouseProductBatchRoutesTest`                                                                                                                     | Filter nang cao / export / doc BA tuy pilot                          |
| P0-05 | Chot trace 2 chieu Warehouse <-> Production (man hinh va deep-link)                                | BA + Dev       | 1 ngay     | **Mot phan**            | Code + checklist + **Pest** `P0BiomixingAutomatedEvidenceTest.php` (wiring); **con** screenshot UAT                                                                                                                                                           | Bien ban UAT 2 chieu + anh man hinh                                  |
| P0-06 | Bo sung reconciliation widget UI (khong chi command)                                               | Dev            | 1-2 ngay   | **Da lam (MVP+nguong)** | Widget `warehouse.stock.index`; `WarehouseReconciliationService::inventorySnapshotVsBatchTotals`; `config('warehouse.inventory_reconciliation')` + env `WAREHOUSE_INVENTORY_RECONCILIATION_*`; test `WarehouseReconciliationServiceInventorySnapshotTest.php` | Tenant pilot chot gia tri env + audit log neu van hanh yeu cau       |
| P0-07 | Recheck WUP status theo quy tac bang chung 3 lop (route/service/test/UAT)                          | QA lead + Dev  | 0.5 ngay   | **Mot phan**            | `04_*` co muc **2.1 Bang Evidence WUP (P0-07)** (code/test/UAT cot)                                                                                                                                                                                           | Dien cot UAT theo tung tenant pilot + cap nhat trang thai WUP-08..10 |
| P0-08 | Chay UAT nho cho 3 luong goc: `Estimate->SO`, `SO->DO->Invoice`, `PO->GRN->Bill`                   | QA + BA        | 1 ngay     | **Mot phan**            | Template `P0_MINI_UAT_CHECKLIST_BIOMIXING_VI.md` + **Pest** route smoke `P0BiomixingAutomatedEvidenceTest.php`                                                                                                                                                | Di day du 3 luong + bien ban Pass/Fail                               |

---

## 3) Thu tu lam trong tuan (khuyen nghi)

**Cap nhat sau khi P0-01 Done:** uu tien **UAT + bang chung** (khong xep "Ngay 1" P0-01 nua).

1. **P0-05:** QA chay checklist hai chieu (EN hoac VI) + screenshot → dong `P0_EXECUTION_LOG`.
2. **P0-08:** QA + BA chay `P0_MINI_UAT_CHECKLIST_BIOMIXING_VI.md` (3 luong) + Pass/Fail.
3. **P0-02:** BA gan role thuc te + bien ban UAT allow/forbidden (tenant pilot).
4. **P0-07:** Dien cot UAT bang §2.1.1 trong `04_WH_RUNBOOK_UPGRADE_VI.md`.
5. **P0-03:** Chi khi PM muon bat shadow — sign-off + cap nhat config + log.

Chi tiet tung buoc (owner, DoD ngan): **`FUNC_IMPROVE/P0_BIOMIXING_NEXT_STEPS_VI.md`**.

### Thu tu cu (lich su — truoc khi P0-01 Done)

1. **Ngay 1:** P0-01, P0-02, P0-03 (chot quy tac truoc khi code tiep).
2. **Ngay 2:** P0-04, P0-05 (thiet ke + chia task).
3. **Ngay 3:** P0-06 + P0-07 (dong bo runbook va reconciliation).
4. **Ngay 4:** P0-08 (UAT va chot trang thai P0 Done/Partial).

### Thu tu toi uu hoa theo trang thai hien tai (2026-05-14)

1. **UAT / evidence:** P0-05 → P0-08 → P0-02 (sign-off) → P0-07 (dien cot UAT §2.1.1).
2. **Tuy chon:** P0-03 chi khi PM yeu cau bat shadow.
3. **Da xong code chinh:** P0-01, P0-04, P0-06; P0-02 da co Pest — con buoc nguoi.

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

- P0-01: **Done** (phuong an 1 — config defaults).
- P0-02: Matrix + **Pest** `ProductionVarianceApprovalPermissionTest.php`; con **UAT BA** tren tenant.
- P0-03: **Done** (pilot shadow OFF; bat ON = sign-off rieng).
- P0-04: **Da lam (MVP UI)**.
- P0-05: Code 2 chieu + checklist + **Pest** `P0BiomixingAutomatedEvidenceTest.php`; **con** QA screenshot + checklist Result.
- P0-06: Da co widget + nguong so (epsilon + canh bao “material” qua `warning_absolute_delta`); can tenant chot gia tri env neu khac mac dinh.
- P0-07: Da co bang evidence WUP §2.1 trong `04_*`; can dien UAT theo pilot.
- P0-08: Template checklist + **Pest** route smoke `P0BiomixingAutomatedEvidenceTest.php`; **con** chay UI 3 luong + dien bien ban.

---

## 7) Bang evidence toi thieu can thu thap tiep

- **P0-01:** snapshot cau hinh tenant pilot (mode/tolerance/flags) + quyet dinh PM.
- **P0-02:** matrix role -> action approve variance + test case role forbidden/allowed.
- **P0-04/P0-05/P0-06:** URL man hinh hoac PR link cho Warehouse Batch list, trace 2 chieu, reconciliation widget; voi P0-06 kem snapshot `.env` pilot (`WAREHOUSE_INVENTORY_RECONCILIATION_EQUALITY_EPSILON`, `WAREHOUSE_INVENTORY_RECONCILIATION_WARNING_ABSOLUTE_DELTA`) neu khac mac dinh.
- **P0-07:** bang WUP co cot `Evidence` theo tung ma `WUP-xx`.
- **P0-08:** bien ban UAT mini (3 luong) + Pest `P0BiomixingAutomatedEvidenceTest.php` (route names only).

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
