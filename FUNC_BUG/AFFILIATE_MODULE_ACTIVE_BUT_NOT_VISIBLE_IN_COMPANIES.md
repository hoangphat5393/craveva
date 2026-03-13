## Mô tả vấn đề

- **Triệu chứng**: Đã cài và active custom module `Affiliate` (trong `storage/app/modules_statuses.json` có `"Affiliate": true`, file cache `bootstrap/cache/affiliate_module.php` đã sinh ra), nhưng khi đăng nhập vào các company (account dashboard) thì **không thấy menu / tính năng Affiliate xuất hiện**, khiến tưởng như module không hoạt động.

## Phân tích code liên quan

- **Cấu hình custom modules**
    - `config/modules.php` cấu hình `FileActivator` với `statuses-file => storage_path('app/modules_statuses.json')`.
    - File trạng thái hiện tại:

```json
{ "Affiliate": true }
```

- **Danh sách plugin custom dùng cho giao diện**
    - Helper `craveva_plugins()` trong `app/Helper/start.php`:
        - Lần đầu gọi sẽ lấy danh sách module enable từ `\Nwidart\Modules\Facades\Module::allEnabled()` rồi cache vào key `craveva_plugins`.
        - Các lần sau **chỉ đọc từ cache**, KHÔNG tự reload lại sau khi bật/tắt module.
    - Nhiều view dùng helper này để render menu custom modules:
        - `resources/views/sections/menu.blade.php`
        - `resources/views/components/setting-sidebar.blade.php`
        - `resources/views/components/super-admin/setting-sidebar.blade.php`
    - Ví dụ trong `menu.blade.php`:

```php
@if (checkCompanyPackageIsValid(user()->company_id))
    @foreach (craveva_plugins() as $item)
        @includeIf(strtolower($item) . '::sections.sidebar')
    @endforeach
@endif
```

- **Luồng bật/tắt custom module qua UI**
    - `CustomModuleController::update($moduleName)`:
        - Gọi `$module->enable()` hoặc `$module->disable()` (Nwidart).
        - Sau khi enable:
            - Chạy migrate cho module, chạy lệnh `{$moduleName}:activate` nếu có.
        - Luôn:
            - Lấy lại tất cả module enable: `$plugins = Module::allEnabled()`.
            - `cache()->forget('user_modules');`
            - `cache(['craveva_plugins' => array_keys($plugins)]);`
            - Gọi `flushData()` → `optimize:clear`, `view:clear`, `cache()->flush()`, reset session và login lại.
        - Kết quả: cache `craveva_plugins` luôn được **cập nhật đúng** theo trạng thái module.

- **Luồng bật custom modules bằng lệnh riêng**
    - `App\Console\Commands\PackageModulesCommand::runEnableCustom()`:
        - Loop qua `NwidartModule::toCollection()` và enable toàn bộ module có method `enable()`.
        - Sau khi bật:

```php
cache()->forget('craveva_plugins');
cache()->forget('user_modules');
cache()->put('craveva_plugins', array_keys(NwidartModule::allEnabled()));
```

- **Trường hợp bật module bằng lệnh chung của Nwidart**
    - Nếu dùng trực tiếp `php artisan module:enable Affiliate` (hoặc tự sửa `modules_statuses.json`), theo code hiện tại:
        - Nwidart sẽ update file `modules_statuses.json` và service provider/route được load.
        - **Nhưng** không có chỗ nào trong core gọi `cache()->forget('craveva_plugins')` hay rebuild lại cache.
    - Do helper:

```php
function craveva_plugins()
{
    if (! cache()->has('craveva_plugins')) {
        $plugins = \Nwidart\Modules\Facades\Module::allEnabled();
        cache(['craveva_plugins' => array_keys($plugins)]);
    }

    return cache('craveva_plugins');
}
```

- Nếu cache `craveva_plugins` **đã được khởi tạo trước khi bật Affiliate** (ví dụ lúc đó chưa có custom module nào → cache là `[]`), thì sau khi chạy `module:enable`:
    - `craveva_plugins()` vẫn trả về giá trị cũ trong cache (không chứa `Affiliate`).
    - Các view `menu.blade.php`, `setting-sidebar.blade.php`, v.v. không include được `affiliate::sections.*`.
    - Trên giao diện các company sẽ không thấy menu/tính năng Affiliate, mặc dù module đã enable và migrations đã chạy.

- **Lưu ý thêm về điều kiện hiển thị sidebar Affiliate**
    - File `Modules/Affiliate/Resources/views/sections/sidebar.blade.php`:

```php
@if (isAffiliate())
    <x-menu-item ... :link="route('affiliate-dashboard.index')">...</x-menu-item>
@endif
```

- Hàm `isAffiliate()` trong `Modules/Affiliate/start.php` chỉ trả về `true` khi:
    - Người dùng hiện tại có bản ghi `Modules\Affiliate\Entities\Affiliate` với `user_id = user()->id` và `status = 'active'`.
- Nghĩa là:
    - Kể cả khi cache `craveva_plugins` đã đúng, menu Affiliate ở company dashboard **chỉ hiện với user là affiliate active**; admin bình thường không tự động thấy menu này nếu chưa tạo Affiliate record/assign.

## Kết luận nguyên nhân

1. **Nguyên nhân chính (cache)**
    - Module `Affiliate` đã được enable (file `modules_statuses.json` và service provider đều OK), nhưng **cache `craveva_plugins` vẫn giữ giá trị cũ không có Affiliate** nếu module được bật bằng lệnh chung `module:enable` hoặc thao tác ngoài `CustomModuleController` / `packages:modules enable-custom`.
    - Các view menu/setting dùng `craveva_plugins()` để include sidebar cho custom modules, nên khi cache không cập nhật thì cả superadmin lẫn company đều **không thấy module Affiliate trong giao diện**, dù module đã active.

2. **Điều kiện bổ sung (per-user Affiliate)**
    - Ở company dashboard, ngay cả khi cache đã đúng và `affiliate::sections.sidebar` được include, menu chỉ hiển thị nếu user là **affiliate active** (`isAffiliate() === true`).
    - Nếu đang test bằng admin bình thường chưa được tạo Affiliate record thì cũng sẽ thấy trạng thái "module đã bật nhưng không thấy menu".

## Hướng xử lý / khuyến nghị

- **Cách khắc phục ngay khi gặp lỗi**
    - Chạy một trong các lệnh sau (ưu tiên dùng cách chính thức):
        - `php artisan packages:modules enable-custom`
            - Bật toàn bộ custom modules, đồng thời rebuild `modules_statuses.json` và `craveva_plugins`.
        - Hoặc bật/tắt module qua UI: `Settings > Module Settings > Custom Modules` (controller `CustomModuleController::update()` sẽ tự clear & rebuild cache).
    - Hoặc, nếu buộc phải dùng `module:enable`, sau đó nên chạy:
        - `php artisan cache:forget craveva_plugins`
        - (hoặc `php artisan cache:clear`) để `craveva_plugins()` được build lại.

- **Đề xuất cải tiến code (giảm nguy cơ tái diễn)**
    - Thêm tài liệu nội bộ nêu rõ: **không nên** dùng trực tiếp `php artisan module:enable` trên môi trường Craveva; hãy dùng:
        - UI `Custom Modules` **hoặc**
        - lệnh `php artisan packages:modules enable-custom`.
    - (Option) Tạo 1 command wrapper `modules:enable-safe`:
        - Gọi nội bộ `module:enable {name}`.
        - Sau đó thực hiện `cache()->forget('craveva_plugins'); cache(['craveva_plugins' => array_keys(Module::allEnabled())]);`.

- **Ghi chú khi kiểm tra ở từng company**
    - Sau khi đảm bảo cache đã đúng:
        - Kiểm tra user đang login có phải affiliate active hay không:
            - Có bản ghi trong bảng `affiliates` với `user_id = current user` và `status = 'active'`.
        - Nếu không có, admin sẽ **không thấy** menu Affiliate mặc dù module đã bật → đây là behavior thiết kế chứ không phải bug.
