# Bug: Pricing có trên Staging, thiếu trên Local dù package đã chọn module

**Triệu chứng:** Trong company panel (Super Admin) đã chọn package có module **Pricing**, và trên **staging** menu **Pricing** hiện trong sidebar; trên **local** menu Pricing không hiện (cùng user/company, cùng package).

---

## 1. Nguyên nhân chính

### 1.1. Sửa package không tự đồng bộ xuống company

Khi bạn **chỉ sửa package** (thêm/xóa module trong form **Packages** – Super Admin), code **chỉ cập nhật** bảng `packages` (cột `module_in_package`). **Không** có bước nào gọi `CompanyObserver::updateModuleSettings($company)` cho các company đang dùng package đó.

- **CompanyObserver::updateModuleSettings()** chỉ chạy khi:
    - Company **đổi gói** (thay đổi `package_id`), hoặc
    - Chạy tay lệnh `packages:modules activate` / `activate-all` (lệnh gọi `updateModuleSettings` cho từng company của package).

Hệ quả:

- **Staging:** Có thể đã từng đổi package của company (→ sync chạy) hoặc đã chạy lệnh đồng bộ → `module_settings` có `pricing` = active → `user_modules()` chứa `pricing` → menu hiện.
- **Local:** Chỉ sửa package (thêm Pricing vào gói), không đổi gói company, không chạy lệnh → `module_settings` của company **không** được cập nhật → vẫn `is_allowed = 0` hoặc `status = 'deactive'` cho `pricing` → `user_modules()` không có `pricing` → menu không hiện.

### 1.2. Các nguyên nhân phụ (local khác staging)

| Nguyên nhân                             | Staging                 | Local                                                | Ảnh hưởng                                                                                |
| --------------------------------------- | ----------------------- | ---------------------------------------------------- | ---------------------------------------------------------------------------------------- |
| **Custom Module Pricing (nwidart) tắt** | Bật                     | Tắt                                                  | Route `pricing.*` không load → `Route::has('pricing.tiers.index')` = false → menu bị ẩn. |
| **Cache `user_modules_{id}` cũ**        | Đã xóa / mới            | Còn cache cũ không có `pricing`                      | Menu vẫn ẩn dù DB đã đúng.                                                               |
| **DB local chưa đồng bộ với staging**   | Package/company đã sync | Package có pricing nhưng `module_settings` chưa sync | Giống mục 1.1.                                                                           |

---

## 2. Flow tóm tắt (vì sao “chọn package” chưa đủ)

```
[Super Admin] Sửa Package (thêm Pricing vào module_in_package)
    → Chỉ cập nhật: packages.module_in_package
    → KHÔNG gọi updateModuleSettings() cho các company dùng package này

[Khi company đổi gói] Company.package_id thay đổi
    → CompanyObserver::saasSaving() → updateModuleSettings($company)
    → module_settings (is_allowed, status) được cập nhật theo module_in_package
    → clearCompanyUserCache($company)

user_modules()
    → Đọc module_settings (company_id, type, is_allowed=1, status=active)
    → Cache: user_modules_{user_id}

Menu Pricing
    → Hiện khi: !is_superadmin && in_array('pricing', user_modules()) && Route::has('pricing.tiers.index')
```

**Kết luận:** “Package đã chọn Pricing” chỉ đảm bảo `packages.module_in_package` có `pricing`. Để menu hiện, **company** phải có `module_settings` tương ứng (is_allowed=1, status=active). Điều này chỉ đạt được sau khi **đồng bộ** (đổi gói hoặc chạy lệnh). Trên local thiếu bước đồng bộ nên menu không hiện.

---

## 3. Giải pháp (thực hiện trên local)

### Bước 1: Đồng bộ module_settings theo package (bắt buộc)

Sau khi đã chọn package có Pricing trong company panel, chạy **trên local** để cập nhật `module_settings` cho mọi company dùng package đó và xóa cache user:

```bash
# Bật pricing cho mọi package và đồng bộ toàn bộ company
php artisan packages:modules activate --module=pricing
```

Nếu chỉ muốn đồng bộ một package (biết rõ package id của company):

```bash
php artisan packages:modules activate-all --package=<PACKAGE_ID>
```

### Bước 2: Bật Custom Module Pricing (nwidart) trên local

Nếu trên local toggle **Pricing** (Settings > Module Settings > Custom Modules) đang tắt, route sẽ không có → menu vẫn ẩn. Chọn một trong hai:

- **Cách A:** Vào **Settings > Module Settings > Custom Modules**, bật toggle **Pricing**.
- **Cách B:** Bật toàn bộ custom module (trong đó có Pricing):

```bash
php artisan packages:modules enable-custom
```

### Bước 3: Xóa cache và kiểm tra lại

```bash
php artisan cache:clear
```

Sau đó đăng xuất (hoặc ít nhất reload trang), đăng nhập lại và mở Dashboard. Menu **Pricing** sẽ hiện nếu:

- Package của company có `pricing` trong `module_in_package`,
- `module_settings` đã được đồng bộ (bước 1),
- Module Pricing (nwidart) đang bật (bước 2),
- Cache đã xóa (bước 3).

---

## 4. Kiểm tra nhanh (local)

- **Package có pricing không:**

    ```bash
    php artisan packages:modules list
    ```

    Xem package mà company đang dùng có `pricing` trong cột “Trong gói”.

- **Module Pricing (nwidart) có bật không:**  
  Mở **Settings > Module Settings > Custom Modules** → toggle **Pricing** phải ON.

- **Cache:** Sau khi sửa DB/lệnh, nên chạy `php artisan cache:clear` và reload/đăng nhập lại.

---

## 5. Tóm tắt

| Vấn đề                                                   | Nguyên nhân                                                               | Giải pháp                                                                                                   |
| -------------------------------------------------------- | ------------------------------------------------------------------------- | ----------------------------------------------------------------------------------------------------------- |
| Package đã chọn Pricing nhưng menu không hiện trên local | Sửa package không tự sync xuống `module_settings` của company             | Chạy `php artisan packages:modules activate --module=pricing` (hoặc `activate-all --package=ID`) trên local |
| Staging có, local không (cùng package)                   | Local chưa chạy bước đồng bộ và/hoặc Custom Module Pricing tắt / cache cũ | Đồng bộ (lệnh trên) + bật Pricing trong Custom Modules + `cache:clear`                                      |

File tham chiếu flow chi tiết: `FUNC_BUG/TIER_PRICING_MODULE_MISSING.md`.
