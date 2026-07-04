# Biomixing P0 — Các bước tiếp theo (hàng đợi)

**Cập nhật:** 2026-06-16
**Mục đích:** Sau khi **P0-01 Done** và phần lớn code P0-04/05/06 xong, các việc còn lại chủ yếu là **QA / BA / PM** (UAT, biên bản, sign-off). Cập nhật bảng dưới khi từng mục hoàn tất.

**Bảng test case một lượt (ưu tiên cho QA/BA):** `FUNC_IMPROVE/P0_QA_BA_MASTER_TEST_CASE_TABLE.md`

**Dev đã bổ sung (2026-05-09):** Pest `tests/Feature/P0BiomixingAutomatedEvidenceTest.php` — wiring trace ↔ warehouse batch + smoke tên route cho mini UAT 3 luồng (không thay biên bản QA).

**Regression Dev gần nhất (2026-06-16):** P0 bundle hiện tại → **43 passed / 161 assertions**. Focused Products stock-on-hand regression (2026-06-14) → **5 passed / 12 assertions**.

---

## Thứ tự đề xuất (1 → 5)

| #   | ID        | Việc                                                                                                                                    | Owner          | Trạng thái hiện tại                                   | Đầu ra (evidence)                                                                                                  |
| --- | --------- | --------------------------------------------------------------------------------------------------------------------------------------- | -------------- | ----------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------ |
| 1   | **P0-05** | Chạy checklist trace **Warehouse ↔ Production** hai chiều, chụp màn hình / ghi URL                                                      | QA             | **Pass dev/QA; chờ BA/PM sign-off nếu pilot yêu cầu** | `P0_QA_BA_MASTER_TEST_CASE_TABLE.md` TC-P0-05 + phụ lục; mini UAT: batch #14 ↔ warehouse batch #17; **Dev regression 2026-06-16:** 43 passed |
| 2   | **P0-08** | Chạy mini UAT **3 luồng:** Estimate→SO, SO→DO→Invoice, PO→GRN→Bill                                                                      | QA + BA        | Pass dev/QA; chờ BA/PM sign-off chính thức           | `P0_MINI_UAT_CHECKLIST_BIOMIXING.md` (A–E Pass); **Dev regression 2026-06-16:** 43 passed; Products stock focused regression 2026-06-14: 5 passed |
| 3   | **P0-02** | Gán role thật trên tenant pilot; xác nhận user **không** có `edit_production_orders` thì không duyệt variance; user có quyền thì được   | BA + PM        | Dev evidence pass 2026-06-16; chờ tenant/role thật để sign-off | `P0_QA_BA_MASTER_TEST_CASE_TABLE.md` TC-P0-02 + phụ lục; focused test 2 passed / 7 assertions; biên bản UAT nếu đổi mapping role thật |
| 4   | **P0-07** | Điền **cột UAT** cho WUP-01…07 theo mẫu §2.1.1 trong `04_WH_RUNBOOK_UPGRADE.md`                                                      | QA lead        | Dev/QA evidence cập nhật 2026-06-16; chờ BA/PM sign-off nếu pilot yêu cầu | `04_WH_RUNBOOK_UPGRADE.md` §2.1/§2.1.1; automated readiness 28 passed / 113 assertions |
| 5   | **P0-03** | **Done ở baseline dev/QA (pilot OFF):** giữ `yield_uom_shadow_enabled=false`. **Nếu** PM quyết **bật** shadow: sign-off riêng + bật flag + ghi log tenant | PM + Tech lead | Dev evidence 2026-06-16 xác nhận OFF; chờ PM sign-off nếu muốn bật shadow | `11_SHADOW_YIELD_UOM_PLANNED_ANALYSIS.md` §8; runtime config false; focused tests 1 passed / 5 assertions và 1 passed / 4 assertions |

---

## Lệnh regression (Dev — trước / sau deploy)

```bash
php artisan test --compact tests/Feature/BiomixingDemoRoutesReadinessTest.php tests/Feature/P0BiomixingAutomatedEvidenceTest.php tests/Feature/ProductionPostingServiceTest.php tests/Feature/ProductionFgQuantityPolicyServiceTest.php tests/Feature/ProductionVarianceApprovalPermissionTest.php tests/Feature/WarehouseReconciliationServiceInventorySnapshotTest.php tests/Feature/WarehouseProductBatchRoutesTest.php
```

---

## Liên kết nhanh

| Nội dung         | File                                                              |
| ---------------- | ----------------------------------------------------------------- |
| Trạng thái P0    | File này + `BIOMIXING_GAP_STATUS.md`                           |
| Checklist trace  | `FUNC_IMPROVE/P0_QA_BA_MASTER_TEST_CASE_TABLE.md` TC-P0-05 + phụ lục |
| Mini UAT 3 luồng | `FUNC_IMPROVE/P0_MINI_UAT_CHECKLIST_BIOMIXING.md`              |
| WUP + mẫu UAT    | `FUNC_IMPROVE/04_WH_RUNBOOK_UPGRADE.md` §2.1 + **§2.1.1**      |
| Hub test một cửa | `FUNC_IMPROVE/BIOMIXING_UAT_AND_TEST_GUIDE.md`                 |
