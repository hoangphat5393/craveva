# Biomixing — Tóm tắt Phase 1 → 4 (quy trình ngắn)

**Mục đích:** một trang nhớ “bản đồ” nghiệp vụ — không đi sâu kỹ thuật hay từng màn hình Hub.

**Một dòng nhớ:**  
**Phase 1 = chốt bán & công thức/giá → Phase 2 = lập lệnh & BOM → Phase 3 = làm thật trên lô & QC số/lô → Phase 4 = giao & tiền.**

---

## Phase 1 — Vào đơn & chốt công thức / giá

- Khách / đại lý cần hàng (thường có **công thức riêng**).
- **Sales** làm báo giá / estimate, thu thập yêu cầu.
- **Duyệt nội bộ** (ví dụ: President xem công thức / lịch sử, VP xem giá & margin).
- **Kết quả:** đơn bán (**Sales Order**) được chốt khi các bước duyệt xong.

---

## Phase 2 — Lập kế hoạch trước khi chạy máy

- Từ đơn đã chốt: **BOM**, số lượng kế hoạch, kho RM / kho FG.
- **Lệnh sản xuất** (draft → release), chốt **snapshot BOM**, chuẩn bị batch.
- **Kết quả:** xưởng biết **làm gì, bao nhiêu, lấy nguyên liệu từ đâu** — sẵn sàng vào chạy lô.

---

## Phase 3 — Chạy lô trên xưởng + kiểm soát chất lượng (số & lô)

- Gán **lô RM**, ghi **tiêu hao**, ghi **thành phẩm / nhận FG**.
- **Lệch số** so với kế hoạch thì có **lý do / duyệt variance** (tùy cấu hình).
- **Truy xuất** RM → batch → movement; có **rework** nếu cần.
- **Kết quả:** có **bằng chứng số** cho từng lô (audit), chưa giao hàng.

---

## Phase 4 — Giao hàng & chốt tài chính

- **DO / giao hàng**, trừ tồn FG đúng luồng kho.
- **Hóa đơn / thanh toán / công nợ** nối với phần đã giao.
- **Kết quả:** hàng ra khỏi xưởng đến khách và **sổ sách** khớp.

---

## Ghi chú

- Các diagram `.mmd` và proposal PDF mô tả **mục tiêu / positioning** (có thể có thêm AI, task Project, v.v.); **triển khai trong Hub** có thể dùng **tên màn hình khác** nhưng vẫn nằm trong bốn “ô” trên.
- Chi tiết pilot / P0: `FUNC_IMPROVE/BIOMIXING_GAP_STATUS.md`, `FUNC_LOGIC/PRODUCTION_BUSINESS.md`.

---

## Phụ lục — Sơ đồ Phase 3 (Production & QA): từng khung làm gì?

Nguồn hình: `PHASE3_PRODUCTION_QA.mmd` / diagram “Phase 3 - Production & QA”. Đây là **nghiệp vụ xưởng + QA** (thường gắn **Project / task** trong proposal), không phải tên menu trên Hub.

**Ghi chú cột “Việc xưởng”:** ô **Có** = việc làm **trên sàn** (người/máy/thực địa). ERP **không** thay thế các bước đó; chỉ **hỗ trợ mã lô, sổ kho, ghi nhận số liệu** (xem phụ lục Hub bên dưới).

| Khung trong sơ đồ              | Ý nghĩa ngắn                                                                                                                                                                         | Việc xưởng (sàn)?                                                                                                                   |
| ------------------------------ | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ | ----------------------------------------------------------------------------------------------------------------------------------- |
| **Task: Print Labels & Batch** | In nhãn và **gán số lô (batch)** cho đợt chạy — mọi bước sau bám đúng **danh tính lô**.                                                                                              | **Có** — xưởng in/dán/gắn nhãn thực tế; ERP chỉ cấp **mã/phiếu in** để đồng bộ giấy tờ.                                             |
| **Task: Ingredient Weighing**  | **Cân / đong** nguyên liệu theo công thức trước khi trộn; có thể bỏ qua nếu đã cân sẵn (trong sơ đồ có nhánh từ “Print Labels” xuống thẳng “Mixing”).                                | **Có** — cân đong tại xưởng; ERP ghi **gán lô RM / post tiêu hao** sau khi nội bộ quy định.                                         |
| **Task: Mixing & Processing**  | **Trộn / xử lý** (máy, thời gian, quy trình) — bước “chạy lô” chính. Mũi tên **Fail** từ QC quay lại đây = không đạt thì **làm lại / xử lý lại** trước khi đóng gói và kiểm tra lại. | **Có** — vận hành máy trộn/xử lý; ERP không có màn “máy trộn”.                                                                      |
| **Task: Packaging**            | **Đóng gói** thành phẩm theo quy cách bán (bao, can, thùng…).                                                                                                                        | **Có** — đóng gói tại xưởng; ERP ghi **FG / nhận kho** khi có số liệu chính thức.                                                   |
| **Quality Check** (hình thoi)  | **QC** lô đã đóng gói: **Pass** → sang bước kế; **Fail** → quay lại **Mixing & Processing**.                                                                                         | **Có** — kiểm đồ thật (mẫu, cảm quan, lab…); ERP chỉ phản ánh phần **sổ** (variance, rework, post FG) nếu dùng Hub.                 |
| **AI Agent: Validate Certs**   | Trong proposal: **tự động / AI** rà **chứng từ – chứng nhận** (COA, checklist…) trước khi coi lô **đủ điều kiện hoàn tất**.                                                          | **Không** — không phải việc sàn; là **công cụ/roadmap** (chưa có trên Hub pilot).                                                   |
| **Project Completed**          | Kết thúc **chuỗi task** của lô/project theo mô hình tài liệu — “lô xong” từ góc quy trình.                                                                                           | **Không** — đóng **task quản trị dự án** (MS Project / PM); song song, ERP có thể coi **order/batch completed** khi đủ bước ghi sổ. |

**Một dòng đọc sơ đồ:** In lô → (cân RM) → trộn/xử lý → đóng gói → QC đạt/không → (nếu đạt) kiểm chứng chứng từ → xong lô.

---

## Phụ lục — Ánh xạ ngắn sang UI Hub (Craveva) — không thay thế sơ đồ

Hub **không** có từng ô chữ giống “Task: Mixing”; phần **đã có** thường gom vào **Production batch** và cài đặt liên quan:

| Ý trong sơ đồ / proposal                     | Gần nhất trên Hub (tham chiếu)                                                                                                                                                                      |
| -------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Gắn lô, tiêu hao RM, nhận FG                 | `/account/production/batches/{id}` — RM consumptions, Post RM consumption; FG outputs; Post FG receipt.                                                                                             |
| QC theo **số / ngưỡng / variance**           | `/account/production/fg-quantity-policy`; trên batch: duyệt variance (khi bật config) trước post FG.                                                                                                |
| Truy xuất lô                                 | `/account/production/batches/{id}/trace` — trace RM / movement.                                                                                                                                     |
| Lô không đạt, xử lý lại                      | Trên batch: **Rework orders** / Request rework (khác hẳn vòng “Fail → Mixing” trong hình nhưng cùng họ “ngoài happy path”).                                                                         |
| **Print labels & batch #** (in nhãn / mã lô) | Trên batch: khối **Ma lo (shop floor)** + nút **Mở phiếu in** → `/account/production/batches/{id}/print-label-slip` (in hoặc PDF từ trình duyệt). **AI Validate Certs** / task Project vẫn roadmap. |
