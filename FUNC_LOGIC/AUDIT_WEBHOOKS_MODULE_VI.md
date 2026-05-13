# Audit module Webhooks (`Modules/Webhooks`)

**Phạm vi:** `Modules/Webhooks/` — cấu hình đẩy sự kiện ra URL ngoài, UI quản lý, log, observer, job queue.  
**Không thuộc phạm vi:** inbound tạo đơn AI (**`POST /api/integrations/orders`**, `AiIntegrationOrdersController` — xem `PM_READY_AI_WEBHOOK_STAGING_VI.md`, `WH_PURCHASE_ENV_REFERENCE_VI.md`, `docs/AI_ORDER_INTEGRATION_REST.md`).  
**Ngày audit:** 2026-05-12

---

## 0) Inbound hay Outbound? (đối với ERP)

| Khái niệm    | Module `Modules/Webhooks`                                                                                                                                             |
| ------------ | --------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Outbound** | **Đúng.** ERP đăng ký URL + method; khi model được tạo (`created`), hệ thống **gọi HTTP ra ngoài** (Guzzle) tới endpoint khách hàng (Zapier, n8n, hệ thống ngoài, …). |
| **Inbound**  | **Không.** Module này **không** expose route public để bên ngoài POST vào ERP như một “receiver webhook”.                                                             |

**Thuật ngữ thống nhất với tài liệu kho:** xem mục _Thuật ngữ Inbound/Outbound_ trong [`WAREHOUSE_INDEX.md`](WAREHOUSE_INDEX.md) — tóm tắt: AI → ERP (**POST `/api/integrations/orders`**) = inbound phía ERP; module Webhooks = outbound.

---

## 1. Tổng quan

| Hạng mục                      | Giá trị                                                                                                                                        |
| ----------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------- |
| **nwidart**                   | `Webhooks` / alias `webhooks` (`module.json`)                                                                                                  |
| **Hằng module**               | `WebhooksGlobalSetting::MODULE_NAME` (dùng menu / sidebar)                                                                                     |
| **Service providers**         | `WebhooksServiceProvider` (observers, config, views, migrations), `EventServiceProvider` (`NewCompanyCreatedEvent` → `CompanyCreatedListener`) |
| **Luồng chính**               | Observer (hoặc observer riêng theo model) → `SendWebhook` job (queue) → HTTP client → `WebhooksLog`                                            |
| **Tài liệu triển khai model** | [`Modules/Webhooks/WEBHOOK_IMPLEMENTATION_REPORT.md`](../Modules/Webhooks/WEBHOOK_IMPLEMENTATION_REPORT.md)                                    |
| **Tests trong repo**          | `Modules/Webhooks/Tests/Feature/WebhookIntegrationTest.php` (integration + queue)                                                              |

---

## 2. Route web (`Routes/web.php`)

- **Prefix:** `account` + middleware **`auth`** (session).
- **Resource:** `webhooks`, `webhooks-log`; thêm `apply-quick-action`, `webhooks/{webhook}/duplicate`, `webhooks-for-variable/{webhookFor}`.
- **Không có** route công khai (guest) trong module này cho nhận payload từ internet.

---

## 3. API (`Routes/api.php`)

- `GET api/webhooks` (trong nhóm `api` + `auth:api`) trả về `$request->user()` — **placeholder**, không phản ánh cấu hình webhook outbound.
- **Rủi ro nhỏ:** nếu coi là API sản phẩm, endpoint không mang nghiệp vụ; chỉ user đã auth Sanctum/API.

---

## 4. Phân quyền

### 4.1. Permission (migration `2023_11_21_092115_add_webhooks_module.php`)

| Tên                  | Ghi chú                  |
| -------------------- | ------------------------ |
| `add_webhooks`       | Tạo/sửa cấu hình webhook |
| `view_webhooks`      | Xem danh sách cấu hình   |
| `view_webhooks_logs` | Xem log (custom)         |

### 4.2. Controller

- Middleware constructor: user phải có module `webhooks` trong `$this->user->modules`.
- Từng action: `abort_403` với `user()->permission('…') == 'all'` tương ứng (`WebhooksController`, `WebhooksLogController`).

### 4.3. Menu

- `resources/views/sections/menu.blade.php`: hiển thị mục Webhooks khi module bật **và** (`view_webhooks` hoặc `view_webhooks_logs`) == `all`.

---

## 5. Hành vi outbound & kỹ thuật

### 5.1. Kích hoạt

- `WebhooksServiceProvider::registerObservers()`: map `WebhooksSetting::WEBHOOK_FOR` → class Eloquent; đăng ký observer cụ thể hoặc `GenericObserver`.
- `GenericObserver::created()`: chỉ hook **`created`** — không tự động gửi trên `updated` / `deleted` trừ khi có observer riêng cho model đó.

### 5.2. Job `SendWebhook`

- Lọc `WebhooksSetting`: `company_id`, `status = active`, `webhook_for` khớp.
- Guzzle: `timeout` / `connect_timeout` 60; `http_errors => false`; **`verify => false`** (client không xác minh TLS chứng chỉ — **rủi ro bảo mật / compliance**, nên cân nhắc cấu hình theo môi trường).
- Ghi log response vào `webhooks_logs` (middleware map response).
- Log Guzzle tùy chọn: file `storage/logs/zapier-YYYY-MM-DD.log` (logger tên Zapier).

### 5.3. Hàng đợi

- `SendWebhook` implements `ShouldQueue` — phụ thuộc worker queue; delay mặc định trong observer ví dụ 5 giây (`GenericObserver`).

---

## 6. Danh mục file “cốt lõi” (không liệt kê từng file ngôn ngữ)

| Nhóm    | Đường dẫn gợi ý                                                                                                 |
| ------- | --------------------------------------------------------------------------------------------------------------- |
| HTTP    | `Http/Controllers/WebhooksController.php`, `WebhooksLogController.php`, `Http/Requests/StoreWebhookRequest.php` |
| Domain  | `Entities/WebhooksSetting.php`, `WebhooksLog.php`, `WebhooksRequest.php`, `WebhooksGlobalSetting.php`           |
| Infra   | `Jobs/SendWebhook.php`, `Observers/GenericObserver.php`, `Observers/*Observer.php`, `Providers/*`               |
| UI / DT | `DataTables/*`, `Resources/views/**`                                                                            |
| DB      | `Database/Migrations/**`, `Database/Seeders/WebhooksDatabaseSeeder.php`                                         |
| Config  | `Config/config.php`, `Config/webhooks.php`                                                                      |
| i18n    | `Resources/lang/**` (nhiều locale)                                                                              |

**Ghi chú:** Các file trong `Resources/lang/` là bản dịch UI; audit chức năng tập trung PHP/route/job ở trên.

---

## 7. Rủi ro & hướng cải thiện (ngắn)

1. **`verify => false` trên HTTP client outbound** — ưu tiên làm cấu hình được bật/tắt theo env (mặc định `true` production).
2. **Chỉ `created` trong `GenericObserver`** — nếu nghiệp vụ cần webhook khi cập nhật/xóa, cần mở rộng observer hoặc dùng event domain.
3. **API stub `GET api/webhooks`** — làm rõ (xóa, đổi tên, hoặc implement nếu có yêu cầu mobile/API).
4. **Secret / URL** lưu DB — đảm bảo backup DB và quyền `add_webhooks` được giao đúng role (đã có kiểm tra permission).

---

## 8. Liên kết chéo

- `MASTER_DOCUMENTATION.md` — mô tả module Webhooks (outbound, logs).
- `ai-context/modules/Webhooks/*` — snapshot kiến trúc (có thể lệch thời gian so với code).
- Webhook **inbound** AI: `FUNC_LOGIC/PM_READY_AI_WEBHOOK_STAGING_VI.md`.
