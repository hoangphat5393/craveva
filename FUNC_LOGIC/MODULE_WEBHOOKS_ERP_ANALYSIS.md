# Phân tích module Webhooks — Craveva ERP (Laravel)

**Phạm vi:** `Modules/Webhooks` (controllers, entities, job, observers, routes, migrations, config).  
**Mục đích tài liệu:** Tổng hợp chức năng, luồng dữ liệu, rủi ro và gợi ý cải tiến (mục 1–8).

---

## 1. Functional Overview

**Mục đích:** Module cung cấp **webhook đi (outbound)**: khi có sự kiện trong ERP (tạo/cập nhật model, v.v.), hệ thống **gửi HTTP request** tới URL đã cấu hình (JSON hoặc form), kèm header/body tùy chỉnh, và **ghi log** phản hồi.

**Vấn đề nghiệp vụ:** Tích hợp với hệ thống bên ngoài (Zapier, automation, CRM, kho riêng, v.v.) **không cần sửa core ERP** — chỉ cần cấu hình endpoint và mapping field/placeholder.

**Lưu ý kiến trúc:** Đây **không phải** webhook **nhận (inbound)** từ LINE/WhatsApp hay AI. Luồng “chat → AI → ghi đơn vào DB” cần **endpoint nhận** + **API tạo đơn**; module này **không** thay thế bước đó.

---

## 2. Main Features

| Tính năng                        | Mô tả                                                                                                                                                |
| -------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Quản lý webhook**              | CRUD qua `WebhooksController`: tên, entity (`webhook_for`), URL, method, định dạng JSON/form, trạng thái active/inactive.                            |
| **Header & body tĩnh/biến**      | `WebhooksRequest`: lưu từng dòng header hoặc body; giá trị body có thể là placeholder (vd. `##EMAIL##`) map sang field payload qua enum `*Variable`. |
| **Placeholder theo loại entity** | `webhooksForVariable()` trả enum cho một số loại: Client, Employee, Invoice, Lead, Project, Proposal, Task.                                          |
| **Sao chép webhook**             | `duplicate()` clone setting + headers + body.                                                                                                        |
| **Quick actions**                | Đổi `status`, `webhook_for`, `run_debug` từng dòng; bulk đổi status / xóa.                                                                           |
| **Nhật ký gọi**                  | `WebhooksLogController` + `WebhookLogsDataTable`: xem log request/response; xóa / bulk xóa.                                                          |
| **Kích hoạt theo sự kiện model** | Observers đăng ký động theo `WebhooksSetting::WEBHOOK_FOR` + `modelMap` trong `WebhooksServiceProvider`; job `SendWebhook` chạy queue.               |
| **Công ty mới**                  | `CompanyCreatedListener` → `WebhooksGlobalSetting::addModuleSetting()` (entry `ModuleSetting` cho role).                                             |
| **Addon / license**              | `Config/config.php`: `verification_required`, `parent_min_version`, tên script module.                                                               |

---

## 3. Business Flow

1. User có module `webhooks` và quyền tương ứng → vào `/account/webhooks`, tạo webhook (validate tối thiểu `name`, `request_url` trong `StoreWebhookRequest`).
2. **Lưu DB:** một bản ghi `webhooks_settings` + nhiều `webhooks_requests` (headers/body).
3. **Runtime:** Khi model được observer bắt (vd. `created`, hoặc `saved` với điều kiện riêng như Invoice), gọi `SendWebhook::dispatch(...)->delay(5)->onQueue('default')`.
4. **Job `SendWebhook`:** Lấy mọi webhook **active** của đúng `company_id` và đúng `webhook_for` → `mapData` (substitute placeholder nếu có enum) → `mapHeaders` → Guzzle `request()` → middleware `mapResponse` → `saveData` tạo `webhooks_logs` (method, URL, headers JSON, raw payload, response body, status code).

---

## 4. Data & Database

**Bảng / model chính**

- **`webhooks_settings`** → `WebhooksSetting`
    - `company_id`, `name`, `webhook_for`, `action` (boolean trong migration), `url`, `request_method`, `request_format`, `status`, `run_debug`, timestamps.
- **`webhooks_requests`** → `WebhooksRequest`
    - Liên kết `webhooks_setting_id`, `request_type` = `headers` \| `body`, các cột key/value tương ứng.
- **`webhooks_logs`** → `WebhooksLog`
    - `webhooks_setting_id`, `company_id` (migration bổ sung), `method`, `action` (trong job được gán = **URL**, không phải cột `action` của setting), `webhook_for`, `raw_content`, `headers`, `response`, `response_code`.
- **`webhooks_global_settings`** → `WebhooksGlobalSetting` (hỗ trợ seed cấu hình module).

**Quan hệ**

- `WebhooksSetting` **hasMany** `WebhooksRequest` (và scope `webhooksHeadersRequests` / `webhooksBodyRequests`).
- `WebhooksSetting` **hasMany** `WebhooksLog`.
- `WebhooksRequest` / `WebhooksLog` **belongsTo** `WebhooksSetting`.

Entity ERP map tới observer qua `WebhooksServiceProvider::registerObservers()` (có `modelMap` cho `ClientDetails`, `EmployeeDetails`, Purchase, Recruit, Zoom, Asset, Warehouse, Letter, v.v.).

---

## 5. Business Logic Rules

- Chỉ gửi webhook khi **`status = active`** (trong `SendWebhook::handle`).
- **Invoice** (`InvoiceObserver::saved`): chỉ gửi khi `status` hoặc `send_status` đổi **và** `send_status === 1`; payload gộp invoice + client (lọc field “invalid” theo `ClientVariable::invalidVariables()`), thêm URL signed cho front invoice.
- **Client / Employee:** observer riêng merge thêm dữ liệu `user` vào payload.
- **Product:** gửi trên `created`, `updated`, `deleted`, thêm `event_action` và flatten một số relation.
- **GenericObserver:** chỉ hook **`created`**, và chỉ dispatch nếu `class_basename($model)` nằm trong `WEBHOOK_FOR`.
- **`dataCleanUp` (job):** với entity có enum `*Variable`, bỏ các key trong `invalidVariables()`; chuỗi scalar thì xử lý escape slash.
- **`mapData`:** nếu `body_value` khớp enum case → gán `data[body_key]` từ payload theo `key()` của enum; không thì dùng literal.
- **UI “create/delete” (`webhook_action` → cột `action`):** được lưu khi store/update nhưng **`SendWebhook` không lọc theo trường này** — mọi lần observer fire vẫn có thể gửi nếu trùng `webhook_for` (có thể lệch kỳ vọng người cấu hình).
- **`run_debug`:** có quick action cập nhật nhưng **không thấy** logic đọc trong `SendWebhook` (có thể dùng nơi khác hoặc dở dang).

---

## 6. External Dependencies

- **Guzzle HTTP** (`Client`, `HandlerStack`, `Middleware`) — gọi endpoint ngoài.
- **`App\Events\NewCompanyCreatedEvent`** + `CompanyCreatedListener` trong module.
- **Core app:** `AccountBaseController`, `Reply`, permissions/modules trên user, `ModuleSetting`.
- **`config/webhooks.php`:** chủ yếu `WEBHOOK_URL` env (cần kiểm tra chỗ sử dụng nếu mở rộng).
- **API route** `GET /api/webhooks` trong module: trả `$request->user()` — gần như stub, không phải API quản lý webhook đầy đủ.

---

## 7. Risks & Issues

| Rủi ro                         | Chi tiết                                                                                                                                                             |
| ------------------------------ | -------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **TLS**                        | `'verify' => false` trong Guzzle — rủi ro MITM nếu không kiểm soát mạng.                                                                                             |
| **Lỗi HTTP / exception**       | `handle()` thiếu `try/catch`; lỗi trước khi có response có thể **không ghi `webhooks_logs`**, chỉ fail job (tùy retry queue).                                        |
| **Cột `action` trên setting**  | UI lưu create/delete nhưng job **không filter** → webhook “chỉ delete” vẫn có thể nhận event create (tùy observer).                                                  |
| **Không đồng nhất sự kiện**    | Nhiều entity chỉ `created`; Product có update/delete; Invoice theo `saved` + điều kiện — dễ hiểu nhầm khi cấu hình.                                                  |
| **GenericObserver + basename** | Phụ thuộc tên class model trùng `WEBHOOK_FOR`; map sai có thể **im lặng không gửi**.                                                                                 |
| **Placeholder UI vs job**      | `webhooksForVariable` **không có** `Product` (và các loại ngoài list match), trong khi `SendWebhook::getVariableClass()` **có** `Product` — UX/config không đồng bộ. |
| **N+1 query**                  | `WebhooksSetting::get()` rồi trong vòng lặp truy cập `webhooksBodyRequests` / `webhooksHeadersRequests` không eager load.                                            |
| **Payload lớn / PII**          | `toArray()` full model + log `raw_content` / headers → DB có thể chứa dữ liệu nhạy cảm.                                                                              |
| **ProductObserver**            | `Log::info` mỗi lần create — có thể ồn log production.                                                                                                               |
| **Phân quyền log**             | Xóa log dùng cùng permission `view_webhooks_logs` — không tách delete.                                                                                               |
| **destroy webhook**            | `findOrFail` rồi `if (! $webhook)` — nhánh unreachable.                                                                                                              |

---

## 8. Suggestions

1. **Hoàn thiện semantics `action`:** Hoặc filter trong `SendWebhook`/observer theo create vs delete/update, hoặc bỏ/đổi tên UI cho khớp hành vi thật.
2. **Cấu hình SSL:** `verify` theo `config` / env, mặc định `true` trên production.
3. **Bắt lỗi + log thất bại:** `try/catch` quanh request; ghi log lỗi hoặc exception; chính sách retry/backoff rõ ràng.
4. **Eager load:** `WebhooksSetting::with(['webhooksBodyRequests','webhooksHeadersRequests'])->get()`.
5. **Đồng bộ placeholder:** Mở rộng `webhooksForVariable` cho mọi loại có enum `*Variable` (ít nhất `Product`), hoặc generate động từ config.
6. **Giảm PII trong log:** Mask header nhạy cảm, truncate body, hoặc tôn trọng `run_debug` nếu bổ sung logic đọc flag.
7. **Chuẩn hóa observer:** Một lớp/strategy “event type” + payload builder thay vì logic phân tán giữa Generic và từng observer.
8. **API (nếu cần):** REST CRUD webhook cho tích hợp headless; route API hiện tại gần như không đáp ứng.

---

## Tóm tắt một dòng

Module Webhooks là lớp **tích hợp event-driven (ERP → ngoài)** dựa trên Eloquent observers + queue + Guzzle, có UI quản lý và log; **không** thay thế **webhook nhận từ LINE/WhatsApp** hay **API tạo đơn** cho luồng đặt hàng qua chat.

---

_Tài liệu tổng hợp từ phân tích codebase `Modules/Webhooks`._
