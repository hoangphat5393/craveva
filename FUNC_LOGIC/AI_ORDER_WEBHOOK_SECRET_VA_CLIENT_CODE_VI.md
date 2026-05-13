# AI Order Webhook — Secret, định danh khách (`client_code` / `client_id`) & payload

> **2026-05-13:** Đường `POST /ai-order-webhook/{hash}` đã **gỡ**. Inbound tạo đơn dùng **`POST /api/integrations/orders`** (cùng JSON + header secret). Xem `FUNC_LOGIC/AI_ORDER_LEGACY_WEBHOOK_REMOVED_VI.md`. Nội dung dưới đây giữ cho **auth / payload / DB**; bỏ qua mọi đoạn chỉ nói URL path cũ.

Tài liệu ngắn cho PM / kỹ thuật: inbound tạo Sales Order qua **REST** `POST /api/integrations/orders` (middleware `api` + `ai.integration.auth` / `ai.integration.method`).

**Cập nhật nội dung:** 2026-05-13 — đối chiếu `AiIntegrationOrdersController`, `StoreAiOrderWebhookRequest`, `OrderObserver`, cấu hình Warehouse, UI Sale order settings.

---

## 1) Secret webhook lưu ở đâu trong database?

| Thành phần                        | Bảng             | Cột                       | Ghi chú                                                                                                                                                         |
| --------------------------------- | ---------------- | ------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Secret **theo công ty** (ưu tiên) | `companies`      | `ai_order_webhook_secret` | Chuỗi hex (unique). Sinh từ màn **Cài đặt → Sale order settings → tab API** → nút tạo secret.                                                                   |
| Secret **dự phòng toàn server**   | _(không lưu DB)_ | —                         | Biến môi trường `AI_ORDER_WEBHOOK_SECRET` trong `.env`, map trong `config('app.ai_order_webhook_secret')`. Dùng khi công ty **chưa** có giá trị trong cột trên. |

URL và header dùng **cùng một** giá trị secret (segment cuối path `{hash}` = giá trị header `X-AI-Webhook-Secret`). So khớp bằng `hash_equals`.

---

## 2) `client_code` / `client_id` lưu ở đâu & ý nghĩa?

| Thành phần                             | Bảng             | Cột           | Ghi chú                                                                                                                                                                                                                                                                                                                  |
| -------------------------------------- | ---------------- | ------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| Mã khách hàng (Customer code)          | `client_details` | `client_code` | Unique theo cặp `(company_id, client_code)` (migration `make_client_code_unique_per_company`).                                                                                                                                                                                                                           |
| User khách (để gán `orders.client_id`) | `users`          | `id`          | Liên kết `client_details.user_id` → `users.id`. Body gửi **`client_code`** và/hoặc **`client_id`** (ít nhất một; gửi cả hai thì phải cùng một khách). `client_code` tra `client_details` theo `company_id` (hoặc `user_id` thuộc user của công ty). `client_id` là `users.id` **active** và **`company_id`** trùng body. |

Trên UI ERP: **Clients (Khách hàng)** — trường **Client code / Mã khách** (nếu trống thì caller chỉ có thể dùng `client_id` = `users.id` nếu biết).

---

## 3) Hướng dẫn sử dụng nhanh

1. Đăng nhập ERP, chọn đúng **công ty (workspace)**.
2. Vào **Sale order settings** → tab **API** → (nếu cần) **Generate new webhook secret** — copy **POST URL** và dòng **HTTP header**.
3. Trên hệ thống gửi webhook (LINE / AI / middleware): cấu hình POST body gồm tối thiểu:
    - `company_id` — id công ty (trùng workspace đang copy; với secret theo công ty **bắt buộc** trùng công ty của secret).
    - `client_code` **hoặc** `client_id` — ít nhất một; mỗi giá trị phải thuộc **cùng** `company_id`.
    - `items[...]` — ít nhất một dòng; mỗi dòng bắt buộc `item_name`, `quantity` (> 0), `unit_price` (≥ 0).
4. Mỗi lần đổi secret trên ERP, cập nhật lại URL + header ở mọi nơi đã cấu hình.

Runbook chi tiết (HTTP code, curl, response mẫu): **`FUNC_LOGIC/PM_READY_AI_WEBHOOK_STAGING_VI.md`**.

---

## 4) Audit chức năng AI Order Webhook (theo code)

### 4.1 Luồng xử lý (tóm tắt)

| Bước | Thành phần                      | Hành vi                                                                                                                                                                                                                                 |
| ---- | ------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| 1    | `routes/api.php`                | Nhóm `Route::middleware(['ai.integration.auth', 'ai.integration.method'])->prefix('integrations')` → **`POST integrations/orders`** → `AiIntegrationOrdersController@store`.                                                            |
| 2    | `StoreAiOrderWebhookRequest`    | Nếu body rỗng nhưng raw body là JSON hợp lệ → merge vào request. Validate field theo `rules()`; `withValidator` **after**: resolve `client_code` → `user_id`, kiểm tra `client_id`/`client_code` thuộc `company_id`, merge `client_id`. |
| 3    | `AiIntegrationOrdersController` | Xác thực secret qua `AiOrderIntegrationAuthService` (header); 401 nếu sai. Gán `client_id` đã resolve; kiểm tra `company_id` vs công ty của secret per-company → 422 JSON tùy chỉnh nếu lệch.                                           |
| 4    | Idempotency                     | Nếu có `external_event_id`: tìm `orders.note` LIKE `%[ai_event:…]%` trong cùng `company_id` → trả JSON success + `duplicate: true` (không tạo đơn lần hai).                                                                             |
| 5    | Warehouse                       | Nếu `WarehouseFlowConfigService::aiOrderWebhookCheckStock($companyId)` bật và body **không** gửi `check_stock: false` → `WarehouseAvailabilityService::validateAiOrderWebhookItems` → lỗi nghiệp vụ 422 JSON.                           |
| 6    | Tạo `Order` + `OrderItems`      | Transaction; `order_date` = ngày hiện tại; `currency_id` từ company; `company_address_id` từ body hoặc địa chỉ mặc định / bất kỳ của công ty.                                                                                           |
| 7    | `OrderObserver`                 | Webhook **không** có session `company()` → không set `added_by` từ user đăng nhập. `NewOrderEvent` chỉ khi `request()->type == 'send'` **và** có `client_id` (tránh `findOrFail` khi không gửi notify).                                 |

### 4.2 Bảng đối chiếu nhanh

| Hạng mục                            | Trạng thái                     | Ghi chú ngắn                                                                                                                   |
| ----------------------------------- | ------------------------------ | ------------------------------------------------------------------------------------------------------------------------------ |
| Auth shared secret                  | Có                             | Per-company hoặc fallback env; `hash_equals`.                                                                                  |
| Ringfence `company_id` vs secret    | Có                             | 422 JSON khi secret theo công ty nhưng `company_id` khác.                                                                      |
| Ringfence khách theo `company_id`   | Có                             | `lookupUserIdFromClientCode` + `isActiveUserInCompany`.                                                                        |
| Body JSON hoặc form                 | Có                             | `prepareForValidation` merge JSON khi `all()` rỗng.                                                                            |
| CSRF                                | **Không áp dụng** (nhóm `api`) | Trước đây (legacy web path): `VerifyCsrfToken` except `ai-order-webhook/*` — **đã gỡ**.                                        |
| Idempotency                         | Có (pilot)                     | Dựa trên chuỗi trong `note`, không phải header `Idempotency-Key` chuẩn IETF.                                                   |
| Kiểm tồn                            | Có (tùy công ty + env)         | `check_stock: false` để bỏ qua khi cần (ví dụ pilot).                                                                          |
| `project_id`                        | Tùy chọn, `exists:projects,id` | Rule **không** giới hạn theo `company_id` trong code — nếu gửi, chỉ nên dùng project id thuộc đúng công ty (quy ước tích hợp). |
| OpenAPI / log DB / rate limit riêng | Chưa                           | Xem `FUNC_LOGIC/AUDIT_AI_ORDER_INBOUND_SO_API_VI.md`.                                                                          |

### 4.3 Rủi ro / lưu ý vận hành

- **Idempotency:** Phụ thuộc nội dung `note` chứa `[ai_event:…]`; không rename/xóa pattern này nếu cần idempotent ổn định.
- **`OrderObserver::saving`:** Khi `company_address_id` null (hiếm vì controller đã cố gán theo company), fallback `CompanyAddress::where('is_default', 1)->first()` **không** lọc `company_id` trong observer — tránh để order thiếu địa chỉ ở luồng UI khác; webhook hiện gán địa chỉ theo company trước khi save.
- **Bảo mật:** Secret trên URL + header — không log full URL có secret; rotate định kỳ.

---

## 5) Mẫu dữ liệu ví dụ (thay placeholder bằng giá trị thật từ tab API)

Giả định: `company_id = 1`, khách có mã `LINE-CUST-001` (hoặc thay bằng `client_id` = `users.id` tương ứng).

### 5.1 HTTP

```http
POST /api/integrations/orders HTTP/1.1
Host: your-erp-host.example
Accept: application/json
Content-Type: application/json
X-AI-Webhook-Secret: YOUR_WEBHOOK_SECRET_HEX
```

(Secret chỉ nằm trong **header** (hoặc `Authorization: Bearer` cùng giá trị), **không** còn segment trên path.)

### 5.2 JSON body (ví dụ đầy đủ tối thiểu + tùy chọn thường dùng)

```json
{
    "company_id": 1,
    "client_code": "LINE-CUST-001",
    "external_event_id": "line-inbound-2026-05-12-0001",
    "note": "Đơn test từ LINE / AI",
    "check_stock": false,
    "items": [
        {
            "item_name": "Cà phê Arabica 250g",
            "quantity": 2,
            "unit_price": 120000,
            "sku": "COF-250"
        }
    ]
}
```

**Phiên bản chỉ dùng `client_id`** (thay `client_code`):

```json
{
    "company_id": 1,
    "client_id": 42,
    "external_event_id": "line-inbound-2026-05-12-0002",
    "note": "Đơn test chỉ định user id",
    "check_stock": false,
    "items": [
        {
            "item_name": "Cà phê Arabica 250g",
            "quantity": 1,
            "unit_price": 120000
        }
    ]
}
```

(`42` chỉ là ví dụ — thay bằng `users.id` **active** thuộc **cùng** `company_id`.)

### 5.3 `application/x-www-form-urlencoded` (Postman / LINE webhook kiểu form)

Cùng ý nghĩa với JSON; ví dụ ghép tham số (xuống dòng chỉ để đọc):

```text
company_id=1
&client_code=LINE-CUST-001
&external_event_id=line-inbound-2026-05-12-0003
&note=Don+tu+form-urlencoded
&check_stock=0
&items[0][item_name]=Ca+phe+Arabica+250g
&items[0][quantity]=2
&items[0][unit_price]=120000
&items[0][sku]=COF-250
```

- `check_stock`: gửi `0` / `1` hoặc boolean tùy client (Laravel cast `boolean`).
- Có thể đổi `client_code=...` thành `client_id=42` (không bắt buộc gửi cả hai).

### 5.4 Response thành công tạo mới (rút gọn)

```json
{
    "status": "success",
    "message": "Order created from AI webhook.",
    "data": {
        "order_id": 100,
        "order_number": "…",
        "company_id": 1,
        "total": 240000
    }
}
```

Giá trị số thực tế phụ thuộc đơn vị tiền và logic giảm giá trên server.

---

## 6) Audit & tài liệu liên quan

- Góc nhìn **REST / OpenAPI / rate limit**: [`AUDIT_AI_ORDER_INBOUND_SO_API_VI.md`](AUDIT_AI_ORDER_INBOUND_SO_API_VI.md)
- Runbook PM / curl: [`PM_READY_AI_WEBHOOK_STAGING_VI.md`](PM_READY_AI_WEBHOOK_STAGING_VI.md)

_Chỉ mục:_ [`INDEX.md`](INDEX.md).
