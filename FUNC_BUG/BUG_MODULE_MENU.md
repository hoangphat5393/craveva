# Module bật nhưng không hiện menu / tính năng

**Mã liên quan:** `MOD-CACHE-001`, `MOD-AFF-002`, `MOD-DEVTOOLS-001`, `MOD-PRICING-001`  
**Sổ lỗi:** [`SO_LOI.md`](SO_LOI.md)

## Khi nào đọc file này?

Đọc file này khi gặp các lỗi cùng kiểu:

- Package có module nhưng tenant không thấy menu.
- Đã `module:enable` nhưng UI vẫn không hiện.
- Staging thấy module, local không thấy.
- Module custom bật trong `modules_statuses.json` nhưng sidebar vẫn thiếu.

## Nguyên nhân chung

| Nhóm nguyên nhân | Dấu hiệu | Cách xử lý |
| --- | --- | --- |
| Package chưa sync `module_settings` | `packages.module_in_package` có module nhưng `module_settings.is_allowed = 0` hoặc thiếu row | Chạy `php artisan packages:modules activate --module=<module>` |
| Custom module nwidart chưa bật đúng cách | `modules_statuses.json` lệch hoặc cache `craveva_plugins` cũ | Bật qua UI Custom Modules hoặc `php artisan packages:modules enable-custom` |
| Cache menu/user module cũ | Sau khi bật module vẫn không đổi UI | `php artisan cache:clear`, đăng xuất/đăng nhập lại |
| Điều kiện nghiệp vụ riêng của module | Module đã bật nhưng user hiện tại vẫn không đủ điều kiện xem menu | Kiểm tra rule riêng trong sidebar/helper |

## Checklist xử lý nhanh

```bash
php artisan migrate
php artisan packages:modules activate --module=<module>
php artisan packages:modules enable-custom
php artisan cache:clear
```

Sau đó đăng nhập lại tenant cần kiểm tra.

## Trường hợp cụ thể đã gặp

### Affiliate

**Triệu chứng:** `modules_statuses.json` có `"Affiliate": true`, module đã migrate, nhưng company dashboard không thấy Affiliate.

**Nguyên nhân:**

- `craveva_plugins()` cache danh sách module enable, không tự refresh nếu chỉ chạy `module:enable` hoặc sửa JSON tay.
- Sidebar của Affiliate có điều kiện `isAffiliate()`: admin thường không thấy menu nếu user không có bản ghi `affiliates.status = active`.

**Fix:**

- Bật bằng UI Custom Modules hoặc `php artisan packages:modules enable-custom`.
- Nếu test bằng user thường, tạo/gán affiliate active trước.

### Pricing

**Triệu chứng:** staging thấy Pricing nhưng local không thấy.

**Nguyên nhân:** sửa package không tự gọi `CompanyObserver::updateModuleSettings()`; custom module Pricing có thể đang tắt; cache `user_modules_{id}` cũ.

**Fix:**

```bash
php artisan packages:modules activate --module=pricing
php artisan packages:modules enable-custom
php artisan cache:clear
```

Kiểm tra thêm: Settings -> Custom Modules -> Pricing ON; route `pricing.tiers.index` tồn tại.

### Developer Tools

**Triệu chứng:** package có `developertools` nhưng không có menu Developer Tools / CodeMap; `module_settings.is_allowed` vẫn 0.

**Nguyên nhân cũ đã vá:**

- `updateModuleSettings()` chỉ update row đã có, không tạo row cho company cũ.
- JSON gói dạng object làm so khớp module sai nếu không chuẩn hóa lower-case.
- Menu cần quyền admin hoặc `manage_module_setting`; super admin cần company context.
- Lệnh activate từng skip sync nếu package đã có module.

**Fix vận hành sau deploy:**

```bash
php artisan migrate
php artisan packages:modules activate --module=developertools
php artisan cache:clear
```

**Test liên quan:** `CompanyObserverPackageModulesTest`, `ModuleSettingDeveloperToolsVisibilityTest`, `PackageModulesActivateResyncsModuleSettingsTest`.

## Code tham chiếu

- Cache module: `app/Helper/start.php` (`craveva_plugins()`, `user_modules()`)
- Bật custom module: `CustomModuleController::update()`, `PackageModulesCommand::runEnableCustom()`
- Developer Tools permission: `user_can_access_developertools_module()`
