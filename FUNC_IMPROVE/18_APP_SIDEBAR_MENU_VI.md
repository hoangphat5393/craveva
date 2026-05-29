# App sidebar — menu trái (ERP)

**Cập nhật:** 2026-05-29 · **UX-014 Done**  
**Quy tắc:** Chỉ **2 cấp** — accordion L1 + link L2 (không menu cấp 3).

**File chính:** `resources/views/sections/menu.blade.php` · ERP: `Modules/Purchase/Resources/views/sections/sidebar.blade.php`

---

## 1. Cấu trúc hiện tại

**Trước UX-009:** 1 accordion **Operations** (~16 mục).  
**Sau UX-009:** 4 accordion L1 (mua / đơn bán / kho / SX).

| #    | L1 EN / VI                                                      | Lang key                      | L2 (EN)                                                                     |
| ---- | --------------------------------------------------------------- | ----------------------------- | --------------------------------------------------------------------------- |
| —    | **Craveva AI** (nếu bật workspace và/hoặc assistant)            | `app.menu.cravevaAi`          | Workspace · Assistant (chỉ mục đã cấu hình)                                 |
| 1    | Home                                                            | —                             | Dashboard                                                                   |
| 2    | Work Management                                                 | `app.menu.workManagement`     | Contracts, Projects, Tasks, Timesheet, Calendar, Events, Messages           |
| 3    | **Purchasing** / Mua hàng                                       | `app.menu.procurement`        | Vendor, PO, GRN, Bills, Vendor payments, Vendor credits                     |
| 4    | **Sales orders** / Đơn bán hàng                                 | `app.menu.salesFulfillment`   | Sales orders, Sales DO, Sales history                                       |
| 5    | **Inventory** / Tồn kho                                         | `app.menu.inventoryWarehouse` | Products, **Opening stock**¹, Warehouses, Stock Overview², Stock movements³ |
| 6    | **Production** / Sản xuất                                       | `app.menu.productionHub`      | Production orders, BOM                                                      |
| 7    | **Customer Management** / Quản lý khách hàng                    | `app.menu.sales`              | Lead, Deal, Clients, Proposal, Quotation                                    |
| 8–15 | Pricing, Finance, **Human Resources**¹ · **Payroll**¹, Reports… | (xem code)                    | …                                                                           |

¹ UX-011 v2: tách **People** thành 2 accordion L1; Performance (OKR, Meetings…) nằm cuối **Human Resources**.

¹ `purchase::app.menu.inventory` = Opening stock / Tồn đầu kỳ.  
² `warehouse::app.adjustStock` = Stock Overview.  
³ `warehouse::app.stockMovements` = Stock movements.

**Stock by batch** (`warehouse.product-batches.index`): không thêm L2 sidebar — một màn danh sách, truy cập từ luồng **Stock Overview** / chi tiết tồn kho là đủ (UX-013 skipped).

Settings: icon ⚙ **footer** → `company-settings`.

---

## 2. Hướng cải thiện tiếp (chưa code)

| Ưu tiên | ID     | Việc                       | Đề xuất                                            |
| ------- | ------ | -------------------------- | -------------------------------------------------- |
| ~~P0~~  | UX-010 | ~~Sales vs Sales orders~~  | **Done** — CRM + Opening stock                     |
| ~~P1~~  | UX-015 | ~~AI menu trùng~~          | **Done** — Craveva AI (Workspace + Assistant)      |
| ~~P1~~  | UX-011 | ~~People quá dài~~         | **Done** — 2 L1: Human Resources + Payroll         |
| ~~P2~~  | UX-012 | ~~Thứ tự sidebar~~         | **Done** — Customer Management sau vận hành        |
| ~~P2~~  | UX-013 | ~~Stock by batch sidebar~~ | **Skipped** — chỉ 1 màn list, đủ từ Stock Overview |
| ~~P3~~  | UX-014 | ~~Kỹ thuật~~               | **Done** — dọn `@if(false)`; icon trùng            |

**Label rà thêm:** `timeLogs` VI · `salesHistory` · Tickets → Phiếu hỗ trợ.

---

## 3. Terminology

| Dùng                              | Tránh                                    |
| --------------------------------- | ---------------------------------------- |
| Customer Management, Sales orders | “Sales” cho cả CRM và SO                 |
| Opening stock                     | Inventory (mục con) trùng nhóm Inventory |
| Stock Overview, Stock movements   | Warehouse Stock Overview, History        |

---

## 4. Tham chiếu

| Việc               | Path                                                                                |
| ------------------ | ----------------------------------------------------------------------------------- |
| Menu               | `resources/views/sections/menu.blade.php`                                           |
| ERP sidebar        | `Modules/Purchase/Resources/views/sections/sidebar.blade.php`                       |
| Lang app           | `Modules/LanguagePack/Languages/app/{en,vi,zh-CN}/app.php`                          |
| Lang purchase menu | `Modules/LanguagePack/Languages/modules/Purchase/{locale}/app.php`                  |
| AI partial         | `resources/views/sections/partials/ai-sidebar-menu-items.blade.php`                 |
| Test               | `tests/Feature/AppSidebarOperationsSplitTest.php`                                   |
| People accordions  | `resources/views/sections/partials/people-sidebar-accordions.blade.php`             |
| HR / Payroll items | `human-resources-sidebar-menu-items` · `payroll-sidebar-menu-items`                 |
| CRM partial        | `resources/views/sections/partials/customer-management-sidebar-accordion.blade.php` |
| Test menu order    | `tests/Feature/AppSidebarMenuOrderTest.php`                                         |
| Test cleanup       | `tests/Feature/AppSidebarMenuCleanupTest.php`                                       |

---

## 5. Lịch sử

| Ngày       | Ghi chú                                           |
| ---------- | ------------------------------------------------- |
| 2026-05-29 | UX-009: tách Operations                           |
| 2026-05-29 | UX-010 P0: CRM + Opening stock                    |
| 2026-05-29 | Craveva AI: gộp Workspace + Assistant             |
| 2026-05-29 | UX-011: People menu group headers                 |
| 2026-05-29 | UX-011 v2: Human Resources + Payroll L1           |
| 2026-05-29 | UX-012: Customer Management sau vận hành          |
| 2026-05-29 | UX-014: dọn dead menu + icon L1                   |
| 2026-05-29 | UX-013: bỏ qua — Stock by batch không vào sidebar |
