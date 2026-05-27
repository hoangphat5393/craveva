# Production — lỗ hổng quy đổi UOM khi trừ tồn (Post RM consumption)

| Thuộc tính      | Giá trị                                                                                                        |
| --------------- | -------------------------------------------------------------------------------------------------------------- |
| **Mã**          | `P2-UOM-OUTBOUND` / `PROD-UOM-001`                                                                             |
| **Cập nhật**    | 2026-05-20                                                                                                     |
| **Trạng thái**  | **Fixed** (2026-05-20) — `convertToBase` trước allocation/outbound trong `ProductionPostingService`            |
| **Phân loại**   | **Bug triển khai** (thiếu `unit_id` trên outbound), **không** phải «chưa làm BIOMIXING» hay «chỉ thiếu shadow» |
| **Ưu tiên**     | **P0** — rủi ro sai tồn khi BOM ĐVT ≠ đơn vị gốc SP                                                            |
| **Kế hoạch vá** | Đã xóa (plan mode) — xem `LEGACY_ARCHIVE.md`; code vá 2026-05-20                                               |

**Liên quan:** [`P2_PRODUCT_UOM_KIOTVIET_PLAN_VI.md`](./P2_PRODUCT_UOM_KIOTVIET_PLAN_VI.md), [`BIOMIXING_GAP_STATUS_VI.md`](./BIOMIXING_GAP_STATUS_VI.md), [`04_WH_RUNBOOK_UPGRADE_VI.md`](./04_WH_RUNBOOK_UPGRADE_VI.md) (WUP-06), [`DOCUMENTATION_AUDIT_CROSS_FOLDER_2026_05_VI.md`](./DOCUMENTATION_AUDIT_CROSS_FOLDER_2026_05_VI.md)

---

## 1. Tóm tắt điều hành

| Câu hỏi                            | Trả lời                                                                                                                                   |
| ---------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------- |
| P2-UOM đã xong chưa?               | **Code xong:** SP, SO/PO, tổng NL lệnh + **post lô** đều `convertToBase` (2026-05-20). **UAT** Luồng D còn lại.                           |
| Đây là lỗi hay backlog PM?         | **Lỗi** so với chuẩn kho (`StockMovementService`) và yêu cầu PM (quy đổi g/kg/gói). PM backlog UOM vẫn có mục khác (UAT Oldtown, shadow). |
| Shadow `yield_uom_shadow_enabled`? | **Không liên quan** trực tiếp. Shadow chỉ so sánh planned song song; luồng post dùng `planned_quantity` vẫn sai nếu thiếu `unit_id`.      |
| Workaround (trước deploy)          | BOM line cùng ĐVT base; sau deploy có thể post đa ĐVT nếu đã map `product_unit_conversions`.                                              |

---

## 2. Doc-to-code validation

| Hạng mục                                | Tài liệu / kỳ vọng                                                               | Code                                                                     | Khớp?           |
| --------------------------------------- | -------------------------------------------------------------------------------- | ------------------------------------------------------------------------ | --------------- |
| Tồn kho lưu theo **base unit**          | `P2_PRODUCT_UOM_KIOTVIET_PLAN_VI.md` §10; `04_WH_RUNBOOK` WUP-06                 | `warehouse_product_batches.quantity` decimal; product `unit_id` = base   | ✅              |
| Movement quy đổi khi có `unit_id`       | `WarehouseUnitConversionService` + `StockMovementService::convertToBaseQuantity` | Chỉ convert nếu payload có `unit_id`                                     | ✅              |
| Tổng NL lệnh SX (Available / thiếu tồn) | P2 epic **C2**                                                                   | `ProductionOrderMaterialRequirementsSummary` gọi `convertToBase`         | ✅              |
| Planned trên **lô** (hiển thị)          | BOM line UOM (g, kg, pack)                                                       | `production_batch_consumptions.planned_quantity` + `unit_id` từ snapshot | ✅ (hiển thị)   |
| **Post RM consumption**                 | Playbook: trừ qua base                                                           | `postSingleConsumption` → `convertToBase` trước allocation/outbound      | ✅ (2026-05-20) |
| Test regression post RM                 | Case g→kg                                                                        | `ProductionPostingServiceTest` filter `P2-UOM-OUTBOUND`                  | ✅              |

**Nguồn sự thật code (đã vá):**

- `ProductionPostingService::postSingleConsumption()` — `qtyBase = unitConversionService->convertToBase(..., consumption.unit_id)`; allocation/outbound dùng **base**.
- Outbound payload không cần `unit_id` (số đã base — tránh quy đổi đôi).

**Ví dụ đã cover test:** planned **100 g**, base **kg**, factor **0,001** → tồn giảm **0,1**.

---

## 3. Spec reconciliation (PM · plan · code)

| Nguồn                                              | Nội dung                                    | Khớp code post?                                                                                        |
| -------------------------------------------------- | ------------------------------------------- | ------------------------------------------------------------------------------------------------------ |
| `PROJECT BIOMIXING/PM_YEU_CAU_TONG_HOP_VI.md` §B.9 | Chuyển đổi UOM (g, kg, gói…)                | ❌                                                                                                     |
| `P2_PRODUCT_UOM_KIOTVIET_PLAN_VI.md` C2            | Tổng NL lệnh SX `convertToBase`             | ✅ (chỉ màn order)                                                                                     |
| `BIOMIXING_GAP_STATUS_VI.md` (trước 2026-05-20)    | P2-UOM «✅ Code»                            | ⚠️ **Quá optimistic** — đã chỉnh § Còn lại                                                             |
| `11_SHADOW_YIELD_UOM_PLANNED_ANALYSIS_VI.md`       | Post dùng `planned_quantity` chuẩn hiện tại | Đúng tên cột; **không** đảm bảo quy đổi ĐVT lúc post                                                   |
| `FUNC_BUG`                                         | Không có ticket cũ                          | Đã thêm [`../FUNC_BUG/PRODUCTION_RM_OUTBOUND_UOM_VI.md`](../FUNC_BUG/PRODUCTION_RM_OUTBOUND_UOM_VI.md) |

---

## 4. Phạm vi ảnh hưởng

- **Có:** Mọi tenant dùng Production + Warehouse, BOM line `unit_id` ≠ `products.unit_id` (base).
- **Không / ít:** BOM line cùng ĐVT với base (vd 2 kg trên SP kg); số tròn trên UI không chứng minh «chỉ integer».
- **Tách bạch:** Tồn **0** trên Inventory (opening vs warehouse) — xem [`13_OPENING_STOCK_VS_WAREHOUSE_STOCK_VI.md`](./13_OPENING_STOCK_VS_WAREHOUSE_STOCK_VI.md).

---

## 5. Workaround (đến khi vá)

1. Chuẩn hóa `product_unit_conversions` (factor đúng).
2. Trên BOM lệnh SX: nhập định mức **cùng đơn vị gốc** SP (vd 0,1 kg thay vì 100 g) **hoặc** không bấm Post consumption.
3. UAT: không ký P2-UOM Oldtown cho luồng **post lô** cho đến khi test g→kg pass.

---

## 6. Tiêu chí Done

- [x] `convertToBase` trước allocation/outbound (entered qty + `consumption.unit_id`).
- [x] Số trừ trên `warehouse_product_batches` = base unit.
- [x] Test Pest: `posts RM consumption in product base unit when line unit_id differs from base (P2-UOM-OUTBOUND)`.
- [x] Doc + `BIOMIXING_GAP_STATUS` + `FUNC_BUG/PRODUCTION_RM_OUTBOUND_UOM_VI.md` cập nhật Fixed.

---

## 7. Living documentation — ai đọc gì

| Vai trò | File                                                                                                   |
| ------- | ------------------------------------------------------------------------------------------------------ |
| PM / BA | §1–§5 file này; workaround §5                                                                          |
| Dev     | §2 doc-to-code; plan vá đã retire (`LEGACY_ARCHIVE.md`)                                                |
| QA      | Test matrix trong plan; mini UAT `P0_MINI_UAT_CHECKLIST_BIOMIXING_VI.md` mục trừ NL (bổ sung case ĐVT) |

---

_Changelog: 2026-05-20 — tạo từ documentation audit + doc-to-code review (session Production UOM)._
