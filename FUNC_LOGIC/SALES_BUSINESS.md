# Quy trình PO · DO · SO · Invoice · Warehouse (Craveva)

**Mục đích:** Một file **hướng dẫn nghiệp vụ + vận hành** theo thứ tự: mua (PO/DO), kho, bán (SO/Invoice), cấu hình.  
**Đối tượng:** PM, BA, vận hành, dev mới.  
**Cập nhật:** 2026-04-12

**Bảng thật trên DB (GRN / Sales DO) vs legacy:** [`SALES_FULFILLMENT_SCHEMA_MATRIX.md`](SALES_FULFILLMENT_SCHEMA_MATRIX.md) — trong file này vẫn dùng từ nghiệp vụ “DO nhận hàng”, “phiếu giao bán”; **bảng ghi hiện tại** lần lượt là `grns` và `sales_dos`, không còn `delivery_orders` / `sales_shipments` cho CRUD chính.

**Trả hàng bán (Credit Note → nhập kho):** [`SALES_RETURN_BUSINESS.md`](SALES_RETURN_BUSINESS.md)  
**Chỉ riêng module kho (điều chỉnh, chuyển, ledger):** [`WAREHOUSE_BUSINESS.md`](WAREHOUSE_BUSINESS.md)  
**URL, quyền, DB:** [`WAREHOUSE_MASTER_GUIDE.md`](WAREHOUSE_MASTER_GUIDE.md)  
**Trạng thái triển khai, audit, prompt Cursor:** [`MODULE_WAREHOUSE.md`](MODULE_WAREHOUSE.md) · [`WAREHOUSE_RUNBOOK_AND_UPGRADE_PLAN.md`](../FUNC_IMPROVE/04_WH_RUNBOOK_UPGRADE.md)
**Test tay / UAT E2E:** [`SALES_PURCHASE_WAREHOUSE_UAT_CHECKLIST.md`](SALES_PURCHASE_WAREHOUSE_UAT_CHECKLIST.md)

---

## 1) Chuẩn bị master (làm trước mọi luồng)

| Thứ tự | Việc                                                                                                       | Ghi chú                                                                                             |
| ------ | ---------------------------------------------------------------------------------------------------------- | --------------------------------------------------------------------------------------------------- |
| 1      | **Công ty / tenant** đúng                                                                                  | Mọi chứng từ gắn `company_id`.                                                                      |
| 2      | **Kho** (Warehouse): ít nhất 1 kho active, 1 kho mặc định công ty (nếu dùng)                               | Module Warehouse bật.                                                                               |
| 3      | **Sản phẩm** (Product): SKU, loại **hàng hóa** (không phải service) nếu cần trừ tồn                        | Chi tiết form/import: [`PRODUCT_BUSINESS.md`](PRODUCT_BUSINESS.md).                                 |
| 4      | **Khách** (Client): có **kho mặc định giao** (`default_warehouse_id`) khi dùng **Scope B** xuất theo khách | Import/map: `WAREHOUSE_MASTER_GUIDE`, `CLIENT_BUSINESS.md`. |

---

## 2) Luồng mua hàng → **tăng** tồn (nhập kho)

### 2.1 Purchase Order (PO) — đặt hàng NCC

1. Tạo **PO**: chọn vendor, **`warehouse_id`** (kho nhận), dòng có **`product_id` + số lượng**.
2. Khi hàng được coi là **đã giao** (`delivery_status` → **delivered** theo UI/flow):
    - Nếu `WAREHOUSE_INBOUND_FROM_PO_DELIVERED=true` và module Warehouse bật → hệ thống ghi **nhập kho** qua `StockMovementService` (tham chiếu PO).
3. **PurchaseBill** (hóa đơn NCC): cập nhật trạng thái thanh toán/billed — **không** tự tạo movement kho trong observer bill.

### 2.2 Phiếu nhận hàng mua (GRN / DO inbound — bảng `grns`)

- Trong Craveva, chứng từ nhận hàng mua gắn PO: nghiệp vụ thường gọi **GRN** hoặc “DO nhập”; **bảng ghi hiện tại** là **`grns` / `grn_items`** (không còn là nguồn ghi chính trên `delivery_orders`). Khi trạng thái **received** có thể ghi nhập lô nếu `WAREHOUSE_INBOUND_FROM_DO_RECEIVED=true`.
- **Quan trọng:** Trên cùng môi trường chỉ nên coi **một** nguồn nhập “chuẩn” cho cùng một lần nhận thật: **PO delivered** **hoặc** **DO received** — bật cả hai dễ **nhập đôi** cùng một lô.

### 2.3 Purchase Inventory (phiếu tồn / sync tuyệt đối)

- Điều chỉnh tồn theo **số đích** từng kho + sản phẩm → delta → movement.
- Màn Inventory datatable hiển thị KPI tồn theo dữ liệu batch kho thời gian thực (tránh nhầm với snapshot legacy).
- Chi tiết bảng ghi: [`INVENTORY_BUSINESS.md`](INVENTORY_BUSINESS.md).

### 2.4 Trả hàng mua / Vendor Credit / công nợ NCC

- **PurchaseBill** = hóa đơn/công nợ phải trả gắn PO; **không** ghi `stock_movements` trong observer bill.
- **Vendor Credit** (`PurchaseVendorCredit`) = chứng từ **giảm phải trả** (thường gắn bill → gián tiếp PO). **Xuất kho trả NCC:** dòng `purchase_vendor_items` loại `item` có `product_id` → `VendorCreditWarehouseStockService` / `StockMovementService::recordOutbound`; xóa chứng từ hoặc sửa/xóa dòng → hoàn tác / đồng bộ lại (idempotent theo khóa movement). Chi tiết: [`PURCHASE_RETURN_BUSINESS.md`](PURCHASE_RETURN_BUSINESS.md).
- **Vendor Payment** = thanh toán / cấn trừ bill (và có thể áp credit).
- Luồng vận hành nên thống nhất: **ghi Vendor Credit khi đã thống nhất SL trả** và cấu hình inbound/outbound kho để tránh lệch với thực tế vật lý.

---

## 3) Luồng kho thuần (không qua PO)

- **Điều chỉnh tay** (+/−), **chuyển kho** giữa các kho, xem **sổ movement**.
- Xem [`WAREHOUSE_BUSINESS.md`](WAREHOUSE_BUSINESS.md).

---

## 4) Luồng bán — **Order (SO)** → **Sales DO**/**Invoice** → **xuất kho**

### 4.1 Sales Order (Order)

1. Tạo **Order** + dòng (`OrderItems`), gắn **client**, sản phẩm, SL.
2. **Lưu ý sản phẩm:** Với một SO, hệ thống **mặc định** coi **tối đa một** `Invoice` gắn `order_id` (1 SO → 1 HĐ kiểu “Cách 1”). Thanh toán từng phần nên là nhiều **Payment** trên cùng invoice; nếu cần tách nhiều invoice thì phải tạo invoice không gắn `order_id` hoặc có adapter riêng.
3. **Trừ tồn:** Trạng thái Order **không** tự gọi xuất kho; phụ thuộc mode outbound ở bước sau.

### 4.2 Sales DO (entity: `SalesDo` — bảng `sales_dos`)

1. Tạo một hoặc nhiều **Sales DO** từ cùng một SO (partial shipment). Route/controller có thể vẫn mang tên “shipment”; dữ liệu lưu ở **`sales_dos` / `sales_do_items`** (xem ma trận legacy).
2. Chuyển trạng thái: `draft -> confirmed -> shipped -> delivered` (hoặc `cancelled`).
3. Khi mode outbound là `shipment`, action **Ship** (`SalesDoService::ship`, trạng thái → **`shipped`**) gọi `SalesShipmentStockService::applyOutboundForShipment` — **đây là bước trừ tồn**, không phải `delivered` một mình.
4. Có action **reverse** (`SalesDoService::reverse`) hoặc **cancel** (nếu đã outbound) để hoàn kho / hủy reservation khi xử lý sai lệch vận hành.

### 4.3 Invoice (hóa đơn bán)

1. Tạo **Invoice** từ order hoặc độc lập; dòng kiểu **item** + **`product_id`** mới đủ điều kiện xuất kho hàng.
2. Khi mode outbound là `invoice`, invoice **không** ở trạng thái **draft** và **không** phải credit note sẽ ghi outbound theo kho resolve (khách → công ty → kho active).

### 4.4 Cấu hình orchestration outbound (quan trọng)

Đồng thời thỏa:

- `.env`: **`WAREHOUSE_SALES_OUTBOUND_ENABLED=true`** (và `php artisan config:clear`).
- `.env`: **`WAREHOUSE_SALES_OUTBOUND_MODE=shipment|invoice`** (mặc định code đang là **`shipment`**; override bằng env nếu tenant cần legacy invoice outbound).
- Module **Warehouse** bật; user đăng nhập có **`warehouse`** trong `user_modules`.
- Đã migrate các bảng kho liên quan (`invoice_warehouse_stock_postings` nếu mode invoice).
- **Payment:** Khi flag trên, `PaymentObserver` **không** chỉnh legacy `PurchaseStockAdjustment` cho đường stock warehouse (tránh lệch đa kho).

Quy tắc tránh double deduction:

- Mode `shipment`: chỉ **Sales DO** (`sales_dos` → ship) trừ tồn, invoice không trừ thêm.
- Mode `invoice`: giữ legacy invoice outbound.

**Tắt flag** = không tự tạo outbound từ shipment/invoice (tồn chỉ thay đổi bởi PO/DO/inventory/chuyển kho/điều chỉnh).

### 4.5 Trả hàng bán (Credit Note)

- Khi phát hành dòng **Credit Note** có `product_id` (hàng): **nhập kho** qua `CreditNoteWarehouseStockService` (idempotent; xóa CN hoàn tác). Chi tiết: [`SALES_RETURN_BUSINESS.md`](SALES_RETURN_BUSINESS.md).
- Mode `shipment`: kho nhận trả có thể suy từ **Sales DO** đã ship cùng order + sản phẩm; có thể ghi đè bằng `credit_note_items.warehouse_id` (migration Warehouse).

---

## 5) Sơ đồ tổng (tóm tắt)

```mermaid
flowchart TB
  subgraph mua [Mua hàng]
    PO[PO delivered]
    GRN[GRN received]
    INVADJ[Purchase Inventory]
  end
  subgraph kho [Kho]
    WH[Warehouse / batch / movement]
  end
  subgraph ban [Bán hàng]
    SO[Order SO]
    SDO[Sales DO ship → shipped]
    INV[Invoice không draft]
    CN[Credit Note dòng hàng]
  end
  PO -->|"inbound nếu flag PO"| WH
  GRN -->|"inbound nếu flag GRN/DO"| WH
  INVADJ --> WH
  SO --> SDO
  SO -.-> INV
  SDO -->|"sales_outbound_mode = shipment"| WH
  INV -->|"sales_outbound_mode = invoice"| WH
  INV -.-> CN
  CN -->|"nhập kho trả"| WH
```

---

## 5.1) Mental model kỹ thuật cần nhớ

| Chứng từ / bảng | Vai trò |
| --------------- | ------- |
| `orders` / `order_items` | **SO**: nhu cầu bán; không tự trừ tồn. |
| `sales_dos` / `sales_do_items` | **Sales DO**: phiếu giao bán; khi **Ship** sẽ trừ tồn nếu mode `shipment`. |
| `invoices` / `invoice_items` | Hóa đơn khách; trừ tồn chỉ khi mode `invoice`. |
| `purchase_orders` / `purchase_items` | PO mua; có `warehouse_id` nhận hàng. |
| `grns` / `grn_items` | Phiếu nhận hàng mua hiện tại; thay cho bảng delivery order legacy. |
| `purchase_bills` | Công nợ NCC; không tự cộng kho. |
| `stock_movements` | Ledger nhập/xuất/chuyển kho; nên là nơi đối soát thay đổi tồn. |

Code/service chính:

- Sales DO outbound: `SalesDoService`, `SalesShipmentStockService`.
- Invoice outbound: `InvoiceWarehouseStockService`.
- Purchase inbound/GRN: module Purchase + `StockMovementService`.
- Trả hàng: `CreditNoteWarehouseStockService`, `VendorCreditWarehouseStockService`.

---

## 6) Checklist vận hành nhanh sau khi cấu hình

- [ ] Một nguồn inbound: PO **hoặc** DO (không double inbound).
- [ ] `WAREHOUSE_ALLOW_NEGATIVE_STOCK` theo policy.
- [ ] Chọn đúng mode outbound (`shipment` hoặc `invoice`) theo quy trình vận hành.
- [ ] Thử 1 PO delivered → tồn tăng + có dòng movement inbound.
- [ ] Nếu mode `shipment`: thử SO 10 → **Ship** DO 4 + 6 (trừ tồn tại bước ship), không cho vượt tồn khả dụng.
- [ ] Nếu mode `invoice`: thử 1 invoice không draft -> tồn giảm + outbound + posting.
- [ ] Sửa/xóa invoice → reversal đúng kỳ vọng (UAT).

---

_File nay la ban canonical cho quy trinh PO/DO/SO/Invoice/Warehouse._

