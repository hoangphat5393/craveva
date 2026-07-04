# Audit: `easyAjax`, AJAX liên quan & axios (`apiHttp`)

**Repo:** `craveva-staging`  
**Cập nhật:** 2026-06-27 (quét tĩnh bằng `rg` trong source, không gồm `node_modules` / `vendor` / `storage/framework/views` đã compile).

## 0. Kết luận hiện tại

Migration `$.easyAjax` đã sạch ở app/module views và installer environment view. Direct `$.ajax` vẫn là backlog riêng.

Kết quả quét ngày 2026-06-27:

| Pattern | Kết quả |
| ------- | ------- |
| `$.easyAjax(` | 0 matches |
| `$.ajax(` / `jQuery.ajax(` | 32 matches trong 21 files |
| `window.apiHttp` / `apiHttp.` | 1065 files |

Breakdown của `$.easyAjax(`:

| Area | Files |
| ---- | ----- |
| `resources/views/**` | 0 |
| `Modules/**` | 0 |

Installer environment view dùng standalone browser `fetch` vì installer pages không load `public/js/main.js` / `window.apiHttp`. Direct `$.ajax` vẫn còn và được theo dõi ở mục 4.

## 1. Tổng quan kiến trúc

| Thành phần                           | Vai trò                                                                                                                    |
| ------------------------------------ | -------------------------------------------------------------------------------------------------------------------------- |
| **`public/vendor/helper/helper.js`** | Định nghĩa **`$.easyAjax`** (jQuery), mặc định `dataType: 'json'`, xử lý `handleFail`, blockUI, v.v.                       |
| **`resources/js/http/apiClient.js`** | **Axios** instance + **`window.apiHttp`** (`get`, `post`, `postForm`, `postUrlEncoded`, `delete`, …), CSRF, normalize lỗi. |
| **`resources/js/main.js`**           | `require('./http/apiClient')` — load client trên layout đã auth.                                                           |
| **Blade `$.ajax` thuần**             | Một số view gọi **`$.ajax`** / **`jQuery.ajax`** trực tiếp (không qua `easyAjax`) — xem mục 4.                             |

**Lưu ý:** "API" REST (`routes/api.php`) **không** là luồng import chính; import dùng route **web** `/account/...` + JSON `Reply` (AJAX trong trình duyệt).

---

## 2. Phương pháp quét

- **`$.easyAjax` / `easyAjax(`** trong `*.blade.php` và `*.js` (core + `Modules/**`).
- **`window.apiHttp` / `apiHttp.`** trong `*.blade.php` và `*.js` (đã migrate một phần).
- **`$.ajax` / `jQuery.ajax`** trong `*.blade.php` và `*.js` (bổ sung).

Chạy lại sau khi sửa code (PowerShell từ root repo):

```powershell
Select-String -Path "resources\views\**\*.blade.php","Modules\**\*.blade.php" -Pattern '\$\.easyAjax' -Recurse
```

Lệnh `rg` đang dùng cho snapshot 2026-06-27:

```powershell
rg -n '\$\.easyAjax\s*\(' resources\views Modules -g '*.blade.php' -g '*.js'
rg -n '\$\.ajax\s*\(|jQuery\.ajax\s*\(' resources\views Modules -g '*.blade.php' -g '*.js'
rg -n 'window\.apiHttp|apiHttp\.' resources\views Modules resources\js -g '*.blade.php' -g '*.js'
```

---

## 3. Core app — `resources/views/`

- **`$.easyAjax`:** 0 matches.
- **`apiHttp`:** Đã dùng ở nhiều view **clients**, **products**, **orders**, **invoices**, **payments**, **warehouse**, **purchase**, **QRCode**, v.v. — xem bảng wave trong `docs/axios-migration/README.md`.

Hotspot còn lại của `$.easyAjax`:

| Area | Files |
| ---- | ----- |
| None | 0 |

Một số scope từng ghi completed nhưng vẫn còn adjacent legacy:

| Scope | Kết quả quét |
| ----- | ------------ |
| `resources/views/invoices/**` | 0 `$.easyAjax`, còn 2 direct `$.ajax` |
| `resources/views/estimate-requests/**` | 0 `$.easyAjax` |
| `resources/views/estimates-templates/**` | 0 `$.easyAjax` |
| `resources/views/tasks/**` | 0 `$.easyAjax` |
| `resources/views/super-admin/**` | 0 `$.easyAjax` |

**File import dùng chung (quan trọng):**

- `resources/views/import/process-form.blade.php` — đã chuyển **`window.apiHttp`** (`get` poll + `postUrlEncoded` submit). Bản song song: `Modules/Recruit/Resources/views/import/process-form.blade.php`.
- Các `*_progress.blade.php` chỉ `@include('import.process-form', ...)`.

---

## 4. `$.ajax` trực tiếp (không qua `easyAjax`)

Còn 32 matches trong 21 files, rải rác trong `resources/views` và `Modules/**` (ví dụ hierarchy, timelog, server, biolinks, invoices). Nên gom vào cùng kế hoạch với axios hoặc wrapper `apiHttp`.

---

## 5. Modules (nwidart) — còn dùng `$.easyAjax` trong Blade

Kết quả sau batch 2026-06-22: không còn file module nào dùng `$.easyAjax`.

Trạng thái các module đã quét:

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
| **Purchase**       | Không còn `$.easyAjax` trong `Modules/Purchase/Resources/views/**`                                                                                                                                                                                  |
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

## 6. Liên quan "AJAX" khác (không phải easyAjax)

- **`layouts/app.blade.php`** — có thể chứa script global / gọi AJAX.
- **Payment / public** — một số trang thanh toán public dùng script riêng.
- **Super-admin / front** — SPA nhẹ, form AJAX.

---

## 7. Hướng dùng tài liệu này

1. Ưu tiên migrate theo hotspot thực tế trong snapshot trên, không chỉ dựa vào bảng wave đã completed.
2. **Import:** `resources/views/import/process-form.blade.php` đã chuyển qua `apiHttp`; nếu sửa tiếp thì giữ cùng pattern với `ImportController`.
3. Tránh copy-paste JS trong từng blade: dùng **một module JS** (`resources/js` + import trong `main.js`) + helper gọi lại.
4. Sau mỗi wave, chạy lại 3 lệnh `rg` ở mục 2 và cập nhật snapshot.

---

_Tệp được dùng để takeover / roadmap; số file chính xác có thể thay đổi theo nhánh git._
