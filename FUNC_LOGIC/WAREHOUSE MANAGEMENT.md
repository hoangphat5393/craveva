# Phân Loại Quy Mô Quản Lý Kho (Warehouse Management Scales)

Tài liệu này phân tích các cấp độ quản lý kho từ cơ bản đến nâng cao, giúp xác định lộ trình phát triển module Warehouse cho hệ thống Craveva ERP.

---

## 1. Quy Mô Cơ Bản (Basic / Manual Warehouse)

**Đặc điểm:** Quản lý thủ công, dựa nhiều vào sức người và trí nhớ của thủ kho.

- **Phù hợp với:** Cửa hàng nhỏ, Startup giai đoạn đầu, số lượng SKU < 100.

### Mô hình dữ liệu (ERD) - Cơ Bản

Đơn giản nhất, chỉ quản lý số lượng tổng. Dữ liệu được phân tách theo **Company** (Multi-tenant).

```
+-------------------+               +---------------------------+               +-----------------------------+
|     COMPANIES     |               |         PRODUCTS          |               |        STOCK_HISTORY        |
+-------------------+               +---------------------------+               +-----------------------------+
| id (PK)           | <------------ | id (PK)                   | <------------ | id (PK)                     |
| name              |      (1-n)    | company_id (FK)           |      (1-n)    | product_id (FK)             |
|                   |               | sku (Mã hàng)             |               | type (IN/OUT)               |
|                   |               | quantity (TỔNG TỒN KHO)   |               | quantity (Số lượng thay đổi)|
+-------------------+               +---------------------------+               +-----------------------------+
```

**Chi tiết cấu trúc:**

| Bảng              | Ý Nghĩa                                                                                                       |
| :---------------- | :------------------------------------------------------------------------------------------------------------ |
| **COMPANIES**     | Định danh doanh nghiệp/thuê bao sử dụng hệ thống. Mọi dữ liệu đều phải gắn với Company ID.                    |
| **PRODUCTS**      | Bảng sản phẩm chính. Cột `quantity` lưu tổng tồn kho hiện tại.                                                |
| **STOCK_HISTORY** | Nhật ký nhập/xuất. Dùng để tra cứu xem tại sao tồn kho lại là số đó (Ví dụ: Hôm qua nhập 10, sáng nay bán 2). |

---

## 2. Quy Mô Trung Bình (Standard WMS - Barcode Ready)

**Đặc điểm:** Quản lý vị trí kho (Bin Location) và Barcode.

- **Phù hợp với:** Doanh nghiệp SME, nhà phân phối B2B.

### Mô hình dữ liệu (ERD) - Trung Bình

**Hỗ trợ Đa Kho (Multi-Warehouse).** Tách `quantity` ra khỏi bảng Product. Kho và Sản phẩm đều thuộc về Company.

```
                                    +-------------------+
                                    |     COMPANIES     |
                                    +-------------------+
                                      ^               ^
                                      | (1-n)         | (1-n)
                                      |               |
+------------------+             +----+-----+     +---+-----------------------+             +-------------------------------+
|    WAREHOUSES    |             | LOCATIONS|     |         PRODUCTS          |             |        INVENTORY_ITEMS        |
+------------------+             +----------+     +---------------------------+             +-------------------------------+
| id (PK)          | <---------- | id (PK)  |     | id (PK)                   | <---------- | id (PK)                       |
| company_id (FK)  |    (1-n)    | wh_id(FK)|     | company_id (FK)           |    (1-n)    | location_id (FK)              |
| name (Kho A, B)  |             | code     |     | sku                       |             | product_id (FK)               |
+------------------+             +----------+     +---------------------------+             | quantity (SL TẠI VỊ TRÍ NÀY)  |
                                      ^                         ^                           +-------------------------------+
                                      |                         |
                                      | (1-n)                   | (1-n)
                                      +-------------------------+
```

**Chi tiết cấu trúc:**

| Bảng                | Ý Nghĩa                                                                                                                                                                         |
| :------------------ | :------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| **COMPANIES**       | Chủ sở hữu dữ liệu. Một công ty có thể có nhiều kho và nhiều sản phẩm.                                                                                                          |
| **WAREHOUSES**      | Danh sách các kho vật lý thuộc về công ty (Kho HCM, Kho HN).                                                                                                                    |
| **LOCATIONS**       | Các vị trí cụ thể trong kho.                                                                                                                                                    |
| **INVENTORY_ITEMS** | **Bảng quan trọng nhất.** Nó trả lời câu hỏi: _"Sản phẩm X đang nằm ở đâu và có bao nhiêu cái?"_ <br> Ví dụ: SP Coca-Cola nằm ở Kệ A (10 lon) và Kệ B (50 lon). Tổng tồn là 60. |

---

## 3. Quy Mô Lớn (Advanced WMS - Target cho Miaolin Foods)

**Đặc điểm:** Quản lý **Lô (Batch/Lot)** và **Hạn sử dụng (Expiry)** + **Đơn vị quy đổi (Units)**.

- **Phù hợp với:** Nhà phân phối thực phẩm, Dược phẩm, B2B.

### Mô hình dữ liệu (ERD) - Nâng Cao

**Hỗ trợ Đa Kho (Multi-Warehouse).**

Mô hình này phức tạp hơn để xử lý "Hàng cận date" và "Bán theo thùng/lẻ".

```
                                                      +-------------------+
                                                      |     COMPANIES     |
                                                      +-------------------+
                                                        ^               ^
                                                        | (1-n)         | (1-n)
                                                        |               |
                                  +---------------------+               +--------------------------+
                                  |    PRODUCT_UNITS    |               |         PRODUCTS         |
                                  +---------------------+               +--------------------------+
                                  | id (PK)             |      (n-1)    | id (PK)                  | <--------+
                                  | unit_name (Thùng)   | <------------ | company_id (FK)          |          |
                                  | conversion_rate     |               | sku, base_unit           |          |
                                  +---------------------+               +--------------------------+          |
                                                                                    ^                         | (1-n)
                                                                                    | (1-n)                   |
+---------------------+                                                             |                         |
|      LOCATIONS      | <-----------------------------------------------------------+               +---------+----------+
+---------------------+             (Không vẽ link Company                          |               |      BATCHES       |
| id (PK)             |              để đỡ rối, nhưng                               |               +--------------------+
| warehouse_id (FK)   |              Location thuộc Warehouse                       |               | id (PK)            |
| code                |              thuộc Company)                                 |               | product_id (FK)    |
+---------------------+                                                             |               | expiry_date        |
          ^                                                                         |               +--------------------+
          |                                                                         |                         ^
          | (1-n)                                                                   |                         | (1-n)
          |                                                                         |                         |
+--------------------------+                                                        |                         |
|        INVENTORY         |                                                        |                         |
+--------------------------+                                                        |                         |
| id (PK)                  | -------------------------------------------------------+-------------------------+
| location_id (FK)         |
| batch_id (FK)            |
| quantity (SL chuẩn)      |
+--------------------------+
```

**Chi tiết cấu trúc (Giải pháp cho Miaolin):**

| Bảng              | Ý Nghĩa & Tác Dụng                                                                                                                                                                     |
| :---------------- | :------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **COMPANIES**     | Tenant (Khách hàng sử dụng hệ thống). Toàn bộ Sản phẩm, Lô hàng, Kho bãi đều bị cô lập bởi `company_id`.                                                                               |
| **PRODUCT_UNITS** | Cho phép nhập hàng theo Thùng nhưng hệ thống tự quy đổi ra Cái để quản lý tồn kho chính xác.                                                                                           |
| **BATCHES**       | Lưu thông tin `expiry_date`. Đây là chìa khóa cho tính năng **Near-expiry Discount**. Khi tạo đơn hàng, hệ thống sẽ ưu tiên lấy từ các Batch có hạn sử dụng gần nhất (FEFO).           |
| **INVENTORY**     | Sự kết hợp của 3 yếu tố: **Sản phẩm (Lô nào) + Vị trí (Ở đâu) + Số lượng**. <br> _Ví dụ: Lô Hết Hạn Tháng 10 đang nằm ở Kệ A (10 cái), Lô Hết Hạn Tháng 12 đang nằm ở Kệ B (100 cái)._ |

### Chính sách xuất kho (FIFO vs FEFO)

- **FIFO (First In, First Out)**
    - Ưu tiên xuất **lô nhập trước** (dựa trên ngày nhập kho hoặc `manufacturing_date`).
    - Phù hợp với hàng không quá nhạy về hạn dùng (bao bì, vật tư, hàng khô lâu hỏng).
    - Logic: khi chọn hàng để xuất, hệ thống sắp xếp các record `INVENTORY` theo **thứ tự thời gian nhập** và trừ dần số lượng.

- **FEFO (First Expired, First Out)**
    - Ưu tiên xuất **lô sắp hết hạn trước** (dựa trên `expiry_date` trong bảng `BATCHES`).
    - Phù hợp với Thực phẩm/Dược phẩm, nơi yếu tố hạn dùng quan trọng hơn thứ tự nhập.
    - Logic: khi chọn hàng để xuất, hệ thống sắp xếp các lô theo **ngày hết hạn tăng dần**, sau đó mới xét đến vị trí/kho.

- **Quy tắc đề xuất cho Miaolin Foods**
    - Mặc định: Sản phẩm có `is_batch_managed = TRUE` thì dùng **FEFO**.
    - Sản phẩm không quản lý lô (`is_batch_managed = FALSE`) thì dùng **FIFO** theo thời gian nhập.
    - Cho phép cấu hình ở cấp **Warehouse** hoặc **Company** nếu cần override (ví dụ: một số kho test hoặc kho hàng khuyến mãi có thể luôn dùng FIFO).

---

## 4. Quy Mô Doanh Nghiệp (Enterprise)

_Dành cho hệ thống Robot/AI - Không đi sâu chi tiết._

- **Tính năng:** Tối ưu đường đi lấy hàng (Pathfinding), Tự động bổ sung hàng (Auto-replenishment).

---

## 5. Phân Tích Cột Bắt Buộc & Tùy Chọn (Data Dictionary)

Trong mô hình SaaS (Multi-tenant), mỗi công ty sẽ có quy trình khác nhau. Dưới đây là phân tích các cột **Cốt lõi (Core/Mandatory)** và **Mở rộng (Optional/Feature-based)** để hệ thống linh hoạt.

### Quy ước

- **[M] Mandatory:** Bắt buộc phải có giá trị.
- **[O] Optional:** Có thể để trống (NULL).

### Bảng PRODUCTS (Sản phẩm)

| Cột                | Loại    | Mô tả                                                                                          |
| :----------------- | :------ | :--------------------------------------------------------------------------------------------- |
| `id`               | **[M]** | Khóa chính.                                                                                    |
| `company_id`       | **[M]** | Xác định sản phẩm thuộc công ty nào.                                                           |
| `sku`              | **[M]** | Mã sản phẩm duy nhất trong phạm vi công ty.                                                    |
| `name`             | **[M]** | Tên sản phẩm.                                                                                  |
| `base_unit`        | **[M]** | Đơn vị cơ bản (Ví dụ: Cái, Gam). Mọi tính toán tồn kho đều quy đổi về đơn vị này.              |
| `is_batch_managed` | **[O]** | (Boolean) `TRUE` nếu sản phẩm này cần quản lý Lô/Date (Thực phẩm). `FALSE` nếu là hàng thường. |
| `min_stock_level`  | **[O]** | Cảnh báo tồn kho tối thiểu.                                                                    |

### Bảng WAREHOUSES (Kho)

| Cột          | Loại    | Mô tả                                                              |
| :----------- | :------ | :----------------------------------------------------------------- |
| `id`         | **[M]** | Khóa chính.                                                        |
| `company_id` | **[M]** |                                                                    |
| `name`       | **[M]** | Tên kho.                                                           |
| `address`    | **[O]** | Địa chỉ (Cần thiết nếu dùng tính năng tính phí vận chuyển từ kho). |

### Bảng LOCATIONS (Vị trí trong kho)

_Nếu công ty không quản lý vị trí chi tiết, hệ thống sẽ tự tạo 1 Location mặc định là "General" cho mỗi kho._

| Cột            | Loại    | Mô tả                               |
| :------------- | :------ | :---------------------------------- |
| `id`           | **[M]** |                                     |
| `warehouse_id` | **[M]** | Thuộc kho nào.                      |
| `code`         | **[M]** | Mã vị trí (Barcode).                |
| `zone`         | **[O]** | Khu vực (Ví dụ: Khu lạnh, Khu mát). |

### Bảng BATCHES (Lô hàng)

_Chỉ sử dụng khi sản phẩm có `is_batch_managed = TRUE`._

| Cột                  | Loại    | Mô tả                                                                    |
| :------------------- | :------ | :----------------------------------------------------------------------- |
| `id`                 | **[M]** |                                                                          |
| `product_id`         | **[M]** |                                                                          |
| `batch_code`         | **[M]** | Mã lô in trên bao bì.                                                    |
| `expiry_date`        | **[O]** | Hạn sử dụng. Bắt buộc nếu là thực phẩm/dược phẩm để chạy tính năng FEFO. |
| `manufacturing_date` | **[O]** | Ngày sản xuất.                                                           |

### Bảng INVENTORY (Tồn kho thực tế)

_Đây là bảng quan trọng nhất, liên kết tất cả._

| Cột                 | Loại    | Mô tả                                                                          |
| :------------------ | :------ | :----------------------------------------------------------------------------- |
| `id`                | **[M]** |                                                                                |
| `location_id`       | **[M]** | Hàng đang ở đâu? (Nếu không quản lý vị trí, trỏ về Location "General").        |
| `batch_id`          | **[O]** | Hàng thuộc lô nào? (NULL nếu sản phẩm không quản lý lô).                       |
| `quantity`          | **[M]** | Số lượng tồn kho (Theo `base_unit`).                                           |
| `reserved_quantity` | **[O]** | Số lượng đang được giữ cho đơn hàng (Chưa xuất kho nhưng không được bán tiếp). |
