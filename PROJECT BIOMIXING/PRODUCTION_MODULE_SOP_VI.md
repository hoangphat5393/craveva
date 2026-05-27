# Module Sản xuất — Quy trình vận hành (SOP phi kỹ thuật)

**Đối tượng:** Tổ trưởng xưởng, planner sản xuất, kho, hỗ trợ bán hàng  
**Hệ thống:** Craveva ERP — module Production  
**Phiên bản:** 2026-05-27  
**Mục đích:** Hướng dẫn từ lập kế hoạch → giữ chỗ NVL → chạy lô → cập nhật tồn → bán / giao hàng.

---

## Tổng quan trạng thái lệnh SX

| Trạng thái                      | Ý nghĩa                                                          |
| ------------------------------- | ---------------------------------------------------------------- |
| **Nháp (Draft)**                | Chỉ lập kế hoạch; có thể sửa lệnh. **Chưa** giữ chỗ nguyên liệu. |
| **Đã phát hành (Released)**     | Đã cam kết sản xuất. Hệ thống **reserve** NVL tại kho NL.        |
| **Đang sản xuất (In progress)** | Đã **trừ** NVL thật (ít nhất một lô đã post).                    |
| **Hoàn thành (Completed)**      | Đã **nhập** thành phẩm vào kho TP (mọi lô đã post FG).           |
| **Đã hủy (Cancelled)**          | Dừng lệnh (xem mục 11).                                          |

---

## 1. Tạo sản phẩm thành phẩm

**Vào:** `Operations → Products` (mua hàng / sản phẩm)

**Các bước:**

- Thêm **thành phẩm** (sản phẩm sản xuất ra).
- Chọn **đơn vị** (Cái, Thùng, Kg, …) và **SKU** nếu cần.
- Chọn đúng **loại sản phẩm** (finished goods) để dùng làm đầu ra trên BOM.
- Lưu.

**Ví dụ:** Cà phê Oldtown · thanh chocolate hoàn chỉnh.

---

## 2. Tạo nguyên liệu / bao bì

**Vào:** `Operations → Products`

**Các bước:**

- Thêm từng NVL, bao bì (bột cà phê, đường, hộp, chocolate, …).
- Bật **theo dõi tồn kho / mua hàng** nếu áp dụng.
- Thống nhất đơn vị với cách mua và tiêu hao (g, kg, cái).
- Lưu.

---

## 3. Nhập tồn kho

**Vào:** `Operations → Inventory` và/hoặc `Warehouse` (kho)

**Cách nhập tồn:**

- **Thủ công / tồn đầu kỳ:** `Operations → Inventory → Add Inventory` — chọn kho, sản phẩm, số lượng.
- **Mua hàng:** Đơn mua (PO) → GRN / nhận hàng → tồn tăng tại kho đã chọn.

**Mục đích:** Hệ thống phải biết **tồn thực** tại **kho nguyên liệu** gắn trên lệnh sản xuất.

**Lưu ý:** Chỉ nhập “opening stock” trên form sản phẩm **chưa chắc** đã vào kho vật lý — cần **Add Inventory** đúng **kho**.

---

## 4. Tạo định mức BOM

**Vào:** `Production → Bill of Materials` (Định mức / BOM)

**Các bước:**

- Chọn **thành phẩm** (đầu ra).
- Thêm từng dòng **nguyên liệu** và định mức / 1 đơn vị TP.
- Nhập **% hao hụt** nếu cần (tính vào tổng NVL).
- Lưu BOM.

**Ví dụ — 1 hộp cà phê:**

| Nguyên liệu | Định mức |
| ----------- | -------- |
| Bột cà phê  | 10 g     |
| Đường       | 5 g      |
| Hộp bao bì  | 1 cái    |

**Quy tắc:** Không có BOM thì lệnh SX không tính và không trừ NVL đúng.

---

## 5. Tạo lệnh sản xuất

**Vào:** `Production → Production Orders` → **New production order** (Lệnh sản xuất mới)

**Các bước:**

- Chọn **thành phẩm** và **BOM**.
- Nhập **số lượng kế hoạch**.
- Chọn **kho nguyên liệu** (kho trừ NL).
- Chọn **kho thành phẩm** (kho nhập TP).
- (Tuỳ chọn) Gắn **đơn bán hàng (SO)**.
- Lưu ở trạng thái **Nháp** trước.

**Mục đích:** Đây là “work order” sản xuất. Nháp = còn sửa kế hoạch.

**Tuỳ chọn:** Từ màn **Sales Order** → nút **Tạo lệnh sản xuất** (điền sẵn SO, SL, BOM).

---

## 6. Kiểm tra đủ nguyên liệu

### A) Trên chi tiết lệnh SX

- Mở lệnh → xem bảng **tổng nguyên liệu**.
- So **tổng cần** vs **tồn khả dụng** tại kho NL.
- Thiếu → có cảnh báo.

### B) Nhiều lệnh cùng lúc (mua hàng / planner)

**Vào:** `Production → Production Orders` → **Material shortage summary** (Tổng hợp thiếu nguyên liệu)

| Bộ lọc trạng thái                           | Khi nào dùng                              |
| ------------------------------------------- | ----------------------------------------- |
| **Đã phát hành + Đang sản xuất** (mặc định) | Lệnh đã cam kết — tồn đã trừ phần reserve |
| **Nháp**                                    | Lập kế hoạch mua trước khi Release        |
| **Tất cả (Nháp + Đã PH + Đang SX)**         | Xem toàn bộ nhu cầu đang mở               |

**Nếu thiếu:**

- Nhập thêm hàng (PO / GRN / Add Inventory), **hoặc**
- Giảm SL kế hoạch, **hoặc**
- Chưa Release cho đến khi đủ tồn.

---

## 7. Phát hành lệnh (Release)

**Vào:** Chi tiết lệnh SX → **Release** (Nháp → **Đã phát hành**)

**Hệ thống làm gì:**

- Chụp **snapshot BOM** theo SL kế hoạch lúc release.
- Kiểm tra **tồn khả dụng** = tồn thực − **đã reserve** (giao hàng, lệnh SX khác).
- Không đủ → **chặn Release**, báo thiếu NVL.
- Đủ → **reserve** NVL tại kho NL (giữ chỗ — **chưa** trừ tồn).

**Ý nghĩa:** Xưởng cam kết chạy lệnh này.

**Khuyến nghị:** Chỉ quản lý / planner có quyền Release.

**Lưu ý:** Lệnh **Nháp** **không** reserve NVL Production.

---

## 8. Chạy lô sản xuất (xưởng) — bắt buộc đúng thứ tự

**Vào:** Lệnh SX → **Batches** (Lô) → tạo / mở lô

| Bước | Thao tác trên màn hình                  | Tồn kho                |
| ---- | --------------------------------------- | ---------------------- |
| 1    | Tạo **lô sản xuất**                     | —                      |
| 2    | **Sinh planned RM** từ snapshot BOM     | Chỉ kế hoạch           |
| 3    | **Gán lô kho** cho từng dòng NL         | Không reserve thêm     |
| 4    | **Deduct raw materials** (Trừ NL)       | **Giảm** tồn NL        |
| 5    | Nhập **dòng thành phẩm** (SL, mã lô TP) | —                      |
| 6    | **Phê duyệt hao hụt** (nếu công ty bật) | QL duyệt trước post TP |
| 7    | **Post finished goods** (Nhập TP)       | **Tăng** tồn TP        |

**Sau khi post:**

- **Đang sản xuất** — khi đã trừ NL (theo lô).
- **Hoàn thành** — khi đã nhập TP hết các lô.

**Quan trọng:** Bấm **Hoàn thành** một mình **không** trừ/nhập kho. Tồn đổi tại bước **Trừ NL** và **Nhập TP**.

---

## 9. Truy xuất (tuỳ chọn)

**Vào:** Màn lô → **Trace**

- Liên kết lô SX ↔ lô kho (NL → sản xuất → TP).
- Dùng khi audit, thu hồi, hoặc khách hỏi nguồn gốc.

---

## 10. Thành phẩm → bán hàng & giao hàng

Sau **Nhập TP**, hàng nằm ở **kho TP**, dùng cho:

- Đơn bán hàng (SO)
- Phiếu giao hàng (DO) — confirm / ship (có thể reserve hoặc trừ TP theo cấu hình)
- Hóa đơn (thường không đổi tồn lần nữa)

**Tuỳ chọn:** Có thể chặn giao hàng nếu lệnh SX chưa **Hoàn thành** (quality lock — nếu bật).

---

## 11. Hủy lệnh

| Trạng thái hiện tại                           | Hủy được?                         |
| --------------------------------------------- | --------------------------------- |
| **Nháp**                                      | Có                                |
| **Đã phát hành** (chưa trừ NL / chưa nhập TP) | Có — hệ thống **trả** reserve NVL |
| **Đang sản xuất** hoặc **Hoàn thành**         | **Không** — đã ghi sổ kho         |

---

## 12. Phân vai đề xuất

| Việc                    | Vai trò thường gặp |
| ----------------------- | ------------------ |
| Tạo BOM / lệnh Nháp     | Planner            |
| Release                 | Quản lý xưởng      |
| Lô — trừ NL / nhập TP   | Sản xuất / kho     |
| Duyệt hao hụt           | Quản lý            |
| Tổng hợp thiếu NVL / PO | Planner / mua hàng |

---

## 13. Lỗi thường gặp

1. Release khi chưa đủ tồn → hệ thống chặn.
2. Bỏ bước lô → tồn không đổi.
3. Chọn sai kho NL / kho TP trên lệnh.
4. ĐVT BOM khác đVT gốc SP — cần khai báo quy đổi đơn vị.
5. Tưởng **Nháp** đã giữ chỗ tồn — chỉ **Released** mới reserve.

---

## 14. Tra cứu menu nhanh

| Việc                | Đường menu                                                 |
| ------------------- | ---------------------------------------------------------- |
| Sản phẩm            | Operations → Products                                      |
| Nhập tồn            | Operations → Inventory · Warehouse                         |
| BOM                 | Production → Bill of Materials                             |
| Lệnh SX             | Production → Production Orders                             |
| Tổng hợp thiếu NVL  | Production → Production Orders → Material shortage summary |
| Cấu hình TP (admin) | Settings → Production                                      |

---

## Tài liệu kỹ thuật liên quan (nội bộ)

- [`FUNC_LOGIC/PRODUCTION_OPERATIONS_LIVE_VI.md`](../FUNC_LOGIC/PRODUCTION_OPERATIONS_LIVE_VI.md)
- [`PRODUCTION_RELEASE_RESERVE_TEST_FLOW_VI.mmd`](./PRODUCTION_RELEASE_RESERVE_TEST_FLOW_VI.mmd)
