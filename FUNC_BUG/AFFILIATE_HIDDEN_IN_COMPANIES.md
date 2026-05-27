# Affiliate bật nhưng không thấy ở company

**Mã:** `MOD-CACHE-001`, `MOD-AFF-002` · **Registry:** [`REGISTRY.md`](REGISTRY.md)

## Triệu chứng

- `modules_statuses.json` có `"Affiliate": true`, module đã migrate — nhưng **không thấy menu/tính năng** trên company dashboard.

## Nguyên nhân

### 1. Cache `craveva_plugins` (bug vận hành)

- `craveva_plugins()` trong `app/Helper/start.php` cache danh sách module enable; **không** tự refresh sau `php artisan module:enable` hoặc sửa `modules_statuses.json` tay.
- Menu dùng `craveva_plugins()` → `@includeIf('affiliate::sections.sidebar')` (`menu.blade.php`, `setting-sidebar.blade.php`).
- **Đúng cách bật:** UI Custom Modules hoặc `packages:modules enable-custom` (controller/command có `cache()->forget` + rebuild).

### 2. Chỉ affiliate active mới thấy menu (thiết kế)

- `affiliate::sections.sidebar` bọc `@if (isAffiliate())` — cần bản ghi `affiliates` với `user_id` hiện tại, `status = active`.
- Admin test không có bản ghi affiliate → **không thấy menu** dù module OK.

## Fix

```bash
php artisan packages:modules enable-custom
# hoặc sau module:enable tay:
php artisan cache:forget craveva_plugins
```

- Kiểm tra user test: có affiliate active trong DB không.
- Tránh `module:enable` trực tiếp trên Craveva nếu không rebuild cache.

## Code tham chiếu

- Cache: `app/Helper/start.php` — `craveva_plugins()`
- Bật module UI: `CustomModuleController::update()`, `PackageModulesCommand::runEnableCustom()`
