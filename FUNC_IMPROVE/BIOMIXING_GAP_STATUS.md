# Biomixing — trạng thái Phase 1 & 2 (đối chiếu code)

**Cập nhật:** 2026-06-16
**Doc hub:** [`BIOMIXING_DOC_HUB.md`](./BIOMIXING_DOC_HUB.md)  
**UOM post lô:** `PRODUCTION_BUSINESS.md` §3 — **Fixed 2026-05-20**
**Nguồn yêu cầu gốc:** `PROJECT BIOMIXING/PM_YEU_CAU_TONG_HOP.md`
**Multi-tenant / rủi ro B2B vs Production:** [`BIOMIXING_MULTITENANT_RISKS.md`](./BIOMIXING_MULTITENANT_RISKS.md)

---

## Thư mục `PROJECT BIOMIXING/` (27 file — vai trò)

| Nhóm             | File                                                            | Mục đích             |
| ---------------- | --------------------------------------------------------------- | -------------------- |
| Yêu cầu PM       | `PM_YEU_CAU_TONG_HOP.md`                                     |
| Kế hoạch / demo  | `BIOMIXING_FULL_DEMO_RUNBOOK.md`, `BIOMIXING_DEMO_SCRIPT.md` |
| Kỹ thuật / sơ đồ | `*.mmd`, `DIAGRAM_*`, `FLOW_*`                                  | Sequence, ERD, luồng |
| Vận hành         | `RUNBOOK_*`, `ENV_*`                                            | Triển khai, hostname |

**Triển khai thực tế** nằm ở `app/`, `Modules/Production/`, `FUNC_IMPROVE/`, không nằm hết trong folder BIOMIXING.

---

## Phase 1 — Báo giá / Quotation (OEM)

**Phạm vi:** Module Estimate hiện có + cờ tenant `estimates_phase1_review`.

### Tóm tắt PM / vận hành

Với tenant gia công như Biomixing, Sales làm báo giá trên Estimate, nhập dòng bán + công thức/BOM báo giá, gửi duyệt nội bộ, rồi chỉ convert Sales Order khi đủ điều kiện.

```text
Estimate / Quotation
  -> BOM báo giá + dòng bán
  -> President approve
  -> VP Pricing approve
  -> Convert Sales Order
  -> Production sau SO
```

| Phần việc | Mức độ hiện tại | Ghi chú |
| --------- | --------------- | ------- |
| Báo giá thường (tạo, gửi, SO) | ~95% | Đủ cho tenant chỉ bán hàng |
| Duyệt President + VP Pricing | ~85% | Có submit/reject/approve, chặn SO, notification, quyền riêng |
| Công thức / nguyên liệu trên báo giá | ~85% | BOM lines, copy Production BOM, similar recipe, margin |
| Workspace 4 vùng | ~75% | Detail page đã có workspace OEM; list/badge còn polish |

**Không làm trong Phase 1:** AI tự duyệt công thức, đổi tên menu Quotation toàn hệ thống, hoặc bỏ qua SO để sang Production.

**Cấu hình nhanh:** bật module setting `estimates_phase1_review` cho tenant gia công; cấu hình margin tại Finance / Invoice settings; gán quyền President / VP trong Roles & Permissions.

### Đã xong (~95% — đủ đóng phase)

| Hạng mục                              | Ghi chú                                        |
| ------------------------------------- | ---------------------------------------------- |
| BOM trên báo giá                      | `estimate_bom_lines`, partial create/edit/show |
| Gửi duyệt President / VP              | Routes + quyền `approve_estimate_*`            |
| Chặn SO khi chưa duyệt                | `Estimate::isCommercialConversionAllowed()`    |
| Revision / VP margin                  | `revision_required`, `EstimateVpMarginPolicy`  |
| Timeline, thông báo, workspace 4 vùng | Events, notifier, `phase1-show-workspace`      |
| Copy BOM Production → báo giá         | `EstimateProductionBomCopier`                  |
| Công thức tương tự                    | `EstimateSimilarRecipeSearch`                  |
| Bật/tắt theo công ty                  | Module Settings `estimates_phase1_review`      |

### Vừa bổ sung (2026-05-20)

| Hạng mục       | Ghi chú                                                        |
| -------------- | -------------------------------------------------------------- |
| **PDF có BOM** | Done — `estimates/partials/pdf-bom-lines` đã include trên 5 template PDF; regression khóa template |

### Còn tùy chọn (không chặn đóng Phase 1)

- Email template riêng cho từng bước duyệt (hiện dùng notification chung).
- Mở rộng form Estimate Request (intake) theo checklist PM.
- Snapshot BOM vào PDF tại thời điểm President approve (hiện PDF = dữ liệu hiện tại).

---

## Phase 2 — Sản xuất (Production)

**Phạm vi:** `Modules/Production` — BOM, lệnh SX, lô, trừ NL / nhập TP.

### Đã có sẵn (MVP ~75%)

| Hạng mục                       | Ghi chú                                          |
| ------------------------------ | ------------------------------------------------ |
| BOM CRUD, tách FG vs component | `Product::forBomOutput()` / `forBomComponents()` |
| Lệnh SX, snapshot khi release  | `ProductionPostingService`                       |
| Lô, planned RM, post NL/TP     | Batch screens                                    |
| Liên kết SO                    | `sales_order_id`, validation trạng thái SO       |
| Rework, trace cơ bản           | Có route & test                                  |

### Vừa bổ sung (2026-05-20)

| ID   | Hạng mục                                                                                            |
| ---- | --------------------------------------------------------------------------------------------------- |
| P0-3 | Bảng **tổng nguyên liệu** = SL kế hoạch × BOM trên chi tiết lệnh SX + cảnh báo thiếu tồn kho kho NL |
| P1-1 | Nút **Tạo lệnh sản xuất** từ màn hình Sales Order (prefill `sales_order_id`)                        |

### Bổ sung theo thứ tự nghiệp vụ (2026-05-20)

| ID   | Hạng mục                                                                   |
| ---- | -------------------------------------------------------------------------- |
| P0-4 | Checklist lô hiện tại **4 bước** vì planned RM tự sinh từ BOM snapshot khi Release / mở batch |
| P0-5 | Nhãn VI mới (workflow, hao hụt, gợi ý mua)                                 |
| P1-2 | Link **Tạo đơn đặt hàng** khi thiếu tồn (module Purchase)                  |
| P1-3 | Cột **% hao hụt** trên BOM + vào công thức tổng NL                         |
| P1-4 | Prefill lệnh SX từ SO (TP, SL, BOM) + gợi ý từ báo giá liên kết            |

### Bổ sung P1c (2026-05-23)

| ID  | Hạng mục                                                                                                                            |
| --- | ----------------------------------------------------------------------------------------------------------------------------------- |
| P1c | **Post FG → Purchase Inventory ledger** — `PRODUCTION_BUSINESS.md` §3; backfill `production:backfill-fg-inventory-ledger` |

### Bổ sung P1d (2026-06-14)

| ID  | Hạng mục                                                                                                                                                                  |
| --- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| P1d | **Products listing stock-on-hand display** — `PurchaseProductsDataTable` hiển thị ledger qty cho tracked/non-tracked; focused regression `PurchaseProductsDataTableTest` **5 passed / 12 assertions** |

### Regression mới nhất (2026-06-16)

| Nhóm | Kết quả |
| ---- | ------- |
| P0 Biomixing bundle | `php artisan test --compact tests/Feature/BiomixingDemoRoutesReadinessTest.php tests/Feature/P0BiomixingAutomatedEvidenceTest.php tests/Feature/ProductionPostingServiceTest.php tests/Feature/ProductionFgQuantityPolicyServiceTest.php tests/Feature/ProductionVarianceApprovalPermissionTest.php tests/Feature/WarehouseReconciliationServiceInventorySnapshotTest.php tests/Feature/WarehouseProductBatchRoutesTest.php` → **43 passed / 161 assertions** |

### Còn lại (Phase 2+ / UAT)

| ID              | Hạng mục                                                                                                                                                             |
| --------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| P2-1 / P2-UOM   | **✅ Code** — A/B/C + post lô `convertToBase` (2026-05-20). **UAT:** Oldtown + Luồng D. [`P2_PRODUCT_UOM_KIOTVIET_PLAN.md`](./P2_PRODUCT_UOM_KIOTVIET_PLAN.md) |
| P2-UOM-OUTBOUND | **✅ Fixed 2026-05-20** — `PRODUCTION_BUSINESS.md` §3 · `FUNC_BUG/BUG_PRODUCTION_UOM.md`                                                        |
| P2-SKU          | **✅ 2026-05-21** — SKU tự động khi tạo SP (Purchase)                                                                                                                |
| P0-02           | Variance approval UAT — badge UX **Done** (UX-008); xem `BIOMIXING_BUSINESS_FLOW_LIVE.md` §3.2                                                                    |
| P0-05 / P0-08   | UAT trace + mini UAT A–E — **Pass dev/QA; chưa ký BA/PM** (P0 bundle 43 passed ngày 2026-06-16; Products stock focused regression 5 passed ngày 2026-06-14; xem `P0_QA_BA_MASTER_TEST_CASE_TABLE.md`, `P0_BIOMIXING_NEXT_STEPS.md`, `P0_MINI_UAT_CHECKLIST_BIOMIXING.md`) |
| P2+             | Phiên bản BOM V2; CCP/QA phase 3+                                                                                                                                    |
| —               | Email/Estimate Request Phase 1 (tùy chọn)                                                                                                                            |

### Guardrails kỹ thuật đã gộp từ playbook P0/P1

**Nguồn sự thật hiện tại:** `BIOMIXING_BUSINESS_FLOW_LIVE.md` cho luồng nghiệp vụ, `FUNC_LOGIC/PRODUCTION_BUSINESS.md` cho vận hành Production, file này cho trạng thái phase/gap.

| Mảng | Quyết định hiện tại |
| ---- | ------------------- |
| BOM + snapshot | Done MVP; release chốt `production_order_bom_snapshot_items`. |
| Lifecycle | `draft -> released -> in_progress -> completed`; `cancelled` chỉ khi chưa có movement chặn. |
| Reserve RM | Done tại Release, không reserve ở Draft; reserve tiêu khi post RM. |
| Multi-batch planned RM | Done MVP theo equal-split theo số batch; per-batch planned qty riêng là P2+. |
| Post RM / FG | Dùng `StockMovementService`; UOM convert nằm ở `PRODUCTION_BUSINESS.md` §3. |
| Trace | Production ↔ Warehouse dùng reference riêng; không dùng reference PO/GRN/DO để tránh nhập/xuất đôi. |
| FG variance | Dùng quyền `edit_production_orders`; quyền approve riêng chỉ mở nếu BA/PM sign-off. |
| SO/DO/Invoice | Phase 1 chỉ gate Estimate -> SO; không đổi state machine DO/Invoice B2B nền. |
| Tenant module | Kiểm tra `packages:modules` + `module_settings`, không chỉ `module:enable`. |

**Thứ tự migration nếu mở rộng Production:** `production_boms` / `production_bom_items` -> `production_orders` (`sales_order_id`, `project_id`) -> `production_batches` / consumptions / outputs -> `production_order_bom_snapshot_items` -> `production_company_fg_policies`.

**P2+ không nằm trong scope hiện tại:** reverse movement sau post, planned qty riêng từng batch, quyền riêng chỉ approve variance, BOM V2/version nâng cao, CCP/HACCP/QA lab sâu.

Chi tiết kỹ thuật: `FUNC_LOGIC/PRODUCTION_BUSINESS.md`, `PROJECT BIOMIXING/UI_RUNBOOK_PHASE2_*`.

---

## Cách kiểm tra nhanh

```powershell
.\scripts\test.ps1 phase1
php artisan test --compact tests/Unit/ProductionOrderMaterialRequirementsSummaryTest.php
```

**Demo:** Bật module _Duyệt báo giá gia công_ → báo giá có BOM → PDF → duyệt → SO → _Tạo lệnh sản xuất_ → xem bảng tổng NL trên lệnh SX.

**Demo P2-UOM + SKU:** Purchase → Tạo SP (để trống SKU → tự sinh) → thêm đơn vị phụ → SO chọn UOM trên dòng → kiểm giá.
