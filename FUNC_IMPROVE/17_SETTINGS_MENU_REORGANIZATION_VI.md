# Settings menu — rà soát & đề xuất sắp xếp lại

**Ngày:** 2026-05-23  
**Nguồn:** Phản hồi PM — menu Settings (cột giữa khi vào `/account/settings` và các trang con) quá dài, khó tìm; SO / PO / DO settings nằm rải rác.  
**Trạng thái:** Đề xuất — **chưa implement** (chỉ tài liệu).  
**Liên quan:** `FUNC_IMPROVE/05_SO_DO_PO_GRN_REFACTOR_VI.md`, `FUNC_LOGIC/DESIGN_BACKEND_UI_UX_VI.md` §13 (company document terms), `FUNC_IMPROVE/10_UX_UI_IMPROVEMENT_BACKLOG.md` (UX-007).

---

## 1. Phạm vi

| Menu                            | File chính                                                     | Ghi chú                                                                              |
| ------------------------------- | -------------------------------------------------------------- | ------------------------------------------------------------------------------------ |
| **Settings sidebar** (cột giữa) | `resources/views/components/setting-sidebar.blade.php`         | Trọng tâm tài liệu này                                                               |
| Module settings (append)        | `Modules/*/Resources/views/sections/setting-sidebar.blade.php` | Include qua `craveva_plugins()`                                                      |
| **App sidebar** (cột trái tối)  | `resources/views/sections/menu.blade.php`                      | Menu vận hành (Home, Sales, Operations, Finance…) — xử lý riêng nếu PM phàn nàn thêm |

---

## 2. Vấn đề (root cause)

1. **Danh sách phẳng** — không có nhóm / collapse; chỉ có ô search (`#search-setting-menu`).
2. **Tên gây nhầm:** `Finance Settings` (`invoice-settings`) thực chất là **Invoice & Estimate** (prefix, template, payment), không bao gồm Sale Order / Purchase.
3. **SO / PO / DO tách rời:**
    - **Sale Order Settings** — mục core ~vị trí #8 (`sales-order-settings`).
    - **Purchase Settings** + **Delivery Order Settings** — module Purchase, append **cuối list** (sau Sign Up, GDPR…).
4. **Thứ tự module plugin** — `@foreach (craveva_plugins() as $item)` → thứ tự từ `Module::allEnabled()` (cache), thường theo tên module (A→Z), **không theo nghiệp vụ**.
5. **Dev / Billing lẫn vận hành** — Developer Tools, CodeMap, Billing cùng hàng với HR/Payroll.
6. **Company bật nhiều module** → có thể **40–50 mục** trong một cột.

---

## 3. Inventory — core app

File: `resources/views/components/setting-sidebar.blade.php`

| #   | Menu key / route                 | Label (EN)                 | Permission / module                                                 |
| --- | -------------------------------- | -------------------------- | ------------------------------------------------------------------- |
| 1   | `company-settings.index`         | Account Settings           | `manage_company_setting`                                            |
| 2   | `business-address.index`         | Business Addresses         | `manage_company_setting`                                            |
| 3   | `app-settings.index`             | App Settings               | `manage_app_setting`                                                |
| 4   | `profile-settings.index`         | Profile Settings           | (luôn)                                                              |
| 5   | `notifications.index`            | Notification Settings      | `manage_notification_setting`                                       |
| 6   | `currency-settings.index`        | Currency Settings          | `manage_currency_setting`                                           |
| 7   | `payment-gateway-settings.index` | Payment Gateway Credential | `manage_payment_setting`                                            |
| 8   | `invoice-settings.index`         | **Finance Settings**       | `manage_finance_setting` + invoices/estimates/orders/leads/payments |
| 9   | `sales-order-settings.index`     | **Sale Order Settings**    | `manage_finance_setting` + `orders`                                 |
| 10  | `contract-settings.index`        | Contract Settings          | `manage_contract_setting` + contracts                               |
| 11  | `taxes.index`                    | Tax Settings               | `manage_tax`                                                        |
| 12  | `ticket-settings.index`          | Ticket Settings            | tickets                                                             |
| 13  | `project-settings.index`         | Project Settings           | projects                                                            |
| 14  | `attendance-settings.index`      | Attendance Settings        | attendance                                                          |
| 15  | `leaves-settings.index`          | Leave Settings             | leaves                                                              |
| 16  | `custom-fields.index`            | Custom Fields              | `manage_custom_field_setting`                                       |
| 17  | `role-permissions.index`         | Roles & Permissions        | `manage_role_permission_setting`                                    |
| 18  | `message-settings.index`         | Message Settings           | messages                                                            |
| 19  | `lead-settings.index`            | Lead Settings              | leads                                                               |
| 20  | `timelog-settings.index`         | Time Log Settings          | timelogs                                                            |
| 21  | `task-settings.index`            | Task Settings              | tasks                                                               |
| 22  | `security-settings.index`        | Security Settings          | (luôn)                                                              |
| 23  | `theme-settings.index`           | Theme Settings             | `manage_theme_setting`                                              |
| 24  | `module-settings.index`          | Module Settings            | `manage_module_setting`                                             |
| 25  | `storage-settings.index`         | Storage Settings           | non-Craveva + permission                                            |
| 26  | `language-settings.index`        | Language Settings          | non-Craveva + permission                                            |
| 27  | `social-auth-settings.index`     | Social Login               | non-Craveva + permission                                            |
| 28  | `google-calendar-settings.index` | Google Calendar            | permission + global flag                                            |
| 29  | `custom-link-settings.index`     | Custom Link                | permission                                                          |
| 30  | `gdpr-settings.index`            | GDPR                       | permission / client                                                 |
| 31  | `database-backup-settings.index` | Database Backup            | role `superadmin`                                                   |
| 32  | `sign-up-settings.index`         | Sign Up Settings           | `manage_company_setting`                                            |
| —   | _(plugin block — xem §4)_        |                            | `checkCompanyPackageIsValid` + `craveva_plugins()`                  |
| 33  | `developertools.index`           | Developer Tools            | dev permission                                                      |
| 34  | `developertools.codemap`         | CodeMap                    | dev permission                                                      |
| 35  | `billing.index`                  | Billing                    | role `admin`                                                        |

### 3.1 Company document terms (SO / PO / DO) — vị trí hiện tại

| Chứng từ | Trang settings                                | DB / cột                                                             |
| -------- | --------------------------------------------- | -------------------------------------------------------------------- |
| PO       | Purchase Settings → tab Purchase Settings     | `purchase_settings.purchase_terms`                                   |
| SO       | Sale Order Settings → tab Sale Order Settings | `invoice_settings.order_terms` (fallback `invoice_terms`)            |
| DO       | **Delivery Order Settings** (menu riêng)      | `purchase_settings.delivery_order_terms` (fallback `purchase_terms`) |

Chi tiết pattern UI: `FUNC_LOGIC/DESIGN_BACKEND_UI_UX_VI.md` §13.

---

## 4. Inventory — module plugins

Include: `@foreach (craveva_plugins() as $item) @includeIf(strtolower($item).'::sections.setting-sidebar')`

| Module      | File                                                               | Menu item(s)                                       |
| ----------- | ------------------------------------------------------------------ | -------------------------------------------------- |
| Asset       | `Modules/Asset/Resources/views/sections/setting-sidebar.blade.php` | Asset Settings                                     |
| EInvoice    | `Modules/EInvoice/...`                                             | E-Invoice Settings                                 |
| Onboarding  | `Modules/Onboarding/...`                                           | Onboarding / Offboarding (1–2)                     |
| Payroll     | `Modules/Payroll/...`                                              | Payroll Settings, Overtime Settings                |
| Performance | `Modules/Performance/...`                                          | Performance Settings                               |
| Production  | `Modules/Production/...`                                           | Production Settings                                |
| Purchase    | `Modules/Purchase/...`                                             | **Purchase Settings**, **Delivery Order Settings** |
| Recruit     | `Modules/Recruit/...`                                              | Recruit Settings                                   |
| Sms         | `Modules/Sms/...`                                                  | SMS Settings                                       |
| Warehouse   | `Modules/Warehouse/...`                                            | Warehouse Flow Settings                            |
| Zoom        | `Modules/Zoom/...`                                                 | Zoom Settings                                      |

**Helper:** `craveva_plugins()` — `app/Helper/start.php` (cache keys từ `Module::allEnabled()`).

---

## 5. Phương án đề xuất

### 5.1 Phương án A — Nhóm có tiêu đề (khuyến nghị P0)

- Giữ nguyên route / controller từng trang.
- **Đổi thứ tự** trong `setting-sidebar.blade.php`.
- **Bỏ** (hoặc thu hẹp) `@foreach` plugin một cục — `include` từng module đúng nhóm.
- Thêm component header nhóm, ví dụ `<li class="settings-menu-group">...</li>` + CSS trong `resources/scss/settings.scss`.

**Effort:** nhỏ–trung bình. **Rủi ro:** thấp.

### 5.2 Phương án B — Hub một trang nhiều tab

Ví dụ:

- **Sales & Billing** — tab: Invoice/Estimate, Sale Order, E-Invoice, (Tax?).
- **Procurement** — tab: Purchase, Delivery Order.

**Effort:** lớn (route, quyền, bookmark, test). **Rủi ro:** trung bình.

### 5.3 Phương án C — Chỉ đổi nhãn

- `Finance Settings` → **Invoice & Estimate Settings** (LanguagePack `app.menu.financeSettings` hoặc key mới).
- Không đổi thứ tự.

**Effort:** rất nhỏ. Nên làm kèm A.

---

## 6. Thứ tự đề xuất (Phương án A)

```
[Công ty & đăng ký]
  Account Settings
  Business Addresses
  Sign Up Settings

[Cá nhân]
  Profile Settings

[Bán hàng & thu tiền]
  Invoice & Estimate Settings     ← route invoice-settings (đổi label)
  Sale Order Settings
  E-Invoice Settings
  Contract Settings
  Lead Settings

[Mua hàng, kho & sản xuất]
  Purchase Settings
  Delivery Order Settings
  Warehouse Flow Settings
  Production Settings
  Asset Settings

[Tài chính & thuế]
  Currency Settings
  Tax Settings
  Payment Gateway Credential

[Nhân sự]
  Attendance Settings
  Leave Settings
  Payroll Settings
  Overtime Settings
  Performance Settings
  Recruit Settings
  On/Offboarding Settings

[Dự án & hỗ trợ]
  Project Settings
  Task Settings
  Time Log Settings
  Ticket Settings
  Message Settings

[Hệ thống & tùy biến]
  App Settings
  Notification Settings
  Theme Settings
  Module Settings
  Security Settings
  Custom Fields
  Roles & Permissions
  Custom Link Settings
  GDPR Settings
  Google Calendar Setting
  Language / Storage / Social Login (nếu bật)

[Admin / kỹ thuật]
  Developer Tools
  CodeMap
  Database Backup Setting
  Billing
```

---

## 7. Kế hoạch implement (khi được duyệt)

| Bước | Việc                                                     | File                                                      |
| ---- | -------------------------------------------------------- | --------------------------------------------------------- |
| 1    | PM/BA sign-off thứ tự nhóm + tên nhóm (VI/EN)            | —                                                         |
| 2    | Component `x-setting-menu-group` (optional)              | `resources/views/components/setting-menu-group.blade.php` |
| 3    | Reorder + include module theo nhóm                       | `setting-sidebar.blade.php`                               |
| 4    | Label Invoice & Estimate                                 | `Modules/LanguagePack/Languages/app/en/app.php`, `vi`     |
| 5    | Regression: search menu, active state, permission ẩn mục | Manual + Pest nếu có test settings                        |
| 6    | (Tùy chọn) Hub tab — phase 2                             | Controllers tương ứng                                     |

**Không đổi trong phase 1:** logic lưu `order_terms`, `purchase_terms`, `delivery_order_terms`; layout tab nội dung từng trang (đã chỉnh riêng 2026-05).

---

## 8. App sidebar (menu trái) — ghi chú riêng

Nếu PM chỉ thêm menu trái (AI Workspace, Reports, Recruit…):

- File: `resources/views/sections/menu.blade.php`
- Đã có nhóm accordion: Home, Work Management, Sales, Operations, Finance, People…
- Đề xuất tách doc: `FUNC_IMPROVE/18_APP_SIDEBAR_MENU_VI.md` (khi có yêu cầu riêng).

---

## 9. Lịch sử

| Ngày       | Ghi chú                                                                            |
| ---------- | ---------------------------------------------------------------------------------- |
| 2026-05-23 | Tạo doc từ phân tích conversation Settings UX (SO/PO/DO alignment + menu quá dài). |
