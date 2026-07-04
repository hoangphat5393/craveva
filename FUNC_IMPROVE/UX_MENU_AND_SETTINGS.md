# UX — Menu Settings & App sidebar (ERP)

**Cập nhật:** 2026-06-16 (sync trạng thái UX-001 Production BOM + UX-006 Client Listing)
**Liên quan:** `05_SO_DO_PO_GRN_REFACTOR.md`, `../docs/UI_BACKEND_UX_STANDARD.md` §13

---

## Phần A — Settings sidebar (UX-007 Done)

**File:** `resources/views/components/setting-sidebar.blade.php` · module includes qua `craveva_plugins()`

### Vấn đề đã xử lý

- Danh sách phẳng 40–50 mục; SO/PO/GRN settings rải rác; label `Finance Settings` gây nhầm (thực chất Invoice & Estimate).
- **Đã implement (2026-05-29):** nhóm có tiêu đề (`x-setting-menu-group`), reorder theo nghiệp vụ, đổi label Invoice & Estimate, gộp GRN terms vào Purchase Settings; bỏ menu Delivery Order Settings (route redirect Purchase Settings).

### Thứ tự nhóm (Phương án A)

Công ty & đăng ký → Cá nhân → Bán hàng & thu tiền → Mua hàng, kho & SX → Tài chính & thuế → Nhân sự → Dự án & hỗ trợ → Hệ thống → Admin/kỹ thuật (Developer Tools, CodeMap, Billing).

### Document terms (SO / PO / GRN)

| Chứng từ     | Trang settings      | DB                                              |
| ------------ | ------------------- | ----------------------------------------------- |
| PO, GRN      | Purchase Settings   | `purchase_settings.purchase_terms`, `grn_terms` |
| SO, Sales DO | Sale Order Settings | `invoice_settings.order_terms`                  |

---

## Phần B — Settings labels (UX-007 follow-up)

**Nguồn key:** LanguagePack → publish `lang/` + `Modules/*/Resources/lang/`

### Quy ước ERP (trích)

| Dùng                          | Tránh            |
| ----------------------------- | ---------------- |
| Sales order / Đơn bán hàng    | SO, Orders mơ hồ |
| Invoice & estimate            | Finance settings |
| Timesheet / Bảng chấm công    | Time log         |
| Support ticket / Phiếu hỗ trợ | Vé               |
| Leave / Nghỉ phép             | Lá               |

### Label ưu tiên sửa (EN/VI)

- `businessAddresses` → plural EN; VI profile → «Cài đặt hồ sơ cá nhân»
- `leaveSettings` VI: «nghỉ phép» (không «lá»)
- `recruitSetting`, `einvoiceSettings`, onboarding — dịch đủ VI
- Developer Tools / CodeMap — đưa vào LanguagePack (không hardcode blade)

**Publish:** `php artisan languagepack:publish-translation` hoặc `php tests/scripts/sync_settings_menu_translations.php`

Bảng inventory đầy đủ ~47 mục: xem git history file `18_SETTINGS_MENU_LABEL_REVIEW.md` (đã gộp pass 4).

---

## Phần C — App sidebar trái (UX-009 / UX-014 Done)

**File:** `resources/views/sections/menu.blade.php` · ERP: `Modules/Purchase/Resources/views/sections/sidebar.blade.php`  
**Quy tắc:** 2 cấp — accordion L1 + link L2.

### Cấu trúc L1 (sau UX-009)

| L1                                        | L2 (trích)                                                     |
| ----------------------------------------- | -------------------------------------------------------------- |
| Craveva AI                                | Workspace, Assistant                                           |
| Work Management                           | Projects, Tasks, Timesheet, …                                  |
| **Purchasing**                            | Vendor, PO, GRN, Bills, …                                      |
| **Sales orders**                          | SO, Sales DO, Sales history                                    |
| **Inventory**                             | Products, Opening stock, Warehouses, Stock Overview, Movements |
| **Production**                            | Production orders, BOM                                         |
| **Customer Management**                   | Lead, Deal, Clients, Quotation, …                              |
| Pricing, Finance, HR, Payroll, Reports, … |                                                                |

- UX-011: tách **Human Resources** + **Payroll** (2 accordion).
- UX-012: Customer Management sau khối vận hành.
- UX-013 skipped: Stock by batch không thêm L2 (truy cập từ Stock Overview).
- Settings: icon ⚙ footer → `company-settings`.

### Test

`AppSidebarOperationsSplitTest`, `AppSidebarMenuOrderTest`, `AppSidebarMenuCleanupTest`

---

## Phần D — UX backlog còn mở

| ID     | Module     | Vấn đề ngắn                                  | Trạng thái  | Ghi chú                                                      |
| ------ | ---------- | -------------------------------------------- | ----------- | ------------------------------------------------------------ |
| UX-001 | Production | BOM create — Add row UX; block RM = FG       | Done core   | Add component picker + JS append line + backend block FG = RM; regression pass 2026-06-16 |
| UX-004 | Production | Badge % planned vs shadow                    | Gated       | Chỉ khi bật `yield_uom_shadow_enabled` — `11_SHADOW_YIELD_*` |
| UX-006 | Clients    | Client listing — tier, contract, outstanding | Done core   | Phase 1–5 + 6.1/6.3/6.4 + 7.2 đã triển khai; category filter + edit custom field regression pass 2026-06-16; optional còn lại ở `14_CLIENT_LISTING_TABLE_UX_PLAN.md` |

**Done (2026-05/06):** UX-001 Production BOM add component / block RM = FG · UX-002/003 RM-FG labels · UX-005 opening stock help · UX-006 Client listing core · UX-007 Settings sidebar · UX-008 variance badge · UX-009/010 sidebar split — chi tiết trong Phần A/C, `14_CLIENT_LISTING_TABLE_UX_PLAN.md`, hoặc `git log -- FUNC_IMPROVE/10_UX_UI_IMPROVEMENT_BACKLOG.md`.

---

## Lịch sử gộp

| Ngày       | Ghi chú                                                |
| ---------- | ------------------------------------------------------ |
| 2026-05-23 | UX-007 plan Settings                                   |
| 2026-05-29 | UX-007 implement + labels; UX-009 sidebar split        |
| 2026-05-27 | Pass 7: gộp `10_UX_UI_IMPROVEMENT_BACKLOG.md` → Phần D |
| 2026-06-16 | Sync UX-001: Production BOM create đã có Add Raw Material picker, JS append row, backend guard chặn component trùng Manufactured Product; `ProductionBomCreateFormCostingScriptTest.php` + `ProductionBomAndOrderTenantFlowTest.php` pass |
| 2026-06-16 | Sync UX-006: Client Listing core Done; category filter + edit custom field save regression pass; optional backlog giữ trong `14_CLIENT_LISTING_TABLE_UX_PLAN.md` |
