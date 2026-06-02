# Developer Tools: gói có tick nhưng không thấy Settings

**Mã:** `MOD-DEVTOOLS-001` · **Cập nhật:** 2026-04-06 · **Registry:** [`REGISTRY.md`](REGISTRY.md) · **Review module:** [`DEVELOPER_TOOLS_MODULE_REVIEW.md`](DEVELOPER_TOOLS_MODULE_REVIEW.md)

## Triệu chứng

- `packages.module_in_package` có `developertools` nhưng **không có** menu Developer Tools / CodeMap.
- `packages:modules activate` báo “đã có module” nhưng `module_settings.is_allowed` vẫn 0.

## Nguyên nhân (tóm)

| #   | Vấn đề                                                                                                                   |
| --- | ------------------------------------------------------------------------------------------------------------------------ |
| 1   | `updateModuleSettings()` chỉ **UPDATE** dòng đã có — company cũ thiếu row `developertools`                               |
| 2   | JSON gói dạng object — so khớp tên module sai nếu không lower-case                                                       |
| 3   | Menu cần `user_can_access_developertools_module()` (admin hoặc `manage_module_setting`); super admin cần company context |
| 4   | `activate --module=developertools` từng **skip** sync nếu gói đã có module (đã sửa)                                      |

## Fix đã có trong code

- `developertools` trong `ModuleSetting::OTHER_MODULES` + migration backfill `2026_04_06_120000_*`
- `ensureModuleSettingsRowsForPackageModules()`, `packageModuleNamesFromJson()`
- `user_can_access_developertools_module()` — impersonate / `manage_module_setting`
- UI Module Settings: `developertools` theo package (chỉ hiện khi `is_allowed = 1`, giống `pricing`). Không còn trong `TENANT_FEATURE_MODULES`.

**Tests:** `CompanyObserverPackageModulesTest`, `ModuleSettingDeveloperToolsVisibilityTest`, `PackageModulesActivateResyncsModuleSettingsTest`

## Vận hành sau deploy

```bash
php artisan migrate
php artisan packages:modules activate --module=developertools
php artisan cache:clear
```

- `modules_statuses.json`: `"DeveloperTools": true`
- Route tenant: `GET /account/developertools` (`developertools.index`); `/developertools` → 301

## Kiểm tra DB

- `packages.module_in_package` có value `developertools`
- `module_settings`: `module_name=developertools`, `company_id` đúng, `status=active`, `is_allowed=1` (admin/employee)

## Lưu ý

- Tick marketing “có Developer Tools” ≠ JSON gói thật → `is_allowed` vẫn 0.
- Key dịch `messages.moduleNotInPackage`: publish LanguagePack (`languagepack:publish-translation`).
