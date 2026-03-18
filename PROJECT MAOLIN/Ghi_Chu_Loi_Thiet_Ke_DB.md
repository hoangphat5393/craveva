# GHI CHÚ CÁC ĐIỂM RỦI RO & SAI LỆCH TRONG DATABASE

**Ngày lập:** 18/03/2026
**Mô tả:** Tài liệu này ghi nhận các điểm thiết kế chưa đồng nhất trong cơ sở dữ liệu hiện tại của hệ thống Craveva (đặc biệt liên quan đến module Client/Khách hàng), cần lưu ý khi code logic Import dữ liệu cho dự án Miaolin B2B.

---

### 1. Dư thừa và nhập nhằng Khóa ngoại tại bảng `client_contacts`

- **Tình trạng:** Bảng `client_contacts` hiện đang chứa cả 2 trường `user_id` (từ thiết kế gốc) và `client_id` (được thêm qua migration sau này). Cả 2 trường này thực chất đều mang ý nghĩa trỏ về Client (Công ty khách hàng).
- **Rủi ro:** Khi Create/Update một người liên hệ (Contact), nếu code không cập nhật đồng thời cả 2 cột này bằng cùng một giá trị ID, các câu truy vấn Eloquent sử dụng Relationship có thể bị sai lệch hoặc không lấy ra được dữ liệu (tùy thuộc vào việc Relationship trong Model đang map với `user_id` hay `client_id`).
- **Hành động đề xuất:** Cần kiểm tra lại Model `ClientContact` xem đang dùng field nào làm khóa ngoại chính thức. Khi Import từ CSV, phải đảm bảo lưu giá trị ID của Client vào đúng trường đó (hoặc lưu vào cả 2 để an toàn).

### 2. Sai lệch Khóa ngoại ở Model `ClientDocument`

- **Tình trạng:** Trong Database (migration tạo bảng `client_docs`), khóa ngoại được lưu với tên cột là `user_id`. Tuy nhiên, trong mã nguồn Model `ClientDocument.php`, hàm Relationship lại đang được định nghĩa là `belongsTo(User::class, 'client_id')`.
- **Rủi ro:** Khi lập trình viên gọi `$document->client`, hệ thống sẽ ném ra lỗi SQL Exception vì cột `client_id` không tồn tại trong bảng `client_docs`.
- **Hành động đề xuất:** Mở file Model `ClientDocument.php` và sửa lại khai báo:
    - Từ: `return $this->belongsTo(User::class, 'client_id');`
    - Thành: `return $this->belongsTo(User::class, 'user_id');`
