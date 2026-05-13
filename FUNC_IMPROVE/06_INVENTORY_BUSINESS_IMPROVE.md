# Inventory Business Direction

## Trạng thái rà soát (2026-04-30)

- Đã có trong hệ thống:
    - Snapshot tồn kho đồng bộ từ `warehouse_product_batches` (on-hand/reserved/available).
    - Guard batch ở Sales DO theo điều kiện thực tế (không bắt batch cứng cho mọi sản phẩm).
    - Trigger tồn kho chuẩn theo DO ship / GRN received trong flow hiện tại.
- Đang còn backlog:
    - Tách màn/luồng hiển thị “Inventory On-hand” vs “Movement History” rõ hơn ở UI.
    - Chuẩn hóa sâu policy theo cờ sản phẩm (`is_batch_tracked`, `is_expiry_tracked`, `is_serial_tracked`) trên toàn bộ inbound paths.
    - Dashboard aging/near-expiry theo batch nâng cao.
- Kết luận: file này vẫn còn giá trị **định hướng improve**, chưa thể xóa.

## Quy ước vận hành (2026-05-05)

### 1) `Opening stock` vs `Add Inventory`

- Khuyến nghị vận hành: dùng `Add Inventory` làm nguồn chính để set/chỉnh tồn (có log nghiệp vụ rõ ràng).
- `Opening stock` ở màn Product chỉ nên dùng một lần khi onboarding nhanh master data; tránh dùng lặp lại sau go-live.
- Không khởi tạo tồn hai lần cho cùng thời điểm (vừa nhập `opening_stock`, vừa tạo phiếu `Add Inventory`) để tránh lệch tồn.

### 2) Ý nghĩa `Reserved Quantity` ở form `Add Inventory`

- Về nghiệp vụ, reserved vẫn là "phần tồn bị giữ chỗ, chưa xuất kho thật".
- Tuy nhiên tại implementation hiện tại:
    - input `Reserved Quantity` ở form `Add Inventory` lưu vào `purchase_stock_adjustments.reserved_quantity` (mức phiếu điều chỉnh),
    - còn số reserved hiển thị ở Inventory list lấy từ `warehouse_product_batches.reserved_quantity` (mức vận hành kho thực tế).
- Vì vậy nhập reserved ở form này hiện không làm cột Reserved trên list thay đổi ngay nếu không có cơ chế sync sang batch layer.

### 3) Chính sách đề xuất cho team

- Chuẩn vận hành: reserved vận hành thực tế chỉ lấy từ layer batch (`warehouse_product_batches`) và luồng reservation service.
- Với UI hiện tại, coi `Reserved Quantity` trong `Add Inventory` là trường lịch sử/ghi chú nghiệp vụ, không phải nguồn reserved canonical.
- Backlog nên làm:
    - hoặc sync input này sang batch reserved,
    - hoặc ẩn/đổi nhãn trường để tránh hiểu nhầm.

## Mục tiêu

Chuẩn hóa nghiệp vụ kho theo 2 lớp dữ liệu:

1. Snapshot tồn hiện tại (On-hand)
2. Ledger giao dịch (Movements)

---

## Hiện trạng

- Màn `Inventory` đang thiên về snapshot (tổng hợp tồn hiện tại).
- Người dùng dễ hiểu nhầm: nhập/chỉnh nhiều lần nhưng list không tăng dòng tương ứng.
- Batch hiện có thể bị null ở một số luồng inbound, gây khó truy vết khi DO ship.

### Vấn đề thực tế đã gặp: filter theo warehouse bị “rỗng giả” (2026-05-05)

- Triệu chứng:
    - `Stock Movements` đã có dòng nhập/xuất đúng warehouse (ví dụ `WAREHOUSE A`),
    - nhưng màn `Inventory` lọc `WAREHOUSE A` lại không thấy dòng tương ứng.
- Root cause kỹ thuật:
    - nguồn list hiện tại của `Inventory` vẫn neo vào `purchase_inventory_adjustment` + `purchase_stock_adjustments` (phiếu điều chỉnh/lịch sử),
    - sau đó mới join số lượng từ layer warehouse,
    - nên nếu warehouse có tồn phát sinh từ movement khác mà thiếu bản ghi tương ứng trong `purchase_stock_adjustments`, kết quả filter sẽ rỗng.
- Kết luận:
    - đây là lệch nguồn dữ liệu hiển thị (UI/query design), không phải lỗi “hệ thống chỉ cho 1 warehouse”.
- Hướng fix chuẩn:
    - dùng `warehouse_product_stock` (và batch aggregate khi cần) làm **nguồn gốc** cho màn `Inventory On-hand`,
    - giữ `purchase_stock_adjustments` là **ledger điều chỉnh**/tham chiếu lịch sử.
- Ưu tiên triển khai:
    - P0: sửa query nguồn list Inventory theo warehouse stock canonical,
    - P1: bổ sung liên kết drill-down từ snapshot sang `Stock Movements` theo `warehouse_id + product_id`,
    - P1: test hồi quy cho case “movement có dữ liệu nhưng inventory filter không rỗng”.

---

## Định hướng chuẩn nghiệp vụ

### 1) Tách rõ 2 lớp dữ liệu

- **Inventory On-hand (Snapshot):**
    - 1 dòng / Warehouse + Product (+ Batch nếu batch-tracked)
    - Dùng cho vận hành nhanh, xem tồn tức thời.
- **Inventory Transactions (Ledger):**
    - Mỗi nghiệp vụ nhập/xuất/chuyển/chỉnh là 1 dòng.
    - Có reference chứng từ, user, thời gian, before/after quantity.

### 2) Chính sách Batch/Lot theo sản phẩm

- Không bắt batch cho tất cả sản phẩm.
- Áp theo cờ sản phẩm:
    - `is_batch_tracked`
    - `is_expiry_tracked`
    - (tùy chọn) `is_serial_tracked`
- Bắt batch/expiry ở inbound với sản phẩm cần batch.
- Với sản phẩm không batch: cho phép null.

### 3) Quy tắc hiển thị

- Màn Snapshot phải ghi rõ là dữ liệu tổng hợp.
- Batch hiển thị có fallback rõ ràng (tránh ô trống khó hiểu).
- Có lối đi nhanh từ Snapshot sang Movement history theo dòng.

---

## Đề xuất UX

- Đổi tên màn hiện tại thành: **Inventory On-hand**
- Thêm nút: **View Movement History**
- Tooltip giải thích:
    - `Quantity on hand` = tồn mục tiêu sau điều chỉnh
    - `Quantity adjusted` = chênh lệch so với tồn hiện tại

---

## Roadmap cải thiện

### Phase 1 (ngắn hạn)

- Làm rõ nhãn/tooltip UI để tránh hiểu nhầm.
- Chuẩn hóa hiển thị batch khi batch_number null.
- Bổ sung cảnh báo nếu sản phẩm batch-tracked nhưng inbound thiếu batch.

### Phase 2 (trung hạn)

- Ràng buộc validation theo cờ sản phẩm (batch/expiry).
- Chuẩn hóa báo cáo movement theo chứng từ nguồn (GRN, DO, Adjust, Transfer, Invoice).

### Phase 3 (dài hạn)

- Chuẩn hóa đầy đủ audit trail:
    - before/after qty
    - actor
    - source document
    - batch/expiry/serial
- Dashboard tồn + aging + near-expiry theo batch.

---

## Quyết định đề xuất

- **Không giữ mô hình “1 màn duy nhất”** cho cả snapshot và lịch sử.
- **Chuẩn là giữ snapshot + bổ sung ledger rõ ràng**.
- Batch bắt buộc theo loại sản phẩm, không áp cứng cho toàn bộ danh mục.

---

## MAOLIN Batch Scope Conclusion

### Kết luận

- Dữ liệu import của MAOLIN đang theo cơ chế **batch theo kho** (multi-warehouse batch), không phải batch global toàn hệ thống.
- Scope phù hợp cho MAOLIN:
    - `company_id, warehouse_id, product_id, batch_number, expiration_date`
- Không nên dùng global unique:
    - `product_id + batch_number`
    - vì sẽ chặn sai khi cùng lô nằm ở nhiều kho khác nhau.

### Bằng chứng từ file import MAOLIN

- File `PROJECT MAOLIN/Product Inventory List (Warehouse_code).csv` có đồng thời:
    - cột `batch_number` (批號),
    - cột `warehouse_code` (庫別),
    - cột `warehouse_name` (庫別名稱),
    - và cùng SKU/lô xuất hiện ở nhiều kho khác nhau.
- File `PROJECT MAOLIN/Craveva_Full Inventory List_全庫存明細表.csv` cũng thể hiện cùng SKU/lô xuất hiện nhiều warehouse khác nhau.
- Tài liệu mapping `FUNC_LOGIC/MAOLIN_IMPORT_MAPPING.md` đặt trọng tâm inventory theo multi-warehouse:
    - ưu tiên map `warehouse_code`,
    - fallback `warehouse_name`,
    - batch/expiry đi kèm theo dòng tồn kho.

### Nơi nhập batch trong flow hiện tại

- Inbound adjust:
    - `Operations -> Inventory -> Add Inventory`
    - nhập `batch_number`, `manufacturing_date`, `expiration_date`.
- GRN inbound:
    - nhập `batch_number`, `expiry_date` theo từng dòng nhận hàng.
- Sales DO outbound:
    - không nhập text batch tay,
    - chọn `warehouse_batch_id` từ batch có sẵn theo kho/sản phẩm.

---

## Demo Execution Tracking (2026-04-23)

### Hoàn thành

- [x] Bổ sung command dedupe batch identity:
    - `php artisan warehouse:batch-dedupe` (dry-run)
    - `php artisan warehouse:batch-dedupe --apply` (gộp + xoá duplicate)
- [x] Bổ sung migration tạo unique index batch identity theo multi-warehouse:
    - unique key: `company_id, warehouse_id, product_id, batch_number, expiration_date`
    - đã chạy migrate local thành công.
- [x] Migration có pre-check duplicate:
    - nếu còn duplicate sẽ fail có hướng dẫn chạy command dedupe trước.
- [x] Thêm test command dedupe để regression-safe.
- [x] Cứng hóa UX/UI ở màn Sales DO (batch selection):
    - chặn bấm Save khi chưa chọn warehouse,
    - validate theo từng dòng: chỉ bắt batch khi server render `data-requires-batch=1` (đồng bộ với dropdown batch),
    - cảnh báo sớm nếu `ship qty` vượt `available` của batch đã chọn,
    - với dòng không có batch khả dụng: cho phép ship như non-batch, chỉ hiện cảnh báo hướng dẫn.
- [x] Cứng hóa backend validation cho Sales DO:
    - chặn lưu khi `ship qty` vượt `available quantity` của batch.
- [x] Verify index sau migrate:
    - `wpb_company_wh_product_batch_exp_unique` đã tồn tại trên `warehouse_product_batches`.

### Ghi chú vận hành trước demo

1. Chạy dry-run:
    - `php artisan warehouse:batch-dedupe`
2. Nếu có duplicate:
    - `php artisan warehouse:batch-dedupe --apply`
3. Chạy migrate:
    - `php artisan migrate`
4. Quick verify:
    - tạo inbound có batch
    - tạo SO -> DO (chọn batch) -> Invoice
    - xác nhận batch không trùng identity trong cùng kho/sản phẩm/hạn dùng.
    - xác nhận UX: báo lỗi ngay trên DO khi thiếu batch hoặc ship vượt available.

---

## UAT Run - SO -> DO -> Invoice (2026-04-24)

### Kết quả tổng quan

- **PASS**: đã chạy thành công luồng Sales DO -> Invoice trong shipment mode.

### Chứng từ test

- Sales Order:
    - `ODR#004`
    - URL: `https://craveva-staging.test/account/orders/12`
- Sales DO:
    - `SS-000008`
    - URL: `https://craveva-staging.test/account/sales-do/8`
    - trạng thái đi qua: `draft -> confirmed -> shipped`
- Invoice:
    - `INV#028`
    - URL: `https://craveva-staging.test/account/invoices/10`
    - trạng thái: `unpaid`

### Dữ liệu batch xác minh

- Product: `CP CHICKEN WING` (SKU `2324122`)
- Warehouse: `DEFAULT WAREHOUSE (DFWH)`
- Batch: `DEMO-ODR004-B1`
- Qty test: `1`

### Checklist xác minh

- [x] Tạo SO bằng sản phẩm có sẵn
- [x] Tạo DO từ SO
- [x] Chọn batch theo warehouse/product
- [x] Confirm DO
- [x] Ship DO
- [x] Tạo Invoice từ DO
- [x] Invoice auto-fill đúng item/client/qty/price

### Kết luận demo

- Core flow `SO -> DO -> Invoice` đã chạy được end-to-end.
- Batch flow multi-warehouse đã có:
    - guard ở UI,
    - guard ở backend,
    - unique index ở DB để chống trùng identity.

---

## Stock Trigger Matrix (staging chot 2026-04-24)

### PO / GRN / Bill

- `PO` tao xong: **chua cong kho** neu `delivery_status` cua PO van `not_started`.
- `GRN` khi status = `received`: **cong kho (+)** vao `stock_movements` (inbound).
- `Bill` (`draft/open/paid`): **khong cong/tru kho**; chi tac dong tai chinh.

### SO / DO / Invoice

- `Sales DO` khi status = `shipped`: **tru kho (-)** (apply outbound).
- `Sales DO` status `delivered`: khong tru them lan nua (chi danh dau giao xong).
- `Invoice` (`unpaid/paid`): **khong cong/tru kho** trong flow hien tai.

### Chung tu test xac nhan

- `PO#002` (SKU `COM123`) -> `GRN 003` (`received`) -> tao movement inbound `+2` (`product_id=8449`, `warehouse=78`), sau do tao `BL#002` khong phat sinh movement moi.
- `ODR#004` -> `SS-000013` (`shipped`) -> tao movement outbound `-1`.
- Multi-warehouse sample:
    - `PO#004` -> `GRN 005` (`received`, `warehouse=79/WHA`) -> movement inbound `id=7105`, qty `+1` (`warehouse_to_id=79`).
    - `ODR#005` -> `SS-000015` (`shipped`, `warehouse=79/WHA`) -> movement outbound `id=7106`, qty `-1` (`warehouse_from_id=79`).
    - Net bien dong SKU `COM123` tai `WHA` trong demo = `+1 - 1 = 0` (dung theo quy trinh).

---

## Tai lieu canonical cho demo nhanh

- Tài liệu theo dõi implementation + test mới nhất: `FUNC_IMPORT/IMPORT_POLL_TRACKERS_VI.md` (Phụ lục A).
- File nay giu vai tro business direction va quyet dinh UI/UX; khi can ket qua test staging thi uu tien tracker ben tren.

---

## Demo Cleanup Command

### Mục tiêu

- Dọn nhanh dữ liệu test/demo sau khi trình bày (SO/DO/Invoice/Batch demo), tránh làm bẩn môi trường.

### Lệnh

- Dry-run (khuyến nghị chạy trước):
    - `php artisan warehouse:demo-cleanup`
- Apply thật:
    - `php artisan warehouse:demo-cleanup --apply`

### Safety

- Command mặc định chạy `dry-run`, chỉ xóa khi có `--apply`.
- Đã thêm feature test:
    - `tests/Feature/WarehouseDemoCleanupCommandTest.php`
    - kiểm tra cả dry-run và apply (chỉ xóa đúng record mục tiêu).

### Default target đang cấu hình

- `order_no=ODR#004`
- `do_no=SS-000008`
- `invoice_no=INV#028`
- `batch_no=DEMO-ODR004-B1`
- `company_id=1`

### Override khi cần

- Ví dụ:
    - `php artisan warehouse:demo-cleanup --apply --order_no="ODR#005" --do_no="SS-000009" --invoice_no="INV#029" --batch_no="DEMO-ODR005-B1" --company_id=1`

### One-command helper script

- Script: `scripts/demo-so-do-invoice.ps1`

- Chuẩn bị trước demo (audit duplicate batch):
    - `powershell -ExecutionPolicy Bypass -File .\scripts\demo-so-do-invoice.ps1 -Mode prep`

- Verify nhanh trước giờ demo (focused tests):
    - `powershell -ExecutionPolicy Bypass -File .\scripts\demo-so-do-invoice.ps1 -Mode verify`

- Cleanup dry-run:
    - `powershell -ExecutionPolicy Bypass -File .\scripts\demo-so-do-invoice.ps1 -Mode cleanup-dry`

- Cleanup apply:
    - `powershell -ExecutionPolicy Bypass -File .\scripts\demo-so-do-invoice.ps1 -Mode cleanup-apply`

---

## Ghi chu nghiep vu: Transfer Stock / Adjust Stock / Stock Movements

### Muc dich tung man

- `Transfer Stock`:
    - Chuyen hang noi bo giua 2 kho (khong phai mua/ban).
    - Tao 2 movement: `outbound` tai kho nguon + `inbound` tai kho dich.

- `Adjust Stock`:
    - Dieu chinh ton kho thu cong (kiem kho lech, hu hong, mat hang, nhap bu...).
    - Day la thao tac manual, nguoi dung chu dong tao.

- `Stock Movements`:
    - So cai lich su bien dong kho de audit.
    - Tap trung tat ca inbound/outbound tu ca luong tu dong va thao tac manual.

### Trong flow SO/PO: cai nao tu dong, cai nao manual

- Tu dong phat sinh:
    - `PO -> GRN (status received)`: he thong cong kho (`inbound`).
    - `SO -> Sales DO (status shipped)`: he thong tru kho (`outbound`).

- Manual (nguoi dung tu tao):
    - `Transfer Stock`
    - `Adjust Stock`

- Luu y:
    - Bill/Invoice chu yeu la chung tu tai chinh, khong phai diem trigger chinh de cong/tru ton vat ly trong flow hien tai.
