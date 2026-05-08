# Ghi chú nghiệp vụ: Approval gates + bối cảnh Flow Biomixing (Phase 1 -> 3)

## 1) President Review là gì?

Đây là bước duyệt **chiến lược / rủi ro lớn**, không phải duyệt chi tiết giá từng dòng.

### Mục tiêu

- Deal này có nên theo không?
- Có phù hợp nhóm khách hàng mục tiêu không?
- Có rủi ro pháp lý/thương mại lớn không?
- Có cho phép đi tiếp vòng pricing không?

### Kết quả

- **Reject:** dừng, Sales sửa lại scope/điều kiện.
- **Approve:** cho qua bước **VP Pricing Review (Vice President of Pricing)**.

---

## 2) VP Pricing (Vice President of Pricing) Review là gì?

Đây là bước duyệt **giá bán và biên lợi nhuận**.

### Mục tiêu

- Giá chào có đạt margin tối thiểu không?
- Có đúng pricing policy theo tier/volume/contract không?
- Có cần điều chỉnh term thanh toán/chiết khấu không?

### Kết quả

- **Reject:** trả về Sales chỉnh giá/điều khoản.
- **Approve:** cho phép **Convert to Sales Order**.

---

## 3) Custom dùng như thế nào? (nói cho PM dễ hiểu)

- Hệ thống mặc định có Proposal/Quotation.
- `President Review` và `VP Pricing Review (Vice President of Pricing)` là **gate custom theo doanh nghiệp** (quy trình nội bộ).
- Mỗi gate có:
    - Điều kiện vào (ví dụ: deal > ngưỡng giá trị, margin thấp, khách mới).
    - Người duyệt.
    - Trạng thái approve/reject.
    - Log lý do.
- Nếu công ty nhỏ, có thể tắt bớt gate (chỉ VP hoặc chỉ 1 cấp duyệt).

---

## 4) Giả sử có khách hàng thật, quy trình chạy sao?

1. Khách gửi yêu cầu (spec, số lượng, deadline).
2. Sales tạo Estimate/Proposal.
3. AI check recipe history (tham khảo công thức cũ, năng lực sản xuất, cảnh báo).
4. President review (gate chiến lược).
5. VP pricing review (gate giá/margin).
6. Approve xong -> convert thành Sales Order.
7. Từ Sales Order mới lập Production Order.
8. Production chạy BOM -> consume RM -> receipt FG -> giao hàng.

---

## 5) Khi nào khách hàng "order production"?

Khách hàng **không đặt Production Order trực tiếp** trong flow chuẩn.

- Khách đặt mua -> doanh nghiệp tạo Sales Order.
- Sau khi SO được chốt (và đủ điều kiện), nội bộ mới tạo Production Order để sản xuất.

### Câu nói ngắn gọn khi demo

> Khách đặt mua (SO), công ty mới đặt sản xuất (PO sản xuất nội bộ).  
> Production bắt đầu sau khi thương mại đã được duyệt và chốt đơn.

---

## 6) Bối cảnh (để đọc sơ đồ không bị lệch)

Phần dưới giúp **hình dung một deal cụ thể**, rồi mới nối với tài liệu/sơ đồ — tránh chỉ nhìn “một mớ” khái niệm.

### 6.1 Bối cảnh khách hàng mẫu (ví dụ minh họa)

**Bối cảnh (giả định, để dễ hình dung — không phải hợp đồng thật)**

- **Khách:** chuỗi đồ uống **FreshTea**
- **Nhu cầu:** đặt **2.000** chai sản phẩm **BioMix Detox** **350 ml**
- **Deadline:** giao sau **10 ngày** (cần thống nhất “10 ngày từ khi nào” trong thương mại, ví dụ từ ngày chốt SO)
- **Yêu cầu:** công thức chuẩn; có **COA** / chứng nhận theo cam kết; **in batch label** rõ, dò được lô

### 6.2 Ví dụ FreshTea nối với flow như thế (đọc theo 3 lớp)

**1) Khách đặt gì trên hệ thống?**  
Khách đặt **mua hàng** (đơn bán), không phải “lệnh sản xuất” trên ERP. Công ty lập **Sales Order (SO)**; xưởng chỉ chạy sau khi thương mại chốt — đúng tinh thần mục 4–5. FreshTea ở đây = một **đơn mua** kiểu đó.

**2) President / VP Pricing xem gì với deal này?**

- Kịp **giao 10 ngày** không (có chạy lệch ca, mua gấp nguyên liệu không).
- **Lợi nhuận** còn ổn khi phải rush + làm chứng nhận/COA đầy đủ không.
- Deal với **chuỗi đồ uống** có rủi ro chiến lược/thương hiệu cần tính không.

**3) Đã có SO rồi thì nội bộ làm gì?** (thấy trong `PHASE1_TO_3_END_TO_END_FLOW.mmd`)

1. Lên kế hoạch: kiểm **BOM**, kiểm **tồn**, mua bổ sung nếu thiếu.
2. Chạy **lô sản xuất** (2.000 chai theo ví dụ).
3. **QC** → cấp **COA / chứng nhận** đúng cam kết.
4. **In nhãn lô** rồi mới giao — khớp phần “COA + batch label” trong mục 6.1.

**4) Còn LINE / WhatsApp + AI thì là chuyện khác** (file `line_whatsapp_ai_hub_architecture…`)  
Khách hoặc CS có thể **hỏi giá, tồn, tiến độ** qua chat. Tin vào **AI → Hub** để trả lời. Đây là **kênh giao tiếp**, không phải chỗ duyệt giá: **President / VP vẫn làm trong ERP** theo quy trình nội bộ, trừ khi hợp đồng dự án ghi rõ tự động hóa khác.

**Gợi ý khi đọc sơ đồ:** _“Nếu là deal FreshTea, bước này tương ứng ô nào trên hình?”_

### 6.3 Hai trục tài liệu (một câu chuyện nghiệp vụ, một kiến trúc kênh)

Cùng nền ERP nhưng **không trộn** khi lên backlog:

- **`PHASE1_TO_3_END_TO_END_FLOW.mmd`** — sau **báo giá & duyệt** đến **SO**, rồi planning → xưởng → QA (ví dụ: lô 2.000 chai, QC, nhãn lô).
- **`line_whatsapp_ai_hub_architecture.mmd`** (+ file `.html` cho sequence) — **LINE/WA → AI → Hub/DB**; trả lời chat qua API kênh.

**Dễ lệch tên:** trong proposal, **“Phase 1”** thường là **báo giá & duyệt → SO**; trong playbook nội bộ repo có thể có chỗ gọi “Phase 1” **khác** (MVP production). Khi lên kế hoạch, **viết đầy đủ nhãn**, ví dụ _Phase 1 — Quotation (PDF)_ và tách _MVP Production (playbook)_.

### 6.4 Lưu ý ngắn khi chốt phạm vi (plan)

- Mốc **Create Production Project** gắn với **đã có SO** (trừ POC ghi rõ giả định).
- **AI Agent: Check Recipe History** trên flow end-to-end = hỗ trợ **estimate**; **AI chat** = **kênh đối thoại** — không tự suy **một chatbot thay cả duyệt giá** nếu scope chưa có.

### 6.5 Checklist rất ngắn

- [ ] Deal cụ thể (như FreshTea) ai là **SO**, bước nào là **xưởng/QA**?
- [ ] Tài liệu không dùng chung một cụm **“Phase 1”** cho hai ý khác nhau.
- [ ] Chat/LINE: webhook, API Hub, optional DB đã tách hạng mục chưa.

### 6.6 File tham chiếu

- `PROJECT BIOMIXING/PHASE1_QUOTATION_FLOW_DIAGRAM.mmd`
- `PROJECT BIOMIXING/PHASE2_PLANNING_PREPRODUCTION.mmd`
- `PROJECT BIOMIXING/PHASE3_PRODUCTION_QA.mmd`
- `PROJECT BIOMIXING/PHASE1_TO_3_END_TO_END_FLOW.mmd`
- `DIAGRAM/line_whatsapp_ai_hub_architecture.mmd` và `DIAGRAM/line_whatsapp_ai_hub_architecture.html`
- `PROJECT BIOMIXING/2-4-2026_Biomixing_Proposal_CravevaERP_Formatted.pdf`
