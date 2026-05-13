# Audit: API AI order REST (`/api/integrations/orders`) — nguyên nhân 404 & file liên quan

**Ngày audit:** theo codebase trong repo.  
**Test local:** `php artisan test --compact tests/Feature/AiIntegrationOrdersRestApiTest.php tests/Feature/AiOrderWebhookPerCompanySecretTest.php` (REST + validation parity; legacy webhook route đã gỡ).

---

## 1. Kết luận ngắn (nguyên nhân lỗi bạn gặp)

JSON lỗi dạng:

```json
{
    "message": "Requested resource not found",
    "error": { "code": 404, "details": { "url": "…/api/integrations/orders" } }
}
```

**không** do `AiIntegrationOrdersController` hay `StoreAiOrderWebhookRequest` trả về. Controller của app khi “không tìm thấy đơn” trả JSON kiểu `status: error`, `message: Order not found.` (không có cấu trúc `error.details.url` như trên).

→ Đây là kiểu phản hồi **lớp ngoài** (thường gặp với stack **Froiden Laravel REST API** khi **không có route khớp** request), tức **HTTP request chưa dispatch vào route Laravel** `POST api/integrations/orders` trên process đang trả lời (deploy cũ, `route:cache`, document root khác, proxy/CDN, v.v.).

**GET** `…/api/integrations/__route_probe` hoặc `GET …/orders/{id}` chạy được chứng minh **cùng host** đôi khi vẫn vào Laravel; **POST** 404 nghĩa là trong tình huống lỗi, **POST không match cùng bảng route** hoặc bị chặn trước router — không phải do “GET được phép mà POST bị cấm” trong một controller duy nhất.

---

## 2. Bản đồ file & luồng (để tự trace)

| Thành phần                                           | File                                                                                                                     |
| ---------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------ |
| Đăng ký route + prefix `api`                         | `app/Providers/RouteServiceProvider.php` → `mapApiRoutes()`                                                              |
| URI REST + probe                                     | `routes/api.php`                                                                                                         |
| Middleware nhóm `api`                                | `app/Http/Kernel.php` (`EnsureFrontendRequestsAreStateful`, `throttle:api`, `SubstituteBindings`)                        |
| Xác thực secret REST                                 | `app/Http/Middleware/AuthenticateAiOrderIntegration.php` + `app/Services/Integrations/AiOrderIntegrationAuthService.php` |
| Kiểm tra verb (POST/GET/…)                           | `app/Http/Middleware/EnsureAiOrderIntegrationMethodAllowed.php`                                                          |
| CSRF (chỉ khi stack stateful chèn `VerifyCsrfToken`) | `app/Http/Middleware/VerifyCsrfToken.php` — ngoại lệ **`api/integrations/*`**                                            |
| Controller REST                                      | `app/Http/Controllers/Api/Integrations/AiIntegrationOrdersController.php`                                                |
| Validation body (create/update)                      | `app/Http/Requests/Integrations/StoreAiOrderWebhookRequest.php`, `UpdateAiIntegrationOrderRequest.php`                   |
| Tạo đơn (dùng chung webhook)                         | `app/Services/Integrations/AiOrderWebhookOrderCreationService.php`                                                       |
| Webhook legacy (đã gỡ)                             | Trước đây: `routes/web-public.php` + `AiOrderWebhookController` — thay bằng REST `POST /api/integrations/orders` |

**Luồng POST tạo đơn (REST):**

`Request` → global middleware → nhóm `api` → `ai.integration.auth` → `ai.integration.method` → `AiIntegrationOrdersController@store` → `StoreAiOrderWebhookRequest` → service tạo đơn.

Các bước trên **không** có nhánh trả JSON `Requested resource not found` như bạn thấy.

---

## 3. Vì sao GET ổn mà POST hay 404 (cùng codebase)

| Yếu tố                                                 | Giải thích                                                                                                                                                                                                                                                                                                                                                     |
| ------------------------------------------------------ | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Sanctum `EnsureFrontendRequestsAreStateful`**        | Với domain nằm trong `SANCTUM_STATEFUL_DOMAINS` / `APP_URL`, request từ Postman/browser có cookie có thể bị **chèn middleware web (gồm CSRF)** vào nhóm `api`. **GET** “an toàn” hơn với CSRF; **POST** dễ bị chặn nếu chưa có ngoại lệ URI. Repo đã thêm **`api/integrations/*`** vào `$except` — **bắt buộc** server thật chạy **đúng bản** có thay đổi này. |
| **Hai môi trường** (`*.test` vs `staging.craveva.com`) | Cùng triệu chứng → thường là **chưa deploy đủ** hoặc **proxy/WAF** chứ không phải bug chỉ ở local.                                                                                                                                                                                                                                                             |
| **URL POST**                                           | Chỉ **`POST /api/integrations/orders`** (không có `/{id}`). Nhầm path dễ tưởng “GET được POST không”.                                                                                                                                                                                                                                                          |
| **Body**                                               | Dán cả lệnh `curl` vào Body → **422**, không phải 404; nhưng dễ gây nhầm khi đổi tab.                                                                                                                                                                                                                                                                          |

---

## 4. Việc cần làm trên server / local khi còn 404

1. `php artisan route:list --path=integrations` — phải có **`POST api/integrations/orders`**.
2. `php artisan optimize:clear` (hoặc ít nhất `route:clear`) + **restart PHP-FPM / Apache**.
3. Trên staging: `curl` POST **vào loopback** (bỏ qua CDN) để tách lỗi proxy.
4. Đảm bảo **một request Postman đã Save**: Headers + raw JSON đúng; `external_event_id` mới mỗi lần tạo đơn thật.

---

## 5. Tài liệu hướng dẫn thao tác

- Tiếng Việt: `docs/AI_ORDER_INTEGRATION_REST_SETUP_VI.md`
- Tiếng Anh + checklist: `docs/AI_ORDER_INTEGRATION_REST.md`

---

_Tài liệu audit chỉ mô tả hành vi theo code trong repo; không thay thế kiểm tra trên máy chủ thật (`route:list`, log access, curl nội bộ)._
