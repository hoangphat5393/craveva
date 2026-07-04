# Warehouse Master Guide

**Doc hub:** Nghiệp vụ & luồng -> [`WAREHOUSE_BUSINESS.md`](WAREHOUSE_BUSINESS.md) · Runbook & WUP -> [`../FUNC_IMPROVE/04_WH_RUNBOOK_UPGRADE.md`](../FUNC_IMPROVE/04_WH_RUNBOOK_UPGRADE.md) · Trạng thái PM/QA -> [`MODULE_WAREHOUSE.md`](MODULE_WAREHOUSE.md) · UAT E2E -> [`SALES_PURCHASE_WAREHOUSE_UAT_CHECKLIST.md`](SALES_PURCHASE_WAREHOUSE_UAT_CHECKLIST.md).

Tài liệu kỹ thuật tổng hợp cho phạm vi Warehouse, thay cho các file audit/plan cũ đã retire.

---

## 1. Mục Tiêu Và Phạm Vi

- Chuẩn hóa nghiệp vụ đa kho theo hướng Purchase-centric.
- Dùng `StockMovementService` làm cổng ghi sổ tồn kho duy nhất cho inbound/outbound/transfer.
- Giữ tồn kho vật lý nhất quán giữa movement ledger, batch stock và tổng tồn theo kho.

---

## 2. Kiến Trúc Luồng Kho

```text
Master data (Product, Client, Vendor)
        |
        v
Warehouse master
        |
PO / GRN / Inventory / Transfer / Sales DO
        |
        v
StockMovementService
        |
+-------------------------------+
| warehouse_product_batches     |
| warehouse_product_stock       |
| stock_movements               |
+-------------------------------+
```

Nguyên tắc:

- Add/Remove stock: append movement mới, không ghi đè movement cũ.
- Transfer: tạo cặp outbound (kho nguồn) + inbound (kho đích) trong 1 transaction.
- Lỗi 1 bước -> rollback toàn bộ.

---

## 3. Ghi Chú DB Quan Trọng

### Add Stock Ghi Vào

1. `warehouse_product_batches`
2. `warehouse_product_stock`
3. `stock_movements` (1 dòng inbound/outbound)

### Transfer Stock Ghi Vào

1. `warehouse_product_batches` kho nguồn (giảm)
2. `warehouse_product_batches` kho đích (tăng)
3. `warehouse_product_stock` (sync cả hai kho)
4. `stock_movements` (2 dòng: outbound + inbound)

Lưu ý:

- Không nên xóa movement trong production.
- Nếu dev cần reset local DB, phải đồng bộ lại tồn tổng sau khi xóa tay.

---

## 4. Vận Hành UI

URL chính:

- `/warehouse` — danh sách kho
- `/warehouse-stock` — stock adjustment list
- `/warehouse-transfer` — transfer
- `/warehouse-movements` — movement ledger

Nguyên tắc vận hành:

- Tạo kho ở `All warehouses`.
- Nhập/xuất tồn nhanh qua `Stock adjustment`.
- Chuyển kho qua `Transfer stock`.
- Đối soát lịch sử qua `Stock movements`.

UI hiện tại:

- Add Stock và Transfer Stock mở bằng right popup.
- Sidebar Operations không hiện item menu Add/Transfer riêng; thao tác nằm trong màn stock.

---

## 5. Module + Permission + Entitlement Runbook

Mục tiêu: đồng bộ 2 lớp:

- Nwidart module status.
- DB entitlement (`modules`, `module_settings`, `module_in_package`, permission).

Migration đã dùng:

- `Modules/Warehouse/Database/Migrations/2026_03_25_120000_setup_warehouse_module_permissions_and_activation.php`

Checklist rollout:

1. Backup DB.
2. Deploy code.
3. Chạy migrate.
4. `php artisan optimize:clear`.
5. Verify module + permission + module settings.
6. Smoke test warehouse CRUD + stock + transfer.

---

## 6. Hardening Và Ràng Buộc An Toàn

Đã áp dụng:

- Company context bắt buộc.
- Guard warehouse/product phải thuộc company hiện tại.
- Transfer from != to.
- Quantity > 0.
- Chặn outbound khi không đủ tồn, thông báo rõ available/requested.
- Chặn xóa kho khi còn stock/batch/movement/reservation.
- Trả lời user-friendly cho cả ajax/non-ajax, tránh generic "Something went wrong".

---

## 7. Custom Fields — Quyết Định Gọn

Nguyên tắc:

- Dữ liệu tồn kho đa kho/lot/expiry phải ở core DB, không dùng custom field làm source of truth.
- Custom field chỉ giữ cho metadata BI/legacy nếu không trùng cột core.

Khuyến nghị:

- Inventory CF trùng với `warehouse_id`, `batch_number`, `expiration_date`, snapshot kỳ -> nên bỏ.
- Product CF trùng cột core (`brand`, `product_grade`, `product_source`, ...) -> nên bỏ.
- Client CF nghiệp vụ kinh doanh, không trùng core -> có thể giữ.

---

## 8. Bin / Location Future Scope

Hiện tại scope đa kho chỉ cần **Warehouse** ở cấp kho. Chưa bắt buộc làm sâu tới kệ/ngăn/vị trí.

Nếu sau này cần biết hàng nằm chính xác ở đâu trong kho, ví dụ `Aisle A / Rack 02 / Bin 05`, cần bổ sung master **Bin/Location**:

- `warehouse_locations` hoặc bảng tương đương, thuộc `warehouse_id`.
- Dòng stock/batch có thêm `location_id`.
- Transfer có thể là kho -> kho hoặc vị trí -> vị trí.
- Picking Sales DO có thể gợi ý vị trí theo FEFO/FIFO.

Trạng thái quyết định: **Not required for current multi-warehouse scope**. Chỉ mở lại khi PM yêu cầu quản lý vị trí trong kho thật.

---

## 9. Vấn Đề Đã Gặp Và Trạng Thái Hiện Tại

Đã xử lý:

- UI action dropdown warehouse theo pattern Product.
- Sidebar Operations tự động mở khi vào route warehouse.
- Add Stock/Transfer popup submit được.
- Hardening lỗi nghiệp vụ và thông báo rõ ràng.

Còn theo dõi:

- Bộ test tự động (feature/integration) cho stock flows.
- Scope B (invoice outbound) đã triển khai v1 — xem `WAREHOUSE_BUSINESS.md` và `MODULE_WAREHOUSE.md`; vẫn cần UAT staging và có thể mở rộng trigger/kho theo PM.

---

## 10. Checklist Test Tay Để Bàn Giao

- Happy path add/remove/transfer.
- Transfer same warehouse -> bị chặn.
- Insufficient stock -> message rõ.
- Delete warehouse blocked cases.
- Permission denied.
- Missing company context.
- Popup ajax validation + success redirect.

---

## 11. Lịch Sử Tinh Gọn

- 2026-03: Hoàn thiện warehouse multi-flow + UI/UX + hardening.
- 2026-03: Gộp tài liệu warehouse về 1 file master để giảm rối.
- 2026-06-21: Chuẩn hóa tiếng Việt có dấu và thêm Bin/Location future scope.

## Link Liên Quan

- `WAREHOUSE_BUSINESS.md`
- `SALES_FULFILLMENT_SCHEMA_MATRIX.md`
- `SALES_FULFILLMENT_QA_CHECKLIST.md`
- `SALES_BUSINESS.md`
- `INVENTORY_BUSINESS.md`
- `PURCHASE_RETURN_BUSINESS.md`
- `SALES_RETURN_BUSINESS.md`
- `../docs/WAREHOUSE_PURCHASE_ENV_REFERENCE.md`
