# Tích hợp AI / bên thứ ba tạo Sales Order — phương án & câu hỏi API vs Webhook

**Mục đích:** Gom quyết định kiến trúc (inbound webhook, REST API, queue, …) và trả lời: _có thể không dùng webhook mà dùng API để third-party AI gọi tạo SO không?_  
**Phạm vi:** ERP Craveva (Laravel) — tạo `Order` / dòng `OrderItems`; không mô tả chi tiết payload (xem tài liệu webhook AI).  
**Cập nhật:** 2026-05-13 (inbound REST; legacy webhook path đã gỡ)

---

## 1. Thuật ngữ (ngắn)

| Thuật ngữ    | Ý nghĩa (đối với ERP)                                       |
| ------------ | ----------------------------------------------------------- |
| **Inbound**  | Hệ ngoài **gọi vào** ERP (POST/GET tới URL của ERP).        |
| **Outbound** | ERP **gọi ra** URL ngoài (ví dụ module `Modules/Webhooks`). |

**API vs Webhook (dùng chung):** Endpoint “webhook nhận đơn” về bản chất thường là **một API HTTP (POST)**; từ “webhook” nhấn mạnh **push theo sự kiện** từ bên gọi. Không loại trừ nhau.

---

## 2. Trạng thái hiện tại trong repo (rút gọn)

| Cách                                                            | File / route gợi ý                                                                     | Ghi chú                                                                                                                                                             |
| --------------------------------------------------------------- | -------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **POST inbound (canonical)**                                    | `routes/api.php` → **`POST /api/integrations/orders`**                                 | [`docs/AI_ORDER_INTEGRATION_REST.md`](../docs/AI_ORDER_INTEGRATION_REST.md), [`PM_READY_AI_WEBHOOK_STAGING_VI.md`](../FUNC_LOGIC/PM_READY_AI_WEBHOOK_STAGING_VI.md) |
| **Tạo đơn qua UI / session**                                    | `routes/web.php` → `OrderController` (resource `orders` trong nhóm `account` + `auth`) | Form web, cookie session — **không** phù hợp làm contract chính cho AI third-party.                                                                                 |
| **REST mở rộng (Sanctum `api/v1/...` tài nguyên Order đầy đủ)** | _Tùy roadmap_                                                                          | Inbound hiện tại đã là REST tại `/api/integrations/orders`; mở rộng CRUD/versioning/OpenAPI là **hướng cải tiện** thêm.                                             |
| **Module Webhooks (`Modules/Webhooks`)**                        | Outbound                                                                               | **Không** thay thế nhu cầu “AI đẩy đơn vào ERP”.                                                                                                                    |

Biến môi trường liên quan inbound AI: [`../FUNC_LOGIC/WH_PURCHASE_ENV_REFERENCE_VI.md`](../FUNC_LOGIC/WH_PURCHASE_ENV_REFERENCE_VI.md). _(Legacy `POST /ai-order-webhook/{hash}` đã gỡ — xem [`../FUNC_LOGIC/AI_ORDER_LEGACY_WEBHOOK_REMOVED_VI.md`](../FUNC_LOGIC/AI_ORDER_LEGACY_WEBHOOK_REMOVED_VI.md).)_

---

## 3. Câu trả lời trực tiếp: _Không dùng webhook, chỉ API — third-party AI có tạo được SO không?_

**Có — và đã có endpoint REST inbound:** **`POST /api/integrations/orders`** (middleware `ai.integration.*`, cùng payload/validation với trước). Secret **không** nằm trên path URL.

1. **Hiện tại:** Đường **sẵn có** cho máy gọi máy là REST ở trên (JSON + header Bearer hoặc `X-AI-Webhook-Secret`).
2. **Nếu muốn “đúng nghĩa REST API” thêm** (ví dụ `api/v1` đầy đủ CRUD Order + OpenAPI): **có thể mở rộng** — tách khỏi integration hiện tại.

**Kết luận cho PM/kiến trúc:** Third-party **được** gọi API để tạo SO; quan trọng là **contract HTTP + auth + idempotency (nếu cần)**. Tên “webhook” hay “API” không quyết định kỹ thuật — hiện contract chính là **`/api/integrations/orders`**.

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

- Runbook PM (curl, payload, response): [`../FUNC_LOGIC/PM_READY_AI_WEBHOOK_STAGING_VI.md`](../FUNC_LOGIC/PM_READY_AI_WEBHOOK_STAGING_VI.md)
- Inbound/outbound & module Webhooks: [`../FUNC_LOGIC/WAREHOUSE_INDEX.md`](../FUNC_LOGIC/WAREHOUSE_INDEX.md), [`../FUNC_LOGIC/AI_ORDER_LEGACY_WEBHOOK_REMOVED_VI.md`](../FUNC_LOGIC/AI_ORDER_LEGACY_WEBHOOK_REMOVED_VI.md)
- Runbook kho + webhook AI (sellable): [`04_WH_RUNBOOK_UPGRADE_VI.md`](04_WH_RUNBOOK_UPGRADE_VI.md)

---

_Chỉ mục FUNC_IMPROVE:_ [`INDEX.md`](INDEX.md).
