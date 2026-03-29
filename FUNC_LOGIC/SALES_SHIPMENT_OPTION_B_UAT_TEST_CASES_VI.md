# UAT Test Cases - Sales Shipment Option B (Step-by-step)

## 1) Mục tiêu

Tài liệu này cung cấp kịch bản UAT chi tiết theo dữ liệu mẫu cố định để QA kiểm thử nhanh:

- Luồng chuẩn: `SO -> Sales Shipment (partial) -> Invoice`
- Outbound mode: `shipment`
- Kiểm tra chống double deduction, reverse, cancel, và tương thích luồng PO/DO inbound cũ.

## 2) Tiền điều kiện (bắt buộc)

1. Cấu hình:
    - `WAREHOUSE_SALES_OUTBOUND_ENABLED=true`
    - `WAREHOUSE_SALES_OUTBOUND_MODE=shipment`
    - `WAREHOUSE_ALLOW_NEGATIVE_STOCK=false` (khuyến nghị cho UAT)
2. Module:
    - `Purchase`, `Warehouse`, `Orders` đã bật cho tenant test.
3. User test có quyền:
    - `view_sales_shipment`
    - `create_sales_shipment`
    - `update_sales_shipment`
    - `ship_sales_shipment`
    - `cancel_sales_shipment`
4. Master data:
    - Có ít nhất 1 kho active: `WH-STAGING-A`
    - Có 1 khách hàng có `default_warehouse_id = WH-STAGING-A`
    - Có 1 sản phẩm goods để test outbound: `SKU-UAT-SS-001`

## 3) Dữ liệu mẫu dùng cho toàn bộ test

- Company test: `COMP-UAT`
- Warehouse: `WH-STAGING-A`
- Client: `CLIENT-UAT-SHIPMENT`
- Product: `SKU-UAT-SS-001`
- Số lượng tồn đầu kỳ tại `WH-STAGING-A`: **50**
- Sales Order mẫu: `SO-UAT-0001`
- SO line:
    - Product: `SKU-UAT-SS-001`
    - Quantity: **10**
    - Unit price: tùy ý (không ảnh hưởng stock test)

## 4) Test case chi tiết

---

## TC-01: Tạo SO mẫu và xác nhận dữ liệu đầu vào

**Mục tiêu:** đảm bảo nền dữ liệu đúng trước khi shipment.

### Bước thực hiện

1. Tạo Sales Order `SO-UAT-0001` với line qty = 10 cho `SKU-UAT-SS-001`.
2. Mở màn show SO, xác nhận nút `Add Sales Shipments` hiển thị.
3. Kiểm tra tồn kho hiện tại của SKU tại `WH-STAGING-A`.

### Kỳ vọng

- SO tạo thành công.
- Nút tạo shipment từ SO hiển thị.
- Tồn kho trước shipment vẫn là **50**.

### Pass/Fail

- [ ] PASS
- [ ] FAIL
- Ghi chú lỗi:

---

## TC-02: Shipment lần 1 (partial = 4)

**Mục tiêu:** kiểm tra partial shipment và outbound khi ship.

### Bước thực hiện

1. Từ SO tạo Shipment #1.
2. Chọn line `SKU-UAT-SS-001`, nhập `quantity_shipped = 4`.
3. `Confirm` shipment.
4. `Ship` shipment.

### Kỳ vọng

- Shipment chuyển trạng thái: `draft -> confirmed -> shipped`.
- `outbound_stock_applied = true`.
- Tồn kho giảm từ 50 xuống **46**.
- Có stock movement outbound với `reference_type = Modules\Purchase\Entities\SalesShipment`.

### Pass/Fail

- [ ] PASS
- [ ] FAIL
- Ghi chú lỗi:

---

## TC-03: Shipment lần 2 (partial = 6)

**Mục tiêu:** hoàn tất phần còn lại của SO.

### Bước thực hiện

1. Tạo Shipment #2 cho cùng SO.
2. Hệ thống hiển thị `Remaining Qty = 6`.
3. Nhập `quantity_shipped = 6`.
4. `Confirm` và `Ship`.

### Kỳ vọng

- Shipment #2 thành công.
- Tồn kho giảm từ 46 xuống **40**.
- Tổng outbound theo 2 shipment = **10**.

### Pass/Fail

- [ ] PASS
- [ ] FAIL
- Ghi chú lỗi:

---

## TC-04: Chặn shipment vượt số lượng còn lại

**Mục tiêu:** đảm bảo không thể over-ship.

### Bước thực hiện

1. Tạo Shipment #3 cho cùng SO.
2. Thử nhập `quantity_shipped > 0` cho line đã giao đủ.

### Kỳ vọng

- UI line bị disable hoặc backend trả lỗi validation.
- Không có movement outbound mới.
- Tồn kho vẫn giữ ở **40**.

### Pass/Fail

- [ ] PASS
- [ ] FAIL
- Ghi chú lỗi:

---

## TC-05: Deliver shipment và reverse outbound

**Mục tiêu:** xác nhận lifecycle và hoàn kho chuẩn.

### Bước thực hiện

1. Chọn Shipment #2 (đang `shipped`) -> `Deliver`.
2. Thực hiện action `Reverse Outbound`.

### Kỳ vọng

- Shipment chuyển `shipped -> delivered`.
- Reverse thành công và chuyển về `confirmed`.
- Tồn kho tăng lại đúng phần đã reverse (ví dụ từ 40 lên **46** nếu reverse shipment qty 6).
- Có movement inbound với `reference_type = sales_shipment_stock_reversal`.

### Pass/Fail

- [ ] PASS
- [ ] FAIL
- Ghi chú lỗi:

---

## TC-06: Cancel shipment đã outbound

**Mục tiêu:** đảm bảo cancel không làm lệch tồn.

### Bước thực hiện

1. Chọn shipment đã từng outbound (trạng thái phù hợp theo UI).
2. Chạy action `Cancel`.

### Kỳ vọng

- Nếu đã outbound, hệ thống reverse trước rồi cancel.
- Trạng thái cuối là `cancelled`.
- Tồn kho sau cancel không bị âm và không lệch so với expected.

### Pass/Fail

- [ ] PASS
- [ ] FAIL
- Ghi chú lỗi:

---

## TC-07: Invoice ở mode shipment không trừ tồn lần 2

**Mục tiêu:** kiểm tra chống double deduction.

### Bước thực hiện

1. Giữ mode: `WAREHOUSE_SALES_OUTBOUND_MODE=shipment`.
2. Tạo/cập nhật invoice liên quan `SO-UAT-0001` (non-draft).
3. Theo dõi tồn kho trước/sau thao tác invoice.

### Kỳ vọng

- Không phát sinh outbound bổ sung từ invoice.
- Tồn kho không bị giảm thêm ngoài phần shipment đã ship.

### Pass/Fail

- [ ] PASS
- [ ] FAIL
- Ghi chú lỗi:

---

## TC-08: Regression PO/DO inbound cũ

**Mục tiêu:** xác nhận Option B không phá luồng mua.

### Bước thực hiện

1. Tạo 1 PO inbound test với SKU khác (`SKU-UAT-IN-001`).
2. Thực hiện luồng inbound theo cấu hình hiện tại (PO delivered hoặc DO received).

### Kỳ vọng

- Inbound movement vẫn ghi bình thường.
- Không có thay đổi bất thường ở luồng DO inbound cũ.

### Pass/Fail

- [ ] PASS
- [ ] FAIL
- Ghi chú lỗi:

## 5) Bảng tổng hợp kết quả UAT

| Test Case | Kết quả | Người test | Thời gian | Ghi chú |
| --------- | ------- | ---------- | --------- | ------- |
| TC-01     |         |            |           |         |
| TC-02     |         |            |           |         |
| TC-03     |         |            |           |         |
| TC-04     |         |            |           |         |
| TC-05     |         |            |           |         |
| TC-06     |         |            |           |         |
| TC-07     |         |            |           |         |
| TC-08     |         |            |           |         |

## 6) Tiêu chí UAT pass để PM sign-off

- 100% test case critical (TC-02, TC-03, TC-04, TC-07, TC-08) phải PASS.
- Không có lệch tồn kho sau các bước reverse/cancel.
- Không ghi nhận double deduction trong mode `shipment`.
