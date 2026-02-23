Tài liệu gốc: 苗林食品 (Miaolin Foods) B2B AI 智慧分銷平台規劃書 | Miaolin Foods B2B AI Smart Distribution Platform Planning Document

# Tài liệu quy hoạch nền tảng phân phối thông minh B2B AI — Miao Lin Foods

Nguồn gốc tài liệu:

-   `NEW FUNCTION/Miao Ling Foods B2B AI Smart Distribution Platform Planning Document.docx`
-   Bên gửi: `CRAVEVA PTE LTD`
-   Bên nhận: `苗林食品 (Miaolin Foods)`
-   Ngày: `12/2025`
-   Phiên bản: `v5.0 (Bản tích hợp đa kênh & tăng cường AI)`

---

## 1) File này là gì?

Đây là **tài liệu quy hoạch/đề xuất giải pháp** (planning/proposal) do Craveva chuẩn bị cho Miao Lin Foods, mô tả:

-   Mục tiêu kinh doanh: xây dựng “trung tâm quản lý phân phối B2B đa kênh” cho nhà nhập khẩu quản lý nhiều thương hiệu và nhiều tầng khách hàng B2B.
-   Kiến trúc giải pháp: nền tảng Craveva AI Enterprise + các module vận hành, định giá, dữ liệu, AI.
-   Cách tích hợp với hệ thống hiện hữu: **giai đoạn 1** dùng trao đổi file hằng ngày (Excel/CSV) để không làm thay đổi Digiwin ERP & Salesforce; **giai đoạn 2** nâng cấp lên tích hợp API real-time.
-   Quy trình vận hành hằng ngày, roadmap triển khai 12–14 tuần (giai đoạn 1), và đề xuất đầu tư (phí triển khai, phí thuê bao, phí sử dụng AI theo token).

---

## 2) Tóm tắt điều khách hàng đang hướng tới (Executive Summary — tóm tắt)

-   Xây dựng một **trung tâm phân phối B2B đa kênh** cho Miao Lin Foods (nhà nhập khẩu lớn), phục vụ nhiều tầng khách hàng B2B: chuỗi, đại lý, cửa hàng đơn.
-   Chọn chiến lược “**số hoá theo giai đoạn**” để giảm rủi ro:
    -   Giai đoạn 1 (làm ngay): chuẩn hoá cơ chế **trao đổi file hằng ngày** để đồng bộ giá/khách hàng/tồn kho và tự động hoá quy trình đơn hàng.
    -   Giai đoạn 2 (tương lai): nâng cấp sang **API real-time** khi dữ liệu và vận hành đã ổn định.

---

## 3) Bản dịch tiếng Việt (dịch theo nội dung trong file gốc)

### 1. Tóm tắt điều hành (Executive Summary)

Tài liệu quy hoạch này nhằm xây dựng cho Miao Lin Foods một “**trung tâm quản lý phân phối thương hiệu B2B đa kênh**”. Do Miao Lin là nhà nhập khẩu quy mô lớn, cần đồng thời quản lý nhiều thương hiệu đại diện quốc tế và phục vụ nhiều cấp độ khách hàng B2B (chuỗi, nhà phân phối, cửa hàng đơn), chúng tôi đề xuất giải pháp dựa trên **Craveva AI Enterprise**.

Kế hoạch áp dụng chiến lược “**số hoá theo giai đoạn**” mang tính thực tế:

-   Giai đoạn 1 (khởi động ngay): xây dựng cơ chế “**trao đổi file hằng ngày (File-Based Integration)**” tiêu chuẩn hoá. Trong điều kiện **không thay đổi** kiến trúc hiện có của **Digiwin ERP** và **Salesforce**, hệ thống sẽ thông qua cơ chế nhập/xuất thông minh và công nghệ tự động chuyển đổi SKU để nhanh chóng đạt được tự động hoá đơn hàng và quản lý định giá phức tạp.
-   Giai đoạn 2 (kế hoạch tương lai): khi dữ liệu vận hành ổn định, nâng cấp sang tích hợp API theo thời gian thực.

### 2. Các “điểm đau” & phân tích nhu cầu (Key Challenges)

Dựa trên bối cảnh nghiệp vụ và hiện trạng vận hành, tài liệu nêu 5 thách thức trọng yếu:

1. **Duy trì định giá B2B phức tạp mỗi ngày**: cần quản lý nhiều thương hiệu và logic định giá 5 tầng (từ giá công khai đến giá hợp đồng riêng theo khách). Giá nguyên vật liệu biến động, vì vậy cần đảm bảo mỗi sáng có thể đồng bộ nhanh chiến lược giá mới nhất từ ERP lên nền tảng để tránh lệch báo giá.

2. **Chuyển đổi định dạng dữ liệu đơn hàng (SKU Mapping)**: khách đặt theo “đơn vị lẻ/ chai (Unit)”, nhưng Digiwin ERP yêu cầu hạch toán theo “thùng (Carton)”. Nếu báo cáo đơn hàng xuất ra không được chuyển đổi, phần sửa tay phía sau sẽ tốn thời gian và dễ sai.

3. **Quản lý tập trung đa thương hiệu**: cần một back-office duy nhất để quản lý mọi thương hiệu đại diện; khách hàng có thể mua “một điểm đến” và HQ có thể tổng hợp đơn hàng thuận tiện.

4. **Quá nhiều thao tác thủ công do nguồn nhận đơn phân tán**: đơn đến từ web, nhóm Line… nhiều kênh và nhiều định dạng (tin nhắn chữ, ảnh chụp, thoại). Trợ lý bán hàng tốn nhiều thời gian tổng hợp, xác nhận quy cách, và nhập đơn thủ công, dẫn đến hiệu suất thấp và dễ sót đơn.

5. **Nhiều nguồn dữ liệu, khó tích hợp & phân tích nội bộ**: dữ liệu bán hàng nằm rải rác ở Digiwin ERP, Salesforce, Google Workspace, và log hội thoại Line, thiếu một “**data hub / data middle platform**” để hợp nhất dữ liệu khác loại; quản lý khó nắm bức tranh doanh số thực theo thương hiệu trên nhiều kênh.

### 3. Kiến trúc giải pháp (Solution Architecture)

Giải pháp lấy **Craveva AI Enterprise** làm nền, xây dựng theo “**ngăn xếp module (Modular Stack)**” để tạo trung tâm phân phối số cho Miao Lin. Kiến trúc có 3 lớp: **lớp vận hành cốt lõi**, **lớp định giá thông minh**, và **lớp tăng cường AI**.

#### 3.1 Cấu hình module cốt lõi (Core Modules Configuration)

Theo nhu cầu quản lý đa thương hiệu của nhà nhập khẩu, chọn các module cấp doanh nghiệp làm “xương sống”:

-   Module chuỗi cung ứng & mua hàng (Supply Chain & Procurement)

    -   Trung tâm tồn kho đa thương hiệu: HQ quản lý tập trung master data và tồn kho của nhiều thương hiệu (ví dụ bột mì Nhật, bơ Pháp), hỗ trợ quản lý lô và hạn dùng.
    -   Bộ chuyển đổi SKU ảo (Virtual SKU Mapper): tự động chuyển “đơn vị bán (Unit)” ở đơn front-end thành “đơn vị tồn kho (Carton)” mà ERP chấp nhận để đẩy dữ liệu.

-   Module tài chính (Financial Management)

    -   Tích hợp công nợ B2B: xử lý hoá đơn, credit note (phiếu giảm trừ), và tính thuế; đảm bảo dữ liệu tài chính xuất ra tương thích Digiwin ERP.

-   Quản trị khách hàng & đối tác (Customer & Partner Management)

    -   Cổng khách hàng B2B (B2B portal): tài khoản phân cấp; khách ở các cấp (tổng công ty chuỗi vs cửa hàng nhượng quyền) có quyền mua và “tầm nhìn giá” tương ứng.

-   Insight & Governance
    -   Data hub: tổng hợp dữ liệu dị thể từ ERP/Line/Web; cung cấp kiểm soát quyền theo vai trò (RBAC) và dashboard theo vai trò.

#### 3.2 Công cụ định giá thông minh (Intelligent Pricing Engine)

Để giải quyết hệ thống báo giá rất phức tạp, nền tảng tích hợp logic “ghi đè theo ưu tiên (Override Logic)” gồm 5 tầng, đảm bảo tính chính xác và tuân thủ hợp đồng:

-   Level 1: **Giá cơ sở (Base Price)** — giá đề xuất/giá nền (MSRP) của sản phẩm.
-   Level 2: **Giá công khai (Public Price)** — ghi đè Level 1. Dành cho khách “chưa đăng nhập”, thường cao hơn để bảo vệ hệ thống phân phối. (Ghi chú trong tài liệu: không nên công khai cho khách chưa đăng nhập.)
-   Level 3: **Giá theo nhóm/tier (Tier Pricing)** — ghi đè Level 2. Thiết lập chiết khấu chung theo nhóm khách (ví dụ đại lý bạch kim, kênh nhà hàng), ví dụ toàn bộ 9%.
-   Level 4: **Giá hợp đồng riêng theo khách (Client-Specific Pricing) [ưu tiên cao nhất]** — ghi đè tất cả lớp dưới.
    -   Cơ chế đồng bộ hằng ngày: hỗ trợ nhập Excel từ ERP mỗi ngày. Khoá giá tuyệt đối theo “khách cụ thể + SKU cụ thể” để khớp hợp đồng offline.
-   Level 5: **Chiết khấu theo lượng (Volume Discount) [chỉnh sửa cuối]** — trên nền các mức giá trên, nếu số lượng đạt ngưỡng (ví dụ 10 gói/1 thùng) thì tự kích hoạt ưu đãi thêm hoặc giá theo thùng.

Các ghi chú/câu hỏi mở xuất hiện trong tài liệu:

-   “Không cần: có cần bổ sung số lượng mua tích luỹ trong một khoảng thời gian không?”
-   “Nếu khác batch/lô nhập kho thì áp giá thùng như thế nào?” (cần kiểm tra mức độ chấp nhận của Digiwin; hiện tại Digiwin đang chọn giá bằng tay trong đơn)
-   “Giá hàng cận date: điều khiển theo batch/lô; theo hạn dùng; lúc nhập kho sẽ thiết lập logic (%) và giá.”
-   Gợi ý đánh giá: giai đoạn 1 chỉ dùng giá tiêu chuẩn + giá khách riêng, và tích hợp Line; giai đoạn 2 mới dùng các cơ chế khuyến mãi đặc biệt.
-   Đồng bộ/nhập xuất dữ liệu sản phẩm: “không cần hình ảnh, chỉ cần text”.

#### 3.3 Các năng lực AI (AI Capabilities)

Tài liệu đề xuất đưa AI Agent vào để nâng cấp từ hệ thống “nhận đơn thụ động” sang “trợ lý nghiệp vụ chủ động”:

1. **AI Procurement Assistant (AI trợ lý mua hàng)**

-   Bối cảnh: giải quyết nút thắt hiệu suất nhận đơn qua Line và tin nhắn.
-   Chức năng:
    -   Đặt hàng bằng ngôn ngữ tự nhiên: khách nhắn Line kiểu “cho tôi như đơn hôm thứ Hai tuần trước, thêm 2 thùng bơ”; AI phân tích ý định, đối chiếu lịch sử, tạo link giỏ hàng chuẩn để khách xác nhận.
    -   Cơ chế chống sai: nếu chỉ dẫn mơ hồ (nói “bột” nhưng có 3 quy cách) AI sẽ hỏi lại để xác nhận.
    -   Ghi chú: cần kiểm tra tồn kho nhưng không cần nói cho khách số lượng tồn thực.

2. **AI Sales Agent (AI nhân viên sales)**

-   Bối cảnh: tư vấn sản phẩm 24/7 và hỗ trợ phát triển kinh doanh.
-   Chức năng:
    -   Knowledge base sản phẩm: học spec (PDF) của toàn bộ thương hiệu; trả lời câu hỏi như “hàm lượng béo?” “xuất xứ?” nhanh và chính xác.
    -   Gợi ý liên quan: dựa trên lịch sử xem sản phẩm để đề xuất mua kèm.

3. **AI Data Analysis (AI phân tích dữ liệu — dùng nội bộ)**

-   Bối cảnh: insight vận hành theo kênh và theo thương hiệu.
-   Chức năng:
    -   Hợp nhất dữ liệu đa kênh: phân tích chéo dữ liệu giao hàng từ ERP và dữ liệu nhận đơn Line/Web.
    -   Insight chủ động: tạo báo cáo tuần, phát hiện bất thường (ví dụ tồn kho quay vòng giảm 15% đề xuất khuyến mãi; khách B 2 tuần không đặt → cảnh báo rời bỏ).

#### 3.4 Kiến trúc tích hợp “headless commerce” (Headless Commerce Integration)

Do Miao Lin cần UI/UX và hình ảnh thương hiệu mức độ tuỳ biến cao, giải pháp hỗ trợ kiến trúc “headless”: Craveva là backend engine; team thiết kế/đối tác của khách phát triển frontend và kết nối qua API.

A) Phân định trách nhiệm hệ thống

-   Frontend (do team thiết kế bên ngoài): UI/UX, giao diện, RWD, luồng mua hàng.
-   Backend (do Craveva): trung tâm hàng hoá & tồn kho, tính giá theo logic 5 tầng, API AI cho chat/recommendation.

B) Luồng tích hợp API (Data Flow)

-   Catalog API: lấy danh mục, ảnh, spec, tồn kho khả dụng.
-   Pricing API: frontend gửi `Client_ID` + `Product_ID` → Craveva trả `Final_Price` (bao gồm hợp đồng Level 4).
-   Order API: khi checkout, đẩy order vào Craveva để thực thi “SKU ảo” và “xuất ERP”.
-   AI Widget: nhúng AI Sales Agent dạng chat widget hoặc API.

### 4. SOP vận hành hằng ngày (Daily Operational Workflow)

Để phù hợp mô hình “trao đổi file hằng ngày”, tài liệu mô tả SOP:

-   Đồng bộ buổi sáng (08:30): quản trị xuất **bảng tồn kho** và **bảng giá khách hàng** từ Digiwin ERP; upload lên Craveva; hệ thống cập nhật tồn kho bán được và giá riêng Level 4.
-   Nhận đơn trong ngày:
    -   Line/Text: AI trợ lý mua hàng xử lý.
    -   Web portal: khách đăng nhập đặt hàng và thấy giá riêng realtime.
-   Xuất buổi tối (tối đa tới 11:00 sáng hôm sau — cần xác nhận): quản trị tải “bảng tổng hợp đơn ngày”; hệ thống thực thi **Virtual SKU mapping** để chuyển “đơn vị lẻ” sang “mã thùng (Box SKU)” cần cho ERP; import vào Digiwin ERP để hoàn tất hạch toán.
-   Câu hỏi mở: cơ chế xử lý trường hợp “đơn bị import trùng” (được ghi chú trong tài liệu).

### 5. Lộ trình triển khai (Implementation Roadmap)

Giai đoạn 1 dự kiến **12–14 tuần** để go-live (không bao gồm thiết kế frontend và phần tích hợp frontend):

-   Phase 1 (Tuần 1–4): nền tảng & làm sạch dữ liệu

    -   Xây dựng cơ sở dữ liệu sản phẩm đa thương hiệu.
    -   Nhiệm vụ chính: định nghĩa template nhập từ Digiwin (Excel) và quy tắc chuyển đổi SKU.

-   Phase 2 (Tuần 5–8): huấn luyện AI & cấu hình định giá

    -   Huấn luyện AI nhận biết ngôn ngữ đặt hàng trong Line (ví dụ “thêm”, “như lần trước”).
    -   Cấu hình logic giá 5 tầng và kiểm thử quy trình nhập giá hằng ngày.

-   Phase 3 (Tuần 9–11): diễn tập quy trình đa kênh

    -   Mô phỏng vòng kín: “ERP export → Craveva nhận đơn (Line/Web) → chuyển mã → ERP import”, đảm bảo dữ liệu không sai lệch.

-   Phase 4 (Tuần 12–14): UAT & go-live
    -   Chuyển đổi chính thức, đào tạo nhân sự, vận hành AI assistant.

### 6. Đề xuất đầu tư (Investment Proposal)

Khuyến nghị gói **Craveva Enterprise Plan** (bản tuỳ biến doanh nghiệp):

A) Phí triển khai & phát triển tuỳ biến (Project Implementation Fee) — chi phí một lần

-   Phát triển module SKU Mapping (chuyển đổi định dạng Digiwin ERP).
-   Tích hợp AI phân tích đơn hàng từ Line.
-   Cấu hình tự động hoá nhập giá hằng ngày.
-   Hỗ trợ tích hợp API cho frontend tuỳ biến:
    -   Cung cấp tài liệu Storefront API.
    -   Họp kỹ thuật với team thiết kế.
    -   Hỗ trợ test tích hợp (môi trường sandbox).
-   Dịch vụ triển khai tại chỗ & hỗ trợ làm sạch dữ liệu.

B) Phí thuê bao hệ thống (System Subscription Fee) — theo tháng/năm

-   Quyền mở rộng không giới hạn: unlimited users, truy cập tất cả module.
-   Cloud hosting & vận hành: SLA 99.9%, backup an toàn.
-   Nâng cấp hệ thống: nhận cập nhật tính năng, vá bảo mật, tối ưu hiệu năng trong thời gian thuê bao.

C) Phí sử dụng AI (AI Usage Fee) — theo token (pay-per-token)

-   Áp dụng cho các tác vụ AI có mức sử dụng lớn (phân tích hội thoại Line, phân tích dữ liệu quy mô lớn, hỏi đáp knowledge base…)
-   Ưu điểm: kiểm soát chi phí theo mức dùng thực tế; mở rộng linh hoạt.

### 7. Kết luận & bước tiếp theo

Giải pháp nhằm giải phóng Miao Lin khỏi các công việc thủ công trong nhận đơn và duy trì báo giá. Qua cơ chế chuyển đổi thông minh và AI đa kênh, doanh nghiệp có thể có ngay nền tảng xử lý logic B2B phức tạp, tích hợp đơn Line, và phối hợp chặt với ERP hiện hữu.

Các bước tiếp theo đề xuất:

7.1 Hành chính & thương mại

-   [v] Ký NDA: trước khi truy cập dữ liệu ERP và danh sách khách hàng.
-   [ ] Xác nhận yêu cầu, báo giá, phạm vi hợp đồng: chốt phạm vi, module thuê bao, và phạm vi dev tuỳ biến.

    7.2 Chuẩn bị dữ liệu kỹ thuật

-   [ ] Cung cấp template import chuẩn của Digiwin (Excel/CSV) (chờ xác nhận): phục vụ phát triển Virtual SKU Mapper; cần chỉ rõ trường bắt buộc (mã khách, mã tồn kho, mã kho…)
-   [v] Cung cấp mẫu master data hàng hoá & tồn kho hiện tại: dùng test “morning sync”; gồm tên sản phẩm, SKU (đơn vị lẻ/thùng), định dạng batch/lô…
-   [ ] Thu thập dữ liệu huấn luyện AI:

    -   Lịch sử hội thoại Line: ~50–100 tin nhắn đã ẩn danh (ảnh hoặc text) để huấn luyện các cụm như “như cũ”, “thêm”. (Ghi chú: cân nhắc khả năng dùng Line API để crawl lịch sử.)
    -   Knowledge base: spec, DM, FAQ dạng PDF/Word.

    7.3 Khởi động phối hợp dự án

-   [ ] Họp kick-off kỹ thuật 3 bên (Miao Lin + team thiết kế web + Craveva): bàn giao Storefront API, xác nhận luồng tích hợp (login, hỏi giá, đặt hàng), phân định trách nhiệm xử lý lỗi.
-   [ ] Workshop định nghĩa quy tắc SKU mapping: cùng nghiệp vụ rà từng thương hiệu, xác định quy tắc “đơn vị lẻ → thùng” (ví dụ bột A 10 gói/thùng; bơ B 12 hộp/thùng).

Ghi chú cuối tài liệu:

-   “Chờ xác nhận: AI demo”.
