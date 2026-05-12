# Sale Order + AI tích hợp — kết quả kiểm tra repo & prompt triển khai

**Mục đích:** Xác nhận đã có API tạo Sales Order nào **ngoài** luồng inbound hiện tại; cung cấp **prompt Cursor** để lên kế hoạch / triển khai trang **Sale order settings** + hoàn thiện tích hợp cho khách.  
**Ngày:** 2026-05-12

---

## 1. Kết quả kiểm tra (toàn dự án, không chỉ Company Settings)

### 1.1. `FUNC_LOGIC/AUDIT_WEBHOOKS_MODULE_VI.md` là gì?

- Đây là **tài liệu audit** cho module **`Modules/Webhooks`** (ERP **đẩy** event **ra** URL ngoài — **outbound**).
- **Không** mô tả API tạo Sales Order; **không** thay cho tài liệu inbound AI.

### 1.2. Có API tạo Sales Order nào khác ngoài inbound “tạm / pilot” không?

| Nguồn                                      | Kết luận                                                                                                                                                                                                                                               |
| ------------------------------------------ | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| `routes/api.php` (nhóm `ApiRoute`)         | Chỉ thấy **`GET purchased-module`** → `HomeController@installedModule`. **Không** có `POST`/`api/.../orders`.                                                                                                                                          |
| `routes/web.php` (nhóm `account` + `auth`) | `OrderController` resource `orders` — tạo đơn qua **`store(PlaceOrder)`** là **web session + form**, không phải API machine-to-machine cho third-party.                                                                                                |
| `routes/web-public.php`                    | **`POST ai-order-webhook/{hash}`** → `AiOrderWebhookController@store` — **đây là endpoint JSON duy nhất** trong repo cho luồng “bên ngoài POST vào → tạo `Order` + `OrderItems`” (kèm `StoreAiOrderWebhookRequest`, secret `AI_ORDER_WEBHOOK_SECRET`). |
| Import SO                                  | `OrderController` import / `ImportSalesOrderChunkJob` — **upload file / queue**, không phải REST contract cho AI real-time.                                                                                                                            |

**Kết luận:** Ngoài **`POST /ai-order-webhook/{hash}`**, **chưa có** REST API chuẩn (ví dụ Sanctum `api/v1/orders`) dành cho third-party tạo Sales Order. Company Settings / Purchase Settings **không** chứa endpoint tạo SO qua API trong code đã quét.

**Tài liệu inbound AI (payload, curl):** `FUNC_LOGIC/PM_READY_AI_WEBHOOK_STAGING_VI.md`.  
**Phương án dài hạn (API vs webhook):** `FUNC_IMPROVE/12_AI_THIRDPARTY_SALES_ORDER_INTEGRATION_OPTIONS_VI.md`.

---

## 2. Prompt gợi ý cho Cursor (copy vào chat Agent mode)

Dùng prompt dưới đây khi muốn agent **lên kế hoạch + code** (điều chỉnh phạm vi nếu chỉ cần plan).

```text
Bối cảnh repo: Laravel 11, đa company. Tạo Sales Order từ bên ngoài hiện chỉ có POST /ai-order-webhook/{hash} (AiOrderWebhookController, StoreAiOrderWebhookRequest, secret AI_ORDER_WEBHOOK_SECRET toàn instance). routes/api.php không có resource orders. OrderController@store là web + session.

Mục tiêu triển khai (ưu tiên theo thứ tự):
1) Thêm mục cài đặt giống pattern "Purchase Settings" (sidebar settings): ví dụ "Sale order settings" hoặc "Sales order integration", nằm cạnh các mục settings hiện có; URL dạng account/...; middleware auth + permission phù hợp (chỉ admin/IT xem secret hướng dẫn).
2) Trang có ít nhất một tab **"API"** (nhãn UI ngắn gọn; nội dung là hướng dẫn tích hợp AI / third-party tạo SO). Tab khác (ví dụ đánh số SO) tách riêng nếu sau này có. Trong tab **API**: hiển thị read-only Base URL (app.url), đường dẫn đầy đủ POST /ai-order-webhook/{hash} (hash = secret đã cấu hình — hoặc chỉ hiển thị phần path + nhắc cấu hình env nếu không muốn lộ secret trên UI), header X-AI-Webhook-Secret, company_id của tenant hiện tại, link tài liệu nội bộ PM_READY_AI_WEBHOOK_STAGING_VI.md, gợi ý external_event_id để idempotent.
3) Không nhét vào form Company Settings (tên/phone). Tuân theo DESIGN_BACKEND_UI_UX_DESIGN_SPEC_VI.md và convention blade/menu hiện có (tìm purchase-settings, sidebar settings partial).
4) Viết ít nhất một Pest feature test: user có quyền truy cập trang mới thấy 200 và thấy company_id; user không có quyền bị 403 (hoặc policy tương đương).
5) (Tùy chọn pha 2, liệt kê trong PR mô tả chứ chưa code nếu chưa được yêu cầu) Sanctum POST api/v1/companies/{company}/orders tái sử dụng validation/service chung với AiOrderWebhookController; secret theo company.

Ràng buộc: không đổi dependencies composer không được phê duyện; chạy vendor/bin/pint --dirty --format agent trên PHP đã sửa; test tối thiểu php artisan test --compact <file test mới>.
```

---

## 3. Liên kết

- [`12_AI_THIRDPARTY_SALES_ORDER_INTEGRATION_OPTIONS_VI.md`](12_AI_THIRDPARTY_SALES_ORDER_INTEGRATION_OPTIONS_VI.md)
- [`../FUNC_LOGIC/PM_READY_AI_WEBHOOK_STAGING_VI.md`](../FUNC_LOGIC/PM_READY_AI_WEBHOOK_STAGING_VI.md)
- [`../FUNC_LOGIC/DESIGN_BACKEND_UI_UX_DESIGN_SPEC_VI.md`](../FUNC_LOGIC/DESIGN_BACKEND_UI_UX_DESIGN_SPEC_VI.md)

_Chỉ mục:_ [`INDEX.md`](INDEX.md).

---

## 4. Triển khai trong repo (2026-05-12)

- **Route:** `GET /account/sales-order-settings` — `sales-order-settings.index`
- **Controller:** `App\Http\Controllers\SalesOrderSettingsController`
- **View:** `resources/views/sales-order-settings/index.blade.php` (tab **API**)
- **Menu:** `resources/views/components/setting-sidebar.blade.php` (sau Finance Settings; điều kiện `manage_finance_setting` + module `orders`)
- **Test:** `tests/Feature/SalesOrderSettingsPageTest.php`
- **Chuỗi:** `Modules/LanguagePack/Languages/app/en|vi` — `app.menu.saleOrderSettings`, `modules.orders.*` (API)

**Kế hoạch triển khai + checklist theo dõi tiến độ:** [`14_SALE_ORDER_AI_WEBHOOK_ROLLOUT_PLAN_VI.md`](14_SALE_ORDER_AI_WEBHOOK_ROLLOUT_PLAN_VI.md).
