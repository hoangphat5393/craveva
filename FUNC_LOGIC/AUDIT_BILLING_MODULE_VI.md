# Audit Billing (SaaS / thanh toán gói)

**Phạm vi:** Luồng billing **không** nằm trong `Modules/` — chủ yếu `app/Http/Controllers/SuperAdmin/` (Billing, gateway, webhook), route `routes/SuperAdmin/web.php`, `routes/SuperAdmin/web-public.php`, `routes/web-public.php`, model `Company` (Cashier `Billable`), `GlobalSubscription` / `GlobalInvoice` / `Package`, cài đặt cổng trong `PaymentGatewayCredentialController`.  
**Ngày audit:** 2026-04-08

---

## 1. Tổng quan

| Hạng mục                           | Giá trị                                                                                                                        |
| ---------------------------------- | ------------------------------------------------------------------------------------------------------------------------------ |
| **Module nwidart**                 | Không có module `Billing` riêng                                                                                                |
| **Controller trung tâm**           | `BillingController` (Stripe/Cashier, gói, offline, lifetime, …)                                                                |
| **PayPal (flow riêng)**            | `SuperAdminPaypalController`, `PaypalIPNController`                                                                            |
| **Cashier**                        | `Company` dùng trait `Laravel\Cashier\Billable`                                                                                |
| **Hóa đơn SaaS**                   | `GlobalInvoice`, `GlobalSubscription`; siêu admin xem qua `InvoiceController` + DataTable                                      |
| **Tests trong repo (trước audit)** | Không có test dedicated cho billing                                                                                            |
| **Tests sau chỉnh sửa**            | `tests/Feature/BillingWebhookRoutesTest.php` — kiểm tra route webhook trỏ tới method tồn tại + tên route download offline plan |

---

## 2. Route chính

### 2.1. Nhóm `account/settings` (middleware `auth`, `multi-company-select`)

Prefix thực tế: `account/settings/...` (xem `routes/SuperAdmin/web.php`).

- Trang billing: `GET .../billing` → `billing.index`
- Gói / nâng cấp: `billing.upgrade_plan`, `billing.packages`, `billing.select-package`, …
- Stripe: `billing.stripe-validate`, `billing.stripe`, `billing.stripeNew`, …
- PayPal: `billing.paypal-payment`, `billing.paypal-recurring` (action `payWithPaypalRecurrring` — typo chữ **r**), `billing.paywithpaypal`, …
- Razorpay / Paystack / Mollie / Authorize / PayFast / offline / free / lifetime: các route tương ứng trong cùng nhóm

**Phân quyền UI:** `BillingController@index` dùng `user()->permission('manage_billing')` và `abort_403` theo vai trò (chặn client, employee không admin, …). Super admin: middleware trong constructor gọi `GlobalSetting::validateSuperAdmin('manage_billing')`.

### 2.2. Webhook / callback (không nằm trong nhóm `auth` ở cuối file)

Đăng ký trực tiếp trên `web.php` (ngoài prefix `account/settings`):

| Tên route                                            | HTTP                               | Controller (khái quát)                    |
| ---------------------------------------------------- | ---------------------------------- | ----------------------------------------- |
| `billing.save_webhook`                               | POST `save-invoices`               | `StripeWebhookController`                 |
| `billing.verify-webhook`                             | POST `billing-verify-webhook/{id}` | `StripeWebhookController`                 |
| `billing.save_razorpay-webhook`                      | POST                               | `RazorpayWebhookController@saveInvoices`  |
| `billing.save_paystack-webhook`                      | POST                               | `PaystackWebhookController@saveInvoices`  |
| `billing.save_paypal-webhook`                        | POST                               | `PaypalIPNController`                     |
| `billing.save_authorize-webhook`                     | POST                               | `AuthorizeWebhookController@saveInvoices` |
| `billing.mollie.callback` / `billing.mollie.webhook` | GET/POST                           | `MollieController`                        |
| `payfast-notification`                               | POST                               | `PayFastWebhookController`                |
| `billing.paystack.callback`                          | GET                                | `PaystackController`                      |

**Stripe (public thêm):** `routes/web-public.php` — `stripe.webhook`, `get_stripe_webhook` trỏ `StripeWebhookController`.

### 2.3. IPN PayPal công khai

- `routes/SuperAdmin/web-public.php`: `POST verify-billing-ipn` → `PaypalIPNController@verifyBillingIPN` (`verify-billing-ipn`).

---

## 3. CSRF & bảo mật webhook

- `VerifyCsrfToken`: loại trừ mẫu `*-webhook/*`, `*/payfast-notification/*`, `/billing-verify-webhook/*`, v.v.
- **Sửa trong repo (2026-04-08):** thêm `save-invoices` vào `$except` vì URL legacy Stripe (`billing.save_webhook`) là POST từ Stripe, không mang CSRF Laravel.

**Rủi ro cần lưu ý khi harden thêm:**

- Một số handler chỉ dựa vào chữ ký gateway (Stripe, Razorpay, …); PayPal IPN / `PaypalIPNController` cần đối chiếu tài liệu PayPal về xác minh (hiện code xử lý một phần `txn_type` recurring).
- Tham số `{id}` trên URL webhook thường là hash hiển thị trong UI cấu hình — cần đảm bảo logic trong từng controller thực sự kiểm tra hash/credential, không chỉ “có route”.

---

## 4. Cổng thanh toán & UI cấu hình

`PaymentGatewayCredentialController`: tab Stripe / Razorpay / Paystack / Authorize / PayPal / … — với **Craveva** và super admin, webhook URL dùng các route `billing.*` (ví dụ PayPal: `billing.save_paypal-webhook`).

---

## 5. Lỗi đã phát hiện và xử lý (2026-04-08)

| Vấn đề                 | Mô tả                                                                                         | Xử lý                                                                                        |
| ---------------------- | --------------------------------------------------------------------------------------------- | -------------------------------------------------------------------------------------------- |
| PayPal webhook route   | `save-paypal-webhook/{id}` trỏ tới method không tồn tại (`verpayment-authorizeifyBillingIPN`) | Đổi thành `verifyBillingIPN`                                                                 |
| Stripe `save-invoices` | Route gọi `saveInvoices` nhưng `StripeWebhookController` không có method đó                   | Trỏ tới `verifyStripeWebhook` (cùng logic xử lý payload)                                     |
| CSRF `save-invoices`   | Sau khi route sống, POST từ Stripe sẽ 419 nếu không exclude                                   | Thêm `save-invoices` vào `VerifyCsrfToken::$except`                                          |
| Typo tên route         | `superadmin.billin-offline-plan.download`                                                     | Đổi thành `superadmin.billing-offline-plan.download` + cập nhật `OfflinePlanChangeDataTable` |

---

## 6. Ghi chú code / maintainability

- `BillingController`: biến `$this->paymentGatewatActive` — typo **Gatewat** (theo convention hiện tại của file, tránh đổi tên hàng loạt nếu không refactor toàn cục).
- `PaypalIPNController`: có nhánh dùng `company()->package_type` trong khi context có thể là subscription payment — đáng review logic (ngoài phạm vi audit route).
- `StripeWebhookController@verifyStripeWebhook`: truy cập `$_SERVER['HTTP_STRIPE_SIGNATURE']` không kiểm tra `isset` — có thể gây notice nếu request lạ.

---

## 7. Liên kết module khác

- **Affiliate:** `Modules/Affiliate/Observers/GlobalInvoiceObserver` observe `GlobalInvoice` (đăng ký trong `AffiliateServiceProvider`).

---

## 8. Checklist hành động tiếp theo (tùy ưu tiên)

- [ ] Bổ sung test tích hợp (mock HTTP) cho ít nhất một webhook (Stripe/Razorpay) nếu cần regression an toàn.
- [ ] Rà soát xác minh PayPal IPN (cả `verify-billing-ipn` và `save-paypal-webhook/{id}`) so với tài liệu PayPal hiện tại.
- [ ] Cân nhắc đổi tên route `payWithPaypalRecurrring` / method (breaking change — cần grep toàn repo).
- [ ] Document URL webhook chuẩn cho từng môi trường (staging/production) trong quy trình triển khai nội bộ (không bắt buộc commit vào repo).

---

_Tài liệu này bổ sung cho các flow package/module trong `FUNC_LOGIC/` và cấu hình menu trong `SPECIFICATION/MENU_ROUTES_AND_CACHE.md` nếu có._
