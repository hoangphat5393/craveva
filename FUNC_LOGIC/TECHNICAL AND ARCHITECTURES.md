# Craveva ERP — TECHNICAL AND ARCHITECTURES

## 1. Executive Summary

Craveva ERP là nền tảng ERP theo kiến trúc module trên Laravel, tách ranh giới tính năng rõ ràng, bảo toàn an toàn hệ thống với chính sách thay đổi bảo thủ. Hệ thống hỗ trợ các quy trình lõi như Order‑to‑Cash, Procure‑to‑Pay, Inventory, Pricing/Tax, Billing/Payments, với UI Blade, dữ liệu MySQL và triển khai Linux (Ubuntu) trên GCP qua Nginx + PHP‑FPM.

## 2. Technology Stack

-   Backend: Laravel (PHP), Eloquent ORM, Traits chia sẻ (ví dụ CustomFieldsTrait)
-   Frontend: Blade templates/components, jQuery/AJAX theo chuẩn hệ thống
-   Database: MySQL, migrations chỉ mang tính bổ sung
-   Infrastructure: Ubuntu + Nginx + PHP‑FPM (GCP)
-   Deployment: Upload ZIP via PowerShell + scp cho Staging/Hub, migrate từ xa cho Hub

## 3. Modular Architecture & Boundaries

-   Tất cả logic tính năng nằm trong Modules/<ModuleName> theo cấu trúc: Http/Controllers, Entities (Models), Resources/views, Database/Migrations, Routes, Config.
-   Code lõi ngoài module chỉ gồm các mô hình/dịch vụ dùng chung (ví dụ app/Models), nhưng mọi tính năng mới phải đặt trong Module theo rule.

## 4. Application Layers

-   UI Layer: Blade views/components, form, pdf templates
-   Application Layer: Controllers theo module điều phối luồng nghiệp vụ, xử lý form, gọi services nếu có
-   Domain Layer: Models/Entities với fillable, casts, relations; observers cho vòng đời
-   Data Layer: Migrations bổ sung, Eloquent cho truy vấn

## 5. Data Model Conventions

-   Chỉ thêm migrations mới; không sửa migrations cũ; không drop trừ chỉ thị rõ ràng
-   Models: giới hạn ở fillable/casts/relations; không đặt logic nghiệp vụ nặng trong Models
-   Custom Fields: lưu dữ liệu tuỳ biến theo Group/Model/Company, hiển thị tự động nhờ Trait

## 6. Cross‑Cutting Concerns

-   Security & Config: không hard‑code secrets; dùng .env → config → code
-   Permissions & Roles: không thay đổi quyền/routing/global middleware khi không có chỉ thị
-   Error Handling: giữ nguyên app/Exceptions; quan sát qua logs hệ thống

## 7. Deployment Pipeline

-   Staging: chuẩn hoá upload qua [upload_staging.ps1](file:///f:/web/new.craveva.com/upload_staging.ps1); giải nén, set quyền; migrate thực hiện thủ công khi cần
-   Hub: upload qua [upload_hub.ps1](file:///f:/web/new.craveva.com/upload_hub.ps1); sau giải nén thực thi `php artisan migrate --force` tự động

## 8. Observability & Logging

-   Logs trong storage/logs (thư mục cấm chỉnh sửa); kiểm soát error bằng chuẩn Laravel
-   Theo dõi trạng thái tiến trình qua thông báo và observers

## 9. Performance & Scalability

-   Tối ưu truy vấn Eloquent theo nhu cầu; tránh N+1 bằng eager loading hợp lý
-   Phân rã module; tách UI logic khỏi domain để dễ mở rộng

## 10. Extension Points

-   Custom Fields: thêm trường tuỳ biến ở cấp model (Invoice, Order, Product, …)
-   Migrations bổ sung: thêm bảng/trường mới mà không phá vỡ schema cũ
-   Views: mở rộng UI trong Blade (không chỉnh layouts trừ chỉ thị)

## 11. Project Rules (Tóm lược vận hành)

-   Không chạm thư mục cấm (vendor, node_modules, storage/framework, …)
-   Không xoá/đổi tên/di chuyển file trừ khi có chỉ thị "DELETE THIS FILE"/"REMOVE THIS CODE ENTIRELY"
-   Logic tính năng chỉ trong Modules/
-   Mọi thay đổi database phải là migrations **mới**

## 12. Key Code References

-   Order model: [Order.php](file:///f:/web/new.craveva.com/app/Models/Order.php)
-   Invoice model: [Invoice.php](file:///f:/web/new.craveva.com/app/Models/Invoice.php)
-   Custom Fields Trait: [CustomFieldsTrait.php](file:///f:/web/new.craveva.com/app/Traits/CustomFieldsTrait.php)
-   Order Controller: [OrderController.php](file:///f:/web/new.craveva.com/app/Http/Controllers/OrderController.php)
-   Invoice create UI: [create.blade.php](file:///f:/web/new.craveva.com/resources/views/invoices/ajax/create.blade.php)
-   F&B Custom Field migrations:
    -   [2026_01_12_190624_add_product_custom_fields_fb.php](file:///f:/web/new.craveva.com/database/migrations/2026_01_12_190624_add_product_custom_fields_fb.php)
    -   [2026_01_13_034358_add_invoice_custom_fields_fb.php](file:///f:/web/new.craveva.com/database/migrations/2026_01_13_034358_add_invoice_custom_fields_fb.php)

## 14. UI Navigation Map (Tham chiếu thực tế)

-   Menu chính: [menu.blade.php](file:///f:/web/new.craveva.com/resources/views/sections/menu.blade.php)
-   Nhóm mục:
    -   Dashboard: Private, Advanced
    -   Orders: danh sách đơn hàng
    -   Purchase: sidebar module mua hàng (includeIf)
    -   Pricing: sidebar module pricing (includeIf)
    -   Finance: Proposals, Estimates, Invoices, Payments, Credit Notes, Expenses, Discount Rules, Bank Accounts
    -   Reports: Task, Time Log, Weekly Timesheet, Finance, Income vs Expense, Leave, Attendance, Expense, Deal, Sales
    -   Work: Contracts, Projects, Tasks, TimeLogs
    -   Calendar: My Calendar
    -   Events: Events
    -   Messages: internal messaging
    -   Letter/Performance: sidebar modules (includeIf)
    -   HR: Employees, Leaves, Attendance, Holidays

## 15. Homepage Value Proposition

-   Theo trang chủ local "Home | New Craveva": quản lý dự án, nhân sự, giao việc, theo dõi tiến độ, tăng lợi nhuận; nêu rõ các năng lực quản lý project/tasks/members.

## 16. Module Catalog (Theo sidebar thực tế)

-   Purchase: [sidebar](file:///f:/web/new.craveva.com/Modules/Purchase/Resources/views/sections/sidebar.blade.php)
    -   Vendors, Products, Purchase Orders, Bills, Vendor Payments, Vendor Credits, Inventory, Reports
-   Server Manager: [sidebar](file:///f:/web/new.craveva.com/Modules/ServerManager/Resources/views/sections/sidebar.blade.php)
    -   Hosting, Domain, Provider, Statistics, Activities
-   Performance: [sidebar](file:///f:/web/new.craveva.com/Modules/Performance/Resources/views/sections/sidebar.blade.php)
    -   Dashboard, Objectives, OKR Scoring, One‑on‑One Meetings
-   Webhooks: [sidebar](file:///f:/web/new.craveva.com/Modules/Webhooks/Resources/views/sections/sidebar.blade.php)
    -   Webhooks, Logs (tuỳ quyền)
-   Affiliate: [sidebar](file:///f:/web/new.craveva.com/Modules/Affiliate/Resources/views/sections/superadmin/sidebar.blade.php)
    -   Affiliate Dashboard, Affiliates, Referrals/Commissions, Payouts
-   Project Roadmap: [sidebar](file:///f:/web/new.craveva.com/Modules/ProjectRoadmap/Resources/views/sections/work/sidebar.blade.php)
    -   Roadmap gắn với Projects
-   E‑Invoice (Finance): [finance sidebar](file:///f:/web/new.craveva.com/Modules/EInvoice/Resources/views/sections/finance/sidebar.blade.php)
    -   E‑Invoice index trong nhóm Finance

## 17. Route & Permission Integration

-   Sub‑menu component: [sub-menu-item.blade.php](file:///f:/web/new.craveva.com/resources/views/components/sub-menu-item.blade.php)
-   Mỗi mục tuỳ thuộc vào `user_modules()` và `user()->permission('<feature>')`; điều này phản ánh kiểm soát khả dụng theo vai trò/quyền.

## 18. Core Routes (Web)

-   Orders: [routes/web.php](file:///f:/web/new.craveva.com/routes/web.php#L359-L377)
    -   Resource: `Route::resource('orders', OrderController::class)`
    -   Make Invoice: `orders.make_invoice`
    -   Download: `orders.download`
-   Invoices: [routes/web.php](file:///f:/web/new.craveva.com/routes/web.php#L603-L626)
    -   Resource: `Route::resource('invoices', InvoiceController::class)`
    -   Store Offline Payment: `invoices.store_offline_payment`
    -   Send Invoice, Payment Reminder, Download, Add Item, Toggle Shipping Address
    -   Recurring Invoices: nhóm `invoices/recurring-invoices`
-   Payments: [routes/web.php](file:///f:/web/new.craveva.com/routes/web.php#L662-L676)
    -   Resource: `Route::resource('payments', PaymentController::class)->except(['edit','update'])`
    -   Bulk payments, offline methods, download
-   Credit Notes: [routes/web.php](file:///f:/web/new.craveva.com/routes/web.php#L676-L690)
    -   Resource: `Route::resource('creditnotes', CreditNoteController::class)`
    -   Apply to invoice, convert invoice, download
-   Purchase Module: [Modules/Purchase/Routes/web.php](file:///f:/web/new.craveva.com/Modules/Purchase/Routes/web.php#L46-L73)
    -   Purchase Products, Adjustment Reasons, Vendor Payments, Contacts, Inventory, Reports
-   E‑Invoice Module: [Modules/EInvoice/Routes/web.php](file:///f:/web/new.craveva.com/Modules/EInvoice/Routes/web.php#L20-L37)
    -   Settings, Index, Export XML, Client Modal/Save
-   Project Roadmap: [Modules/ProjectRoadmap/Routes/web.php](file:///f:/web/new.craveva.com/Modules/ProjectRoadmap/Routes/web.php#L12-L19)
-   Webhooks: [Modules/Webhooks/Routes/web.php](file:///f:/web/new.craveva.com/Modules/Webhooks/Routes/web.php#L16-L25)

## 19. Authentication Flow

-   Login: [Subdomain/Routes/web.php](file:///f:/web/new.craveva.com/Modules/Subdomain/Routes/web.php#L68-L75) → `route('login')`
-   Middleware: [Authenticate.php](file:///f:/web/new.craveva.com/app/Http/Middleware/Authenticate.php#L18-L26) đảm bảo redirect tới login nếu chưa auth
-   Redirect logic sau login: [LoginController.php](file:///f:/web/new.craveva.com/app/Http/Controllers/LoginController.php#L148-L185) — xử lý workspace/superadmin/home

## 20. Authentication UI & Localization

-   Login page hiển thị Email, Password, Forgot password, “Stay logged in”, nút “Go to Home”/Sign up.
-   Selector ngôn ngữ: English, عربي, Bulgarian, Vietnamese, 简体中文, 繁體中文, Singapore.
-   Khi truy cập trang bảo vệ (ví dụ [account/companies](file:///f:/web/new.craveva.com/Modules/Subdomain/Routes/web.php#L84-L95)), nếu chưa đăng nhập sẽ bị chuyển tới login.

## 13. Architectural Decisions (ADR Highlights)

-   Ưu tiên Custom Fields ở mức header chứng từ (Invoice/Order) để triển khai nhanh không phá schema
-   Trường batch/expiry ở cấp dòng (invoice_items) cần migrations schema thật khi triển khai quản lý lô hàng chuẩn F&B
-   Tách deployment Staging/Hub, chỉ Hub tự migrate để giảm rủi ro

## 21. Realtime Preview Verification

-   Environment: http://127.0.0.1:8000 (Laravel dev server chạy thành công; assets phục vụ bình thường).
-   Authentication redirect: truy cập /account/companies khi chưa đăng nhập → chuyển hướng về [login](file:///f:/web/new.craveva.com/Modules/Subdomain/Routes/web.php#L68-L75) theo [Authenticate](file:///f:/web/new.craveva.com/app/Http/Middleware/Authenticate.php#L18-L26).
-   Login UI: hiển thị đầy đủ trường Email/Password, Forgot password, Stay logged in và selector ngôn ngữ (English/Arabic/Bulgarian/Vietnamese/简体中文/繁體中文/Singapore).
-   Finance/Invoices: menu Invoices khả dụng theo quyền; các hành động chính được hỗ trợ bởi controller:
    -   Send Invoice: [sendInvoice](file:///f:/web/new.craveva.com/app/Http/Controllers/InvoiceController.php#L1022-L1048)
    -   Payment Reminder: [remindForPayment](file:///f:/web/new.craveva.com/app/Http/Controllers/InvoiceController.php#L1050-L1064)
    -   Store Offline Payment: [storeOfflinePayment](file:///f:/web/new.craveva.com/app/Http/Controllers/InvoiceController.php#L1277-L1316)
    -   Toggle Shipping Address: [toggleShippingAddress](file:///f:/web/new.craveva.com/app/Http/Controllers/InvoiceController.php#L1463-L1469)
    -   Download Invoice PDF: [download](file:///f:/web/new.craveva.com/app/Http/Controllers/InvoiceController.php#L471-L507)

## 22. Authentication Roles & Impersonation

-   Role Types:
    -   SuperAdmin: truy cập quản trị toàn cục, chọn workspace, có thể impersonate vào công ty.
    -   Company: Admin/Employee hoạt động trong workspace công ty; menu hiển thị theo permissions.
    -   Client: cổng khách hàng, xem/nhận chứng từ theo quyền.
-   Impersonation Flow (SuperAdmin):
    -   Bắt đầu impersonation: [loginAsCompany](file:///f:/web/new.craveva.com/app/Http/Controllers/SuperAdmin/CompanyController.php#L541-L562)
        -   Ghi session: impersonate, impersonate_company_id, user; đăng nhập bằng `Auth::loginUsingId($admin->user_auth_id)`.
    -   Dừng impersonation: [stopImpersonate](file:///f:/web/new.craveva.com/app/Http/Controllers/SuperAdmin/SuperAdminController.php#L246-L257)
        -   Khôi phục người dùng gốc từ session, `Auth::loginUsingId($userAuthId)`, chuyển về trang công ty.
    -   Chọn workspace: [workspaces](file:///f:/web/new.craveva.com/app/Http/Controllers/SuperAdmin/SuperAdminController.php#L259-L269) / [chooseWorkspace](file:///f:/web/new.craveva.com/app/Http/Controllers/SuperAdmin/SuperAdminController.php#L271-L293)

## 23. Role-Based Menus (SuperAdmin vs Company)

-   SuperAdmin Sidebar (theo ngôn ngữ menu): [vi/superadmin.php](file:///f:/web/new.craveva.com/Modules/LanguagePack/Languages/app/vi/superadmin.php#L110-L130)
    -   Packages, Companies, Billing, Admin FAQ, Super Admin, Offline Request, Support Ticket, Front Settings, Affiliates, Settings
    -   Routes liên quan: [routes/SuperAdmin/web.php](file:///f:/web/new.craveva.com/routes/SuperAdmin/web.php#L52-L80) (workspaces/choose_workspace, superadmin, invoices, permissions)
-   Company Sidebar (workspace công ty): [menu.blade.php](file:///f:/web/new.craveva.com/resources/views/sections/menu.blade.php#L327-L343)
    -   Dashboard, Orders, Purchase, Tier Pricing, Leads, Clients, Finance (Invoices/Payments/Credit Notes/Bank Accounts...), Reports, Work (Projects/Tasks/Time Logs), My Calendar, Events, Messages, Letter, HR (Employees/Leaves/Attendance/Holidays)
    -   Các module phụ liên kết qua includeIf: ví dụ [Modules/Purchase sidebar](file:///f:/web/new.craveva.com/Modules/Purchase/Resources/views/sections/sidebar.blade.php), [Modules/EInvoice finance sidebar](file:///f:/web/new.craveva.com/Modules/EInvoice/Resources/views/sections/finance/sidebar.blade.php)

## 24. Package–Module Association (SuperAdmin → Company)

### BA/PM View (không mã)

-   Mục tiêu: gói (Package) xác định bộ module được phép dùng cho từng công ty.
-   SuperAdmin cấu hình danh sách module trong mỗi Package.
-   Khi công ty được gán/đổi Package, hệ thống tự động bật/tắt các module của công ty theo danh sách gói.
-   Menu hiển thị theo module đã bật và quyền người dùng; chỉ thấy chức năng thuộc gói.
-   Cache menu theo người dùng được làm mới sau khi đổi gói để phản ánh đúng thay đổi.

Các tham chiếu kỹ thuật (liên kết, không hiển thị mã):

-   Cập nhật gói: [PackageController::update](file:///f:/web/new.craveva.com/app/Http/Controllers/SuperAdmin/PackageController.php#L149-L179)
-   Đồng bộ theo gói: [PackageObserver::updated](file:///f:/web/new.craveva.com/app/Observers/SuperAdmin/PackageObserver.php#L1-L38)
-   Cập nhật module công ty: [CompanyObserver::updateModuleSettings](file:///f:/web/new.craveva.com/app/Observers/CompanyObserver.php#L1008-L1030)
-   Lọc module theo vai trò: [user_modules](file:///f:/web/new.craveva.com/app/Helper/start.php#L326-L371)
-   Migration đồng bộ trạng thái: [2024_11_06_115214_update_modules_according_to_selected_packages.php](file:///f:/web/new.craveva.com/database/migrations/2024_11_06_115214_update_modules_according_to_selected_packages.php#L1-L44)

-   Package lưu danh sách module được chọn trong trường JSON `module_in_package`.
    -   Cập nhật gói: [PackageController::update](file:///f:/web/new.craveva.com/app/Http/Controllers/SuperAdmin/PackageController.php#L149-L179) sử dụng `modifyRequest` để encode dữ liệu.

```php
// f:/web/new.craveva.com/app/Http/Controllers/SuperAdmin/PackageController.php
public function update(UpdateRequest $request, $id)
{
    $this->editPermission = user()->permission('edit_packages');
    abort_403(!($this->editPermission == 'all'));
    if ($request->module_in_package == null) {
        return Reply::error(__('superadmin.messages.moduleBlank'));
    }
    if ($request->has('is_recommended') && $request->is_recommended == 'on') {
        Package::where('is_recommended', 1)->update(['is_recommended' => 1]);
    }
    $package = Package::with('companies')->find($id);
    $data = $this->modifyRequest($request);
    $package->update($data);
    $this->updateTrialPackage($package, $request);
    return Reply::redirect(route('superadmin.packages.index'), __('messages.updateSuccess'));
}
```

-   Khi gói thay đổi danh sách module, hệ thống đồng bộ ModuleSetting theo công ty.
    -   Quan sát gói: [PackageObserver::updated](file:///f:/web/new.craveva.com/app/Observers/SuperAdmin/PackageObserver.php#L1-L38) gọi `CompanyObserver->updateModuleSettings` cho từng công ty.
    -   Đồng bộ công ty khi package thay đổi: [CompanyObserver::saasSaving](file:///f:/web/new.craveva.com/app/Observers/CompanyObserver.php#L1039-L1051) kiểm tra `package_id` dirty và cập nhật ModuleSetting.

```php
// f:/web/new.craveva.com/app/Observers/SuperAdmin/PackageObserver.php
public function updated(Package $package)
{
    $package->companies->each(function ($company) use ($package) {
        if ($package->isDirty('module_in_package')) {
            (new CompanyObserver())->updateModuleSettings($company);
        }
        clearCompanyValidPackageCache($company->id);
    });
}
```

```php
// f:/web/new.craveva.com/app/Observers/CompanyObserver.php
public function updateModuleSettings($company): void
{
    $moduleSettings = ModuleSetting::where('company_id', $company->id)->get();
    $moduleInPackage = collect(json_decode(Package::where('id', $company->package_id)->first()->module_in_package));
    self::widgetUpdate($company, $moduleInPackage->toArray());
    $activeModuleSettings = [];
    $inactiveModuleSettings = [];
    foreach ($moduleSettings as $moduleSetting) {
        if ($moduleInPackage->contains($moduleSetting->module_name)) {
            $activeModuleSettings[] = $moduleSetting->id;
        } else {
            $inactiveModuleSettings[] = $moduleSetting->id;
        }
    }
    ModuleSetting::whereIn('id', $activeModuleSettings)->update(['is_allowed' => 1, 'status' => 'active']);
    ModuleSetting::whereIn('id', $inactiveModuleSettings)->update(['is_allowed' => 0, 'status' => 'deactive']);
    $this->clearCompanyUserCache($company);
}
```

-   Truy hồi module khả dụng theo vai trò người dùng được cache theo user id.
    -   Helper: [user_modules](file:///f:/web/new.craveva.com/app/Helper/start.php#L326-L371) lọc `ModuleSetting` theo `is_allowed=1`, `status='active'` và `type`.

```php
// f:/web/new.craveva.com/app/Helper/start.php
function user_modules()
{
    $user = user();
    if (!$user) { return []; }
    if (user()->is_superadmin) { return []; }
    if (cache()->has('user_modules_' . $user->id)) {
        return cache('user_modules_' . $user->id);
    }
    $module = \App\Models\ModuleSetting::where('is_allowed', 1);
    if (in_array('admin', user_roles())) { $module = $module->where('type', 'admin'); }
    elseif (in_array('client', user_roles())) { $module = $module->where('type', 'client'); }
    elseif (in_array('employee', user_roles())) { $module = $module->where('type', 'employee'); }
    $module = $module->where('status', 'active');
    $module->select('module_name');
    $module = $module->get();
    $moduleArray = [];
    foreach ($module->toArray() as $item) { $moduleArray[] = array_values($item)[0]; }
    cache()->put('user_modules_' . $user->id, $moduleArray);
    return $moduleArray;
}
```

-   Đồng bộ lịch sử: migration cập nhật trạng thái modules theo package cho tất cả công ty hiện hữu.
    -   [2024_11_06_115214_update_modules_according_to_selected_packages.php](file:///f:/web/new.craveva.com/database/migrations/2024_11_06_115214_update_modules_according_to_selected_packages.php#L1-L44)

```php
// f:/web/new.craveva.com/database/migrations/2024_11_06_115214_update_modules_according_to_selected_packages.php
public function up(): void
{
    $companies = Company::all();
    foreach($companies as $company){
        $package = Package::findOrFail($company->package_id);
        $modulesInPackage = json_decode($package->module_in_package, true);
        ModuleSetting::where('company_id', $company->id)
            ->whereIn('module_name', $modulesInPackage)
            ->update(['status' => 'active']);
        ModuleSetting::where('company_id', $company->id)
            ->whereNotIn('module_name', $modulesInPackage)
            ->update(['status' => 'deactive']);
    }
}
```
