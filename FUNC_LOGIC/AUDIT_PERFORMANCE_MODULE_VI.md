# Audit module Performance (OKR / 1:1 / Check-in)

**Phạm vi:** `Modules/Performance/` — route, quyền, menu, dữ liệu, lịch, API, rủi ro và hướng cải thiện.  
**Ngày audit:** 2026-04-08

---

## 1. Tổng quan

| Hạng mục                     | Giá trị                                                                                                                                          |
| ---------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------ |
| **Tên nwidart**              | `Performance` / alias `performance` (`module.json`)                                                                                              |
| **Hằng module (settings)**   | `PerformanceSetting::MODULE_NAME = 'performance'`                                                                                                |
| **Service providers**        | `PerformanceServiceProvider`, `EventServiceProvider`                                                                                             |
| **Migrations**               | `Modules/Performance/Database/Migrations/` (bảng settings, goal types, meetings, permissions, …)                                                 |
| **Artisan**                  | `performance:send-check-in-reminder`, `performance:set-objective-status`, `performance:activate-module` (tên lệnh cần xác nhận trong `Console/`) |
| **Tests tự động trong repo** | **Không** có file test nào trong `tests/` tham chiếu `Performance` / `performance-` (khoảng trống QA)                                            |

---

## 2. Route web (`Routes/web.php`)

- **Prefix:** `account` + middleware `auth` (không thấy `Route::has` bảo vệ ở định nghĩa route; phụ thuộc controller).
- **Nhóm chức năng:**
    - Objectives, Key results, OKR scoring, Check-ins
    - Performance dashboard (chart AJAX)
    - Performance settings, goal types, key results metrics
    - Meetings (+ calendar, reminders, cancel/complete)
    - Agenda, Action (1:1)
- **Tên route:** resource names như `objectives`, `performance-dashboard`, `meetings`, … (dùng trong menu).

**Ghi chú:** Một số route tên resource dùng `action` / `agenda` (singular) — cần đồng bộ với bất kỳ tài liệu/menu plugin nào.

---

## 3. API (`Routes/api.php`)

- `GET api/v1/performance` → trả về `$request->user()` (placeholder / debug).
- **Rủi ro:** Endpoint gần như không mang nghiệp vụ Performance; nếu deploy public cần xem lại (information disclosure tối thiểu — chỉ user đã auth Sanctum).

---

## 4. Phân quyền

### 4.1. Permission đã thấy trong code

| Tên                          | Vai trò gợi ý                                                           |
| ---------------------------- | ----------------------------------------------------------------------- |
| `view_performance_module`    | Xem module (menu HR dùng `== 'all'` — xem §5)                           |
| `manage_performance_setting` | Cài đặt Performance, goal type, key results metrics (một số controller) |

### 4.2. Migration

- `2024_09_20_071405_add_performance_permissions.php` — `manage_performance_setting`
- `2025_02_21_035730_view_performance_permissions.php` — `view_performance_module`

**Cảnh báo kỹ thuật (đọc dễ / an toàn khi maintain):** Nhiều chỗ dùng:

```php
Module::where('module_name', operator: 'performance')
```

Trong Eloquent, chữ ký là `where($column, $operator = null, $value = null, …)`. Dùng named argument `operator: 'performance'` **không** giống cách viết thông dụng `where('module_name', 'performance')` (một số phiên bản/framework có thể vẫn sinh điều kiện đúng nhờ `prepareValueAndOperator`, nhưng dễ gây hiểu nhầm). **Khuyến nghị:** chuẩn hóa thành `where('module_name', 'performance')` (hoặc `'=' , 'performance'`) trong:

- hai migration permission trên
- `Listeners/CompanyCreatedListener.php` (khoảng dòng 77)

### 4.3. Controller vs menu

- **Menu People (HR)** (`resources/views/sections/menu.blade.php`): hiển thị Performance khi `view_performance_module == 'all'` **và** module bật **và** `performance` ∈ `user_modules()`.
- **Hầu hết controller** (Objective, Dashboard, Check-in, Key results, OKR scoring, …): middleware chỉ kiểm tra `in_array(PerformanceSetting::MODULE_NAME, $this->user->modules)` — **không** kiểm tra `view_performance_module`.
- **Hệ quả:** User có module trong gói nhưng bị thu hồi quyền “view” (≠ `all`) vẫn **có thể truy cập URL trực tiếp** nếu biết route. Cân nhắc align với `user()->permission('view_performance_module')` hoặc policy.

- **Performance settings** (`PerformanceSettingController`, `GoalTypeController`, `KeyResultsMetricsController`): có nhánh `manage_performance_setting` — tương đối chặt hơn.

- **Meeting / Agenda / Action:** dùng `checkViewAccess` / `checkManageAccess` — cần đảm bảo luôn gắn `company_id` / participant đúng (không rà từng dòng trong audit này).

---

## 5. Menu & sidebar

1. **Menu chính (People):** Dashboard Performance, Objectives, OKR scoring, Meetings — điều kiện như §4.3.
2. **`performance::sections.sidebar`:** Trong `menu.blade.php` hiện bọc trong `@if (false)` — **không render**; toàn bộ rely vào block People ở trên. Sidebar module vẫn hữu ích nếu bật lại cho layout plugin / theme khác.
3. **Settings:** `performance::sections.setting-sidebar` — gắn `manage_performance_setting`.

**Khuyến nghị:** Thêm `Route::has('performance-dashboard.index')` (và route chính khác) trước `route()` trong menu People giống pattern Payroll — tránh `RouteNotFoundException` khi module tắt nhưng cache menu/module lệch.

---

## 6. Dữ liệu & tenant

- Entity chính (`Objective`, `CheckIn`, …) dùng `HasCompany` / `BaseModel` — **cần** xác nhận global scope company trên mọi query (đặc biệt chỗ `Role::all()`, `Team::all()` trong accessor `PerformanceSetting` — có nguy cơ **lẫn role cross-company** nếu không lọc `company_id`).

---

## 7. Lịch (scheduler)

`PerformanceServiceProvider::registerCommandSchedules()`:

- `performance:send-check-in-reminder` — `dailyAt('00:05')`
- `performance:set-objective-status` — `dailyAt('00:05')`

**Lưu ý:** Chạy mỗi ngày cùng giờ; multi-tenant cần đảm bảo command xử lý theo từng company / timezone (xem implementation trong `Console/`).

---

## 8. Sự kiện & observer

`EventServiceProvider`:

- `NewCompanyCreatedEvent` → `CompanyCreatedListener` (seed settings, goal types, metrics, meeting defaults, `view_performance_module` cho admin company)
- Observers: `Objective`, `CheckIn`, `KeyResults`

**Gợi ý:** Soát `CompanyCreatedListener` có cần đồng bộ **`manage_performance_setting`** cho admin company mới (migration cũ chỉ chạy một lần cho company hiện có).

---

## 9. Chất lượng code / UX nhỏ

| Vấn đề             | Vị trí                                                                                                               |
| ------------------ | -------------------------------------------------------------------------------------------------------------------- |
| Typo tên view      | `edit-key-results-**metrcis**.blade.php` + `KeyResultsMetricsController` return view `...metrcis`                    |
| Typo request class | `CreteKeyResultsRequest` (thiếu “a”)                                                                                 |
| Typo accessor      | `getCreateMeetingRoles**Namew**Attribute` trong `PerformanceSetting` (có thể đổi tên sẽ break API blade — cần alias) |
| HTML heading       | Một số modal `<h5>…</h4>` trong views meetings/agenda                                                                |

---

## 10. Khuyến nghị theo thứ tự ưu tiên

1. **Chuẩn hóa** `Module::where('module_name', …)` — bỏ named argument `operator:` gây nhầm; dùng `where('module_name', 'performance')`.
2. **Thống nhất** kiểm tra `view_performance_module` ở middleware chung module hoặc `AccountBaseController` branch Performance.
3. **Menu:** `Route::has()` cho các route Performance; optional bật lại sidebar nếu product muốn menu riêng.
4. **Rà soát** `PerformanceSetting` accessors dùng `Role::all()` — filter theo `company_id`.
5. **Bổ sung test:** smoke route + policy + một flow Objective/Meeting tối thiểu (Pest).
6. **API v1/performance:** hoặc implement nghiệp vụ thật hoặc gỡ / bảo vệ feature flag.

---

## 11. Tài liệu liên quan trong repo

- `SPECIFICATION/MENU_ROUTES_AND_CACHE.md` — pattern `Route::has` + module.
- `FUNC_LOGIC/FLOW_Modules_Package_LanguagePack_CustomFields_VI.md` — module & LanguagePack (chuỗi `performance::`).

---

_Khi sửa từng mục, có thể tick hoặc ghi chú commit bên dưới._

- [ ] Chuẩn hóa `where('module_name', …)`
- [ ] Align `view_performance_module` với controller
- [ ] `Route::has` trên menu
- [ ] Sửa typo view/request/accessor
- [ ] Test tự động cơ bản
- [ ] Rà Role::all() theo company
