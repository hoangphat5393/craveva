# Hướng dẫn thao tác UI — Phase 2 (Lập kế hoạch & Tiền sản xuất sau Sales Order)

**Phạm vi nghiệp vụ (Biomixing):** sau khi đã có **Sales Order** — lập kế hoạch sản xuất/giao hàng, kiểm BOM & tồn, mua bổ sung nếu thiếu, nhận nguyên liệu, tạo công việc (task), chuẩn bị nhãn/lô — khớp `PHASE2_PLANNING_PREPRODUCTION.mmd` và subgraph **P2** trong `PHASE1_TO_3_END_TO_END_FLOW.mmd`.

**Lưu ý tên “Phase 2”:** trong `FUNC_IMPROVE/BIOMIXING_GAP_STATUS_VI.md`, backlog **kỹ thuật** (CCP, Receiving QC, Quality lock DO) là phase sau. Tài liệu **này** mô tả **planning / pre-production trên Hub** (trước/kèm module Production). Production vận hành: `FUNC_LOGIC/PRODUCTION_OPERATIONS_LIVE_VI.md`.

---

## 1. Điểm xuất phát

- Đã hoàn tất **Phase 1** (xem `UI_RUNBOOK_PHASE1_QUOTATION_TO_SO_VI.md`).
- Trên Hub đã có **Sale Order** (Operations → **Sale Orders**), ví dụ URL `/account/orders/{id}`.

---

## 2. Tổng quan bước trên Hub (ánh xạ sơ đồ P2)

| Bước sơ đồ                   | Ý nghiệp vụ                     | Gợi ý menu / khu vực trên Hub                                                                                                                                                                                       |
| ---------------------------- | ------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Create Production Project    | Gom việc xưởng theo đơn / khách | **Work Management** → **Projects** — tạo project mới, gán khách hàng & thông tin đơn hàng; có thể **liên kết project** với đơn hàng (trường project trên order — xem chỉnh sửa order hoặc tạo order đã gắn project) |
| AI: Check BOM & Stock        | Đối chiếu công thức & tồn       | **AI Workspace** (nếu bật) + **Products** / **Inventory** / **Warehouses** / **Stock batches** — đối chiếu thủ công hoặc qua công cụ đã triển khai                                                                  |
| Stock sufficient?            | Quyết định mua hay không        | **Inventory** / báo cáo tồn theo kho & lô (tenant đã bật module Warehouse)                                                                                                                                          |
| Purchase missing ingredients | Mua nguyên liệu                 | **Operations** → **Purchase Order** → xử lý duyệt PO theo quy trình công ty                                                                                                                                         |
| Receive raw materials        | Nhập kho RM                     | **Goods Received Note** (GRN) / luồng nhận hàng gắn PO trong **Purchase**                                                                                                                                           |
| Generate tasks               | Chia nhỏ công việc xưởng        | Vào **Project** đã tạo → **Tasks** — thêm task (cân, trộn, in nhãn, v.v.) hoặc dùng template task nếu công ty cấu hình                                                                                              |
| Print labels & batch #       | Chuẩn bị nhãn lô                | Thực hiện qua task + quy trình xưởng; dữ liệu lô có thể tham chiếu **Stock batches** / quy trình in của tenant                                                                                                      |

---

## 3. Thao tác UI chi tiết (thứ tự gợi ý)

### 3.1 Xác nhận Sales Order

1. **Operations** → **Sale Orders**.
2. Mở đơn vừa tạo từ báo giá: kiểm **Billed To**, dòng hàng, ngày giao (nếu có).
3. (Tùy chọn) Gắn **Project**: nếu form **Edit** order có chọn **Project**, chọn project sản xuất đã có hoặc tạo project trước rồi gán lại vào order — giúp lọc đơn theo project trên danh sách.

### 3.2 Tạo “Production project” (container công việc)

1. **Work Management** → **Projects** → **Add Project** (hoặc tương đương).
2. Đặt tên theo đơn / khách (ví dụ `FreshTea — BioMix Detox — SO xxx`).
3. Gán **Client**, timeline, thành viên như quy trình PM nội bộ.
4. Trong project: tạo **Tasks** tương ứng các bước tiền sản xuất (BOM check, reserve stock, PO follow-up, **in nhãn & batch**).
    - Đây là cách Hub **hiện tại** thường mô phỏng “production planning” trước/kèm module **Production** (`BIOMIXING_GAP_STATUS_VI.md`).

### 3.3 Kiểm tồn & BOM (thủ công / bán tự động)

**Loại sản phẩm (bắt buộc trước khi tạo BOM):** xem [`FUNC_LOGIC/PRODUCTION_PRODUCT_TYPES_VI.md`](../FUNC_LOGIC/PRODUCTION_PRODUCT_TYPES_VI.md) và SOP [`PRODUCTION_MODULE_SOP_VI.md`](./PRODUCTION_MODULE_SOP_VI.md) mục 0–2.

| Cần tạo / kiểm             | Product type                       | Menu                  |
| -------------------------- | ---------------------------------- | --------------------- |
| Thành phẩm bán / đầu ra SX | **Manufactured product** (`goods`) | Operations → Products |
| Nguyên liệu công thức      | **Raw Material**                   | Operations → Products |
| Bao bì (nếu trừ tồn)       | **Packaging**                      | Operations → Products |

1. **Products**: tạo đủ FG (`goods`) + NVL (`raw_material`, …) — **không** dùng chung một loại cho cả hai.
2. **Production → Bill of Materials**: output = FG; components = NVL/bao bì.
3. **Inventory** / **Warehouses** / **Stock batches**: tồn khả dụng theo kho NL trên lệnh SX.
4. Nếu thiếu: task project hoặc **Purchase Order** (mua NVL, không mua FG như mua NL).

### 3.4 Mua và nhận hàng (khi tồn không đủ)

1. **Operations** → **Purchase Order** — tạo PO tới nhà cung cấp nguyên liệu.
2. Khi hàng về: **Goods Received Note** (và các bước nhập kho theo module Purchase/Warehouse của tenant).
3. Cập nhật lại task “đã đủ RM” trên project.

### 3.5 Chuẩn bị nhãn & số lô

1. Giao task cho xưởng/QA: nội dung task mô tả **batch number**, quy tắc in nhãn, HSD (nếu có).
2. Dữ liệu lô trên hệ thống: cập nhật theo policy tại **Stock batches** / nhập kho — chi tiết kỹ thuật xem runbook kho trong repo nếu cần.

### 3.6 AI hỗ trợ BOM & stock

- Không thay thế việc tạo PO/GRN hay task; dùng **AI Workspace** (nếu được cấu hình) để gợi ý checklist hoặc truy vấn lịch sử — **quyết định vận hành** vẫn do người và chứng từ trên Hub.

---

## 4. Điều kiện chưa có trên Hub (để PM/BA kỳ vọng đúng)

Các hạng mục sau thuộc **roadmap module Production / Quality** (xem `BIOMIXING_GAP_STATUS_VI.md`), **chưa** nhất thiết là một nút wizard trên UI:

- **CCP checkpoint** cứng giữa các bước xưởng.
- **Receiving QC** pass/fail tách biệt trên từng lô nhận.
- **Rework** có duyệt gắn batch nguồn.
- **Quality lock** chặn **Sale Delivery Order** nếu chưa QC/COA.

Khi các tính năng này go-live, bổ sung mục vào runbook này hoặc tách **UI_RUNBOOK_PHASE2_HACCP_VI.md**.

---

## 5. Checklist nhanh (sau SO)

| #   | Việc cần làm                                 | Ghi chú                     |
| --- | -------------------------------------------- | --------------------------- |
| 1   | Mở Sale Order, đối chiếu khách & dòng hàng   | `/account/orders/{id}`      |
| 2   | Tạo / gán **Project**                        | Work Management → Projects  |
| 3   | Kiểm **tồn & lô**                            | Inventory / Stock batches   |
| 4   | Nếu thiếu: **PO** → **GRN**                  | Operations → Purchase       |
| 5   | Tạo **Tasks** (BOM, mua hàng, in nhãn lô, …) | Trong project               |
| 6   | (Tùy) AI Workspace                           | Hỗ trợ, không thay chứng từ |

---

## 6. Bước tiếp theo (Phase 3)

- Sau khi tiền sản xuất xong, luồng **sản xuất & QA** trên sơ đồ nằm ở `PHASE3_PRODUCTION_QA.mmd` — runbook UI Phase 3 có thể tách file khi scope UI được chốt.

## 7. Tài liệu liên quan

- `PROJECT BIOMIXING/PHASE2_PLANNING_PREPRODUCTION.mmd`
- `PROJECT BIOMIXING/PHASE1_TO_3_END_TO_END_FLOW.mmd`
- `PROJECT BIOMIXING/UI_RUNBOOK_PHASE1_QUOTATION_TO_SO_VI.md`
- `FUNC_IMPROVE/BIOMIXING_GAP_STATUS_VI.md`, `FUNC_LOGIC/PRODUCTION_OPERATIONS_LIVE_VI.md`

_Cập nhật: 2026-05-09._
