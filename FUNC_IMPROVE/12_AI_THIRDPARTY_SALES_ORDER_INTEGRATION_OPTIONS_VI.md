# Tích hợp AI / bên thứ ba tạo Sales Order — phương án & câu hỏi API vs Webhook

**Mục đích:** Gom quyết định kiến trúc (inbound webhook, REST API, queue, …) và trả lời: _có thể không dùng webhook mà dùng API để third-party AI gọi tạo SO không?_  
**Phạm vi:** ERP Craveva (Laravel) — tạo `Order` / dòng `OrderItems`; không mô tả chi tiết payload (xem tài liệu webhook AI).  
**Cập nhật:** 2026-05-12

---

## 1. Thuật ngữ (ngắn)

| Thuật ngữ    | Ý nghĩa (đối với ERP)                                       |
| ------------ | ----------------------------------------------------------- |
| **Inbound**  | Hệ ngoài **gọi vào** ERP (POST/GET tới URL của ERP).        |
| **Outbound** | ERP **gọi ra** URL ngoài (ví dụ module `Modules/Webhooks`). |

**API vs Webhook (dùng chung):** Endpoint “webhook nhận đơn” về bản chất thường là **một API HTTP (POST)**; từ “webhook” nhấn mạnh **push theo sự kiện** từ bên gọi. Không loại trừ nhau.

---

## 2. Trạng thái hiện tại trong repo (rút gọn)

| Cách                                                            | File / route gợi ý                                                                     | Ghi chú                                                                                                                                                                                                         |
| --------------------------------------------------------------- | -------------------------------------------------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **POST inbound (đang có, machine-friendly)**                    | `routes/web-public.php` → `POST ai-order-webhook/{hash}` → `AiOrderWebhookController`  | Secret URL + header; validation `StoreAiOrderWebhookRequest`; có thể kiểm sellable (Warehouse). Tài liệu: [`../FUNC_LOGIC/PM_READY_AI_WEBHOOK_STAGING_VI.md`](../FUNC_LOGIC/PM_READY_AI_WEBHOOK_STAGING_VI.md). |
| **Tạo đơn qua UI / session**                                    | `routes/web.php` → `OrderController` (resource `orders` trong nhóm `account` + `auth`) | Form web, cookie session — **không** phù hợp làm contract chính cho AI third-party.                                                                                                                             |
| **REST API `POST /api/.../orders` (Sanctum) dành riêng cho AI** | _Chưa thấy_ route tương đương trong `routes/api.php` cho tạo SO giống webhook          | **Có thể triển khai** — cần thiết kế token theo company, policy, rate limit, payload (có thể tái sử dụng logic/service từ `AiOrderWebhookController`).                                                          |
| **Module Webhooks (`Modules/Webhooks`)**                        | Outbound                                                                               | **Không** thay thế nhu cầu “AI đẩy đơn vào ERP”. Audit: [`../FUNC_LOGIC/AUDIT_WEBHOOKS_MODULE_VI.md`](../FUNC_LOGIC/AUDIT_WEBHOOKS_MODULE_VI.md).                                                               |

Biến môi trường liên quan webhook AI: [`../FUNC_LOGIC/WAREHOUSE_AND_PURCHASE_FLOW_ENV_REFERENCE_VI.md`](../FUNC_LOGIC/WAREHOUSE_AND_PURCHASE_FLOW_ENV_REFERENCE_VI.md).

---

## 3. Câu trả lời trực tiếp: _Không dùng webhook, chỉ API — third-party AI có tạo được SO không?_

**Có — về mặt kiến trúc hoàn toàn khả thi**, với hai ý:

1. **Hiện tại:** Đường **sẵn có** và ổn định cho máy gọi máy là **`POST /ai-order-webhook/{hash}`** (về kỹ thuật là HTTP API POST; có thể cấu hình trên `ai.craveva.com` như “API CRUD” / “writeback” miễn là trỏ đúng URL + auth).
2. **Nếu muốn “đúng nghĩa REST API”** (ví dụ `Authorization: Bearer <token>`, prefix `api/v1`, không đặt secret trong path): **cần phát triển thêm** endpoint + phân quyền (Sanctum hoặc API key) — repo chưa thể hiện một endpoint API công khai tương đương chỉ cho tạo SO ngoài luồng trên.

**Kết luận cho PM/kiến trúc:** Third-party **được** gọi API để tạo SO; **không bắt buộc** gọi là “webhook” theo tên — quan trọng là **contract HTTP + auth + idempotency (nếu cần)**. Hiện tại “gói” sẵn nhất cho AI là endpoint `ai-order-webhook`; chuyển sang Bearer + `/api/v1/...` là **hướng cải tiện / chuẩn hóa**, không phải bật sẵn.

---

## 4. Các phương án ngoài inbound webhook (tóm tắt)

| Phương án                                                            | Khi nào phù hợp                 | Ghi chú Craveva                                                       |
| -------------------------------------------------------------------- | ------------------------------- | --------------------------------------------------------------------- |
| **REST + Sanctum / API key**                                         | Chuẩn B2B, rotate secret, scope | Cần implement route + request + test.                                 |
| **Inbound POST nhưng auth mạnh hơn** (HMAC body, mTLS, JWT ngắn hạn) | URL/secret dễ lộ trong log      | Vẫn là “một POST”; đổi cách xác thực.                                 |
| **Queue (SQS/Rabbit/Redis)**                                         | Spike, retry, tách tải          | Hạ tầng + consumer trong ERP.                                         |
| **Import / batch**                                                   | Đồng bộ hàng loạt               | `ImportSalesOrderChunkJob`… — không thay real-time “vừa nói vừa tạo”. |
| **Gián tiếp: Lead / Estimate → SO**                                  | Kiểm soát phê duyệt             | AI tạo báo giá; người/rule chuyển SO.                                 |
| **Outbound Webhooks module**                                         | Đồng bộ _sau_ khi đã có đơn     | Không tạo SO từ AI.                                                   |

---

## 5. Liên kết nội bộ

- Runbook staging AI → SO: [`../FUNC_LOGIC/PM_READY_AI_WEBHOOK_STAGING_VI.md`](../FUNC_LOGIC/PM_READY_AI_WEBHOOK_STAGING_VI.md)
- Inbound/outbound & module Webhooks: [`../FUNC_LOGIC/WAREHOUSE_INDEX.md`](../FUNC_LOGIC/WAREHOUSE_INDEX.md) (mục thuật ngữ), [`../FUNC_LOGIC/AUDIT_WEBHOOKS_MODULE_VI.md`](../FUNC_LOGIC/AUDIT_WEBHOOKS_MODULE_VI.md)
- Runbook kho + webhook AI (sellable): [`04_WAREHOUSE_RUNBOOK_AND_UPGRADE_PLAN_VI.md`](04_WAREHOUSE_RUNBOOK_AND_UPGRADE_PLAN_VI.md)

---

_Chỉ mục FUNC_IMPROVE:_ [`INDEX.md`](INDEX.md).
