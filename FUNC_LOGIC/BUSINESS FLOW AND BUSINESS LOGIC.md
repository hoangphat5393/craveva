# Craveva ERP — BUSINESS FLOW AND BUSINESS LOGIC

## 1. Phạm Vi & Vai Trò

- Hệ thống ERP phục vụ các quy trình: Order‑to‑Cash, Procure‑to‑Pay, Inventory, Pricing/Tax, Billing/Payments.
- Vai trò: Sales, Warehouse, Accounting/Finance, Purchasing, Admin.

## 2. Order‑to‑Cash (Bán Hàng)

### Luồng chính

1. Sales tạo Order với khách hàng, sản phẩm, giá, chiết khấu
2. Xác nhận Order (confirmed)
3. Tạo Invoice từ Order, kiểm tra thuế, tổng
4. Ghi nhận Payment: Unpaid → Partial → Paid
5. Đối soát và báo cáo doanh thu

### Trạng thái & chuyển trạng thái

- Order: draft → confirmed → completed/cancelled
- Invoice: unpaid → partial → paid → void (nếu cần)

### Luật kinh doanh

- Số Order/Invoice là duy nhất
- Không ghi nhận Payment vượt tổng phải thu
- Chiết khấu có thể theo dòng hoặc tổng; thuế theo cấu hình công ty

## 3. Procure‑to‑Pay (Mua Hàng)

### Luồng chính

1. Tạo Purchase Order (PO) → gửi Vendor
2. Nhận hàng (GRN) → nhập kho
3. Nhận hóa đơn mua → ghi nhận công nợ
4. Thanh toán nhà cung cấp → đối soát

### Luật kinh doanh

- PO phải có trạng thái rõ ràng (not_started/in_transaction/delivered)
- Không nhập âm tồn; GRN liên kết batch nếu quản lý theo lô

## 4. Inventory (Kho)

### Luồng chính

- Nhập/ xuất/ điều chỉnh tồn; (tùy chọn) quản lý theo lô/batch và hạn dùng
- Xuất kho liên kết Invoice; nhập kho liên kết GRN/PO

### Luật kinh doanh

- Không xuất âm kho
- Nếu quản lý lô: bắt buộc chọn lô (Batch / Lot Number) + Expiry khi xuất

## 5. Pricing & Taxes

### Luồng chính

- Đơn giá chuẩn theo Product; có thể thay đổi theo khách (client pricing) hoặc theo tầng (tier)
- Thuế VAT/GST/SST áp dụng theo luật công ty/quốc gia

### Luật kinh doanh

- Rounding theo chuẩn hệ thống; precision tiền/tỷ lệ cố định
- Chiết khấu dòng/tổng ảnh hưởng sub_total và total

## 6. Billing & Payments

### Luồng chính

- Tạo Payment cho Invoice; cập nhật trạng thái thanh toán
- Phương thức: chuyển khoản, tiền mặt, cổng thanh toán

### Luật kinh doanh

- Không vượt số phải thu
- Partial khi thanh toán nhỏ hơn tổng; Paid khi bằng tổng

## 7. F&B Enhancements (Đặt vị trí dữ liệu)

- Header (Invoice): Delivery Reference No., Cost per Unit (giá vốn tại xuất kho)
- Header (Order): Purchase Order Reference (Buyer PO)
- Product attributes: Unit of Measure, HS Code, Certification (hiển thị tự động)
- Batch/Expiry: cấp dòng hàng khi quản lý lô; nếu chưa có schema dòng, tạm thời lưu ở header bằng Custom Fields để thể hiện trên chứng từ

## 8. Acceptance Criteria (Given‑When‑Then)

### Tạo Invoice từ Order

- Given Order ở trạng thái confirmed
- When người dùng chọn "Generate Invoice" từ Order
- Then hệ thống tạo một Invoice liên kết, copy dòng hàng, tính thuế/chiết khấu/tổng chính xác

### Cập nhật thanh toán Invoice

- Given Invoice đang unpaid
- When ghi nhận Payment số tiền X
- Then trạng thái chuyển partial nếu X < tổng; paid nếu X = tổng

### Gắn Batch & Expiry vào xuất kho

- Given kho chọn lô với hạn dùng
- When xuất kho và lưu Invoice
- Then Batch/Expiry hiển thị trên Invoice/PDF và lưu vào dữ liệu chứng từ

## 9. Edge Cases & Exception Handling

- Hủy Order sau khi đã tạo Invoice: yêu cầu void/cancel theo policy, không mất dữ liệu
- Sai lệch giá/thuế do cấu hình: báo lỗi, yêu cầu xác nhận/điều chỉnh
- Thiếu batch/expiry khi bắt buộc: chặn lưu, hiển thị cảnh báo

## 10. KPIs & Reporting

- Doanh thu theo thời gian/khách/sản phẩm
- Công nợ phải thu/phải trả
- Tồn kho theo sản phẩm/lô/hạn dùng
- Hiệu quả chiết khấu và thuế

## 11. Risks & Mitigations

- Chưa có custom fields cấp dòng: cần migrations cho invoice_items để chuẩn F&B
- Thiếu multi‑warehouse: đánh giá mở rộng schema & luồng trước khi triển khai
- Rounding sai: cố định độ chính xác và test tự động

## 12. Code References

- Order model: [Order.php](file:///f:/web/new.craveva.com/app/Models/Order.php)
- Invoice model: [Invoice.php](file:///f:/web/new.craveva.com/app/Models/Invoice.php)
- Order Controller: [OrderController.php](file:///f:/web/new.craveva.com/app/Http/Controllers/OrderController.php)
- Invoice create UI: [create.blade.php](file:///f:/web/new.craveva.com/resources/views/invoices/ajax/create.blade.php)

## 13. UI Modules & Business Tie‑ins

- Dashboard: theo dõi tổng quan KPI, tiến độ
- Orders: khởi phát Order‑to‑Cash; liên kết Invoice 1‑1
- Purchase: khởi phát Procure‑to‑Pay; GRN/nhập kho
- Pricing: chính sách giá, chiết khấu; ảnh hưởng sub_total/total
- Finance: hóa đơn, thanh toán, chi phí, ngân hàng; báo cáo doanh thu
- Reports: báo cáo theo module (tasks, timelogs, finance, sales…)
- Work: hợp đồng, dự án, nhiệm vụ, timesheet; ảnh hưởng chi phí doanh thu
- Calendar/Events/Messages: lịch, sự kiện, trao đổi; hỗ trợ điều phối nghiệp vụ
- HR (Employees/Leaves/Attendance/Holidays): nguồn lực thực thi, hạn chế và quy tắc nghỉ làm

## 14. Homepage Business Context

- Giá trị sản phẩm: hợp nhất quản lý dự án và nhân sự trong một hệ thống, giao việc, theo dõi, đồng bộ thành viên, tối ưu lợi nhuận.

## 15. Business Flows mở rộng theo modules

- Purchase
    - Vendor Management: tạo/sửa vendor; liên quan PO/Bill/Payments
    - Purchase Orders: trạng thái đặt hàng → nhận hàng (GRN) → Bills
    - Vendor Payments/Credits: công nợ với nhà cung cấp
    - Inventory: kiểm soát tồn nhập/xuất/điều chỉnh; liên kết F&B batch khi triển khai
    - Reports: hiệu suất mua hàng, tồn kho, công nợ nhà cung cấp

- Server Manager
    - Hosting/Domain/Provider: quản lý tài nguyên hạ tầng; dashboard/thống kê/hoạt động
    - Business logic: theo dõi lifecycle domain/hosting, cảnh báo hết hạn

- Performance (OKR)
    - Objectives, Scoring, One‑on‑One meetings: theo dõi mục tiêu, chấm điểm, mentoring
    - Business logic: kỳ/performance cycle, quyền xem/đánh giá theo vai trò

- Affiliate
    - Affiliates, Referrals/Commissions, Payouts: dòng tiền hoa hồng
    - Business logic: tính hoa hồng, trạng thái chi trả, kiểm soát gian lận

- Webhooks
    - Webhooks/Logs: tích hợp hệ thống ngoài; quan sát sự kiện
    - Business logic: retry, bảo mật chữ ký, hạn mức call

## 16. Permissions & Roles

- Menu/route hiển thị theo `user_modules()` và `user()->permission('<feature>')`
- Acceptance Criteria:
    - Given user có permission “view_invoices”
    - When truy cập menu Finance → Invoices
    - Then hiển thị danh sách hoá đơn; nếu “none” thì không thấy mục

## 17. Reporting & KPIs theo Modules

- Finance: doanh thu, sales report, income vs expense
- Purchase: tồn kho, công nợ vendor, hiệu quả mua
- Work: tiến độ dự án/nhiệm vụ/timesheet
- Performance: chỉ số hoàn thành mục tiêu theo kỳ

## 18. Acceptance Criteria theo màn hình chính

- Orders
    - Given user có quyền view_order
    - When vào [orders.index](file:///f:/web/new.craveva.com/routes/web.php#L372)
    - Then hiển thị danh sách; tạo Order mới; thay đổi trạng thái; [make_invoice](file:///f:/web/new.craveva.com/routes/web.php#L359-L367)

- Invoices
    - Given user có quyền view_invoices
    - When vào [invoices.index](file:///f:/web/new.craveva.com/routes/web.php#L626)
    - Then tạo/sửa/xem; gửi invoice; nhắc thanh toán; [store_offline_payment](file:///f:/web/new.craveva.com/routes/web.php#L603)

- Payments
    - Given user có quyền view_payments
    - When vào [payments.index](file:///f:/web/new.craveva.com/routes/web.php#L662-L676)
    - Then ghi nhận payment; bulk payments; export/download chứng từ

- Credit Notes
    - Given user có quyền đối soát credit notes
    - When vào [creditnotes.index](file:///f:/web/new.craveva.com/routes/web.php#L688-L690)
    - Then áp credit vào invoice; convert invoice; download

- Purchase
    - Given quyền view_purchase_order
    - When vào [purchase-order.index](file:///f:/web/new.craveva.com/Modules/Purchase/Routes/web.php#L46-L73)
    - Then tạo PO; nhận hàng; ghi nhận bill; vendor payments; inventory; reports

## 19. Đầu vào/Dữ liệu & Ràng buộc

- Orders/Invoices: khách hàng, sản phẩm, quantity, đơn giá, chiết khấu, thuế, currency; số chứng từ duy nhất
- Payments: số tiền ≤ tổng phải thu; partial/paid đúng quy tắc
- Purchase: PO trạng thái hợp lệ; GRN/Inventory không âm tồn; vendor payments logic công nợ

## 20. Liên kết Authentication tới Business

- Người chưa đăng nhập bị chuyển tới [login](file:///f:/web/new.craveva.com/Modules/Subdomain/Routes/web.php#L68-L75)
- Sau login, điều hướng workspace/superadmin/home theo [LoginController](file:///f:/web/new.craveva.com/app/Http/Controllers/LoginController.php#L148-L185)

## 21. Login & Localization Acceptance

- Given người dùng truy cập trang bảo vệ (ví dụ /account/companies) khi chưa đăng nhập
- When hệ thống redirect tới [login](file:///f:/web/new.craveva.com/Modules/Subdomain/Routes/web.php#L68-L75)
- Then hiển thị form Email/Password, Forgot password, Stay logged in, và chọn ngôn ngữ (English/Arabic/Bulgarian/Vietnamese/Simplified/Traditional Chinese/Singapore). Sau đăng nhập, điều hướng theo vai trò/quyền.

## 22. Invoices — Acceptance (Runtime)

- Environment: http://127.0.0.1:8000 (server chạy, xác nhận redirect login và menu Finance hiển thị theo quyền)

### Gửi hóa đơn (Send Invoice)

- Given hoá đơn ở trạng thái draft hoặc unpaid
- When người dùng chọn gửi hoá đơn (không phải “mark_as_send”)
- Then hệ thống đặt send_status=1, nếu draft chuyển trạng thái thành unpaid, phát sự kiện tới client; [sendInvoice](file:///f:/web/new.craveva.com/app/Http/Controllers/InvoiceController.php#L1022-L1048)

### Đánh dấu đã gửi (Mark As Sent)

- Given hoá đơn bất kỳ
- When người dùng chọn “mark_as_send”
- Then hệ thống đặt send_status=1, không phát sự kiện, trả thông báo invoiceMarkAsSent

### Nhắc thanh toán (Payment Reminder)

- Given hoá đơn chưa thanh toán với client hợp lệ
- When người dùng nhấn “Remind for payment”
- Then hệ thống phát PaymentReminderEvent tới client; [remindForPayment](file:///f:/web/new.craveva.com/app/Http/Controllers/InvoiceController.php#L1050-L1064)

### Yêu cầu thanh toán offline (Store Offline Payment)

- Given người dùng gửi yêu cầu thanh toán offline cho hoá đơn hoặc đơn hàng
- When hệ thống tạo Payment ở trạng thái pending, có thể đính kèm bill
- Then nếu là hoá đơn, chuyển invoice sang pending-confirmation; trả về redirect tới trang chi tiết; [storeOfflinePayment](file:///f:/web/new.craveva.com/app/Http/Controllers/InvoiceController.php#L1277-L1316)

### Duyệt thanh toán offline (Approve Offline Invoice)

- Given invoice ở trạng thái pending-confirmation với payment pending
- When người dùng duyệt
- Then Payment thành complete, gán bank_account_id; Invoice chuyển paid/partial theo số tiền; [approveOfflineInvoice](file:///f:/web/new.craveva.com/app/Http/Controllers/InvoiceController.php#L984-L1020)

### Bật/tắt địa chỉ giao hàng (Toggle Shipping Address)

- Given invoice có cờ show_shipping_address
- When người dùng bật/tắt hiển thị địa chỉ giao hàng
- Then hệ thống chuyển giá trị yes/no và trả updateSuccess; [toggleShippingAddress](file:///f:/web/new.craveva.com/app/Http/Controllers/InvoiceController.php#L1463-L1469)

### Tải xuống PDF (Download Invoice)

- Given user có quyền xem phù hợp
- When tải PDF từ trang invoice
- Then hệ thống xuất template theo invoiceSetting, stream hoặc download bằng invoice_number; [download](file:///f:/web/new.craveva.com/app/Http/Controllers/InvoiceController.php#L471-L507)

## 23. Authentication & Impersonation — Acceptance

### SuperAdmin Login

- Given người dùng là SuperAdmin và có quyền `view_superadmin`
- When đăng nhập vào hệ thống
- Then truy cập panel superadmin, xem danh sách công ty, có thể chọn workspace; [workspaces](file:///f:/web/new.craveva.com/app/Http/Controllers/SuperAdmin/SuperAdminController.php#L259-L269), [chooseWorkspace](file:///f:/web/new.craveva.com/app/Http/Controllers/SuperAdmin/SuperAdminController.php#L271-L293)

### Company Admin Login

- Given người dùng có vai trò admin trong công ty
- When đăng nhập
- Then được điều hướng tới workspace công ty (account dashboard), menu module hiển thị theo permissions.

### Employee Login

- Given người dùng có vai trò employee với quyền phù hợp
- When đăng nhập
- Then truy cập workspace công ty và chức năng tương ứng theo menu/phân quyền.

### Client Login

- Given người dùng có vai trò client
- When đăng nhập
- Then truy cập khu vực khách hàng, xem chứng từ/đề nghị/phương thức thanh toán theo quyền.

### Impersonate as Company (SuperAdmin)

- Given SuperAdmin đang xem chi tiết một công ty có admin hoạt động
- When chọn "Login as Company"
- Then hệ thống bắt đầu impersonation, set session (impersonate, impersonate_company_id, user), đăng nhập bằng admin công ty; UI hiển thị nút “Stop Impersonation”; [loginAsCompany](file:///f:/web/new.craveva.com/app/Http/Controllers/SuperAdmin/CompanyController.php#L541-L562)

### Stop Impersonation

- Given đang ở trạng thái impersonation trong workspace công ty
- When người dùng chọn “Stop Impersonation”
- Then hệ thống khôi phục người dùng SuperAdmin ban đầu, đăng nhập lại và điều hướng về trang chi tiết công ty; [stopImpersonate](file:///f:/web/new.craveva.com/app/Http/Controllers/SuperAdmin/SuperAdminController.php#L246-L257)

## 24. Role-Based Menu Visibility — Acceptance

### SuperAdmin Menu

- Given user là SuperAdmin
- When truy cập sidebar superadmin
- Then hiển thị các mục: Packages, Companies, Billing, Admin FAQ, Super Admin, Offline Request, Support Ticket, Front Settings, Affiliates, Settings; tham chiếu nhãn theo [vi/superadmin.php](file:///f:/web/new.craveva.com/Modules/LanguagePack/Languages/app/vi/superadmin.php#L110-L130); các chức năng hoạt động theo [routes/SuperAdmin/web.php](file:///f:/web/new.craveva.com/routes/SuperAdmin/web.php#L52-L80)

### Company Workspace Menu

- Given user thuộc công ty (admin/employee) và có quyền phù hợp
- When truy cập sidebar workspace công ty
- Then hiển thị các nhóm: Dashboard, Orders, Purchase, Tier Pricing, Leads, Clients, Finance, Reports, Work, My Calendar, Events, Messages, Letter, HR; menu được dựng theo [menu.blade.php](file:///f:/web/new.craveva.com/resources/views/sections/menu.blade.php#L327-L343) và include các sidebar module như [Purchase](file:///f:/web/new.craveva.com/Modules/Purchase/Resources/views/sections/sidebar.blade.php), [EInvoice](file:///f:/web/new.craveva.com/Modules/EInvoice/Resources/views/sections/finance/sidebar.blade.php)

## 25. Package–Module — Acceptance

- BA/PM View (không mã)
    - Package xác định bộ module được phép dùng cho công ty.
    - Khi công ty đổi gói, hệ thống bật/tắt module tương ứng; menu/khả năng truy cập phản ánh ngay theo quyền.
    - Người dùng chỉ thấy và dùng các tính năng thuộc gói; truy cập ngoài gói bị ẩn hoặc chặn.
    - Cache menu theo người dùng được làm mới sau thay đổi gói để hiển thị chính xác.

- Given SuperAdmin tạo/cập nhật Package và chọn các module trong `module_in_package`
- When công ty được gán hoặc thay đổi Package
- Then hệ thống đồng bộ `ModuleSetting` của công ty: các module có trong `module_in_package` được đặt `is_allowed=1` và `status='active'`, module khác đặt `is_allowed=0` và `status='deactive'`; menu và quyền hiển thị theo `user_modules()`.

- Given người dùng trong công ty cố truy cập module không nằm trong `module_in_package`
- When kiểm tra quyền hiển thị/chạy controller
- Then hệ thống ẩn menu hoặc từ chối truy cập do `ModuleSetting::checkModule($moduleName)` trả về false.

[CompanyObserver::updateModuleSettings](file:///f:/web/new.craveva.com/app/Observers/CompanyObserver.php#L1008-L1030) và [PackageObserver::updated](file:///f:/web/new.craveva.com/app/Observers/SuperAdmin/PackageObserver.php#L1-L38) đảm bảo đồng bộ sau khi thay đổi gói.

```php
// f:/web/new.craveva.com/app/Models/ModuleSetting.php
public static function checkModule($moduleName)
{
    $module = ModuleSetting::where('module_name', $moduleName);
    if (in_array('admin', user_roles())) { $module = $module->where('type', 'admin'); }
    elseif (in_array('client', user_roles())) { $module = $module->where('type', 'client'); }
    elseif (in_array('employee', user_roles())) { $module = $module->where('type', 'employee'); }
    $module = $module->where('status', 'active');
    $module = $module->first();
    return (bool)$module;
}
```

- Given gói hoặc package_id của công ty thay đổi
- When đồng bộ ModuleSetting
- Then cache `user_modules_<user_id>` được xoá để hiển thị đúng module mới theo người dùng.

```php
// f:/web/new.craveva.com/app/Observers/CompanyObserver.php
User::withoutGlobalScopes([ActiveScope::class, CompanyScope::class])
    ->where('company_id', $company->id)->each(function ($user) {
        cache()->forget('user_modules_' . $user->id);
    });
```
