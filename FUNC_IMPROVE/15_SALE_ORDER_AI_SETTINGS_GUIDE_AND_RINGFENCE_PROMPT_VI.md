# Prompt triển khai — Hướng dẫn tích hợp API (Sale order settings) + ringfence theo công ty

**Mục đích:** Tài liệu + **prompt Cursor (Agent mode)** để hoàn thiện trang **Company → Sale order settings → tab API**, đồng bộ với runbook [`FUNC_LOGIC/PM_READY_AI_WEBHOOK_STAGING_VI.md`](../FUNC_LOGIC/PM_READY_AI_WEBHOOK_STAGING_VI.md), và **giảm rủi ro tạo đơn nhầm công ty**.  
**Ngày:** 2026-05-12  
**Triển khai code Tier A (UI + ringfence UX + curl mẫu):** đã gắn vào `SalesOrderSettingsController`, `resources/views/sales-order-settings/index.blade.php`, `modules.orders.*` (en/vi/zh-CN/zh-TW). **Tier B** (token/secret theo company trong DB) vẫn là tùy chọn — dùng prompt mục 3 nếu PM bật.

---

## 1) Hiện trạng kỹ thuật (đừng mơ hồ khi viết prompt)

| Thành phần                      | Hành vi hiện tại                                                                                                                                                            |
| ------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Xác thực                        | `AI_ORDER_WEBHOOK_SECRET` trong `.env` → `config('app.ai_order_webhook_secret')` — **một secret cho cả instance** (`config/app.php`).                                       |
| URL                             | `POST {APP_URL}/ai-order-webhook/{hash}` với `hash` = secret; header `X-AI-Webhook-Secret` phải khớp.                                                                       |
| Phân tách công ty trong payload | `company_id` bắt buộc; `client_id` phải thuộc đúng `company_id` (validation trong `StoreAiOrderWebhookRequest`).                                                            |
| Trang settings                  | `SalesOrderSettingsController` + `resources/views/sales-order-settings/index.blade.php` — hiển thị Base URL, `company_id` session, URL + header **khi** secret đã cấu hình. |

**Hệ quả:** “Ringfence” hiện tại chủ yếu là **logic** (đúng `company_id` + `client_id` trong JSON), **chưa** phải “mỗi công ty một URL/secret độc lập” ở tầng mạng. Muốn **mỗi company một API ringfence** (lộ URL công ty A không thể ghi đơn vào công ty B) cần **Tier B** bên dưới.

---

## 2) Mục tiêu sản phẩm (PM)

1. Người dùng có quyền mở **Sale order settings → API** và **tự triển khai** tích hợp bên ngoài (AI, middleware, script) **không cần đọc repo**.
2. Nội dung trên UI **khớp** field / flow trong `PM_READY_AI_WEBHOOK_STAGING_VI.md` (Base URL, POST URL, header, payload tối thiểu, idempotency, mã lỗi).
3. **Ringfence:**
    - **Tier A (tối thiểu):** UX + văn bản + checklist: “một cấu hình tích hợp bên ngoài = một công ty ERP”; nhắc đổi workspace trước khi copy; cảnh báo nếu `company_id` trong JSON ≠ công ty đang chọn (nếu có thể so client-side / chỉ hiển thị cảnh báo).
    - **Tier B (khuyến nghị nếu PM yêu cầu an toàn mạnh):** **Token/secret riêng theo `company_id`**; URL webhook **chỉ** chấp nhận đơn cho đúng công ty gắn với token (bỏ hoặc ghi đè `company_id` trong body theo token — thiết kế rõ trong code + test).

---

## 3) Prompt Cursor — copy vào Agent mode (điều chỉnh Tier A/B theo PM)

```text
Bối cảnh: Laravel 11, đa company. Inbound tạo Sales Order: POST /ai-order-webhook/{hash} (AiOrderWebhookController, StoreAiOrderWebhookRequest). Secret hiện tại: config('app.ai_order_webhook_secret') toàn instance. Runbook PM: FUNC_LOGIC/PM_READY_AI_WEBHOOK_STAGING_VI.md. Trang hướng dẫn: GET sales-order-settings (SalesOrderSettingsController), view resources/views/sales-order-settings/index.blade.php. Chuỗi: Modules/LanguagePack/Languages/app/en|vi|zh-CN|zh-TW modules.orders.*.

Mục tiêu:
1) Đồng bộ nội dung tab “API” với PM_READY_AI_WEBHOOK_STAGING_VI.md: payload tối thiểu, external_event_id, Accept header, ví dụ curl rút gọn (có thể partial + link “xem đầy đủ trong repo” nếu không muốn dài trên UI).
2) Hiển thị rõ “Công ty ERP đang chọn” (company_name + company_id) để người dùng tin tưởng số copy sang bên ngoài.
3) Tier A — Ringfence UX: thêm cảnh báo (alert/info) kiểu: mỗi tích hợp bên ngoài chỉ được dùng một company_id; luôn chọn đúng workspace ERP trước khi copy; client_id phải là khách hàng thuộc đúng company (đã validate server-side — ghi chú trên UI).
4) (Chỉ làm nếu PM bật Tier B) Ringfence kỹ thuật: secret/token riêng theo company.
   - Migration + model: lưu token ngẫu nhiên per company (ví dụ bảng company_ai_order_webhook_tokens: company_id, token, timestamps) hoặc cột nullable trên companies — chọn cách nhất quán với convention DB hiện có.
   - AiOrderWebhookController: nếu path khớp token per-company → chỉ cho tạo Order với company_id của token đó (bỏ qua hoặc so khớp cứng company_id trong body).
   - Giữ backward compatibility với AI_ORDER_WEBHOOK_SECRET toàn instance nếu token per-company chưa bật (hoặc company chưa có token) — mô tả rõ trong PR.
   - Trang Sale order settings: hiển thị URL riêng của company khi có token; nút “Tạo / làm mới token” (permission manage_finance_setting); cảnh báo rotate.
5) Pest: cập nhật tests/Feature/SalesOrderSettingsPageTest.php (thấy chuỗi mới / company name). Nếu Tier B: thêm feature test POST webhook với token company A không tạo đơn cho company B.
6) vendor/bin/pint --dirty --format agent; php artisan test --compact <file test đã đụng>.

Ràng buộc: không thêm dependency composer không được phê duyệt; không xóa test cũ; tuân convention Form Request / naming hiện có.
```

---

## 4) Checklist nghiệm thu (PM / QA)

- [ ] Tab API đọc được **không cần developer** (tiếng Việt + EN tối thiểu).
- [ ] Có **Base URL**, **POST URL**, **Header**, **`company_id` + tên công ty** (khi Tier A/B có tên).
- [ ] Có đoạn về **`external_event_id`** và retry idempotent.
- [ ] Secret chưa cấu hình → vẫn có hướng dẫn rõ (admin `.env`) như hiện tại.
- [ ] (Tier B) Rotate token công ty A không ảnh hưởng URL công ty B.
- [ ] (Tier B) POST đúng token nhưng body `company_id` khác → **422 hoặc 403** (theo thiết kế đã chọn), không tạo đơn nhầm.

---

## 5) Liên kết

- **Audit “API viết theo chuẩn gì”:** [`../FUNC_LOGIC/AUDIT_AI_ORDER_INBOUND_SO_API_VI.md`](../FUNC_LOGIC/AUDIT_AI_ORDER_INBOUND_SO_API_VI.md)
- Runbook curl & payload: [`../FUNC_LOGIC/PM_READY_AI_WEBHOOK_STAGING_VI.md`](../FUNC_LOGIC/PM_READY_AI_WEBHOOK_STAGING_VI.md)
- Kế hoạch rollout tổng: [`14_SALE_ORDER_AI_WEBHOOK_ROLLOUT_PLAN_VI.md`](14_SALE_ORDER_AI_WEBHOOK_ROLLOUT_PLAN_VI.md)
- Phương án API dài hạn: [`12_AI_THIRDPARTY_SALES_ORDER_INTEGRATION_OPTIONS_VI.md`](12_AI_THIRDPARTY_SALES_ORDER_INTEGRATION_OPTIONS_VI.md)

_Chỉ mục:_ [`INDEX.md`](INDEX.md).
