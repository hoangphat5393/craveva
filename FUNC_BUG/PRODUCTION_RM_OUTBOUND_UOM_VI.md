# Known issue — Production Post RM không quy đổi UOM khi trừ kho

| Thuộc tính     | Giá trị                |
| -------------- | ---------------------- |
| **Mã**         | `PROD-UOM-001`         |
| **Trạng thái** | **Fixed** (2026-05-20) |
| **Ưu tiên**    | **P0**                 |
| **Cập nhật**   | 2026-05-20             |

## Triệu chứng

- Trên lô SX: planned **100 g**; Inventory SP **kg**.
- Sau **Deduct raw materials**: tồn giảm **100** (đơn vị kho) thay vì **~0,1 kg**.
- Màn **tổng NL lệnh SX** có thể hiển thị đúng (đã `convertToBase`) — **lệch** với số thực post.

## Nguyên nhân

`ProductionPostingService::postSingleConsumption` gọi `recordOutbound` **không** truyền `unit_id` → `StockMovementService` không quy đổi.

## Không phải

- Lỗi «chỉ trừ số nguyên» — DB hỗ trợ decimal.
- Thiếu toàn bộ epic BIOMIXING — Production MVP đã có; thiếu **một call-site**.
- Bắt buộc bật shadow UOM.

## Workaround

BOM line cùng ĐVT với `products.unit_id`; không post đến khi vá.

## Spec

- Gap (living doc, Fixed): [`../FUNC_IMPROVE/15_PRODUCTION_OUTBOUND_UOM_GAP_VI.md`](../FUNC_IMPROVE/15_PRODUCTION_OUTBOUND_UOM_GAP_VI.md)

## Đã vá

- `Modules/Production/Services/ProductionPostingService.php` — `convertToBase` trước allocation/outbound.
- Test: `ProductionPostingServiceTest` — `P2-UOM-OUTBOUND` (100 × 0,001 = 0,1 kg).
