# Audit — API inbound tạo Sales Order (AI / third-party)

> **2026-05-13:** Endpoint legacy **`POST /ai-order-webhook/{hash}`** (`web` + CSRF except) **đã gỡ**. Bản audit dưới mô tả **hợp đồng HTTP + validation** vẫn dùng chung (`StoreAiOrderWebhookRequest`, service tạo đơn). **Định tuyến hiện tại:** `routes/api.php` → nhóm `integrations` → **`POST /api/integrations/orders`** → `AiIntegrationOrdersController@store` (middleware `ai.integration.auth`, `ai.integration.method`). Tài liệu vận hành: [`../docs/AI_ORDER_INTEGRATION_REST.md`](../docs/AI_ORDER_INTEGRATION_REST.md), [`AI_ORDER_LEGACY_WEBHOOK_REMOVED_VI.md`](AI_ORDER_LEGACY_WEBHOOK_REMOVED_VI.md).

**Phạm vi (bản chụp + cập nhật):** `AiIntegrationOrdersController@store`, `StoreAiOrderWebhookRequest`, `config('app.ai_order_webhook_secret')`, `PM_READY_AI_WEBHOOK_STAGING_VI.md`, `AI_ORDER_WEBHOOK_SECRET_VA_CLIENT_CODE_VI.md`, UI `sales-order-settings`.  
**Ngày audit gốc:** 2026-05-12 · **Cập nhật routing:** 2026-05-13

**Ghi chú tài liệu (2026-05-12):** `PM_READY_AI_WEBHOOK_STAGING_VI.md` **không** còn chứa URL/secret staging hardcode; runbook dùng placeholder và hướng dẫn lấy giá trị từ **Sale order settings → API**.

---

## 1) API này “viết theo chuẩn gì”?

**Không** gắn với một **chuẩn công nghiệp đã đăng ký** kiểu **OpenAPI 3.x**, **JSON:API**, **OData**, hay **REST maturity model (Richardson)** đầy đủ.

| Khía cạnh      | Thực tế trong code                                                                                                                                                                                                                                                                      | Gọi tên gọn                                                                         |
| -------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ----------------------------------------------------------------------------------- |
| Giao thức      | HTTP **POST**, response **JSON** (`JsonResponse`)                                                                                                                                                                                                                                       | HTTP API / **RPC-style over HTTP**                                                  |
| Contract body  | Field cố định + `Illuminate\Foundation\Http\FormRequest` (`StoreAiOrderWebhookRequest`)                                                                                                                                                                                                 | **Laravel validation contract** (de facto schema)                                   |
| Content-Type   | Hỗ trợ merge JSON từ raw body trong `prepareForValidation()`; form-urlencoded / form-data cũng khớp runbook PM                                                                                                                                                                          | Không bắt buộc một RFC duy nhất; **linh hoạt theo Laravel**                         |
| Auth           | Secret **theo công ty** (`companies.ai_order_webhook_secret`) **hoặc** fallback `.env` `AI_ORDER_WEBHOOK_SECRET`; header **`X-AI-Webhook-Secret`** hoặc **`Authorization: Bearer`**, `hash_equals`. Với secret theo công ty: `company_id` trong body **phải** trùng công ty gắn secret. | **Custom shared secret** (không phải OAuth 2.0 đầy đủ; Bearer chỉ mang cùng secret) |
| Định tuyến     | `routes/api.php` — prefix **`integrations`**, middleware nhóm **`api`** + `ai.integration.auth` + `ai.integration.method`                                                                                                                                                               | REST-style path **`/api/integrations/orders`** (không còn segment secret trên URL)  |
| Idempotency    | Tùy chọn `external_event_id` — tra cứu `Order.note` LIKE `[ai_event:…]`                                                                                                                                                                                                                 | **Tự định nghĩa** (không phải `Idempotency-Key` chuẩn IETF)                         |
| Lỗi validation | **Froiden / RestAPI envelope** trên nhóm `api` — thường **422** + `error.details` (khác top-level `errors` Laravel mặc định một số chỗ)                                                                                                                                                 | Chuẩn **de facto** của package + Laravel                                            |

**Kết luận một dòng:** Đây là **integration endpoint** JSON của ứng dụng, **đặc tả bằng code + tài liệu nội bộ** (`PM_READY_*`, tab Sale order settings, `docs/AI_ORDER_INTEGRATION_REST*.md`), **không** có spec machine-readable (OpenAPI) đi kèm trong repo.

---

## 2) Bảng đối chiếu nhanh (audit chức năng)

| Hạng mục                                | Trạng thái                                                                       | Ghi chú                                                                                                                                                                                                               |
| --------------------------------------- | -------------------------------------------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Xác thực                                | Có                                                                               | Secret rỗng → 401; path và header phải khớp.                                                                                                                                                                          |
| Timing-safe compare                     | Có                                                                               | `hash_equals`.                                                                                                                                                                                                        |
| Phân tách tenant                        | Có (secret theo company) + validation `company_id` + `client_code` / `client_id` | Secret cột `companies.ai_order_webhook_secret` (unique); fallback env khi công ty chưa tạo secret. Khách: `client_code` → `client_details` theo `company_id`; hoặc `client_id` = `users.id` active cùng `company_id`. |
| CSRF                                    | **Không áp dụng** (nhóm `api`, không session web)                                | Trước đây (legacy): URI trong `$except` của `VerifyCsrfToken` cho `ai-order-webhook/*` — **đã gỡ**.                                                                                                                   |
| Idempotency                             | Có (pilot)                                                                       | Trùng `external_event_id` → JSON success + `duplicate`.                                                                                                                                                               |
| Kiểm tồn (Warehouse)                    | Có (tùy config)                                                                  | `WarehouseFlowConfigService` + `WarehouseAvailabilityService` → 422.                                                                                                                                                  |
| Tài liệu người dùng                     | Có                                                                               | `PM_READY_*` + UI settings + `docs/AI_ORDER_INTEGRATION_REST*.md`.                                                                                                                                                    |
| OpenAPI / Postman collection trong repo | **Chưa**                                                                         | Nếu cần audit ngoại vi: generate từ route + FormRequest.                                                                                                                                                              |
| Rate limit riêng endpoint               | **Chưa** (chung nhóm `web` throttle)                                             | Cân nhắc `RateLimiter` theo IP hoặc theo secret.                                                                                                                                                                      |
| Audit log DB                            | **Chưa**                                                                         | PM_READY đề xuất `ai_order_webhook_logs` (backlog).                                                                                                                                                                   |

---

## 3) Khuyến nghị sau audit (ưu tiên)

1. **Chuẩn hóa tài liệu kỹ thuật:** thêm **OpenAPI 3** (YAML) hoặc ít nhất bảng field — giảm lệch giữa PM_READY và validation thực tế.
2. **Bảo mật:** rotate secret, không log header có secret; xem HMAC / Tier B trong backlog tích hợp (Part 3 `SO_AI_WEBHOOK_PROMPTS_VI.md`).
3. **Vận hành:** rate limit + log structured (request id, `company_id`, kết quả).

---

## 4) Liên kết

- Khảo sát **toàn bộ route `/api`** và mức độ REST trong repo: [`SURVEY_SYSTEM_WIDE_API_AND_REST_VI.md`](SURVEY_SYSTEM_WIDE_API_AND_REST_VI.md)
- Runbook: [`PM_READY_AI_WEBHOOK_STAGING_VI.md`](PM_READY_AI_WEBHOOK_STAGING_VI.md)
- Secret DB + `client_code`: [`AI_ORDER_WEBHOOK_SECRET_VA_CLIENT_CODE_VI.md`](AI_ORDER_WEBHOOK_SECRET_VA_CLIENT_CODE_VI.md)
- Ringfence & prompt UI: [`../FUNC_IMPROVE/SO_AI_WEBHOOK_PROMPTS_VI.md#part-3-api-tab--ringfence-prompt`](../FUNC_IMPROVE/SO_AI_WEBHOOK_PROMPTS_VI.md#part-3-api-tab--ringfence-prompt)
- Phương án dài hạn REST/Sanctum: [`../FUNC_IMPROVE/12_AI_THIRDPARTY_SO_OPTIONS_VI.md`](../FUNC_IMPROVE/12_AI_THIRDPARTY_SO_OPTIONS_VI.md)
- Outbound Webhooks module (khác luồng): [`AUDIT_WEBHOOKS_MODULE_VI.md`](AUDIT_WEBHOOKS_MODULE_VI.md)

_Chỉ mục:_ [`INDEX.md`](INDEX.md).
