# Yêu cầu PM Biomixing — Bản gộp tiếng Việt

_Tài liệu chính thức gộp từ: (1) spec Gary — [`PM_REQUEST.md`](./PM_REQUEST.md) / [`PM_REQUEST_VI.md`](./PM_REQUEST_VI.md); (2) phần Phase 1 báo giá OEM từ [`PM REQUEST CHAT.md`](./PM%20REQUEST%20CHAT.md). **Đã bỏ** mọi bước AI / AI Agent. Rà soát thuật ngữ ERP bằng tiếng Việt có dấu._

| Tài liệu liên quan                                                                                              | Mục đích                                      |
| --------------------------------------------------------------------------------------------------------------- | --------------------------------------------- |
| [`FUNC_IMPROVE/PHASE1_PM_STATUS_LIVE_VI.md`](../FUNC_IMPROVE/PHASE1_PM_STATUS_LIVE_VI.md)                       | Tiến độ triển khai (~65%, cập nhật theo code) |
| [`FUNC_IMPROVE/PHASE1_QUOTATION_PM_HUMAN_VI.md`](../FUNC_IMPROVE/PHASE1_QUOTATION_PM_HUMAN_VI.md)               | Tóm tắt ngắn cho dev nội bộ                   |
| [`FUNC_IMPROVE/PHASE1_QUOTATION_PM_GAP_ANALYSIS_VI.md`](../FUNC_IMPROVE/PHASE1_QUOTATION_PM_GAP_ANALYSIS_VI.md) | Gap kỹ thuật, URL, file code                  |

**Ngày gộp:** 20/05/2026 · **Nguồn Gary:** 15/05/2026

---

## 1. Mục tiêu tổng thể

Xây dựng trong Craveva ERP quy trình phù hợp **sản xuất OEM / FMCG** (ví dụ cà phê 3-in-1 theo công thức riêng), gồm hai lớp:

1. **Phase 1 — Báo giá (Quotation / Estimate):** tiếp nhận yêu cầu, công thức (BOM), duyệt công thức & giá, rồi mới chuyển **Sales Order**.
2. **Phase 2 trở đi — Sản xuất & kho:** SO → Lệnh sản xuất → tiêu thụ BOM → cập nhật tồn → giao hàng → hóa đơn.

**Không** tạo module Estimate riêng — nâng cấp module **Báo giá** (`/account/estimates`) sẵn có. **Không** đổi tên menu “OEM Quotation” cho toàn hệ thống; bật/tắt theo **từng công ty** (công ty chỉ bán hàng giữ báo giá thường).

---

## 2. Ví dụ nghiệp vụ chung (Oldtown)

| Hạng mục          | Giá trị                                  |
| ----------------- | ---------------------------------------- |
| Khách hàng        | Oldtown White Coffee                     |
| Thành phẩm        | Cà phê 3-in-1 Custom 150g / gói          |
| Công thức (1 gói) | Đường 50g + Kem 30g + Cà phê Arabica 70g |
| Đơn hàng          | 3.000 gói                                |
| NL cần (tự tính)  | Đường 150 kg, Kem 90 kg, Cà phê 210 kg   |

---

## Phần A — Phase 1: Báo giá OEM & duyệt

### A.1. Estimate = Quotation = Báo giá

Trong ERP, **một module**, khác tên trên menu (Odoo: Quotation; SAP: Sales Quotation; …). Luồng PM **không** phải “tạo báo giá → chuyển SO ngay” mà là **quy trình kiểm soát trước khi bán**.

### A.2. Luồng 6 bước (đã bỏ AI)

```text
(1) Yêu cầu khách        → intake / Estimate Request (mô tả, budget…)
(2) Sales tạo báo giá    → khách, công thức, SL, giá mục tiêu, MOQ, bao bì, SKU OEM
(3) [BỎ] AI kiểm tra     → thay bằng tìm công thức/BOM tương tự thủ công (ưu tiên sau)
(4) President duyệt      → công thức / khả thi sản xuất
(5) VP Pricing duyệt     → giá, margin, rule lãi tối thiểu
(6) Chuyển Sales Order   → chỉ khi đủ duyệt; sau đó mới Production
```

**Không được:**

```text
Báo giá → Sales Order (bỏ qua duyệt)
Báo giá → Production (bỏ qua SO)
```

### A.3. Trang chi tiết báo giá = workspace OEM

Một màn hình làm việc (hiện vẫn form cũ + block; layout **4 vùng** là mục tiêu UX):

| Vùng                 | Nội dung                                                                                               |
| -------------------- | ------------------------------------------------------------------------------------------------------ |
| **1 — Thương mại**   | Khách, số báo giá, sales, ngày, trạng thái / đang chờ ai duyệt                                         |
| **2 — Công thức**    | Tên SP OEM, SKU, bao bì, khối lượng mục tiêu, MOQ, ghi chú                                             |
| **3 — BOM lines**    | Từng NL / bao bì cho **1 đơn vị** TP: SL, đơn giá NL, thành tiền; có thể copy từ Production BOM        |
| **4 — Duyệt & tiền** | President / VP (đồng ý, từ chối, lý do); timeline; tổng cost từ BOM; margin; nút Gửi duyệt / Chuyển SO |

**Hai bảng trên cùng một báo giá (không thay nhau):**

| Bảng                     | Ý nghĩa                         | Ví dụ                   |
| ------------------------ | ------------------------------- | ----------------------- |
| **BOM (trên)**           | Cách làm **1 gói** + chi phí NL | 50g đường + 30g kem + … |
| **Dòng sản phẩm (dưới)** | Khách **mua bao nhiêu × giá**   | 10.000 gói × 2,50 USD   |

### A.4. Trạng thái workflow (đề xuất PM)

| Trạng thái           | Ý nghĩa                               |
| -------------------- | ------------------------------------- |
| Nháp                 | Sales đang soạn                       |
| Chờ duyệt President  | Đã gửi duyệt, chờ TGĐ                 |
| Chờ duyệt VP Pricing | President đã đồng ý công thức         |
| Đã duyệt đủ          | Được chuyển SO                        |
| Cần sửa lại          | Từ chối → trả Sales (không chỉ “hủy”) |
| Đã chuyển SO         | Đã convert                            |

### A.5. Nút & quyền

| Nút / hành động             | Ai thực hiện                     |
| --------------------------- | -------------------------------- |
| Gửi duyệt                   | Sales                            |
| Duyệt / Từ chối (công thức) | President                        |
| Duyệt giá / Trả lại Sales   | VP Pricing                       |
| Chuyển Sales Order          | Sales / Admin (sau khi đủ duyệt) |

Cần **quyền riêng** President và VP — không dùng chung quyền “sửa báo giá”.

### A.6. Timeline / audit (ngành thực phẩm)

Ví dụ PM mong muốn:

```text
15/05 10:00  Sales tạo báo giá
15/05 10:30  Sales gửi duyệt
15/05 11:00  President duyệt
15/05 11:30  VP Pricing duyệt
15/05 11:45  Chuyển Sales Order
```

### A.7. Tìm công thức cũ (không AI)

- Tìm BOM / báo giá tương tự (cùng khách, cùng NL, margin trước đó, MOQ trước).
- Ví dụ kết quả: “BOM-2025-014, margin 22%, MOQ 5.000”.
- **Không** có AI Agent bắt buộc trong Phase 1.

### A.8. So sánh báo giá thường vs OEM

|                 | Chỉ bán hàng        | OEM / sản xuất         |
| --------------- | ------------------- | ---------------------- |
| Dòng báo giá    | SP × SL × giá       | Thêm BOM + duyệt 2 cấp |
| Cài đặt công ty | **Tắt** OEM Phase 1 | **Bật** OEM Phase 1    |
| Sau chốt        | Giao hàng           | SO → Lệnh SX → batch   |

### A.9. Đặt tên UI (khuyến nghị từ chat — không bắt buộc đổi menu toàn cục)

Tránh viết tắt kỹ thuật trên label người dùng (`RM`, `FG`, `consumption`):

| Nên dùng                      | Tránh        |
| ----------------------------- | ------------ |
| Nguyên liệu                   | RM           |
| Thành phẩm                    | FG           |
| Số lượng sử dụng              | consumption  |
| Trừ tồn kho / Nhập thành phẩm | thuật ngữ DB |

Menu có thể giữ **Báo giá / Quotation**; workspace nội bộ có thể gọi “Báo giá sản xuất OEM”.

### A.10. Kiến trúc luồng Phase 1 (kỹ thuật)

```text
Báo giá
  → Duyệt công thức (President)
  → Duyệt giá (VP + rule margin)
  → Sales Order
```

Phase 2: SO → Lệnh sản xuất → kế hoạch NVL → batch → hoàn thành TP.  
Phase 3: Lệnh giao hàng → Hóa đơn → Thanh toán.

---

## Phần B — Sản xuất, BOM, kho (spec Gary)

### B.1. Vấn đề hiện tại (BOM UX)

- Dropdown BOM trộn **tất cả** sản phẩm → dễ chọn sai, UX kém, tồn kho sai.
- Cần tách: thành phẩm / nguyên liệu / bao bì / dịch vụ.

### B.2. Product Master

- Tạo sản phẩm tại **Hoạt động → Sản phẩm**.
- **BOM không tạo sản phẩm** — chỉ tham chiếu sản phẩm đã có.

**Loại sản phẩm đề xuất:**

| Loại             | Mục đích              |
| ---------------- | --------------------- |
| `raw_material`   | Nguyên liệu thô       |
| `finished_goods` | Thành phẩm bán được   |
| `semi_finished`  | Bán thành phẩm        |
| `packaging`      | Bao bì                |
| `service`        | Dịch vụ không tồn kho |

### B.3. Khái niệm BOM

BOM = công thức: **cần gì để sản xuất 1 đơn vị** thành phẩm.

**Header BOM (đề xuất):** mã BOM, tên, phiên bản, số lượng đầu ra, UOM, trạng thái (active), waste % trên từng dòng.

**Lọc dropdown (bắt buộc):**

- Chọn **thành phẩm** (FG): chỉ `product_type = finished_goods`.
- Chọn **thành phần BOM**: chỉ `raw_material`, `semi_finished`, `packaging`.

### B.4. Tính NVL theo đơn hàng

Đặt 3.000 gói → hệ thống nhân định mức BOM × 3.000 (150 kg đường, …).

### B.5. Logic tồn kho sau sản xuất

| Hướng   | Ví dụ                                     |
| ------- | ----------------------------------------- |
| Trừ NL  | Đường −150 kg, Kem −90 kg, Cà phê −210 kg |
| Tăng TP | Oldtown 3-in-1 Custom +3.000 gói          |

### B.6. Màn hình lệnh sản xuất

Cần rõ: lệnh SX nào, NL kế hoạch, batch NL đã dùng, đã trừ kho chưa, đã nhập TP chưa, có rework không.  
Thông báo “Không có BOM liên kết” là đúng nếu chưa gán BOM — cần UX gán BOM / FG rõ ràng.

**Trường đề xuất:** số lệnh, FG, BOM, SL kế hoạch / thực tế, kho NL, kho TP, mã lô, trạng thái (Nháp → Đã phát hành → Đang SX → Hoàn thành → Đóng).

### B.7. Luồng trạng thái tài liệu

| Tài liệu     | Trạng thái                                     |
| ------------ | ---------------------------------------------- |
| Đơn bán (SO) | Nháp → Đã xác nhận                             |
| Lệnh SX      | Nháp → Phát hành → Đang SX → Hoàn thành → Đóng |
| Lệnh giao    | Chờ → Đã chọn → Đã giao                        |
| Hóa đơn      | Nháp → Đã gửi → Đã thanh toán                  |

### B.8. Quy trình ERP đầy đủ (Gary — 9 bước)

1. Tạo khách hàng (Sales → Khách hàng)
2. Tạo nguyên liệu thô (Hoạt động → Sản phẩm)
3. Tạo thành phẩm
4. Tạo BOM (Production → Bill of Materials)
5. Tạo **Sales Order** (sau báo giá đã duyệt ở Phase 1)
6. Tạo lệnh sản xuất — gắn BOM, tính NVL, dự trữ
7. Hoàn thành SX — trừ NL, nhập TP
8. Lệnh giao hàng
9. Hóa đơn

### B.9. Khuyến nghị bổ sung (ưu tiên sau)

| Hạng mục                           | Ghi chú                  |
| ---------------------------------- | ------------------------ |
| Chuyển đổi UOM (g, kg, gói, thùng) | Ví dụ 1.000 g = 1 kg     |
| Truy xuất lô (batch, NSX, HSD)     | Quan trọng FMCG / cà phê |
| Phiên bản BOM (V1, V2, lưu trữ)    | Khi đổi công thức        |
| BOM bao bì (gói, túi, thùng, nhãn) | Tách loại `packaging`    |

### B.10. Mô hình lớp (Gary)

```text
Product Master
  ├── Nguyên liệu / Bao bì / Bán TP / Thành phẩm
BOM → Công thức
Lệnh sản xuất → Thực thi
Tồn kho → Chuyển kho
Sales Order → Thương mại
```

**Ưu tiên cao (sản xuất):** phân loại SP, lọc dropdown BOM, cấu trúc BOM, tiêu thụ tồn, tự tính NVL theo SL đơn.  
**Ưu tiên trung bình:** lô, phiên bản BOM, UOM, bao bì.

---

## 3. Thỏa thuận không làm

| Không làm                                | Lý do                          |
| ---------------------------------------- | ------------------------------ |
| Module Estimate tách riêng               | Trùng Quotation                |
| AI Agent bắt buộc trong luồng            | PM + dev đồng thuận Phase 1    |
| Đổi menu “OEM” toàn hệ thống             | Ảnh hưởng công ty chỉ bán hàng |
| Báo giá → Production trực tiếp           | Phải qua SO đã duyệt           |
| BOM chỉ ở Production, không trên báo giá | Không costing / duyệt được     |

---

## 4. Ánh xạ Phase → triển khai

| Phase PM    | Nội dung                                           | Ghi chú triển khai                                                                                     |
| ----------- | -------------------------------------------------- | ------------------------------------------------------------------------------------------------------ |
| **Phase 1** | Báo giá + BOM trên báo giá + duyệt 2 cấp + chặn SO | **~90% — chốt go-live** · [`PHASE1_PM_STATUS_LIVE_VI.md`](../FUNC_IMPROVE/PHASE1_PM_STATUS_LIVE_VI.md) |
| **Phase 2** | SO → SX → batch → tồn                              | Vận hành: [`PRODUCTION_OPERATIONS_LIVE_VI.md`](../FUNC_LOGIC/PRODUCTION_OPERATIONS_LIVE_VI.md)         |
| **Phase 3** | Giao hàng → Hóa đơn                                | Đã có phần lớn trong ERP                                                                               |

**Công tắc kỹ thuật Phase 1:** module `estimates_phase1_review` / `estimates_phase1_review_enabled()` theo công ty.

---

## 5. Một đoạn PM đọc cho stakeholder

> Báo giá không còn là bảng giá bán lẻ đơn giản. Sales nhập **công thức từng nguyên liệu** trên cùng báo giá với **số lượng bán và giá chào**. Tổng giám đốc duyệt **công thức**, VP duyệt **giá và lãi**. Chỉ khi đủ duyệt mới chuyển Sales Order và đi sản xuất. Master sản phẩm và BOM Production vẫn là nền tảng; BOM trên báo giá là bản chào giá và costing, có thể copy từ Production BOM.

---

_Cập nhật tiến độ: chỉ sửa `FUNC_IMPROVE/PHASE1_PM_STATUS_LIVE_VI.md`, không nhân bản % trong file này._
