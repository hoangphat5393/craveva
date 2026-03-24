# Ghi chú: Gỡ `doctrine/dbal` (đã thực hiện)

_File này lưu trạng thái sau khi đã gỡ package `doctrine/dbal` khỏi Craveva, cùng các rủi ro migration còn lại._

**Tham chiếu:** `docs/LARAVEL_11_UPGRADE_GUIDE.md` §2.1.1

---

## 1. Phiên bản trong project

| Nguồn                           | Giá trị                                                      |
| ------------------------------- | ------------------------------------------------------------ |
| `composer.json`                 | `doctrine/dbal` đã được remove                               |
| `composer.lock`                 | không còn package `doctrine/dbal`                            |

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

Package **`doctrine/dbal`** đã được gỡ. Các migration còn `->change()`/`->renameColumn()` cần tiếp tục được theo dõi theo phase để giảm rủi ro tương thích theo driver DB.

**Đã refactor (không còn trong bảng trên):** `Modules/ServerManager/Console/DatabaseAuditCommand.php` — dùng `getTableListing()` — xem `LARAVEL_11_UPGRADE_GUIDE.md` §7.5 mục 14.

> **Ghi chú:** `app/`, `tests/` — không có `getDoctrineSchemaManager()` (đã rà). Lệnh `servermanager:db-audit` **không còn** gọi Doctrine.

### 3.2 Migration dùng `renameColumn` / `change()` (rủi ro sau khi gỡ)

Đã audit lại ngày **2026-03-24**:

- `->change(...)`: **43** file
- `->renameColumn(...)`: **13** file
- Unique migration file còn phụ thuộc: **52**

Danh sách đầy đủ nằm ở `docs/DOCTRINE_DBAL_MIGRATION_AUDIT.md`.

Sau khi gỡ DBAL cần chạy thử toàn bộ migration trên engine giống production để bắt lỗi còn lại.

### 3.3 Không phải lỗi runtime app thường

- **`config/ide-helper.php`**: comment/cấu hình map kiểu Doctrine cho **barryvdh/laravel-ide-helper** — chỉ liên quan khi generate helper.
- **Request web / queue:** không phụ thuộc DBAL theo các pattern trên.

---

## 4. Checklist sau khi đã gỡ

1. [x] ~~Thay `getDoctrineSchemaManager()` trong migration~~ — đã dùng `getForeignKeys` / `getIndexes` / `hasIndex` (§7.5 mục 19); migration còn `renameColumn`/`change()` vẫn cần `doctrine/dbal`.
2. [x] ~~Refactor `DatabaseAuditCommand`~~ — đã dùng `getSchemaBuilder()->getTableListing()`.
3. [x] Rà migration có `->change()` / `->renameColumn()` — đã có audit file và số lượng (`docs/DOCTRINE_DBAL_MIGRATION_AUDIT.md`).
4. [ ] Refactor theo nhóm migration để giảm phụ thuộc DBAL.
5. [ ] CI: chạy migrate trên DB giống production (không DB reset ở môi trường đang dùng).
6. [x] `composer remove doctrine/dbal` đã chạy thành công.
7. [ ] Tiếp tục refactor các migration còn `->change()` / `->renameColumn()` để giảm rủi ro chạy migration trên môi trường mới.

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
