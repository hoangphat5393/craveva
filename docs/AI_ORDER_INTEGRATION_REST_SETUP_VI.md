# Hướng dẫn thiết lập REST API đơn hàng AI (Postman / ERP)

Inbound tạo đơn **chỉ còn REST**: `POST …/api/integrations/orders` với secret theo công ty trong **header** (`X-AI-Webhook-Secret` hoặc `Authorization: Bearer`). URL legacy `/ai-order-webhook/{secret}` đã **gỡ**.

| Cách           | URL                                      | Secret                                    |
| -------------- | ---------------------------------------- | ----------------------------------------- |
| **REST**       | `POST https://…/api/integrations/orders`   | Gửi trong **header** (không có trong URL) |

---

## 1. Trong app: Settings → Sale order settings

1. Đảm bảo đã **tạo / copy secret** theo công ty (nút “Tạo secret webhook mới” nếu cần — lúc đó phải cập nhật mọi client Postman/script).
2. Mục **Allowed HTTP methods**: bật **Create (POST)** cho REST. Tắt = 403 `INTEGRATION_METHOD_DISABLED`.
3. Trên trang có sẵn **REST URLs for Postman**: copy **đúng** `POST …/api/integrations/orders` và các header mẫu.

---

## 2. Postman — tạo request REST

1. **Method:** `POST`
2. **URL:** `https://craveva-staging.test/api/integrations/orders`  
   (đổi host cho đúng môi trường; **có** prefix `/api`, **không** có secret trên path.)
3. **Headers** (bắt buộc tối thiểu):

    | Key                   | Value                                                                                            |
    | --------------------- | ------------------------------------------------------------------------------------------------ |
    | `Accept`              | `application/json`                                                                               |
    | `Content-Type`        | Chỉ để `application/json` khi body là **raw JSON** (xem mục 4 bên dưới).                         |
    | `X-AI-Webhook-Secret` | `32442221d321e90e77a4cd544a763d3b500f97d8b79db5aa9409270b17c2c886` (secret **của đúng company**) |

    Hoặc dùng: `Authorization: Bearer 32442221d321e90e…` (cùng giá trị secret).

    **Lỗi thường gặp:** Tab Body chọn **x-www-form-urlencoded** nhưng Headers vẫn ghi **`Content-Type: application/json`** (Postman có thể thêm tay). Hai thứ **mâu thuẫn**: server đọc body theo kiểu JSON → không thấy `items` / dữ liệu lệch. Hãy **bỏ** header `Content-Type` để Postman tự gửi `application/x-www-form-urlencoded`, **hoặc** (khuyến nghị) chuyển body sang **raw JSON** và giữ `Content-Type: application/json`.

4. **Body:** tab **Body** → **raw** → dropdown **JSON** (khuyến nghị; **không** dùng `x-www-form-urlencoded` nếu bạn muốn copy mẫu JSON từ Sale order settings).  
   Ví dụ (giống webhook, kèm `company_id` khớp secret):

    ```json
    {
        "company_id": 37,
        "client_code": "ZT418",
        "external_event_id": "example-event-002",
        "check_stock": false,
        "items": [
            {
                "item_name": "日清山茶花強力粉25K",
                "quantity": 1,
                "unit_price": 1530
            }
        ]
    }
    ```

5. **Send.** Thành công thường là `200`/`201` kèm JSON đơn; trùng `external_event_id` có thể trả `duplicate: true`.

### Checklist đối chiếu nhanh (Postman ↔ code trong repo)

| Hạng mục               | Hệ thống (Laravel) mong đợi                                                                                                        | Ghi chú từ ảnh Postman của bạn                                                                                  |
| ---------------------- | ---------------------------------------------------------------------------------------------------------------------------------- | --------------------------------------------------------------------------------------------------------------- |
| Method + path          | `POST` + `/api/integrations/orders`                                                                                                | Đúng.                                                                                                           |
| Prefix                 | `RouteServiceProvider` gom `routes/api.php` dưới prefix `api`                                                                      | URI đầy đủ: `…/api/integrations/orders`.                                                                        |
| Auth                   | Header `X-AI-Webhook-Secret` hoặc `Authorization: Bearer` (secret theo company)                                                    | Đúng hướng.                                                                                                     |
| Body vs `Content-Type` | Hoặc **raw JSON** + `Content-Type: application/json`, hoặc **form-urlencoded** + `Content-Type: application/x-www-form-urlencoded` | **Sai nếu** Body = form nhưng header vẫn là `application/json`.                                                 |
| `client_code`          | Key đúng: `client_code` (chữ thường)                                                                                               | Tránh `Client_code` (có thể không map vào rule).                                                                |
| Cookie / CSRF          | GET probe không cần CSRF; Postman gửi cookie session cùng domain có thể khiến POST vào `api` bị CSRF (GET vẫn 200)                 | Dùng code có `api/integrations/*` trong `$except` của `VerifyCsrfToken`, hoặc xóa cookie `.test` trong Postman. |

**`client_code` → lưu `orders.client_id`:** Sau validate, backend map `company_id` + `client_code` → `users.id` (qua `client_details`). REST controller gán id này vào payload tạo order trước khi persist.

Trong repo này, lệnh `php artisan route:list --path=integrations` phải thấy **6** route (gồm `__route_probe` và `POST …/orders`).

---

## 3. Lỗi 401 — `INTEGRATION_REST_REQUIRES_COMPANY_SECRET`

REST **không** dùng secret chỉ trong `.env` (`AI_ORDER_WEBHOOK_SECRET`). Phải dùng **secret theo company** và gửi qua header như mục 2.

---

## 4. Lỗi 403 — `INTEGRATION_METHOD_DISABLED`

Bật **Create (POST)** trong Sale order settings → Save.

---

## 5. Lỗi 404 — `Requested resource not found`

**Bước 0 (bắt buộc):** Mở Postman hoặc trình duyệt, gọi **GET** (không cần header secret):

`https://craveva-staging.test/api/integrations/__route_probe`

- Trả **200** và JSON có `"ok": true` → domain **đang** vào đúng Laravel có file `routes/api.php` của project (sau khi bạn **deploy/pull** bản có route này). Khi đó nếu `POST …/orders` vẫn 404 thì kiểm tra lại URL/method và `php artisan route:clear`.
- **GET probe = 200** nhưng **POST …/orders = 404** trong Postman, và tab **Cookies** có `XSRF-TOKEN` / `craveva_session`: thường do **Sanctum stateful** + **CSRF** trên nhóm `api` khi gọi cùng domain với app. **Đã xử lý trong code:** thêm `api/integrations/*` vào `$except` của `VerifyCsrfToken` (bảo mật vẫn dựa trên `X-AI-Webhook-Secret`). Pull/deploy bản mới rồi thử lại; hoặc tạm thời xóa cookie domain `.test` trong Postman rồi gửi POST.
- **404** luôn cả URL probe → domain **không** trỏ vào codebase này (Apache/Herd trỏ nhầm thư mục, site khác, chưa deploy). Phải sửa vhost / đường dẫn site trước; không phải lỗi JSON body Postman.

Request **không** match route `POST /api/integrations/orders` trên máy đang trả lời URL đó.

Trên máy chạy site (máy có Herd `craveva-staging.test`), trong thư mục **đúng** project:

```bash
php artisan route:list --path=integrations
```

Phải thấy ít nhất: `GET api/integrations/__route_probe` và `POST api/integrations/orders` → `AiIntegrationOrdersController@store`.

- **Không thấy:** `git pull` bản có route, hoặc chỉnh Herd trỏ đúng `public` của repo này.
- **Đã có route mà vẫn 404:** `php artisan route:clear` rồi thử lại.

Chi tiết tiếng Anh + bảng triệu chứng: [`AI_ORDER_INTEGRATION_REST.md`](./AI_ORDER_INTEGRATION_REST.md).

---

## 6. Kiểm chứng tự động (dev)

Từ thư mục gốc repo:

```bash
php artisan test --compact tests/Feature/AiIntegrationOrdersRestApiTest.php
```

---

## Tóm tắt

- Webhook: secret **trên URL**.
- REST: **cùng secret** nhưng gửi **`X-AI-Webhook-Secret` hoặc `Authorization: Bearer`**, URL **`/api/integrations/orders`**, body **raw JSON**, bật **Create (POST)** trong settings.
- 404: kiểm **route:list** + **route:clear** + **đúng document root / đúng codebase** cho host bạn gọi.
