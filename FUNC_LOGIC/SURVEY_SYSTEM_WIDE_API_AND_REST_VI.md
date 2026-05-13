# Khảo sát toàn hệ thống — API HTTP & mức độ “RESTful”

**Mục đích:** Trả lời: hệ thống có API không? Có phần nào theo **REST** (chuẩn nghiêm / gần chuẩn) không?  
**Phương pháp:** Đọc toàn bộ `routes/api.php`, mọi file `Modules/*/Routes/api.php` (22 module có file), và grep `ApiRoute::resource` / `apiResource` trong repo. **Không** quét từng `Route::post` trong `web.php` (hàng trăm endpoint UI + JSON nội bộ — ngoài phạm vi “API công khai dạng product”).  
**Ngày:** 2026-05-12

---

## 1) Kết luận ngắn

| Câu hỏi                                                                                   | Kết luận                                                                                                                                                                                                |
| ----------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Có “không có bất kỳ API nào” không?                                                       | **Không.** Có nhóm route dưới prefix **`/api`** (middleware `api`) và thêm các endpoint **public/webhook** ngoài `/api`.                                                                                |
| Có API **RESTful đầy đủ** (tài nguyên + HATEOAS + OpenAPI chuẩn hóa toàn hệ thống) không? | **Không.** Không có bộ **REST API tổng quát** được mô tả chuẩn hóa (OpenAPI) cho toàn ERP.                                                                                                              |
| Có chỗ nào **gần REST** (verb + danh từ + CRUD) không?                                    | **Có — hạn chế:** chủ yếu **ServerManager** (`hosting`, `domain`). Một số route **`/api/v1/...`** dùng **Sanctum** nhưng nhiều chỉ là stub **GET** trả user.                                            |
| Gói **Froiden RestAPI** (`ApiRoute`)                                                      | Có trong codebase (`routes/api.php`, cấu hình). **Payroll** từng có `ApiRoute::resource` nhưng **đang comment** → hiện **không** có resource REST đang bật qua `ApiRoute::resource` trong repo đã quét. |
| Module **RestAPI** (tên plugin)                                                           | `HomeController@installedModule` kiểm tra plugin **RestAPI** — có thể là module mở rộng (tuỳ bật); **không** thấy thư mục `Modules/RestAPI` trong workspace khảo sát này.                               |

---

## 2) Danh sách endpoint `/api` đang thấy trong code (theo module)

> URL đầy đủ = `{APP_URL}/api` + đường dẫn trong cột (trừ khi module tự thêm prefix con như `server-manager`).

| Nguồn                                                                                     | Đường dẫn / nhóm                                                                                                     | Auth                                           | Ghi chú “REST”                                                                                                             |
| ----------------------------------------------------------------------------------------- | -------------------------------------------------------------------------------------------------------------------- | ---------------------------------------------- | -------------------------------------------------------------------------------------------------------------------------- |
| `routes/api.php`                                                                          | `GET purchased-module`                                                                                               | _(theo cấu hình RestAPI / facade)_             | Một endpoint tiện ích, không phải CRUD tài nguyên.                                                                         |
| `routes/api.php`                                                                          | `GET integrations/__route_probe`, **`POST integrations/orders`** (+ GET/PATCH/PUT/DELETE `integrations/orders/{id}`) | `ai.integration.auth`, `ai.integration.method` | Inbound AI / third-party Sales Order — **REST path**; không dùng secret trên URL. Xem `docs/AI_ORDER_INTEGRATION_REST.md`. |
| `Modules/Warehouse/Routes/api.php`                                                        | `GET v1/warehouse/availability`                                                                                      | `auth:sanctum`                                 | Đọc tính khả dụng — **một operation**; có test `WarehouseAvailabilityApiTest`.                                             |
| `Modules/Pricing/Routes/api.php`                                                          | `GET pricing/preview`                                                                                                | `auth:sanctum`                                 | **RPC** (preview), không phải resource REST.                                                                               |
| `Modules/ServerManager/Routes/api.php`                                                    | `server-manager/hosting`, `server-manager/domain`                                                                    | `auth:sanctum`                                 | **GET/POST/PUT/DELETE** theo `id` — **gần REST** cho hai tập “hosting” và “domain”.                                        |
| `Modules/Webhooks/Routes/api.php`                                                         | `GET webhooks`                                                                                                       | `auth:api`                                     | Closure trả `$request->user()` — stub / kiểm tra auth.                                                                     |
| `Modules/Recruit/Routes/api.php`                                                          | `GET recruit`                                                                                                        | `auth:api`                                     | Tương tự stub.                                                                                                             |
| `Modules/Policy/Performance/Onboarding/LineIntegration/DeveloperTools/Routes/api.php`     | `GET v1/{moduleKey}`                                                                                                 | `auth:sanctum`                                 | Trả user — stub SPA/module.                                                                                                |
| `Modules/Biometric/Routes/api.php`                                                        | `GET` / `POST` … `/iclock/…`                                                                                         | _(thiết bị)_                                   | **Không** REST; protocol kiểu máy chấm công.                                                                               |
| `Modules/Payroll/Routes/api.php`                                                          | _(toàn bộ block comment)_                                                                                            | —                                              | Nếu bật: sẽ có `ApiRoute::resource('payroll', ...)` — dạng REST resource điển hình.                                        |
| `Modules/Production/Asset/EInvoice/QRCode/Subdomain/Sms/Zoom/LanguagePack/Routes/api.php` | _(file rỗng hoặc chỉ `<?php`)_                                                                                       | —                                              | Chưa đăng ký route API trong file.                                                                                         |

**Inbound Sales Order (AI):** **`POST /api/integrations/orders`** trong **`routes/api.php`** (middleware `ai.integration.*`) — xem [`AUDIT_AI_ORDER_INBOUND_SO_API_VI.md`](AUDIT_AI_ORDER_INBOUND_SO_API_VI.md), [`AI_ORDER_LEGACY_WEBHOOK_REMOVED_VI.md`](AI_ORDER_LEGACY_WEBHOOK_REMOVED_VI.md). _(Legacy `POST /ai-order-webhook/{hash}` đã gỡ.)_

---

## 3) “Chuẩn REST” nghĩa là gì trong tài liệu này?

- **REST “đủ ý” (pragmatic):** nhóm URL danh từ + HTTP verb map CRUD (index/show/store/update/destroy), thường kèm **JSON** + **401/403/422**. Ví dụ gần nhất trong repo: **ServerManager** `hosting` / `domain`.
- **REST + versioning + auth chuẩn B2B:** nhiều hệ thống dùng ` /api/v1/...` + Bearer / OAuth2 + OpenAPI — **repo có prefix `v1` ở vài module**; inbound AI Order dùng **`/api/integrations/orders`** (shared secret header) — xem `FUNC_IMPROVE/12_AI_THIRDPARTY_SO_OPTIONS_VI.md`.
- **OpenAPI / JSON:API:** **không** thấy file spec đi kèm trong khảo sát tĩnh này.

---

## 4) Khuyến nghị (nếu muốn “chuẩn hóa” sau này)

1. Một file **OpenAPI 3** tập trung các route `/api` đang dùng thật (Warehouse, ServerManager, …).
2. Nếu bật **Payroll API**: uncomment + policy + test; đó là mẫu **resource** rõ ràng.
3. Tách rõ **public webhook** vs **`/api` Sanctum** trong tài liệu vận hành (đã có phần nào ở audit AI order).

---

## 5) Liên kết

- Inbound AI → SO (không phải `/api`): [`AUDIT_AI_ORDER_INBOUND_SO_API_VI.md`](AUDIT_AI_ORDER_INBOUND_SO_API_VI.md)
- Phương án REST/Sanctum tương lai: [`../FUNC_IMPROVE/12_AI_THIRDPARTY_SO_OPTIONS_VI.md`](../FUNC_IMPROVE/12_AI_THIRDPARTY_SO_OPTIONS_VI.md)

_Chỉ mục:_ [`INDEX.md`](INDEX.md).
