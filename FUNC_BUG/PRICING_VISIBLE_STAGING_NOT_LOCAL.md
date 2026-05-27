# Pricing có trên staging, thiếu trên local

**Mã:** `MOD-PRICING-001` · **Registry:** [`REGISTRY.md`](REGISTRY.md)

## Triệu chứng

Cùng package có **Pricing**, staging thấy menu, local không.

## Nguyên nhân

1. **Chính:** Sửa package (`module_in_package`) **không** gọi `CompanyObserver::updateModuleSettings()` — chỉ chạy khi company **đổi** `package_id` hoặc lệnh `packages:modules activate`.
2. **Phụ:** Custom module Pricing (nwidart) tắt trên local; cache `user_modules_{id}` cũ.

## Fix (local)

```bash
php artisan packages:modules activate --module=pricing
php artisan packages:modules enable-custom   # nếu cần bật nwidart Pricing
php artisan cache:clear
```

Đăng nhập lại. Menu cần: `in_array('pricing', user_modules())` và `Route::has('pricing.tiers.index')`.

## Flow (tóm)

```
Sửa package (thêm pricing) → chỉ cập nhật packages.module_in_package
Đổi gói company HOẶC packages:modules activate → module_settings sync → user_modules cache
```

## Kiểm tra

- `php artisan packages:modules list` — package có `pricing` trong gói.
- Settings → Custom Modules → Pricing ON.
