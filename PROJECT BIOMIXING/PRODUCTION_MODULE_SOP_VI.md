# Quy trình module Sản xuất (SOP phi kỹ thuật)

**Đối tượng:** Tổ trưởng xưởng, planner sản xuất, kho, hỗ trợ bán hàng  
**Hệ thống:** Craveva ERP — module Production  
**Phiên bản:** 2026-05-28  
**Mục đích:** Hướng dẫn lập kế hoạch theo **định mức (BOM) trước** → giữ chỗ NVL khi Release → chạy lô 4 bước → cập nhật tồn → bán / giao hàng.

---

## Tổng quan trạng thái lệnh SX

| Trạng thái                      | Ý nghĩa                                                                                        |
| ------------------------------- | ---------------------------------------------------------------------------------------------- |
| **Nháp (Draft)**                | Chỉ lập kế hoạch; có thể sửa lệnh. **Chưa** giữ chỗ nguyên liệu.                               |
| **Đã phát hành (Released)**     | Đã cam kết SX; **chốt định mức** trên lệnh; **reserve** NVL; thường **tự tạo lô SX đầu tiên**. |
| **Đang sản xuất (In progress)** | Đã **trừ** NVL thật (ít nhất một lô đã post).                                                  |
| **Hoàn thành (Completed)**      | Đã **nhập** thành phẩm vào kho TP (mọi lô đã post).                                            |
| **Đã hủy (Cancelled)**          | Dừng lệnh (xem mục 11).                                                                        |

---

## 1. Tạo sản phẩm thành phẩm (master)

**Vào:** `Operations → Products`

**Các bước:**

- Thêm **thành phẩm** (sản phẩm sản xuất ra).
- Chọn **đơn vị**, **SKU** nếu cần.
- Chọn đúng **loại sản phẩm** (finished goods) để dùng làm đầu ra trên định mức.
- Lưu.

---

## 2. Tạo nguyên liệu / bao bì (master)

**Vào:** `Operations → Products`

**Các bước:**

- Thêm từng NVL, bao bì.
- Bật **theo dõi tồn kho / mua hàng** nếu áp dụng.
- Thống nhất đơn vị (g, kg, cái).
- Lưu.

---

## 3. Nhập tồn kho

**Vào:** `Operations → Inventory` và/hoặc `Warehouse`

**Cách nhập tồn:**

- **Thủ công / tồn đầu kỳ:** `Add Inventory` — chọn kho, sản phẩm, số lượng.
- **Mua hàng:** PO → GRN / nhận hàng.

**Lưu ý:** “Opening stock” trên form SP **chưa chắc** vào kho vật lý — cần **Add Inventory** đúng **kho**.

---

## 4. Tạo định mức (BOM)

**Vào:** `Production → Bill of Materials`

**Các bước:**

- Chọn **thành phẩm** (đầu ra).
- Thêm từng dòng **nguyên liệu** và định mức / 1 đơn vị TP.
- Lưu BOM (**ít nhất một dòng**).

**Ví dụ — 1 hộp cà phê:**

| Nguyên liệu | Định mức |
| ----------- | -------- |
| Bột cà phê  | 10 g     |
| Đường       | 5 g      |
| Hộp bao bì  | 1 cái    |

**Quy tắc:** Lệnh SX **bắt buộc** có BOM có dòng trước khi **Release** (cấu hình Biomixing hiện tại).

---

## 5. Tạo lệnh sản xuất (chọn BOM trước)

**Vào:** `Production → Production Orders` → **New production order**

**Các bước:**

1. Chọn **BOM** (dropdown có placeholder — hệ thống **không** tự chọn BOM đầu tiên).
2. **Thành phẩm** tự điền theo BOM đã chọn (không chọn TP trước trong luồng mặc định).
3. Nhập **số lượng kế hoạch**.
4. Chọn **kho nguyên liệu** và **kho thành phẩm**.
5. (Tuỳ chọn) Gắn **đơn bán hàng (SO)**.
6. Xem **bảng xem trước NVL** trên form (đổi khi đổi BOM / SL / kho NL). Preview = BOM **master**; xưởng dùng bản **chốt trên lệnh** sau Release.
7. Lưu **Nháp**.

**Không Release được** nếu chưa chọn BOM hoặc BOM không có dòng.

**Tuỳ chọn:** Từ **Sales Order** → **Tạo lệnh sản xuất**.

---

## 6. Kiểm tra đủ nguyên liệu

### A) Trên chi tiết lệnh SX

- Bảng **tổng nguyên liệu** (từ BOM / snapshot sau Release).
- So **cần** vs **khả dụng** tại kho NL.

### B) Nhiều lệnh (mua hàng / planner)

**Vào:** `Production → Production Orders` → **Material shortage summary**

| Bộ lọc                                      | Khi nào dùng                   |
| ------------------------------------------- | ------------------------------ |
| **Đã phát hành + Đang sản xuất** (mặc định) | Lệnh đã cam kết — đã reserve   |
| **Nháp**                                    | Lập kế hoạch mua trước Release |
| **Tất cả (Nháp + Đã PH + Đang SX)**         | Toàn bộ nhu cầu đang mở        |

**Nếu thiếu:** Nhập thêm hàng · giảm SL kế hoạch · chưa Release.

---

## 7. Phát hành lệnh (Release)

**Vào:** Chi tiết lệnh → **Release**

**Hệ thống:**

- **Snapshot BOM** trên lệnh (chốt cho lô này).
- Kiểm tra tồn khả dụng; không đủ → **chặn**.
- Đủ → **reserve** NVL (chưa trừ tồn).
- Tạo **lô SX đầu tiên** nếu chưa có.
- **Tự sinh** dòng NVL kế hoạch trên lô từ snapshot (không cần bấm nút “sinh planned” mặc định).

**Lưu ý:** Lệnh **Nháp** **không** reserve NVL Production.

---

## 8. Chạy lô sản xuất — 4 bước trên màn hình

**Vào:** Lệnh SX → **Batches** → mở lô (thường đã có sau Release)

Checklist hiển thị **4 bước** (đánh số 1–4). **Không** còn bước riêng “sinh dòng NVL kế hoạch”.

| Bước | Thao tác                             | Tồn kho                              |
| ---- | ------------------------------------ | ------------------------------------ |
| 1    | **Gán lô kho** từng dòng NVL         | Chưa trừ; reserve đã tạo lúc Release |
| 2    | **Deduct raw materials** (Trừ NL)    | **Giảm** tồn NL                      |
| 3    | **Thêm dòng thành phẩm** (SL, mã lô) | Chưa nhập kho đến bước 4             |
| 4    | **Post finished goods** (Nhập TP)    | **Tăng** tồn TP                      |

**Sau post:**

- **Đang sản xuất** — khi đã trừ NL (theo quy tắc lô).
- **Hoàn thành** — khi đã nhập TP hết các lô.

**Quan trọng:**

- Bấm **Hoàn thành** trên lệnh **không** tự trừ/nhập kho.
- Mặc định **không** thêm tay dòng NVL trên lô — chỉ từ snapshot.
- Mở lô cũ chưa có dòng → hệ thống vẫn có thể **tự sinh** nếu lệnh đã snapshot.

**Tuỳ chọn:** Duyệt hao hụt trước nhập TP · **In nhãn lô** trên màn lô.

---

## 9. Truy xuất (tuỳ chọn)

**Vào:** Lô → **Trace** — liên kết lô SX ↔ lô kho (NL → SX → TP).

---

## 10. Thành phẩm → bán hàng & giao hàng

Sau **Nhập TP**, hàng ở **kho TP** — dùng cho SO, phiếu giao (DO), hóa đơn.

**Tuỳ chọn (thường bật):** Chặn giao hàng nếu lệnh SX liên quan chưa **Hoàn thành** (quality lock).

---

## 11. Hủy lệnh

| Trạng thái hiện tại                           | Hủy được?            |
| --------------------------------------------- | -------------------- |
| **Nháp**                                      | Có                   |
| **Đã phát hành** (chưa trừ NL / chưa nhập TP) | Có — **trả** reserve |
| **Đang sản xuất** / **Hoàn thành**            | **Không**            |

---

## 12. Phân vai đề xuất

| Việc                         | Vai trò            |
| ---------------------------- | ------------------ |
| Tạo BOM / lệnh Nháp          | Planner            |
| Release                      | Quản lý xưởng      |
| Lô — gán lô, trừ NL, nhập TP | Sản xuất / kho     |
| Duyệt hao hụt                | Quản lý            |
| Tổng hợp thiếu NVL / PO      | Planner / mua hàng |

---

## 13. Lỗi thường gặp

1. Release khi thiếu tồn → chặn.
2. Release khi chưa chọn BOM / BOM trống → chặn.
3. Chọn TP trước thay vì BOM → luồng mặc định chọn **BOM trước**.
4. Bỏ bước 1–4 trên lô → tồn không đổi.
5. Nhầm **preview trên form** (BOM master) với **dòng trên lô** (snapshot lúc Release).
6. Tưởng **Nháp** đã giữ chỗ tồn — chỉ **Released** mới reserve.

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

## Tài liệu kỹ thuật (nội bộ)

- [`FUNC_LOGIC/PRODUCTION_OPERATIONS_LIVE_VI.md`](../FUNC_LOGIC/PRODUCTION_OPERATIONS_LIVE_VI.md)
- [`FUNC_LOGIC/PRODUCTION_MODULE_AUDIT_VI.md`](../FUNC_LOGIC/PRODUCTION_MODULE_AUDIT_VI.md)
- [`FUNC_LOGIC/PRODUCTION_BATCH_STEP1_RESTORE_VI.md`](../FUNC_LOGIC/PRODUCTION_BATCH_STEP1_RESTORE_VI.md)
- [`PRODUCTION_RELEASE_RESERVE_TEST_FLOW_VI.mmd`](./PRODUCTION_RELEASE_RESERVE_TEST_FLOW_VI.mmd)
