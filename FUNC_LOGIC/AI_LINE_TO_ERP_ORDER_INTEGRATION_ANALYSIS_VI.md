# Phân tích tích hợp LINE -> AI -> ERP để tạo Order (phiên bản tiếng Việt)

**Ngữ cảnh:** Khách hàng đặt sản phẩm qua LINE (ví dụ: “Tôi muốn đặt hàng coffee”), AI hiểu yêu cầu, sau đó tạo đơn hàng trong ERP.

**Tham chiếu nền tảng AI:** [Craveva AI](https://ai.craveva.com/)

---

## 1) Trả lời nhanh câu hỏi chính

### Câu hỏi: “AI phải thêm webhook ERP, hay ERP phải thêm webhook AI?”

Với bài toán **chat -> tạo đơn hàng trong ERP**, kiến trúc đúng là:

- LINE gửi sự kiện vào **webhook inbound** (điểm nhận) của bạn.
- Điểm nhận này nên đặt ở **AI service** hoặc **Integration Gateway**.
- Sau khi AI trích xuất thông tin đơn, hệ thống gọi **API ERP** để tạo Order.

**Kết luận:** Không phải chỉ “thêm webhook qua lại” là xong; cần một luồng **inbound + xử lý nghiệp vụ + gọi API ERP**.

### Câu hỏi: “AI có thể insert trực tiếp vào DB ERP không?”

**Không khuyến nghị** (chỉ có thể dùng tạm cho POC).

Lý do:

- Bỏ qua validation và business rules của ERP.
- Rủi ro sai giá, sai tồn kho, sai thuế, sai trạng thái.
- Khó audit, khó rollback, khó bảo trì lâu dài.

**Nên làm:** AI/Gateway gọi **API hoặc Service Layer** của ERP, để ERP tự ghi dữ liệu vào DB.

---

## 2) Vai trò thực tế của module Webhooks ERP hiện tại

Module `Modules/Webhooks` hiện tại là **webhook outbound**:

- ERP có sự kiện -> ERP gửi request ra ngoài.
- ERP ghi log request/response vào `webhooks_logs`.
- **Không** phải endpoint nhận lệnh đặt hàng từ LINE/WhatsApp.

=> Module này **không đủ** để hoàn thành trực tiếp bài toán “chat đặt hàng -> tạo Order ERP”.

---

## 3) Kiến trúc đề xuất (production-ready)

## 3.1 Luồng tổng quan

1. Khách nhắn tin trên LINE.
2. LINE đẩy webhook vào **AI/Integration Gateway**.
3. Gateway xác thực chữ ký LINE, lưu raw event (audit).
4. AI/NLU trích xuất:
    - Ý định: đặt hàng
    - Sản phẩm (coffee)
    - Số lượng
    - Khách hàng
    - Ghi chú giao hàng/thanh toán
5. Gateway gọi ERP API để kiểm tra:
    - SKU hợp lệ
    - Giá hiện hành/chính sách
    - Tồn kho
6. AI gửi tin nhắn xác nhận đơn cho khách.
7. Khách xác nhận.
8. Gateway gọi **ERP API tạo Order**.
9. ERP trả `order_id`, `order_no`, `status`.
10. Gateway phản hồi kết quả về LINE.
11. (Tùy chọn) ERP dùng webhook outbound để thông báo sang hệ thống khác (CRM/WMS/BI).

## 3.2 Thành phần cần có

- **LINE Webhook Receiver** (AI hoặc Integration Service)
- **Session Store** (Redis/DB) để giữ ngữ cảnh hội thoại
- **NLP/Intent Extraction** (AI)
- **ERP API Client**
- **Idempotency layer** (tránh tạo đơn trùng)
- **Monitoring/Logging/Alerting**

---

## 4) Nên đặt webhook endpoint ở đâu?

### Phương án A: Đặt ở AI platform

**Ưu điểm**

- Nhanh triển khai POC
- Tập trung xử lý hội thoại + AI

**Nhược điểm**

- Dễ phụ thuộc vào một nền tảng AI
- Khó chuẩn hóa tích hợp nhiều kênh về lâu dài

### Phương án B: Đặt ở Integration Gateway riêng (khuyến nghị dài hạn)

**Ưu điểm**

- Tách kênh chat khỏi ERP và khỏi AI model
- Dễ thay AI provider hoặc thay model sau này
- Kiểm soát bảo mật, retry, idempotency, audit tốt hơn
- Mở rộng đa kênh (LINE/WhatsApp/Zalo/Telegram) dễ hơn

**Nhược điểm**

- Tốn công setup ban đầu cao hơn

### Kết luận

Nếu làm lâu dài và mở rộng đa kênh: **chọn Integration Gateway riêng**.

---

## 5) API contract tối thiểu để tạo Order an toàn

Ví dụ endpoint ERP:

- `POST /api/integrations/orders`

Payload đề xuất:

- `external_source`: `line`
- `external_message_id`: ID duy nhất từ LINE
- `customer_ref`: line_user_id / phone / mã khách
- `items`: danh sách SKU + số lượng + ghi chú
- `requested_delivery_date`
- `channel_metadata` (JSON)

ERP phải xử lý:

- Validate schema + business rules
- Xác thực API key/JWT/HMAC
- Idempotency theo `external_message_id`
- Trả response chuẩn: `order_id`, `order_no`, `status`, `errors`

---

## 6) Bảo mật và vận hành (bắt buộc)

- Xác thực chữ ký webhook LINE
- API auth giữa Gateway và ERP (JWT/HMAC/API key)
- Rate limit + IP allowlist (nếu phù hợp)
- Mã hóa secrets, xoay vòng key
- Retry có backoff + dead-letter queue
- Log có masking dữ liệu nhạy cảm
- Correlation ID xuyên suốt từ tin nhắn -> đơn hàng ERP

---

## 7) Lộ trình triển khai giảm rủi ro

### Giai đoạn 1: Pilot

- 1 kênh LINE, 1 nhóm SKU đơn giản
- Bắt buộc bước xác nhận đơn trước khi tạo
- Chưa bật tự động 100%

### Giai đoạn 2: Tự động có kiểm soát

- Tự động tạo đơn khi confidence cao
- Bổ sung dashboard theo dõi tỷ lệ thành công/thất bại
- Chuẩn hóa retry/idempotency

### Giai đoạn 3: Mở rộng đa kênh

- Reuse Gateway cho WhatsApp/Zalo/Telegram
- Bổ sung hủy/sửa đơn trong hội thoại
- SLA, alerting, runbook vận hành

---

## 8) So sánh các lựa chọn

### Lựa chọn 1: Dùng module Webhooks ERP hiện tại làm trung tâm

- **Không phù hợp** cho inbound chat đặt hàng

### Lựa chọn 2: AI nhận webhook rồi insert trực tiếp DB ERP

- Làm nhanh nhưng **rủi ro cao**, không bền vững

### Lựa chọn 3: AI/Gateway nhận webhook -> gọi ERP API/Service (khuyến nghị)

- **Tốt nhất cho dài hạn**
- Đảm bảo dữ liệu đúng nghiệp vụ
- Dễ audit, dễ mở rộng, giảm lock-in

---

## 9) Checklist để PM chốt nhanh

1. Không insert trực tiếp DB ERP.
2. Tạo webhook inbound cho LINE ở AI/Gateway.
3. Chuẩn hóa ERP Order API cho kênh tích hợp.
4. Bắt buộc idempotency + xác thực chữ ký + retry.
5. Dùng webhook outbound ERP cho thông báo hậu xử lý (nếu cần).

---

## 10) Mẫu câu trả lời PM (copy nhanh)

“Để khách đặt hàng qua LINE và AI tạo Order trong ERP, cần webhook inbound từ LINE vào AI/Integration Gateway, sau đó gọi ERP Order API để tạo đơn. Module Webhooks ERP hiện tại là outbound nên không thay thế được luồng này. Giải pháp dài hạn là Gateway + ERP API + idempotency + security, không insert trực tiếp DB.”

# Phân tích tích hợp LINE -> AI -> ERP tạo Order (khuyến nghị triển khai dài hạn)

**Ngữ cảnh:** Doanh nghiệp muốn khách hàng đặt hàng qua LINE (ví dụ: "toi muon dat hang coffee"), AI hiểu ý định, sau đó tạo Order trong ERP.

**Tham chiếu nền tảng AI:** [Craveva AI](https://ai.craveva.com/)

---

## 1) Tra loi nhanh cau hoi chinh

### Q1. "AI phai them webhook ERP hay ERP them webhook AI?"

**Cau tra loi dung ban chat:** Neu muc tieu la "chat -> tao Order ERP", thi can **webhook inbound o phia AI/Integration Gateway** de nhan su kien tu LINE, sau do goi **API ERP** de tao don.

- LINE se goi vao endpoint ban dang ky tren LINE channel (webhook receiver).
- Endpoint nay nen o phia AI service hoac integration service trung gian.
- Sau khi AI trich xuat du lieu don hang, service se goi API ERP (`POST /orders`) thay vi insert truc tiep DB.

**Vi vay:**

- Khong phai "ERP them webhook cua AI" theo nghia module Webhooks hien co.
- Khong phai "chi can them webhook ERP vao AI la xong".
- Dung hon la: **tao luong inbound chat webhook + ERP Order API**.

### Q2. Co nen insert truc tiep DB ERP tu AI?

**Khong nen** (chi dung tam thoi khi POC).

Ly do:

- Bo qua validation/business rules trong ERP.
- De loi du lieu, sai gia, sai ton kho, sai tax, khong co audit dung chuan.
- Kho maintain va nang cap.

**Khuyen nghi:** AI/goi Integration Service -> **ERP API/Service Layer** -> ERP tu ghi DB.

---

## 2) Hieu dung ve module Webhooks hien tai cua ERP

Module `Modules/Webhooks` trong ERP hien tai la **outbound webhook**:

- ERP co su kien -> ERP gui request ra he thong ngoai.
- Co log vao `webhooks_logs`.
- **Khong** phai inbound endpoint de nhan lenh dat hang tu LINE/WhatsApp.

=> Module nay khong du de hoan tat bai toan "chat dat hang -> tao Order trong ERP".

---

## 3) Kien truc de xuat (production-ready)

## 3.1 Luong tong quan

1. Khach nhan tin tren LINE.
2. LINE webhook day message vao **AI/Integration Gateway**.
3. Gateway verify chu ky LINE, luu raw event (audit).
4. AI/NLU trich xuat thong tin:
    - intent: dat_hang
    - san_pham (coffee)
    - so_luong
    - thong tin khach
    - delivery note
5. Gateway goi dich vu "pricing/availability check" (ERP API) de xac nhan:
    - SKU hop le
    - ton kho
    - gia/ap dung khuyen mai/chinh sach
6. AI gui lai cau hoi xac nhan don cho khach ("Ban xac nhan dat 2 coffee...?").
7. Khach xac nhan.
8. Gateway goi **ERP API tao Order**.
9. ERP tra ve `order_id`, `order_no`, `status`.
10. Gateway gui thong bao thanh cong ve LINE.
11. (Tuy chon) ERP outbound webhook thong bao sang cac he thong khac (WMS/CRM/BI).

## 3.2 Thanh phan can co

- **LINE Webhook Receiver** (AI hoac Integration Service).
- **Conversation/Session Store** (Redis/DB): luu trang thai hoi dap.
- **Intent + Entity Extraction** (AI model/tool).
- **ERP Integration Client** (goi API ERP).
- **Idempotency layer** (tranh tao don trung).
- **Observability** (request log, trace, dead-letter, dashboard).

---

## 4) Nen dat "webhook endpoint" o dau?

### Phuong an A - Dat endpoint o AI platform

**Uu diem**

- Nhanh cho POC.
- Mot cho xu ly NLP + workflow.

**Nhuoc diem**

- De phu thuoc vao AI vendor/noi bo implementation.
- Kho tach bien business integration dai han.

### Phuong an B - Dat endpoint o Integration Gateway rieng (khuyen nghi)

**Uu diem**

- Tach kenh chat (LINE/WhatsApp/Telegram) khoi ERP va khoi AI model.
- De thay AI model/nen tang sau nay.
- Kiem soat bao mat, idempotency, retry, audit tot hon.

**Nhuoc diem**

- Ton cong setup ban dau nhieu hon.

### Ket luan

Cho san pham chay dai han: **Phuong an B** tot hon.
AI van la "bo nao", nhung Gateway la "xuong song tich hop".

---

## 5) API contract toi thieu de tao Order an toan

Gateway goi ERP API, vi du:

- `POST /api/integrations/orders`
    - `external_source`: `line`
    - `external_message_id`: unique id tu LINE
    - `customer_ref`: line_user_id / phone / mapped client id
    - `items`: sku, qty, note
    - `requested_delivery_date`
    - `channel_metadata` (json)

ERP phai:

- Validate schema + business rules.
- Check permission/api key.
- Xu ly idempotency theo `external_message_id`.
- Tra response chuan:
    - `order_id`
    - `order_no`
    - `status`
    - `errors` (neu co)

---

## 6) Bao mat va van hanh (bat buoc)

- Verify signature webhook LINE.
- API key + HMAC/JWT giua Gateway va ERP.
- IP allowlist (neu co).
- Encrypt secrets, rotate key.
- Rate limiting + WAF.
- Retry co backoff; dead-letter queue khi loi.
- Log co masking PII.
- Correlation ID xuyen suot chat event -> ERP order.

---

## 7) Lo trinh trien khai de it rui ro

### Phase 1 - Pilot

- 1 kenh LINE, 1 nhom SKU don gian, 1 thi truong.
- Human confirmation truoc khi tao don.
- Chua cho phep auto-order 100%.

### Phase 2 - Controlled Automation

- Tu dong tao don voi nguong tin cay cao.
- Them idempotency, retry, dashboard.
- Bo sung refund/cancel flow.

### Phase 3 - Multi-channel scale

- Reuse Gateway cho WhatsApp/Zalo/Telegram.
- Tach intent pack theo ngon ngu/quoc gia.
- SLA, alerting, SRE runbook.

---

## 8) Danh gia lua chon "tot hon va lau dai"

## Lua chon 1: Dung module ERP Webhooks hien tai lam trung tam

- **Khong phu hop** cho bai toan inbound chat dat hang.

## Lua chon 2: AI nhan webhook va insert truc tiep DB ERP

- Nhanh nhung **khong ben vung**, rui ro data integrity cao.

## Lua chon 3: AI/Gateway nhan webhook -> goi ERP API/Domain Service (khuyen nghi)

- **Tot nhat ve dai han**:
    - Dung business rules.
    - De audit.
    - De mo rong da kenh.
    - Giam lock-in.

---

## 9) Quy tac thuc thi de PM chot nhanh

1. Khong insert truc tiep DB ERP.
2. Tao endpoint inbound cho LINE o Gateway.
3. Dung ERP API de tao Order.
4. Bat buoc idempotency + signature verification.
5. Dung module Webhooks outbound ERP cho thong bao sau khi Order tao xong (neu can).

---

## 10) Tuyen bo kien truc de tra loi PM (co the copy)

"De khach dat hang qua LINE va AI tao Order trong ERP, can luong inbound webhook tu LINE vao AI/Integration Gateway, sau do goi ERP Order API. Module Webhooks ERP hien tai la outbound nen khong thay the duoc luong nay. Giai phap dai han la Gateway + ERP API + idempotency + security, khong insert truc tiep DB."
