# Audit chức năng Sales DO (Sale Delivery Order / phiếu giao bán)

**Phạm vi:** `SalesShipmentController`, `SalesDoService`, view `sales-shipment/*`, `SalesShipmentStockService` / `StockReservationService`, `SalesDoRuntime` (bảng `sales_dos`).  
**Ngày:** 2026-04-09  
**Mục đích:** Liệt kê hành vi thực tế, chỗ dễ hiểu nhầm, và **bug / rủi ro** có thể giải thích cảm giác “lỗi” khi dùng.

---

## 1) Luồng nghiệp vụ (chuẩn thiết kế)

| Bước             | Hành động                                        | Code                                                                                         |
| ---------------- | ------------------------------------------------ | -------------------------------------------------------------------------------------------- |
| Tạo / sửa        | Lưu header + dòng theo SO                        | `store` / `update` → `SalesDoService::create` / `update`                                     |
| Confirm          | Giữ chỗ tồn (reservation)                        | `confirm` → `ensureReservationsForShipment`                                                  |
| Ship             | Xuất kho (mode `shipment`) + consume reservation | `ship` → `ensureReservationsForShipment` → `applyOutboundForShipment` → `consumeByReference` |
| Deliver          | Đổi trạng thái                                   | `deliver`                                                                                    |
| Reverse / Cancel | Hoàn kho / hủy giữ chỗ                           | `reverse` / `cancel`                                                                         |

Form **create/edit** chỉ cho chọn trạng thái **draft | confirmed** (view), nhưng **validation server** cho phép thêm `shipped|delivered|cancelled` — xem mục 3.

---

## 2) Hành vi dễ tưởng là bug (thường không phải lỗi code)

### 2.1 “Remaining Qty” giảm dù chưa Ship

`remainingQtyByOrderItem()` cộng `SUM(quantity_shipped)` của **mọi** DO cùng SO **trừ** trạng thái `cancelled` — **kể cả draft và confirmed**.

**Hệ quả:** Một DO **draft** đã nhập Ship Qty > 0 vẫn **ăn hết quota** giao còn lại cho DO sau. Đây là cách **khóa sớm** để tránh hai người cùng tạo hai phiếu giao vượt SO; nhưng dễ gây hiểu nhầm “chưa xác nhận sao đã trừ”.

### 2.2 Lưu với trạng thái **Confirmed** mà không bấm nút **Confirm** trên màn chi tiết

- Nút **Confirm** trên overview **chỉ hiện khi** `status === draft` (`overview.blade.php`).
- Nếu lưu form với **Confirmed**, record đã `confirmed` trong DB nhưng **không** đi qua action `confirm()` — reservation **chưa** tạo tại thời điểm Save (reservation chỉ chắc chắn khi gọi `confirm()` hoặc khi `ship()` gọi `ensureReservationsForShipment`).

**Điểm tốt:** `ship()` vẫn gọi `ensureReservationsForShipment` trước outbound, nên thường **vẫn ship được** nếu tồn đủ.

**Điểm xấu:** Trạng thái “confirmed” trên form **không đồng nghĩa** đã chạy đủ logic `confirm()` (đặc biệt nếu sau này thêm rule chỉ trong `confirm()`).

### 2.3 Ship Qty = 0

Validation không bắt buộc tổng ship > 0 khi **Save**; `ship()` từ chối nếu `sum(quantity_shipped) <= 0`. Người dùng phải **Sửa** phiếu, nhập Ship Qty, rồi **Ship** — đúng thiết kế nhưng dễ thấy “lỗi” nếu kỳ vọng Save là đủ.

---

## 3) Bug / rủi ro thật (nên xử lý hoặc quy ước vận hành)

### 3.1 Đổi **Warehouse** (hoặc dòng hàng) sau khi đã **Confirm** — reservation có thể lệch kho

- `confirm()` tạo reservation theo **`warehouse_id` hiện tại** trên header.
- `update()` chỉ cập nhật DB + `syncItems`, **không** gọi `releaseReservationsForShipment` / `ensureReservationsForShipment`.
- `ensureReservationsForShipment`: nếu **đã** có reservation active → **return sớm**, **không** tạo lại theo kho mới.
- `ship()` outbound dùng **`warehouse_id` mới** trên header, nhưng `consumeByReference` vẫn “ăn” reservation gắn **kho cũ**.

**Mức độ:** Cao — có thể dẫn tới **trừ tồn kho A** nhưng giải phóng **reserved** ở kho B (hoặc ngược lại), lệch sổ batch/reserved.

**Hướng xử lý (kỹ thuật):** Trên `update()`, nếu `warehouse_id` hoặc các dòng ảnh hưởng tồn thay đổi và phiếu chưa ship: `releaseByReference` rồi (tuỳ trạng thái) gọi lại `ensureReservationsForShipment` hoặc bắt buộc user **Cancel** phiếu và tạo lại.

### 3.2 Validation `status` quá rộng

`validateForm` cho phép `draft|confirmed|shipped|delivered|cancelled`. Form UI không gửi `shipped`/`delivered`, nhưng request giả mạo có thể tạo record **shipped** mà **không** chạy `ship()` → **không** có movement kho. **Rủi ro** chủ yếu nếu có client không tin cậy hoặc lỗi tích hợp.

**Hướng xử lý:** Chỉ cho phép `draft|confirmed` ở `store`/`update`; các trạng thái khác **chỉ** qua action `ship`/`deliver`/`cancel`.

### 3.3 Đổi **order_id** khi Edit

Cho phép đổi SO trên phiếu đã có dòng — dễ tạo dữ liệu không nhất quán (dòng gắn `order_item_id` của SO cũ với header trỏ SO mới) nếu không validate chặt. Cần rà soát thêm rule “không cho đổi order_id sau khi tạo” hoặc validate từng `order_item_id` thuộc `order_id` mới.

### 3.4 Số phiếu tự sinh `SS-00000x`

`nextShipmentNumber()` dựa trên `max(id)+1`, không phải max số hiển thị — có thể trùng nếu nhập tay số khác; unique theo `(company_id, do_number)` giúp bắt một phần.

---

## 4) Checklist UAT nhanh (tay)

1. SO 1 dòng → tạo DO draft → nhập Ship Qty → **Confirm** → **Ship** → kiểm `stock_movements` outbound (reference Sales DO).
2. Cùng SO → DO thứ hai: **Remaining** đã trừ phần DO đầu (kể cả draft nếu đã nhập ship qty).
3. **Sau Confirm**, đổi Warehouse rồi **Ship**: kiểm reservation + outbound cùng kho (hiện có rủi ro ở §3.1).
4. Permission: user thiếu quyền `sales_do.ship` không thấy Ship/Deliver.

---

## 5) File code chính (tham chiếu)

- `Modules/Purchase/Http/Controllers/SalesShipmentController.php` — validate, remaining, CRUD.
- `Modules/Purchase/Services/SalesDoService.php` — confirm/ship/cancel/reverse.
- `Modules/Warehouse/Services/SalesShipmentStockService.php` — reserve / outbound / consume.
- `Modules/Purchase/Resources/views/sales-shipment/ajax/overview.blade.php` — nút Confirm chỉ khi draft.

---

## 6) Kết luận ngắn

- Nhiều cảm giác “bug” đến từ **cách tính remaining (kể cả draft)** và **hai nghĩa của “Confirmed”** (dropdown form vs nút Confirm).
- **Bug nghiêm trọng nhất đã xác định trong audit này:** **đổi kho sau khi đã giữ chỗ** mà không đồng bộ reservation (mục **3.1**).
