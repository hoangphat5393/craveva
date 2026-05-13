# Super Admin — Package (gói): audit & flow

Tài liệu rà soát chức năng **Packages** (`/account/packages`): dữ liệu, UI, đồng bộ company, và sửa hiển thị module trùng / sai tick.

---

## 1. Luồng dữ liệu (tóm tắt)

| Thành phần                 | Vai trò                                                                                               |
| -------------------------- | ----------------------------------------------------------------------------------------------------- |
| Bảng **`packages`**        | Gói: `name`, `module_in_package` (JSON), limit storage/users, giá, `default` (yes/no/trial/lifetime). |
| **`companies.package_id`** | Company gán vào một gói.                                                                              |
| **`module_settings`**      | Đồng bộ theo gói (`CompanyObserver::updateModuleSettings`) — company được phép module nào.            |
| **`modules`**              | Danh sách module hệ thống (bỏ `settings`, `dashboards`, `restApi`, `disabledModuleArray()`).          |

**Lưu ý:** `module_in_package` khi lưu từ form là **`json_encode($request->module_in_package)`** với cấu trúc **`module_in_package[module_id] = module_name`** → JSON dạng object `{"53":"clients",...}` (value là tên module).

---

## 2. Code chính

| File                                                                            | Vai trò                                                               |
| ------------------------------------------------------------------------------- | --------------------------------------------------------------------- |
| `app/Http/Controllers/SuperAdmin/PackageController.php`                         | CRUD package, `modifyRequest()` encode JSON module.                   |
| `app/DataTables/SuperAdmin/PackageDataTable.php`                                | Cột module: tick/cross theo từng module trong `modules`.              |
| `resources/views/super-admin/packages/ajax/create.blade.php` / `edit.blade.php` | Checkbox `module_in_package[{{ $module->id }}]`.                      |
| `app/Observers/CompanyObserver.php`                                             | Khi đổi gói / sync package: cập nhật `module_settings` theo JSON gói. |
| `app/Console/Commands/PackageModulesCommand.php`                                | `packages:modules` — bật module trong gói + sync company.             |

---

## 3. Vấn đề đã xử lý (audit UI)

### 3.1. Cùng một module hiện hai lần / tick lệch

**Nguyên nhân có thể:**

- Bảng **`modules`** có **nhiều dòng trùng `module_name`** (dữ liệu lịch sử) → foreach hiển thị hai cột giống nhau.
- So khớp `in_array` không chuẩn hóa chữ hoa/thường so với `CompanyObserver::packageModuleNamesFromJson()` (lower-case).

**Đã chỉnh:**

- Query module cho form + DataTable: **`->orderBy('module_name')->get()->unique('module_name')->values()`** — mỗi `module_name` chỉ một dòng.
- Chuẩn hóa tên module trong package JSON: **`Package::normalizedModuleNamesFromPackageJson()`** — lấy **value** JSON, lower-case, unique; cột danh sách dùng **`in_array(..., true)`** so với tên module lower-case.

### 3.2. Triệu chứng “gói có module nhưng company không thấy menu”

Không thuộc riêng Package UI — xem **`FUNC_LOGIC/FLOW_Modules_Package_LanguagePack_CustomFields_VI.md`** và **`FUNC_BUG/DEVTOOLS_NO_COMPANY_SETTINGS.md`** (mẫu: `module_settings` thiếu dòng, nwidart tắt module).

---

## 4. Quyền Super Admin

- `PackageController`: `view_packages`, `add_packages`, `edit_packages`, `delete_packages` (permission `all` cho từng action).
- Package **default** / **trial** thường **không được xóa** (xem `destroy()`).

---

## 5. Kiểm tra nhanh sau khi sửa gói

1. `packages.module_in_package` có đủ module mong muốn (JSON values = tên module chữ thường sau khi lưu từ form).
2. Company dùng gói đó: `module_settings` khớp (chạy `php artisan packages:modules activate --module=...` nếu cần đồng bộ hàng loạt).
3. Reload trang Packages: mỗi module chỉ **một** dòng tick/cross; không còn trùng nhãn do duplicate `modules`.

---

## 6. Test

- `tests/Unit/PackageNormalizedModuleNamesTest.php` — parse JSON `module_in_package`.

---

## 7. Điều kiện gói hiển thị ở Frontend Pricing

Nguồn dữ liệu FE pricing lấy trực tiếp từ BE (`FrontendController@pricing`, `FrontendController@pricingPlan`) và bảng `packages`; FE không có bảng giá riêng.

### 7.1 Điều kiện lọc gói để render ở FE

Gói xuất hiện trên `/pricing` khi thỏa:

1. `is_private = 0`
2. Và thỏa **một trong ba nhánh** (tạm thời FE pricing khóa giá theo USD cho gói trả phí):
    - `default = 'no'` và `currency_id = USD` (id từ `global_currencies.currency_code = USD`)
    - `default = 'lifetime'` và `currency_id = USD`
    - `is_free = 1` — **không** lọc theo `currency_id` (gói Free/Default thường gắn currency mặc định hệ thống như SGD; nếu bắt trùng USD thì sẽ biến mất trên FE)

Sau đó FE chia tab:

- **Monthly tab**: `monthly_status = 1` hoặc gói lifetime
- **Annual tab**: `annual_status = 1` hoặc gói lifetime

### 7.2 Giải thích nhầm lẫn “không tick private mà vẫn hiện FE”

Checkbox **Make Private** có nghĩa:

- tick (`is_private = 1`) => ẩn khỏi FE pricing
- không tick (`is_private = 0`) => gói public, có thể hiện ở FE nếu thỏa các điều kiện còn lại

### 7.3 Case đã verify: Professional / Free

- Gói trả phí (ví dụ Professional) chỉ khớp nhánh USD như trên.
- Gói Free / Default (`is_free = 1`, `is_private = 0`) hiện trên FE dù `currency_id` của bản ghi không phải USD.

### 7.4 Gói Trial (`default = 'trial'`) có lên `/pricing` không?

Query FE **không** có nhánh `default = 'trial'`. Gói trial chỉ lên bảng giá công khai nếu thỏa một trong các nhánh mục 7.1 (ví dụ: đồng thời `is_free = 1` và `is_private = 0`, hoặc là gói paid USD với `default = 'no'`). Thông thường **trial là giai đoạn sau đăng ký**, không cần là một cột so sánh trên pricing.

---

## 8. Vai trò Default vs Trial (tóm tắt vận hành)

| Trường / khái niệm | **Default** (`default = 'yes'`) | **Trial** (`default = 'trial'`) |
| ------------------ | ------------------------------- | -------------------------------- |
| Vai trò chính | Gói **neo hệ thống**: company rớt về đây khi hết trial (cron `trial-expire`), khi cần gán fallback, khi xóa gói paid có company đang dùng. | Gói **dùng thử có thời hạn**; cấu hình thời gian ở Trial package settings. |
| Xóa được | **Không** (controller chặn). | Thường giữ 1 bản ghi; không dùng thay cho Default. |
| Trên FE `/pricing` | Chỉ hiện nếu thỏa mục 7.1 (thường là cùng lúc `is_free = 1` **hoặc** đang cấu hình như gói paid USD `default = 'no'`). Tên hiển thị là `name` trong DB — không bắt buộc phải chữ “Default” trên marketing. | Thường **không** là cột pricing; nếu cần hiện phải cố ý làm khớp nhánh 7.1 (hiếm). |

**Khác nhau một câu:** Trial = giai đoạn có hạn; Default = **điểm đến** sau khi hết trial (và các luồng fallback).

---

## 9. Chỉ muốn **2 cột trên FE: Free + Professional** — nên thao tác thế nào?

Nguyên tắc: FE lấy **mọi** gói public thỏa mục 7.1. Để chỉ còn đúng hai cột, cần **một gói free** + **một gói Professional (USD)** public, còn lại **không** thỏa điều kiện hiển thị hoặc gắn **private**.

1. **Professional (bắt buộc cho nhánh USD)**  
   - `default = 'no'`  
   - `is_free = 0`  
   - `currency_id` = USD (`global_currencies.currency_code = USD`)  
   - Bật ít nhất **Monthly** hoặc **Annual** tuỳ tab muốn show  
   - `is_private = 0`  
   - `sort` đặt thứ tự cột (ví dụ sau Free).

2. **Free (marketing)**  
   - `default = 'no'` (nên dùng gói “Free” marketing riêng, không trộn vai trò với `default = 'yes'` nếu muốn tránh hai cột free)  
   - `is_free = 1`, `monthly_price` / `annual_price` = 0  
   - `is_private = 0`  
   - Module trong gói = đúng tier free bạn muốn bán.

3. **Gói Default hệ thống** (`default = 'yes'`) — **không xóa**  
   - Để **không** hiện thêm một cột “Default” trên pricing trong khi vẫn giữ fallback:  
     - **Cách A (khuyên dùng):** chỉ giữ **một** gói public có `is_free = 1` (gói Free marketing ở mục 2). Với bản ghi `default = 'yes'`: **bỏ** `is_free` (set `is_free = 0`) *hoặc* đặt `is_private = 1` **chỉ khi** bạn đã xác nhận toàn hệ thống không cần hiện gói này trên pricing (signup vẫn gán `package_id` được).  
     - **Cách B:** đồng bộ nội dung gói Default với gói Free và **không** tạo gói Free thứ hai — tránh hai cột “Default” và “Free” trùng ý nghĩa.  
   - Không xóa bản ghi `default = 'yes'`.

4. **Gói Trial** (`default = 'trial'`)  
   - Giữ **Active** + số ngày nếu vẫn dùng thử.  
   - Không cần xuất hiện trên `/pricing` nếu trial chỉ là trạng thái sau đăng ký.  
   - Tránh bật `is_free` thửa cho trial nếu không muốn thêm cột trên pricing.

5. **Các gói khác** (Starter, Test, v.v.)  
   - `is_private = 1` → ẩn khỏi FE; company có thể vẫn gán qua admin nếu nghiệp vụ cần.  
   - Hoặc gỡ khỏi nhánh hiển thị (ví dụ không phải USD paid).

6. **Kiểm tra**  
   - Mở `/pricing`: đúng 2 cột Free + Professional, thứ tự theo `sort`.  
   - `php artisan trial-expire` / luồng hết trial: company vẫn về đúng gói `default = 'yes'`.

---

_Cập nhật: audit Package Super Admin; mục 8–9 Default vs Trial và chỉ 2 cột FE._
