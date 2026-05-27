# Production module — Audit đồng bộ (VI)

_Cập nhật sau BOM-first + auto planned RM trên batch. Dùng khi review PM/QA hoặc trước UAT._

## 1) Luồng chuẩn hiện tại (tóm tắt)

| Giai đoạn  | User thấy gì                                      | DB / service                                                |
| ---------- | ------------------------------------------------- | ----------------------------------------------------------- |
| BOM master | `/production/boms`                                | `production_boms` + items                                   |
| Lệnh draft | BOM **bắt buộc**, FG readonly, preview AJAX       | `production_orders.production_bom_id`                       |
| Release    | Reserve RM, snapshot, tạo batch đầu               | `production_order_bom_snapshot_items`, `stock_reservations` |
| Mở batch   | Checklist **1–4** (không còn step “sinh planned”) | Auto `production_batch_consumptions`                        |
| Batch      | Gán lô → Deduct → FG → Post FG                    | movements, consume reserve                                  |

**Config SSOT:** `Modules/Production/Config/config.php` → `production.ui.*`

**Phục hồi Step 1 thủ công:** `FUNC_LOGIC/PRODUCTION_BATCH_STEP1_RESTORE_VI.md`

---

## 2) Đã sửa trong đợt audit này

| Vấn đề                                             | Sửa                                                                                                        |
| -------------------------------------------------- | ---------------------------------------------------------------------------------------------------------- |
| Checklist batch vẫn hiện **2–5** sau khi bỏ step 1 | `ProductionBatchWorkflowSteps` thêm `display_label` đánh số **1..n** động                                  |
| Help text vẫn “plan raw materials” đầu tiên        | `batchCompletionWorkflowHelp` (auto) vs `HelpManual` (khi bật lại step 1)                                  |
| Lang EN/VI step labels cứng số 1–5                 | Bỏ số trong key; số do UI gắn                                                                              |
| Thiếu ghi chú auto trên màn RM                     | `batchRmAutoAppliedNote` khi auto + ẩn nút magic                                                           |
| Doc Biomixing §3.1 vẫn 5 bước + nút snapshot       | Bảng 4 bước + dòng legacy                                                                                  |
| **Nút Deduct (trừ NL) biến mất** khi tắt manual RM | Tách `@if` trong `batches/show.blade.php`: Deduct **không** gộp với `allow_manual_batch_consumption_lines` |
| Hiện key `production::app.batchRmAutoAppliedNote`  | Sync string vào `Modules/Production/Resources/lang/{en,vi}/app.php` (file module load trước LanguagePack)  |

---

## 3) Checklist file quan trọng (không lệch)

| Khu vực                | File                                                                                |
| ---------------------- | ----------------------------------------------------------------------------------- |
| Policy BOM-first       | `Support/ProductionBomFirstPolicy.php`                                              |
| Policy batch step 1    | `Support/ProductionBatchPlannedLinesPolicy.php`                                     |
| Auto planned lines     | `Services/ProductionBatchPlannedLinesApplicator.php`                                |
| Snapshot → batch lines | `Services/ProductionPlannedConsumptionFromSnapshotService.php`                      |
| Checklist số thứ tự    | `Support/ProductionBatchWorkflowSteps.php`                                          |
| Batch UI               | `Resources/views/batches/show.blade.php`, `partials/completion-workflow.blade.php`  |
| Order form             | `orders/partials/order-bom-header-fields.blade.php`, `bom-preview-script.blade.php` |
| Validation BOM         | `Http/Requests/Concerns/ValidatesProductionOrderBomPolicy.php`                      |
| Lang                   | `Modules/LanguagePack/.../Production/{en,vi}/app.php`                               |
| Vận hành               | `FUNC_LOGIC/PRODUCTION_OPERATIONS_LIVE_VI.md` §7                                    |
| SOP                    | `PROJECT BIOMIXING/PRODUCTION_MODULE_SOP_EN.md` §8                                  |

---

## 4) Các điểm dễ lệch (cần nhớ khi đổi code)

1. **Hai snapshot khác nhau:** preview trên form lệnh = BOM **master**; dòng batch = snapshot **trên lệnh** lúc release.
2. **Nhiều batch / lệnh:** planned qty chia đều `bom_snapshot_planned_quantity ÷ số batch`.
3. **Ẩn nút ≠ xóa route:** `apply-planned-from-bom-snapshot` vẫn tồn tại khi restore config.
4. **Test posting cũ:** `ProductionPostingServiceTest` set `bom_first_workflow_enabled = false` trong `beforeEach`.
5. **Material shortage:** scope status có thể khác doc tóm tắt — đọc `ProductionMaterialSummaryService::statusesForScope()`.
6. **Đổi config** trên server: `php artisan config:clear` nếu cache config.

---

## 5) Tài liệu khách hàng — loại sản phẩm (2026-05-27)

| File                                                                                                          | Nội dung                                                                |
| ------------------------------------------------------------------------------------------------------------- | ----------------------------------------------------------------------- |
| [`PRODUCTION_PRODUCT_TYPES_VI.md`](./PRODUCTION_PRODUCT_TYPES_VI.md)                                          | SSOT: `goods` vs `raw_material` / `packaging` / `semi_finished` cho BOM |
| [`PRODUCTION_PRODUCT_TYPES_EN.md`](./PRODUCTION_PRODUCT_TYPES_EN.md)                                          | Bản EN                                                                  |
| [`PROJECT BIOMIXING/PRODUCTION_MODULE_SOP_VI.md`](../PROJECT%20BIOMIXING/PRODUCTION_MODULE_SOP_VI.md)         | SOP mục **0–2** mở rộng                                                 |
| [`FUNC_IMPROVE/PRODUCT_TYPE_BUYER_VS_INVENTORY_VI.md`](../FUNC_IMPROVE/PRODUCT_TYPE_BUYER_VS_INVENTORY_VI.md) | Mua hàng vs tồn (bổ sung)                                               |

Khi đổi `ProductType` / scope BOM → cập nhật các file trên + `StoreProductionBomRequest` validation.

---

## 6) Test nên chạy sau thay đổi Production

```bash
php artisan test --compact tests/Feature/ProductionOrderBomFirstWorkflowTest.php
php artisan test --compact tests/Feature/ProductionPostingServiceTest.php
php artisan test --compact --filter=ProductionOrder
```

---

## 7) Changelog audit

| Ngày       | Ghi chú                                                         |
| ---------- | --------------------------------------------------------------- |
| 2026-05-27 | Bổ sung doc loại SP / SOP mục 0–2                               |
| 2026-05    | Đánh số checklist 1–4; help/lang; doc Biomixing; file audit này |
