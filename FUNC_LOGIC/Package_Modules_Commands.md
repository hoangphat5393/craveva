# Lệnh Package & Module (Craveva)

Tài liệu lưu các lệnh Artisan và cách dùng cho quản lý module trong Package.

**Hai loại "bật module" khác nhau:**

| Loại | Lệnh / UI | Lưu ở đâu | Ảnh hưởng |
|------|-----------|-----------|-----------|
| **Module trong gói (Package)** | `activate-all` / `activate` | `packages.module_in_package`, `module_settings` | Quyền module theo gói/công ty (menu, tính năng theo subscription). |
| **Custom Modules (toggle trên UI)** | `enable-custom` hoặc bật từng cái trên trang **Settings > Module Settings > Custom Modules** | `storage/app/modules_statuses.json` (nwidart) | Bật/tắt module Laravel (Affiliate, Asset, Payroll, …); nếu tắt thì module không load. |

→ Chạy `activate-all` **không** đổi trạng thái toggle trên trang **Custom Modules**. Để tất cả toggle ON, dùng `enable-custom`.

---

## 1. Lệnh chính: `packages:modules`

**File:** `app/Console/Commands/PackageModulesCommand.php`

| Tham số / Option | Mô tả |
|------------------|--------|
| `action` (bắt buộc) | `list` \| `activate-all` \| `activate` \| `enable-custom` |
| `--package=` | ID package (tùy chọn; mặc định: tất cả package) |
| `--module=` | Tên module (bắt buộc khi `action=activate`) |

---

## 2. Các lệnh cụ thể

### 2.1. Xem danh sách module và trạng thái từng package

```bash
php artisan packages:modules list
```

- In danh sách toàn bộ module dùng cho Package (từ bảng `modules`, trừ settings/dashboards/restApi và module disabled).
- Với mỗi package: số module trong gói và **danh sách module thiếu** (nếu có).

---

### 2.2. Bật toàn bộ module

**Cho tất cả package:**

```bash
php artisan packages:modules activate-all
```

**Cho một package (theo ID):**

```bash
php artisan packages:modules activate-all --package=1
```

- Cập nhật `packages.module_in_package` = JSON đầy đủ module.
- Đồng bộ `module_settings` (is_allowed, status) cho mọi company dùng package đó.

---

### 2.3. Bật một module cụ thể

**Cho mọi package:**

```bash
php artisan packages:modules activate --module=clients
```

**Cho một package:**

```bash
php artisan packages:modules activate --module=products --package=9
```

- Thêm module vào `module_in_package` nếu chưa có.
- Đồng bộ `module_settings` cho các company thuộc package.

---

### 2.4. Bật toàn bộ Custom Modules (toggle trên trang Module Settings)

Trang **Settings > Module Settings > Custom Modules** hiển thị trạng thái bật/tắt từ **nwidart** (`storage/app/modules_statuses.json`). Lệnh `activate-all` không ghi vào đây.

Để **tất cả toggle chuyển sang ON** (bật toàn bộ module Affiliate, Asset, Payroll, …):

```bash
php artisan packages:modules enable-custom
```

- Ghi trạng thái enabled cho từng module vào `modules_statuses.json`.
- Xóa cache `craveva_plugins`, `user_modules`.
- Reload trang **Module Settings** để thấy toggle ON.

---

## 3. Tên module (lowercase, không dấu)

Ví dụ: `clients`, `tasks`, `timelogs`, `knowledgebase`, `bankaccount`, `invoices`, `estimates`, `products`, `orders`, `reports`, `pricing`, …

Danh sách đầy đủ lấy từ bảng `modules` (trừ settings, dashboards, restApi và module bị disabled).

---

## 4. Lưu ý

- Danh sách module “đầy đủ” trùng với form sửa Package trong Super Admin.
- Sau khi chạy activate, cache user (`user_modules_{id}`) được xóa qua `CompanyObserver::updateModuleSettings`.
