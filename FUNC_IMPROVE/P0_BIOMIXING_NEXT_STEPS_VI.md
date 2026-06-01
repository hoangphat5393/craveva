# Biomixing P0 — Các bước tiếp theo (hàng đợi)

**Cập nhật:** 2026-05-09  
**Mục đích:** Sau khi **P0-01 Done** và phần lớn code P0-04/05/06 xong, các việc còn lại chủ yếu là **QA / BA / PM** (UAT, biên bản, sign-off). Cập nhật bảng dưới khi từng mục hoàn tất.

**Bảng test case một lượt (ưu tiên cho QA/BA):** `FUNC_IMPROVE/P0_QA_BA_MASTER_TEST_CASE_TABLE_VI.md`

**Dev đã bổ sung (2026-05-09):** Pest `tests/Feature/P0BiomixingAutomatedEvidenceTest.php` — wiring trace ↔ warehouse batch + smoke tên route cho mini UAT 3 luồng (không thay biên bản QA).

---

## Thứ tự đề xuất (1 → 5)

| #   | ID        | Việc                                                                                                                                    | Owner          | Đầu ra (evidence)                                                                                                  |
| --- | --------- | --------------------------------------------------------------------------------------------------------------------------------------- | -------------- | ------------------------------------------------------------------------------------------------------------------ |
| 1   | **P0-05** | Chạy checklist trace **Warehouse ↔ Production** hai chiều, chụp màn hình / ghi URL                                                      | QA             | Checklist VI/EN + log; **Dev:** `P0BiomixingAutomatedEvidenceTest.php` (wiring Blade)                              |
| 2   | **P0-08** | Chạy mini UAT **3 luồng:** Estimate→SO, SO→DO→Invoice, PO→GRN→Bill                                                                      | QA + BA        | `P0_MINI_UAT_CHECKLIST_BIOMIXING_VI.md` (Pass/Fail); **Dev:** `P0BiomixingAutomatedEvidenceTest.php` (route names) |
| 3   | **P0-02** | Gán role thật trên tenant pilot; xác nhận user **không** có `edit_production_orders` thì không duyệt variance; user có quyền thì được   | BA + PM        | Biên bản UAT + sign-off; cập nhật `P0_VARIANCE_APPROVAL_ROLE_MATRIX_VI.md` nếu đổi mapping                         |
| 4   | **P0-07** | Điền **cột UAT** cho WUP-01…07 theo mẫu §2.1.1 trong `04_WH_RUNBOOK_UPGRADE_VI.md`                                                      | QA lead        | Bảng có ngày / tester / link biên bản                                                                              |
| 5   | **P0-03** | **Done (pilot OFF):** giữ `yield_uom_shadow_enabled=false`. **Nếu** PM quyết **bật** shadow: sign-off riêng + bật flag + ghi log tenant | PM + Tech lead | Log P0-03 (2026-05-09); bật ON = cần sign-off + deploy                                                             |

---

## Lệnh regression (Dev — trước / sau deploy)

```bash
php artisan test --compact tests/Feature/BiomixingDemoRoutesReadinessTest.php tests/Feature/P0BiomixingAutomatedEvidenceTest.php tests/Feature/ProductionPostingServiceTest.php tests/Feature/ProductionFgQuantityPolicyServiceTest.php tests/Feature/ProductionVarianceApprovalPermissionTest.php tests/Feature/WarehouseReconciliationServiceInventorySnapshotTest.php tests/Feature/WarehouseProductBatchRoutesTest.php
```

---

## Liên kết nhanh

| Nội dung         | File                                                              |
| ---------------- | ----------------------------------------------------------------- |
| Trạng thái P0    | File này + `BIOMIXING_GAP_STATUS_VI.md`                           |
| Checklist trace  | `FUNC_IMPROVE/P0_05_TRACE_BIDIRECTIONAL_UAT_CHECKLIST.md` (VI+EN) |
| Mini UAT 3 luồng | `FUNC_IMPROVE/P0_MINI_UAT_CHECKLIST_BIOMIXING_VI.md`              |
| WUP + mẫu UAT    | `FUNC_IMPROVE/04_WH_RUNBOOK_UPGRADE_VI.md` §2.1 + **§2.1.1**      |
| Hub test một cửa | `FUNC_IMPROVE/BIOMIXING_UAT_AND_TEST_GUIDE_VI.md`                 |
