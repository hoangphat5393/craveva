# Báo giá Phase 1 — toggle duyệt 2 cấp bị khóa trên Hub

## Phạm vi

- Người dùng báo không bật được `OEM quotation review (2-step approval)` cho `company_id=20` trên Hub.
- Yêu cầu lúc đó: kiểm tra SSH nhiều công ty, xác định nguyên nhân, đưa phương án xử lý không cần sửa code ngay.

## Đã kiểm tra

### 1) UI disable condition (code)

- File: `resources/views/module-settings/ajax/modules.blade.php`
- Toggle is disabled only when:
    - `module_name == settings`, or
    - `is_allowed != 1`

```php
@if ($setting->module_name == 'settings' || (int) $setting->is_allowed !== 1) disabled @endif
```

### 2) Hub DB state for feature module (SSH verified)

- Query result (`module_settings`, `module_name='estimates_phase1_review'`):
    - `company_id=20`: `admin=deactive,is_allowed=0`, `employee=deactive,is_allowed=0` (before fix)
    - This directly explains why toggle appeared disabled in UI.
    - After operational fix: `admin=active,is_allowed=1`, `employee=active,is_allowed=1`.

### 3) Cross-company impact

- Companies that have `is_allowed=0` for this module will see disabled toggle in Module Settings.
- Company 20 was one confirmed affected case on Hub during this investigation.
- Recommend running one SQL audit on Hub to enumerate all affected company IDs before bulk rollout.

### 4) Git parity check (local vs hub)

- Local HEAD: `2ab265e1`
- Hub HEAD: `2ab265e1`
- Commit is aligned (no commit drift).
- Hub has runtime-only modified files:
    - `public/css/custom-css`
    - `public/user-uploads/.htaccess`

## Nguyên nhân gốc

Primary root cause on Hub for company 20:

1. **Row-level lock condition**: `is_allowed=0` in `module_settings`
2. UI logic disables toggle whenever `is_allowed !== 1`

Secondary contributing cause: 3. **Resync/package operations** can revert this module back to deactive/not-allowed if package entitlement policy does not include it.

## Cách xử lý vận hành

1. Force-enable for company 20 (DB-level operational fix):
    - Set `status='active', is_allowed=1` for `module_name='estimates_phase1_review'` on `admin` + `employee`.
2. Clear cache:
    - `php artisan optimize:clear`
3. Re-test on:
    - `/account/settings/module-settings?tab=admin`
    - `/account/settings/module-settings?tab=employee`
4. If status flips back automatically:
    - Audit scheduled/manual runs of module resync commands and package edits.
5. Optional permission check if toggle still cannot persist:
    - verify current user has `manage_module_setting = all`.

## Kết luận

- Đây không phải lỗi lệch code upload giữa local và lịch sử commit trên hub.
- Nguyên nhân chặn trực tiếp là `is_allowed=0` trên hub cho company 20.
- Đã xử lý vận hành và kiểm tra qua SSH: các dòng hiện là `active + is_allowed=1`.

## Fix code lâu dài

- `TENANT_FEATURE_MODULES` = `estimates_phase1_review` only (`developertools` uses package flow like `pricing`).
- Đã cập nhật `CompanyObserver::updateModuleSettings()` để các module tính năng theo tenant
  (`ModuleSetting::TENANT_FEATURE_MODULES`) không còn bị ép về
  `is_allowed=0` khi không nằm trong `packages.module_in_package`.
- Đã thêm migration sửa dữ liệu:
    - `database/migrations/2026_06_02_160000_normalize_tenant_feature_module_settings_is_allowed.php`
    - Ensures tenant feature rows are `is_allowed=1` for `admin` and `employee`.
- Đã thêm test hồi quy:
    - `tests/Feature/CompanyObserverTenantFeatureModuleSettingsTest.php`
    - Confirms package resync keeps `estimates_phase1_review` allowed.
