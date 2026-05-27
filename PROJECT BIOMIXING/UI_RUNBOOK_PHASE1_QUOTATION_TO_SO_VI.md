# Hướng dẫn thao tác UI — Phase 1 (Báo giá → Duyệt nội bộ → Sales Order)

**Phạm vi nghiệp vụ (Biomixing):** từ tạo báo giá (Estimate / Quotation) đến **President review**, **VP Pricing review**, rồi **chuyển Sales Order** — khớp `PHASE1_QUOTATION_FLOW_DIAGRAM.mmd` và `PHASE1_TO_3_END_TO_END_FLOW.mmd` (subgraph P1).

**Lưu ý tên “Phase 1”:** trong repo, **Phase 1 báo giá** (Estimate) khác **Production MVP** (BOM, lệnh SX — xem `FUNC_LOGIC/PRODUCTION_OPERATIONS_LIVE_VI.md`). Tài liệu **này** chỉ nói **báo giá → SO**.

---

## 1. Điều kiện chung

- Đăng nhập **Workspace Hub** (tài khoản nhân viên nội bộ, không phải portal khách hàng nếu cần đủ menu duyệt).
- Quyền tối thiểu (thực tế do admin cấu hình role):
    - Xem/sửa báo giá: `view_estimates`, `edit_estimates` (theo policy _all_ / _added_ / …).
    - **President / VP:** thường gắn với user có quyền sửa estimate như trên (chi tiết gán vai trò do BA/PM — xem `PHASE_BUSINESS_CONTEXT_EXAMPLE.md` §6.3).
    - **Convert to Sales Order:** cần quyền tạo estimate kiểu `add_estimates` = _all_ hoặc _added_ (để menu **Convert** hiển thị khi đủ điều kiện nghiệp vụ).

---

## 2. Đường dẫn menu trên Hub

1. Sidebar **Sales** → **Quotation** (ngôn ngữ giao diện có thể hiển thị “Quotation” / “Báo giá”).
2. URL tham chiếu (sau khi đăng nhập, domain theo môi trường của bạn): danh sách báo giá thường là `/account/estimates`.
3. Mở **một dòng báo giá** → màn **chi tiết** (URL dạng `/account/estimates/{id}` — `{id}` là id bản ghi, **không** nhất thiết trùng số hiển thị EST#…).

---

## 3. Luồng thao tác từng bước

### Bước 1 — Tạo / chỉnh báo giá

- Trên danh sách Quotation: dùng nút **Add** / **Tạo** (theo bản dịch) để tạo estimate mới **hoặc** **Edit** một báo giá ở trạng thái cho phép sửa.
- Điền: khách hàng, dòng hàng, điều kiện, ngày hiệu lực, v.v. → **Save**.

### Bước 2 — Gửi báo giá (đưa sang trạng thái chờ / thương mại)

- Trên **chi tiết** báo giá, mở menu **Action** (nút thả xuống).
- Chọn **Send** (gửi cho khách / đánh dấu đã gửi — tùy cấu hình tenant).
- **Quan trọng:** mục **Convert to Sales Order** chỉ xuất hiện khi trạng thái báo giá là **`waiting`** (và đã đủ duyệt nội bộ — xem bước 4–5). Nếu chỉ ở **`draft`**, có thể vẫn duyệt President/VP trên giao diện nhưng **chưa** thấy **Convert** cho đến khi báo giá được đưa sang `waiting` (thường qua **Send** hoặc luồng đổi trạng thái tương đương trong công ty bạn).

### Bước 3 — AI / tham chiếu công thức (tùy triển khai)

- Trên sơ đồ có bước **AI Agent: Check Recipe History** — đây **không** thay thế nút duyệt trên cùng màn Estimate.
- Nếu công ty bật **AI Workspace** hoặc tích hợp kênh khác, dùng đúng module đó để tra cứu; **chốt thương mại** vẫn nằm trên báo giá trong Hub (`PHASE_BUSINESS_CONTEXT_EXAMPLE.md` §6.5–6.6).

### Bước 4 — President review

- Trên **chi tiết** báo giá, **Action**:
    - **President approve** hoặc **President reject**.
- Xác nhận trong hộp thoại (SweetAlert); có thể nhập **ghi chú**.
- **Thứ tự hệ thống:** phải **President approve** trước thì bước VP Pricing mới hợp lệ (API từ chối nếu bỏ qua).

### Bước 5 — VP Pricing review

- Cùng màn chi tiết, **Action** (sau khi President đã approve):
    - **VP pricing approve** hoặc **VP pricing reject** → xác nhận / ghi chú.
- **Reject:** báo giá trả về cho Sales chỉnh scope hoặc giá rồi lặp lại từ bước phù hợp.

### Bước 6 — Convert to Sales Order

- Điều kiện **đồng thời** (theo giao diện `resources/views/estimates/ajax/show.blade.php`):
    1. `status == waiting`
    2. President **và** VP đều **approved** (hoặc báo giá “legacy” chưa có trạng thái review — cả hai null — được coi là đã qua cửa review).
    3. Quyền `add_estimates` đủ điều kiện (all/added).
- **Action** → **Convert to Sales Order** → hộp thoại → **Convert**.
- Sau khi thành công: hệ thống tạo **Sale Order**; mở **Operations** → **Sale Orders** (hoặc từ thông báo/link nếu có) để xem đơn — URL dạng `/account/orders/{orderId}`.

---

## 4. Khách hàng (portal client)

- Nếu báo giá ở trạng thái **`waiting`** và user đang là **khách** được gán: trên menu **Action** có thể có **Accept** / **Decline** (chấp nhận / từ chối báo giá) — khác với duyệt nội bộ President/VP.

---

## 5. Checklist nhanh (UAT / demo)

| #   | Việc cần làm                      | Kỳ vọng                          |
| --- | --------------------------------- | -------------------------------- |
| 1   | Tạo báo giá, có dòng hàng + khách | Lưu thành công                   |
| 2   | **Send** (hoặc đưa về `waiting`)  | Trạng thái phù hợp để convert    |
| 3   | President approve                 | VP actions khả dụng              |
| 4   | VP pricing approve                | Đủ điều kiện thương mại          |
| 5   | **Convert to Sales Order**        | Có bản ghi mới trong Sale Orders |

---

## 6. Tài liệu liên quan trong repo

- `PROJECT BIOMIXING/PHASE1_QUOTATION_FLOW_DIAGRAM.mmd`
- `PROJECT BIOMIXING/PHASE_BUSINESS_CONTEXT_EXAMPLE.md` (§6.3–6.4)
- `PROJECT BIOMIXING/UI_RUNBOOK_PHASE2_PLANNING_PREPRODUCTION_VI.md` — bước tiếp theo sau SO.

_Cập nhật: 2026-05-09 — căn theo luồng đã demo trên Hub (Quotation → duyệt → SO)._
