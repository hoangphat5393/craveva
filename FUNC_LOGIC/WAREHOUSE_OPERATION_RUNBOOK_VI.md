# Warehouse Operation Runbook (Local) - Craveva

## 1) Mục tiêu

Tài liệu này là quy trình vận hành Warehouse theo luồng mới (WUP-01..WUP-07 nền), ưu tiên chạy local để test UI và nghiệp vụ trước khi đồng bộ môi trường khác.

## 2) Cấu hình local khuyến nghị

Trong `.env`:

- `WAREHOUSE_SALES_OUTBOUND_ENABLED=true`
- `WAREHOUSE_SALES_OUTBOUND_MODE=shipment`
- `WAREHOUSE_INBOUND_FROM_PO_DELIVERED=true`
- `WAREHOUSE_INBOUND_FROM_DO_RECEIVED=false`
- `WAREHOUSE_ALLOW_NEGATIVE_STOCK=false`
- `WAREHOUSE_STRICT_UNIT_CONVERSION=false` (bật `true` khi đã map đầy đủ conversion)

Sau khi đổi config:

- `php artisan config:clear`

## 3) Chu kỳ nghiệp vụ cần test trên UI

### 3.1 Kho và loại kho

1. Tạo/Update warehouse với `warehouse_type`:
    - `normal`: bán được
    - `locked`, `scrap`, `transit`: không được reserve/outbound bán hàng
2. Đảm bảo kho bán hàng mặc định của client map vào kho `normal`.

### 3.2 Luồng bán hàng (reserve -> outbound -> release)

1. Tạo Sales DO từ Order.
2. `Confirm`:
    - hệ thống reserve tồn.
3. `Ship`:
    - hệ thống trừ tồn outbound.
    - reservation chuyển sang consumed.
4. `Cancel`:
    - nếu đã outbound thì reverse outbound.
    - reservation active sẽ release.

### 3.3 Luồng inbound canonical

Chỉ được chọn 1 trong 2:

- PO delivered inbound
- DO received inbound

Nếu bật đồng thời cả 2, hệ thống sẽ guard và báo lỗi conflict để tránh double-count.

## 4) Unit conversion (WUP-06 nền)

### 4.1 Nguyên tắc

- Tất cả reserve/deduct/inbound sẽ convert về base unit của product trước khi xử lý tồn.
- Mapping conversion nằm trong bảng `product_unit_conversions`:
    - `product_id`
    - `unit_id` (đơn vị đầu vào)
    - `factor_to_base` (hệ số nhân về base)

### 4.2 Ví dụ

- Base unit của SKU = `Pcs`
- Đơn vị bán = `Box`, `factor_to_base = 10`
- Nhập 2 Box -> hệ thống xử lý tồn là 20 Pcs.

## 5) Idempotent + reconciliation tối thiểu (WUP-07 nền)

### 5.1 Idempotent stock movement

- `stock_movements.idempotency_key` được dùng để chặn duplicate posting từ cùng sự kiện.
- Các luồng invoice/shipment/inbound mới sẽ truyền key này.

### 5.2 Reconciliation report

Chạy command local:

- `php artisan warehouse:reconciliation-report --date=YYYY-MM-DD`
- `php artisan warehouse:reconciliation-report --date=YYYY-MM-DD --company_id=1`

Kết quả:

- File JSON: `storage/app/warehouse-reconciliation/warehouse-reconciliation-<date>-*.json`
- Bản ghi DB: `warehouse_sync_reconciliation_logs`

## 6) Checklist UAT nhanh

1. Kho `locked/scrap` không ship được.
2. 2 DO reserve gần đồng thời không oversell.
3. Bật sai cờ PO + DO inbound -> bị guard conflict.
4. Cancel DO release reservation đúng.
5. API availability trả đúng:
    - `GET /api/v1/warehouse/availability?company_id=...&product_id=...`

## 7) Vận hành sự cố

- Lỗi `Inbound configuration conflict`: tắt 1 trong 2 cờ inbound PO/DO.
- Lỗi `Missing unit conversion mapping`: thêm mapping `product_unit_conversions` hoặc tạm thời tắt strict mode.
- Số liệu outbound bị lặp: chạy reconciliation report và đối soát duplicate group theo `reference_type/reference_id`.

## 8) Phạm vi local-first

- Ưu tiên xác nhận nghiệp vụ và UI trên local trước.
- Không phụ thuộc hub/staging trong giai đoạn này.
