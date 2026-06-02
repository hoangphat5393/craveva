# Legacy PHP / asset — audit candidates (2026-05-27)

**Mục đích:** Chỉ xóa file **rác / thừa** — không được gọi từ bất kỳ đâu, hoặc backup/duplicate tạo trong lúc dev.

**Công cụ:** `php scripts/audit_orphan_controllers.php <route-list.json>`

---

## 0) Chính sách xóa code (PM / team) — **bắt buộc**

| Được xóa                                                         | **Không** được xóa                                                 |
| ---------------------------------------------------------------- | ------------------------------------------------------------------ |
| Stub/scaffold (class rỗng, view orphan không `return view(...)`) | **Cả module** `Modules/<Name>/` — kể cả module **tắt** trên tenant |
| Backup dev: `*.bak`, `* copy.*`, `*.backup-*`, `.md` trùng byte  | File thuộc module còn route / provider / migration / package       |
| Script one-off hết reference trong repo + runbook                | Migration đã deploy (dù tên `legacy`)                              |
| View/layout zero reference (`view()`, `@include`, `@extends`)    | `Resources/lang/eng/` — ticket LanguagePack riêng                  |

**Quy tắc vàng:** Module **off** ≠ dead code. Chỉ xóa **từng file** khi `rg`/IDE **0 reference** (routes, config, tests, blade, autoload, runbook).

**Quy trình:** `rg` → test filter → PR nhỏ → ghi §9.

### Scaffold nwidart (`index`, `layouts/master`) — **không phải runtime**

|                      |                                                                                                                                                                                                                      |
| -------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Là gì**            | Template copy **một lần** từ `php artisan module:make` — khai báo trong `config/modules.php` → `stubs.files` (`views/index`, `views/master`).                                                                        |
| **Runtime dùng gì**  | `module.json` → `ServiceProvider` (`loadViewsFrom`, routes) → `storage/app/modules_statuses.json` (bật/tắt) → lớp Craveva `packages:modules` + DB `module_settings`. **Không** đọc checklist “phải có index/master”. |
| **Có tự sinh lại?**  | **Không** — trừ khi chạy lệnh generate thủ công (`module:make`, `module:make-view`, …). `module:enable`, `cache:clear`, deploy **không** tạo lại file đã xóa.                                                        |
| **Khi nào xóa được** | `rg` **0 reference** tới view đó (`return view(...)`, `@extends`, `@include`). Module vẫn chạy nếu code dùng view khác (`layouts.app`, `letter::template.index`, …).                                                 |
| **Flow SSOT**        | [FUNC_LOGIC/FLOW_Modules_Package_LanguagePack_CustomFields_VI.md](../FUNC_LOGIC/FLOW_Modules_Package_LanguagePack_CustomFields_VI.md) §3                                                                             |

---

## 1) False positive (không xóa chỉ vì…)

| Lý do                            | Ví dụ                                  |
| -------------------------------- | -------------------------------------- |
| Không match `route:list` JSON    | `AccountBaseController`, ServerManager |
| **Module tắt**                   | Vẫn **giữ** — bật lại phải còn code    |
| Event / Job / Schedule / Artisan | Console commands                       |
| Blade động                       | Kiểm tra partials                      |
| Provider `boot()`                | `app/Providers/*`                      |

---

## 2) Asset backup (Tier 0)

| Path                              | Trạng thái                                    |
| --------------------------------- | --------------------------------------------- |
| `public/js/custom copy.js`        | **Đã xóa**                                    |
| `menu.blade.backup-20260116.php`  | **Đã xóa**                                    |
| `theme-custom.backup-*.css`       | **Đã xóa**                                    |
| `DESIGN_BACKEND_UI_UX_VI copy.md` | **Đã xóa**                                    |
| `main.js.bak`                     | **Đã xóa** — backup dev, không Mix            |
| `phpunit.xml.bak`                 | **Giữ** (gitignore; artifact PHPUnit migrate) |

**Giữ:** `copy-production-bom.blade.php` — partial nghiệp vụ, không phải rác.

---

## 3) Controller heuristic (12 file) — **không đủ để xóa module**

| Path                                         | Kết luận                               |
| -------------------------------------------- | -------------------------------------- |
| Base controllers (Account, Front SA/Recruit) | **Giữ**                                |
| ~~`ForgotPasswordController`~~               | **Đã xóa** — stub; Fortify             |
| ~~`RecruitController`~~                      | **Đã xóa** — scaffold; no route        |
| Payroll API, ServerManager, Subdomain        | **Giữ** — có route `Modules/*/Routes/` |

---

## 4) Provider TODO — **giữ** đến ticket refactor

`SmtpConfigProvider`, `TranslateSettingConfigProvider`, `SessionDriverConfigProvider`, `PaymentGatewayCredentialController`.

---

## 5) Migration tên `legacy` — **không xóa file**

---

## 6) Scripts — theo `scripts/AUDIT_2026_VI.md`. Xóa script chỉ khi zero reference.

---

## 7) Diagram

~~`pis_e2e_current_copy.*`~~ — **đã xóa** (doc pass 12).

---

## 8) Checklist xóa **một file**

- [ ] `rg` class / view / path — **0** hit (trừ audit log)
- [ ] **Không** xóa cả module
- [ ] **Không** dùng tiêu chí “module đang tắt”
- [ ] Test pass (nếu có)
- [ ] Ghi §9

---

## 9) Đã xóa

| Ngày       | Path                                           | Ghi chú                                        |
| ---------- | ---------------------------------------------- | ---------------------------------------------- |
| 2026-05-27 | Tier 0 doc/asset                               | LEGACY_PRE_DELETE_AUDIT                        |
| 2026-06-02 | `ForgotPasswordController.php`                 | Stub; Fortify                                  |
| 2026-06-02 | `RecruitController.php`                        | Scaffold; no route                             |
| 2026-06-02 | Production / Performance / Policy orphan views | Hello World; zero call                         |
| 2026-06-02 | Doc pass 9–13                                  | LEGACY_ARCHIVE                                 |
| 2026-06-02 | `public/js/main.js.bak`                        | Backup dev; không Mix, zero reference          |
| 2026-05-27 | `Letter/Resources/views/index.blade.php`       | Scaffold; chỉ text "Letter"; 0 `letter::index` |
| 2026-05-27 | `Onboarding/.../layouts/master.blade.php`      | nwidart default; 0 `@extends` / `view()`       |
| 2026-05-27 | `Zoom/.../layouts/master.blade.php`            | nwidart default; module dùng `layouts.app`     |
| 2026-05-27 | `Sms/.../layouts/master.blade.php`             | nwidart default; zero reference                |
| 2026-05-27 | `DeveloperTools/.../layouts/master.blade.php`  | nwidart default; zero reference                |

**Test:** `tests/Feature/LegacyDeadControllerCleanupTest.php`

---

## 10) Pass tiếp (chỉ file rác)

| Hạng mục                       | Hành động                                        |
| ------------------------------ | ------------------------------------------------ |
| Production / Client / Estimate | **Pass 12** — báo cáo §11; không xóa             |
| `recruit::index` (Hello World) | **Giữ** — controller khác vẫn `return view(...)` |
| `LineIntegration` index        | **Giữ** — có route resource                      |
| `Modules/*/lang/eng/`          | **Chưa xóa** — i18n ticket                       |
| `tests/scripts/*.php`          | Rà reference; xóa từng file nếu 0 hit            |

**Cấm:** xóa thư mục module, gỡ package composer, drop bảng vì module tắt.

---

## 11) Báo cáo module / domain phức tạp — **chưa xóa** (cần PM duyệt)

> **Pass 12 (2026-05-27):** Production (nwidart), Client + Estimate (core `app/`). Chỉ báo cáo — **không xóa** trong pass này.

### Production (`Modules/Production/`) — nwidart module

| Kiểm tra                             | Kết quả                                                                                                                                                                                                                                          |
| ------------------------------------ | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| Hello World / root `index.blade.php` | **Không có** — đã xóa pass trước                                                                                                                                                                                                                 |
| `layouts/master.blade.php` scaffold  | **Không có** file                                                                                                                                                                                                                                |
| View vs controller                   | 34 blade; cross-ref `@include` / `view('production::…')` / DataTable `render()` — **đủ reference**                                                                                                                                               |
| Controller                           | 5 controller active (BOM, orders, batches, material-shortages, fg-quantity-policy)                                                                                                                                                               |
| Backup dev (`*.bak`, `* copy.*`)     | **Không có** trong module                                                                                                                                                                                                                        |
| Config comment “legacy FG”           | **Nghiệp vụ** (`Config/config.php`) — không phải file rác                                                                                                                                                                                        |
| **Candidate (alias scaffold)**       | `Modules/Production/app/Services/ProductionMaterialSummaryService.php` — extends class gốc `Services/`; **0 import** `Modules\Production\App\Services\…` trong repo. Có thể gộp/xóa alias **sau khi PM duyệt** (không ảnh hưởng nwidart enable). |

**Đề xuất:** Không xóa view/controller. Alias `app/Services/` — ticket riêng nếu muốn dọn namespace nwidart `module:make`.

### Client — core app (`app/Http/Controllers/Client*.php`, `resources/views/clients/`)

| Kiểm tra               | Kết quả                                                                                                                                                                                                          |
| ---------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Vị trí                 | **Không** phải nwidart module — routes `routes/web.php`, 7 controller (`ClientController`, contacts, notes, docs, category, …)                                                                                   |
| View                   | 35 blade dưới `resources/views/clients/` — index, ajax tabs, contacts, notes, import, GDPR — **đều wired** qua controller / DataTable                                                                            |
| Hello World / scaffold | **Không có**                                                                                                                                                                                                     |
| Backup dev             | **Không có**                                                                                                                                                                                                     |
| **Bug tiềm ẩn**        | `ClientDocController::show()` → `view('clients.files.view')` nhưng **chỉ có** `clients/files/show.blade.php` — route `client-docs/{id}` có thể **View not found** (store/destroy dùng đúng `clients.files.show`) |

**Đề xuất:** Không xóa file client. Sửa bug view name (`view` → `show`) — ticket riêng, không phải “legacy cleanup”.

### Estimate — core app (`app/Http/Controllers/Estimate*.php`, `resources/views/estimates/`)

| Kiểm tra               | Kết quả                                                                                                                                            |
| ---------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------- |
| Vị trí                 | Core app — 3 controller (`EstimateController`, `EstimateTemplateController`, `EstimateRequestController`) + services `app/Services/Estimates/`     |
| View                   | 27 blade + 5 PDF template + partials phase1/BOM/margin — **active**; tích hợp Production (`estimates.production_bom_lines`, `copy-production-bom`) |
| Hello World / scaffold | **Không có**                                                                                                                                       |
| Backup dev             | **Không có**                                                                                                                                       |
| **Giữ tuyệt đối**      | `estimates/partials/copy-production-bom.blade.php` — partial nghiệp vụ (đã ghi §2)                                                                 |

**Đề xuất:** Không xóa gì trong pass này.

### Pricing (`Modules/Pricing/`)

| Kiểm tra                    | Kết quả                                                                                                      |
| --------------------------- | ------------------------------------------------------------------------------------------------------------ |
| Hello World / scaffold view | **Không có**                                                                                                 |
| Controller                  | 8 controller; `PricingController` = API JSON preview (có route `api.php`); `VolumeDiscountController` = JSON |
| View                        | 29 blade; khớp client/company/tiers/volume_rules/import                                                      |

**Đề xuất:** Không xóa gì thêm trong pass này.

### Module phức tạp khác — orphan `layouts/master` (chưa xóa)

Scaffold nwidart, **0 reference** trong repo — có thể xóa **sau khi duyệt** (giống Production/Pricing policy):

| Path                                                         | Module    |
| ------------------------------------------------------------ | --------- |
| `Modules/Warehouse/Resources/views/layouts/master.blade.php` | Warehouse |
| `Modules/Payroll/Resources/views/layouts/master.blade.php`   | Payroll   |
| `Modules/Purchase/Resources/views/layouts/master.blade.php`  | Purchase  |

### Bug tiềm ẩn (không xóa — báo cáo)

| Path                                                               | Ghi chú                                                              |
| ------------------------------------------------------------------ | -------------------------------------------------------------------- |
| `OnboardingSettingController::show()` → `view('onboarding::show')` | **Không có** `show.blade.php` — có thể 404 nếu route `show` được gọi |
