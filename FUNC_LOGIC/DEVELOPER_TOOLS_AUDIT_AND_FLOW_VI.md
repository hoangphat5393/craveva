# Developer Tools — audit, flow User / Company, AI SQL

Tài liệu rà soát module **DeveloperTools** (gateway DB, view, quyền UI), và ghi chú lỗi **CRAVEVA AI semantic layer** khi chạy SQL có `CREATE`.

**Tham chiếu lỗi “gói có module nhưng không hiện Settings”:** [../FUNC_BUG/DEVTOOLS_NO_COMPANY_SETTINGS.md](../FUNC_BUG/DEVTOOLS_NO_COMPANY_SETTINGS.md).

---

## 1. Vai trò module

| Thành phần     | Mô tả                                                                                                                                           |
| -------------- | ----------------------------------------------------------------------------------------------------------------------------------------------- |
| **UI**         | `/account/developertools` — tạo credential MySQL, xem log; **CodeMap** quét file (`FileScanner`).                                               |
| **Gateway DB** | Mỗi company một schema `api_gateway_{company_id}` + **VIEW** trỏ bảng app, lọc `company_id` (hoặc join rule trong `config/developertools.php`). |
| **User MySQL** | `api_{company_id}_{random}` — `GRANT` chỉ trên schema gateway (không grant toàn DB production trừ khi cấu hình khác).                           |

**Lưu ý:** Lớp **CRAVEVA AI / semantic layer** (nếu kết nối tới DB gateway hoặc proxy SQL) thường **chỉ cho SELECT** và **chặn DDL** (`CREATE`, `DROP`, `ALTER`, …). Lỗi `Dangerous SQL keyword detected: CREATE` là **đúng thiết kế** — không chạy `CREATE TABLE` qua AI; dùng **migration Laravel** hoặc DBA trực tiếp (không qua semantic layer).

---

## 2. Sơ đồ quan hệ User ↔ Company ↔ Module ↔ Gateway

```text
+------------------+          +------------------+
| Super Admin      |          | Package          |
| (không vào DT    |          | module_in_package|
|  tenant UI)      |          | (JSON modules)   |
+--------+---------+          +--------+---------+
         |                             |
         | cấu hình gói                | developertools trong JSON
         v                             v
+--------+---------+          +-------+--------+
| Company          |<---------+ companies      |
| package_id       |          +----------------+
+--------+---------+
         |
         | company_id
         +------------------+---------------------------+
         |                  |                           |
         v                  v                           v
+--------+---------+ +-----+------+            +-------+--------+
| module_settings  | | User       |            | api_gateway_N  |
| module_name=     | | admin /    |            | (MySQL schema) |
| developertools   | | manage_*   |            | VIEWs filtered |
| is_allowed,      | +-----+------+            | by company_id  |
| status, type     |       |                 +-------+--------+
+--------+---------+       |                         ^
         ^                 | đăng nhập               | credential
         |                 v                         |
         |          user_can_access_                 |
         |          developertools_module()        |
         |                 |                         |
         +-----------------+----> Menu Developer     |
                            Tools + tạo credential -+
```

**Điều kiện vào UI (tóm tắt):**

1. User **không** phải superadmin.
2. Role **admin** hoặc quyền **`manage_module_setting` = all** (logic chi tiết trong `user_can_access_developertools_module()`).
3. `company.package` hợp lệ; JSON gói có **`developertools`** (chuẩn hóa qua `CompanyObserver::packageModuleNamesFromJson`).
4. `module_settings`: `module_name = developertools`, `type = admin`, `status = active`, `is_allowed = 1`.
5. Module **nwidart** `DeveloperTools` **enabled** (`modules_statuses.json` / `Module::find('DeveloperTools')->isEnabled()`).

---

## 3. File / cấu hình chính

| File                                                                   | Vai trò                                                                                                                               |
| ---------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------- |
| `app/Helper/start.php`                                                 | `user_can_access_developertools_module()`                                                                                             |
| `Modules/DeveloperTools/Http/Controllers/DeveloperToolsController.php` | Tạo DB, VIEW, user MySQL, credential, log                                                                                             |
| `Modules/DeveloperTools/Services/DbAccessPolicy.php`                   | Bảng được phép, deny, cột nhạy cảm, join view                                                                                         |
| `Modules/DeveloperTools/Config/config.php`                             | Merge config key `developertools` (`config('developertools.db_access')`) — `modules`, `deny_tables`, `sensitive_tables`, `join_views` |
| `Modules/DeveloperTools/Entities/DeveloperToolsCredential.php`         | Credential theo `company_id`                                                                                                          |
| `Modules/DeveloperTools/Entities/DbAccessLog.php`                      | Log truy cập / tạo credential                                                                                                         |

---

## 4. Rủi ro & ghi chú bảo mật

- **Gateway user** có thể được `GRANT ALL` trên schema `api_gateway_*` (xem comment trong controller) — staging/demo dễ; production nên siết read-only nếu policy yêu cầu.
- **Bảng không có `company_id`** và không có rule trong `join_views` → bị **skip** khi tạo view (cảnh báo trong `last_generation_warnings`).
- **AI / semantic SQL:** không dùng để chạy DDL; chỉ `SELECT` (hoặc policy tương đương).

---

## 5. Checklist vận hành (module không hiện / credential lỗi)

1. `php artisan migrate` (backfill `module_settings` nếu có migration liên quan).
2. `php artisan packages:modules activate --module=developertools` (đồng bộ gói → `module_settings`).
3. Nwidart: `php artisan packages:modules enable-custom` hoặc bật **DeveloperTools** trên **Module Settings → Custom Modules** nếu module bị tắt globally.
4. Đăng xuất / đăng nhập lại; xóa cache `user_modules_*` nếu cần.
5. Kiểm tra URL `.../account/developertools` — 403 đọc message; 404 kiểm tra route + module enabled.

---

## 6. Lệnh audit (local / server)

```bash
php artisan developertools:audit
```

In: trạng thái module nwidart, số bản ghi `module_settings` (developertools), credential theo company, gợi ý nếu thiếu.

---

_Cập nhật: flow + audit Developer Tools; semantic layer chặn CREATE là hành vi mong đợi._
