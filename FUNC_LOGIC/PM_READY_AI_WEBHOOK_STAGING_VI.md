# PM Ready - AI Order Webhook (Staging)

## Trạng thái hiện tại

- Webhook inbound AI đã triển khai trên staging và test thành công.
- Migration lỗi trước đó đã được sửa và chạy thành công.
- Endpoint đã tạo được `orders` + `order_items`.
- Có kiểm tra idempotency cơ bản theo `external_event_id`.

---

## 1) Thông tin webhook để setup

**Environment:** Staging  
**Base URL:** `https://staging.craveva.com`

**Webhook URL:**

`POST https://staging.craveva.com/ai-order-webhook/stg-ai-order-20260329-9fA2mK`

**Headers bắt buộc:**

- `X-AI-Webhook-Secret: stg-ai-order-20260329-9fA2mK`
- `Accept: application/json`

> Ghi chú: đây là secret tạm cho test gấp. Sau khi PM xác nhận flow, nên rotate secret mới để UAT/production.

---

## 2) Payload tối thiểu (khuyến nghị gửi dạng form-data hoặc x-www-form-urlencoded)

Trường bắt buộc:

- `company_id` (ví dụ `1`)
- `client_id` (ví dụ `1`)
- `external_event_id` (unique theo từng event)
- `items[0][item_name]`
- `items[0][quantity]`
- `items[0][unit_price]`

Ví dụ:

```bash
curl -X POST "https://staging.craveva.com/ai-order-webhook/stg-ai-order-20260329-9fA2mK" \
  -H "Accept: application/json" \
  -H "X-AI-Webhook-Secret: stg-ai-order-20260329-9fA2mK" \
  -d "company_id=1" \
  -d "client_id=1" \
  -d "external_event_id=line-msg-001" \
  -d "note=Order from LINE AI" \
  -d "items[0][item_name]=Coffee test" \
  -d "items[0][quantity]=1" \
  -d "items[0][unit_price]=10000"
```

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

### Lỗi dữ liệu (422)

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "company_id": ["The company id field is required."],
        "client_id": ["The client id field is required."],
        "items": ["The items field is required."]
    }
}
```

---

## 4) Checklist test cho PM

1. Gọi webhook với payload hợp lệ -> nhận `201`.
2. Kiểm tra màn hình Orders trên staging -> có đơn mới.
3. Gọi lại đúng `external_event_id` cũ -> nhận `duplicate: true`.
4. Đổi secret sai -> nhận `401`.

---

## 5) Scope hiện tại và bước tiếp theo

### Đã có

- Nhận webhook từ AI.
- Tạo order + items.
- Xử lý trùng event cơ bản.

### Đề xuất bước tiếp theo

- Rotate secret mới sau khi PM test xong.
- Thêm bảng log inbound riêng (`ai_order_webhook_logs`) để audit chi tiết.
- Tách mapping `line_user_id -> client_id` tự động (không gửi `client_id` thủ công).
- Nâng cấp chữ ký HMAC chuẩn thay vì shared secret đơn.

Dữ liệu mãu

curl -X POST "https://staging.craveva.com/ai-order-webhook/stg-ai-order-20260329-9fA2mK" \
 -H "Accept: application/json" \
 -H "X-AI-Webhook-Secret: stg-ai-order-20260329-9fA2mK" \
 -d "company_id=20" \
 -d "client_id=345" \
 -d "external_event_id=line-company20-20260329-001" \
 -d "note=LINE order test for company 20" \
 -d "items[0][item_name]=Coffee Arabica 250g" \
 -d "items[0][quantity]=2" \
 -d "items[0][unit_price]=120000" \
 -d "items[0][sku]=COF-250"
