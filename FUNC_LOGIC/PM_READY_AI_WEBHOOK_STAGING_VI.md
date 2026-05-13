# PM Ready — AI Order Webhook (runbook)

**Cập nhật:** 2026-05-12 — **Đã gỡ khỏi tài liệu** URL/secret tạm dùng cho test gấp (`stg-ai-order-*` trên `staging.craveva.com`). Mọi môi trường dùng **secret lấy từ ERP** (theo công ty) hoặc, khi vận hành cho phép, **fallback `.env`** do quản trị cấu hình (không đăng secret thật trong repo).

**2026-05-13:** Inbound tạo đơn **chỉ** qua **`POST /api/integrations/orders`** (REST). Đường cũ `POST /ai-order-webhook/{hash}` đã **gỡ khỏi app** — xem [`AI_ORDER_LEGACY_WEBHOOK_REMOVED_VI.md`](AI_ORDER_LEGACY_WEBHOOK_REMOVED_VI.md) và [`../docs/AI_ORDER_INTEGRATION_REST_SETUP_VI.md`](../docs/AI_ORDER_INTEGRATION_REST_SETUP_VI.md).

---

## Trạng thái hiện tại

- **Inbound AI (canonical):** **`POST {APP_URL}/api/integrations/orders`** — `AiIntegrationOrdersController@store`, `StoreAiOrderWebhookRequest`, `AiOrderWebhookOrderCreationService`; middleware `ai.integration.auth`, `ai.integration.method`.
- **Xác thực:** header **`X-AI-Webhook-Secret`** hoặc **`Authorization: Bearer`** (cùng giá trị secret); **không** đặt secret trên path URL.
- Ringfence: secret **theo công ty** (`companies.ai_order_webhook_secret`) + `company_id` trong body phải khớp khi dùng secret đó.
- Idempotency cơ bản: `external_event_id` (tra cứu qua tag trong `note`).
- Chi tiết secret / `client_code` / `client_id`: [`AI_ORDER_WEBHOOK_SECRET_VA_CLIENT_CODE_VI.md`](AI_ORDER_WEBHOOK_SECRET_VA_CLIENT_CODE_VI.md).

---

## 1) Lấy URL và secret (không copy secret từ tài liệu này)

1. Đăng nhập ERP, chọn đúng **công ty (workspace)**.
2. **Cài đặt → Sale order settings → tab API**.
3. Copy **POST URL** (`…/api/integrations/orders`) và dòng **HTTP header** hiển thị trên màn hình (REST panels).
4. Dán vào hệ thống gửi đơn (LINE / AI / middleware). **Không** hardcode secret vào repo hoặc ticket công khai.

**Ghi chú vận hành:** Nếu công ty chưa tạo secret nhưng server có biến **`AI_ORDER_WEBHOOK_SECRET`** trong `.env`, UI có thể hiển thị hướng dẫn dựa trên fallback toàn instance — quyền quyết định thuộc quản trị; runbook này khuyến nghị ưu tiên **secret theo công ty**. Nếu trước đây `.env` từng chứa secret tạm đã công bố trong tài liệu cũ, nên **đổi (rotate)** giá trị đó trên server và cập nhật mọi tích hợp.

---

## 2) Payload tối thiểu (khuyến nghị **raw JSON**)

Trường bắt buộc (cùng validation với trước đây; xem tab API trên ERP để copy mẫu JSON đầy đủ):

- `company_id` (integer, tồn tại trong `companies`)
- **Khách:** ít nhất một trong hai — `client_code` **hoặc** `client_id` (xem [`AI_ORDER_WEBHOOK_SECRET_VA_CLIENT_CODE_VI.md`](AI_ORDER_WEBHOOK_SECRET_VA_CLIENT_CODE_VI.md))
- `external_event_id` (khuyến nghị unique theo từng event)
- `items[0].item_name`, `items[0].quantity`, `items[0].unit_price` (mảng **`items`** trong JSON)

### `client_code` / `client_id` — tránh 422

- Phải gửi **ít nhất một** trong hai; nếu gửi cả hai thì phải cùng một user trong công ty đó.
- `client_code` / `client_id` phải thuộc đúng **`company_id`** trong body (user **active**).

### Cách test

1. **Postman / curl:** URL + header lấy từ tab API; body **JSON** đủ trường trên (xem [`../docs/AI_ORDER_INTEGRATION_REST_SETUP_VI.md`](../docs/AI_ORDER_INTEGRATION_REST_SETUP_VI.md)).
2. **Test tự động (repo):** `php artisan test --compact tests/Feature/AiOrderWebhookPerCompanySecretTest.php` (gọi REST).

### Ví dụ curl (placeholder — thay bằng giá trị từ ERP)

```bash
curl -X POST "https://YOUR_APP_HOST/api/integrations/orders" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "X-AI-Webhook-Secret: YOUR_SECRET_HEX_FROM_SETTINGS" \
  --data-raw '{"company_id":1,"client_code":"YOUR_CLIENT_CODE","external_event_id":"line-msg-001","note":"Order from LINE AI","check_stock":false,"items":[{"item_name":"Coffee test","quantity":1,"unit_price":10000}]}'
```

Có thể thay `client_code` bằng `client_id` (user active đúng công ty). `check_stock: false` tương đương bỏ kiểm tồn khi cấu hình warehouse bật kiểm tra (tùy nhu cầu pilot).

---

## 3) Response mẫu

### Thành công (201)

```json
{
    "status": "success",
    "message": "Order created from AI webhook.",
    "data": {
        "order_id": 5,
        "order_number": "ODR#001",
        "company_id": 1,
        "total": 10000
    }
}
```

### Gửi lại cùng `external_event_id` (idempotency)

```json
{
    "status": "success",
    "message": "Event already processed.",
    "duplicate": true
}
```

### Lỗi xác thực secret (401)

```json
{
    "status": "error",
    "message": "Unauthorized webhook request."
}
```

### Lỗi dữ liệu (422) — hình dạng tham khảo

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "company_id": ["The company id field is required."],
        "client_code": ["…"],
        "items": ["The items field is required."]
    }
}
```

Thông điệp cụ thể phụ thuộc locale và rule trong `StoreAiOrderWebhookRequest`.

---

## 4) Checklist test cho PM

1. Gọi webhook với payload hợp lệ → nhận **201**.
2. Kiểm tra màn **Orders** → có đơn mới, `client_id` đúng khách.
3. Gọi lại đúng `external_event_id` cũ → nhận **`duplicate: true`**.
4. Sai secret (URL hoặc header) → **401**.

---

## 5) Bước tiếp theo (sản phẩm / kỹ thuật)

- Thêm bảng log inbound (`ai_order_webhook_logs`) nếu cần audit chi tiết.
- Mapping `line_user_id → client_code` tự động (tùy nghiệp vụ).
- Cân nhắc chữ ký **HMAC** thay cho shared secret đơn (tài liệu cải tiến: [`../FUNC_IMPROVE/SO_AI_WEBHOOK_PROMPTS_VI.md#part-3-api-tab--ringfence-prompt`](../FUNC_IMPROVE/SO_AI_WEBHOOK_PROMPTS_VI.md#part-3-api-tab--ringfence-prompt)).

---

## Liên kết

- Secret DB + client: [`AI_ORDER_WEBHOOK_SECRET_VA_CLIENT_CODE_VI.md`](AI_ORDER_WEBHOOK_SECRET_VA_CLIENT_CODE_VI.md)
- Audit API: [`AUDIT_AI_ORDER_INBOUND_SO_API_VI.md`](AUDIT_AI_ORDER_INBOUND_SO_API_VI.md)

_Chỉ mục:_ [`INDEX.md`](INDEX.md).
