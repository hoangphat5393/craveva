# Maolin Daily Import/Export Demo Analysis (ERP Sync Loop)

## 0. Tóm tắt nhanh (1 trang — PM / khách)

_Gộp từ `MAOLIN_PM_CLIENT_ONEPAGE.md` để chỉ còn một bản daily sync._

- **Mục tiêu demo:** Sáng import từ ERP Maolin → Craveva; tối export Craveva → Maolin nhập lại ERP. Ba nhóm: **Product, Inventory, Client**.
- **Hiện trạng nhanh:** Client import ổn theo `client_code`; Product cần cột giá; Inventory cần **map đúng kho + SKU (+ lô)**; export chuẩn 3 file về đêm chưa đủ trong code.
- **Chốt trước demo:** Template v1; key đồng bộ (SKU, `client_code`, `warehouse_code` + SKU); rule insert/update; nguồn giá chuẩn; cutoff thời gian.
- **Rủi ro:** Thiếu giá Product → import fail; map kho sai → tồn lệch; SKU/client_code không chuẩn → sai liên kết.

_Chi tiết kỹ thuật, bảng format đề xuất và câu hỏi khách: các mục A–F bên dưới._

---

**Bối cảnh demo:**

- Sáng: import dữ liệu từ Maolin vào hệ thống Craveva.
- Tối: export dữ liệu từ Craveva, để Maolin import ngược lại ERP của họ.
- Chu kỳ lặp mỗi ngày cho 3 domain: Product, Inventory, Client.

**Nguồn đối chiếu thực tế:**

- File khách: `FUNC_LOGIC/PROJECT_MAOLIN_NEW_FILES_ANALYSIS.md`
- Import fields: `app/Imports/ProductImport.php`, `app/Imports/ClientImport.php`, `Modules/Purchase/Imports/InventoryImport.php`
- Import logic: `app/Jobs/ImportProductChunkJob.php`, `app/Services/ClientImportProcessor.php`, `Modules/Purchase/Jobs/ImportInventoryJob.php`
- Tồn kho/đa kho: `Modules/Warehouse/Entities/WarehouseProductStock.php`, `app/Models/StockMovement.php`

---

## A. Đánh giá file hiện tại

### A.1 Product

- **File đang có:** `Craveva product.xlsx`
- **Đủ cho import cơ bản:** SKU, tên, quy cách, đơn vị, brand, grade, inventory_type, storage_condition, shelf_life_days.
- **Thiếu cho vòng sync đầy đủ:** price/standard_price, price_per_box, product_source, product_category, product_sub_category.
- **Lưu ý kỹ thuật:** logic import product hiện tại yêu cầu có `price` hoặc `standard_price`; thiếu giá thì job báo invalid data.

### A.2 Client

- **File đang có:** `Craveva customer.xlsx`
- **Đủ cho import cơ bản:** client_code, name, tax id, address, phone.
- **Thiếu/không khớp:** thiếu `department`; có thêm `region` (地區別); kho chỉ định map qua `designated_warehouse_code` / `designated_warehouse_name` → `default_warehouse_id` (import + form client).
- **Lưu ý kỹ thuật:** import client hỗ trợ update theo `client_code` (upsert theo nghiệp vụ), nhưng nếu trùng email sai khách có thể báo duplicate.

### A.3 Inventory

- **File đang có:** `Quote, unit price, inventory.xlsx`, `Craveva fullinventory.xlsx`.
- **Đánh giá:** chưa đủ spec mapping để import tự động ổn định (đặc biệt nhiều cột kỳ kế toán: opening/in/out/closing).
- **Lưu ý kỹ thuật (cập nhật 2026-03):** Luồng **Purchase Inventory** đã có chọn `warehouse_id` và đồng bộ movement đa kho; khi đọc code/job cũ, vẫn kiểm tra từng job import xem đã truyền `warehouse_id` đầy đủ chưa. Xem thêm `FUNC_LOGIC/MAOLIN_IMPORT_MAPPING.md` và `WAREHOUSE_UI_OPERATIONS_GUIDE.md`.

### A.4 Export hiện trạng cho demo “buổi tối”

- **Chưa thấy bộ export chuẩn Product/Client/Inventory** (CSV/XLSX) trong `app/Exports` hoặc `Modules/Purchase/Exports`.
- `app/Exports` hiện chủ yếu cho attendance/timelog/leave/deal report, chưa có export domain sync Product/Client/Inventory.

---

## B. Thiếu field / thiếu dữ liệu

### B.1 Product (chuẩn sync 2 chiều)

- Thiếu trong file hiện tại:
    - `price` (hoặc `standard_price`) — quan trọng
    - `price_per_box`
    - `product_source`
    - `product_category`, `product_sub_category`
    - `status` (active/inactive)
    - `updated_at` hoặc `source_last_updated_at` để đồng bộ delta

### B.2 Inventory

- Thiếu/cần chuẩn hóa:
    - `warehouse_code` (bắt buộc nếu multi-warehouse)
    - `sku` (bắt buộc, không nên chỉ dựa product_name)
    - `on_hand` (tồn hiện tại) và/hoặc `movement_qty` (biến động)
    - `batch_number`, `expiration_date` (nếu áp dụng FEFO)
    - `as_of_datetime` (thời điểm snapshot tồn)
    - `source_doc_no` / `reference_type`

### B.3 Client

- Thiếu/cần thống nhất:
    - `status` (active/inactive)
    - `payment_terms_code` (nếu có dictionary)
    - `salesperson_code` (nếu cần map nhân sự ổn định)
    - `updated_at` để sync delta

---

## C. Vấn đề về mapping

### C.1 Product ↔ Inventory

- Key đề xuất: **`sku`** (duy nhất theo company).
- Rủi ro hiện tại:
    - Inventory import fallback theo `product_name` nếu không có SKU -> dễ sai khi trùng tên.
    - Unit có thể bị auto-create khi import -> làm bẩn master nếu typo.

### C.2 Client ↔ Order (tương lai)

- Key đề xuất: **`client_code`** (duy nhất theo company), không dùng name để join.
- Hiện trạng:
    - Client import có update theo `client_code`.
    - Mapping từ chatbot/order tương lai cần chốt rõ client identity (client_code, phone, channel id).

### C.3 Upsert / overwrite rule

- **Product import:** hiện tại thiên về **insert mới**, SKU trùng thì **skip** (không update record cũ).
- **Client import:** có **update** khi trùng `client_code`.
- **Inventory import:** tạo chứng từ điều chỉnh tồn, không phải upsert snapshot chuẩn; cần rule riêng cho demo.

### C.4 Soft delete / versioning / timestamp

- **Soft delete:** Chưa rõ bắt buộc cho demo sync.
- **Versioning:** Chưa có cơ chế version file/dataset chuẩn cho 3 domain.
- **Timestamp sync:** Nên bắt buộc có `updated_at` (hoặc `source_updated_at`) để incremental sync.

---

## D. Đề xuất format chuẩn

## D.1 Product (CSV/XLSX)

| Cột                | Kiểu          | Bắt buộc | Ghi chú                  |
| ------------------ | ------------- | -------- | ------------------------ |
| product_code (SKU) | string        | Yes      | unique theo company      |
| product_name       | string        | Yes      |                          |
| unit_code          | string        | Yes      | map unit chuẩn           |
| standard_price     | decimal(15,4) | Yes      | dùng cho import hiện tại |
| price_per_box      | decimal(15,4) | No       |                          |
| product_source     | string        | No       |                          |
| brand              | string        | No       |                          |
| product_grade      | string        | No       |                          |
| category_code      | string        | No       | khuyến nghị có           |
| sub_category_code  | string        | No       |                          |
| inventory_type     | string        | No       | enum nội bộ              |
| storage_condition  | string        | No       |                          |
| shelf_life_days    | int           | No       |                          |
| status             | string        | Yes      | active/inactive          |
| source_updated_at  | datetime      | Yes      | phục vụ sync delta       |

## D.2 Inventory (CSV/XLSX)

| Cột                | Kiểu          | Bắt buộc | Ghi chú                          |
| ------------------ | ------------- | -------- | -------------------------------- |
| warehouse_code     | string        | Yes      | key kho                          |
| product_code (SKU) | string        | Yes      | map product                      |
| on_hand_qty        | decimal(15,4) | Yes      | tồn tại thời điểm chốt           |
| reserved_qty       | decimal(15,4) | No       | nếu có reservation               |
| available_qty      | decimal(15,4) | No       | có thể tính = on_hand - reserved |
| unit_code          | string        | Yes      |                                  |
| batch_number       | string        | No       |                                  |
| expiration_date    | date          | No       |                                  |
| movement_type      | string        | No       | inbound/outbound/adjustment      |
| movement_qty       | decimal(15,4) | No       | dùng nếu file theo biến động     |
| source_doc_no      | string        | No       | PO/DO/INV ref                    |
| as_of_datetime     | datetime      | Yes      | thời điểm snapshot               |
| source_updated_at  | datetime      | Yes      |                                  |

## D.3 Client (CSV/XLSX)

| Cột                       | Kiểu     | Bắt buộc | Ghi chú             |
| ------------------------- | -------- | -------- | ------------------- |
| client_code               | string   | Yes      | unique theo company |
| client_name               | string   | Yes      |                     |
| tax_id                    | string   | No       |                     |
| phone_1                   | string   | No       |                     |
| phone_2                   | string   | No       |                     |
| address                   | string   | No       |                     |
| region                    | string   | No       | map từ 地區別       |
| designated_warehouse_code | string   | No       | map từ 指定庫別名稱 |
| payment_terms             | string   | No       |                     |
| channel_type              | string   | No       |                     |
| business_type             | string   | No       |                     |
| status                    | string   | Yes      | active/inactive     |
| source_updated_at         | datetime | Yes      | sync delta          |

---

## E. Câu hỏi cho khách hàng

1. ERP Maolin dùng key chính nào cho Product/Client/Inventory? (`product_code`, `client_code`, `warehouse_code`?)
2. Inventory file họ gửi là **snapshot tồn cuối ngày** hay **movement trong ngày**?
3. Nếu cùng SKU ở nhiều kho: có file master kho (`warehouse_code`) chính thức chưa?
4. Rule update Product: SKU trùng thì update field nào, field nào không được overwrite?
5. Rule update Client: cho phép đổi `client_name`, `tax_id`, phone không?
6. Khi record không còn tồn tại ở ERP nguồn thì bên Craveva xử lý thế nào (inactive hay giữ nguyên)?
7. Giá áp dụng lấy từ file nào là nguồn chuẩn (product file hay quote/inventory file)?
8. Cần hỗ trợ tier pricing theo khách trong demo không?
9. Mốc thời gian đồng bộ dùng timezone nào? có yêu cầu cutoff cụ thể (vd 23:00) không?
10. Có cần checksum/file version để tránh import trùng file cùng ngày không?

---

## F. Rủi ro khi demo

### F.1 Lỗi có thể xảy ra ngay

- Import Product fail nếu thiếu cột giá (`price`/`standard_price`).
- Inventory import thiếu `warehouse_id` / sai map `warehouse_code` → tồn kho lệch (luồng UI đã có chọn kho; job/import cần kiểm tra tương đương).
- SKU không chuẩn (space/case) -> tạo product mới ngoài ý muốn hoặc không match tồn.
- Date format Excel không đồng nhất -> parse sai `manufacturing_date`/`expiration_date`.

### F.2 Điểm cần fix gấp trước demo

1. Chốt **template chuẩn v1** cho 3 file (Product/Inventory/Client) theo bảng D.
2. Đảm bảo mọi kênh import tồn đều map `warehouse_code -> warehouse_id` nhất quán (đối chiếu template trong `MAOLIN_IMPORT_MAPPING.md`).
3. Quy định rõ rule upsert:
    - Product: insert hay update theo SKU?
    - Client: update theo client_code (đã có), cần thêm policy field-level overwrite.
    - Inventory: snapshot overwrite hay movement append?
4. Tạo bộ **export chuẩn** (nightly) cho đúng format import ERP Maolin.
5. Thêm log run-id cho từng job (file_name, started_at, row_count, success_count, failed_count).

### F.3 Mức độ sẵn sàng demo

- **Demo kỹ thuật import sáng:** khả thi nếu chuẩn hóa template và khoá rule.
- **Demo full vòng lặp sáng-import / tối-export:** **Chưa đủ** vì phần export chuẩn Product/Client/Inventory chưa thấy hoàn chỉnh trong code hiện tại.

---

## Kết luận ngắn

- Với hiện trạng hiện tại, có thể demo **nhập dữ liệu một chiều** tương đối nhanh.
- Để demo đúng vòng lặp hàng ngày 2 chiều, cần hoàn tất 3 phần tối thiểu: **template chuẩn**, **rule upsert rõ ràng**, **export chuẩn theo key ổn định**.
- Chỗ nào chưa có bằng chứng trong code đã được ghi rõ là **Chưa rõ** hoặc **Chưa thấy**.
