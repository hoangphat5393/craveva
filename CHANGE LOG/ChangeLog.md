# Báo cáo thay đổi

Dưới đây là danh sách các file đã được sửa đổi để khắc phục lỗi PSR-4 Autoloading và tương thích với Laravel 10+:

## 1. Modules/ServerManager

### Services

-   **Services/ServerManagerServiceProvider.php**
    -   Sửa Namespace từ `Providers` thành `Services` để khớp với cấu trúc thư mục.
    -   Loại bỏ phương thức `registerFactories()` chứa mã nguồn cũ (Legacy Factory) không còn tương thích với Laravel 10.

### Database/Migrations

Các file migration sau đã được chuyển đổi từ **Named Class** sang **Anonymous Class** (`return new class extends Migration`) để tuân thủ chuẩn PSR-4 khi nằm trong thư mục Modules:

-   `2025_01_01_00001_create_server_global_settings_table.php`
-   `2025_07_28_000001_create_server_hostings_table.php`
-   `2025_07_28_000002_create_server_domains_table.php`
-   `2025_07_28_000003_create_server_logs_table.php`
-   `2025_07_28_000004_create_server_settings_table.php`
-   `2025_07_28_000005_add_servermanager_permissions.php`
-   `2025_07_28_000006_create_server_providers_table.php`
-   `2025_07_28_000008_add_servermanager_to_modules_table.php`
-   `2025_07_28_000009_create_server_types_table.php`

## 2. Modules/Payroll

### Http/Requests

-   **OvertimeSetting/Request/RequestStoreRequest.php**
    -   Đổi tên class từ `PayCodeStoreRequest` thành `RequestStoreRequest` để khớp với tên file.
-   **OvertimeSetting/Request/RequestUpdateRequest.php**
    -   Đổi tên class từ `PayCodeUpdateRequest` thành `RequestUpdateRequest` để khớp với tên file.

## 3. Modules/Discount

### Database/Migrations

-   **2025_12_22_000000_create_discount_rules_table.php**
    -   Chuyển đổi sang Anonymous Class để khắc phục lỗi PSR-4.

## 4. Bảo trì & Sửa lỗi (2025-12-29)

### app/Http/Controllers

-   **TaskBoardController.php**
    -   Thay thế helper `company()` bằng `$this->company` để tránh lỗi truy cập thuộc tính protected và đảm bảo scope company nhất quán.
    -   Các khu vực logic bị ảnh hưởng:
        -   Đếm số lượng chờ duyệt (waiting approval) cho view employee/admin.
        -   Kiểm tra slug trạng thái trùng lặp theo company.
        -   Gán lại trạng thái mặc định khi xóa cột.
    -   Đã xác minh controller tuân thủ quy ước AccountBaseController và middleware, permissions.

### app/Providers

-   **AppServiceProvider.php**
    -   Được cập nhật và thêm vào danh sách upload để khắc phục các vấn đề hệ thống (composer/boot logic).

### Deployment Script

-   **upload_deploy.ps1**
    -   Cập nhật để bao gồm các file mới sửa đổi và đảm bảo thư mục remote tồn tại trước khi upload.
    -   Thêm upload cho controller và Blade views sử dụng trong task settings, recurring tasks, calendar, và reports.
    -   Giữ cấu trúc non-destructive (không xóa, chỉ thêm).

### Phạm vi an toàn

-   Không thay đổi authentication, middleware, routes, hoặc permission systems toàn cục.
-   Không sửa đổi database migrations cũ; logic ứng dụng chỉ thêm mới (additive) và scoped.
-   Không chạm vào vendor hoặc framework files.

## 5. Cập nhật (2025-12-31) - Ngôn ngữ, Menu & Giao diện

### app/Models

-   **LanguageSetting.php**
    -   Cập nhật nhãn ngôn ngữ tiếng Trung (xóa chú thích tiếng Anh):
        -   `zh-CN`: "简体中文"
        -   `zh-TW`: "繁體中文"

### Modules/LanguagePack

-   **Languages/app/en/app.php**
    -   Đổi tên menu item "Estimates" thành "Quotation".

### Deployment Script

-   **upload_deploy.ps1**
    -   Thêm file `app/Providers/AppServiceProvider.php`.
    -   Thêm file `app/Models/LanguageSetting.php`.
    -   Thêm file `Modules/LanguagePack/Languages/app/en/app.php`.
    -   Thêm file `resources/lang/en/app.php`.
    -   Thêm file `resources/views/sections/menu.blade.php`.
    -   Thêm các file Blade views:
        -   `resources/views/components/forms/custom-field.blade.php`
        -   `resources/views/contracts/contract-pdf.blade.php`
        -   `Modules/Recruit/Resources/views/components/cards/custom-question-field.blade.php`
        -  - `Modules/Recruit/Resources/views/jobs/offer-letter/offer-letter-pdf.blade.php`
    -   Thêm lệnh tạo thư mục (mkdir) cho các đường dẫn mới trên Staging và Hub servers.

## 6. Cập nhật (2026-02-13) - Pricing Module

### Modules/Pricing

#### Database/Migrations
-   **2026_02_11_121332_add_start_and_end_date_to_client_product_pricing_table.php**
    -   Thêm trường `start_date` và `end_date` vào bảng `client_product_pricings`.
    -   Xóa ràng buộc unique cũ (client_id, product_id) để cho phép nhiều giá theo thời gian.
    -   Backfill dữ liệu cũ với `end_date = 2099-12-31`.

#### Http/Controllers
-   **ClientPricingController.php**
    -   Cập nhật `store` và `update` methods để xử lý `start_date`, `end_date`.
    -   Thêm validation: `product_id` required, `start_date` required/future, `end_date` optional/after start.
    -   Thêm logic kiểm tra trùng lặp thời gian (overlap check).

#### Resources/Views
-   **client_pricing/ajax/create.blade.php** & **edit.blade.php**
    -   Thêm Datepicker cho Start/End Date.
    -   Thêm real-time validation (Javascript) hiển thị lỗi ngay khi nhập liệu.
    -   Disable nút Submit khi form chưa hợp lệ.
-   **client_pricing/index.blade.php**
    -   Cập nhật hiển thị cột Start/End Date trong bảng danh sách.

#### Tests/Unit
-   **ContractPricingTest.php**
    -   Thêm unit tests cho các trường hợp validation (bắt buộc, định dạng ngày, overlap).

### Deployment Script
-   **upload_hub.ps1**
    -   Cập nhật danh sách file mới (migration, docs, views).
    -   **DISABLED** remote execution (chỉ sync file, không chạy lệnh trên server).
-   **upload_staging.ps1**
    -   Cập nhật danh sách file mới.
    -   Thêm lệnh chạy migration mới trên server staging.
    -   **ENABLED** remote execution.
