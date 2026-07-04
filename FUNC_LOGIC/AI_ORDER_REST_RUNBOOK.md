# AI Order REST — kiến trúc, auth và runbook

Tài liệu này là bản gộp của các ghi chú cũ về AI/LINE tạo Sales Order trong ERP.

## 1. Trạng thái hiện tại

Phần ERP inbound order đã triển khai theo REST:

- Endpoint chính: `POST /api/integrations/orders`
- Controller: `App\Http\Controllers\Api\Integrations\AiIntegrationOrdersController`
- Request validation: `App\Http\Requests\Integrations\StoreAiOrderWebhookRequest`
- Service tạo đơn: `App\Services\Integrations\AiOrderWebhookOrderCreationService`
- Middleware: `ai.integration.auth`, `ai.integration.method`
- Legacy endpoint `POST /ai-order-webhook/{hash}` đã gỡ khỏi runtime.

Ngoài `POST`, hệ thống cũng có route REST cho đọc/sửa/xóa tích hợp:

- `GET /api/integrations/orders/{orderId}`
- `PATCH|PUT /api/integrations/orders/{orderId}`
- `DELETE /api/integrations/orders/{orderId}`

Phần chưa nằm trong ERP repo này: LINE/WhatsApp/Zalo receiver hoặc AI Gateway đọc tin nhắn khách hàng. Gateway bên ngoài sẽ nhận chat, hiểu ý định, rồi gọi API ERP ở trên.

## 2. Kiến trúc đúng

Luồng khuyến nghị:

1. Khách nhắn tin qua LINE/chat.
2. LINE gửi webhook vào AI service hoặc Integration Gateway.
3. Gateway xác thực chữ ký LINE, lưu raw event để audit.
4. AI trích xuất khách hàng, sản phẩm, số lượng, ghi chú.
5. Gateway kiểm tra SKU, giá, tồn kho nếu cần.
6. Gateway hỏi khách xác nhận đơn.
7. Gateway gọi ERP REST API `POST /api/integrations/orders`.
8. ERP validate và tạo Sales Order qua business rules hiện có.
9. ERP trả `order_id`, `order_number`, `status`.

Không khuyến nghị để AI insert trực tiếp vào DB ERP vì sẽ bỏ qua validation, pricing, stock rule, tax, audit và rollback.

Module `Modules/Webhooks` của ERP hiện là webhook outbound: ERP có sự kiện rồi gửi ra ngoài. Nó không phải endpoint nhận lệnh đặt hàng từ LINE/AI.

## 3. Auth và định danh khách

Secret ưu tiên theo công ty:

| Thành phần | Nơi lưu | Ghi chú |
| --- | --- | --- |
| Secret theo công ty | `companies.ai_order_webhook_secret` | Sinh từ Sale order settings, tab API. |
| Secret fallback toàn server | `.env` `AI_ORDER_WEBHOOK_SECRET` | Chỉ dùng khi công ty chưa có secret và vận hành cho phép. |

Request gửi secret qua một trong hai header:

- `X-AI-Webhook-Secret: <secret>`
- `Authorization: Bearer <secret>`

Không đặt secret trong URL path.

Khách hàng có thể gửi bằng:

| Field | Ý nghĩa |
| --- | --- |
| `client_code` | Mã khách trong `client_details.client_code`, unique theo `company_id`. |
| `client_id` | `users.id` của khách active thuộc đúng `company_id`. |

Phải gửi ít nhất một trong hai. Nếu gửi cả hai thì phải cùng một khách trong cùng công ty.

## 4. Payload tối thiểu

```json
{
    "company_id": 1,
    "client_code": "LINE-CUST-001",
    "external_event_id": "line-msg-001",
    "note": "Order from LINE AI",
    "check_stock": false,
    "items": [
        {
            "item_name": "Coffee test",
            "quantity": 1,
            "unit_price": 10000,
            "sku": "COF-250"
        }
    ]
}
```

Field quan trọng:

- `company_id`: bắt buộc, phải khớp secret theo công ty nếu dùng company secret.
- `client_code` hoặc `client_id`: bắt buộc một trong hai.
- `external_event_id`: khuyến nghị unique để tránh tạo đơn trùng.
- `items`: bắt buộc ít nhất một dòng.
- `check_stock`: gửi `false` nếu pilot cần bỏ kiểm tồn khi cấu hình warehouse bật kiểm tra.

## 5. Cách test nhanh

Lấy URL và secret:

1. Đăng nhập ERP, chọn đúng công ty.
2. Vào Sale order settings -> tab API.
3. Copy REST URL và header secret.

Curl mẫu:

```bash
curl -X POST "https://YOUR_APP_HOST/api/integrations/orders" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "X-AI-Webhook-Secret: YOUR_SECRET_HEX_FROM_SETTINGS" \
  --data-raw '{"company_id":1,"client_code":"YOUR_CLIENT_CODE","external_event_id":"line-msg-001","note":"Order from LINE AI","check_stock":false,"items":[{"item_name":"Coffee test","quantity":1,"unit_price":10000}]}'
```

Response thành công:

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

Response gửi lại cùng `external_event_id`:

```json
{
    "status": "success",
    "message": "Event already processed.",
    "duplicate": true
}
```

## 6. Checklist nghiệm thu

- Payload hợp lệ -> HTTP `201`, tạo Sales Order.
- Sai secret -> HTTP `401`.
- `company_id` không khớp company secret -> HTTP `422`.
- `client_code` / `client_id` không thuộc công ty -> HTTP `422`.
- Gửi lại cùng `external_event_id` -> không tạo đơn mới, trả `duplicate: true`.
- Khi bật stock check, item không đủ điều kiện -> HTTP `422`.

## 7. Test tự động liên quan

- `tests/Feature/AiIntegrationOrdersRestApiTest.php`
- `tests/Feature/AiOrderWebhookPerCompanySecretTest.php`
- `tests/Feature/WarehouseUpgradeP0Test.php` có phần validate AI order stock.

Chạy nhanh:

```bash
php artisan test --compact tests/Feature/AiIntegrationOrdersRestApiTest.php tests/Feature/AiOrderWebhookPerCompanySecretTest.php
```

## 8. Backlog còn lại

Các mục này là cải tiến, không chặn chức năng REST hiện tại:

- Bảng log inbound riêng, ví dụ `ai_order_webhook_logs`.
- Mapping tự động `line_user_id -> client_code`.
- HMAC hoặc signature mạnh hơn shared secret đơn.
- Rate limit / OpenAPI / dashboard giám sát riêng.
- Integration Gateway đa kênh nếu triển khai LINE/WhatsApp/Zalo production.

## 9. Tài liệu liên quan

- REST API chi tiết: [`../docs/AI_ORDER_REST.md`](../docs/AI_ORDER_REST.md)
- Hướng dẫn Postman/probe: [`../docs/AI_ORDER_REST_SETUP.md`](../docs/AI_ORDER_REST_SETUP.md)
- Backlog tùy chọn: [`../FUNC_IMPROVE/12_AI_THIRDPARTY_SO_OPTIONS.md`](../FUNC_IMPROVE/12_AI_THIRDPARTY_SO_OPTIONS.md)
