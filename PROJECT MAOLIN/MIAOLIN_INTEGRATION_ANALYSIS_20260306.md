# Phân Tích & Kế Hoạch Tích Hợp Miaolin B2B (Miaolin Integration Analysis)

**Ngày:** 06/03/2026
**Gửi:** Project Manager / Alex (Miaolin)

## 1. Danh sách Tệp Nhập Liệu Hàng Ngày (06:00 AM)

| Tệp CSV Mục Tiêu        | Nguồn Dữ Liệu Excel                                   | Sheet Nguồn                   | Ánh Xạ Chính (Key Mapping)                                            |
| :---------------------- | :---------------------------------------------------- | :---------------------------- | :-------------------------------------------------------------------- |
| **customers.csv**       | `Miaolin Customer - Product infomation_20260304.xlsx` | `Customer`                    | Mã KH (A), Tên (B), MST (J), SĐT (K), Địa chỉ (M)                     |
| **products.csv**        | `import_product.xlsx`                                 | `產品價格表 Standard Pricing` | Mã SP (A), Tên (B), Giá chuẩn (D)                                     |
| **contract_prices.csv** | `import_Tier Pricing.xlsx`                            | `報價單匯出 Tier Pricing`     | Mã KH (C), Mã SP (W), Giá (AG), Ngày hiệu lực (AD), Ngày hết hạn (AE) |
| **inventory.csv**       | `import_inventory.xlsx`                               | `產品庫存表 Inventory`        | Mã SP (A), Kho (K), Tồn cuối (P)                                      |

## 1.1 Khách Cần Gửi Những File Gì Để Import Vào ERP Craveva

### Bắt buộc (để import danh mục & vận hành bán hàng B2B)

- `customers.csv`: danh mục khách hàng (mã khách là khóa chính).
- `products.csv`: danh mục sản phẩm (SKU là khóa chính).
- `contract_prices.csv`: bảng giá theo khách / theo hợp đồng (tham chiếu `customer_code` và `product_code` từ 2 file trên).
- `inventory.csv`: tồn kho theo SKU (và theo kho nếu có nhiều kho).

### Tuỳ chọn / cần bổ sung mẫu

- `orders.csv`: đơn hàng phát sinh hàng ngày (hiện thiếu file mẫu/định dạng, cần Miaolin cung cấp để chốt mapping).

### Quy ước kỹ thuật khi gửi file

- Định dạng: CSV, UTF-8, có dòng tiêu đề (header).
- Tần suất: 1 lần/ngày trước 06:00 AM.
- Phụ thuộc: Import theo thứ tự `customers.csv` → `products.csv` → `contract_prices.csv` → `inventory.csv` (→ `orders.csv` nếu có).

## 1.2 Cập Nhật Nguồn File Sau Khi Tách (Customer/Product)

Khách đã tách file tổng `Miaolin Customer - Product infomation_20260304.xlsx` thành 2 file riêng:

- File khách hàng: `Miaolin Customer infomation_20260304.xlsx` (Sheet: `Customer`) → dùng để tạo `customers.csv`.
- File sản phẩm: `Miaolin Product infomation_20260304.xlsx` (Sheet: `Product`) → dùng để tạo `products.csv`.

Ghi chú: `import_product.xlsx` vẫn có thể dùng như file tham chiếu/đối soát giá, nhưng nguồn chuẩn ưu tiên là `Miaolin Product infomation_20260304.xlsx`.

## 1.3 So Sánh 2 Nguồn Product & Quy Tắc Hợp Nhất

Nguồn 1: `Miaolin Product infomation_20260304.xlsx` (Sheet: `Product`)

- Trường chính: 品號(SKU), 品名(Tên), 規格(Quy cách), 庫存單位(Đơn vị tồn), 商品級別, 商品來源, 品牌類別, 保存天數, 備貨型態, 儲存溫層, 標準價, 成箱價.

Nguồn 2: `import_product.xlsx` (Sheet: `產品價格表 Standard Pricing`)

- Trường chính: 品號(SKU), 品名(Tên), 備貨型態, 標準售價 (Giá chuẩn), 中盤價 (Giá sỉ), 成箱價 (Giá theo thùng), 員工價 (Giá nhân viên).

Khác biệt nổi bật:

- Nguồn 1 có đầy đủ thông tin kỹ thuật sản phẩm (quy cách, đơn vị, nhiệt độ bảo quản, nguồn, thương hiệu…).
- Nguồn 2 tập trung vào thông tin giá bán đa tầng (giá chuẩn, giá sỉ, giá theo thùng, giá nhân viên), có 備貨型態 nhưng không có quy cách/đơn vị/nhiệt độ.

Lưu ý: `import_product.xlsx` là bảng Giá Chuẩn theo SKU (Standard Pricing), **không phải Tier Pricing theo khách hàng**.  
Tier Pricing (giá theo khách/hợp đồng) nằm trong file `import_Tier Pricing.xlsx` (Sheet: `報價單匯出 Tier Pricing`).

Quy tắc hợp nhất khi tạo `products.csv`:

- Khóa chính: ghép theo SKU (品號).
- Thuộc tính sản phẩm (SKU, Tên, Quy cách, Đơn vị, Nhiệt độ, Nguồn/Thương hiệu): lấy từ `Miaolin Product infomation_20260304.xlsx`.
- Giá chuẩn: ưu tiên 標準價 từ `Miaolin Product infomation_20260304.xlsx`; nếu trống, fallback sang 標準售價 từ `import_product.xlsx`.
- Giá theo thùng: ưu tiên 成箱價 từ `Miaolin Product infomation_20260304.xlsx`; nếu trống, fallback sang 成箱價 từ `import_product.xlsx`.
- Giá sỉ (中盤價) và Giá nhân viên (員工價): chỉ có ở `import_product.xlsx` → bổ sung thêm trường mở rộng nếu hệ thống hỗ trợ.

## 2. Chi Tiết Ánh Xạ Dữ Liệu (Data Mapping)

### 2.1 Khách Hàng (Customers)

_Nguồn: `Miaolin Customer - Product infomation_20260304.xlsx` - Sheet: `Customer`_

_Cập nhật: `Miaolin Customer infomation_20260304.xlsx` - Sheet: `Customer`_

- **Mã Khách Hàng**: Cột A (客戶代號)
- **Tên Khách Hàng**: Cột B (客戶簡稱)
- **Mã Số Thuế**: Cột J (統一編號)
- **Số Điện Thoại**: Cột K (TEL_NO(一))
- **Địa Chỉ Giao Hàng**: Cột M (送貨地址)
- **Điều Khoản Thanh Toán**: Cột N (交易條件)

### 2.2 Sản Phẩm (Products)

_Nguồn: `import_product.xlsx` - Sheet: `產品價格表 Standard Pricing`_

_Cập nhật: `Miaolin Product infomation_20260304.xlsx` - Sheet: `Product`_

- **Mã Sản Phẩm (SKU)**: Cột A (品號 | SKU)
- **Tên Sản Phẩm**: Cột B (品名 | Product Name)
- **Loại Tồn Kho**: Cột C (備貨型態)
- **Giá Bán Chuẩn**: Cột D (標準售價)
- **Giá Bán Buôn**: Cột E (中盤價)

Cập nhật mapping theo file `Miaolin Product infomation_20260304.xlsx` (Sheet: `Product`):

- **Mã Sản Phẩm (SKU)**: Cột A (品號)
- **Tên Sản Phẩm**: Cột B (品名)
- **Quy Cách**: Cột C (規格)
- **Đơn Vị Tồn (Unit)**: Cột D (庫存單位)
- **Loại Tồn Kho**: Cột I (備貨型態)
- **Tầng Nhiệt Bảo Quản**: Cột J (儲存溫層)
- **Giá Bán Chuẩn**: Cột K (標準價)
- **Giá Theo Thùng**: Cột L (成箱價)

### 2.3 Giá Hợp Đồng (Contract Prices)

_Nguồn: `import_Tier Pricing.xlsx` - Sheet: `報價單匯出 Tier Pricing`_

- **Mã Khách Hàng**: Cột C (客戶代號)
- **Mã Sản Phẩm**: Cột W (品 號 | SKU)
- **Đơn Giá**: Cột AG (單價 | unit price)
- **Ngày Hiệu Lực**: Cột AD (生效日期)
- **Ngày Hết Hạn**: Cột AE (失效日期)

### 2.4 Tồn Kho (Inventory)

_Nguồn: `import_inventory.xlsx` - Sheet: `產品庫存表 Inventory`_

- **Mã Sản Phẩm**: Cột A (品號 | SKU)
- **Mã Kho**: Cột K (庫別 | Warehouse Code)
- **Số Lượng Tồn Cuối**: Cột P (期末庫存 | Ending Inventory)

## 3. Các Vấn Đề Cần Xác Nhận & Bước Tiếp Theo

1.  **Xác nhận Nguồn Dữ Liệu Sản Phẩm:**
    - Sheet `Product` trong tệp `Miaolin Customer...xlsx` hiện không có dữ liệu hoặc bị lỗi đọc.
    - Chúng tôi đang sử dụng `import_product.xlsx` làm danh mục sản phẩm chuẩn (Master Data). Vui lòng xác nhận?
    - Cập nhật: Khách đã tách file và `Miaolin Product infomation_20260304.xlsx` đã đọc được dữ liệu sản phẩm; đề xuất dùng file này làm nguồn chuẩn cho `products.csv`.
2.  **Dữ liệu Đơn Hàng (Orders):**
    - Thiếu tệp mẫu cho `orders.csv`. Cần cung cấp định dạng xuất dữ liệu đơn hàng hàng ngày.
3.  **Phương Thức Truyền Tải:**
    - Buổi sáng: Xuất file từ hệ thống Digiwind và import vào ERP Craveva (06:00 AM).
    - Buổi tối: Xuất dữ liệu từ ERP Craveva và import vào hệ thống Digiwind của Miaolin.

---

_Generated by Craveva Tech Team_
