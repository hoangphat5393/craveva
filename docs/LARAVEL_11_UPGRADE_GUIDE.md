# Hướng dẫn nâng cấp Laravel 10 → 11 (thực tế)

Tài liệu này tập trung vào **thay đổi gây vỡ** và **việc cần làm cụ thể**, không lặp lại toàn bộ changelog chính thức.

**Tham chiếu chính thức:** [Laravel 11.x Upgrade Guide](https://laravel.com/docs/11.x/upgrade)

---

## 1. Breaking changes quan trọng (ảnh hưởng thường gặp)

### 1.1 Framework & runtime

| Thay đổi                         | Tác động                                                                                                                          |
| -------------------------------- | --------------------------------------------------------------------------------------------------------------------------------- |
| **PHP ≥ 8.2**                    | Bắt buộc.                                                                                                                         |
| **Bỏ Doctrine DBAL khỏi core**   | `->change()` trong migration vẫn dùng được nhưng **mọi modifier không khai báo lại có thể bị mất** (unsigned, default, comment…). |
| **Sửa cột (`->change()`)**       | Phải **liệt kê đủ** thuộc tính cột cần giữ sau khi đổi; nếu không, Laravel 11 có thể làm mất default/index ngắm ngầm.             |
| **`double` / `float` migration** | Cú pháp cột đổi (bỏ `total`/`places` kiểu cũ, dùng `precision` nơi cần).                                                          |
| **Carbon 3**                     | Laravel 11 dùng Carbon 3; `diffIn*()` có thể trả về float/âm khác Carbon 2.                                                       |
| **SQLite**                       | Nếu dùng SQLite: yêu cầu phiên bản SQLite 3.26.0+.                                                                                |

### 1.2 Gói first-party (phải nâng cùng framework)

| Package                | Laravel 10                | Laravel 11                                                                                                                |
| ---------------------- | ------------------------- | ------------------------------------------------------------------------------------------------------------------------- |
| `laravel/sanctum`      | ^3.x                      | **^4.0** — publish migration nếu cần                                                                                      |
| `laravel/cashier`      | ^14.x                     | **^15.0** — publish `cashier-migrations`; Stripe SDK **^13** hoặc **^16.2** (tùy bản Cashier 15)                          |
| `laravel/fortify`      | **^1.36** (vd. `v1.36.1`) | Trên Packagist không có tag `v2` stable; **`^2.0` trong composer.json dễ kéo `dev-master`** — nên dùng **^1.36** cho L11. |
| `nunomaduro/collision` | ^7.x                      | **^8.1**                                                                                                                  |

### 1.3 Hành vi khác

- **Password rehash:** sau login có thể rehash; nếu cột mật khẩu không tên `password` → cấu hình `authPasswordName` hoặc tắt trong `config/hashing.php`.
- **Rate limiting:** `Limit` / một số throttles dùng **giây** thay vì phút; custom code tạo `new Limit(...)` cần rà lại.
- **Cache prefix:** prefix Redis/Memcached/DynamoDB **không** tự thêm `:` ở cuối như trước.
- **`Schema::getColumnType()`:** trả về kiểu thực tế, không còn tương đương DBAL type.

---

## 2. Cần cập nhật gì

### 2.1 `composer.json`

**Bắt buộc (tối thiểu):**

```json
"php": "^8.2",
"laravel/framework": "^11.0",
"nunomaduro/collision": "^8.1",
"laravel/sanctum": "^4.0"
```

**Thường phải tăng theo dự án ERP/CRM:**

- `laravel/cashier` → **^15.0** **và** nâng `stripe/stripe-php` **cùng lúc** — **không được** giữ `stripe/stripe-php ^7.66`:
    - Cashier **15.0–15.4.3** → `stripe/stripe-php` **^13.0**
    - Cashier **≥15.5** → `stripe/stripe-php` **^16.2**
    - Nếu chỉ đổi `laravel/cashier` lên ^15 mà vẫn `stripe/stripe-php ^7.66`, Composer báo conflict (đúng như log terminal).
- `laravel-notification-channels/telegram` → **^6.0** (cần `illuminate/*` ^11).
- `froiden/laravel-rest-api` → **^11.0** hoặc **^12.0** — **bắt buộc**, không được để `^10.0`:
    - **Lý do:** `froiden/laravel-rest-api` **10.0.x** khai báo `require laravel/framework` tối đa **10.x** (5.6…|10.\*). Nếu root đã `laravel/framework ^11.0` thì Composer báo **conflict** — không có cách “giữ ^10” và vẫn lên L11.
    - **Sửa:** đổi dòng trong `composer.json` thành `"froiden/laravel-rest-api": "^12.0"` hoặc `"^11.0"` (tùy bản Packagist bạn chọn), rồi `composer update -W`.
- `nwidart/laravel-modules` → **^11.0** (có thay đổi cấu hình / merge-plugin — xem tài liệu package v11).
- `tanmuhittin/laravel-google-translate` thường cần bản **^2.4** + `stichoza/google-translate-php` **^5.x**.

**Rủi ro thường gặp trong codebase lớn:**

- `mitchbred/entrust` + `macsidigital/laravel-zoom` → **xung đột Carbon 2 vs 3** (phải thay Entrust bằng `spatie/laravel-permission` hoặc gỡ/thay Zoom SDK).
- `paypal/rest-api-sdk-php` branch dev — kiểm tra tương thích L11.

**Sau khi sửa `composer.json`:** luôn chạy **`composer update -W`** trên nhánh riêng (không partial update khi lock còn L10).

**Ví dụ hai dòng tối thiểu phải đổi cùng `laravel/framework ^11` (tránh đúng lỗi đã gặp):**

```json
"froiden/laravel-rest-api": "^12.0",
"stripe/stripe-php": "^13.0",
```

(Sau đó chạy `composer update laravel/framework froiden/laravel-rest-api laravel/cashier stripe/stripe-php -W` hoặc `composer update -W` toàn phần.)

**`doctrine/dbal` (Craveva — không nên gỡ):** Laravel 11 **không** nhét DBAL vào core, nhưng project này **vẫn cần** package `doctrine/dbal` vì code đã dùng trực tiếp API Doctrine (xem **§2.1.1**). Gỡ sẽ làm lỗi migration / lệnh audit nếu chưa refactor.

---

#### 2.1.1 `doctrine/dbal` — có gỡ được không?

**Ghi chú chi tiết (danh sách file, checklist, ví dụ):** `docs/NOTE_DOCTRINE_DBAL_REMOVAL.md`

| Trong repo                                            | Mục đích                                                                                                                                                                                    |
| ----------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `Schema::getConnection()->getDoctrineSchemaManager()` | Liệt kê FK/index (drop trước khi `renameColumn`), v.v. — **~8 chỗ trong migration** (đã refactor `servermanager:db-audit` sang `getTableListing()` — không còn DBAL)                        |
| `$table->renameColumn(...)`                           | Nhiều file migration (đổi tên cột) — với MySQL, Laravel thường dùng driver native, nhưng **SQLite / một số thao tác** vẫn liên quan tài liệu DBAL; đừng gỡ khi chưa kiểm tra toàn bộ CI/DB. |
| `$table->...->change()`                               | Sửa kiểu cột — Laravel **khuyến nghị** cài `doctrine/dbal` cho `change()`.                                                                                                                  |

**Kết luận:** Giữ **`"doctrine/dbal": "^3.0"`** (lock hiện **3.10.x**). **Không gỡ** trừ khi:

1. Thay toàn bộ `getDoctrineSchemaManager()` bằng API Laravel / raw SQL tương đương (list FK, drop, v.v.).
2. Refactor `DatabaseAuditCommand` dùng `Schema::getConnection()->getSchemaBuilder()->getTableListing()` (hoặc truy vấn `information_schema` / `SHOW TABLES` tùy driver).
3. Chạy lại toàn bộ migration trên DB mục tiêu (MySQL/SQLite) sau khi gỡ — xác nhận không lỗi.

---

### 2.2 Config

- **Không bắt buộc** xóa toàn bộ `config/` để giống skeleton L11 — Laravel 11 **vẫn hỗ trợ** cấu trúc app kiểu L10.
- **Cần xem lại sau khi cài package:**
    - `config/hashing.php` — `rehash_on_login` nếu có vấn đề đăng nhập.
    - `config/cache.php` — key prefix nếu có logic phụ thuộc dấu `:`.
- **Publish migration (nếu dùng):**
    - `php artisan vendor:publish --tag=sanctum-migrations`
    - `php artisan vendor:publish --tag=cashier-migrations` (Cashier 15)

---

### 2.3 Middleware

- **Giữ `App\Http\Kernel`** — vẫn được hỗ trợ; không cần ngày đầu chuyển sang `bootstrap/app.php` kiểu Laravel 11 mới.
- **Kiểm tra:**
    - Middleware custom bắt `AuthenticationException` và gọi `redirectTo()` — signature L11 **yêu cầu `Request`**.
    - Provider tùy chỉnh implement `UserProvider` / model implement `Authenticatable` — thêm method mới nếu có (theo upgrade guide).
- **Các alias trong `$routeMiddleware` / `middlewareAliases`** — tên route middleware giữ nguyên trừ khi bạn đổi tên trong `bootstrap/app.php` (nếu migrate cấu trúc mới).

---

### 2.4 Routes

- `routes/web.php`, `routes/api.php` — **thường không đổi** cú pháp.
- **Module routes** (`nwidart/laravel-modules`): sau khi lên v11, chạy lệnh/cấu hình theo hướng dẫn package; kiểm tra `RouteServiceProvider` / load module.
- Route `signed`, `throttle`, `auth` — hành vi gần như cũ; chỉ rà throttle tùy chỉnh (giây vs phút).

---

## 3. PHP — tương thích

| Yêu cầu                              | Ghi chú                                                            |
| ------------------------------------ | ------------------------------------------------------------------ |
| `laravel/framework` ^11              | **PHP ≥ 8.2**                                                      |
| PHP 8.3 / 8.4                        | Tương thích L11 (kiểm tra từng extension: `ext-*` trong composer). |
| Một số package (vd. bản Entrust mới) | Có thể yêu cầu PHP 8.3+ — đọc constraint trên Packagist.           |

Chạy: `php -v` và `composer check-platform-reqs` sau khi cập nhật `composer.json`.

---

## 4. Kế hoạch nâng cấp từng bước (thực tế)

### Bước 0 — Chuẩn bị

1. Branch `upgrade/laravel-11`, backup DB.
2. Đảm bảo test suite / checklist thủ công (đăng nhập, đổi công ty, 1 PO/DO, 1 import nhỏ).

### Bước 1 — Gỡ dependency kẹt

1. Giải quyết **Entrust vs Zoom vs Carbon** (hoặc bỏ một trong hai).
2. Cập nhật `composer.json` theo mục 2.1 (Cashier, Stripe, Telegram, Froiden REST API, modules, v.v.).

### Bước 2 — Composer

```bash
composer update -W
```

Nếu lỗi: `composer why-not illuminate/contracts 11.0` để tìm package còn kẹt L10.

### Bước 3 — Migrations

- **Tùy chọn an toàn cho DB đã production:** `php artisan schema:dump` rồi chỉ chạy migration mới; **hoặc**
- Rà **từng migration có `->change()`** và bổ sung đủ modifier theo [upgrade guide](https://laravel.com/docs/11.x/upgrade#modifying-columns).

### Bước 4 — App

```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear
```

Publish migration Sanctum/Cashier nếu cần; chạy `migrate` trên staging.

### Bước 5 — Kiểm chứng

- Stripe / Cashier (subscription, invoice).
- API dùng `froiden/laravel-rest-api` / `BaseModel`.
- Queue worker, import lớn.
- Đa tenant: `company_id` trên route thay đổi.

### Bước 6 — Deploy

- Staging trước; rollback plan; giám sát log 24–48h.

---

## 5. Lỗi Composer thường gặp (mapping với log thực tế)

### Problem 1 — `froiden/laravel-rest-api` vs `laravel/framework ^11.0`

**Thông báo kiểu:**  
`froiden/laravel-rest-api[10.0.x] require laravel/framework ...|10.*` **but it conflicts** with `require (^11.0)`.

**Nguyên nhân:** Constraint trong `composer.json` vẫn là `froiden/laravel-rest-api ^10.0`. Bản 10.x **không** hỗ trợ Laravel 11.

**Cách xử lý:** Đổi sang **`^11.0` hoặc `^12.0`** (kiểm tra trên Packagist phiên bản ổn định), không giữ `^10.0`. Sau khi nâng, **test toàn bộ** model kế thừa `ApiModel` và route `ApiRoute`.

---

### Problem 2 — `laravel/cashier ^15` vs `stripe/stripe-php ^7.66`

**Thông báo kiểu:**  
Cashier 15.x require `stripe/stripe-php ^13.0` (hoặc `^16.2` bản Cashier mới) **but it conflicts** with root `stripe/stripe-php ^7.66`.

**Nguyên nhân:** Cashier 15 gắn với Stripe PHP SDK **13+** (hoặc **16+**). Giữ SDK **7.x** là không tương thích.

**Cách xử lý:**

1. Trong `composer.json` đổi: `"stripe/stripe-php": "^13.0"` (hoặc `^16.2` nếu dùng Cashier ≥15.5 và composer yêu cầu).
2. Chạy `composer update stripe/stripe-php laravel/cashier -W`.
3. **Hậu kiểm:** rà code gọi trực tiếp `\Stripe\Customer::create`, `\Stripe\PaymentIntent::create`, v.v. — SDK 7→13 là nhảy major, cần test Stripe **test mode**.

---

### Ghi nhớ

- Hai lỗi trên **không** do thiếu `-W` một mình — cần **sửa constraint trong `composer.json`** trước.
- Sau khi sửa đủ dependency, vẫn nên dùng `composer update -W` để Composer được phép nâng/hạ các gói phụ thuộc.

---

## 6. Tham chiếu nhanh — file trong repo Craveva

Cần đặc biệt test sau upgrade:

- `app/Models/Company.php` + `AppServiceProvider` (Cashier).
- `app/Http/Controllers/SuperAdmin/BillingController.php` — Stripe/Cashier.
- `OrderController`, `HomeController`, `InvoiceController` — `\Stripe\...` trực tiếp (SDK 7 → 13).
- `Modules/Sms/Notifications/*` — Telegram channel.
- `app/Models/BaseModel.php` + mọi model extends `ApiModel` — Froiden REST API.
- Migration có `->change()` (nhiều file trong `database/migrations` và `Modules/**/Migrations`).

---

## 7. Nhật ký nâng cấp Craveva (đã thực hiện trong repo)

Phần này ghi lại **phiên bản đã cài** (theo `composer.lock` tại thời điểm nâng cấp) và **các thay đổi code/cấu hình** đã làm — để team biết đã đổi gì và cần kiểm tra gì tiếp.

### 7.1 Framework & runtime

| Mục                         | Giá trị / ghi chú                                      |
| --------------------------- | ------------------------------------------------------ |
| `laravel/framework`         | **v11.50.0**                                           |
| `php` trong `composer.json` | **^8.3** (L11 yêu cầu tối thiểu 8.2; dự án chọn 8.3)   |
| `nesbot/carbon`             | **3.11.3** (Carbon 3 — cần rà code phụ thuộc Carbon 2) |

### 7.2 Gói first-party & tích hợp chính

| Package                                  | Phiên bản lock                     | Ghi chú                                                                            |
| ---------------------------------------- | ---------------------------------- | ---------------------------------------------------------------------------------- |
| `laravel/sanctum`                        | v4.3.1                             | Không còn `Sanctum::ignoreMigrations()` — migration publish khi cần                |
| `laravel/cashier`                        | v15.4.3                            | Không còn `Cashier::ignoreMigrations()` — xem mục 7.4                              |
| `stripe/stripe-php`                      | v13.18.0                           | Nâng từ SDK 7.x — **bắt buộc test** mọi chỗ gọi Stripe trực tiếp                   |
| `laravel/fortify`                        | **v1.36.1** (constraint **^1.36**) | Đã ghim stable; tránh `^2.0` (Composer kéo `dev-master` vì chưa có tag v2 stable). |
| `laravel-notification-channels/telegram` | 6.0.0                              | Bản ^4 kẹt `illuminate/*` ^10 — phải lên ^6 cho L11                                |
| `froiden/laravel-rest-api`               | 12.0.0                             | Thay thế ^10 không tương thích L11                                                 |

### 7.3 Gói bên thứ ba (ERP / tích hợp)

| Package                                | Phiên bản lock     | Ghi chú                                                                         |
| -------------------------------------- | ------------------ | ------------------------------------------------------------------------------- |
| `nwidart/laravel-modules`              | v11.1.10           | Cần `wikimedia/composer-merge-plugin` + cập nhật `config/modules.php` (mục 7.4) |
| `yajra/laravel-datatables-oracle`      | v11.1.6            | Cùng nhánh ^11 với `laravel-datatables-html`, `laravel-datatables-buttons`      |
| `webklex/laravel-imap`                 | 6.2.0              | Kèm `webklex/php-imap` 6.x — hỗ trợ Carbon 2 hoặc 3                             |
| `mollie/laravel-mollie`                | v3.1.0             | Thay v2 (Illuminate ^9\|^10) — **API có thể khác**, test thanh toán Mollie      |
| `mitchbred/entrust`                    | **2.5.5.1** (path) | Fork cục bộ trong `packages/mitchbred-entrust-l11` — xem 7.5                    |
| `macsidigital/laravel-oauth2-client`   | 2.1.1 (path)       | Fork cục bộ — xem 7.5                                                           |
| `macsidigital/laravel-api-client`      | 5.0.2 (path)       | Fork cục bộ — xem 7.5                                                           |
| `macsidigital/laravel-zoom`            | 8.0.2 (path)       | Fork cục bộ — xem 7.5                                                           |
| `tanmuhittin/laravel-google-translate` | (constraint ^2.4)  | Thường đi kèm `stichoza/google-translate-php` ^5                                |
| `phpro/grumphp`                        | v2.19.0            | Tương thích Symfony 7 (L11); v1 kẹt Symfony 5/6                                 |

### 7.4 Công cụ dev & test

| Package                       | Phiên bản lock |
| ----------------------------- | -------------- |
| `pestphp/pest`                | v3.8.6         |
| `pestphp/pest-plugin-laravel` | v3.2.0         |
| `phpunit/phpunit`             | 11.5.50        |
| `nunomaduro/collision`        | v8.9.1         |

### 7.5 Việc đã làm (code & cấu hình)

1. **`composer.json`**
    - Nâng các constraint tương thích L11 (Telegram ^6, Yajra ^11, Fortify **^1.36** (stable `v1.36.1`) — không dùng `^2.0` vì trên Packagist chưa có tag `v2` stable, Composer dễ kéo `dev-master`), Mollie ^3.1, v.v.).
    - Thêm **repository `path`** cho: `packages/mitchbred-entrust-l11`, `packages/macsidigital-laravel-oauth2-client`, `packages/macsidigital-laravel-api-client`, `packages/macsidigital-laravel-zoom`.
    - Bật plugin: `"wikimedia/composer-merge-plugin": true` (cần cho `nwidart/laravel-modules` v11).

2. **`app/Providers/AppServiceProvider.php`**
    - Xóa `Cashier::ignoreMigrations()` và `Sanctum::ignoreMigrations()` (Cashier 15 / Sanctum 4 không còn API này; migration dùng publish khi cần).

3. **`config/modules.php`**
    - Chuyển sang đăng ký lệnh kiểu **v11**: `ConsoleServiceProvider::defaultCommands()->merge([...])->toArray()` — bỏ danh sách `Commands\CommandMakeCommand::class`, … (một số class không còn tồn tại trong package mới).

4. **Fork cục bộ `mitchbred/entrust` (2.5.5.1)**
    - **Lý do:** Bản Packagist 2.5.5 khai báo `symfony/string ^6.3` trong khi `symfony/console ^7` (L11/Pest) cần `symfony/string ^7` → xung đột Composer.
    - **Đã sửa trong `packages/mitchbred-entrust-l11/composer.json`:** `symfony/string` → `^7.0`, `symfony/translation` → `^6.3|^7.0`, thêm `version` **2.5.5.1**.

5. **Fork cục bộ chuỗi MacsiDigital (Zoom)**
    - **Lý do:** Upstream chỉ khai báo `illuminate/support` tới ^10 — không cài được cùng `laravel/framework` ^11.
    - **Đã nới** `illuminate/support` (và Carbon nơi cần) trong `packages/macsidigital-laravel-*` với version **2.1.1 / 5.0.2 / 8.0.2**.

6. **`app/Http/Kernel.php`**
    - Gộp **toàn bộ** alias middleware vào **`$middlewareAliases`** (Laravel 11 khuyến nghị); bỏ trùng `$routeMiddleware` + `$middlewareAliases` (chỉ còn một mảng alias).

7. **`app/Http/Controllers/Payment/StripeController.php`**
    - **`route:list`** khởi tạo controller để đọc middleware → constructor **không** được gọi DB / đọc cast `encrypted` trên `PaymentGatewayCredentials`.
    - **Đã refactor (bước tiếp theo):** constructor chỉ còn `parent::__construct()` + `pageTitle`. Cấu hình Stripe chuyển vào **`configureStripeForInvoice(Invoice $invoice)`** trong từng action:
        - Ưu tiên `company.paymentGatewayCredentials` (đúng multi-tenant hơn `PaymentGatewayCredentials::first()`).
        - Fallback **`Config::get('cashier.secret')`** (đã set bởi `CustomConfigProvider`).
        - Dùng Facade: **`Config`**, **`Log`**, **`URL::temporarySignedRoute`** thay cho `url()->…` nơi có thể.

8. **Providers cấu hình DB (`CustomConfigProvider`, `SmtpConfigProvider`, `FileStorageCustomConfigProvider`)**
    - **Đã đổi** `catch (\Exception $e)` → **`catch (\Throwable $e)`** (và thêm kiểm tra `$setting === null` trước `switch` khi đọc file storage) để lỗi giải mã không làm vỡ `artisan` khi bootstrap.

9. **Sau khi clone / deploy**
    - Chạy `composer install` (hoặc `composer update -W` trên nhánh upgrade).
    - Xóa cache config/route/view nếu cần: `php artisan optimize:clear`.
    - **`php artisan route:list`** — đã chạy được sau khi sửa `StripeController`; nếu vẫn lỗi MAC ở chỗ khác: rà controller khác có **side effect trong `__construct`** + đồng bộ **APP_KEY** với DB.

10. **`app/Providers/FileStorageCustomConfigProvider.php` (bước tiếp theo — Facade)**
    - Thay helper `config([...])` → **`Config::set('key', $value)`** từng mục.
    - Thay `app()->environment('demo')` kiểu `in_array(...)` → **`App::environment('demo')`**.
    - Giữ **`Crypt`**, **`DB`** facade như cũ.

11. **Kiểm chứng tự động**
    - `php artisan route:list` — ~3288 route, exit code 0 (không còn lỗi MAC do constructor).
    - `pest` (full suite) — **22 passed**, 1 skipped (LoginTest inactive user), ~352 assertions — chạy OK sau upgrade.

12. **Lệnh kiểm tra test vs live trước khi QA thanh toán**
    - `php artisan payment:stripe-verify` — hiển thị `stripe_mode` trong DB (`global_payment_gateway_credentials`), nhận diện **`pk_test_` / `pk_live_`**, **`sk_test_` / `sk_live_`**, và key Mollie **`test_` / `live_`** (mask key, không in full secret).
    - Cảnh báo nếu **DB `stripe_mode` không khớp** prefix key (ví dụ DB = test nhưng key live).

13. **`phpunit.xml` (PHPUnit 11)** — chạy `./vendor/bin/phpunit --migrate-configuration` để bỏ schema deprecated; sinh **`phpunit.xml.bak`**, thêm **`cacheDirectory=".phpunit.cache"`**, `<coverage>` → `<source>` (theo PHPUnit 11). Thêm **`.phpunit.cache/`** + **`phpunit.xml.bak`** vào `.gitignore`.

14. **`servermanager:db-audit`** — `Modules/ServerManager/Console/DatabaseAuditCommand.php` dùng **`Schema::getConnection()->getSchemaBuilder()->getTableListing()`** thay `getDoctrineSchemaManager()` (giảm phụ thuộc DBAL cho lệnh audit; migration vẫn cần `doctrine/dbal` — xem `docs/NOTE_DOCTRINE_DBAL_REMOVAL.md`).

15. **Phương án B — constructor Super Admin (CLI)** — `SuperAdmin/BillingController` và `SuperAdmin/SuperAdminPaypalController`: khi **`app()->runningInConsole()`** thì **không** query `GlobalPaymentGatewayCredentials` / không **`setStripConfigs()`** / không tạo PayPal **`ApiContext`** (tránh tác dụng phụ khi Artisan). Chi tiết: **`docs/NOTE_CONTROLLER_CONSTRUCTOR_AUDIT.md`**.

16. **PHPUnit 12 — bỏ `/** @test */` trong doc-comment** — thay bằng attribute **`#[Test]`** + `use PHPUnit\Framework\Attributes\Test;`(vd.`tests/Feature/ChatboxToggleTest.php`, `FuncNewsTest.php`) để hết cảnh báo *Metadata in doc-comments is deprecated\*.

17. **CI — chạy Pest** — khung lệnh: **`docs/CI_PEST.md`**. **Triển khai an toàn (manual only, không đụng Staging):** **`docs/PROCEDURE_CI_PEST_SAFE.md`** + **`.github/workflows/pest-mysql-manual.yml`**.

18. **Migration `double('col', …)` → `decimal`** — Laravel 11 **`Blueprint::double($column)`** chỉ nhận **một** tham số. Đã đổi `->double('…', n, m)`, `->double('…', [n,m])`, `->double('…', n)` thành **`->decimal(…, n, m)`** trong **`database/migrations`** và **`Modules/*/Database/Migrations`**. Script **`scripts/fix_migration_double_to_decimal.php`** xử lý các pattern trên; chạy lại không đổi thêm nếu đã xử lý xong. File sửa thủ công: `2022_10_03_…_create_super_admin_tables` (sub_total/total `[15,2]`), `Modules/Payroll/…/2024_07_05_110403_add_hourly_rate_column` (time/fixed_amount).

19. **`getDoctrineSchemaManager()` trong migration** — Laravel 11 **gỡ** method này trên `Connection`. Đã thay bằng **`getSchemaBuilder()->getForeignKeys()`** / **`getIndexes()`** (và **`Schema::hasIndex`** nơi đã làm trước đó) trong các file: `fix_bug`, `fix_invoice_units`, `2018_02_01…_saas_upgrade_fix`, Recruit `create_recruit_salary_structure`, Pricing `2026_01_30`, `2026_02_11`.

### 7.6 Việc nên làm tiếp (checklist)

- [x] ~~Ghim **`laravel/fortify`** bản stable (đã dùng **^1.36** → `v1.36.1`)~~
- [x] ~~Xác nhận **`php artisan route:list`** — đã OK sau khi sửa `StripeController`~~
- [ ] Test **Stripe/Cashier** (subscription, webhook, invoice) sau SDK 13 — **trước khi test**: `php artisan payment:stripe-verify` + xem **§7.7**.
- [ ] Test **Mollie** (luồng thanh toán, redirect) — ưu tiên key `test_…`; xem **§7.7**.
- [ ] Test **IMAP** (đọc mail) sau `webklex` 6.x.
- [ ] Test **Zoom** (module Recruit/Zoom) sau fork MacsiDigital.
- [ ] Rà **Entrust** (role/permission, middleware) sau Carbon 3 và fork Symfony.
- [x] ~~Chạy full suite Pest (`pest --no-coverage`) — 22 passed, 1 skipped~~
- [x] ~~Rà `__construct` payment controllers (Paypal/Square/Authorize/… chỉ `pageTitle`; `route:list` ~3288 route OK)~~
- [x] ~~Migrate `phpunit.xml` schema PHPUnit 11 (`phpunit --migrate-configuration`) + `.gitignore` `.phpunit.cache/`, `phpunit.xml.bak`~~
- [x] ~~`servermanager:db-audit` bỏ `getDoctrineSchemaManager()` (dùng `getTableListing()`)~~
- [x] ~~Phương án B: `BillingController` + `SuperAdminPaypalController` bỏ tải credential khi `runningInConsole()` — xem `docs/NOTE_CONTROLLER_CONSTRUCTOR_AUDIT.md`~~
- [x] ~~Chuẩn bị PHPUnit 12: `ChatboxToggleTest` + `FuncNewsTest` dùng `#[Test]` thay `/** @test */`~~
- [ ] (Tùy chọn) Trên GitHub: chạy workflow **“Pest (MySQL, manual only)”** theo **`docs/PROCEDURE_CI_PEST_SAFE.md`** — không bắt buộc, không ảnh hưởng Staging nếu chỉ dùng như hướng dẫn.
- [x] ~~Migration: batch `double(col,n,m)` → `decimal` (L11) — **`scripts/fix_migration_double_to_decimal.php`** + §7.5 mục 18~~
- [x] ~~Migration: bỏ `getDoctrineSchemaManager()` → `getForeignKeys` / `getIndexes` (L11) — §7.5 mục 19~~
- [x] ~~Ghi chú migration / gộp file / schema dump — §7.8~~
- [ ] Sau deploy L11: **đóng gói** theo **§7.9** (migrate, cache, queue restart, QA) — tùy môi trường.

### 7.7 QA thanh toán an toàn (không mất tiền nhầm)

**Nguyên tắc**

1. **Luôn kiểm tra môi trường trước khi bấm thanh toán**
    - Chạy: `php artisan payment:stripe-verify`
    - Trong Admin (Super Admin / Payment gateway): xem **`stripe_mode`** = **test** hay **live** (`global_payment_gateway_credentials`).
    - Với **từng công ty** (multi-tenant): trong **Payment gateway** của company đó, xác nhận `stripe_mode` và key test/live — giao dịch invoice/order dùng credential **company**, không chỉ global.

2. **Stripe**
    - **Test (không tiền thật):** publishable `pk_test_…`, secret `sk_test_…`, thẻ test Stripe (vd. `4242424242424242`).
    - **Live (tiền thật):** `pk_live_…`, `sk_live_…` — mọi giao dịch thật.
    - Nếu **bắt buộc** thử trên live: dùng **số tiền tối thiểu** có ý nghĩa (vd. đơn vị nhỏ nhất), một người duyệt, và quy trình hoàn tiền nếu cần.

3. **Mollie**
    - Key bắt đầu **`test_`** = môi trường test; **`live_`** = thật (trừ tiền thật). Lệnh trên gợi ý nhanh từ key đang cấu hình.

4. **Zoom / IMAP**
    - **Zoom:** OAuth/API — không “trừ tiền” theo từng lần bấm như Stripe; chủ yếu kiểm tra kết nối/quota tài khoản developer.
    - **IMAP (webklex):** không liên quan thanh toán — chỉ kiểm tra đọc mail/sync.

5. **Staging**
    - Ưu tiên **staging** + key **test/sandbox**; tách biệt **`.env`** khỏi production để tránh nhầm `STRIPE_SECRET` / `MOLLIE_KEY` live.

### 7.8 Migration — server đã có DB, có nên “gộp” file?

**Thực tế:**

- Trên **Staging / Hub** (DB đã chạy migrate trước đó): Laravel ghi trong bảng **`migrations`** — migration cũ **không chạy lại** mỗi lần deploy. File migration **chủ yếu** cần cho: **môi trường mới**, dev clone, **CI**, **restore DB trống**, hoặc **`migrate:fresh`** (chỉ dev/test).

**Không nên:**

- **Xóa / gộp tay** hàng loạt file migration cho “gọn” — dễ làm **lệch schema** giữa môi trường mới và cũ nếu không có quy trình.

**Hướng xử lý chính thức của Laravel (khi muốn cài mới nhanh hơn):**

- **Squashing / schema dump:** [Squashing migrations](https://laravel.com/docs/11.x/migrations#squashing-migrations) — ví dụ `php artisan schema:dump` (MySQL) tạo file schema SQL, kết hợp với chiến lược team (thường làm trên nhánh riêng + test `migrate` trên DB trống).

**ERP lớn:** gộp migration là **dự án có kế hoạch**, không bắt buộc để “xong” L11 — có thể **giữ nguyên** lịch sử file như hiện tại.

### 7.9 Đóng gói sau upgrade L11 (trước / sau deploy)

1. **Code:** `composer install` (production: `--no-dev --optimize-autoloader` nếu policy cho phép).
2. **DB:** `php artisan migrate --force` (chỉ khi có migration mới — xem trước `migrate:status`).
3. **Cache (khi ổn định, không bật khi đang debug):** `php artisan optimize:clear` rồi `php artisan config:cache`, `route:cache`, `view:cache` — **nếu** lệnh không lỗi (một số app cần cấu hình đầy đủ).
4. **Queue / Horizon:** nếu dùng queue — `php artisan queue:restart` sau deploy.
5. **QA:** `php artisan payment:stripe-verify` + thử nghiệm nghiệp vụ (§7.7).

---

**Người dùng không chuyên kỹ thuật:** xem **`docs/LARAVEL_11_NGUOI_DUNG_KHONG_KY_THUAT.md`**.

---

_Tài liệu: `docs/LARAVEL_11_UPGRADE_GUIDE.md` — gồm **§7.7–7.9**, **QA thanh toán**, và lệnh **`payment:stripe-verify`**._
