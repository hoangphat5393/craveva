# Báo cáo chức năng đã hoàn thành — Craveva ERP

## Thông tin chung

-   Môi trường: Staging
-   URL demo: https://staging.craveva.com/account/companies
-   Trạng thái truy cập: Trang đăng nhập hiển thị các trường `Email Address`, `Password`, tuỳ chọn `Stay logged in`, liên kết `Forgot your password?`, và chọn ngôn ngữ `English`/`中國人`.

## Phạm vi chức năng (theo module hiện có)

-   Affiliate — web routes (đăng ký/quan hệ người dùng cơ bản)
-   Asset — web + api routes (quản lý tài sản, loại tài sản)
-   Biolinks — web routes (cấu hình liên kết sinh học/landing)
-   Biometric — web + api routes (tích hợp/ghi nhận dữ liệu sinh trắc)
-   CyberSecurity — web routes (cấu hình/bảo mật cơ bản)
-   Discount — web + api routes (cấu hình khuyến mãi/giảm giá)
-   EInvoice — web + api routes (hóa đơn điện tử)
-   LanguagePack — web + api routes (gói ngôn ngữ, chuyển đổi đa ngôn ngữ)
-   Letter — web routes (mẫu thư, quản lý template)
-   Payroll — web + api routes (mã lương, cấu hình pay codes)
-   Performance — web + api routes (hiệu suất, KPI)
-   ProjectRoadmap — web routes (lộ trình dự án)
-   Purchase — web + api routes (sản phẩm mua hàng, quy trình mua)
-   QRCode — web + api routes (tạo mã QR, định dạng/loại)
-   Recruit — web + api routes (tuyển dụng)
-   ServerManager — web + api routes (quản lý máy chủ, jobs/events)
-   Sms — web + api routes (cấu hình SMS, đa ngôn ngữ)
-   Subdomain — web + api routes (quản lý subdomain)
-   Webhooks — web + api routes (sự kiện webhook, biến cấu hình)
-   Zoom — web + api routes (lịch/meeting, thông báo mời)

## Nền tảng & tiêu chuẩn kỹ thuật

-   Kiến trúc module Laravel: `Modules/<Module>` với `Routes/`, `Config/`, `Entities/`…
-   Phân tách cấu hình theo module (`config.php`, `xss_ignore.php` nơi cần thiết)
-   Hỗ trợ đa ngôn ngữ: ví dụ `Modules/Sms/Resources/lang` bao gồm `vi`, `en`, và nhiều locale khác

## Kiểm thử & chất lượng

-   Smoke test staging: Truy cập URL demo hiển thị màn hình đăng nhập và chọn ngôn ngữ thành công
-   Kiểm tra routes: Các module liệt kê có tệp `web.php` và/hoặc `api.php`, sẵn sàng gắn vào UI/REST

## Hạn chế hiện tại / việc tiếp theo

-   Liên kết các chức năng với user stories cụ thể để nghiệm thu chi tiết
-   Bổ sung tài liệu sử dụng cho từng module (luồng, quyền hạn, thông số)
-   Triển khai test tích hợp/UI theo phạm vi từng module

## Liên kết

-   Demo: https://staging.craveva.com/account/companies
