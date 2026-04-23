# Kho bán cơ bản — bạn đang hiểu đúng; phần “thêm” trong Craveva là gì?

**Dành cho:** Người chỉ cần nắm **SO → phiếu giao (xuất) → trừ kho → hóa đơn** và **PO → nhập (GRN) → cộng kho**, không muốn đọc hết tài liệu kỹ thuật.  
**Cập nhật:** 2026-04-09 — có mục **§3 đa kho** + ví dụ C.

---

## 1) Mô hình trong đầu bạn — khớp nghiệp vụ chuẩn

| Bạn nghĩ (đơn giản)                                                    | Ý nghĩa                                                                                                                                                                                   |
| ---------------------------------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **SO** → **DO (xuất)** → **trừ kho** → **invoice**                     | Đúng hướng: **đơn bán** → **phiếu giao / giao hàng** → **ghi nhận xuất kho** → **hóa đơn (công nợ / doanh thu)**.                                                                         |
| **PO** → **GRN (nhập)** → **cộng kho** → có/không invoice NCC đều được | Đúng: **đặt mua** → **nhận hàng vào kho** → **tăng tồn**; **hóa đơn nhà cung cấp** là chuyện **mua / kế toán công nợ**, không nhất thiết trùng thời điểm với “hàng đã vào kho” trong app. |

Hệ thống **không** cố thay đổi logic này; phần “nhiều thứ” chủ yếu là **tên gọi trong phần mềm**, **bước trạng thái**, và **công tắc cấu hình** để nhiều khách hàng dùng chung một code.

---

## 2) Tên gọi — tránh nhầm một chữ “DO”

Trong Craveva có **hai loại “phiếu giao / nhận”** khác nhau hẳn:

| Bạn hay gọi                      | Trong app (tóm tắt)                                                          | Ghi chú                                                                             |
| -------------------------------- | ---------------------------------------------------------------------------- | ----------------------------------------------------------------------------------- |
| **DO bán / phiếu giao xuất kho** | **Sales DO** (kỹ thuật: entity `SalesDo`, bảng `sales_dos`)                  | Gắn **đơn bán (SO)**. Đây mới là chỗ **xuất kho bán** khi bạn chọn mode `shipment`. |
| **GRN / phiếu nhận hàng mua**    | **DO nhập** hoặc **GRN** (bảng `delivery_orders` / `grns`, kiểu **inbound**) | Gắn **PO mua**. Đây là **nhập kho mua**, **không** phải đơn bán.                    |

**Không có** “DO bán” trên cùng bảng `delivery_orders` như DO mua — bán dùng **Sales DO** (phiếu giao bán), để khỏi lẫn nhập/xuất một chỗ.

---

## 3) Đa kho — file này có liên quan không? Có, nhưng phần trên **gộp “kho” thành một khái niệm**

Luồng **SO → Sales DO → ship → invoice** và **PO → GRN → nhập** **vẫn y nguyên** khi công ty có **nhiều kho**. Khác biệt là: mỗi chứng từ phải gắn **đúng `warehouse_id`** (hoặc hệ thống suy ra kho mặc định), và **tồn** được lưu **theo từng kho** (và lô/batch nếu dùng), không phải một số tồn chung mơ hồ.

| Việc                     | Đa kho ảnh hưởng chỗ nào?                                                                                                                                                                                                    |
| ------------------------ | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Mua / nhập**           | PO có **kho nhận**; phiếu **GRN / DO nhập** nhận vào **kho ghi trên phiếu** → cộng tồn **kho đó**.                                                                                                                           |
| **Bán, mode `shipment`** | **Sales DO** có **kho xuất** → reserve + trừ tồn **đúng kho** trên phiếu.                                                                                                                                                    |
| **Bán, mode `invoice`**  | Thường chọn kho theo thứ tự: **kho mặc định của khách** (`client_details.default_warehouse_id`) → kho mặc định công ty → một kho active; **chưa** có “mỗi dòng SO một kho” đầy đủ trong service invoice (cần biết giới hạn). |
| **Cân kho giữa các kho** | Dùng **chuyển kho** (warehouse transfer) — luồng **riêng** với mua/bán, không thay thế PO/GRN.                                                                                                                               |

**Ví dụ C — hai kho:**  
Hàng nhập vào **Kho Bắc** (PO/GRN đúng kho). Khách thường lấy từ **Kho Nam** → cần **Sales DO** (hoặc invoice mode) trỏ **Kho Nam** _và_ tồn Nam phải đủ, **hoặc** **chuyển kho** Bắc → Nam trước khi giao.

Tài liệu **chuyên về đa kho** (bảng, rủi ro, import khách gắn kho): [`multi_warehouse_audit_report.md`](multi_warehouse_audit_report.md), [`WAREHOUSE_MASTER_GUIDE.md`](WAREHOUSE_MASTER_GUIDE.md), phần chuyển kho trong [`WAREHOUSE_FLOW_VA_NGHIEP_VU_VI.md`](WAREHOUSE_FLOW_VA_NGHIEP_VU_VI.md).  
Master “kho mặc định khách” trong quy trình tổng: [`QUY_TRINH_PO_DO_SO_INVOICE_WAREHOUSE_VI.md`](QUY_TRINH_PO_DO_SO_INVOICE_WAREHOUSE_VI.md) §1.

---

## 4) Luồng bán — khớp từng bước (mặc định hay dùng: xuất khi **giao/ship**)

Thứ tự thực tế khi cấu hình **`WAREHOUSE_SALES_OUTBOUND_MODE=shipment`** (mặc định trong code):

1. **SO (Order)** — tạo đơn, **chưa** tự trừ tồn chỉ vì có SO.
2. **Sales DO** — phiếu giao từ SO: thường qua các bước **draft → confirmed → shipped → delivered** (tùy màn hình).
3. **Trừ kho** — xảy ra khi phiếu chuyển sang **shipped** (đã giao/xuất theo quy ước hệ thống), **không** phải lúc chỉ mới tạo SO.
4. **Invoice** — hóa đơn bán: **kế toán / công nợ**; ở mode `shipment`, **invoice không trừ thêm tồn** (tránh trừ hai lần).

**Ví dụ A — một lần giao đủ:**  
Khách đặt 100 áo → tạo SO → tạo **một** Sales DO 100 áo → **Ship** → kho trừ 100 → xuất hóa đơn (invoice) cho khách.

**Ví dụ B — giao nhiều đợt:**  
SO 100 áo → Sales DO đợt 1: 30 áo → Ship đợt 1 → trừ 30 tồn → sau đó Sales DO đợt 2: 70 áo → Ship → trừ 70. **Hóa đơn (invoice) gắn SO:** model hiện tại là **tối đa một** `Invoice` với cùng `order_id` (xem `SALES_PURCHASE_FLOW.md` §2.1); thanh toán từng phần = nhiều **Payment** trên cùng một invoice, hoặc hóa đơn **không** gắn SO nếu tách chứng từ.

---

## 5) “Thêm” so với suy nghĩ đơn giản — vì sao có?

### 5.1 Hai bước **confirmed** rồi mới **shipped** (không ship ngay từ SO)

- **Confirmed + giữ chỗ tồn (reserve):** báo kho “đã có kế hoạch xuất”, giảm **bán trùng** khi nhiều đơn cùng một SKU (oversell).
- **Shipped:** mới **trừ tồn thật** (xuất kho) trong mode `shipment`.

Nếu doanh nghiệp bạn **chỉ cần “có đơn là trừ kho”**, đó là **mô hình khác**; trong Craveva mặc định là **tách kế hoạch giao vs xuất thật** — bớt sai số khi có hủy, đổi kho, giao chậm.

### 5.2 Hai **mode** xuất bán: `shipment` vs `invoice`

| Mode                      | Ai trừ kho?                           | Khi nào dùng gợi ý                                                              |
| ------------------------- | ------------------------------------- | ------------------------------------------------------------------------------- |
| **`shipment`** (mặc định) | Khi **Sales DO ship**                 | Có quy trình **kho / giao** rõ: xuất theo lần giao.                             |
| **`invoice`**             | Khi **hóa đơn** (invoice) không draft | Doanh nghiệp quen **“có hóa đơn mới ghi nhận xuất / doanh thu”** — kiểu legacy. |

**Chỉ chọn một mode** cho một môi trường; bật cả hai nguồn trừ tồn sẽ **lệch** hoặc bị chặn bởi cấu hình.

### 5.3 Hai đường **nhập mua**: PO **delivered** vs **GRN/DO nhập received**

Một số công ty nhập kho **khi PO được đánh dấu đã giao đủ**; số khác nhập **khi có phiếu nhận (GRN) quét kho**.  
App cho **hai cờ** (`WAREHOUSE_INBOUND_FROM_PO_DELIVERED`, `WAREHOUSE_INBOUND_FROM_DO_RECEIVED`) nhưng **trên cùng một lần nhận thật chỉ nên dùng một đường** — nếu bật cả hai, hệ thống có **cảnh báo / chặn** để **không nhập đôi** cùng một lượng.

### 5.4 **Invoice nhà cung cấp (Purchase bill)** không tự cộng kho

- **Nhập kho vật lý** thường theo **PO delivered** hoặc **GRN received** (đã nói ở trên).
- **Hóa đơn NCC** là **công nợ / thanh toán** — có thể đến **trước hoặc sau** lúc hàng vào kho tùy thực tế. Vì vậy trong thiết kế hiện tại, **bill không đồng nghĩa tự động một movement nhập** (tránh lệch thời điểm kế toán vs kho).

### 5.5 **Công tắc env** (`WAREHOUSE_*`)

Để **không sửa code** khi từng khách tắt kho tự động, chỉ nhập tay, hoặc dùng mode invoice — tất cả nằm trong **biến môi trường**. Bảng đầy đủ: [`WAREHOUSE_AND_PURCHASE_FLOW_ENV_REFERENCE_VI.md`](WAREHOUSE_AND_PURCHASE_FLOW_ENV_REFERENCE_VI.md).

### 5.6 Tích hợp **AI / webhook** tạo SO

Có thể **kiểm tra còn bán được không** trước khi tạo đơn — là lớp **bảo vệ bán quá tồn**, không thay thế luồng SO → Sales DO → ship ở trên.

---

## 6) Một trang “chỉ cần nhớ” khi vận hành

1. **Bán:** SO → **Sales DO** → **Ship** → trừ kho (mode `shipment`) → Invoice là kế toán.
2. **Mua:** PO → nhận hàng (**một** nguồn: PO delivered **hoặc** GRN/DO nhập received) → cộng kho.
3. **Đừng** trộn hai đường nhập cho cùng một lần nhận; **đừng** kỳ vọng vừa ship vừa invoice đều trừ tồn — chọn **một** mode.
4. **Nhiều kho:** luôn kiểm tra **kho trên PO / GRN / Sales DO** (và **kho mặc định khách** nếu xuất theo invoice); thiếu hàng **đúng kho** thì **chuyển kho** hoặc nhập vào kho đúng.

---

## 7) Tài liệu đi sâu hơn (khi cần)

| Nhu cầu                                                 | File                                                                                                                            |
| ------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------- |
| **Đa kho** (tồn theo kho, transfer, import khách → kho) | [`multi_warehouse_audit_report.md`](multi_warehouse_audit_report.md) · [`WAREHOUSE_MASTER_GUIDE.md`](WAREHOUSE_MASTER_GUIDE.md) |
| Quy trình đầy đủ PO / DO / SO / Invoice / kho           | [`QUY_TRINH_PO_DO_SO_INVOICE_WAREHOUSE_VI.md`](QUY_TRINH_PO_DO_SO_INVOICE_WAREHOUSE_VI.md)                                      |
| Biến `.env` giải thích từng dòng                        | [`WAREHOUSE_AND_PURCHASE_FLOW_ENV_REFERENCE_VI.md`](WAREHOUSE_AND_PURCHASE_FLOW_ENV_REFERENCE_VI.md)                            |
| So sánh kỳ vọng vs code (QA, có mục multi-warehouse)    | [`ERP_SO_PO_DO_INVOICE_WAREHOUSE_QA_VERIFICATION_VI.md`](ERP_SO_PO_DO_INVOICE_WAREHOUSE_QA_VERIFICATION_VI.md)                  |
| Audit tổng quan ổn định luồng                           | [`ERP_SO_PO_DO_GRN_SCHEMA_AND_LEGACY_MATRIX_VI.md`](ERP_SO_PO_DO_GRN_SCHEMA_AND_LEGACY_MATRIX_VI.md)                            |
| Smoke test sau khi đổi cấu hình                         | [`ERP_SO_PO_DO_INVOICE_WAREHOUSE_QA_VERIFICATION_VI.md`](ERP_SO_PO_DO_INVOICE_WAREHOUSE_QA_VERIFICATION_VI.md)                  |
| Luồng kho thuần (điều chỉnh, **chuyển kho**, movement)  | [`WAREHOUSE_FLOW_VA_NGHIEP_VU_VI.md`](WAREHOUSE_FLOW_VA_NGHIEP_VU_VI.md)                                                        |

**Mục lục nhanh:** [`WAREHOUSE_INDEX.md`](WAREHOUSE_INDEX.md) · [`SALES_FULFILLMENT_DOCS_INDEX.md`](SALES_FULFILLMENT_DOCS_INDEX.md)
