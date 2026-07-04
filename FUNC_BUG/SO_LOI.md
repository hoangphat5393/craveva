# FUNC_BUG — Sổ lỗi đã gặp

> Cách dùng: tìm theo nhóm lỗi, đọc dòng tóm tắt trước, mở file chi tiết khi cần bước xử lý cụ thể.

## 1. Module / package không hiện

| Mã | Triệu chứng | Nguyên nhân chính | Cách xử lý | Chi tiết |
| --- | --- | --- | --- | --- |
| `MOD-CACHE-001` | Custom module bật nhưng không thấy menu | Cache `craveva_plugins` / `user_modules` cũ | Bật qua UI Custom Modules hoặc `packages:modules enable-custom`, rồi `cache:clear` | [`BUG_MODULE_MENU.md`](BUG_MODULE_MENU.md) |
| `MOD-AFF-002` | Affiliate bật nhưng admin không thấy menu | Sidebar Affiliate chỉ hiện với user có affiliate active | Tạo/gán affiliate active cho user test | [`BUG_MODULE_MENU.md`](BUG_MODULE_MENU.md) |
| `MOD-DEVTOOLS-001` | Gói có Developer Tools nhưng Settings không có menu | Thiếu/sai `module_settings`, quyền admin/company context | `migrate`, `packages:modules activate --module=developertools`, đăng nhập lại | [`BUG_MODULE_MENU.md`](BUG_MODULE_MENU.md) |
| `MOD-PRICING-001` | Staging có Pricing, local không có | Package đổi nhưng chưa sync `module_settings`; custom module tắt | `packages:modules activate --module=pricing`, bật Custom Module, `cache:clear` | [`BUG_MODULE_MENU.md`](BUG_MODULE_MENU.md) |
| `MOD-EST-P1-001` | Hub không bật được toggle duyệt báo giá Phase 1 | `module_settings.is_allowed = 0` cho `estimates_phase1_review` | Set `active + is_allowed=1`, clear cache; code đã có migration normalize | [`BUG_ESTIMATE_TOGGLE.md`](BUG_ESTIMATE_TOGGLE.md) |

## 2. Import / hiệu năng dữ liệu

| Mã | Triệu chứng | Nguyên nhân chính | Cách xử lý | Chi tiết |
| --- | --- | --- | --- | --- |
| `IMPORT-CLIENT` | Import khách lỗi delimiter, name null, file staging không thấy | Encoding/map cột/quyền thư mục temp | Kiểm map cột, quyền `user-uploads/temp`, đọc runbook staging | [`BUG_IMPORT_CLIENT.md`](BUG_IMPORT_CLIENT.md) |
| `IMPORT-PRODUCT` | Import sản phẩm chậm hoặc cột không match | Custom field không vào map; poll chạy queue trong HTTP | Chunk + cache lookup; xem flow import canonical | [`BUG_IMPORT_PRODUCT.md`](BUG_IMPORT_PRODUCT.md) |
| `PERF-CLIENT-DT-001` | Bảng khách hàng ~100 dòng vẫn chậm | Log `info()` và load toàn bộ `custom_fields_data` | Bỏ log, chỉ load CF theo ids trang hiện tại | [`BUG_CLIENT_TABLE_SLOW.md`](BUG_CLIENT_TABLE_SLOW.md) |

## 3. UI / Auth / Ngôn ngữ

| Mã | Triệu chứng | Nguyên nhân chính | Cách xử lý | Chi tiết |
| --- | --- | --- | --- | --- |
| `AUTH-SOCIAL-001` | Social Auth Settings báo `The MAC is invalid` | DB copy từ môi trường khác nhưng `APP_KEY` khác | Đồng bộ `APP_KEY` hoặc nhập lại secret; UI không render secret cũ | [`BUG_SOCIAL_AUTH_MAC.md`](BUG_SOCIAL_AUTH_MAC.md) |
| `AUTH-AI-002` | Dashboard báo `The MAC is invalid` sau đăng nhập | Hai AI API key được mã hóa bằng APP_KEY cũ | Optional key lỗi trả `null`, reset ciphertext hỏng và nhập lại khi cần | [`BUG_AI_GLOBAL_SETTING_MAC.md`](BUG_AI_GLOBAL_SETTING_MAC.md) |
| `I18N-RECRUIT-001` | Recruit Settings báo `Array to string conversion` | Key dịch `Source` trùng file `source.php` trên Windows | Dùng key đầy đủ `recruit::modules.sourceSetting.source` | [`BUG_RECRUIT_SOURCE.md`](BUG_RECRUIT_SOURCE.md) |
| `I18N-ENG-001` | Locale `eng` và `en` lệch | LanguagePack từng dùng code không chuẩn | Dùng `en`, publish translation sau deploy | Phụ lục bên dưới |

## 4. Production / kho

| Mã | Triệu chứng | Nguyên nhân chính | Cách xử lý | Chi tiết |
| --- | --- | --- | --- | --- |
| `PROD-UOM-001` | Post RM trừ kho sai đơn vị, ví dụ 100g thành 100kg | `recordOutbound` thiếu quy đổi UOM | Đã vá `ProductionPostingService`; giữ test regression | [`BUG_PRODUCTION_UOM.md`](BUG_PRODUCTION_UOM.md) |

## 5. Staging / vận hành

| Mã | Triệu chứng | Nguyên nhân chính | Cách xử lý | Chi tiết |
| --- | --- | --- | --- | --- |
| `OPS-STAGING-SSH` | SSH permission denied, deploy script lỗi | GCP metadata `ssh-keys`, khác user Windows/Linux | Dùng script sync SSH và script SSH chuẩn | [`BUG_STAGING_OPS.md`](BUG_STAGING_OPS.md), [`BUG_STAGING_SSH.md`](BUG_STAGING_SSH.md) |
| `OPS-STAGING-INC` | Nginx timeout, PHP upload, module missing | Incident vận hành từng đợt | Đọc `docs/SERVER_RUNBOOK.md` | [`BUG_STAGING_OPS.md`](BUG_STAGING_OPS.md) |
| `QA-TESTS-SNAPSHOT` | Full suite từng fail hàng loạt | Test/env lệch spec | Xem `FUNC_TEST/INDEX.md`, sửa từng cụm | Đã gộp pass 4 |

## 6. Rà soát / tham khảo

| Mã | Nội dung | Khi cần đọc |
| --- | --- | --- |
| `REF-DEVTOOLS` | Rà soát module Developer Tools: bảo mật, cấu trúc, scanner | Khi tiếp tục refactor Developer Tools: [`REVIEW_DEVTOOLS.md`](REVIEW_DEVTOOLS.md) |

---

## Mẫu ghi bug mới (copy)

```markdown
| **MÃ-XXX** | Triệu chứng một dòng | Nguyên nhân một dòng | Lệnh / bước fix | Open/Fixed | [file](file.md) |
```

**Luồng module / package hay gặp lại:**

1. Sửa **package** (`module_in_package`) **không** tự cập nhật `module_settings` → chạy `php artisan packages:modules activate --module=<tên>`.
2. Bật **nwidart** custom module → dùng UI Custom Modules hoặc `packages:modules enable-custom` (không chỉ `module:enable`).
3. Menu custom module → cache `craveva_plugins`; menu tenant → `user_modules_{id}` → `cache:clear` + đăng nhập lại.

**Canonical vận hành staging:** `docs/SERVER_RUNBOOK.md`, `docs/STAGING_OPERATIONS.md`.

---

## Phụ lục: I18N-ENG-001 — chuẩn hóa `eng` → `en`

**Mục tiêu:** ISO 639-1 `en` duy nhất trong LanguagePack và runtime; không còn thư mục `eng` trong repo.

### Git & môi trường

| Nội dung               | Ghi chú                                                                                                                                                             |
| ---------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **`resources/lang/*`** | Trong `.gitignore` — **không push** bản dịch publish. Mỗi môi trường cần **publish** từ LanguagePack.                                                               |
| **Nguồn trong Git**    | `Modules/LanguagePack/Languages/app/en`, `Modules/LanguagePack/Languages/modules/<Module>/en` — **sửa và commit tại đây**.                                          |
| **Sau deploy / pull**  | `php artisan migrate` (nếu chưa chạy `2026_03_13_100000_standardize_language_code_eng_to_en`), rồi `languagepack:publish-translation`, `cache:clear`, `view:clear`. |

### Database

Migration: `database/migrations/2026_03_13_100000_standardize_language_code_eng_to_en.php` — gộp/xóa `language_settings.language_code = 'eng'`, chuyển FK sang `en`.

### Checklist staging / hub

1. Pull branch.
2. `composer install` nếu cần.
3. `php artisan migrate` (backup DB production trước).
4. `php artisan languagepack:publish-translation`
5. `php artisan cache:clear` && `php artisan view:clear`
6. Smoke: Settings → Language, Custom Fields, một màn hình thanh toán / hóa đơn (`en`).

### Không ảnh hưởng / cần kiểm tra

- **PayFast** URL `…/eng/process`: path cổng thanh toán, không đổi.
- Sau deploy: ngôn ngữ mặc định công ty/user = `en`; không còn record `eng`.

### Script merge backup (hiếm)

```bash
# LANGPACK_ENG_BACKUP_ROOT = tree Languages còn app/eng (git history hoặc zip)
php scripts/merge_eng_into_en_lang.php --dry-run
```

**Lịch sử doc riêng:** `git log -- FUNC_BUG/ENG_TO_EN_STANDARDIZATION.md`
