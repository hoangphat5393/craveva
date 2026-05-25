# Biomixing — trạng thái Phase 1 & 2 (đối chiếu code)

**Cập nhật:** 2026-05-24  
**Doc sync manifest:** [`BIOMIXING_DOCUMENTATION_SYNC_2026_05_VI.md`](./BIOMIXING_DOCUMENTATION_SYNC_2026_05_VI.md)  
**Audit quy trình theo phase:** [`BIOMIXING_FULL_PROCESS_AUDIT_2026_05_VI.md`](./BIOMIXING_FULL_PROCESS_AUDIT_2026_05_VI.md)  
**Audit tài liệu:** [`DOCUMENTATION_AUDIT_CROSS_FOLDER_2026_05_VI.md`](./DOCUMENTATION_AUDIT_CROSS_FOLDER_2026_05_VI.md)  
**UOM post lô:** [`15_PRODUCTION_OUTBOUND_UOM_GAP_VI.md`](./15_PRODUCTION_OUTBOUND_UOM_GAP_VI.md) — **Fixed 2026-05-20**
**Nguồn yêu cầu gốc:** `PROJECT BIOMIXING/PM_YEU_CAU_TONG_HOP_VI.md` (gộp từ PM_REQUEST, PM REQUEST CHAT, RTF).  
**Multi-tenant / rủi ro B2B vs Production:** [`BIOMIXING_MULTITENANT_RISKS_VI.md`](./BIOMIXING_MULTITENANT_RISKS_VI.md)

---

## Thư mục `PROJECT BIOMIXING/` (27 file — vai trò)

| Nhóm             | File                                                                                                     | Mục đích                                         |
| ---------------- | -------------------------------------------------------------------------------------------------------- | ------------------------------------------------ |
| Yêu cầu PM       | `PM_YEU_CAU_TONG_HOP_VI.md`, `PM_REQUEST_VI.md`, `PM_REQUEST.md`, `PM REQUEST CHAT.md`, `PM request.rtf` | Luồng Oldtown, duyệt President/VP, BOM, sản xuất |
| Kế hoạch / demo  | `2-4-2026_BIOMIXIN_DEMO_PREP_CHECKLIST.md`, `BIOMIXING_*_CHECKLIST*.md`                                  | Checklist demo & UAT                             |
| Kỹ thuật / sơ đồ | `*.mmd`, `DIAGRAM_*`, `FLOW_*`                                                                           | Sequence, ERD, luồng                             |
| Vận hành         | `RUNBOOK_*`, `ENV_*`                                                                                     | Triển khai, hostname                             |

**Triển khai thực tế** nằm ở `app/`, `Modules/Production/`, `FUNC_IMPROVE/`, không nằm hết trong folder BIOMIXING.

---

## Phase 1 — Báo giá / Quotation (OEM)

**Phạm vi:** Module Estimate hiện có + cờ tenant `estimates_phase1_review`.

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
| **PDF có BOM** | Partial `estimates/partials/pdf-bom-lines` trên 5 template PDF |

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
| P0-4 | Checklist 5 bước hoàn thành lô (sinh NL → gán lô → trừ NL → TP → nhập kho) |
| P0-5 | Nhãn VI mới (workflow, hao hụt, gợi ý mua)                                 |
| P1-2 | Link **Tạo đơn đặt hàng** khi thiếu tồn (module Purchase)                  |
| P1-3 | Cột **% hao hụt** trên BOM + vào công thức tổng NL                         |
| P1-4 | Prefill lệnh SX từ SO (TP, SL, BOM) + gợi ý từ báo giá liên kết            |

### Bổ sung P1c (2026-05-23)

| ID  | Hạng mục                                                                                                                                                                                       |
| --- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| P1c | **Post FG → Purchase Inventory ledger** — [`16_PRODUCTION_FG_INVENTORY_LEDGER_SYNC_VI.md`](./16_PRODUCTION_FG_INVENTORY_LEDGER_SYNC_VI.md); backfill `production:backfill-fg-inventory-ledger` |

### Còn lại (Phase 2+ / UAT)

| ID              | Hạng mục                                                                                                                                                             |
| --------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| P2-1 / P2-UOM   | **✅ Code** — A/B/C + post lô `convertToBase` (2026-05-20). **UAT:** Oldtown + Luồng D. [`P2_PRODUCT_UOM_KIOTVIET_PLAN_VI.md`](./P2_PRODUCT_UOM_KIOTVIET_PLAN_VI.md) |
| P2-UOM-OUTBOUND | **✅ Fixed 2026-05-20** — [`15_PRODUCTION_OUTBOUND_UOM_GAP_VI.md`](./15_PRODUCTION_OUTBOUND_UOM_GAP_VI.md)                                                           |
| P2-SKU          | **✅ 2026-05-21** — SKU tự động khi tạo SP (Purchase)                                                                                                                |
| P0-02           | Variance approval UAT — badge UX **Done** (UX-008); xem `BIOMIXING_BUSINESS_FLOW_LIVE_VI.md` §3.2                                                                 |
| P0-05 / P0-08   | UAT trace + mini UAT A–D — **chưa ký** (xem [`BIOMIXING_FULL_PROCESS_AUDIT_2026_05_VI.md`](./BIOMIXING_FULL_PROCESS_AUDIT_2026_05_VI.md))                            |
| P2+             | Phiên bản BOM V2; CCP/QA phase 3+                                                                                                                                    |
| —               | Email/Estimate Request Phase 1 (tùy chọn)                                                                                                                            |

Chi tiết kỹ thuật: `FUNC_IMPROVE/PHASE2_PM_PLAN_VI.md`, `FUNC_IMPROVE/BIOMIXING_PLAYBOOK_P0P1_VI.md`.

---

## Cách kiểm tra nhanh

```powershell
.\scripts\test.ps1 phase1
php artisan test --compact tests/Unit/ProductionOrderMaterialRequirementsSummaryTest.php
```

**Demo:** Bật module _Duyệt báo giá gia công_ → báo giá có BOM → PDF → duyệt → SO → _Tạo lệnh sản xuất_ → xem bảng tổng NL trên lệnh SX.

**Demo P2-UOM + SKU:** Purchase → Tạo SP (để trống SKU → tự sinh) → thêm đơn vị phụ → SO chọn UOM trên dòng → kiểm giá.
