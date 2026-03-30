# PROJECT MAOLIN New — Thống kê import liên quan giá & tier pricing

**Thư mục dữ liệu:** `PROJECT MAOLIN New/`  
**Cập nhật:** 2026-03-30  
**Mục đích:** Gom **một chỗ** file nào có **giá / bậc giá / báo giá** và đối chiếu với **import Pricing** trong code — tránh nhầm “nhiều cột giá trên sản phẩm” với module **Pricing Tier** / **Client product pricing**.

**Tài liệu tổng thể file Maolin:** [`PROJECT_MAOLIN_NEW_FILES_ANALYSIS.md`](PROJECT_MAOLIN_NEW_FILES_ANALYSIS.md)  
**Map cột vận hành:** [`MAOLIN_IMPORT_MAPPING.md`](MAOLIN_IMPORT_MAPPING.md) — mục **3) PRICING — sheet 產品價格表**

---

## 1) Hai nghĩa của “tier / giá” trong ngữ cảnh này

| Khái niệm                       | Ý nghĩa trong Craveva                                                               | Import / bảng liên quan                                                                                                                          |
| ------------------------------- | ----------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------ |
| **Giá nhiều mức trên sản phẩm** | Chuẩn / sỉ / thùng / nhân viên… là **cột giá** gắn **product**                      | `products.price`, `wholesale_price`, `price_per_box`, `employee_price` + import **Product** (khi file có cột tương ứng)                          |
| **Pricing Tier (module)**       | Tên tier + **chiết khấu** theo SKU (`tier_name`, `discount_type`, `discount_value`) | `Modules/Pricing/Imports/PricingTierItemsImport.php` → job `ImportPricingTierItemsJob`                                                           |
| **Giá riêng theo khách + SKU**  | Giá / chiết khấu từng khách–từng SP                                                 | `Modules/Pricing/Imports/ClientProductPricingImport.php` → job `ImportClientProductPricingJob`                                                   |
| **Phân cấp khách (grade)**      | VD: C級客戶 — có thể **gắn** tier pricing nếu nghiệp vụ map `customer_grade` → tier | File **客戶資料**; ghi chú trong [`MAOLIN_IMPORT_MAPPING.md`](MAOLIN_IMPORT_MAPPING.md) (customer_grade → `customer_grade_id` nếu dùng cho tier) |

---

## 2) Thống kê file trong `PROJECT MAOLIN New/` (số dòng ≈ UTF-8)

| File                                         | Số dòng (file) | Số dòng dữ liệu (ước lượng) | Liên quan **module** Pricing Tier / Client SKU price                                                 | Liên quan **giá / bậc giá** (product / báo giá)                                          |
| -------------------------------------------- | -------------- | --------------------------- | ---------------------------------------------------------------------------------------------------- | ---------------------------------------------------------------------------------------- |
| `Craveva product price.csv`                  | 5 918          | ~5 917                      | **Không** — không có `tier_name`, `discount_*`, `customer_code`+`custom_price` theo template Pricing | **Có** — 標準售價, 中盤價, 成箱價 → map `products.*` (xem mục 3)                         |
| `Quote_unit_price_inventory__產品價格表.csv` | 20             | ~19                         | **Không**                                                                                            | **Có** — thêm **員工價**; cùng họ cột với sheet giá trong mapping                        |
| `Quote_unit_price_inventory__報價單匯出.csv` | 20             | ~19                         | **Không trực tiếp** — schema khác `ClientProductPricingImport`                                       | **Có** — báo giá theo KH + dòng SP: **單價**, **標準價**, **價格判定** (標準價 / 特殊價) |
| `Craveva product info.csv`                   | 2 475          | ~2 474                      | Không                                                                                                | **Không** — master SP, không cột giá                                                     |
| `Craveva full inventory.csv`                 | 2 846          | ~2 845                      | Không                                                                                                | Không (tồn kho)                                                                          |
| `Quote_inventory.csv`                        | 2 932          | ~2 931                      | Không                                                                                                | Không (tồn theo kho/lô)                                                                  |
| `Quote_unit_price_inventory__產品庫存表.csv` | 20             | ~19                         | Không                                                                                                | Không (tồn)                                                                              |
| `Craveva_Customer Profile_客戶資料.csv`      | 17 754         | ~17 753                     | **Gián tiếp** — **客戶(集團)分級** nếu map sang tier                                                 | Không cột giá SP                                                                         |
| `Last_year_net_sales__*.csv` (3 file)        | 40 mỗi file    | ~39 mỗi file                | Không                                                                                                | Không — giao dịch / doanh số                                                             |
| `customer do.txt`                            | 4 dòng         | —                           | Không                                                                                                | Không — quy trình sync DigiWin                                                           |

---

## 3) Header & nội dung giá (trích từ file thật)

### 3.1 `Craveva product price.csv`

- **Header (dòng 1):** `品號 | SKU`, `品名 | Product Name`, `標準售價 | Standard Price`, `中盤價 | Whole sale price`, `成箱價 | Price per box`
- **Ý nghĩa:** Bảng giá **theo SKU** — đủ để đổ vào các cột giá **product** (không phải bảng tier).
- **Khối lượng:** file lớn nhất nhóm giá (~5,9k dòng).

### 3.2 `Quote_unit_price_inventory__產品價格表.csv`

- **Header:** `品號`, `品名`, `備貨型態`, `標準售價`, `中盤價`, `成箱價`, `員工價`
- **Ý nghĩa:** Cùng họ **giá sản phẩm** + **員工價**; bản mẫu/ngắn (≈19 dòng dữ liệu trong bộ export hiện tại).

### 3.3 `Quote_unit_price_inventory__報價單匯出.csv`

- **Cột giá / định giá đáng chú ý:** `單價`, `金額`, `標準價`, **價格判定** (ví dụ: 標準價 / 特殊價), kèm `客戶代號`, `品號`, …
- **Ý nghĩa:** Dữ liệu **báo giá theo khách hàng** — gần nghiệp vụ “giá đặc biệt theo KH”, nhưng **không** trùng cột với template import `ClientProductPricingImport` (`customer_code`, `product_sku`, `custom_price`, `discount_type`, `discount_value`).

### 3.4 `Craveva_Customer Profile_客戶資料.csv`

- Cột **客戶(集團)分級 | Customer Grade** — dùng cho **phân hạng khách**; liên kết **tier pricing** chỉ khi cấu hình nghiệp vụ + map (xem `MAOLIN_IMPORT_MAPPING`).

---

## 4) Đối chiếu với import trong code (Pricing module)

| Template import                | Field bắt buộc / chính (tóm tắt)                                                    | Có file sẵn trong `PROJECT MAOLIN New/` đúng format?               |
| ------------------------------ | ----------------------------------------------------------------------------------- | ------------------------------------------------------------------ |
| **PricingTierItemsImport**     | `tier_name`, `product_sku`, `discount_type`, (`discount_value`)                     | **Không**                                                          |
| **ClientProductPricingImport** | `customer_code`, `product_sku`, (`custom_price`, `discount_type`, `discount_value`) | **Không** — có thể **suy ra** từ báo giá sau bước chuyển đổi / ETL |

**Controller:** `Modules/Pricing/Http/Controllers/PricingImportController.php` — batch queue tên class ngắn: `PricingTierItemsImport`, `ClientProductPricingImport` (xem thêm `ImportController::ALLOWED_IMPORT_QUEUE_NAMES`).

---

## 5) Kết luận ngắn

1. Trong `PROJECT MAOLIN New/`, **mọi thông tin “tier pricing” theo nghĩa giá nhiều mức / báo giá** nằm ở **`Craveva product price.csv`**, **`產品價格表`**, và **`報價單匯出`** — **không** có file CSV mẫu sẵn cho **Pricing Tier items** hay **Client product pricing** đúng field import hiện tại.
2. **Customer grade** trong file khách là **cầu nối tiềm năng** tới tier, không thay thế file tier/SKU.
3. Để dùng module Pricing đúng template: cần **xuất/transform** từ báo giá hoặc định nghĩa tier + file `tier_name` + `discount_*`, hoặc map `customer_code` + `product_sku` + giá/chiết khấu cho `ClientProductPricingImport`.

---

## 6) Tham chiếu nhanh

| Nội dung                           | Vị trí                                                   |
| ---------------------------------- | -------------------------------------------------------- |
| Field `PricingTierItemsImport`     | `Modules/Pricing/Imports/PricingTierItemsImport.php`     |
| Field `ClientProductPricingImport` | `Modules/Pricing/Imports/ClientProductPricingImport.php` |
| Map cột giá sheet 產品價格表       | `FUNC_LOGIC/MAOLIN_IMPORT_MAPPING.md` §3                 |
| Phân tích đầy đủ từng loại file    | `FUNC_LOGIC/PROJECT_MAOLIN_NEW_FILES_ANALYSIS.md`        |
