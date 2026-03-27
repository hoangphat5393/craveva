# Audit: `easyAjax`, AJAX liên quan & axios (`apiHttp`)

**Repo:** `craveva-staging`  
**Cập nhật:** 2026-03-27 (quét tĩnh bằng pattern trong source, không gồm `node_modules` / `vendor` / `storage/framework/views` đã compile).

## 1. Tổng quan kiến trúc

| Thành phần                           | Vai trò                                                                                                                    |
| ------------------------------------ | -------------------------------------------------------------------------------------------------------------------------- |
| **`public/vendor/helper/helper.js`** | Định nghĩa **`$.easyAjax`** (jQuery), mặc định `dataType: 'json'`, xử lý `handleFail`, blockUI, v.v.                       |
| **`resources/js/http/apiClient.js`** | **Axios** instance + **`window.apiHttp`** (`get`, `post`, `postForm`, `postUrlEncoded`, `delete`, …), CSRF, normalize lỗi. |
| **`resources/js/main.js`**           | `require('./http/apiClient')` — load client trên layout đã auth.                                                           |
| **Blade `$.ajax` thuần**             | Một số view gọi **`$.ajax`** / **`jQuery.ajax`** trực tiếp (không qua `easyAjax`) — xem mục 4.                             |

**Lưu ý:** “API” REST (`routes/api.php`) **không** là luồng import chính; import dùng route **web** `/account/...` + JSON `Reply` (AJAX trong trình duyệt).

---

## 2. Phương pháp quét

- **`$.easyAjax` / `easyAjax(`** trong `*.blade.php` (core + `Modules/**`).
- **`window.apiHttp` / `apiHttp.`** trong `*.blade.php` (đã migrate một phần).
- **`$.ajax` / `jQuery.ajax`** trong `*.blade.php` (bổ sung).

Chạy lại sau khi sửa code (PowerShell từ root repo):

```powershell
Select-String -Path "resources\views\**\*.blade.php","Modules\**\*.blade.php" -Pattern '\$\.easyAjax' -Recurse
```

---

## 3. Core app — `resources/views/`

- **`$.easyAjax`:** Vẫn còn nhiều file (ước lượng **hàng trăm** blade) — tập trung ở `invoices/**`, `dashboard/**`, `auth/**`, `settings/**`, v.v.
  _Đã dọn sạch trong các wave core gần nhất: `tasks/**`, `projects/**`, `project-templates/**`, `leads/**`, `tickets/**`, `event-calendar/**`, `super-admin/**`._
- **`apiHttp`:** Đã dùng ở nhiều view **clients**, **products**, **orders**, **invoices**, **payments**, **warehouse**, **purchase** (một phần), **QRCode**, v.v. — xem `docs/axios-migration/client.md`, `product.md`, …

**File import dùng chung (quan trọng):**

- `resources/views/import/process-form.blade.php` — đã chuyển **`window.apiHttp`** (`get` poll + `postUrlEncoded` submit). Bản song song: `Modules/Recruit/Resources/views/import/process-form.blade.php`.
- Các `*_progress.blade.php` chỉ `@include('import.process-form', ...)`.

---

## 4. `$.ajax` trực tiếp (không qua `easyAjax`)

Có mặt rải rác trong `resources/views` và `Modules/**` (ví dụ hierarchy, timelog, server, biolinks, …). Nên gom vào cùng kế hoạch với axios hoặc wrapper `apiHttp`.

---

## 5. Modules (nwidart) — còn dùng `$.easyAjax` trong Blade

Các module **có `module.json`** trong repo: 25.  
Quét **`$\.easyAjax`** trong `Modules/<Name>/Resources/views/**/*.blade.php`:

| Module             | Ghi chú                                                                                                                                                                                                                                             |
| ------------------ | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Affiliate**      | Không còn `$.easyAjax` trong `Modules/Affiliate/Resources/views/**`                                                                                                                                                                                 |
| **Asset**          | Không còn `$.easyAjax` trong `Modules/Asset/Resources/views/**`                                                                                                                                                                                     |
| **Biometric**      | Không còn `$.easyAjax` trong `Modules/Biometric/Resources/views/**`                                                                                                                                                                                 |
| **Biolinks**       | Không còn `$.easyAjax` trong `Modules/Biolinks/Resources/views/**`                                                                                                                                                                                  |
| **CyberSecurity**  | Không còn `$.easyAjax` trong `Modules/CyberSecurity/Resources/views/**`                                                                                                                                                                             |
| **EInvoice**       | Không còn `$.easyAjax` trong `Modules/EInvoice/Resources/views/**`                                                                                                                                                                                  |
| **LanguagePack**   | Không còn `$.easyAjax` trong `Modules/LanguagePack/Resources/views/**`                                                                                                                                                                              |
| **Letter**         | Không còn `$.easyAjax` trong `Modules/Letter/Resources/views/**`                                                                                                                                                                                    |
| **Onboarding**     | Không còn `$.easyAjax` trong `Modules/Onboarding/Resources/views/**`                                                                                                                                                                                |
| **Payroll**        | Không còn `$.easyAjax` trong `Modules/Payroll/Resources/views/**` (đã hoàn tất wave 1-3: tất cả `payroll-setting/**`, `payroll/**`, `payroll-report/**`, `overtime-setting/**`, `overtime-request/**`, `employee-salary/**`, `payroll-expenses/**`) |
| **Performance**    | Không còn `$.easyAjax` trong `Modules/Performance/Resources/views/**`                                                                                                                                                                               |
| **Policy**         | Không còn `$.easyAjax` trong `Modules/Policy/Resources/views/**`                                                                                                                                                                                    |
| **Pricing**        | Không còn `$.easyAjax` trong `Modules/Pricing/Resources/views/**`                                                                                                                                                                                   |
| **ProjectRoadmap** | Không còn `$.easyAjax` trong `Modules/ProjectRoadmap/Resources/views/**`                                                                                                                                                                            |
| **Purchase**       | Không còn `$.easyAjax` trong `Resources/views/**`                                                                                                                                                                                                   |
| **Recruit**        | Không còn `$.easyAjax` trong `Modules/Recruit/Resources/views/**` (đã hoàn tất wave 1-3: `recruit-setting/**`, `report/**`, `jobs/**`, `job-applications/**`, `interview-schedule/**`, `front/**`, `skills/**`, `candidate-database/**`)            |
| **ServerManager**  | Không còn `$.easyAjax` trong `Modules/ServerManager/Resources/views/**`                                                                                                                                                                             |
| **Sms**            | Không còn `$.easyAjax` trong `Modules/Sms/Resources/views/**`                                                                                                                                                                                       |
| **Subdomain**      | Không còn `$.easyAjax` trong `Modules/Subdomain/Resources/views/**`                                                                                                                                                                                 |
| **Warehouse**      | Không còn `$.easyAjax` trong `Modules/Warehouse/Resources/views/**`                                                                                                                                                                                 |
| **Webhooks**       | Không còn `$.easyAjax` trong `Modules/Webhooks/Resources/views/**`                                                                                                                                                                                  |
| **Zoom**           | Không còn `$.easyAjax` trong `Modules/Zoom/Resources/views/**`                                                                                                                                                                                      |

**Không thấy `$.easyAjax` trong Blade** (tại thời điểm audit):

| Module              | Ghi chú                                   |
| ------------------- | ----------------------------------------- |
| **QRCode**          | Đã dùng **`apiHttp`** trong các view ajax |
| **DeveloperTools**  | Không khớp pattern trong `*.blade.php`    |
| **LineIntegration** | Không khớp pattern trong `*.blade.php`    |

_(Nếu sau này thêm blade mới, chạy lại lệnh quét.)_

---

## 6. Liên quan “AJAX” khác (không phải easyAjax)

- **`layouts/app.blade.php`** — có thể chứa script global / gọi AJAX.
- **Payment / public** — một số trang thanh toán public dùng script riêng.
- **Super-admin / front** — SPA nhẹ, form AJAX.

---

## 7. Hướng dùng tài liệu này

1. Ưu tiên migrate theo **`docs/axios-migration/README.md`** (Product → Client → …).
2. **Import:** ưu tiên nâng **`import/process-form.blade.php`** + `ImportController` poll lên `apiHttp` sau khi backend ổn định (worker, không queue trong HTTP).
3. Tránh copy-paste JS trong từng blade: dùng **một module JS** (`resources/js` + import trong `main.js`) + helper gọi lại.

---

_Tệp được tạo để takeover / roadmap; số file chính xác có thể thay đổi theo nhánh git._
