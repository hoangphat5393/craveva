# Kế Hoạch Phát Triển Hệ Thống Logistics Nâng Cao (Future Roadmap)

Tài liệu này mô tả chi tiết các tính năng của hệ thống **Logistics / TMS (Transport Management System)** sẽ được phát triển trong các giai đoạn sau.

Hiện tại (Giai đoạn B2B Pricing), dự án chỉ tập trung vào **Module Delivery cơ bản** (tính phí ship). Tài liệu này nhằm lưu trữ tầm nhìn dài hạn để đảm bảo khả năng mở rộng sau này.

---

## 1. Phân biệt Module Delivery (Hiện tại) vs Hệ thống Logistics (Tương lai)

| Tiêu chí           | Module Delivery (Giai đoạn 1)                         | Hệ thống Logistics / TMS (Giai đoạn 2+)                                                            |
| :----------------- | :---------------------------------------------------- | :------------------------------------------------------------------------------------------------- |
| **Mục tiêu chính** | Cho khách biết **giá tiền** và **thời gian dự kiến**. | Giúp công ty **tiết kiệm chi phí**, **quản lý tài xế** và **tối ưu lộ trình**.                     |
| **Đối tượng dùng** | Khách hàng (Buyer) & Sales.                           | Điều phối viên (Dispatcher) & Tài xế.                                                              |
| **Quản lý xe**     | Không quan tâm xe nào chạy.                           | Quản lý hồ sơ xe (tải trọng, bảo trì, đăng kiểm, định mức xăng).                                   |
| **Lộ trình**       | Chỉ biết điểm đi -> điểm đến.                         | **Tối ưu đa điểm:** Giao 10 đơn hàng thì chạy đường nào ngắn nhất? Xếp hàng lên xe sao cho đầy xe? |
| **Theo dõi**       | Trạng thái tĩnh (Đã gửi -> Đã giao).                  | **Real-time:** Xe đang ở đâu trên bản đồ (GPS)? Tài xế đang chạy hay nghỉ?                         |
| **Bằng chứng**     | Không có (hoặc nhập tay).                             | **POD (Proof of Delivery):** Tài xế dùng App chụp ảnh hàng, khách ký tên trên điện thoại.          |

---

## 2. Các Tính Năng Logistics Nâng Cao (Future Features)

### 2.1. Fleet Management (Quản lý Đội xe)

-   **Hồ sơ phương tiện:**
    -   Quản lý thông tin chi tiết: Biển số, Loại xe (Tải lạnh/Xe máy), Tải trọng tối đa, Kích thước thùng xe.
    -   Nhắc nhở bảo trì, bảo dưỡng, hạn đăng kiểm, bảo hiểm.
-   **Hồ sơ tài xế:**
    -   Thông tin cá nhân, bằng lái, hạn bằng lái.
    -   Lịch sử chạy, chấm công tài xế.

### 2.2. Smart Dispatching (Điều phối Thông minh)

-   **Gợi ý ghép đơn (Load Planning):**
    -   Hệ thống tự động tính toán trọng lượng/thể tích của các đơn hàng chờ giao.
    -   Gợi ý: _"Xe 500kg đi tuyến Bình Dương đang trống 20%, nên ghép thêm đơn hàng #123"_.
-   **Tối ưu lộ trình (Route Optimization):**
    -   Sắp xếp thứ tự điểm giao hàng tối ưu nhất để tiết kiệm xăng và thời gian.
    -   Ví dụ: Kho -> Khách A -> Khách B -> Khách C -> Kho (thay vì chạy lòng vòng).

### 2.3. Driver Mobile App (Ứng dụng Tài xế)

-   **Nhận lệnh điều xe:** Tài xế nhận danh sách đơn hàng cần giao ngay trên điện thoại.
-   **Quy trình giao hàng:**
    -   Bấm "Bắt đầu đi" -> Tracking vị trí GPS.
    -   Đến nơi -> Bấm "Check-in".
    -   Giao hàng -> Chụp ảnh hàng hóa + Chữ ký khách hàng (Electronic POD).
    -   Cập nhật trạng thái đơn hàng thành "Đã giao" realtime về hệ thống ERP.
-   **Báo cáo sự cố:** Báo hỏng xe, tắc đường, khách không nhận hàng ngay trên App.

### 2.4. Cost Allocation & Analytics (Phân bổ Chi phí)

-   **Tính lãi/lỗ từng chuyến:**
    -   Ghi nhận chi phí thực tế: Tiền xăng, cầu đường, lương tài xế, khấu hao xe.
    -   Phân bổ chi phí này ngược lại cho từng đơn hàng trên chuyến xe đó.
-   **Báo cáo hiệu suất:**
    -   Tài xế nào chạy hiệu quả nhất?
    -   Tuyến đường nào chi phí cao bất thường?
    -   Tỷ lệ giao hàng thành công/đúng giờ (OTIF).

---

## 3. Lý do chưa triển khai ngay (Out of Scope)

-   **Độ phức tạp cao:** Xây dựng TMS tương đương với một dự án phần mềm độc lập.
-   **Ưu tiên nghiệp vụ:** Hiện tại khách hàng Miao Lin ưu tiên giải quyết bài toán **Pricing (Định giá B2B)** và **Delivery cơ bản** (tính phí ship cho khách) trước.
-   **Vận hành thủ công:** Với quy mô hiện tại, việc điều phối xe có thể thực hiện thủ công bởi nhân viên kho mà chưa cần hệ thống tự động hóa hoàn toàn.

---

_Tài liệu này dùng để tham chiếu khi khách hàng có nhu cầu mở rộng hệ thống vận chuyển trong tương lai._
