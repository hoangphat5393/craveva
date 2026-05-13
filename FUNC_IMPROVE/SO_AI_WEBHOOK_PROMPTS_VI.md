# Sale Order + AI webhook — rollout, checklist & prompt (gộp)

> **Gộp (2026-05-12):** 13_SALE_ORDER_AI_INTEGRATION_ROLLOUT_PROMPT_VI.md + 14_SALE_ORDER_AI_WEBHOOK_ROLLOUT_PLAN_VI.md + 15_SALE_ORDER_AI_SETTINGS_GUIDE_AND_RINGFENCE_PROMPT_VI.md. **Phương án dài hạn:** vẫn đọc 12_AI_THIRDPARTY_SO_OPTIONS_VI.md.

---

## Part 1 — Repo audit & rollout prompt {#part-1-repo-audit--prompt}

**Mục đích:** Xác nhận đã có API tạo Sales Order nào **ngoài** luồng inbound hiện tại; cung cấp **prompt Cursor** để lên kế hoạch / triển khai trang **Sale order settings** + hoàn thiện tích hợp cho khách.  
**Ngày:** 2026-05-12

---

## 1. Kết quả kiểm tra (toàn dự án, không chỉ Company Settings)

### 1.1. `FUNC_LOGIC/AUDIT_WEBHOOKS_MODULE_VI.md` là gì?

- Đây là **tài liệu audit** cho module **`Modules/Webhooks`** (ERP **đẩy** event **ra** URL ngoài — **outbound**).
- **Không** mô tả API tạo Sales Order; **không** thay cho tài liệu inbound AI.

### 1.2. Có API tạo Sales Order nào khác ngoài inbound “tạm / pilot” không?

| Nguồn                                      | Kết luận                                                                                                                                                                                                                                                                                     |
| ------------------------------------------ | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `routes/api.php` (nhóm `ApiRoute`)         | Chỉ thấy **`GET purchased-module`** → `HomeController@installedModule`. **Không** có `POST`/`api/.../orders`.                                                                                                                                                                                |
| `routes/web.php` (nhóm `account` + `auth`) | `OrderController` resource `orders` — tạo đơn qua **`store(PlaceOrder)`** là **web session + form**, không phải API machine-to-machine cho third-party.                                                                                                                                      |
| `routes/web-public.php`                    | **`POST ai-order-webhook/{hash}`** → `AiOrderWebhookController@store` — **đây là endpoint JSON duy nhất** trong repo cho luồng “bên ngoài POST vào → tạo `Order` + `OrderItems`” (`StoreAiOrderWebhookRequest`; secret **theo công ty** hoặc fallback **`.env` `AI_ORDER_WEBHOOK_SECRET`**). |
| Import SO                                  | `OrderController` import / `ImportSalesOrderChunkJob` — **upload file / queue**, không phải REST contract cho AI real-time.                                                                                                                                                                  |

**Kết luận:** Ngoài **`POST /ai-order-webhook/{hash}`**, **chưa có** REST API chuẩn (ví dụ Sanctum `api/v1/orders`) dành cho third-party tạo Sales Order. Company Settings / Purchase Settings **không** chứa endpoint tạo SO qua API trong code đã quét.

**Tài liệu inbound AI (payload, curl):** `FUNC_LOGIC/PM_READY_AI_WEBHOOK_STAGING_VI.md`.  
**Phương án dài hạn (API vs webhook):** `FUNC_IMPROVE/12_AI_THIRDPARTY_SO_OPTIONS_VI.md`.

---

## 2. Prompt gợi ý cho Cursor (copy vào chat Agent mode)

Dùng prompt dưới đây khi muốn agent **lên kế hoạch + code** (điều chỉnh phạm vi nếu chỉ cần plan).

```text
Bối cảnh repo: Laravel 11, đa company. Tạo Sales Order từ bên ngoài hiện chỉ có POST /ai-order-webhook/{hash} (AiOrderWebhookController, StoreAiOrderWebhookRequest). Xác thực: secret trên companies.ai_order_webhook_secret (ưu tiên, UI Sale order settings) hoặc fallback AI_ORDER_WEBHOOK_SECRET trong .env. routes/api.php không có resource orders. OrderController@store là web + session.

Mục tiêu triển khai (ưu tiên theo thứ tự):
1) Thêm mục cài đặt giống pattern "Purchase Settings" (sidebar settings): ví dụ "Sale order settings" hoặc "Sales order integration", nằm cạnh các mục settings hiện có; URL dạng account/...; middleware auth + permission phù hợp (chỉ admin/IT xem secret hướng dẫn).
2) Trang có ít nhất một tab **"API"** (nhãn UI ngắn gọn; nội dung là hướng dẫn tích hợp AI / third-party tạo SO). Tab khác (ví dụ đánh số SO) tách riêng nếu sau này có. Trong tab **API**: hiển thị read-only Base URL (app.url), đường dẫn đầy đủ POST /ai-order-webhook/{hash} (hash = secret đã cấu hình — hoặc chỉ hiển thị phần path + nhắc cấu hình env nếu không muốn lộ secret trên UI), header X-AI-Webhook-Secret, company_id của tenant hiện tại, link tài liệu nội bộ PM_READY_AI_WEBHOOK_STAGING_VI.md, gợi ý external_event_id để idempotent.
3) Không nhét vào form Company Settings (tên/phone). Tuân theo DESIGN_BACKEND_UI_UX_VI.md và convention blade/menu hiện có (tìm purchase-settings, sidebar settings partial).
4) Viết ít nhất một Pest feature test: user có quyền truy cập trang mới thấy 200 và thấy company_id; user không có quyền bị 403 (hoặc policy tương đương).
5) (Tùy chọn pha 2, liệt kê trong PR mô tả chứ chưa code nếu chưa được yêu cầu) Sanctum POST api/v1/companies/{company}/orders tái sử dụng validation/service chung với AiOrderWebhookController; secret theo company.

Ràng buộc: không đổi dependencies composer không được phê duyện; chạy vendor/bin/pint --dirty --format agent trên PHP đã sửa; test tối thiểu php artisan test --compact <file test mới>.
```

---

## 3. Liên kết

- [`12_AI_THIRDPARTY_SO_OPTIONS_VI.md`](12_AI_THIRDPARTY_SO_OPTIONS_VI.md)
- [`../FUNC_LOGIC/PM_READY_AI_WEBHOOK_STAGING_VI.md`](../FUNC_LOGIC/PM_READY_AI_WEBHOOK_STAGING_VI.md)
- [`../FUNC_LOGIC/DESIGN_BACKEND_UI_UX_VI.md`](../FUNC_LOGIC/DESIGN_BACKEND_UI_UX_VI.md)

_Chỉ mục:_ [`INDEX.md`](INDEX.md).

---

## 4. Triển khai trong repo (2026-05-12)

- **Route:** `GET /account/sales-order-settings` — `sales-order-settings.index`
- **Controller:** `App\Http\Controllers\SalesOrderSettingsController`
- **View:** `resources/views/sales-order-settings/index.blade.php` (tab **API**)
- **Menu:** `resources/views/components/setting-sidebar.blade.php` (sau Finance Settings; điều kiện `manage_finance_setting` + module `orders`)
- **Test:** `tests/Feature/SalesOrderSettingsPageTest.php`
- **Chuỗi:** `Modules/LanguagePack/Languages/app/en|vi` — `app.menu.saleOrderSettings`, `modules.orders.*` (API)

**Kế hoạch triển khai + checklist theo dõi tiến độ:** [Part 2 trong file này](#part-2-rollout-plan--checklist).

---

## Part 2 — Rollout plan & checklist {#part-2-rollout-plan--checklist}

**Mục đích:** Theo dõi tiến độ triển khai tích hợp **bên AI / ai.craveva.com** gọi vào ERP để tạo **Sales Order** qua `POST /ai-order-webhook/{secret}`, và màn **Sale order settings → tab API** cung cấp thông tin copy cho bên AI.  
**Cập nhật lần cuối:** 2026-05-12  
**Owner / PM:** _(điền tên)_  
**Kỹ thuật dẫn dắt:** _(điền tên)_

---

## Tóm tắt kiến trúc (để không lệch phạm vi)

| Thành phần                       | Vai trò                                                                                                                                                                                                                                         |
| -------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| ERP                              | Nhận **một** HTTP POST đã xác thực; tạo `Order` + `OrderItems`. Secret **theo công ty** (`companies.ai_order_webhook_secret`, UI) hoặc fallback **toàn instance** (`.env` `AI_ORDER_WEBHOOK_SECRET` → `config('app.ai_order_webhook_secret')`). |
| Body JSON / form                 | Phải có `company_id` (tenant), **`client_code` và/hoặc `client_id`** (ít nhất một), `items`, … — xem `FUNC_LOGIC/PM_READY_AI_WEBHOOK_STAGING_VI.md` và `AI_ORDER_WEBHOOK_SECRET_VA_CLIENT_CODE_VI.md`.                                          |
| Trang **Sale order settings**    | Hiển thị Base URL, `company_id` workspace đang chọn, URL POST + header (khi secret đã cấu hình).                                                                                                                                                |
| Module **Webhooks** (ERP đẩy ra) | **Không** thay cho luồng này — xem `FUNC_LOGIC/AUDIT_WEBHOOKS_MODULE_VI.md`.                                                                                                                                                                    |

**Phương án dài hạn (REST Sanctum, v.v.):** `FUNC_IMPROVE/12_AI_THIRDPARTY_SO_OPTIONS_VI.md`.

---

## Chức năng này **nên / đang** nằm ở đâu? (timeline hiện tại — **không** gộp inbound vào `Modules/Webhooks`)

Theo kế hoạch **cũ** trong file này: **không** mở rộng `Modules/Webhooks` để làm inbound SO. Phân định sở hữu trong repo như sau để team và PM cùng ngôn ngữ:

| Phạm vi                                 | Nơi trong repo                                                                                                                                                                                         | Ghi chú                                                                                               |
| --------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ | ----------------------------------------------------------------------------------------------------- |
| **Inbound webhook tạo đơn**             | `app/Http/Controllers/Integrations/AiOrderWebhookController.php`                                                                                                                                       | Core app, không phải Nwidart module.                                                                  |
| **Form request / validation payload**   | `app/Http/Requests/Integrations/StoreAiOrderWebhookRequest.php`                                                                                                                                        | Cùng namespace **Integrations**.                                                                      |
| **Trang cài đặt copy thông tin cho AI** | `app/Http/Controllers/SalesOrderSettingsController.php` + `resources/views/sales-order-settings/`                                                                                                      | Core app; menu settings (cạnh các mục finance/settings khác).                                         |
| **Route**                               | `routes/web-public.php` (`POST ai-order-webhook/{hash}`), `routes/web.php` (`sales-order-settings`)                                                                                                    | Webhook công khai + trang account cần đăng nhập.                                                      |
| **Secret & Base URL**                   | Cột **`companies.ai_order_webhook_secret`** + UI regenerate; fallback **`config('app.ai_order_webhook_secret')`** từ `.env` khi công ty chưa có secret. **Base URL** từ `config('app.url')` / tab API. |
| **Bật tính năng Orders trong product**  | Module key **`orders`** (`user_modules()`, `module_settings`)                                                                                                                                          | Đây là **tên module nghiệp vụ** trong app — **không** tồn tại thư mục `Modules/Orders` trong repo.    |
| **Module `Webhooks`**                   | `Modules/Webhooks`                                                                                                                                                                                     | Chỉ **outbound** (ERP → URL ngoài). **Không** đặt luồng inbound SO vào đây trong phạm vi rollout này. |
| **Warehouse (phụ)**                     | `Modules/Warehouse` (config `WAREHOUSE_AI_ORDER_WEBHOOK_*`, service kiểm tồn)                                                                                                                          | Chỉ ảnh hưởng validation tồn kho khi POST; không “sở hữu” endpoint.                                   |
| **Chuỗi UI**                            | `Modules/LanguagePack/Languages/app/*` (`modules.orders.*`, `app.menu.saleOrderSettings`)                                                                                                              | Bản dịch; có thể publish sang `resources/lang`.                                                       |

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
- [ ] Sai `company_id` hoặc khách không hợp lệ → **422** validation (thông điệp theo `StoreAiOrderWebhookRequest`)
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
- [x] Secret **theo company** (`companies.ai_order_webhook_secret` + UI **Sale order settings → API**) — **đã có**; vẫn giữ fallback `.env` tùy chính sách vận hành
- [ ] _(tùy chọn)_ Bỏ hoàn toàn fallback `.env` / bắt buộc secret per company mọi tenant _(đổi policy + thông báo UI)_
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

| Tài liệu                                                                                                                                | Nội dung                                                                                           |
| --------------------------------------------------------------------------------------------------------------------------------------- | -------------------------------------------------------------------------------------------------- |
| [`FUNC_LOGIC/PM_READY_AI_WEBHOOK_STAGING_VI.md`](../FUNC_LOGIC/PM_READY_AI_WEBHOOK_STAGING_VI.md)                                       | Runbook PM: curl, payload, response; **không** hardcode secret trong repo — copy URL/header từ ERP |
| [Part 1 — repo audit & prompt](#part-1-repo-audit--prompt)                                                         | Mapping route/controller/view/test                                                                 |
| [`FUNC_IMPROVE/12_AI_THIRDPARTY_SO_OPTIONS_VI.md`](12_AI_THIRDPARTY_SO_OPTIONS_VI.md)         | Webhook vs API dài hạn                                                                             |
| [Part 3 — API tab & ringfence](#part-3-api-tab--ringfence-prompt)                                                  | Prompt triển khai hướng dẫn UI + ringfence theo company                                            |
| [`FUNC_LOGIC/DESIGN_BACKEND_UI_UX_VI.md`](../FUNC_LOGIC/DESIGN_BACKEND_UI_UX_VI.md)                             | Nguyên tắc UI settings                                                                             |

---

## Part 3 — API tab & ringfence prompt {#part-3-api-tab--ringfence-prompt}

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
Bối cảnh: Laravel 11, đa company. Inbound tạo Sales Order: `POST /ai-order-webhook/{hash}` (`AiOrderWebhookController`, `StoreAiOrderWebhookRequest`). Secret: ưu tiên **`companies.ai_order_webhook_secret`** (UI **Sale order settings → API**); fallback **`config('app.ai_order_webhook_secret')`** từ `.env` khi công ty chưa tạo secret. Runbook: `FUNC_LOGIC/PM_READY_AI_WEBHOOK_STAGING_VI.md`. Trang hướng dẫn: `GET sales-order-settings` (`SalesOrderSettingsController`), view `resources/views/sales-order-settings/index.blade.php`. Chuỗi: `Modules/LanguagePack/Languages/app/en|vi|zh-CN|zh-TW` `modules.orders.*`.

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
- Kế hoạch rollout tổng: [Part 2 trong file này](#part-2-rollout-plan--checklist)
- Phương án API dài hạn: [`12_AI_THIRDPARTY_SO_OPTIONS_VI.md`](12_AI_THIRDPARTY_SO_OPTIONS_VI.md)

_Chỉ mục:_ [`INDEX.md`](INDEX.md).