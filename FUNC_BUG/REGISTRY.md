# FUNC_BUG — Sổ đăng ký lỗi (tra cứu nhanh)

> **Cách dùng:** Tìm triệu chứng → xem **Nguyên nhân** + **Fix**. Chi tiết dài: link cột **Chi tiết**.  
> **Thêm bug mới:** thêm một dòng bảng + (nếu cần) file `MÃ_ngắn_VI.md` theo mẫu `PRODUCTION_RM_OUTBOUND_UOM_VI.md`.

## Bảng tổng hợp

| Mã / chủ đề            | Triệu chứng (tóm)                                    | Nguyên nhân (tóm)                                                     | Fix nhanh                                                                                             | Trạng thái      | Chi tiết                                                                                               |
| ---------------------- | ---------------------------------------------------- | --------------------------------------------------------------------- | ----------------------------------------------------------------------------------------------------- | --------------- | ------------------------------------------------------------------------------------------------------ |
| **MOD-CACHE-001**      | Bật custom module (Affiliate…) nhưng không thấy menu | Cache `craveva_plugins` cũ sau `module:enable` / sửa JSON tay         | `packages:modules enable-custom` hoặc `cache:forget craveva_plugins`; bật qua UI Custom Modules       | Ghi nhận        | [AFFILIATE](AFFILIATE_HIDDEN_IN_COMPANIES.md)                                                          |
| **MOD-AFF-002**        | Module Affiliate bật nhưng user “không thấy gì”      | Menu chỉ cho user `isAffiliate()` (bản ghi `affiliates` active)       | Tạo/gán affiliate; không phải bug nếu login admin thường                                              | Thiết kế        | [AFFILIATE](AFFILIATE_HIDDEN_IN_COMPANIES.md)                                                          |
| **MOD-DEVTOOLS-001**   | Gói có Developer Tools, Settings không có menu       | Thiếu `module_settings`, JSON gói không sync, quyền admin/impersonate | `migrate` + `packages:modules activate --module=developertools` + đăng nhập lại                       | Đã vá (2026-04) | [DEVTOOLS](DEVTOOLS_NO_COMPANY_SETTINGS.md)                                                            |
| **MOD-PRICING-001**    | Pricing có trên staging, không trên local            | Sửa package không gọi `updateModuleSettings`; cache / nwidart tắt     | `packages:modules activate --module=pricing` + bật Custom Module Pricing + `cache:clear`              | Ghi nhận        | [PRICING](PRICING_VISIBLE_STAGING_NOT_LOCAL.md)                                                        |
| **AUTH-SOCIAL-001**    | Social Auth Settings: `The MAC is invalid`           | `APP_KEY` ≠ lúc encrypt secret; Blade decrypt khi render              | Đồng bộ `APP_KEY` (`download_staging_env.ps1 -SyncAppKey`); nhập lại secret (UI không hiện secret cũ) | Đã vá UI        | [SOCIAL](SOCIAL_AUTH_SETTINGS_MAC_INVALID_FIX.md)                                                      |
| **I18N-RECRUIT-001**   | Recruit Settings: `Array to string conversion`       | Windows: `@lang('Source')` trùng file `source.php` (array)            | Dùng `recruit::modules.sourceSetting.source`                                                          | Đã vá           | [RECRUIT](RECRUIT_SOURCE_SETTING_ARRAY_TO_STRING_VI.md)                                                |
| **I18N-ENG-001**       | Locale `eng` vs `en` lệch                            | Chuẩn hóa LanguagePack                                                | `migrate` + `languagepack:publish-translation` + clear cache                                          | Đã làm          | [ENG→EN](ENG_TO_EN_STANDARDIZATION.md)                                                                 |
| **PROD-UOM-001**       | Post RM trừ kho sai ĐVT (100g → trừ 100 kg)          | `recordOutbound` thiếu quy đổi UOM                                    | Đã vá `ProductionPostingService`                                                                      | **Fixed**       | [UOM](PRODUCTION_RM_OUTBOUND_UOM_VI.md) · [spec](../FUNC_IMPROVE/15_PRODUCTION_OUTBOUND_UOM_GAP_VI.md) |
| **PERF-CLIENT-DT-001** | Client DataTable ~100 dòng chậm                      | `info()` log; load toàn bộ `custom_fields_data`                       | Xóa `info()`; filter CF theo ids trang                                                                | Đã vá           | [DATATABLE](CLIENT_DATATABLE_PERFORMANCE.md)                                                           |
| **IMPORT-CLIENT**      | CSV delimiter / name null / file not found staging   | Encoding, map cột, quyền thư mục `user-uploads/temp`                  | Xem master → details; runbook `docs/SERVER_RUNBOOK_VI.md`                                             | Ghi nhận        | [Master](CLIENT_IMPORT_MASTER.md) → [Details](CLIENT_IMPORT_DETAILS_VI.md)                             |
| **IMPORT-PRODUCT**     | Unmatched columns / import chậm                      | Custom field không vào map; poll `queue:work` trong HTTP              | Chunk + cache lookup; xem FUNC_LOGIC import                                                           | Ghi nhận        | [Master](PRODUCT_IMPORT_MASTER.md) → [Details](PRODUCT_IMPORT_DETAILS_VI.md)                           |
| **QA-TESTS-SNAPSHOT**  | Full suite 15 fail (2026-04-08)                      | Test/env lệch spec (302 vs 403, redis queue…)                         | Sửa từng case trong file snapshot                                                                     | Mở              | [TESTS](FULL_TEST_SUITE_FAILURES_SNAPSHOT.md)                                                          |
| **OPS-STAGING-SSH**    | SSH Permission denied; deploy script lỗi             | GCP metadata `ssh-keys`, User Admin vs hoangphat5393                  | `gcloud_staging_ssh_key_sync.ps1`, `ssh_staging.ps1`                                                  | Runbook         | [SSH](STAGING_SSH_GCLOUD_METADATA_AND_DEPLOY_SCRIPT_VI.md)                                             |
| **OPS-STAGING-INC**    | Nginx timeout, PHP upload, module missing…           | Incident từng đợt                                                     | **Ưu tiên** `docs/SERVER_RUNBOOK_VI.md`                                                               | Archive         | [Archive](STAGING_INCIDENTS_ARCHIVE_VI.md)                                                             |
| **REF-DEVTOOLS**       | Review module DeveloperTools (bảo mật, cấu trúc)     | Tài liệu review, không phải ticket                                    | —                                                                                                     | Tham khảo       | [Review](DEVELOPER_TOOLS_MODULE_REVIEW.md)                                                             |

---

## Mẫu ghi bug mới (copy)

```markdown
| **MÃ-XXX** | Triệu chứng một dòng | Nguyên nhân một dòng | Lệnh / bước fix | Open/Fixed | [file](file.md) |
```

**Luồng module / package (hay gặp lại):**

1. Sửa **package** (`module_in_package`) **không** tự cập nhật `module_settings` → chạy `php artisan packages:modules activate --module=<tên>`.
2. Bật **nwidart** custom module → dùng UI Custom Modules hoặc `packages:modules enable-custom` (không chỉ `module:enable`).
3. Menu custom module → cache `craveva_plugins`; menu tenant → `user_modules_{id}` → `cache:clear` + đăng nhập lại.

**Canonical vận hành staging:** `docs/SERVER_RUNBOOK_VI.md`, `docs/STAGING_OPERATIONS.md`.
