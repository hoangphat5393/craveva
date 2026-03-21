# KẾ HOẠCH PHÁT TRIỂN TÍNH NĂNG LOGGING CHI TIẾT CHO MODULE DEVELOPERTOOLS

**Dự án:** Craveva ERP - DeveloperTools Module
**Trạng thái:** Bản kế hoạch phân tích (Design Doc)
**Ngày lập:** 18/03/2026

---

## 1. MỤC TIÊU (OBJECTIVES)
Nâng cấp khả năng giám sát của module DeveloperTools bằng cách ghi lại chi tiết mọi câu lệnh SQL được thực thi thông qua module này. Điều này giúp:
- Kiểm soát các thao tác của AI Agent/Third-party khi truy cập Database.
- Phân tích hiệu suất (Execution Time).
- Truy vết các thay đổi dữ liệu (Affected Rows).
- Tối ưu dung lượng lưu trữ thông qua cơ chế Log Rotation.

---

## 2. PHÂN TÍCH CẤU TRÚC DỮ LIỆU (DATABASE SCHEMA)

Để tránh làm nặng hệ thống vốn đã có nhiều bảng, tôi đề xuất **chỉ thêm 01 bảng duy nhất** chuyên biệt cho việc lưu trữ log chi tiết.

### Bảng mới: `developer_tools_query_logs`
| Trường | Kiểu dữ liệu | Mô tả |
| :--- | :--- | :--- |
| `id` | BigInt (PK) | ID tự tăng |
| `company_id` | Int (FK) | ID công ty thực hiện query (Multi-tenant) |
| `credential_id` | Int (FK) | ID của Credential/AI Agent gọi lệnh |
| `query_type` | Enum | SELECT, INSERT, UPDATE, DELETE, ALTER, etc. |
| `sql_query` | Text | Nội dung câu lệnh SQL thực tế |
| `bindings` | JSON | Các tham số truyền vào câu lệnh |
| `execution_time_ms` | Float | Thời gian thực thi (mili giây) |
| `affected_rows` | Int | Số dòng bị ảnh hưởng |
| `status` | Enum | success, failed |
| `error_log` | Text | Thông báo lỗi nếu query thất bại |
| `ip_address` | String | IP của Agent gọi lệnh |
| `created_at` | Timestamp | Thời điểm thực thi |

---

## 3. GIẢI PHÁP KỸ THUẬT (TECHNICAL SOLUTIONS)

### 3.1 Cơ chế Bật/Tắt (Feature Toggle)
- **Cấu hình:** Thêm trường `is_logging_enabled` vào bảng `developer_tools_credentials`.
- **Lợi ích:** Cho phép bật log cho từng Agent cụ thể. Khi không cần debug, có thể tắt đi để đảm bảo hiệu suất Database tối đa.

### 3.2 Ghi log thời gian thực (Real-time Logging)
- Sử dụng `DB::listen` trong `DeveloperToolsServiceProvider` để bắt các query phát sinh từ scope của module.
- Chỉ ghi log nếu `is_logging_enabled = true`.

### 3.3 Tối ưu hiệu suất & Dung lượng
- **Async Logging:** Sử dụng Laravel Jobs/Queue để ghi log vào DB, tránh làm chậm response của query chính.
- **Log Rotation (Xoay vòng):** 
    - Xây dựng một Artisan Command `dev-tools:clear-old-logs`.
    - Tự động xóa các log cũ hơn 30 ngày hoặc khi bảng vượt quá 1 triệu bản ghi.
    - Cấu hình qua Schedule trong `Console/Kernel.php`.

### 3.4 API Xem & Xuất Log
- **API Endpoint:** `GET /api/developer-tools/logs`.
- **Tính năng:** Filter theo `company_id`, `date_range`, `query_type`.
- **Export:** Hỗ trợ xuất ra định dạng CSV/Excel.

---

## 4. KẾ HOẠCH TRIỂN KHAI (IMPLEMENTATION PLAN)

| Giai đoạn | Công việc cụ thể | Thời gian (AI Cursor Pro) |
| :--- | :--- | :--- |
| **Giai đoạn 1** | Migration tạo bảng `developer_tools_query_logs` và thêm nút Bật/Tắt. | 1 ngày |
| **Giai đoạn 2** | Implement logic `DB::listen` và xử lý Queue ghi log. | 1 ngày |
| **Giai đoạn 3** | Viết API xem/export log và Command xoay vòng (Rotation). | 1 ngày |
| **Giai đoạn 4** | Viết Unit Test (Mục tiêu 95% coverage) & Tài liệu hóa. | 1 ngày |
| **TỔNG CỘNG** | | **4 ngày** |

---

## 5. ĐÁNH GIÁ TÁC ĐỘNG (IMPACT ASSESSMENT)

- **Số lượng bảng thêm mới:** 01 bảng (`developer_tools_query_logs`).
- **Tác động hiệu suất:** Rất thấp (do sử dụng cơ chế Bật/Tắt và ghi log bất đồng bộ qua Queue).
- **Tính an toàn:** Log được gắn với `company_id`, đảm bảo dữ liệu log của công ty nào chỉ công ty đó thấy.

---

## 6. PHƯƠNG ÁN DỰ PHÒNG: GENERAL QUERY LOGGING (SERVER-SIDE)
Áp dụng khi cần đối soát trực tiếp các truy vấn từ bên thứ ba (AI Agent) mà không can thiệp vào mã nguồn Laravel.

### 6.1 Cách thức thực hiện
Sử dụng HeidiSQL hoặc MySQL Client để bật log toàn cục:
```sql
-- Bật log
SET GLOBAL general_log = 'ON';
SET GLOBAL log_output = 'TABLE';

-- Truy vấn log để đối soát lỗi AI
SELECT event_time, argument FROM mysql.general_log 
WHERE argument LIKE '%users%' OR argument LIKE '%client_details%'
ORDER BY event_time DESC LIMIT 10;
```

### 6.2 Ưu điểm & Rủi ro
- **Ưu điểm:** Thấy được 100% câu lệnh thực tế AI gửi tới DB, xác định nhanh lỗi do Prompt hay do Data.
- **Rủi ro:** Gây chậm hệ thống và đầy ổ cứng nếu quên tắt.
- **Khuyến nghị:** Chỉ bật khi đang thực hiện test và tắt ngay sau khi có kết quả.

---
*Bản kế hoạch này được lưu để phục vụ việc phê duyệt trước khi tiến hành code.*
