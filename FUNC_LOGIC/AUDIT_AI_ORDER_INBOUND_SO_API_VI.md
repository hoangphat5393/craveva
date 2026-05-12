# Audit — API inbound tạo Sales Order (AI webhook)

**Phạm vi:** `POST /ai-order-webhook/{hash}` → `AiOrderWebhookController@store`, `StoreAiOrderWebhookRequest`, cấu hình `config('app.ai_order_webhook_secret')`, tài liệu vận hành `PM_READY_AI_WEBHOOK_STAGING_VI.md`, UI `sales-order-settings`.  
**Ngày audit:** 2026-05-12

---

## 1) API này “viết theo chuẩn gì”?

**Không** gắn với một **chuẩn công nghiệp đã đăng ký** kiểu **OpenAPI 3.x**, **JSON:API**, **OData**, hay **REST maturity model (Richardson)** đầy đủ.

| Khía cạnh      | Thực tế trong code                                                                                             | Gọi tên gọn                                                                               |
| -------------- | -------------------------------------------------------------------------------------------------------------- | ----------------------------------------------------------------------------------------- |
| Giao thức      | HTTP **POST**, response **JSON** (`JsonResponse`)                                                              | HTTP API / **RPC-style over HTTP**                                                        |
| Contract body  | Field cố định + `Illuminate\Foundation\Http\FormRequest` (`StoreAiOrderWebhookRequest`)                        | **Laravel validation contract** (de facto schema)                                         |
| Content-Type   | Hỗ trợ merge JSON từ raw body trong `prepareForValidation()`; form-urlencoded / form-data cũng khớp runbook PM | Không bắt buộc một RFC duy nhất; **linh hoạt theo Laravel**                               |
| Auth           | Shared secret: **segment URL `{hash}`** + header **`X-AI-Webhook-Secret`**, so khớp `hash_equals` với `config` | **Custom bearer-like shared secret** (không phải OAuth 2.0 / RFC 6750 Bearer token chuẩn) |
| Định tuyến     | `routes/web-public.php` (middleware nhóm **web**), không nằm `routes/api.php`                                  | Cùng **pattern public webhook** với các cổng thanh toán (`*-webhook/{hash}`) trong repo   |
| Idempotency    | Tùy chọn `external_event_id` — tra cứu `Order.note` LIKE `[ai_event:…]`                                        | **Tự định nghĩa** (không phải `Idempotency-Key` chuẩn IETF)                               |
| Lỗi validation | Laravel mặc định **422** + envelope `message` / `errors`                                                       | Chuẩn **de facto** của Laravel / nhiều API JSON                                           |

**Kết luận một dòng:** Đây là **webhook inbound / integration endpoint** của ứng dụng Laravel, **đặc tả bằng code + tài liệu nội bộ** (`PM_READY_*`, tab Sale order settings), **không** có spec machine-readable (OpenAPI) đi kèm trong repo.

---

## 2) Bảng đối chiếu nhanh (audit chức năng)

| Hạng mục                                | Trạng thái                           | Ghi chú                                                                                                                                           |
| --------------------------------------- | ------------------------------------ | ------------------------------------------------------------------------------------------------------------------------------------------------- |
| Xác thực                                | Có                                   | Secret rỗng → 401; path và header phải khớp.                                                                                                      |
| Timing-safe compare                     | Có                                   | `hash_equals`.                                                                                                                                    |
| Phân tách tenant                        | Một phần                             | `company_id` + `client_id` cùng company (Rule `exists` scoped). Secret **toàn instance** — xem `FUNC_IMPROVE/15_*` Tier B nếu cần ringfence mạnh. |
| CSRF                                    | N/A đúng chỗ                         | URI trong `$except` của `VerifyCsrfToken` cho `ai-order-webhook/*`.                                                                               |
| Idempotency                             | Có (pilot)                           | Trùng `external_event_id` → JSON success + `duplicate`.                                                                                           |
| Kiểm tồn (Warehouse)                    | Có (tùy config)                      | `WarehouseFlowConfigService` + `WarehouseAvailabilityService` → 422.                                                                              |
| Tài liệu người dùng                     | Có                                   | PM*READY + UI settings + `15*\*`.                                                                                                                 |
| OpenAPI / Postman collection trong repo | **Chưa**                             | Nếu cần audit ngoại vi: generate từ route + FormRequest.                                                                                          |
| Rate limit riêng endpoint               | **Chưa** (chung nhóm `web` throttle) | Cân nhắc `RateLimiter` theo IP hoặc theo secret.                                                                                                  |
| Audit log DB                            | **Chưa**                             | PM_READY đề xuất `ai_order_webhook_logs` (backlog).                                                                                               |

---

## 3) Khuyến nghị sau audit (ưu tiên)

1. **Chuẩn hóa tài liệu kỹ thuật:** thêm **OpenAPI 3** (YAML) hoặc ít nhất bảng field — giảm lệch giữa PM_READY và validation thực tế.
2. **Bảo mật:** rotate secret, không log full URL có secret; xem HMAC / Tier B trong `15_*`.
3. **Vận hành:** rate limit + log structured (request id, `company_id`, kết quả).

---

## 4) Liên kết

- Khảo sát **toàn bộ route `/api`** và mức độ REST trong repo: [`SURVEY_SYSTEM_WIDE_API_AND_REST_VI.md`](SURVEY_SYSTEM_WIDE_API_AND_REST_VI.md)
- Runbook: [`PM_READY_AI_WEBHOOK_STAGING_VI.md`](PM_READY_AI_WEBHOOK_STAGING_VI.md)
- Ringfence & prompt UI: [`../FUNC_IMPROVE/15_SALE_ORDER_AI_SETTINGS_GUIDE_AND_RINGFENCE_PROMPT_VI.md`](../FUNC_IMPROVE/15_SALE_ORDER_AI_SETTINGS_GUIDE_AND_RINGFENCE_PROMPT_VI.md)
- Phương án dài hạn REST/Sanctum: [`../FUNC_IMPROVE/12_AI_THIRDPARTY_SALES_ORDER_INTEGRATION_OPTIONS_VI.md`](../FUNC_IMPROVE/12_AI_THIRDPARTY_SALES_ORDER_INTEGRATION_OPTIONS_VI.md)
- Outbound Webhooks module (khác luồng): [`AUDIT_WEBHOOKS_MODULE_VI.md`](AUDIT_WEBHOOKS_MODULE_VI.md)

_Chỉ mục:_ [`INDEX.md`](INDEX.md).
