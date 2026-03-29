# Ghi chú nhanh: Inbound vs Outbound (Webhooks/API)

## Kết luận cho chức năng test vừa làm

Chức năng test nhanh mà mình vừa triển khai:

- `POST /ai-order-webhook/{hash}`
- AI gọi vào ERP để tạo `Order`

=> Đây là **INBOUND endpoint phía ERP** (ERP nhận request vào).

Nó **không phải** webhook outbound của module Webhooks hiện tại.

---

## Phân biệt dễ nhớ

- **Inbound**: hệ thống khác gọi vào mình (nhận vào).
- **Outbound**: hệ thống mình gọi ra ngoài (gửi đi).

Mẹo nhớ:

- **IN** = người ta gọi vào mình
- **OUT** = mình gọi ra người ta

---

## Áp vào bài toán LINE -> AI -> ERP

1. **LINE -> AI**: AI mở webhook receiver để nhận tin nhắn từ LINE.  
   => **Inbound ở phía AI**

2. **AI -> ERP**: AI gọi endpoint ERP để tạo đơn.  
   => **Inbound ở phía ERP** (API integration endpoint)

3. **ERP Webhooks module hiện tại**: ERP đẩy event ra hệ thống ngoài.  
   => **Outbound**

---

## Trạng thái hệ thống hiện tại (đã làm)

- Đã có endpoint inbound test trên staging:
    - `POST https://staging.craveva.com/ai-order-webhook/{hash}`
- Có auth secret + validate + idempotency cơ bản.
- Dùng để test nhanh luồng AI gọi vào ERP tạo đơn.

---

## Câu trả lời ngắn để gửi PM/CTO

"Endpoint test vừa làm là inbound (AI -> ERP). Module Webhooks ERP hiện tại là outbound (ERP -> external). Hai hướng này khác nhau và không thay thế nhau."

---

## Ghi chú liên quan kho / PO–DO–invoice (ERP)

Luồng **nhập–xuất tồn** và phân biệt **hóa đơn NCC (`PurchaseBill`) vs nhập kho** được tóm trong:

- `FUNC_LOGIC/ERP_SO_PO_DO_INVOICE_WAREHOUSE_QA_VERIFICATION_VI.md` — đặc biệt **mục L** (PurchaseBill không post stock: đúng thiết kế hiện tại, trừ khi PM chốt mở rộng).
