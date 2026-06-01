# Phase 1 — Báo giá OEM: PM muốn gì? (bản dễ đọc cho dev & nội bộ)

_Tóm tắt ngắn. **Bản yêu cầu gộp đầy đủ (Gary + Phase 1 chat, tiếng Việt có dấu, không AI):** [`PROJECT BIOMIXING/PM_YEU_CAU_TONG_HOP_VI.md`](../PROJECT%20BIOMIXING/PM_YEU_CAU_TONG_HOP_VI.md)._

**Tiến độ live (đánh dấu ✅/🟡/⬜):** `PHASE1_PM_STATUS_LIVE_VI.md`  
_Technical gap + trạng thái: `PHASE1_PM_STATUS_LIVE_VI.md`._

---

## 1. PM đang nói chuyện gì?

Khách hàng kiểu **sản xuất OEM** (ví dụ cà phê 3-in-1 theo công thức riêng) không mua “1 dòng sản phẩm” đơn giản như bán laptop.

Họ cần:

1. Gửi yêu cầu (khách muốn công thức / số lượng / giá mục tiêu).
2. Sales lập **báo giá** có **công thức (BOM)** và **giá bán**.
3. Lãnh đạo duyệt **công thức có làm được không**.
4. VP duyệt **giá / lợi nhuận có đủ không**.
5. Chỉ sau đó mới **chuyển sang đơn bán (Sales Order)** và đi sản xuất.

PM gọi đây là **Phase 1: Tiếp nhận đơn + duyệt công thức & giá** — không phải chỉ “tạo quotation rồi convert SO”.

Trong ERP, **Estimate = Quotation = Báo giá** — cùng một module, chỉ khác tên trên menu.

---

## 2. Luồng PM mong muốn (đơn giản)

```text
Khách yêu cầu
    → Sales tạo báo giá (có công thức + giá)
    → (tùy chọn: gợi ý công thức cũ — bỏ AI trong scope hiện tại)
    → Tổng giám đốc / President duyệt
    → VP Pricing duyệt giá
    → Chuyển Sales Order
```

**Không** muốn:

```text
Tạo báo giá → Convert SO ngay (không ai duyệt công thức/giá)
```

---

## 3. Trang báo giá chi tiết phải trở thành gì?

PM muốn **một màn hình làm việc**, không chỉ bảng giống hóa đơn.

### Phần trên — Thông tin thương mại

- Khách hàng, số báo giá, sales phụ trách, ngày.
- Trạng thái / giai đoạn duyệt (đang chờ ai?).

### Phần giữa — Công thức & BOM (quan trọng nhất)

**BOM line** = từng nguyên liệu để làm **1 đơn vị thành phẩm**.

Ví dụ 1 gói cà phê 30g:

| Nguyên liệu | Số lượng / 1 gói | Đơn giá NL | Thành tiền |
| ----------- | ---------------- | ---------- | ---------- |
| Đường       | 50g              | …          | …          |
| Kem bột     | 30g              | …          | …          |
| Cà phê      | 70g              | …          | …          |
| Bao bì      | 1 cái            | …          | …          |

Thêm có thể có: MOQ, quy cách đóng gói, SKU OEM, ghi chú.

**Tách hẳn** với bảng dòng bán hiện tại (ví dụ: “10,000 gói × 2.50 SGD”) — đó là thứ khách **mua**, không phải **cách làm**.

### Phần duyệt — Ai đã đồng ý / từ chối

- President: đồng ý / từ chối + lý do.
- VP Pricing: đồng ý / từ chối + lý do.
- Dòng thời gian (ai làm gì lúc mấy giờ) — PM rất nhấn mạnh cho ngành thực phẩm.

### Phần dưới — Tiền

- Tổng chi phí nguyên liệu (từ BOM).
- Giá bán / margin ước tính.
- VP cần thấy margin có đạt ngưỡng công ty không.

### Nút thao tác (ý PM)

| Nút                       | Ai bấm                         |
| ------------------------- | ------------------------------ |
| Gửi duyệt                 | Sales                          |
| Duyệt / Từ chối           | President                      |
| Duyệt giá / Trả lại Sales | VP Pricing                     |
| Chuyển Sales Order        | Sales/Admin (sau khi đủ duyệt) |

---

## 4. So sánh: Báo giá “thương mại” vs “sản xuất OEM”

|                   | Bán hàng thường   | OEM (PM)                 |
| ----------------- | ----------------- | ------------------------ |
| Dòng trên báo giá | Laptop × 5 @ 1000 | 10,000 gói cà phê @ 2.50 |
| Công thức         | Không cần         | **Bắt buộc** (BOM lines) |
| Duyệt             | Ít hoặc không     | President + VP           |
| Sau khi chốt      | Giao hàng         | SO → Lệnh SX → Batch     |

**Task nào xung đột nếu ép cho mọi công ty?** Bảng đầy đủ (Production + Phase 1): **`PHASE1_PM_STATUS_LIVE_VI.md` → mục F**. Tóm lại: BOM, duyệt 2 cấp, chặn SO, copy BOM Production **chỉ** cho tenant bật Phase 1; Miaolin-style **tắt** module → giữ Quotation thường.

---

## 5. ERP hiện tại: đã có gì, chưa có gì?

### Đã có (PM đôi khi nghĩ là chưa có)

| Việc                                | Ghi chú ngắn                                                               |
| ----------------------------------- | -------------------------------------------------------------------------- |
| Module báo giá (Estimate/Quotation) | Tạo, sửa, gửi, PDF                                                         |
| Yêu cầu khách (Estimate Request)    | Intake cơ bản: mô tả, budget → tạo báo giá                                 |
| Duyệt President + VP Pricing        | Trên trang chi tiết báo giá; có ghi chú; chặn convert SO nếu chưa duyệt đủ |
| Convert Sales Order                 | Copy dòng bán sang đơn hàng                                                |
| Production BOM (module riêng)       | Định mức master — **chưa** gắn lên báo giá                                 |

### Chưa có / còn yếu (đúng ý PM)

| Việc                           | Ý nghĩa với user                                               |
| ------------------------------ | -------------------------------------------------------------- |
| **BOM lines trên báo giá**     | ✅ Nhập NL/qty/cost; copy Production BOM                       |
| **Recipe header**              | 🟡 MOQ, bao bì, SKU OEM, giá mục tiêu                          |
| **Cost + margin từ BOM**       | 🟡 Panel % lãi; VP chặn nếu dưới ngưỡng (`.env`, mặc định 15%) |
| **Một trạng thái workflow rõ** | 🟡 Badge / stage; chưa đủ thống nhất một enum                  |
| **Timeline duyệt**             | 🟡 `estimate_approval_events` + timeline; chưa full audit      |
| **Nút “Gửi duyệt”**            | ✅                                                             |
| **Từ chối → sửa lại**          | 🟡 `revision_required` + gửi duyệt lại                         |
| **Quyền riêng President / VP** | 🟡 Permission có; gán role trên UI còn polish                  |
| **Layout workspace OEM**       | ⬜ ~15% — vẫn form cũ + block                                  |
| Tìm công thức cũ tương tự      | ⬜ Search thủ công; **không** AI                               |

---

## 6. Không làm gì (PM + kỹ thuật đồng thuận)

- Module Estimate tách riêng.
- AI tự duyệt / AI bắt buộc trong luồng.
- Đưa BOM **chỉ** ở Production mà không có trên báo giá (giá và duyệt sẽ vô nghĩa).
- Production nhảy thẳng từ Estimate — vẫn đi **Sales Order** đã duyệt.

---

## 7. Nên làm theo thứ tự nào? (gợi ý cho dev)

| Bước  | Làm gì                                                      | Tại sao trước                                |
| ----- | ----------------------------------------------------------- | -------------------------------------------- |
| **1** | BOM lines + lưu DB + UI create/edit/show                    | Nền cho công thức, costing, duyệt có ý nghĩa |
| **2** | Recipe header (MOQ, packaging…)                             | PM section 2                                 |
| **3** | Hoàn thiện workflow (stage, submit review, timeline, quyền) | PM section 3–5                               |
| **4** | Cost / margin panel + rule VP                               | PM financial summary                         |
| **5** | Copy từ Production BOM / tìm BOM tương tự                   | Tiện Sales, không nhập tay                   |
| **6** | Đổi tên menu “Sales Estimate / OEM Quotation”               | UX                                           |

Màn hình trọng tâm: **`/account/estimates/{id}`** (chi tiết báo giá).

---

## 8. Một đoạn PM có thể đọc cho dev

> Báo giá của chúng ta không còn là bảng giá bán hàng đơn giản. Sales phải nhập **công thức (từng nguyên liệu + định mức + chi phí)** trên cùng báo giá với **số lượng bán và giá chào**. President duyệt **công thức**, VP duyệt **giá và lãi**. Chỉ khi cả hai đồng ý mới được chuyển Sales Order. BOM trên báo giá là đầu vào cho costing và sau này cho sản xuất — không đợi đến lúc vào module Production mới khai báo công thức.

---

## 9. Liên kết tài liệu khác

| File                                                  | Dùng khi                         |
| ----------------------------------------------------- | -------------------------------- |
| `PROJECT BIOMIXING/PM_YEU_CAU_TONG_HOP_VI.md`         | **Spec PM gộp** (Gary + Phase 1) |
| `PHASE1_PM_STATUS_LIVE_VI.md`                         | Bảng gap / trạng thái Phase 1    |
| `PHASE1_PM_STATUS_LIVE_VI.md`                         | % tiến độ cho PM                 |
| `PROJECT BIOMIXING/PHASE_BUSINESS_CONTEXT_EXAMPLE.md` | President vs VP nghĩa nghiệp vụ  |

---

_Cuối: ~**65%** Phase 1 — BOM + duyệt + chặn SO đã chạy; còn layout 4 vùng, search công thức, Settings % lãi, polish workflow. Chi tiết: `PHASE1_PM_STATUS_LIVE_VI.md`._
