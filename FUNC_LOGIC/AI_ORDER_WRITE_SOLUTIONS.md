# Giải pháp cho khách đặt hàng qua AI và ghi dữ liệu vào DB

Tài liệu này ghi lại các phương án khi khách hàng cần đặt hàng thông qua AI (WhatsApp, LINE, web) và hệ thống cần **insert/update dữ liệu** (orders, order_items, v.v.) vào database.

---

## 1. So sánh các phương án

| Phương án                                 | Mô tả                                                                                                             | Ưu điểm                                          | Nhược điểm                                                      | Khuyến nghị                                                     |
| ----------------------------------------- | ----------------------------------------------------------------------------------------------------------------- | ------------------------------------------------ | --------------------------------------------------------------- | --------------------------------------------------------------- |
| **A. AI ghi trực tiếp DB (gateway view)** | AI chạy `INSERT`/`UPDATE`/`DELETE` trên DB ảo (api_gateway_xx) như demo hiện tại.                                 | Đơn giản, không cần code API.                    | Rủi ro cao (xoá/sửa nhầm, không validate nghiệp vụ, khó audit). | Chỉ dùng cho **demo / POC**.                                    |
| **B. API do ERP cung cấp**                | ERP có API (REST/GraphQL). AI **gọi API** để “đặt hàng”; API validate rồi mới ghi DB.                             | An toàn, đúng luật nghiệp vụ, dễ log/rollback.   | Cần thiết kế và implement API.                                  | **Khuyến nghị cho production** khi khách thật đặt hàng qua AI.  |
| **C. Queue + job**                        | AI ghi vào bảng/queue “yêu cầu” (vd: `ai_order_requests`). Job Laravel đọc, validate, rồi mới tạo order trong DB. | Tách bạch “ý định” và “thực thi”, dễ kiểm duyệt. | Độ trễ, cần xử lý async và thông báo lại cho user.              | Phù hợp khi cần **kiểm duyệt** từng đơn trước khi vào hệ thống. |

---

## 2. Phương án khuyến nghị: API (B)

Cho use case **khách đặt hàng qua AI và insert vào DB**, nên dùng **API do ERP cung cấp**.

### Luồng hoạt động

1. Khách chat với AI (WhatsApp / LINE / web).
2. AI xác thực (client_code + SĐT) qua DB đọc (gateway read-only như hiện tại).
3. Khi khách đồng ý đặt hàng, **AI không INSERT thẳng vào DB**, mà **gọi API của ERP**, ví dụ:
    - `POST /api/ai/orders` (body: client_id, items[], shipping, note…).
4. ERP (Laravel):
    - Kiểm tra token/key cho request từ AI.
    - Validate (tồn kho, giá, quyền công ty, v.v.).
    - Tạo order (và order_items, v.v.) trong DB.
    - Trả về order_id, status.
5. AI nhận response và trả lời khách (“Đơn #123 đã đặt thành công…”).

### Lợi ích

- Đặt hàng = insert/update dữ liệu **đúng luật** (order, order_items, inventory, v.v.).
- Không cho AI quyền ghi trực tiếp DB → giảm rủi ro xoá/sửa sai.
- Dễ audit, log, rollback.
- Có thể tái dùng API cho app/web/mobile sau này.

---

## 3. Chi tiết từng phương án

### A. AI ghi trực tiếp DB (gateway)

- **Cách làm:** GRANT `SELECT, INSERT, UPDATE, DELETE` trên `api_gateway_xx` cho user AI (đã triển khai tạm cho demo).
- **Tài liệu liên quan:** `FUNC_LOGIC/DeveloperTools_FullAccess_Demo.md`
- **Khi nào dùng:** Chỉ demo/POC; không khuyến nghị cho production.

### B. API do ERP cung cấp

- **Cách làm:**
    - Tạo route API (vd: prefix `/api/ai/` hoặc `/api/v1/ai/`).
    - Endpoint ví dụ:
        - `POST /api/ai/orders` — tạo đơn (body: client_id hoặc client_code+phone, items[], shipping_address, note).
        - `PATCH /api/ai/orders/{id}` — cập nhật/huỷ đơn (nếu cho phép).
    - Middleware: xác thực request từ AI (API key, JWT, hoặc IP whitelist).
    - Controller: validate → gọi service tạo order (dùng logic nghiệp vụ hiện có) → ghi DB → trả JSON.
- **Khi nào dùng:** Production khi khách thật đặt hàng qua AI.

### C. Queue + job

- **Cách làm:**
    - Bảng ví dụ: `ai_order_requests` (payload JSON, status: pending/approved/rejected).
    - AI chỉ `INSERT` vào bảng này (hoặc gọi API `POST /api/ai/order-requests`).
    - Job Laravel (scheduled hoặc queue) đọc bản ghi pending, validate, tạo order thật, cập nhật status.
    - AI hoặc webhook thông báo lại khách (đã nhận / đã duyệt / từ chối).
- **Khi nào dùng:** Khi cần nhân viên duyệt đơn trước khi vào hệ thống.

---

## 4. Lộ trình gợi ý

- **Hiện tại (demo):** Giữ cơ chế full quyền trên gateway (A) nếu cần demo nhanh; nhớ chỉ dùng tạm.
- **Khi đưa khách thật đặt hàng qua AI:** Triển khai API (B). Trong prompt AI: quy định “Khi khách xác nhận đặt hàng thì gọi API POST … thay vì chạy INSERT”. Sau khi API ổn định, có thể revert gateway về chỉ `SELECT` (bỏ INSERT/UPDATE/DELETE).
- **Nếu sau này cần kiểm duyệt đơn:** Kết hợp B với C (AI gửi request vào queue, nhân viên duyệt, job tạo order và ghi DB).

---

## 5. Tài liệu liên quan

- `FUNC_LOGIC/DeveloperTools_FullAccess_Demo.md` — Cách bật full quyền gateway (demo).
- `FUNC_LOGIC/AI_AGENT_PROMPT_TEMPLATE.md` — Prompt cho AI (đọc DB, xác thực, sau này có thể bổ sung “gọi API khi đặt hàng”).
