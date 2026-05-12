# Kế hoạch triển khai — AI → ERP tạo Sales Order (webhook inbound)

**Mục đích:** Theo dõi tiến độ triển khai tích hợp **bên AI / ai.craveva.com** gọi vào ERP để tạo **Sales Order** qua `POST /ai-order-webhook/{secret}`, và màn **Sale order settings → tab API** cung cấp thông tin copy cho bên AI.  
**Cập nhật lần cuối:** 2026-05-12  
**Owner / PM:** _(điền tên)_  
**Kỹ thuật dẫn dắt:** _(điền tên)_

---

## Tóm tắt kiến trúc (để không lệch phạm vi)

| Thành phần                       | Vai trò                                                                                                               |
| -------------------------------- | --------------------------------------------------------------------------------------------------------------------- |
| ERP                              | Nhận **một** HTTP POST đã xác thực; tạo `Order` + `OrderItems`. Secret **toàn instance** (`AI_ORDER_WEBHOOK_SECRET`). |
| Body JSON / form                 | Phải có `company_id` (tenant), `client_id`, `items`, … — xem `FUNC_LOGIC/PM_READY_AI_WEBHOOK_STAGING_VI.md`.          |
| Trang **Sale order settings**    | Hiển thị Base URL, `company_id` workspace đang chọn, URL POST + header (khi secret đã cấu hình).                      |
| Module **Webhooks** (ERP đẩy ra) | **Không** thay cho luồng này — xem `FUNC_LOGIC/AUDIT_WEBHOOKS_MODULE_VI.md`.                                          |

**Phương án dài hạn (REST Sanctum, v.v.):** `FUNC_IMPROVE/12_AI_THIRDPARTY_SALES_ORDER_INTEGRATION_OPTIONS_VI.md`.

---

## Chức năng này **nên / đang** nằm ở đâu? (timeline hiện tại — **không** gộp inbound vào `Modules/Webhooks`)

Theo kế hoạch **cũ** trong file này: **không** mở rộng `Modules/Webhooks` để làm inbound SO. Phân định sở hữu trong repo như sau để team và PM cùng ngôn ngữ:

| Phạm vi                                 | Nơi trong repo                                                                                      | Ghi chú                                                                                               |
| --------------------------------------- | --------------------------------------------------------------------------------------------------- | ----------------------------------------------------------------------------------------------------- |
| **Inbound webhook tạo đơn**             | `app/Http/Controllers/Integrations/AiOrderWebhookController.php`                                    | Core app, không phải Nwidart module.                                                                  |
| **Form request / validation payload**   | `app/Http/Requests/Integrations/StoreAiOrderWebhookRequest.php`                                     | Cùng namespace **Integrations**.                                                                      |
| **Trang cài đặt copy thông tin cho AI** | `app/Http/Controllers/SalesOrderSettingsController.php` + `resources/views/sales-order-settings/`   | Core app; menu settings (cạnh các mục finance/settings khác).                                         |
| **Route**                               | `routes/web-public.php` (`POST ai-order-webhook/{hash}`), `routes/web.php` (`sales-order-settings`) | Webhook công khai + trang account cần đăng nhập.                                                      |
| **Secret & Base URL**                   | `.env` → `config/app.php` (`ai_order_webhook_secret`, `url`)                                        | Toàn instance; không lưu trong bảng Webhooks module.                                                  |
| **Bật tính năng Orders trong product**  | Module key **`orders`** (`user_modules()`, `module_settings`)                                       | Đây là **tên module nghiệp vụ** trong app — **không** tồn tại thư mục `Modules/Orders` trong repo.    |
| **Module `Webhooks`**                   | `Modules/Webhooks`                                                                                  | Chỉ **outbound** (ERP → URL ngoài). **Không** đặt luồng inbound SO vào đây trong phạm vi rollout này. |
| **Warehouse (phụ)**                     | `Modules/Warehouse` (config `WAREHOUSE_AI_ORDER_WEBHOOK_*`, service kiểm tồn)                       | Chỉ ảnh hưởng validation tồn kho khi POST; không “sở hữu” endpoint.                                   |
| **Chuỗi UI**                            | `Modules/LanguagePack/Languages/app/*` (`modules.orders.*`, `app.menu.saleOrderSettings`)           | Bản dịch; có thể publish sang `resources/lang`.                                                       |

**Cách nói với stakeholder:** “**Tích hợp inbound Orders** (core **Integrations** + **Orders** trong app), **không** phải mở rộng module **Webhooks**.”

---

## Pha 0 — Đã có trong codebase (baseline)

Dùng làm mốc; tick khi xác nhận trên nhánh/triển khai thực tế của bạn.

- [x] Endpoint `POST /ai-order-webhook/{hash}` + `AiOrderWebhookController` + `StoreAiOrderWebhookRequest`
- [x] Route cài đặt `sales-order-settings.index` + `SalesOrderSettingsController` + view tab **API**
- [x] Menu sidebar settings (điều kiện `manage_finance_setting` + module `orders`)
- [x] Pest `tests/Feature/SalesOrderSettingsPageTest.php`
- [x] Chuỗi Language Pack (`modules.orders.*`, `app.menu.saleOrderSettings`) + merge từ module LanguagePack (`AppServiceProvider`)
- [ ] _(tùy chọn)_ `php artisan languagepack:publish-translation` trên môi trường chỉ đọc `resources/lang` đã publish

---

## Pha 1 — Chuẩn bị môi trường ERP (mỗi môi trường: dev / staging / production)

- [ ] **`APP_URL`**: đúng scheme + host công khai (HTTPS production); đây là **Base URL** copy sang AI
- [ ] **`AI_ORDER_WEBHOOK_SECRET`**: đặt giá trị dài, ngẫu nhiên; **không** commit vào git; lưu vault / runbook nội bộ
- [ ] Sau khi đổi `.env`: `php artisan config:clear` (hoặc quy trình cache config của deploy)
- [ ] Xác nhận trang **Sale order settings → API** hiển thị **POST URL** + dòng header (không còn cảnh báo secret thiếu)
- [ ] **Đa công ty:** chọn đúng workspace trên ERP → kiểm tra `company_id` hiển thị khớp tenant cần nhận đơn
- [ ] **Warehouse / tồn kho:** nắm flag `WAREHOUSE_AI_ORDER_WEBHOOK_CHECK_STOCK` (nếu dùng) — xem `WarehouseFlowConfigService`

---

## Pha 2 — Cấu hình phía AI (ai.craveva.com hoặc tương đương)

- [ ] Nhận từ ERP: **Base URL**, **URL POST đầy đủ**, **header `X-AI-Webhook-Secret`**, **`company_id`** (và tài liệu payload)
- [ ] Ưu tiên chế độ **Webhook / writeback** nếu sản phẩm AI hỗ trợ — khớp **một URL** POST cố định
- [ ] Nếu dùng **API-Only** + trường **API Version Path** (`/v1`): xác nhận sản phẩm AI **không** ghép sai path (luồng Craveva không phải REST CRUD `/v1/orders`); điều chỉnh theo hướng dẫn trên tab API ERP
- [ ] Map body JSON đúng field bắt buộc + `external_event_id` (idempotent)
- [ ] Môi trường AI (Sandbox / Production) khớp với ERP tương ứng

---

## Pha 3 — Kiểm thử (UAT / nghiệm thu)

- [ ] **curl** hoặc client HTTP: POST mẫu thành công (201) — tham chiếu `PM_READY_AI_WEBHOOK_STAGING_VI.md`
- [ ] Gửi lại cùng `external_event_id` → response duplicate / không tạo đơn trùng
- [ ] Sai secret / thiếu header → 401
- [ ] Sai `company_id` hoặc `client_id` không tồn tại → lỗi rõ ràng (422/404 theo code hiện tại)
- [ ] Kiểm tra đơn trên UI ERP: số tiền, dòng hàng, ghi chú / tag `[ai_event:…]`
- [ ] _(nếu bật kiểm tồn)_ payload vi phạm tồn → 422 warehouse

---

## Pha 4 — Go-live & vận hành

- [ ] Rotate secret staging → secret production riêng; cập nhật lại cấu hình AI
- [ ] Runbook sự cố: ai hết gọi được (DNS, TLS, WAF, rate limit)
- [ ] Người có quyền `manage_finance_setting` biết chỗ lấy thông tin API (đào tạo ngắn)
- [ ] _(tùy chọn)_ Giám sát log / alert trên endpoint webhook

---

## Pha 5 — Backlog (chưa cam kết)

- [ ] REST API có version (ví dụ Sanctum) tái sử dụng validation — tham chiếu file `12_…`
- [ ] Secret **theo company** thay vì toàn instance _(đổi contract + migration cấu hình)_
- [ ] Tab cài đặt bổ sung (ví dụ quy tắc đánh số SO) — tách khỏi tab API

---

## Tiến độ tổng hợp (PM điền %)

| Pha         | Trạng thái | Ghi chú |
| ----------- | ---------- | ------- |
| 0 Baseline  | ☐ %        |         |
| 1 ERP env   | ☐ %        |         |
| 2 AI config | ☐ %        |         |
| 3 UAT       | ☐ %        |         |
| 4 Go-live   | ☐ %        |         |
| 5 Backlog   | ☐ %        |         |

---

## Liên kết nhanh

| Tài liệu                                                                                                                                | Nội dung                                                |
| --------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------- |
| [`FUNC_LOGIC/PM_READY_AI_WEBHOOK_STAGING_VI.md`](../FUNC_LOGIC/PM_READY_AI_WEBHOOK_STAGING_VI.md)                                       | URL mẫu staging, curl, payload, response                |
| [`FUNC_IMPROVE/13_SALE_ORDER_AI_INTEGRATION_ROLLOUT_PROMPT_VI.md`](13_SALE_ORDER_AI_INTEGRATION_ROLLOUT_PROMPT_VI.md)                   | Mapping route/controller/view/test                      |
| [`FUNC_IMPROVE/12_AI_THIRDPARTY_SALES_ORDER_INTEGRATION_OPTIONS_VI.md`](12_AI_THIRDPARTY_SALES_ORDER_INTEGRATION_OPTIONS_VI.md)         | Webhook vs API dài hạn                                  |
| [`FUNC_IMPROVE/15_SALE_ORDER_AI_SETTINGS_GUIDE_AND_RINGFENCE_PROMPT_VI.md`](15_SALE_ORDER_AI_SETTINGS_GUIDE_AND_RINGFENCE_PROMPT_VI.md) | Prompt triển khai hướng dẫn UI + ringfence theo company |
| [`FUNC_LOGIC/DESIGN_BACKEND_UI_UX_DESIGN_SPEC_VI.md`](../FUNC_LOGIC/DESIGN_BACKEND_UI_UX_DESIGN_SPEC_VI.md)                             | Nguyên tắc UI settings                                  |
