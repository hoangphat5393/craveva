# PHÂN TÍCH HỆ THỐNG GIAO VẬN (DELIVERY SYSTEM) - MIAO LING FOODS

## 1. Đặc thù ngành hàng (Context)

Khác với giao hàng thương mại điện tử (Shopee/Lazada), giao hàng B2B thực phẩm đông lạnh (Miao Ling) có các đặc điểm cốt lõi sau:

1.  **Cold Chain (Chuỗi lạnh):** Hàng cần xe lạnh chuyên dụng, khó dùng shipper xe máy thông thường (Grab/Ahamove) trừ đơn nhỏ/gần.
2.  **Đa kho (Multi-warehouse):** Hàng xuất từ kho gần khách nhất để giảm chi phí lạnh.
3.  **Tải trọng lớn:** Đơn hàng thường tính bằng thùng/tạ/tấn -> Phí ship tính theo **Trọng lượng** hoặc **Thể tích**.
4.  **Free-ship Threshold:** Chính sách "Mua trên 5 triệu miễn phí vận chuyển" rất phổ biến để kích cầu B2B.

---

## 2. Mô hình tính phí vận chuyển (Shipping Models)

Hệ thống cần hỗ trợ 3 phương pháp tính phí sau (theo thứ tự ưu tiên):

### A. Flat Rate (Đồng giá theo khu vực) - _Dễ triển khai nhất_

-   **Logic:** Chia các tỉnh thành thành các Zone (Vùng).
-   **Ví dụ:**
    -   Zone 1 (Nội thành HCM): 30.000đ.
    -   Zone 2 (Ngoại thành HCM): 50.000đ.
    -   Zone 3 (Tỉnh lân cận): 150.000đ.
-   **Ưu điểm:** Khách dễ hiểu, Sale dễ báo giá.

### B. Table Rate (Bảng giá theo trọng lượng & Vùng) - _Chính xác nhất_

-   **Logic:** Kết hợp `Vùng` + `Tổng trọng lượng đơn hàng`.
-   **Ví dụ:**
    -   Zone 1 + < 10kg: 30.000đ.
    -   Zone 1 + > 10kg: Mỗi kg thêm 2.000đ.
-   **Yêu cầu:** Mọi sản phẩm trong ERP phải nhập chính xác trường `weight` (trọng lượng).

### C. Free Shipping Rules (Quy tắc miễn phí) - _Quan trọng cho B2B_

-   **Logic:** Ghi đè lên 2 phương pháp trên nếu thỏa điều kiện.
-   **Ví dụ:** "Đơn hàng > 10.000.000đ -> Phí ship = 0đ".

---

## 3. Các Mô Hình Vận Hành Giao Hàng (Delivery Execution Models)

Với đặc thù hàng đông lạnh và B2B, hệ thống cần hỗ trợ 3 mô hình vận hành chính:

### A. Đội vận chuyển nội bộ (In-house Fleet)

-   **Mô tả:** Công ty sở hữu xe tải lạnh và đội ngũ tài xế riêng.
-   **Phù hợp:** Đơn hàng lớn, tuyến đường cố định (ví dụ: giao cho siêu thị/đại lý mỗi thứ 2-4-6).
-   **Yêu cầu hệ thống:**
    -   Module quản lý tài xế.
    -   App Mobile cho tài xế (Driver App) để xem lộ trình và chụp ảnh POD.

### B. Đối tác vận chuyển thuê ngoài (3PL Outsourcing)

-   **Mô tả:** Thuê các đơn vị chuyên nghiệp (Ahamove/Lalamove cho xe tải, hoặc các chành xe tỉnh).
-   **Phù hợp:** Đơn hàng lẻ tẻ, cần giao gấp, hoặc đi tỉnh xa mà xe nhà không chạy tới.
-   **Yêu cầu hệ thống:**
    -   Tích hợp API (nếu có) để lấy giá cước và Booking tự động.
    -   Nhập thủ công phí vận chuyển thực tế (đối với chành xe truyền thống).

### C. Khách đến kho lấy hàng (Self-pickup / Will Call)

-   **Mô tả:** Khách hàng (nhà hàng/quán ăn) tự mang xe đến kho để lấy hàng.
-   **Phù hợp:** Khách cần gấp, muốn tiết kiệm phí ship, hoặc tiện đường.
-   **Quy trình:**
    1.  Khách đặt hàng -> Chọn "Đến lấy tại kho A".
    2.  Kho soạn hàng -> Bấm "Sẵn sàng (Ready)".
    3.  Hệ thống gửi Email/Notif kèm **Mã QR nhận hàng**.
    4.  Khách đến -> Quét QR -> Ký nhận -> Xong.

---

## 4. Luồng nghiệp vụ (Business Flow)

### Bước 1: Khách hàng đặt hàng (B2B App)

1.  Khách chọn hàng -> Vào Giỏ hàng.
2.  Khách chọn **Địa chỉ nhận hàng** (Shipping Address).
3.  Hệ thống (Backend) thực hiện ngầm:
    -   **B1:** Xác định tọa độ/Quận/Huyện của khách.
    -   **B2:** Tìm **Kho (Warehouse)** gần nhất có đủ tồn kho (Logic Multi-warehouse).
    -   **B3:** Tính khoảng cách/Vùng từ Kho -> Khách.
    -   **B4:** Áp dụng bảng giá (Table Rate).
    -   **B5:** Kiểm tra quy tắc Miễn phí (Free Ship Rule).
4.  Hệ thống hiển thị: **Phí vận chuyển dự kiến**.

### Bước 2: Xử lý đơn hàng (ERP Admin)

1.  Sale duyệt đơn -> Chuyển sang kho.
2.  Thủ kho đóng gói (Pick & Pack).
3.  **Chọn đơn vị vận chuyển (Dispatch):**
    -   _Option 1 (Xe nhà):_ Gán cho tài xế công ty (Driver App).
    -   _Option 2 (Thuê ngoài):_ Gọi xe tải ngoài (Thủ công nhập phí thực tế).
4.  Cập nhật trạng thái: `Dispatched` (Đang giao).

### Bước 3: Giao thành công & POD

1.  Hàng đến nơi -> Khách kiểm đếm.
2.  **POD (Proof of Delivery):** Chụp ảnh biên bản giao nhận/Ký tên.
3.  Cập nhật trạng thái: `Delivered` -> Kích hoạt xuất hóa đơn (Invoice).

---

## 4. Thiết kế Dữ liệu (Database Design) - Multi-Tenant Support

Vì hệ thống dùng chung Database cho nhiều công ty (Tenant), tất cả các bảng cấu hình đều phải có `company_id`.

### A. Nhóm Cấu Hình (Configuration Tables)

```sql
-- 1. Bảng Khu vực vận chuyển (Shipping Zones)
-- Định nghĩa vùng địa lý (VD: Nội thành HCM, Miền Tây)
CREATE TABLE shipping_zones (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    company_id BIGINT NOT NULL,       -- Của công ty nào?
    name VARCHAR(255) NOT NULL,       -- Tên vùng
    province_ids JSON DEFAULT NULL,   -- Danh sách ID tỉnh/thành [1, 2, 3]
    district_ids JSON DEFAULT NULL,   -- Danh sách ID quận/huyện (nếu chia nhỏ)
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    INDEX(company_id)
);

-- 2. Bảng Phương thức vận chuyển (Shipping Methods)
-- Định nghĩa cách giao (VD: Xe tải lạnh, Hỏa tốc, Đến kho lấy)
CREATE TABLE shipping_methods (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    company_id BIGINT NOT NULL,
    name VARCHAR(255) NOT NULL,
    type ENUM('flat_rate', 'table_rate', 'free_shipping', 'local_pickup') NOT NULL,
    requires_shipping_address BOOLEAN DEFAULT TRUE, -- Pickup thì = False
    is_active BOOLEAN DEFAULT TRUE,
    INDEX(company_id)
);

-- 3. Bảng Giá cước (Shipping Rates)
-- Quy định giá tiền cho từng Vùng + Phương thức + Trọng lượng
CREATE TABLE shipping_rates (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    shipping_zone_id BIGINT NOT NULL,   -- Áp dụng cho vùng nào
    shipping_method_id BIGINT NOT NULL, -- Áp dụng cho phương thức nào

    -- Điều kiện áp dụng
    min_weight DECIMAL(10,2) DEFAULT 0, -- Trọng lượng từ...
    max_weight DECIMAL(10,2) DEFAULT NULL, -- ...đến (NULL = vô cùng)
    min_order_total DECIMAL(15,2) DEFAULT 0, -- Giá trị đơn hàng từ... (để làm Free Ship)

    -- Kết quả giá
    price DECIMAL(15,2) NOT NULL DEFAULT 0, -- Giá cố định (Flat)
    rate_per_kg DECIMAL(15,2) DEFAULT 0,    -- Giá cộng thêm mỗi kg (Table rate)

    FOREIGN KEY (shipping_zone_id) REFERENCES shipping_zones(id) ON DELETE CASCADE,
    FOREIGN KEY (shipping_method_id) REFERENCES shipping_methods(id) ON DELETE CASCADE
);
```

### B. Nhóm Vận Hành (Operation Tables)

```sql
-- 4. Bảng Tài xế (Drivers) - Cho đội xe nhà
CREATE TABLE drivers (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    company_id BIGINT NOT NULL,
    user_id BIGINT NULL,            -- Link tới bảng users (để login App Driver)
    name VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    vehicle_plate VARCHAR(20),      -- Biển số xe
    status ENUM('active', 'busy', 'offline') DEFAULT 'offline',
    current_lat DECIMAL(10,8),      -- Vị trí hiện tại
    current_lng DECIMAL(11,8),
    INDEX(company_id)
);

-- 5. Bảng Chuyến giao hàng (Shipments)
-- Tách riêng khỏi Order vì 1 đơn có thể giao nhiều lần (hoặc gộp đơn)
CREATE TABLE shipments (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    company_id BIGINT NOT NULL,
    order_id BIGINT NOT NULL,
    warehouse_id BIGINT NOT NULL,    -- Xuất từ kho nào (Multi-warehouse)

    -- Thông tin giao hàng
    tracking_code VARCHAR(50),       -- Mã vận đơn
    carrier_name VARCHAR(100),       -- Tên đơn vị vận chuyển (Ahamove, Xe nhà...)
    driver_id BIGINT NULL,           -- Nếu là xe nhà thì link tới Drivers

    -- Trạng thái
    status ENUM('pending', 'picked', 'packed', 'dispatched', 'delivered', 'cancelled') DEFAULT 'pending',
    shipping_fee_cost DECIMAL(15,2), -- Chi phí thực tế phải trả cho bên vận chuyển
    shipping_fee_charged DECIMAL(15,2), -- Chi phí thu của khách hàng

    -- Bằng chứng giao hàng (POD)
    pod_image_url VARCHAR(255),      -- Ảnh chụp khi giao
    delivered_at TIMESTAMP NULL,

    created_at TIMESTAMP,
    updated_at TIMESTAMP,

    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (driver_id) REFERENCES drivers(id),
    INDEX(company_id),
    INDEX(tracking_code)
);
```

### Giải thích Cơ chế "Dùng chung DB" (Multi-Tenant Strategy)

1.  **Cột `company_id` là bắt buộc:**

    -   Mọi bảng (trừ bảng danh mục hệ thống như Tỉnh/Thành phố) đều phải có `company_id`.
    -   Khi **Miao Ling (Seller)** đăng nhập ERP -> Hệ thống tự động thêm `WHERE company_id = 1` vào mọi câu truy vấn.
    -   Khi **Đại lý A (Buyer)** đăng nhập B2B -> Hệ thống truy vấn các bảng cấu hình của Miao Ling (Seller) để tính phí ship cho Đại lý A.

2.  **Tách `Shipments` khỏi `Orders`:**
    -   Đây là chuẩn ERP. Đơn hàng (Order) là cam kết thương mại. Giao hàng (Shipment) là hành động kho vận.
    -   Việc tách bảng giúp xử lý tình huống: Đơn hàng đặt 10 thùng, nhưng kho chỉ còn 5 thùng -> Tạo Shipment 1 giao trước 5 thùng -> Tuần sau hàng về tạo Shipment 2 giao nốt.

---

## 5. Yêu cầu giao diện (UI Requirements)

### Cho Admin (ERP)

1.  **Cấu hình Vận chuyển:** Màn hình để Admin vẽ vùng (Zone) và nhập bảng giá (Rate).
2.  **Màn hình Điều phối (Dispatch):** Danh sách đơn chờ giao -> Nút "Gán tài xế" hoặc "Nhập mã vận đơn".

### Cho Khách hàng (B2B)

1.  **Checkout:** Dropdown chọn "Phương thức giao hàng" (Ví dụ: Giao thường, Giao gấp, Tự đến lấy).
2.  **Tracking:** Xem trạng thái đơn hàng (Đã xuất kho / Đang trên đường).

---

## 6. Kết luận & Lộ trình

Với Miao Ling Foods, chúng ta nên triển khai theo pha:

-   **Pha 1 (MVP):** Dùng **Flat Rate** (Đồng giá theo quận/huyện) + **Free Ship Rule** (Trên X tiền thì miễn phí). Đây là cách nhanh nhất để chạy.
-   **Pha 2:** Áp dụng **Table Rate** (Tính theo kg) khi dữ liệu trọng lượng sản phẩm đã chuẩn.
-   **Pha 3:** Tích hợp API đơn vị vận chuyển thứ 3 (nếu họ mở rộng sang bán lẻ B2C).
