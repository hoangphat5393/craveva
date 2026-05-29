# Settings menu — ERP label review (UX-007 follow-up)

**Ngày:** 2026-05-29  
**Phạm vi:** Cột menu Settings (`setting-sidebar.blade.php`) + module setting-sidebar includes.  
**Nguồn key:** LanguagePack `Modules/LanguagePack/Languages/...` → publish `lang/` + `Modules/*/Resources/lang/`.

---

## Inventory — toàn bộ label menu Settings

| # | Lang key | EN (hiện tại) | VI (hiện tại) | Nhóm |
|---|----------|---------------|---------------|------|
| G1 | `app.menu.settingsMenuGroupCompany` | Company & registration | Công ty & đăng ký | Group |
| 1 | `app.menu.accountSettings` | Company Settings | Cài đặt công ty | Company |
| 2 | `app.menu.businessAddresses` | Business Address | Địa chỉ doanh nghiệp | Company |
| 3 | `app.menu.signUpSetting` | Sign Up Settings | Cài đặt đăng ký | Company |
| G2 | `app.menu.settingsMenuGroupPersonal` | Personal | Cá nhân | Group |
| 4 | `app.menu.profileSettings` | Profile Settings | Cài đặt cấu hình | Personal |
| G3 | `app.menu.settingsMenuGroupSales` | Sales & billing | Bán hàng & thu tiền | Group |
| 5 | `app.menu.financeSettings` | Invoice & Estimate Settings | Cài đặt hóa đơn & báo giá | Sales |
| 6 | `app.menu.saleOrderSettings` | Sale Order Settings | Cài đặt đơn bán hàng | Sales |
| 7 | `einvoice::app.menu.einvoiceSettings` | E-Invoice Settings | E-Invoice Settings | Sales |
| 8 | `app.menu.contractSettings` | Contract Settings | Cài đặt hợp đồng | Sales |
| 9 | `app.menu.leadSettings` | Lead Settings | Cài đặt khách hàng tiềm năng | Sales |
| G4 | `app.menu.settingsMenuGroupProcurement` | Procurement, warehouse & production | Mua hàng, kho & sản xuất | Group |
| 10 | `purchase::app.menu.purchaseSettings` | Purchase Settings | Cài đặt mua hàng | Procurement |
| 11 | `warehouse::app.warehouseFlowSettingsMenu` | Warehouse Flow & Stock | Luồng kho & tồn | Procurement |
| 12 | `production::app.productionSettingsMenu` | Production Settings | Cài đặt Sản xuất | Procurement |
| 13 | `asset::app.menu.assetSettings` | Asset Settings | (module) | Procurement |
| G5 | `app.menu.settingsMenuGroupFinanceTax` | Finance & tax | Tài chính & thuế | Group |
| 14 | `app.menu.currencySettings` | Currency Settings | Cài đặt tiền tệ | Finance |
| 15 | `app.menu.taxSettings` | Tax Settings | Cài đặt thuế | Finance |
| 16 | `app.menu.paymentGatewayCredential` | Payment Credentials | Thông tin xác thực thanh toán | Finance |
| G6 | `app.menu.settingsMenuGroupHumanResources` | Human resources | Nhân sự | Group |
| 17 | `app.menu.attendanceSettings` | Attendance Settings | Cài đặt chấm công | HR |
| 18 | `app.menu.leaveSettings` | Leaves Settings | Cài đặt lá | HR |
| 19 | `payroll::app.menu.payrollSettings` | Payroll Settings | Cài đặt bảng lương | HR |
| 20 | `payroll::app.menu.overtimeSettings` | Overtime Settings | Cài đặt ngoài giờ | HR |
| 21 | `performance::app.performanceSettings` | Performance Settings | Cài đặt hiệu suất | HR |
| 22 | `recruit::app.menu.recruitSetting` | Recruit  | (module) | HR |
| 23 | `onboarding::clan.menu.onOffboardingSettings` | On Offboarding Settings | On Offboarding Settings | HR |
| G7 | `app.menu.settingsMenuGroupProjectsSupport` | Projects & support | Dự án & hỗ trợ | Group |
| 24 | `app.menu.projectSettings` | Project Settings | Thiết lập dự án | Projects |
| 25 | `app.menu.taskSettings` | Task Settings | (app) | Projects |
| 26 | `app.menu.timeLogSettings` | Time Log Settings | Cài đặt nhật ký thời gian | Projects |
| 27 | `app.menu.ticketSettings` | Ticket Settings | Cài đặt vé | Projects |
| 28 | `app.menu.messageSettings` | Message Settings | Cài đặt tin nhắn | Projects |
| G8 | `app.menu.settingsMenuGroupSystem` | System & customization | Hệ thống & tùy biến | Group |
| 29 | `app.menu.appSettings` | App Settings | Cài đặt ứng dụng | System |
| 30 | `app.menu.notificationSettings` | Notification Settings | Thiết lập thông báo | System |
| 31 | `app.menu.themeSettings` | Theme Settings | Cài đặt chủ đề | System |
| 32 | `app.menu.moduleSettings` | Module Settings | Cài đặt mô-đun | System |
| 33 | `app.menu.securitySettings` | Security Settings | Cài đặt hệ thống bảo vệ | System |
| 34 | `app.menu.customFields` | Custom Fields | Trường tùy chỉnh | System |
| 35 | `app.menu.rolesPermission` | Roles & Permissions | Vai trò & Quyền | System |
| 36 | `app.menu.customLinkSetting` | Custom Link Settings | Cài đặt liên kết tùy chỉnh | System |
| 37 | `app.menu.gdprSettings` | GDPR Settings | Cài đặt GDPR | System |
| 38 | `app.menu.googleCalendarSetting` | Google Calendar Settings | Cài đặt Lịch Google | System |
| 39 | `app.menu.storageSettings` | Storage Settings | Cài đặt lưu trữ | System |
| 40 | `app.menu.languageSettings` | Language Settings | (app) | System |
| 41 | `app.menu.socialLogin` | Social Login Settings | (app) | System |
| 42 | `sms::app.smsSetting` | SMS Setting | SMS Setting | System |
| 43 | `zoom::app.menu.zoomSetting` | Zoom Settings | Zoom Settings | System |
| G9 | `app.menu.settingsMenuGroupAdminTechnical` | Admin & technical | Quản trị & kỹ thuật | Group |
| 44 | *(hardcoded)* | Developer Tools | — | Admin |
| 45 | *(hardcoded)* | CodeMap | — | Admin |
| 46 | `app.menu.databaseBackupSetting` | Database Backup Settings | (app) | Admin |
| 47 | `superadmin.menu.billing` | Billing | (superadmin) | Admin |

---

## Review — Current | Suggested | Reason

| Lang key | Current (EN) | Suggested (EN) | Reason |
|----------|--------------|----------------|--------|
| `app.menu.businessAddresses` | Business Address | Business addresses | Số nhiều; trang quản lý nhiều địa chỉ. |
| `app.menu.saleOrderSettings` | Sale Order Settings | Sales order settings | Thống nhất với `saleOrders` / sidebar Sales; bỏ viết tắt SO. |
| `app.menu.leaveSettings` | Leaves Settings | Leave settings | Ngữ pháp EN; “leave” = nghỉ phép. |
| `app.menu.timeLogSettings` | Time Log Settings | Timesheet settings | Trùng terminology menu trái `timeLogs` → Timesheet. |
| `app.menu.paymentGatewayCredential` | Payment Credentials | Payment gateway settings | Trang cấu hình gateway, không chỉ credential. |
| `app.menu.securitySettings` | Security Settings | Security settings | Giữ; VI nên dịch “Bảo mật” thay “hệ thống bảo vệ”. |
| `recruit::app.menu.recruitSetting` | Recruit  | Recruitment settings | Thiếu chữ; khoảng trắng thừa; là settings không phải module. |
| `onboarding::clan.menu.onOffboardingSettings` | On Offboarding Settings | Onboarding & offboarding settings | Sửa lỗi chính tả “On Offboarding”. |
| `sms::app.smsSetting` | SMS Setting | SMS settings | Pattern “… Settings” như các mục khác. |
| `production::app.productionSettingsMenu` | Production Settings | Production settings | OK nghiệp vụ; chỉ chuẩn hóa chữ thường “settings”. |
| `warehouse::app.warehouseFlowSettingsMenu` | Warehouse Flow & Stock | Warehouse & inventory settings | ERP: inventory/stock rõ hơn “flow”. |
| `app.menu.developerTools` | *(hardcoded)* Developer Tools | Developer tools | Đưa vào LanguagePack; không hardcode blade. |
| `app.menu.codeMap` | *(hardcoded)* CodeMap | Code map | Tách từ; i18n. |
| `app.menu.settingsMenuGroupAdminTechnical` | Admin & technical | Administration & technical | “Admin” mơ hồ; nhất quán Title Case. |

| Lang key | Current (VI) | Suggested (VI) | Reason |
|----------|--------------|----------------|--------|
| `app.menu.profileSettings` | Cài đặt cấu hình | Cài đặt hồ sơ cá nhân | “Cấu hình” quá chung; đúng nghiệp vụ profile user. |
| `app.menu.leaveSettings` | Cài đặt lá | Cài đặt nghỉ phép | “Lá” = machine translation sai. |
| `app.menu.timeLogSettings` | Cài đặt nhật ký thời gian | Cài đặt bảng chấm công | Khớp Timesheet / chấm công ERP. |
| `app.menu.ticketSettings` | Cài đặt vé | Cài đặt phiếu hỗ trợ | “Vé” = ticket event; đúng là support ticket. |
| `app.menu.securitySettings` | Cài đặt hệ thống bảo vệ | Cài đặt bảo mật | Chuẩn ERP terminology. |
| `app.menu.notificationSettings` | Thiết lập thông báo | Cài đặt thông báo | Thống nhất “Cài đặt …” trong menu Settings. |
| `app.menu.projectSettings` | Thiết lập dự án | Cài đặt dự án | Cùng pattern. |
| `purchase::app.menu.purchaseSettings` | Cài đặt mua hàng | Cài đặt mua hàng & nhập kho | PO + GRN cùng trang; gợi ý scope. |
| `payroll::app.menu.overtimeSettings` | Cài đặt ngoài giờ | Cài đặt làm thêm giờ | “Overtime” chuẩn HR VN. |
| `einvoice::app.menu.einvoiceSettings` | E-Invoice Settings | Cài đặt hóa đơn điện tử | VI chưa dịch. |
| `sms::app.smsSetting` | SMS Setting | Cài đặt SMS | Pattern + tiếng Việt. |
| `onboarding::clan.menu.onOffboardingSettings` | On Offboarding Settings | Cài đặt onboarding & offboarding | VI chưa dịch. |
| `zoom::app.menu.zoomSetting` | Zoom Settings | Cài đặt Zoom | Giữ tên sản phẩm; thêm “Cài đặt”. |

**Giữ nguyên (đã OK):** `financeSettings`, `settingsMenuGroup*`, `saleOrderSettings` (VI), `warehouseFlowSettingsMenu` (VI), `attendanceSettings` (VI), `currencySettings`, `taxSettings`, `leadSettings` (VI).

---

## Terminology ERP — quy ước đề xuất

| Khái niệm | Dùng | Tránh |
|-----------|------|-------|
| Sales order | Sales order / Đơn bán hàng | SO, Orders (mơ hồ) |
| Purchase | Purchase order / Đơn mua hàng | PO trong label UI |
| Goods receipt | Goods receipt note / Phiếu nhập kho | GRN, Delivery order |
| Sales delivery | Sales delivery order / Phiếu giao bán | Sales DO, DO |
| Invoice / Estimate | Invoice & estimate / Hóa đơn & báo giá | Finance settings |
| Timesheet | Timesheet / Bảng chấm công | Time log |
| Support ticket | Support ticket / Phiếu hỗ trợ | Vé |
| Leave | Leave / Nghỉ phép | Lá, Leaves |

---

## Cơ chế lưu key (LanguagePack)

1. Sửa nguồn: `Modules/LanguagePack/Languages/app/{en,vi}/app.php` và module tương ứng.  
2. Publish: `php artisan languagepack:publish-translation` hoặc `php tests/scripts/sync_settings_menu_translations.php` (app en/vi).  
3. Tránh `ltm_translations` ghi đè: xóa row trùng key sau khi import Translation Manager.  
4. Locale UI: **Profile → Language** (không phải Company Settings → Language).

---

## Lịch sử

| Ngày | Ghi chú |
|------|---------|
| 2026-05-29 | Review + cập nhật key EN/VI ưu tiên (settings menu). |
| 2026-05-29 | Hoàn tất LanguagePack + blade i18n + publish lang + test pass. |
