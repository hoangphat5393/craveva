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

- **`$.easyAjax`:** Rất nhiều file (ước lượng **hàng trăm** blade) — gồm `super-admin/**`, `tasks/**`, `projects/**`, `leads/**`, `invoices/**`, `dashboard/**`, `import/process-form.blade.php`, v.v.
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

| Module             | Ghi chú                                        |
| ------------------ | ---------------------------------------------- |
| **Affiliate**      | Có                                             |
| **Asset**          | Có                                             |
| **Biometric**      | Có                                             |
| **Biolinks**       | Có (nhiều block)                               |
| **CyberSecurity**  | Có                                             |
| **EInvoice**       | Có (ít)                                        |
| **LanguagePack**   | Có (script)                                    |
| **Letter**         | Có                                             |
| **Onboarding**     | Có                                             |
| **Payroll**        | Có (nhiều)                                     |
| **Performance**    | Có (nhiều)                                     |
| **Policy**         | Có                                             |
| **Pricing**        | Có                                             |
| **ProjectRoadmap** | Có                                             |
| **Purchase**       | Có (nhiều)                                     |
| **Recruit**        | Có (rất nhiều; có `import/process-form` riêng) |
| **ServerManager**  | Có                                             |
| **Sms**            | Có                                             |
| **Subdomain**      | Có                                             |
| **Warehouse**      | Có (ít)                                        |
| **Webhooks**       | Có                                             |
| **Zoom**           | Có                                             |

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
