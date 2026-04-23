# Checklist nghiệm thu (UAT) — Mua · Bán · Kho (End-to-End)

**Mục đích:** Một chỗ cho QA/PM nghiệm thu luồng ERP (procurement, sales, đa kho, trả hàng) và module Warehouse.  
**Đồng bộ code/tài liệu:** [`QUY_TRINH_PO_DO_SO_INVOICE_WAREHOUSE_VI.md`](QUY_TRINH_PO_DO_SO_INVOICE_WAREHOUSE_VI.md) · [`SALES_PURCHASE_FLOW.md`](SALES_PURCHASE_FLOW.md) · [`HUONG_DAN_KHO_BAN_CO_BAN_VA_PHAN_MO_RONG_VI.md`](HUONG_DAN_KHO_BAN_CO_BAN_VA_PHAN_MO_RONG_VI.md) · trả hàng bán [`SALES_RETURN_CREDIT_NOTE_STOCK_VI.md`](SALES_RETURN_CREDIT_NOTE_STOCK_VI.md) · trả hàng mua [`PURCHASE_RETURN_VENDOR_CREDIT_STOCK_VI.md`](PURCHASE_RETURN_VENDOR_CREDIT_STOCK_VI.md).

**Cập nhật:** 2026-04-23 — bản UAT canonical, da loai bo hoan toan file checklist legacy.

---

## 0) Điều kiện tiên quyết & cấu hình

| Bước | Kết quả mong đợi                                                                                                                                                | Tác động tồn / ghi chú             |
| ---- | --------------------------------------------------------------------------------------------------------------------------------------------------------------- | ---------------------------------- |
| 0.1  | Module Warehouse bật, migration áp dụng; user UAT có đủ quyền (`warehouse_*`, stock, transfer, movement).                                                       | Truy cập đúng phạm vi.             |
| 0.2  | Xác nhận **một** nguồn nhập chuẩn: `WAREHOUSE_INBOUND_FROM_PO_DELIVERED` **hoặc** `WAREHOUSE_INBOUND_FROM_DO_RECEIVED` (không bật đồng thời nếu gây nhập đôi).  | Tránh tồn ảo.                      |
| 0.3  | Xác nhận sales outbound: `WAREHOUSE_SALES_OUTBOUND_ENABLED` + `WAREHOUSE_SALES_OUTBOUND_MODE` = `shipment` hoặc `invoice` (không trừ kép giữa ship và invoice). | Một nguồn xuất cho cùng giao dịch. |
| 0.4  | `WAREHOUSE_ALLOW_NEGATIVE_STOCK` theo chính sách (khuyến nghị `false` cho production).                                                                          | Chặn bán/điều chỉnh vượt tồn.      |
| 0.5  | Chuẩn bị ≥2 kho, 1 default; SP `goods` có tồn lô; (tuỳ chọn) SP có batch/expiry.                                                                                | Dữ liệu test ổn định.              |

---

## 1) Luồng mua hàng (Procurement)

### 1.1 PO → nhận hàng (GRN / DO nhập) → Bill — “3-way matching”

| #     | Bước thực hiện                                                                      | Kết quả mong đợi                                     | Tác động tồn kho                                                          | Giá vốn / costing                             |
| ----- | ----------------------------------------------------------------------------------- | ---------------------------------------------------- | ------------------------------------------------------------------------- | --------------------------------------------- |
| 1.1.1 | Tạo PO có `warehouse_id` và dòng hàng.                                              | PO lưu đúng công ty, NCC, kho.                       | Chưa nhập kho (trừ logic đặc biệt).                                       | Giá PO = tham chiếu.                          |
| 1.1.2 | Ghi nhận nhận hàng theo cấu hình: **PO delivered** _hoặc_ **GRN/DO nhập received**. | Một sự kiện nhận → một lần ghi nhận kho (không đôi). | **Inbound** `StockMovementService`; batch + tổng tồn kho tăng.            | Xác minh mapping giá nhập ↔ bill/GL (nếu có). |
| 1.1.3 | Tạo Purchase Bill gắn PO (nếu có).                                                  | AP đúng; khớp SL/giá trong giới hạn hệ thống.        | Bill **không** là nguồn chính `stock_movements` (theo thiết kế hiện tại). | AP vs giá vốn — quy trình DN.                 |
| 1.1.4 | Đối chiếu PO vs nhận vs bill.                                                       | Lệch có thể truy vết / xử lý theo quy trình.         | Điều chỉnh tồn chỉ qua nhập/trả/kiểm kê.                                  | Điều chỉnh giá sau nhập: chứng từ riêng.      |

### 1.2 Trả hàng cho NCC (Vendor Credit + xuất kho)

**Thuật ngữ:** Nghiệp vụ thường nói _Debit Note_ (NCC); trên Craveva kiểm chứng theo **Vendor Credit** (`PurchaseVendorCredit`).

| #     | Bước thực hiện                                                               | Kết quả mong đợi                                    | Tác động tồn kho                                                                                          | Giá vốn / costing                        |
| ----- | ---------------------------------------------------------------------------- | --------------------------------------------------- | --------------------------------------------------------------------------------------------------------- | ---------------------------------------- |
| 1.2.1 | Tạo Vendor Credit gắn bill/PO (nếu có), dòng `item` + `product_id` + SL > 0. | AP giảm theo nghiệp vụ.                             | **Outbound** ref `PurchaseVendorCredit`; tồn giảm đúng kho (dòng `warehouse_id` → PO qua bill → default). | Xuất theo FEFO/batch; đối chiếu lớp tồn. |
| 1.2.2 | Kiểm tra đúng kho xuất.                                                      | SL trừ đúng `warehouse_id`.                         | Batch đúng kho.                                                                                           |                                          |
| 1.2.3 | Sửa SL / `product_id` trên dòng.                                             | AP & tồn đồng bộ (hoàn tác + xuất lại, idempotent). | Tồn khớp SL mới.                                                                                          |                                          |
| 1.2.4 | Xóa dòng hoặc xóa header VC.                                                 | Hoàn tác AP.                                        | **Inbound hoàn tác** (reversal).                                                                          |                                          |
| 1.2.5 | Retry / gọi lặp post.                                                        | Không xuất đôi.                                     | Idempotent.                                                                                               |                                          |

---

## 2) Luồng bán hàng (Sales)

### 2.1 SO → Confirm DO (giữ chỗ) → Ship DO → Invoice (mode shipment mặc định)

| #     | Bước thực hiện                                   | Kết quả mong đợi                       | Tác động tồn kho                                             | Giá vốn / costing             |
| ----- | ------------------------------------------------ | -------------------------------------- | ------------------------------------------------------------ | ----------------------------- |
| 2.1.1 | Tạo SO + dòng hàng.                              | Lưu đúng khách, SP, SL.                | Chưa trừ tồn.                                                |                               |
| 2.1.2 | Tạo Sales DO có `warehouse_id`, dòng có SL ship. | DO ở trạng thái cho phép Confirm.      |                                                              |                               |
| 2.1.3 | **Confirm DO**.                                  | Đặt chỗ active.                        | `reserved_quantity` ↑; available ↓; on_hand không đổi.       | COGS khi nào ghi — theo GL.   |
| 2.1.4 | **Ship DO**.                                     | Trạng thái shipped.                    | **Outbound** ref Sales DO; tồn giảm; release reservation.    | COGS thường gắn xuất/invoice. |
| 2.1.5 | Lập Invoice.                                     | AR đúng.                               | **Không** trừ tồn thêm khi `sales_outbound_mode = shipment`. |                               |
| 2.1.6 | (Tuỳ cấu hình) Mode `invoice`.                   | Outbound từ Invoice; không chồng ship. | Một nguồn xuất.                                              |                               |

### 2.2 Trả hàng bán (Credit Note + nhập kho)

| #     | Bước thực hiện                                                                     | Kết quả mong đợi | Tác động tồn kho                  | Giá vốn / costing                 |
| ----- | ---------------------------------------------------------------------------------- | ---------------- | --------------------------------- | --------------------------------- |
| 2.2.1 | Tạo Credit Note, dòng `item` + `product_id`; có thể chọn kho trên dòng / logic DO. | AR điều chỉnh.   | **Inbound** ref `CreditNotes`.    | Nhập lại có thể khác lô xuất gốc. |
| 2.2.2 | Xóa Credit Note.                                                                   | Hoàn tác AR.     | **Outbound hoàn tác** (reversal). |                                   |
| 2.2.3 | Retry post.                                                                        | Không nhập đôi.  | Idempotent.                       |                                   |

---

## 3) Đa kho, chuyển kho & “virtual stock”

### 3.1 Chuyển kho A → B

| #     | Bước thực hiện                                                            | Kết quả mong đợi                  | Tác động tồn kho                          | Giá vốn / costing                                                                                    |
| ----- | ------------------------------------------------------------------------- | --------------------------------- | ----------------------------------------- | ---------------------------------------------------------------------------------------------------- |
| 3.1.1 | Chuyển kho hợp lệ (UI/module gọi `StockMovementService::recordTransfer`). | Một transaction: xuất A + nhập B. | A ↓, B ↑ cùng SL; tổng công ty không đổi. | **Không** có ledger “in-transit” riêng trong code hiện tại (xuất+nhanh nhập trong cùng transaction). |
| 3.1.2 | Chuyển From = To.                                                         | Lỗi validation; không movement.   | Không đổi.                                |                                                                                                      |
| 3.1.3 | Chuyển vượt tồn (nếu không cho âm).                                       | Từ chối; không cập nhật lẻ.       |                                           |                                                                                                      |

### 3.2 Tồn “ảo” / đa kho khi SO gắn kho A hết hàng

| #     | Bước thực hiện                                                               | Kết quả mong đợi                               | Ghi chú hệ thống                                                     |
| ----- | ---------------------------------------------------------------------------- | ---------------------------------------------- | -------------------------------------------------------------------- |
| 3.2.1 | `WarehouseAvailabilityService` / màn availability: SP có tồn ở B, không ở A. | Báo cáo theo kho + `total_sellable`.           | Hỗ trợ quyết định.                                                   |
| 3.2.2 | SO/DO vẫn gắn kho A.                                                         | Confirm/Ship có thể lỗi hoặc không đủ tồn.     | **Không** tự lấy từ B trừ khi đổi kho trên DO hoặc chuyển kho trước. |
| 3.2.3 | Tạo PO nhập về A khi thiếu.                                                  | Theo quy trình thủ công (hoặc tích hợp riêng). | Ghi nhận gap nếu không có auto PO từ shortage.                       |

---

## 4) Ma trật costing (xác nhận với kế toán)

| Tình huống                     | Số lượng                  | Giá vốn (cần xác nhận ngoài module Warehouse) |
| ------------------------------ | ------------------------- | --------------------------------------------- |
| Chuyển kho nội bộ              | Tổng SL công ty không đổi | Thường không phát sinh COGS                   |
| Xuất bán (Ship / Invoice mode) | Giảm tồn                  | COGS theo FIFO/lô                             |
| Nhập trả bán (CN)              | Tăng tồn                  | Có thể tạo lô mới                             |
| Xuất trả NCC (Vendor Credit)   | Giảm tồn                  | Khớp AP & lớp tồn                             |

Module Warehouse ghi nhận **số lượng + movement**; lớp định giá đầy đủ có thể nằm ở module kế toán/GL — UAT cần bảng mapping do DN cung cấp.

---

## 5) Tiêu chí Pass / Fail tổng hợp (P0–P2)

- **P0:** Không double-count nhập; không double-count xuất (ship vs invoice).
- **P0:** Mọi biến động có trace trên `stock_movements` (reference + idempotency nơi áp dụng).
- **P1:** Vendor Credit & Credit Note hoàn tác đúng khi xóa/sửa.
- **P1:** Chuyển kho cân A/B.
- **P2:** Costing/GL mapping có tài liệu riêng, không suy diễn chỉ từ SL.

---

## 6) Kế hoạch chạy UAT — giai đoạn tiếp theo (đề xuất thứ tự)

Chạy lần lượt để dễ cô lập lỗi; ghi **Pass/Fail + ảnh chụp / số movement** vào sheet nội bộ.

| Giai đoạn                | Phạm vi      | Nội dung checklist (tham chiếu)                                                                                                                |
| ------------------------ | ------------ | ---------------------------------------------------------------------------------------------------------------------------------------------- |
| **1 — P0 / cấu hình**    | Môi trường   | **§0** điều kiện tiên quyết; xác nhận một nguồn nhập + một mode xuất bán; smoke mở Operations không lỗi.                                       |
| **2 — Kho lõi**          | Warehouse UI | **Phụ lục A** mục **B → E** (master kho, điều chỉnh tồn, chuyển kho, ledger); import kho thử **từ trang Warehouses** (không qua menu sidebar). |
| **3 — Mua E2E**          | Procurement  | **§1.1** PO → nhận → bill; **§1.2** Vendor Credit (xuất kho + hoàn tác).                                                                       |
| **4 — Bán E2E**          | Sales        | **§2.1** SO → DO confirm → ship → invoice; **§2.2** Credit Note (nhập kho + hoàn tác).                                                         |
| **5 — Đa kho & costing** | PM/Kế toán   | **§3** chuyển kho / availability; **§4** chốt mapping giá vốn với GL; **§5** P2 sign-off.                                                      |

Sau mỗi giai đoạn: đối chiếu `stock_movements` + tồn lô với kỳ vọng; nếu Fail — ghi `reference_type`, `idempotency_key`, cấu hình `.env` / UI công ty.

---

## Phụ lục A — Module Warehouse (CRUD, điều chỉnh, chuyển kho, ledger)

_Nội dung gộp từ checklist Miaolin (màn hình kho)._

### A. Preconditions / Setup

- [ ] Đăng nhập user có quyền chỉnh sửa
- [ ] Warehouse module enabled; migration applied
- [ ] Permissions: `warehouse_view`, `warehouse_add`, `warehouse_edit`, `warehouse_delete`, `warehouse_stock_view`, `warehouse_stock_add`, `warehouse_transfer_add`, `warehouse_movement_view`
- [ ] Cấu hình inbound (chỉ một nguồn) và negative stock như mục 0
- [ ] Dữ liệu: ≥2 kho, 1 default; ≥3 SP; (tuỳ chọn) batch/expiry

### B. Warehouse Master (CRUD + Import + Bulk)

- [ ] Tạo kho default: chỉ một default/company; lưu OK
- [ ] Tạo kho thứ hai non-default: hiển thị list
- [ ] Sửa tên/trạng thái: cập nhật đúng
- [ ] Xóa kho có tồn/movement/reservation: **bị chặn**, message rõ
- [ ] Xóa kho trống: cho phép
- [ ] Bulk đổi status / xóa: đúng rule bảo vệ dữ liệu
- [ ] Import Excel: tạo/cập nhật theo Company+Code; duplicate xử lý đúng

### C. Stock Adjustment (nhập/xuất tay)

- [ ] Nhập kho A + SP P + SL 10: tồn +10, movement inbound, batch cập nhật/tạo
- [ ] Xuất 3 (đủ tồn): tồn 7, outbound, FEFO nếu có expiry
- [ ] Xuất vượt tồn (negative off): lỗi, không movement
- [ ] (Tuỳ policy) Bật negative stock tạm: xuất vượt được — xác nhận chấp nhận trước production

### D. Stock Transfer

- [ ] A→B SL 2: A-2, B+2, atomic, ledger đúng from/to
- [ ] From = To: lỗi, không movement

### E. Movements Ledger

- [ ] Lọc theo kho / loại movement / SP
- [ ] Reference type đúng: điều chỉnh, nhập PO/DO, Purchase Inventory sync, v.v.

### F. Purchase → Warehouse Inbound (một luồng chuẩn)

- [ ] **Option 1:** PO delivered → inbound theo dòng
- [ ] **Option 2:** DO nhập received → inbound (nếu bật)
- [ ] Không nhập đôi cùng một lần nhận thật

### G. Purchase Inventory (sync tuyệt đối)

- [ ] Đặt tồn đích → delta → movement ref `PurchaseInventory`

### H. Permissions / Multi-tenant

- [ ] Thiếu quyền → chặn route/action
- [ ] Company scope: không lộ dữ liệu công ty khác
- [ ] Validation: SL âm, không số, precision, ID sai → server chặn sạch

### I. UX / Smoke

- [ ] Modal/tab order; lỗi field-level
- [ ] Mobile usable; performance list lớn (filter + pagination)
- [ ] **Menu Operations** (sidebar Purchase): chỉ **Warehouses / Adjust stock / Transfer stock / Stock movements** (Phụ lục C); **Adjust** và **Transfer** active tách nhau; Import & Flow **không** nằm sidebar (đúng UX đã chốt).

---

## Phụ lục C — Menu UI (Operations) — cập nhật 2026-04-12

Trong **`purchase::sections.sidebar`** (menu **Operations**), khi module **Warehouse** bật, **chỉ** các mục sau (gọn menu; import & cấu hình luồng **không** hiển thị ở đây):

| Mục menu        | Route                       | Điều kiện hiển thị                         |
| --------------- | --------------------------- | ------------------------------------------ |
| Warehouses      | `warehouse.index`           | `view_warehouses` hoặc quyền xem Inventory |
| Adjust stock    | `warehouse.stock.index`     | `view_warehouse_stock` hoặc Inventory      |
| Transfer stock  | `warehouse.transfer.create` | `manage_warehouse_transfer` hoặc Inventory |
| Stock movements | `warehouse.movements.index` | như Adjust stock                           |

**Truy cập khác (không qua Operations sidebar):**

| Nhu cầu                                                       | Cách vào                                                                                                                                                                             |
| ------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| **Import warehouses**                                         | Trang **Warehouses** (`warehouse.index`) — nút/link Import (quyền `add_warehouses`).                                                                                                 |
| **Warehouse flow & stock** (cờ inbound/outbound theo công ty) | **Cài đặt** → menu module Warehouse (`warehouse::sections.setting-sidebar`) → mục tương ứng; route `warehouse.company-flow-settings.index` (quyền `manage_company_setting === all`). |

**Luồng bán/mua ngoài Warehouse:** Credit Note — menu **Invoices** (`creditnotes.index`); Vendor Credits — Operations (`vendor-credits.index`).

---

## Phụ lục B — Gap / rủi ro đã ghi nhận (lịch sử Miaolin, cần xác minh staging)

- **Inbound đôi:** hai flag PO delivered + DO received cùng bật → tồn phình.
- **Sales outbound:** đã có shipment/invoice mode — vẫn audit idempotency, reversal, đúng kho.
- **Legacy PaymentObserver / PurchaseStockAdjustment:** khi warehouse sales outbound bật có nhánh skip — vẫn rà chỗ khác dùng adjustment không warehouse.
- **UI batch/expiry** trên điều chỉnh/chuyển có thể hạn chế — FEFO chỉ phát huy khi có dữ liệu lô.
- **Ledger:** reference có thể chưa deep-link sang chứng từ nguồn.

---

## Liên kết nhanh

| Mục đích                | File                                                                 |
| ----------------------- | -------------------------------------------------------------------- |
| Mục lục Warehouse       | [`WAREHOUSE_INDEX.md`](WAREHOUSE_INDEX.md)                           |
| Index Sales/Fulfillment | [`SALES_FULFILLMENT_DOCS_INDEX.md`](SALES_FULFILLMENT_DOCS_INDEX.md) |
| Sơ đồ E2E               | `DIAGRAM/Purchasing - Inventory - Sales End-to-End Current Flow.mmd` |
| Legacy checklist        | Da loai bo; su dung duy nhat file UAT nay                            |
