# Ghi chú nghiệp vụ: Approval gates + bối cảnh Flow Biomixing (Phase 1 -> 3)

_Bản bổ sung bối cảnh & ví dụ thao tác Hub — cập nhật 2026-05-09 (mục 6)._

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

### 6.2 Vì sao phải duyệt Estimate hai lần (President rồi VP Pricing)?

**Không phải “duyệt trùng”.** Hai người nhìn **hai câu hỏi khác nhau** trên **cùng một chứng từ báo giá (Estimate / Quotation trong Hub)**:

| Cửa            | Câu hỏi chính (một câu)                                                                                     | Ví dụ FreshTea (số minh họa — có thể điều chỉnh theo pilot)                                                                                                                                                                                                                |
| -------------- | ----------------------------------------------------------------------------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **President**  | **“Deal này có nên làm không?”** (chiến lược, rủi ro, uy tín, cam kết giao hạn/COA có chấp nhận được không) | Giao **10 ngày** + COA + nhãn lô: có chấp nhận **rủi ro vận hành** (rush, ca đêm, nhà cung cấp chứng nhận) không? Khách là **chuỗi lớn** — nếu trễ hoặc lỗi lô, ảnh hưởng thương hiệu Biomixing ra sao? **President không cần** quyết định “giá 12.345 đ/chai hay 12.400”. |
| **VP Pricing** | **“Giá này có ăn không?”** (margin, policy giá theo tier/khối lượng, chiết khấu, điều khoản thanh toán)     | Sale đề xuất **45.000 đ/chai**. VP tính **COGS + biên phải đạt** (ví dụ tối thiểu 22%): nếu rush + COA làm COGS tăng, giá có còn đủ biên không? Có cần **tăng giá / giảm chiết khấu / đổi MOQ** không? **VP mới** chỉnh số tiền trên báo giá rồi duyệt.                    |

**Ví dụ “vì sao không gộp một người”:**

- Tình huống A: President **đồng ý** làm deal (quan hệ chiến lược, kịp deadline), nhưng VP **từ chối** giá vì margin không đủ → Sale **sửa giá / giảm chiết khấu** hoặc **thương lượng lại** với khách, **không** phải đổi quyết định “có làm hay không” từ đầu.
- Tình huống B: President **từ chối** (rủi ro pháp lý / deadline không thực tế) → **dừng luôn**, VP không cần tính giá chi tiết cho deal chết.

Như vậy: **President = “có chơi ván này không”**; **VP = “chơi thì cược bao nhiêu (giá)”**.

### 6.3 Trên hệ thống Hub, President và VP duyệt _kiểu gì_?

- **Chứng từ:** cùng một **Estimate** (trên giao diện thường gọi **Quotation / báo giá**). Hai cửa duyệt là **hai trạng thái** lưu trên estimate: `president_review_*` và `vp_pricing_review_*` (ai duyệt, lúc nào, ghi chú).
- **Thứ tự bắt buộc trong code:** chỉ khi **President đã approve** thì **VP Pricing** mới được gọi API duyệt; nếu chưa có President approve, hệ thống trả lỗi “invalid request” (tránh VP duyệt trước).
- **Thao tác người dùng (UI):** mở **chi tiết Estimate** (trạng thái thường là draft / waiting — tùy cấu hình), trong menu thao tác có các lựa chọn kiểu **President approve / President reject**; sau khi President approve, mới hiện (hoặc mới hợp lệ) **VP Pricing approve / reject**. Khi bấm duyệt, có thể nhập **ghi chú** (note) để lưu lý do.
- **API (kỹ thuật, cho dev/QA):** `POST .../estimates/{id}/president-review` và `POST .../estimates/{id}/vp-pricing-review` (tên route: `estimates.president_review`, `estimates.vp_pricing_review`).
- **Quyền:** người duyệt cần quyền chỉnh/sửa estimate theo policy công ty (trong code đang kiểm tra nhóm quyền `edit_estimates` — **ai được gán vai trò President/VP trên thực tế** do BA/PM cấu hình role + quy trình nội bộ, không phải tự động theo chức danh trong CMND).
- **Bật/tắt cửa Phase 1:** có thể tắt gate theo tenant qua cấu hình module `estimates_phase1_review` (khi tắt, luồng legacy có thể bỏ qua hai bước — chỉ dùng khi pilot đã chốt).

### 6.4 Ví dụ FreshTea — từng bước (gắn đúng President vs VP)

Giữ bối cảnh mục **6.1**. Luồng **trước SO** như sau:

1. **Sale** tạo Estimate: 2.000 chai, đơn giá draft, điều kiện giao 10 ngày, COA + nhãn lô.
2. **AI** (nếu có): gợi ý công thức / cảnh báo tồn — **không thay** President/VP.
3. **President** vào Hub → **President approve** (hoặc reject + note). _Ví dụ quyết định:_ “Chấp nhận rủi ro rush + COA cho chuỗi FreshTea; cho phép VP chốt giá.”
4. **VP Pricing** vào cùng estimate → chỉnh giá/margin nếu cần → **VP Pricing approve** (hoặc reject trả Sale). _Ví dụ quyết định:_ “Giữ 45k/chai nhưng bỏ chiết khấu 3%” hoặc “Tăng lên 46,5k mới đủ biên.”
5. Khi **cả hai** approve → Sale (hoặc quyền tương đương) **Convert Estimate → Sales Order**.
6. **Sau SO** mới tới lớp xưởng: BOM, tồn, Production, QC… (như mục 6.5 bên dưới).

**1) Khách “đặt” gì trên hệ thống ở giai đoạn báo giá?**  
Ở bước 1–5, trên ERP vẫn là **Estimate / báo giá**; khách chưa tạo **Sales Order** — SO xuất hiện sau bước **convert** (sau khi đã duyệt nội bộ theo policy).

**2) Đã có SO rồi thì nội bộ làm gì?** (thấy trong `PHASE1_TO_3_END_TO_END_FLOW.mmd`)

1. Lên kế hoạch: kiểm **BOM**, kiểm **tồn**, mua bổ sung nếu thiếu.
2. Chạy **lô sản xuất** (2.000 chai theo ví dụ).
3. **QC** → cấp **COA / chứng nhận** đúng cam kết.
4. **In nhãn lô** rồi mới giao — khớp phần “COA + batch label” trong mục 6.1.

**3) LINE / WhatsApp + AI** (file `chat_ai_hub…`)  
Là **kênh hỏi đáp**; **duyệt chính thức** vẫn nằm trên **Estimate trong Hub** như mục 6.3.

**Gợi ý khi đọc sơ đồ:** _“FreshTea: President = ô chiến lược; VP = ô giá; Convert = ô ra SO.”_

### 6.5 Hai trục tài liệu (một câu chuyện nghiệp vụ, một kiến trúc kênh)

Cùng nền ERP nhưng **không trộn** khi lên backlog:

- **`PHASE1_TO_3_END_TO_END_FLOW.mmd`** — sau **báo giá & duyệt** đến **SO**, rồi planning → xưởng → QA (ví dụ: lô 2.000 chai, QC, nhãn lô).
- **`chat_ai_hub.mmd`** (+ file `.html` cho sequence) — **LINE/WA → AI → Hub/DB**; trả lời chat qua API kênh.

**Dễ lệch tên:** trong proposal, **“Phase 1”** thường là **báo giá & duyệt → SO**; trong playbook nội bộ repo có thể có chỗ gọi “Phase 1” **khác** (MVP production). Khi lên kế hoạch, **viết đầy đủ nhãn**, ví dụ _Phase 1 — Quotation (PDF)_ và tách _MVP Production (playbook)_.

### 6.6 Lưu ý ngắn khi chốt phạm vi (plan)

- Mốc **Create Production Project** gắn với **đã có SO** (trừ POC ghi rõ giả định).
- **AI Agent: Check Recipe History** trên flow end-to-end = hỗ trợ **estimate**; **AI chat** = **kênh đối thoại** — không tự suy **một chatbot thay cả duyệt giá** nếu scope chưa có.

### 6.7 Checklist rất ngắn

- [ ] Deal cụ thể (như FreshTea) ai là **SO**, bước nào là **xưởng/QA**?
- [ ] Tài liệu không dùng chung một cụm **“Phase 1”** cho hai ý khác nhau.
- [ ] Chat/LINE: webhook, API Hub, optional DB đã tách hạng mục chưa.

### 6.8 File tham chiếu

- `PROJECT BIOMIXING/PHASE1_QUOTATION_FLOW_DIAGRAM.mmd`
- `PROJECT BIOMIXING/PHASE2_PLANNING_PREPRODUCTION.mmd`
- `PROJECT BIOMIXING/PHASE3_PRODUCTION_QA.mmd`
- `PROJECT BIOMIXING/PHASE1_TO_3_END_TO_END_FLOW.mmd`
- `DIAGRAM/chat_ai_hub.mmd` và `DIAGRAM/chat_ai_hub.html`
- `PROJECT BIOMIXING/2-4-2026_Biomixing_Proposal_CravevaERP_Formatted.pdf`

