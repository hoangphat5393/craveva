# Đối chiếu Import với DB vs Custom Field – Miaolin (MAOLIN)

**Cập nhật 2026-03:** Tài liệu này bám **file Excel Miaolin cũ** (mẫu đầu dự án). Phần **kho / lô / map warehouse** đã được thay bằng triển khai đa kho trong code và ghi trong [`MAOLIN_INDEX.md`](MAOLIN_INDEX.md), [`MAOLIN_IMPORT_MAPPING.md`](MAOLIN_IMPORT_MAPPING.md), [`WAREHOUSE_CUSTOM_FIELDS_RATIONALIZATION.md`](WAREHOUSE_CUSTOM_FIELDS_RATIONALIZATION.md). Giữ file này cho **bối cảnh ngành + bảng cột mẫu cũ**.

**Mục đích:** So sánh cột trong file Miaolin Customer.xlsx / Miaolin Product.xlsx với cấu trúc hệ thống (bảng DB + custom field), phân loại **trường bắt buộc (DB)** và **chỉ cần custom field**, đồng thời nêu ngành nghề MAOLIN và vai trò ERP.

**Dữ liệu mẫu đã đọc:** 5 dòng đầu (header + 4 data) từ cả hai file Excel.

---

## 1. Công ty MAOLIN kinh doanh gì? ERP dùng để làm gì?

### 1.1. Từ dữ liệu Miaolin Product.xlsx

- **Sản phẩm:** Bột mì, bột bánh (日清山茶花強力粉 = Nisshin flour), đơn vị 包 (bao), KG, quy cách 25KG/包, 1KG分裝.
- **Thuộc tính:** 商品級別 (S+級), 商品來源 (自行進口商品, 分裝品), 品牌 (日清製粉), 保存天數 (270, 240, 180 ngày), 備貨型態 (常備), 儲存溫層 (常溫), 標準價 / 成箱價.

→ **Ngành:** Kinh doanh **nguyên liệu thực phẩm / bán thành phẩm** (bột mì, nguyên liệu làm bánh), nhập/分裝 (chia gói) và bán B2B.

### 1.2. Từ dữ liệu Miaolin Customer.xlsx

- **Khách hàng:** 客戶代號 (A0000, A0001...), 客戶簡稱 (B2B測試, B2B烘焙展1), 業務員, 部門 (業務部-苗實, 資訊總務部), 通路別 (西式烘焙業), 型態別 (西式麵包店), 送貨地址, 交易條件 (月結30天, 貨到收款).

→ **Khách hàng:** Chủ yếu là **doanh nghiệp** (B2B): tiệm bánh, công ty F&B, có mã khách, địa chỉ giao hàng, điều khoản thanh toán.

### 1.3. Kết luận

| Nội dung          | Mô tả                                                                                                                                                                                                                                   |
| ----------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **MAOLIN (苗林)** | Công ty kinh doanh **nguyên liệu** (bột, nguyên liệu làm bánh, F&B) bán B2B cho tiệm bánh, công ty thực phẩm.                                                                                                                           |
| **ERP hiện tại**  | Dùng để **lưu và quản lý nguyên liệu (product)** (SKU, quy cách, đơn vị, giá, tồn kho, hạn dùng, nguồn hàng, thương hiệu) và **khách hàng (client)** (mã KH, tên, địa chỉ giao hàng, nhân viên phụ trách, điều khoản thanh toán, v.v.). |

---

## 2. Miaolin Customer.xlsx – Cột file vs Hệ thống

### 2.1. Header và dữ liệu mẫu (đã đọc từ file)

| Cột (index) | Header trong file                    | Ví dụ giá trị                                      |
| ----------- | ------------------------------------ | -------------------------------------------------- |
| 0           | 客戶代號 \| Customer Code            | 1, A0000, A0001, A0002                             |
| 1           | 客戶簡稱 \| Customer Short Name      | 新客戶(營業目標用), B2B測試, B2B烘焙展1, PM 鎖貨用 |
| 2           | 業務員 \| Salesperson                | 苗實, 員購, 苗實                                   |
| 3           | 部門 \| Department                   | 業務部-苗實, 資訊總務部, 業務部                    |
| 4           | 業務助理名稱 \| Sales Assistant Name | null, 林克軒, null                                 |
| 5           | 客戶(集團)分級 \| Customer Grade     | C級客戶, C級客戶, null                             |
| 6           | 通路別 \| Channel Type               | null, 西式烘焙業, 西式麵包店                       |
| 7           | 型態別 \| Business Type              | null, 西式烘焙業, 西式麵包店                       |
| 8           | 最近交易 \| Last Transaction Date    | null, 20240513, null                               |
| 9           | 統一編號 \| Tax ID                   | null, null, null                                   |
| 10          | TEL_NO(一) \| Phone 1                | null, 0955-538238, 0955-538238                     |
| 11          | TEL_NO(二) \| Phone 2                | null, 02-26589848#219, 02-26589848#219             |
| 12          | 送貨地址 \| Delivery Address         | null, 114臺北市…, 114臺北市…                       |
| 13          | 交易條件 \| Payment Terms            | 貨到收款, 月結30天, 貨到收款                       |
| 14          | 歇業日期 \| Business Closure Date    | null, null, null                                   |

**Ghi chú:** File **không có** cột email, tên đầy đủ (chỉ có 客戶簡稱). Hệ thống bắt buộc **name** → dùng 客戶簡稱 (Customer Short Name) làm tên hiển thị là phù hợp.

### 2.2. Trường nhất thiết cần (DB – bảng users + client_details)

Các trường dùng trong nghiệp vụ cốt lõi: tìm kiếm, trùng lặp, địa chỉ, liên hệ, thuế. **Phải** có cột trong DB.

| Trường hệ thống            | Bảng           | Cột file Miaolin tương ứng      | Bắt buộc?                                         |
| -------------------------- | -------------- | ------------------------------- | ------------------------------------------------- |
| **name** (users)           | users          | 客戶簡稱 \| Customer Short Name | **Có** (required)                                 |
| **client_code**            | client_details | 客戶代號 \| Customer Code       | Nên có (unique theo company, dùng kiểm tra trùng) |
| **company_name**           | client_details | Có thể = 客戶簡稱 hoặc để trống | Không                                             |
| **email**                  | users          | (File không có cột email)       | Không                                             |
| **mobile**                 | users          | TEL_NO(一) hoặc TEL_NO(二)      | Không                                             |
| **office** (company_phone) | client_details | TEL_NO(一) / TEL_NO(二)         | Không                                             |
| **address**                | client_details | 送貨地址 \| Delivery Address    | Không (nhưng quan trọng cho giao hàng)            |
| **gst_number**             | client_details | 統一編號 \| Tax ID              | Không                                             |

**Tóm tắt DB (bắt buộc / nên có):**

- **Bắt buộc:** `name` (users) — map từ 客戶簡稱.
- **Nên có:** `client_code` (client_details) — map từ 客戶代號; `address` (client_details) — map từ 送貨地址; `office` hoặc `mobile` — map từ TEL_NO(一)/(二); `gst_number` — map từ 統一編號.

### 2.3. Trường chỉ cần Custom Field (đã có trong hệ thống)

Các trường nghiệp vụ đặc thù công ty, không dùng làm khóa hay filter cốt lõi. Hệ thống **đã có** Custom Field Group "Client" với các field sau (migration 2026_03_09):

| Trường custom (name)  | Label                 | Cột file Miaolin                     | Ghi chú                  |
| --------------------- | --------------------- | ------------------------------------ | ------------------------ |
| salesperson           | Salesperson           | 業務員 \| Salesperson                | ✅ Có trong ClientImport |
| department            | Department            | 部門 \| Department                   | ✅ Có trong ClientImport |
| sales_assistant_name  | Sales Assistant Name  | 業務助理名稱 \| Sales Assistant Name | ✅ Có trong ClientImport |
| channel_type          | Channel Type          | 通路別 \| Channel Type               | ✅ Có trong ClientImport |
| business_type         | Business Type         | 型態別 \| Business Type              | ✅ Có trong ClientImport |
| last_transaction_at   | Last Transaction Date | 最近交易 \| Last Transaction Date    | ✅ Có trong ClientImport |
| payment_terms         | Payment Terms         | 交易條件 \| Payment Terms            | ✅ Có trong ClientImport |
| business_closure_date | Business Closure Date | 歇業日期 \| Business Closure Date    | ✅ Có trong ClientImport |

→ Tất cả các cột nghiệp vụ đặc thù trên **chỉ cần lưu qua custom field**; không cần thêm cột DB.

### 2.4. Cột trong file chưa có trong ClientImport

| Cột file                                          | Đề xuất                                                                                                                                                           |
| ------------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **客戶(集團)分級 \| Customer Grade** (C級客戶, …) | Có thể thêm **custom field** "customer_grade" (hoặc "Customer Grade") cho Client group nếu MAOLIN cần import cấp độ khách hàng. Hiện ClientImport chưa có id này. |

### 2.5. Bảng tổng hợp – Client

| Cột file (Miaolin Customer) | Trường hệ thống       | DB hay Custom?                               |
| --------------------------- | --------------------- | -------------------------------------------- |
| 客戶代號                    | client_code           | **DB** (client_details)                      |
| 客戶簡稱                    | name                  | **DB** (users) – bắt buộc                    |
| (không có)                  | company_name          | **DB** (client_details) – có thể = name      |
| (không có)                  | email                 | **DB** (users) – optional                    |
| TEL_NO(一),(二)             | mobile / office       | **DB** (users.mobile, client_details.office) |
| 送貨地址                    | address               | **DB** (client_details)                      |
| 統一編號                    | gst_number            | **DB** (client_details)                      |
| 業務員                      | salesperson           | **Custom**                                   |
| 部門                        | department            | **Custom**                                   |
| 業務助理名稱                | sales_assistant_name  | **Custom**                                   |
| 客戶(集團)分級              | (chưa map)            | Có thể **Custom** (customer_grade)           |
| 通路別                      | channel_type          | **Custom**                                   |
| 型態別                      | business_type         | **Custom**                                   |
| 最近交易                    | last_transaction_at   | **Custom**                                   |
| 交易條件                    | payment_terms         | **Custom**                                   |
| 歇業日期                    | business_closure_date | **Custom**                                   |

---

## 3. Miaolin Product.xlsx – Cột file vs Hệ thống

### 3.1. Header và dữ liệu mẫu (đã đọc từ file)

| Cột | Header trong file             | Ví dụ giá trị                           |
| --- | ----------------------------- | --------------------------------------- |
| 0   | 品號 \| SKU                   | A0101001, A0101002, A0101003            |
| 1   | 品名 \| Product Name          | 日清山茶花強力粉25K, 日清山茶花強力粉1K |
| 2   | 規格 \| Specification         | 25KG/包, 1KG分裝, 25KG/包               |
| 3   | 庫存單位 \| Unit Type         | 包, KG                                  |
| 4   | 商品級別 \| Product Grade     | S+級                                    |
| 5   | 商品來源 \| Product Origin    | 自行進口商品, 分裝品                    |
| 6   | 品牌類別 \| Brand             | 日清製粉                                |
| 7   | 保存天數 \| Shelf Life (days) | 270, 240, 180                           |
| 8   | 備貨型態 \| Inventory Type    | 常備                                    |
| 9   | 儲存溫層 \| Storage Condition | 常溫                                    |
| 10  | 標準價 \| Standard Price      | 1530, 100, 1500, 1950                   |
| 11  | 成箱價 \| Price per box       | null                                    |

### 3.2. Trường nhất thiết cần (DB – bảng products)

Toàn bộ cột trong Miaolin Product.xlsx đều đã được thiết kế map vào **cột DB** của bảng `products` (và `unit_types`), **không** dùng custom field cho product khi import.

| Cột file | Trường DB (products / unit_types) | Ghi chú                                  |
| -------- | --------------------------------- | ---------------------------------------- |
| 品號     | sku                               | Unique, tìm kiếm, trùng lặp              |
| 品名     | name (product_name)               | Bắt buộc                                 |
| 規格     | specification                     | Cột DB (migration 2026_03_11)            |
| 庫存單位 | unit_id (qua unit_type name)      | unit_types, dùng trong đơn hàng/tồn kho  |
| 商品級別 | product_grade                     | **Cột DB** (migration 2026_03_11_000003) |
| 商品來源 | product_source                    | **Cột DB** (migration 2026_03_11_000003) |
| 品牌類別 | brand                             | **Cột DB** (migration 2026_03_11_000003) |
| 保存天數 | shelf_life_days                   | Cột DB                                   |
| 備貨型態 | inventory_type                    | Cột DB                                   |
| 儲存溫層 | storage_condition                 | Cột DB                                   |
| 標準價   | price                             | Cột DB (standard_price → price)          |
| 成箱價   | price_per_box                     | Cột DB                                   |

→ **Product:** Tất cả các trường cần cho nguyên liệu (SKU, tên, quy cách, đơn vị, cấp/nguồn/thương hiệu, hạn dùng, loại tồn, điều kiện bảo quản, giá) đều **nhất thiết** nằm ở DB; **không** cần custom field cho các cột Miaolin Product hiện tại.

### 3.3. Trường chỉ cần Custom Field – Product

- Với **Miaolin Product.xlsx** và cấu trúc hiện tại: **không có** cột nào cần lưu **chỉ** bằng custom field. product_grade, product_source, brand đã là cột DB.
- Nếu sau này có thêm thuộc tính đặc thù (vd. chứng nhận, nhà cung cấp mặc định) có thể bổ sung custom field cho Product, nhưng **không** ảnh hưởng mapping file Miaolin hiện tại.

### 3.4. Bảng tổng hợp – Product

| Cột file (Miaolin Product) | Trường hệ thống        | DB hay Custom?      |
| -------------------------- | ---------------------- | ------------------- |
| 品號                       | sku                    | **DB**              |
| 品名                       | name (product_name)    | **DB**              |
| 規格                       | specification          | **DB**              |
| 庫存單位                   | unit_type → unit_id    | **DB** (unit_types) |
| 商品級別                   | product_grade          | **DB**              |
| 商品來源                   | product_source         | **DB**              |
| 品牌類別                   | brand                  | **DB**              |
| 保存天數                   | shelf_life_days        | **DB**              |
| 備貨型態                   | inventory_type         | **DB**              |
| 儲存溫層                   | storage_condition      | **DB**              |
| 標準價                     | price (standard_price) | **DB**              |
| 成箱價                     | price_per_box          | **DB**              |

---

## 4. Tóm tắt: Trường nhất thiết DB vs Chỉ cần Custom

### 4.1. Client (Miaolin Customer)

| Loại                   | Trường                                                                                                                                | Ghi chú                                                           |
| ---------------------- | ------------------------------------------------------------------------------------------------------------------------------------- | ----------------------------------------------------------------- |
| **Nhất thiết DB**      | name (users), client_code (client_details)                                                                                            | name bắt buộc; client_code dùng kiểm tra trùng theo company.      |
| **Nên có DB**          | address, office/mobile, gst_number                                                                                                    | Địa chỉ giao hàng, SĐT, mã số thuế — dùng nhiều trong giao dịch.  |
| **Chỉ cần Custom**     | salesperson, department, sales_assistant_name, channel_type, business_type, last_transaction_at, payment_terms, business_closure_date | Đã có trong Client group; import qua custom_fields_data.          |
| **Có thể thêm Custom** | 客戶(集團)分級 (Customer Grade)                                                                                                       | Thêm custom field "customer_grade" nếu cần import cấp khách hàng. |

### 4.2. Product (Miaolin Product)

| Loại               | Trường                                                                                       | Ghi chú                                                          |
| ------------------ | -------------------------------------------------------------------------------------------- | ---------------------------------------------------------------- |
| **Nhất thiết DB**  | sku, name, specification, unit_id, price, shelf_life_days, inventory_type, storage_condition | Core cho nguyên liệu: định danh, đơn vị, giá, tồn kho, bảo quản. |
| **DB (đã có)**     | product_grade, product_source, brand, price_per_box                                          | Đã là cột DB, không dùng custom field.                           |
| **Chỉ cần Custom** | (không có với file Miaolin Product hiện tại)                                                 | Toàn bộ cột file đã map DB.                                      |

---

## 5. Mapping header file ↔ Import (để map đúng khi header tiếng Trung/empty)

- **Client:** Header file dạng "客戶代號 | Customer Code" — form import cần map theo **id** (client_code, name, salesperson, …). Nếu heading formatter biến header Trung thành empty, cần cho phép map theo vị trí cột hoặc giữ nhãn song ngữ (như trong file) để user chọn đúng cột.
- **Product:** Tương tự, "品號 | SKU", "品名 | Product Name", … — ProductImport::fields() đã có product_name, sku, specification, unit_type, product_grade, product_source, brand, shelf_life_days, inventory_type, storage_condition, price (standard_price), price_per_box. Cần đảm bảo header trong file (hoặc nhãn song ngữ) có thể khớp với các id này (xem thêm MIAOLIN_PRODUCT_IMPORT_COLUMNS_ANALYSIS.md).

---

## 6. Nguồn tham chiếu

| Nội dung              | File / vị trí                                                                                                                                                                |
| --------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Client import fields  | `App\Imports\ClientImport::fields()`                                                                                                                                         |
| Client processor      | `App\Services\ClientImportProcessor::processRow()`, `saveCustomFieldsFromRow()`                                                                                              |
| Client DB             | users (name, email, mobile); client_details (client_code, company_name, address, city, state, postal_code, office, gst_number, …)                                            |
| Client custom group   | CustomFieldGroup name = "Client", model = ClientDetails::CUSTOM_FIELD_MODEL                                                                                                  |
| Product import fields | `App\Imports\ProductImport::fields()`                                                                                                                                        |
| Product DB            | products (sku, name, description, specification, unit_id, product_grade, product_source, brand, shelf_life_days, inventory_type, storage_condition, price, price_per_box, …) |
| Product custom        | Hiện không dùng custom field cho các cột Miaolin Product (đã đủ cột DB).                                                                                                     |
