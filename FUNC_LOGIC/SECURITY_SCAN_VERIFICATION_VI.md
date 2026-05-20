# Xác minh báo cáo Bug & Security (Hub) — trừ mục APP_DEBUG

**Ngày:** 2026-05-16  
**Phạm vi:** Đối chiếu scan AI với codebase `craveva-staging`; các sửa đã merge trong session này.

---

## Bảng tóm tắt

| #   | Mức báo cáo                          | Khẳng định                                                                                           | Đã sửa trong repo                        |
| --- | ------------------------------------ | ---------------------------------------------------------------------------------------------------- | ---------------------------------------- |
| 1   | CRITICAL — `APP_DEBUG`               | _(bỏ qua theo yêu cầu)_                                                                              | Không                                    |
| 2   | CRITICAL — SQL `ProjectObserver`     | **Đúng**                                                                                             | Có — binding + validation `project_code` |
| 3   | HIGH — `$fillable = []`              | **Đúng cấu hình rủi ro** (kèm `guarded = ['id']`); exploit thực tế thấp vì controller gán từng field | Có — `$fillable` rõ trên 7 model         |
| 4   | HIGH — API Knowledge Base không auth | **Sai** — KB là web `/account/knowledgebase/*` + `auth`                                              | Không (false positive)                   |
| 4b  | — `GET purchased-module`             | **Đúng một phần** — public, lộ module + version                                                      | Chưa (xem khuyến nghị)                   |
| 5   | HIGH — `whereRaw` nối chuỗi          | **Một phần** — nhiều chỗ an toàn (`?`); HR/Birthday dùng `m-d` server-side                           | Chưa (ưu tiên thấp)                      |

---

## Mục 2 — Project / Work Management

| Thành phần              | Chi tiết                                                                                                                                               |
| ----------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------ |
| **File**                | `app/Observers/ProjectObserver.php` (khi `project_short_code` đổi)                                                                                     |
| **Module**              | Work Management — Projects, Tasks                                                                                                                      |
| **Lỗi**                 | Nối `project_short_code` vào `DB::statement()` → SQL injection nếu mã dự án chứa `'`, `;`, v.v.                                                        |
| **Điều kiện khai thác** | User có quyền tạo/sửa project và gửi `project_code` độc hại                                                                                            |
| **Sửa đã làm**          | `DB::statement(..., [$project->project_short_code, $project->id])`; rule `regex:/^[A-Za-z0-9_\-]+$/` + `max:50` trong `StoreProject` / `UpdateProject` |
| **Test**                | `tests/Unit/ProjectShortCodeValidationTest.php`                                                                                                        |

---

## Mục 3 — Model file / settings (mass assignment)

| Model               | Module / màn                  | Controller chính                                     | Ghi chú                               |
| ------------------- | ----------------------------- | ---------------------------------------------------- | ------------------------------------- |
| `EmployeeDocument`  | HR — Employee docs            | `EmployeeDocController`                              | Gán field tay → rủi ro thấp trước sửa |
| `ClientDocument`    | Clients                       | `ClientDocController`                                |                                       |
| `DealFile`          | Lead/Deal                     | `LeadFileController`                                 |                                       |
| `InvoiceFiles`      | Invoices                      | `InvoiceFilesController`                             |                                       |
| `ProductFiles`      | Products (+ Purchase product) | `ProductFileController`, `PurchaseProductController` |                                       |
| `LogTimeFor`        | Timelogs settings             | `TimeLogSettingController`                           |                                       |
| `KnowledgeBaseFile` | Knowledge Base (web auth)     | `KnowledgeBaseFileController`                        | Cùng pattern, không nằm báo cáo gốc   |

**Sửa:** Khai báo `$fillable` chỉ các cột nghiệp vụ; giữ `$guarded = ['id']`.

**Khuyến nghị thêm:** Rà toàn app các model `fillable = []` + `guarded = ['id']` (grep) — không chỉ 6 model scan.

---

## Mục 4 — API / Knowledge Base

| Khẳng định báo cáo                 | Thực tế trong repo                                                                               |
| ---------------------------------- | ------------------------------------------------------------------------------------------------ |
| `/api/knowledge-base/*` không auth | **Không tồn tại** trong `routes/api.php`                                                         |
| Knowledge Base                     | `routes/web.php` → prefix `account`, middleware `auth`, `multi-company-select`, `email_verified` |
| Controller                         | `KnowledgeBaseController` — middleware module `knowledgebase` + permission `view_knowledgebase`  |

### `GET /api/.../purchased-module`

| Thành phần      | Chi tiết                                                                                                                                                   |
| --------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **File**        | `routes/api.php` → `HomeController@installedModule`                                                                                                        |
| **Rủi ro**      | Information disclosure: danh sách module bật, version app                                                                                                  |
| **Khuyến nghị** | (a) `auth:sanctum` nếu client đã có token; (b) hoặc API key / throttle; (c) hoặc chỉ bật khi `RestAPI` module yêu cầu — kiểm tra app mobile trước khi khóa |

---

## Mục 5 — `whereRaw` nối chuỗi

| Vị trí                                             | Input                                                       | Rủi ro            |
| -------------------------------------------------- | ----------------------------------------------------------- | ----------------- |
| `app/Traits/HRDashboard.php`                       | `$fromMonthDay` / `$tillMonthDay` từ `format('m-d')` server | Thấp              |
| `app/Traits/EmployeeDashboard.php`                 | `$currentDay` server                                        | Thấp              |
| `app/Console/Commands/BirthdayReminderCommand.php` | `now()->format('m-d')`                                      | Thấp              |
| Nhiều controller                                   | `whereRaw('md5(id) = ?', $id)`                              | Thấp (có binding) |

**Khuyến nghị:** Refactor dần sang `whereRaw('... BETWEEN ? AND ?', [$from, $till])` cho nhất quán; ưu tiên chỗ có biến từ `$request`.

---

## Checklist deploy

1. Deploy các file PHP đã sửa (Observer, Requests, Models).
2. `php artisan config:cache` trên server (sau khi chỉnh `.env` riêng — không thuộc scope này).
3. Chạy: `php artisan test --compact tests/Unit/ProjectShortCodeValidationTest.php`
4. UAT: đổi **Project short code** hợp lệ (`ABC-01`) → task codes cập nhật; thử mã có dấu `'` → validation 422.
