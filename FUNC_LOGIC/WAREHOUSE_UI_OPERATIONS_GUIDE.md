# Hướng dẫn thao tác trên giao diện — Multi-Warehouse (sau khi triển khai)

Tài liệu dành cho người **không chuyên kỹ thuật**: mô tả **vào đâu, làm gì** trên ERP để vận hành kho. Tên menu có thể khác nhẹ theo ngôn ngữ / cấu hình, nhưng **đường dẫn (URL)** và **luồng nghiệp vụ** giữ nguyên.

---

## Trước khi dùng (một lần — IT / admin)

| Việc                | Ghi chú                                                                                                                                                                                                                                                                                                                                                                                                                                                                               |
| ------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Chạy migration      | `php artisan migrate` trên server (có bảng batch, backfill từ tồn cũ).                                                                                                                                                                                                                                                                                                                                                                                                                |
| Module bật cho user | Công ty cần có `warehouse` trong **module_settings** (gói có Purchase/Products + migration warehouse). Trên UI, mục kho nằm **trong Operations**, ngay **dưới Inventory** (All warehouses, Add warehouse, Stock…). Ai có quyền **Inventory** (Purchase) cũng thấy **danh sách kho / điều chỉnh tồn** dù chưa gán riêng `view_warehouses` (employee thường thiếu quyền warehouse trong role). Muốn đủ chức năng: gán thêm quyền warehouse trong **Settings → Role** (add/chuyển kho…). |
| Đăng nhập           | Dùng tài khoản nhân viên có quyền tương ứng.                                                                                                                                                                                                                                                                                                                                                                                                                                          |

**Tùy chọn:** `.env` có thể đặt `WAREHOUSE_ALLOW_NEGATIVE_STOCK=false` (mặc định không cho tồn âm). Chỉ bật `true` khi nghiệp vụ cho phép (cần xác nhận sếp/kế toán).

**Phase 3 — chọn nguồn nhập kho (tránh nhập đôi cùng một lần nhận hàng):**

| Biến `.env`                           | Mặc định | Ý nghĩa                                                                                                                                                                      |
| ------------------------------------- | -------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `WAREHOUSE_INBOUND_FROM_PO_DELIVERED` | `true`   | Nhập kho khi PO chuyển **delivered** (mặc định như trước).                                                                                                                   |
| `WAREHOUSE_INBOUND_FROM_DO_RECEIVED`  | `false`  | Nhập kho khi **Delivery Order** (inbound) chuyển **received**. Bật khi dùng DO làm phiếu nhận chính; khi đó nên tắt nhập từ PO hoặc không dùng cả hai cho cùng một lần nhận. |

---

## 1) Đường dẫn (URL) thường dùng

Ứng dụng thường có tiền tố domain, ví dụ: `https://your-domain.com/...`

| Chức năng                               | Đường dẫn (sau domain)             | Ghi chú                               |
| --------------------------------------- | ---------------------------------- | ------------------------------------- |
| Danh sách PO (mua hàng)                 | `/account/purchase-order`          | Cần đăng nhập (`account` + `auth`).   |
| Tạo / sửa PO                            | `/account/purchase-order/create` … | Theo resource Laravel.                |
| Tồn kho Purchase (điều chỉnh)           | `/account/purchase-inventory`      | Inventory trong module Purchase.      |
| Danh sách kho (master)                  | `/warehouse`                       | Module Warehouse (route gốc).         |
| Tồn theo kho (xem / nhập-xuất thủ công) | `/warehouse-stock`                 | `index` = xem, `create` = điều chỉnh. |
| Chuyển kho                              | `/warehouse-transfer`              | GET = form, POST = lưu.               |

_Nếu vào URL báo 404:_ kiểm tra module Warehouse đã bật trong hệ thống và route đã load.

---

## 2) Luồng dữ liệu bạn cần nhớ (1 phút)

1. **Tạo kho** (danh mục kho) → có chỗ chọn kho khi mua / nhận hàng.
2. **Đặt hàng (PO)** → chọn **kho nhận** (nếu màn hình có).
3. Khi hàng **thực sự về kho** → đổi trạng thái giao nhận **Delivered** (hoặc tương đương) → hệ thống **nhập kho** (tăng tồn).
4. **Chuyển kho / chỉnh tồn thủ công** → dùng màn Warehouse (stock / transfer).
5. **Hóa đơn (Invoice)** → **không** tự trừ kho trực tiếp theo thiết kế hiện tại; tồn theo **nhận hàng / xuất kho / chuyển kho** (phần xuất bán gắn DO sẽ phát triển thêm ở phase sau nếu có).

---

## 3) Thao tác chi tiết theo màn hình

### A. Master kho (`/warehouse`)

1. **Warehouse** → **Danh sách** (`/warehouse`).
2. **Thêm mới** → tên kho, mã (nếu có), trạng thái active.
3. Lưu. Các màn khác sẽ chọn kho trong dropdown.

### B. Purchase Order — nhập kho khi nhận hàng (`/account/purchase-order`)

1. Vào **Purchase Order** → tạo đơn hoặc mở đơn đã có.
2. Điền **dòng hàng**, **số lượng**; nếu có trường **Warehouse / kho nhận** → chọn đúng kho.
3. Khi **hàng đã về và nhập kho**:
    - Cập nhật trạng thái giao hàng **Delivered** (đã giao/nhận — theo label trên UI của bạn).
4. Hệ thống sẽ ghi nhận **nhập kho** (theo kho + sản phẩm; lô/hạn nếu sau này có trên PO).

**Lưu ý:** Nếu không chọn kho trên PO, phần nhập kho qua service có thể không chạy — cần nhập kho thủ công hoặc chỉnh đơn (tùy cấu hình).

### C. Inventory Purchase — điều chỉnh tồn kế toán (`/account/purchase-inventory`)

1. Dùng cho **phiếu điều chỉnh tồn** (giá trị / số lượng), thường kèm **kho** trên form.
2. Chọn **Warehouse** trước khi thêm sản phẩm; nếu đổi kho giữa chừng, hệ thống reset danh sách dòng để tránh nhầm tồn kho theo kho khác.
3. Với mode **quantity**, hệ thống đồng bộ chênh lệch tồn vào movement pipeline (inbound/outbound) để nhất quán với `warehouse_product_batches` + `stock_movements`.
4. Phù hợp **điều chỉnh**, **kiểm kê** theo quy trình Purchase — không thay thế hoàn toàn màn Warehouse nếu bạn chỉ quản lý “tồn vật lý” nhanh.

### D. Tồn kho Warehouse — xem & nhập/xuất thủ công (`/warehouse-stock`)

1. **Danh sách** (`/warehouse-stock`): lọc theo kho, tìm SKU.
2. **Tạo / điều chỉnh** (`/warehouse-stock/create`):
    - Chọn **kho**, **sản phẩm**, **số lượng**.
    - Loại: **inbound** (nhập), **outbound** (xuất), **adjustment** (thường kèm **action** add/remove nếu form có).
3. Lưu → cập nhật tồn qua **StockMovementService** (ledger + batch mặc định khi không có lô).

### E. Chuyển kho (`/warehouse-transfer`)

1. Mở form **transfer**.
2. Chọn **kho nguồn**, **kho đích**, **sản phẩm**, **số lượng**.
3. Lưu → hệ thống **trừ nguồn + cộng đích** (FEFO nếu không chỉ định lô).

### F. Client — kho ưu tiên (đã có cột DB)

- Trường **Default Warehouse** đã có trên form Client (create/edit), lưu vào `client_details.default_warehouse_id`.
- Đây là kho ưu tiên để định tuyến giao hàng ở phase sau; hiện chưa tự động áp vào mọi luồng SO/DO nếu chưa bật rule tương ứng.

### G. Invoice

- **Tạo / gửi hóa đơn** như bình thường — **không** tự trừ kho theo thiết kế hiện tại.

---

## 4) Checklist “đã làm đúng” sau một ngày

- [ ] PO đã delivered có **kho** (nếu quy trình yêu cầu).
- [ ] Tồn trên `/warehouse-stock` khớp cảm quan (hoặc đối soát với report).
- [ ] Chuyển kho có **đủ tồn nguồn** (hệ thống báo lỗi nếu không đủ — trừ khi bật cờ tồn âm).

---

## 5) Phase sau (có thể chưa có trên UI)

- **Delivery Order (DO) xuất bán**, **giữ chỗ (reservation)** — thường gắn **Sales Order**; sẽ bổ sung khi phase module integration hoàn tất.
- Báo cáo theo **lô + hạn** đầy đủ — thường ở phase UI/Report.

---

## 6) Khi lỗi / không thấy menu

1. Đăng nhập đúng user có quyền (ít nhất một trong: xem kho, thêm kho, tồn kho, chuyển kho).
2. Thử **URL trực tiếp** theo bảng mục 1 (ví dụ `/warehouse`, `/warehouse/create`).
3. Nhờ IT: module **warehouse** đã bật cho công ty (`user_modules()` có `warehouse`), role được gán quyền warehouse; `php artisan route:clear` nếu vừa deploy.

_Nếu trước đây không thấy mục Kho trên sidebar: bản giao diện đã gắn `warehouse::sections.sidebar` vào menu chính — cần deploy code mới._

---

_Tài liệu đi kèm: `FUNC_LOGIC/B2B_ERP_PO_DO_INVOICE_GUIDE.md` (nghiệp vụ PO/DO/Invoice), `FUNC_LOGIC/WAREHOUSE_ANALYSIS_AND_PLAN.md` (kỹ thuật + trạng thái triển khai), `FUNC_LOGIC/MAOLIN_INDEX.md` (mục lục ghi chú MAOLIN)._
