# Audit Reports (ERP — menu Reports)

**Phạm vi:** Báo cáo trong **sidebar Reports** (ảnh chụp: Task, Time log, Weekly timesheet, Finance, Income vs Expense, Leave, Attendance, Expense, Deal, Sales). **Không** có module nwidart riêng tên “Reports”; route nằm trong **`routes/web.php`** nhóm `account` + middleware `auth`, `multi-company-select`, `email_verified`.  
**Ngày audit:** 2026-04-08

---

## 1. Tổng quan

| Hạng mục                           | Giá trị                                                                                                                                                                                  |
| ---------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Module trong `module_settings`** | Cần **`reports`** ∈ `user_modules()` để hiện nhóm menu (xem `menu.blade.php`).                                                                                                           |
| **Controller**                     | 11 controller báo cáo ERP trong `app/Http/Controllers/` (gồm `AuditReportController`, `IncomeVsExpenseReportController`, `TimelogWeeklyApprovalController`, …).                          |
| **View**                           | Thư mục `resources/views/reports/` (tasks, finance, timelogs, …).                                                                                                                        |
| **API**                            | Không có nhóm API riêng cho các báo cáo này trong phạm vi audit.                                                                                                                         |
| **“Audit Report” trong menu**      | **Có (2026-04-08):** `audit-report.index`, quyền **`view_audit_report`**, bảng **`user_activities`** (scope company). Chỉ hiện log từ `logUserActivity`, không phải full DB audit trail. |
| **Purchase “Reports”**             | Module Purchase có `reports.index` riêng — **đang ẩn** trong sidebar (`permission` … `&& false`) — xem `FUNC_LOGIC/AUDIT_PURCHASE_MODULE_VI.md`.                                         |

---

## 2. Route chính (`routes/web.php`, prefix `account/…`)

| Menu (app)        | Route name (index / vào cửa)      | Controller                        | Ghi chú                                    |
| ----------------- | --------------------------------- | --------------------------------- | ------------------------------------------ |
| Task Report       | `task-report.index`               | `TaskReportController`            | + chart, employee-wise, consolidated       |
| Time Log Report   | `time-log-report.index`           | `TimelogReportController`         | + consolidated, project-wise, export       |
| Weekly Timesheet  | `time-log-weekly-report.index`    | `TimelogWeeklyApprovalController` | + `weekly-pending-time-log-report.report`  |
| Finance Report    | `finance-report.index`            | `FinanceReportController`         | + chart                                    |
| Income Vs Expense | `income-expense-report.index`     | `IncomeVsExpenseReportController` | resource                                   |
| Leave Report      | `leave-report.leave_quota` (menu) | `LeaveReportController`           | resource + quota export                    |
| Attendance Report | `attendance-report.index`         | `AttendanceReportController`      | resource                                   |
| Expense Report    | `expense-report.index`            | `ExpenseReportController`         | + category report, chart                   |
| Deal Report       | `lead-report.index`               | `LeadReportController`            | URI có `deal-report/*` cho vài action      |
| Sales Report      | `sales-report.index`              | `SalesReportController`           | resource                                   |
| Audit Report      | `audit-report.index`              | `AuditReportController`           | chỉ `index`; `view_audit_report` = **all** |

---

## 3. Menu & quyền (`resources/views/sections/menu.blade.php`)

- Điều kiện nhóm: `in_array('reports', user_modules())` và từng mục con: **`sidebarUserPermissions['view_*_report']`** (lấy từ `sidebar_user_perms()` — **`permission_type_id`**, trong đó **4 = “all”** theo `Permission` constants) **cộng** module phụ thuộc (`tasks`, `timelogs`, `payments`, …).
- **Trùng lặp:** Cùng một khối submenu Reports xuất hiện **hai lần** trong `menu.blade.php` (khoảng dòng **235** và **762**) với logic gần như giống nhau — rủi ro **lệch khi sửa một nơi**.

### 3.1. So khớp menu ↔ controller (rủi ro)

- Nhiều controller dùng `abort_403(user()->permission('view_*') != 'all')` (vd. `TaskReportController`, `TimelogReportController`, `SalesReportController`, `FinanceReportController` …) trong khi menu dùng **`== 4`** (tương đương **all** trong DB). Thường **khớp** nếu user có đủ quyền; nếu sau này đổi điều kiện menu mà không đổi controller (hoặc ngược lại) dễ **403 có link**.
- **`LeaveReportController`:** cho phép `['all', 'added', 'owned', 'both']` — **nới** hơn một số báo cáo chỉ `all`.
- **`TimelogWeeklyApprovalController`:** có comment **TODO** “permission for both reports” — weekly / pending có thể cần tách quyền sau này.

---

## 4. Bảo mật & hiệu năng (khái quát)

- Mọi route báo cáo nằm sau **auth** + **multi-company-select** — phụ thuộc `AccountBaseController` / DataTable để lọc **`company_id`**; cần rà từng query khi harden (ngoài phạm vi audit từng dòng).
- Một số endpoint **POST chart** / export — nên giữ CSRF và quyền giống `index`.

---

## 5. Tests tự động

- **Smoke route:** `tests/Feature/ReportsRoutesSmokeTest.php` — `Route::has()` cho **11** tên route (thêm `audit-report.index`).
- **Chưa có** test HTTP/DataTable cho từng báo cáo (khoảng trống QA sâu hơn).

---

## 6. Gợi ý tiếp theo (tùy sản phẩm)

- [x] **Audit Report (2026-04-08):** migration `view_audit_report`, `AuditReportController`, `UserAuditReportDataTable`, view `reports/audit/index`, menu + `sidebar_user_perms`.
- [ ] Gộp **một** khối Reports trong `menu.blade.php` (partial Blade) để tránh duplicate.
- [ ] Hoàn thiện TODO quyền trong `TimelogWeeklyApprovalController`.
- [x] Smoke route cho nhóm report (gồm audit) — `ReportsRoutesSmokeTest`.

---

_Tài liệu liên quan: `SPECIFICATION/MENU_ROUTES_AND_CACHE.md`, `FUNC_LOGIC/AUDIT_PURCHASE_MODULE_VI.md` (Purchase reports)._
