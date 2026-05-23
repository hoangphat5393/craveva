# Kế hoạch vá lỗi — Production outbound UOM (`P2-UOM-OUTBOUND`)

| Thuộc tính     | Giá trị                                                                          |
| -------------- | -------------------------------------------------------------------------------- |
| **Trạng thái** | **Done** (2026-05-20)                                                            |
| **Phụ thuộc**  | `product_unit_conversions` đã có map (P2-UOM A)                                  |
| **Spec**       | [`15_PRODUCTION_OUTBOUND_UOM_GAP_VI.md`](./15_PRODUCTION_OUTBOUND_UOM_GAP_VI.md) |
| **Ước lượng**  | **0,5–1 sprint-day** (code + test + doc tick)                                    |

---

## 1. Mục tiêu

Khi **Post RM consumption** / **Deduct raw materials**, số lượng gửi `StockMovementService::recordOutbound` phải là **số lượng đã quy về đơn vị gốc SP** (`products.unit_id`), giống GRN/SO/AI order và giống `ProductionOrderMaterialRequirementsSummary`.

---

## 2. Phạm vi

| Trong scope                                                   | Ngoài scope                      |
| ------------------------------------------------------------- | -------------------------------- |
| `ProductionPostingService::postSingleConsumption`             | Bật `yield_uom_shadow_enabled`   |
| Payload `unit_id` + (tuỳ chọn) assert strict conversion       | Đổi công thức planned trên UI lô |
| Test feature g→kg + regression `ProductionPostingServiceTest` | GRN line UOM (đã có luồng PO)    |
| Doc tick + `BIOMIXING_GAP_STATUS`                             | Refactor toàn module Production  |

---

## 3. Thiết kế kỹ thuật

### 3.1 Luồng đích

```text
ProductionBatchConsumption
  → planned_quantity / actual_quantity (theo BOM line UOM)
  → unit_id từ consumption (snapshot / BOM item)
  → WarehouseUnitConversionService::convertToBase(company, product, qty, unit_id)
  → recordOutbound({ quantity: qtyBase, unit_id: null hoặc base })
```

**Ghi chú:** Sau `convertToBase`, payload outbound có thể **không** cần `unit_id` (số đã là base) — khớp pattern caller khác; hoặc truyền `unit_id` = base product để audit. Chọn một convention và đồng nhất với `StockMovementService` (đọc caller GRN/Sales DO trước khi code).

### 3.2 Allocation theo lô

`resolveWarehouseBatchAllocationsForConsumption` hiện chia `requiredQty` **trước** convert — **phải** convert **trước** khi so với `warehouse_product_batches.quantity` (đã base).

Thứ tự đề xuất:

1. `$qtyEntered = actual ?? planned`
2. `$qtyBase = convertToBase(..., $consumption->unit_id)` (null unit_id → coi là base, giữ số)
3. Allocation + outbound dùng `$qtyBase`

### 3.3 Strict conversion

Nếu `warehouse.strict_unit_conversion` = true và thiếu map → `WarehouseBusinessException` (giống movement khác), không âm thầm trừ sai.

### 3.4 Hiển thị (tùy chọn P1 doc)

- Cột «Số trừ kho (base)» trên batch show — **không bắt buộc** cho MVP fix.

---

## 4. Các bước triển khai

| #   | Việc                                                                                                        | File / artifact                                                                  |
| --- | ----------------------------------------------------------------------------------------------------------- | -------------------------------------------------------------------------------- |
| 1   | Đọc caller mẫu có `unit_id` (Sales DO, GRN)                                                                 | `Modules/Warehouse/Services/*`                                                   |
| 2   | Inject `WarehouseUnitConversionService` vào `ProductionPostingService` (nếu chưa)                           | `ProductionPostingService.php`                                                   |
| 3   | Convert qty trước allocation + outbound                                                                     | `postSingleConsumption`, có thể `resolveWarehouseBatchAllocationsForConsumption` |
| 4   | Đảm bảo `ProductionBatchConsumption` luôn có `unit_id` khi tạo từ snapshot                                  | `ProductionPlannedConsumptionFromSnapshotService` (verify)                       |
| 5   | Test mới: g / kg / factor 0,001                                                                             | `tests/Feature/ProductionPostingServiceTest.php`                                 |
| 6   | Cập nhật test happy path nếu cần fixture `unit_id`                                                          | Cùng file                                                                        |
| 7   | `vendor/bin/pint --dirty`                                                                                   | —                                                                                |
| 8   | Doc: tick `15_*_GAP` §6, `BIOMIXING_GAP_STATUS`, `P2` §16, đóng `FUNC_BUG/PRODUCTION_RM_OUTBOUND_UOM_VI.md` | FUNC_IMPROVE / FUNC_BUG                                                          |

---

## 5. Test plan

| ID  | Case                                                 | Kỳ vọng                   |
| --- | ---------------------------------------------------- | ------------------------- |
| T1  | BOM 0,05 kg/FG, base kg, post 50                     | Batch RM −50 (kg)         |
| T2  | BOM 100 g/FG, factor 0,001, base kg, post 100 g line | Batch RM −0,1             |
| T3  | Strict on, thiếu map                                 | Exception, không movement |
| T4  | Post 2 lần                                           | Idempotency như hiện tại  |
| T5  | Regression phase1 / `ProductionPostingServiceTest`   | Pass                      |

```powershell
php artisan test --compact tests/Feature/ProductionPostingServiceTest.php
php artisan test --compact tests/Feature/ProductionOrderMaterialRequirementsSummaryTest.php
```

---

## 6. Rủi ro & rollback

| Rủi ro                     | Giảm thiểu                                                                                 |
| -------------------------- | ------------------------------------------------------------------------------------------ |
| Lô đã post sai trước fix   | Không auto-reverse; audit movement + điều chỉnh thủ công (ghi runbook incident nếu xảy ra) |
| Consumption `unit_id` null | Coi entered qty = base (giữ hành vi cũ cho tenant chỉ dùng base)                           |

Rollback: revert PR; không cần migration.

---

## 7. Definition of Done

- [x] Code + tests §5 pass (`ProductionPostingServiceTest` filter `P2-UOM-OUTBOUND` + full file 17 tests)
- [x] `15_PRODUCTION_OUTBOUND_UOM_GAP_VI.md` — **Fixed**
- [x] `BIOMIXING_GAP_STATUS_VI.md` — P2-UOM-OUTBOUND ✅
- [ ] PM UAT Luồng D (`P0_MINI_UAT_CHECKLIST_BIOMIXING_VI.md`) sau deploy

---

_Implemented 2026-05-20: `ProductionPostingService::postSingleConsumption` uses `WarehouseUnitConversionService::convertToBase` before batch allocation and outbound quantities (already in base; no double conversion in `recordOutbound`)._
