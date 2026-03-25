# Đối chiếu file tài liệu mới (PROJECT MAOLIN New) với hệ thống

**Ngày:** 2026-03-13  
**Thư mục:** `PROJECT MAOLIN New/`  
**Mục đích:** Liệt kê nội dung các file mới khách gửi, đối chiếu với trường trong hệ thống (DB + custom field), nêu còn thiếu gì.

**Mục lục MAOLIN (điểm vào, thứ tự đọc):** **`FUNC_LOGIC/MAOLIN_INDEX.md`**.

**Tài liệu gộp MAOLIN (đọc 1 file là đủ):** xem **`FUNC_LOGIC/MAOLIN_MASTER_GUIDE.md`**.  
**Tóm tắt phạm vi 4 nhóm + file đã gộp:** xem **`FUNC_LOGIC/MAOLIN_INDEX.md`** (mục 2–3).  
**Phân tích Multi-Warehouse + roadmap triển khai end-to-end:** xem **`FUNC_LOGIC/WAREHOUSE_ANALYSIS_AND_PLAN.md`**.

---

## 1. Danh sách file trong PROJECT MAOLIN New

| File                                                                                                 | Mô tả (dựa trên tên)                                                                          |
| ---------------------------------------------------------------------------------------------------- | --------------------------------------------------------------------------------------------- |
| **Craveva customer.xlsx**                                                                            | Dữ liệu khách hàng (Customer)                                                                 |
| **Craveva product.xlsx**                                                                             | Thông tin sản phẩm (Product)                                                                  |
| **Craveva fullinventory.xlsx**                                                                       | Toàn bộ chi tiết tồn kho (Full inventory list)                                                |
| **Quote, unit price, inventory.xlsx**                                                                | Báo giá / Đơn giá / Tồn kho (Quote, unit price, inventory)                                    |
| **Last year net sales.xlsx**                                                                         | Doanh thu ròng năm gần nhất (Last year net sales)                                             |
| **[BRD] 後台：苗林 x Craveva 銷售流程整合需求規劃.docx**                                             | BRD – Yêu cầu tích hợp quy trình bán hàng (Backend: Miaolin x Craveva sales flow integration) |
| **苗林實業股份有限公司 B2B AI智慧分銷平台規劃書.docx**                                               | Kế hoạch nền tảng B2B (bản .docx)                                                             |
| **Miaolin Industrial Co., Ltd. B2B AI Smart Distribution Platform Planning Document (contract).pdf** | Hợp đồng / Kế hoạch nền tảng B2B (bản PDF tiếng Anh)                                          |

---

## 1.1. Phiên dịch các cột mới (chưa có trong hệ thống)

Các cột sau **có trong file khách gửi** nhưng **hệ thống hiện chưa có trường tương ứng**. Dùng bảng này để hiểu nghĩa tiếng Trung khi bàn với khách hoặc khi thêm custom field.

| Tiếng Trung      | Phiên âm (pinyin)        | Tiếng Việt                                      | English                   | File                          | Ghi chú                          |
| ---------------- | ------------------------ | ----------------------------------------------- | ------------------------- | ----------------------------- | -------------------------------- |
| **地區別**       | dìqū bié                 | **Khu vực** (phân theo vùng/địa lý)             | Region / Area type        | Craveva customer.xlsx (cột I) | VD: Bắc, Nam, Trung tâm…         |
| **指定庫別名稱** | zhǐdìng kù bié míngchēng | **Tên kho chỉ định** (kho giao hàng cho KH này) | Designated warehouse name | Craveva customer.xlsx (cột P) | Kho mặc định giao cho khách hàng |
| **失效日期**     | shīxiào rìqī             | **Ngày hết hạn** (của lô/sản phẩm)              | Expiry date               | Craveva product.xlsx (cột J)  | Ngày hết hạn sử dụng             |

**Cột file Customer cũ có nhưng file mới không có:**

| Tiếng Trung | Nghĩa (Việt / English) |
| ----------- | ---------------------- |
| 部門        | Phòng ban / Department |

---

## 2. Craveva customer.xlsx (Khách hàng)

### 2.1. Header đọc được (dòng 1)

| Cột | Header trong file (tiếng Trung) | Nghĩa (Việt / English)                             | Trường hệ thống tương ứng      | Ghi chú                       |
| --- | ------------------------------- | -------------------------------------------------- | ------------------------------ | ----------------------------- |
| A   | 客戶代號                        | Mã khách hàng / Customer code                      | client_code (DB)               | ✅ Có                         |
| B   | 客戶簡稱                        | Tên gọi tắt KH / Customer short name               | name (DB)                      | ✅ Có, bắt buộc               |
| C   | 統一編號                        | Mã số thuế / Tax ID (Unified number)               | gst_number (DB)                | ✅ Có                         |
| D   | 業務員                          | Nhân viên kinh doanh / Salesperson                 | salesperson (custom)           | ✅ Có                         |
| E   | 業務助理名稱                    | Tên trợ lý kinh doanh / Sales assistant name       | sales_assistant_name (custom)  | ✅ Có                         |
| F   | 客戶(集團)分級                  | Phân cấp khách (tập đoàn) / Customer (group) grade | customer_grade (custom)        | ✅ Có                         |
| G   | 通路別                          | Loại kênh / Channel type                           | channel_type (custom)          | ✅ Có                         |
| H   | 型態別                          | Loại hình kinh doanh / Business type               | business_type (custom)         | ✅ Có                         |
| I   | **地區別**                      | **Khu vực / Region**                               | —                              | ⚠️ **Chưa có** trong hệ thống |
| J   | 送貨地址                        | Địa chỉ giao hàng / Delivery address               | address (DB)                   | ✅ Có                         |
| K   | TEL_NO(一)                      | Số điện thoại 1 / Phone 1                          | mobile (DB)                    | ✅ Có                         |
| L   | TEL_NO(二)                      | Số điện thoại 2 / Phone 2                          | company_phone → office (DB)    | ✅ Có                         |
| M   | 交易條件                        | Điều khoản thanh toán / Payment terms              | payment_terms (custom)         | ✅ Có                         |
| N   | 最近交易                        | Giao dịch gần nhất / Last transaction (date)       | last_transaction_at (custom)   | ✅ Có                         |
| O   | 歇業日期                        | Ngày đóng cửa / Business closure date              | business_closure_date (custom) | ✅ Có                         |
| P   | **指定庫別名稱**                | **Tên kho chỉ định / Designated warehouse name**   | —                              | ⚠️ **Chưa có** trong hệ thống |

### 2.2. So với file Miaolin Customer cũ (tài liệu trước)

| Nội dung                  | Tiếng Trung  | Nghĩa            | File cũ    | File mới (Craveva customer.xlsx) | Ghi chú                           |
| ------------------------- | ------------ | ---------------- | ---------- | -------------------------------- | --------------------------------- |
| Department                | 部門         | Phòng ban        | Có (cột 3) | **Không có**                     | ❌ File mới **thiếu** cột 部門    |
| Region                    | 地區別       | Khu vực          | Không      | **Có (cột I)**                   | Hệ thống chưa có trường tương ứng |
| Designated warehouse name | 指定庫別名稱 | Tên kho chỉ định | Không      | **Có (cột P)**                   | Hệ thống chưa có trường tương ứng |

### 2.3. Kết luận – Khách hàng

- **Đủ cho import cơ bản:** client_code, name, gst_number, address, TEL_NO(一)/(二), và toàn bộ custom field hiện có (salesperson, sales_assistant_name, customer_grade, channel_type, business_type, payment_terms, last_transaction_at, business_closure_date) đều có cột tương ứng, trừ **部門**.
- **Thiếu so với hệ thống (khi import file mới):**
    - **部門 (Department):** File mới không có cột này → import sẽ để trống department.
- **Thừa so với hệ thống (chưa map):**
    - **地區別 (Khu vực / Region):** Cần thêm custom field (vd. `region`) nếu muốn lưu.
    - **指定庫別名稱 (Tên kho chỉ định / Designated warehouse name):** Cần thêm custom field (vd. `designated_warehouse`) nếu muốn lưu.

---

## 3. Craveva product.xlsx (Sản phẩm)

### 3.1. Header đọc được

| Cột | Header trong file (tiếng Trung) | Nghĩa (Việt / English)                 | Trường hệ thống        | Ghi chú                                                       |
| --- | ------------------------------- | -------------------------------------- | ---------------------- | ------------------------------------------------------------- |
| A   | 品號                            | Mã sản phẩm / SKU                      | sku (DB)               | ✅ Có                                                         |
| B   | 品名                            | Tên sản phẩm / Product name            | product_name (DB)      | ✅ Có                                                         |
| C   | 規格                            | Quy cách / Specification               | specification (DB)     | ✅ Có                                                         |
| D   | 庫存單位                        | Đơn vị tồn kho / Stock unit            | unit_type (DB)         | ✅ Có                                                         |
| E   | 商品級別                        | Cấp hàng / Product grade               | product_grade (DB)     | ✅ Có                                                         |
| F   | 品牌類別                        | Thương hiệu / Brand                    | brand (DB)             | ✅ Có                                                         |
| G   | 保存天數                        | Số ngày bảo quản / Shelf life (days)   | shelf_life_days (DB)   | ✅ Có                                                         |
| H   | 備貨型態                        | Loại dự trữ / Inventory type           | inventory_type (DB)    | ✅ Có                                                         |
| I   | 儲存溫層                        | Điều kiện bảo quản / Storage condition | storage_condition (DB) | ✅ Có                                                         |
| J   | **失效日期**                    | **Ngày hết hạn / Expiry date**         | —                      | Chưa có trong hệ thống; có thể map description hoặc cột riêng |

### 3.2. So với file Miaolin Product cũ / hệ thống

| Trường         | Tiếng Trung | Nghĩa          | File cũ / Hệ thống | File mới (Craveva product.xlsx) | Ghi chú                               |
| -------------- | ----------- | -------------- | ------------------ | ------------------------------- | ------------------------------------- |
| product_source | 商品來源    | Nguồn hàng     | Có                 | **Không có**                    | ❌ File mới không có cột này          |
| price          | 標準價      | Giá tiêu chuẩn | Có                 | **Không có**                    | ❌ File mới không có giá              |
| price_per_box  | 成箱價      | Giá theo thùng | Có                 | **Không có**                    | ❌ File mới không có giá              |
| Expiry date    | 失效日期    | Ngày hết hạn   | —                  | Có (cột J)                      | Hệ thống có thể chưa có cột tương ứng |

### 3.3. Hãng (brand) và Danh mục (category) – đã có chưa?

| Thông tin               | Trong hệ thống (DB)                                                                        | Trong file Craveva product.xlsx               | Kết luận                                                                                      |
| ----------------------- | ------------------------------------------------------------------------------------------ | --------------------------------------------- | --------------------------------------------------------------------------------------------- |
| **Hãng (brand)**        | ✅ Có — cột `products.brand`, import map qua field `brand`                                 | ✅ Có — cột **品牌類別** (cột F)              | File có hãng; map 品牌類別 → `brand` là đủ.                                                   |
| **Danh mục (category)** | ✅ Có — `category_id`, `sub_category_id` (bảng `product_category`, `product_sub_category`) | ❌ **Chưa có** — không có cột 品類 / 商品類別 | File **không có** danh mục; muốn có thì khách thêm cột vào Excel hoặc gán sau trong hệ thống. |

**Tóm tắt:** Trong **hệ thống** sản phẩm đã có đầy đủ **hãng** và **danh mục**. Trong **file Excel** khách gửi: đã có **hãng (品牌類別)**, **chưa có** cột **danh mục** (category).

### 3.4. Kết luận – Sản phẩm

- File **Craveva product.xlsx** giống **master sản phẩm** (mã, tên, quy cách, đơn vị, cấp, thương hiệu, bảo quản, tồn) **không kèm giá**.
- **Thiếu so với mapping Product hiện tại:** product_source (商品來源), price (標準價), price_per_box (成箱價). Nếu import chỉ file này thì giá sẽ trống; giá có thể nằm ở file **Quote, unit price, inventory.xlsx**.
- **Danh mục:** File không có cột category → khi import, category_id/sub_category_id sẽ trống trừ khi khách thêm cột hoặc cập nhật sau.
- **失效日期 (Ngày hết hạn / Expiry date):** Cần quyết định map vào cột DB (nếu có) hay custom field.

---

## 4. Quote, unit price, inventory.xlsx (Báo giá / Đơn giá / Tồn kho)

Header đọc được (kèm nghĩa): 品號 (SKU), 品名 (Tên SP), 規格 (Quy cách), 單位 (Đơn vị), 小單位 (Đơn vị nhỏ), 包裝單位 (Đơn vị đóng gói), 批號 (Số lô), 有效日期 (Ngày hiệu lực), 製造日期 (Ngày sản xuất), 結案碼 (Mã kết thúc), 庫別 (Mã kho), 庫別名稱 (Tên kho), 期初庫存 (Tồn đầu kỳ), 本期入庫 (Nhập trong kỳ), 本期出庫 (Xuất trong kỳ), 期末庫存 (Tồn cuối kỳ), 期初包裝庫存 (Tồn đóng gói đầu kỳ), 本期包裝入庫 (Nhập đóng gói kỳ), 本期包裝出庫 (Xuất đóng gói kỳ), 期末包裝庫存 (Tồn đóng gói cuối kỳ), 最近入庫日 (Ngày nhập gần nhất), 批號最近入庫日 (Lô nhập gần nhất).

- File này là **tồn kho + có thể đơn giá**, cấu trúc khác với **Craveva product.xlsx** (không phải 1 row = 1 product đơn giản). Cần tài liệu riêng hoặc rule nghiệp vụ để map vào product (sku/品號) + inventory/price module (nếu có).

---

## 5. Craveva full inventory.xlsx (Toàn bộ tồn kho chi tiết)

- Dòng 1 đọc được toàn empty → có thể header ở dòng khác hoặc nhiều sheet. Cần mở file trực tiếp để xác định cấu trúc và đối chiếu với module tồn kho / báo giá.

---

## 6. Last year net sales.xlsx (Doanh thu ròng năm gần nhất)

- Chưa đọc được (script lỗi bộ nhớ khi xử lý file lớn). Nhiều khả năng là **báo cáo doanh thu** (sales), không phải master Client/Product. Cần xác định sau: dùng cho báo cáo hay cần import vào bảng nào.

---

## 7. Tóm tắt: Còn thiếu / cần bổ sung

### 7.1. Trường hệ thống cần dùng khi import file mới

| Loại        | Trường                                                          | Trạng thái                                                                                 |
| ----------- | --------------------------------------------------------------- | ------------------------------------------------------------------------------------------ |
| **Client**  | Toàn bộ DB + custom hiện có (trừ department)                    | Đủ cột trong file 客戶資料, trừ 部門                                                       |
| **Client**  | 部門 (Phòng ban / Department)                                   | ❌ File mới **không có** cột 部門                                                          |
| **Client**  | 地區別 (Khu vực / Region)                                       | ⚠️ File có, hệ thống **chưa có** → cần thêm custom field nếu muốn lưu                      |
| **Client**  | 指定庫別名稱 (Tên kho chỉ định / Designated warehouse name)     | ⚠️ File có, hệ thống **chưa có** → cần thêm custom field nếu muốn lưu                      |
| **Product** | product_source, price, price_per_box (商品來源, 標準價, 成箱價) | ❌ File Craveva product.xlsx **không có**; có thể lấy từ Quote, unit price, inventory.xlsx |
| **Product** | 失效日期 (Ngày hết hạn / Expiry date)                           | File có; cần quyết định map vào cột DB hay custom field                                    |

### 7.2. Gợi ý bước tiếp theo

1. **Client**
    - Nếu khách cần import **部門 (Phòng ban / Department):** yêu cầu bổ sung cột 部門 vào **Craveva customer.xlsx**, hoặc lấy từ nguồn khác.
    - Nếu cần lưu **地區別 (Khu vực)** và **指定庫別名稱 (Tên kho chỉ định):** thêm 2 custom field cho nhóm Client, sau đó thêm id tương ứng vào `ClientImport::fields()` và `getClientCustomFieldNames()`.

2. **Product**
    - **Craveva product.xlsx:** Import được master sản phẩm (sku, tên, quy cách, đơn vị, grade, brand, shelf life, inventory type, storage); giá để trống hoặc cập nhật sau.
    - Giá (và tồn chi tiết): xác định rule map từ **Quote, unit price, inventory.xlsx** (và nếu cần **Craveva fullinventory.xlsx**) vào module Product/Inventory/Pricing.

3. **File BRD / Hợp đồng**
    - Đọc [BRD] 後台：苗林 x Craveva 銷售流程整合需求規劃.docx và 規劃書 để bám đúng yêu cầu tích hợp và bổ sung trường/flow nếu cần.

---

## 8. Bảng tra cứu nhanh: Tiếng Trung → Việt / English (các cột trong file)

| Tiếng Trung              | Việt                                  | English                             |
| ------------------------ | ------------------------------------- | ----------------------------------- |
| 客戶代號                 | Mã khách hàng                         | Customer code                       |
| 客戶簡稱                 | Tên gọi tắt KH                        | Customer short name                 |
| 統一編號                 | Mã số thuế                            | Tax ID (unified number)             |
| 業務員                   | Nhân viên kinh doanh                  | Salesperson                         |
| 業務助理名稱             | Tên trợ lý kinh doanh                 | Sales assistant name                |
| 客戶(集團)分級           | Phân cấp khách (tập đoàn)             | Customer (group) grade              |
| 通路別                   | Loại kênh                             | Channel type                        |
| 型態別                   | Loại hình kinh doanh                  | Business type                       |
| 地區別                   | Khu vực                               | Region                              |
| 送貨地址                 | Địa chỉ giao hàng                     | Delivery address                    |
| TEL_NO(一) / (二)        | Số điện thoại 1 / 2                   | Phone 1 / Phone 2                   |
| 交易條件                 | Điều khoản thanh toán                 | Payment terms                       |
| 最近交易                 | Giao dịch gần nhất                    | Last transaction (date)             |
| 歇業日期                 | Ngày đóng cửa                         | Business closure date               |
| 指定庫別名稱             | Tên kho chỉ định                      | Designated warehouse name           |
| 部門                     | Phòng ban                             | Department                          |
| 品號                     | Mã sản phẩm                           | SKU / Product code                  |
| 品名                     | Tên sản phẩm                          | Product name                        |
| 規格                     | Quy cách                              | Specification                       |
| 庫存單位                 | Đơn vị tồn kho                        | Stock unit                          |
| 商品級別                 | Cấp hàng                              | Product grade                       |
| 品牌類別                 | Thương hiệu                           | Brand                               |
| 保存天數                 | Số ngày bảo quản                      | Shelf life (days)                   |
| 備貨型態                 | Loại dự trữ                           | Inventory type                      |
| 儲存溫層                 | Điều kiện bảo quản                    | Storage condition                   |
| 失效日期                 | Ngày hết hạn                          | Expiry date                         |
| 商品來源                 | Nguồn hàng                            | Product source                      |
| 標準價                   | Giá tiêu chuẩn                        | Standard price                      |
| 成箱價                   | Giá theo thùng                        | Price per box                       |
| 單位 / 小單位 / 包裝單位 | Đơn vị / Đơn vị nhỏ / Đơn vị đóng gói | Unit / Sub unit / Packing unit      |
| 批號                     | Số lô                                 | Batch number                        |
| 有效日期 / 製造日期      | Ngày hiệu lực / Ngày sản xuất         | Effective date / Manufacturing date |
| 庫別 / 庫別名稱          | Mã kho / Tên kho                      | Warehouse code / Warehouse name     |
| 期初庫存 / 期末庫存      | Tồn đầu kỳ / Tồn cuối kỳ              | Opening stock / Closing stock       |
| 本期入庫 / 本期出庫      | Nhập trong kỳ / Xuất trong kỳ         | Stock in / Stock out (period)       |

---

## 9. Nguồn tham chiếu

| Nội dung                           | File / vị trí                                                                          |
| ---------------------------------- | -------------------------------------------------------------------------------------- |
| Đối chiếu Client/Product (file cũ) | FUNC_LOGIC/MIAOLIN_IMPORT_FIELDS_DB_VS_CUSTOM_ANALYSIS.md                              |
| Client import fields               | App\Imports\ClientImport::fields(), ClientImportProcessor::getClientCustomFieldNames() |
| Product import fields              | App\Imports\ProductImport::fields()                                                    |
| Script đọc header (tạm)            | scripts/read_maolin_headers.php (cập nhật đường dẫn file nếu đổi tên)                  |
