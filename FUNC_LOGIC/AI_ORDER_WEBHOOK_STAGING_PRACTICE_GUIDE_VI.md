# Hướng dẫn deploy & thực hành AI Order Webhook trên Staging

Tài liệu này hướng dẫn cách đưa tính năng webhook inbound AI lên staging và test end-to-end.

## 1) Các file đã thay đổi

- `app/Http/Controllers/Integrations/AiOrderWebhookController.php`
- `app/Http/Requests/Integrations/StoreAiOrderWebhookRequest.php`
- `routes/web-public.php`

## 2) Endpoint để test

- `POST /ai-order-webhook/{hash}`
- Header bắt buộc: `X-AI-Webhook-Secret`
- `hash` trên URL và header phải trùng với `AI_ORDER_WEBHOOK_SECRET`

---

## 3) Cách upload lên staging (khuyến nghị an toàn)

Không nên dùng ngay `scripts/upload_staging.ps1` cho case này nếu chưa rà kỹ, vì script đang có bước dọn thư mục remote khá mạnh.

### Cách an toàn: upload đúng 3 file

Từ máy local (PowerShell):

```powershell
scp "app/Http/Controllers/Integrations/AiOrderWebhookController.php" "craveva-staging:/var/www/craveva-staging/current/craveva/app/Http/Controllers/Integrations/AiOrderWebhookController.php"
scp "app/Http/Requests/Integrations/StoreAiOrderWebhookRequest.php" "craveva-staging:/var/www/craveva-staging/current/craveva/app/Http/Requests/Integrations/StoreAiOrderWebhookRequest.php"
scp "routes/web-public.php" "craveva-staging:/var/www/craveva-staging/current/craveva/routes/web-public.php"
```

> Nếu thư mục `Integrations` chưa có trên server, tạo trước:

```bash
ssh craveva-staging "mkdir -p /var/www/craveva-staging/current/craveva/app/Http/Controllers/Integrations /var/www/craveva-staging/current/craveva/app/Http/Requests/Integrations"
```

---

## 4) Cấu hình trên staging

SSH vào staging:

```bash
ssh craveva-staging
cd /var/www/craveva-staging/current/craveva
```

Thêm vào `.env`:

```env
AI_ORDER_WEBHOOK_SECRET=your-very-strong-secret
```

Clear cache config/route:

```bash
php artisan optimize:clear
php artisan config:clear
php artisan route:clear
```

---

## 5) Test thực hành (cURL)

```bash
curl -X POST "https://staging-domain.com/ai-order-webhook/your-very-strong-secret" \
  -H "Content-Type: application/json" \
  -H "X-AI-Webhook-Secret: your-very-strong-secret" \
  -d '{
    "company_id": 1,
    "client_id": 12,
    "external_event_id": "line-msg-0001",
    "note": "Order from LINE AI",
    "discount_type": "fixed",
    "discount_value": 0,
    "items": [
      {
        "item_name": "Coffee Arabica 250g",
        "product_id": 5,
        "sku": "COF-250",
        "quantity": 2,
        "unit_price": 120000
      }
    ]
  }'
```

Kỳ vọng:

- HTTP `201`
- JSON trả về `order_id`, `order_number`

---

## 6) Kiểm tra dữ liệu trong DB staging

```sql
SELECT id, company_id, client_id, order_number, total, status, created_at
FROM orders
ORDER BY id DESC
LIMIT 5;
```

```sql
SELECT id, order_id, item_name, quantity, unit_price, amount
FROM order_items
ORDER BY id DESC
LIMIT 10;
```

---

## 7) Lỗi thường gặp

- `401 Unauthorized webhook request`
    - Sai `hash` URL hoặc sai header `X-AI-Webhook-Secret`
    - `.env` chưa đúng hoặc chưa clear config cache

- `422 Unprocessable Entity`
    - Thiếu `company_id`
    - Thiếu `items` hoặc item thiếu `item_name/quantity/unit_price`

- `500`
    - DB/foreign key không hợp lệ (vd `company_id`, `client_id`, `product_id`)
    - Kiểm tra log: `storage/logs/laravel.log`

- Trùng event nhưng không tạo đơn mới
    - `external_event_id` đã xử lý trước đó (idempotency cơ bản)

---

## 8) Nếu vẫn muốn dùng `upload_staging.ps1`

Phải thêm 3 file vào biến `$FilesToCopy`:

- `app/Http/Controllers/Integrations/AiOrderWebhookController.php`
- `app/Http/Requests/Integrations/StoreAiOrderWebhookRequest.php`
- `routes/web-public.php`

Và cần review kỹ các bước xóa file remote trong script trước khi chạy.

---

## 9) Khuyến nghị trước khi UAT với AI thật

1. Tạo secret dài, không dùng secret ngắn.
2. Giới hạn IP gọi webhook (nếu infra cho phép).
3. Bật HTTPS bắt buộc.
4. Tạo user API/line mapping chuẩn trước khi auto tạo order.
5. Bổ sung bảng log inbound riêng cho audit ở vòng tiếp theo.
