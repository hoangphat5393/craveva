# Rà soát phương án B — `__construct` controller (Laravel 11)

**Mục đích:** Tránh lỗi khi **`php artisan route:list`** / CLI resolve controller (ví dụ lỗi decrypt **MAC invalid** nếu đọc model có cast `encrypted` trong constructor), và ghi nhận chỗ cần lưu ý.

**Stripe / QA:** Chỉ cần test trên **môi trường test** (`stripe_mode` = test, key `pk_test_` / `sk_test_`) — dùng `php artisan payment:stripe-verify` và xem **§7.7** trong `LARAVEL_11_UPGRADE_GUIDE.md`.

---

## 1. Đã an toàn / chỉ `parent` + `pageTitle` + middleware

- **`app/Http/Controllers/Payment/*`** (Stripe, Mollie, PayPal public, Paystack, …): constructor không đọc DB credential; Stripe đã tách sang **`configureStripeForInvoice()`** (xem guide §7.5).
- **`PaymentGatewayCredentialController`** (company & super-admin): `PaymentGatewayCredentials::first()` nằm trong **`index()`**, không trong `__construct`.

---

## 2. Đã chỉnh (tháng 3/2026)

| File                                        | Thay đổi                                                                                                                                              |
| ------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------- |
| `SuperAdmin/BillingController.php`          | Không query `GlobalPaymentGatewayCredentials` / không gọi `setStripConfigs()` khi **`app()->runningInConsole()`**; gán mặc định an toàn cho các flag. |
| `SuperAdmin/SuperAdminPaypalController.php` | Không đọc credential / không tạo PayPal **`ApiContext`** khi **`runningInConsole()`**.                                                                |

Request web vẫn khởi tạo đầy đủ như trước.

---

## 3. Theo dõi thêm (chưa bắt buộc sửa)

- **`Controller` / `AccountBaseController`:** middleware gọi `global_setting()`, `company()` — chạy khi **có HTTP request**, không phải rủi ro chính cho `route:list` như đọc Eloquent có `encrypted` trong constructor từng controller payment (đã xử lý Stripe).
- Các controller khác: nếu sau này thêm **DB / decrypt** vào `__construct`, nên làm theo hướng **lazy load trong action** hoặc **bỏ qua khi `runningInConsole()`** tùy nghiệp vụ.

---

_Tài liệu kèm: `docs/LARAVEL_11_UPGRADE_GUIDE.md` §7.5 (mục liên quan constructor)._
