# Legacy AI order webhook đã gỡ (`POST /ai-order-webhook/{hash}`)

**Thay thế:** `POST {APP_URL}/api/integrations/orders` — cùng JSON body (`StoreAiOrderWebhookRequest`), header `X-AI-Webhook-Secret` hoặc `Authorization: Bearer` với **`companies.ai_order_webhook_secret`** (secret **không** nằm trên path).

**Đã xóa / gỡ:**

- Route trong `routes/web-public.php`
- `app/Http/Controllers/Integrations/AiOrderWebhookController.php`
- CSRF exception `ai-order-webhook/*` trong `VerifyCsrfToken` (route không còn)

**Giữ nguyên (dùng chung cho REST):**

- `StoreAiOrderWebhookRequest`, `AiOrderWebhookOrderCreationService`, cột & flag trên `companies`, UI **Sale order settings** (chỉ còn hướng dẫn REST + nút tạo secret).

**Tài liệu cập nhật:** `docs/AI_ORDER_INTEGRATION_REST.md`, `docs/AI_ORDER_INTEGRATION_REST_SETUP_VI.md`.
