# Ghi chú: Gỡ `doctrine/dbal` (tham khảo — **chưa thực hiện**)

_File này lưu các rủi ro và vị trí code liên quan **trước khi** ai đó xóa package `doctrine/dbal` khỏi Craveva. Hiện tại **khuyến nghị: không gỡ** cho đến khi refactor xong._

**Tham chiếu:** `docs/LARAVEL_11_UPGRADE_GUIDE.md` §2.1.1

---

## 1. Phiên bản trong project

| Nguồn                           | Giá trị                                                      |
| ------------------------------- | ------------------------------------------------------------ |
| `composer.json`                 | `"doctrine/dbal": "^3.0"`                                    |
| `composer.lock` (thời điểm ghi) | **3.10.x** — kiểm tra lại bằng `composer show doctrine/dbal` |

---

## 2. `doctrine/dbal` dùng để làm gì ở đây?

- **DBAL** = lớp trừu tượng schema/SQL; Laravel **không** ship sẵn toàn bộ chức năng này trong core.
- Trong repo Craveva, DBAL chủ yếu xuất hiện qua:
    - **`Schema::getConnection()->getDoctrineSchemaManager()`** — introspection (FK, index, tên bảng, …).
    - Migration dùng **`$table->renameColumn(...)`**, **`$table->...->change()`** — Laravel khuyến nghị DBAL cho **`change()`**; `renameColumn` tùy driver/phiên bản.

**Không nhầm với:** `doctrine/inflector` (package khác, ví dụ trong fork Entrust).

---

## 3. Nếu gỡ package — chỗ nào **gãy ngay**?

### 3.1 Gọi trực tiếp `getDoctrineSchemaManager()` (Laravel 11 đã **bỏ** API này)

**Cập nhật:** Các migration từng liệt kê ở đây đã **được thay** bằng `Schema::getConnection()->getSchemaBuilder()->getForeignKeys()` / `getIndexes()` / `Schema::hasIndex()` — xem **`LARAVEL_11_UPGRADE_GUIDE.md` §7.5 mục 19**.

Package **`doctrine/dbal`** trong `composer.json` **vẫn nên giữ** cho các migration còn dùng **`->change()`**, **`renameColumn`**, v.v. (xem §3.2).

**Đã refactor (không còn trong bảng trên):** `Modules/ServerManager/Console/DatabaseAuditCommand.php` — dùng `getTableListing()` — xem `LARAVEL_11_UPGRADE_GUIDE.md` §7.5 mục 14.

> **Ghi chú:** `app/`, `tests/` — không có `getDoctrineSchemaManager()` (đã rà). Lệnh `servermanager:db-audit` **không còn** gọi Doctrine.

### 3.2 Migration dùng `renameColumn` / `change()` (rủi ro sau khi gỡ)

Nhiều file (ví dụ `database/migrations/2024_02_02_114946_lead-files_changes_for_deals.php` dùng `renameColumn`). Sau khi gỡ DBAL cần **chạy thử toàn bộ** `migrate` / `migrate:fresh` trên **đúng** engine DB production (MySQL/MariaDB/SQLite…) để bắt lỗi còn lại.

### 3.3 Không phải lỗi runtime app thường

- **`config/ide-helper.php`**: comment/cấu hình map kiểu Doctrine cho **barryvdh/laravel-ide-helper** — chỉ liên quan khi generate helper.
- **Request web / queue:** không phụ thuộc DBAL theo các pattern trên.

---

## 4. Checklist nếu sau này quyết định gỡ

1. [x] ~~Thay `getDoctrineSchemaManager()` trong migration~~ — đã dùng `getForeignKeys` / `getIndexes` / `hasIndex` (§7.5 mục 19); migration còn `renameColumn`/`change()` vẫn cần `doctrine/dbal`.
2. [x] ~~Refactor `DatabaseAuditCommand`~~ — đã dùng `getSchemaBuilder()->getTableListing()`.
3. [ ] Rà migration có `->change()` — đảm bảo hành vi sau khi gỡ (theo tài liệu Laravel cho từng driver).
4. [ ] CI: chạy migrate trên DB giống production.
5. [ ] Chỉ sau đó mới `composer remove doctrine/dbal` và kiểm tra `composer why-not` / test toàn bộ.

---

## 5. Ví dụ minh họa (trích repo)

**Introspection FK trước khi đổi tên cột** — `Modules/Pricing/.../2026_01_30_160749_update_company_pricing_to_use_clients.php`:

```php
$sm = Schema::getConnection()->getDoctrineSchemaManager();
$indexes = $sm->listTableForeignKeys('company_customer_pricing');
// ... dropForeign ...
$table->renameColumn('customer_company_id', 'client_id');
```

**Liệt kê bảng (Artisan)** — trước đây dùng Doctrine; hiện dùng:

```php
$tables = Schema::getConnection()->getSchemaBuilder()->getTableListing();
```

---

_Cập nhật: ghi nhận trước các bước tiếp theo trong quy trình upgrade Laravel 11 — xem `docs/LARAVEL_11_UPGRADE_GUIDE.md`._
