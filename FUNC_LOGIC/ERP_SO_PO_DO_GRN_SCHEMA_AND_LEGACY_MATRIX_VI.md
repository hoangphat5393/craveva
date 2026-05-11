# SO · PO · GRN · Sales DO · Warehouse — Master: schema, luồng bán, legacy & drop

**Mục đích:** Một file **master** — (1) **luồng bán/mua hiện tại** đối chiếu code, (2) **bảng canonical vs lịch sử**, (3) **trạng thái bảng legacy đã gỡ** (migration + hậu quả cột/UX), (4) **audit luồng** đã hợp nhất.  
**Cập nhật:** 2026-04-10.

**Trạng thái triển khai (đã xác nhận):** Bốn bảng `sales_shipment_items`, `sales_shipments`, `delivery_order_items`, `delivery_orders` **không còn tồn tại** trên **cả ba** môi trường đã rà (local / staging / hub). Schema nghiệp vụ mua·bán dùng **`grns` / `grn_items`** và **`sales_dos` / `sales_do_items`**. Phần dưới vẫn mô tả migration DROP và cột “treo” để audit / UX / restore DB dump cũ.

**Quy trình vận hành (PO/SO/invoice):** [`QUY_TRINH_PO_DO_SO_INVOICE_WAREHOUSE_VI.md`](QUY_TRINH_PO_DO_SO_INVOICE_WAREHOUSE_VI.md)  
**Biến `.env`:** [`WAREHOUSE_AND_PURCHASE_FLOW_ENV_REFERENCE_VI.md`](WAREHOUSE_AND_PURCHASE_FLOW_ENV_REFERENCE_VI.md)  
**Danh sách issue QA chi tiết (bảng A–O):** [`ERP_SO_PO_DO_INVOICE_WAREHOUSE_QA_VERIFICATION_VI.md`](ERP_SO_PO_DO_INVOICE_WAREHOUSE_QA_VERIFICATION_VI.md)  
**Lịch sử refactor / tracker:** [`SO_DO_PO_GRN_REFACTOR_VI.md`](../FUNC_IMPROVE/05_SO_DO_PO_GRN_REFACTOR_VI.md)

---

## Mục lục

1. [Luồng bán hàng hiện tại (canonical)](#1-luồng-bán-hàng-hiện-tại-canonical)
2. [Luồng mua / GRN (tóm tắt)](#2-luồng-mua--grn-tóm-tắt)
3. [Bảng database: canonical vs legacy](#3-bảng-database-canonical-vs-legacy)
4. [Bảng legacy đã gỡ & migration DROP](#4-bảng-legacy-đã-gỡ--migration-drop)
5. [Class / file PHP](#5-class--file-php)
6. [Lệnh Artisan migrate dữ liệu](#6-lệnh-artisan-migrate-dữ-liệu)
7. [Audit luồng (gộp)](#7-audit-luồng-gộp)
8. [Map tài liệu Markdown](#8-map-tài-liệu-markdown)

---

## 1) Luồng bán hàng hiện tại (canonical)

**Chứng từ bán:** `orders` / `order_items` (SO). **Phiếu giao bán (Sales DO):** bảng **`sales_dos`**, dòng **`sales_do_items`** — model `SalesDo` / `SalesDoItem`. UI/route có thể vẫn tên “sales-shipment” hoặc “sales-do” tùy `PURCHASE_FLOW_NAMING_MODE`; **DataTable** (`SalesShipmentDataTable`) query qua `SalesDoRuntime` → **chỉ `sales_dos`**. Bảng `sales_shipments` **không còn** trên DB đã triển khai (đã DROP).

**Chuỗi nghiệp vụ kho (mode mặc định khuyến nghị `WAREHOUSE_SALES_OUTBOUND_MODE=shipment`):**

| Bước | Việc                        | Code / ghi chú                                                                                                  |
| ---- | --------------------------- | --------------------------------------------------------------------------------------------------------------- |
| 1    | Tạo / sửa SO                | `Order` — **không** tự trừ tồn.                                                                                 |
| 2    | Tạo Sales DO từ SO          | `SalesDoService::create` → ghi `sales_dos` + `sales_do_items`.                                                  |
| 3    | Confirm (draft → confirmed) | `SalesDoService::confirm` → `SalesShipmentStockService::ensureReservationsForShipment` (đặt chỗ / reservation). |
| 4    | Ship                        | `SalesDoService::ship` → reservation + outbound batch, `outbound_stock_applied` trên header.                    |
| 5    | Deliver                     | Đổi trạng thái delivered (không thêm outbound theo design hiện tại).                                            |
| 6    | Reverse / Cancel            | Hoàn kho / nhả reservation theo service — xem `SalesDoService`.                                                 |

**Hóa đơn bán:** `Invoice` / `InvoiceItems`. Khi `WAREHOUSE_SALES_OUTBOUND_MODE=shipment`, **`InvoiceWarehouseStockService` không post outbound từ invoice** (tránh trừ trùng với ship). Khi mode `invoice`, xuất kho theo invoice (legacy so với shipment).

**Lịch sử / code:** Tên class `SalesShipment*` và service `SalesShipmentStockService` vẫn trong repo; **bảng** `sales_shipments*` đã **không còn** trên các môi trường đã xác nhận. Dữ liệu cũ có thể truy vần qua cột `legacy_*` trên `sales_do*` và qua `stock_movements.reference_type` / `reference_id` (bản ghi lịch sử).

---

## 2) Luồng mua / GRN (tóm tắt)

- **PO** → **GRN** (`grns` / `grn_items`, `Grn` / `GrnItem`) nhận hàng inbound; `GrnService` + `DeliveryOrderObserver` (tên lịch sử) xử lý nhập kho khi cấu hình bật.
- Bảng **`delivery_orders` / `delivery_order_items`** đã **không còn** trên DB triển khai; trước đây tương đương phiếu nhận mua cũ — thay bằng **`grns` / `grn_items`**.
- Cấu hình nhập: PO delivered vs GRN/DO received — chỉ **một** nguồn canonical cho cùng lần nhận; xem mục 7 và env reference.

---

## 3) Bảng database: canonical vs legacy

### 3.1 Canonical (luồng chính sau cutover)

| Bảng                          | Migration (Purchase)                           | Ghi chú                                                      |
| ----------------------------- | ---------------------------------------------- | ------------------------------------------------------------ |
| `grns`, `grn_items`           | `2026_03_30_191000_create_grn_tables.php`      | `legacy_delivery_order_id` / `legacy_delivery_order_item_id` |
| `sales_dos`, `sales_do_items` | `2026_03_30_190000_create_sales_do_tables.php` | `legacy_sales_shipment_id` / `legacy_sales_shipment_item_id` |

Kho chung: `stock_movements`, `warehouse_product_stock`, `warehouse_product_batches`, `stock_reservations`, …

### 3.2 Bảng lịch sử đã gỡ khỏi DB (xác nhận 3 môi trường)

Các bảng sau **không còn** trong schema sau khi chạy migration `2026_03_31_091500_…` (đã áp dụng trên local, staging, hub theo kiểm tra của team):

| Bảng (đã DROP)         | Ghi chú                     |
| ---------------------- | --------------------------- |
| `sales_shipment_items` | Thay bằng `sales_do_items`. |
| `sales_shipments`      | Thay bằng `sales_dos`.      |
| `delivery_order_items` | Thay bằng `grn_items`.      |
| `delivery_orders`      | Thay bằng `grns`.           |

**Cột trên bảng khác vẫn mang tên cũ:** `stock_movements.delivery_order_item_id` — **không có FK** tới bảng đã xóa; giá trị có thể là **ID dòng nhận cũ** hoặc (sau cutover) **`grn_items.id`**. `invoice_items.delivery_order_item_id` — join Eloquent `DeliveryOrderItem` trả **null** cho dữ liệu cũ. Xem mục **4.4** bên dưới.

---

## 4) Bảng legacy đã gỡ & migration DROP

### 4.0 Trạng thái hiện tại (đã xác nhận)

Trên **local, staging, hub**: bốn bảng dưới đây **không còn** (`SHOW TABLES` rỗng). Đây là **schema chuẩn** của dự án sau khi chuỗi migration đã chạy đủ.

| Thứ tự DROP (trong migration) | Bảng                   |
| ----------------------------- | ---------------------- |
| 1                             | `sales_shipment_items` |
| 2                             | `sales_shipments`      |
| 3                             | `delivery_order_items` |
| 4                             | `delivery_orders`      |

### 4.1 Migration trong repo

`Modules/Purchase/Database/Migrations/2026_03_31_091500_drop_legacy_sales_shipment_and_delivery_order_tables.php`

- `up()`: `DROP` theo thứ tự con trước cha — như bảng trên.
- `down()`: scaffold tạo lại bảng **rỗng** (không phục hồi dữ liệu; chỉ dùng khi rollback kỹ thuật hiếm).

### 4.2 Khi **mở DB dump cũ** hoặc **môi trường mới** chưa migrate

Migration **không** tự chạy `purchase:sales-do-migrate-data` / `purchase:grn-migrate-data`. Nếu ai đó áp migration DROP lên DB **chỉ còn dữ liệu trên bảng cũ** mà chưa copy sang `sales_dos` / `grns` → **mất dữ liệu**. Thứ tự an toàn (cho bản sao DB cũ hoặc onboarding):

1. Backup.
2. Có `sales_dos`, `grns` (`2026_03_30_190000`, `191000`).
3. Chạy migrate dữ liệu (dry-run trước): `purchase:sales-do-migrate-data`, `purchase:grn-migrate-data`.
4. Đối soát (`legacy_*`, rehearsal/reconcile nếu cần).
5. Rồi mới chạy migration DROP (hoặc để deploy chạy theo thứ tự đã kiểm soát).

### 4.3 Hậu quả vận hành (đã DROP — trùng với môi trường hiện tại)

| Điều kiện                                                                         | Ý nghĩa                                                                                                   |
| --------------------------------------------------------------------------------- | --------------------------------------------------------------------------------------------------------- |
| App đọc/ghi chứng từ qua `sales_dos` / `grns`                                     | Luồng chính.                                                                                              |
| `stock_movements.delivery_order_item_id` / `invoice_items.delivery_order_item_id` | Có thể **không** join được tới bảng đã xóa — xử lý UX / báo cáo như mục 4.4.                              |
| Artisan `purchase:*-migrate-data` đọc `sales_shipments` / `delivery_orders`       | **Lỗi “Required tables not found”** — bình thường khi bảng đã DROP; chỉ chạy trên bản DB **còn** bảng cũ. |

### 4.4 Danh sách bảng / cột kiểm tra dữ liệu & gợi ý UX (schema hiện tại)

**A) Bảng đã không còn (không query trong app chính)**

Không dùng `SELECT` từ bốn bảng trên môi trường đã xác nhận — sẽ lỗi “table doesn’t exist”. Báo cáo BI / SQL ngoài app cần sửa join sang `sales_dos` / `grns`.

**B) Bảng vẫn tồn tại nhưng có cột / dữ liệu “treo” tham chiếu tới bảng đã DROP**

| Bảng                 | Cột / nội dung                   | Kiểm tra dữ liệu                                                                                                                                                                                   | Gợi ý UX                                                                                                                                                                             |
| -------------------- | -------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| `stock_movements`    | `delivery_order_item_id`         | Đếm `WHERE delivery_order_item_id IS NOT NULL`; sau DROP, giá trị **không còn** khớp `delivery_order_items.id`. Với inbound sau cutover, cột này thường chứa **`grn_items.id`** (tên cột lịch sử). | Trên màn chi tiết movement: nếu join `DeliveryOrderItem` null → hiển thị “Dòng nhận hàng (GRN)” và resolve qua `grn_items` / `reference_type` + `reference_id` thay vì link “DO cũ”. |
| `stock_movements`    | `reference_type`, `reference_id` | Tìm các giá trị class/string cũ: `SalesShipment`, `Modules\Purchase\Entities\SalesShipment`, `DeliveryOrder`, `App\Models\DeliveryOrder`, …                                                        | Báo cáo kho / audit: nhãn “Chứng từ gốc (legacy)” hoặc map sang `sales_dos` / `grns` qua `legacy_*` trên bảng mới.                                                                   |
| `invoice_items`      | `delivery_order_item_id`         | Đếm NOT NULL — sau DROP không resolve `DeliveryOrderItem`.                                                                                                                                         | Ẩn link “Xem DO”; nếu cần, tra cứu song song `grn_items` qua mapping lịch sử hoặc chỉ hiển thị mã tham chiếu dạng text.                                                              |
| `stock_reservations` | `reference_type`, `reference_id` | Giống `stock_movements` — có thể còn chuỗi tham chiếu tới entity cũ cho bản ghi lịch sử.                                                                                                           | Reserve đang mở: ưu tiên chuẩn hóa về `SalesDo`; lịch sử cũ: nhãn rõ “legacy shipment id”.                                                                                           |

**C) Bảng mới — cột lưu mapping (nên có sau migrate, hữu ích cho audit & UX)**

| Bảng             | Cột                             |
| ---------------- | ------------------------------- |
| `sales_dos`      | `legacy_sales_shipment_id`      |
| `sales_do_items` | `legacy_sales_shipment_item_id` |
| `grns`           | `legacy_delivery_order_id`      |
| `grn_items`      | `legacy_delivery_order_item_id` |

Dùng các cột này để hiển thị “Mã chứng từ cũ” / tra cứu khi bảng nguồn đã gỡ.

**D) Custom fields / báo cáo tùy chỉnh**

- Nhóm CF **Delivery Order** đã được gỡ theo migration native columns (xem `FUNC_LOGIC/CUSTOM_FIELDS_SYSTEMWIDE_AUDIT_TABLE_VI.md`). Rà **report SQL / BI** còn join tới tên bảng cũ — **sửa** sang `grns` / `sales_dos`.

**E) Hành vi sản phẩm (UX)**

- **DataTables** Sales DO / GRN: `normalizeLegacyRequestColumns` — nếu user kẹt sort/search, **xóa state localStorage** cột tên cũ một lần.
- **Menu / nhãn:** `PURCHASE_FLOW_NAMING_MODE`; URL kỹ thuật có thể vẫn chứa `sales-shipments`.

---

## 5) Class / file PHP

| Thành phần                                          | Vai trò                                                                                                                    |
| --------------------------------------------------- | -------------------------------------------------------------------------------------------------------------------------- |
| `SalesDo`, `SalesDoItem`                            | Active — bảng `sales_dos` / `sales_do_items`.                                                                              |
| `SalesDoService`, `SalesShipmentStockService`       | Active — xuất kho / reservation (tên service giữ “Shipment”).                                                              |
| `SalesShipment`, `SalesShipmentItem`                | Eloquent map tên bảng **đã DROP** — còn trong repo (type hints / migrate / rollback); **không** dùng cho DB đã triển khai. |
| `SalesShipmentController`, `SalesShipmentDataTable` | Active — dùng `SalesDoRuntime` cho query.                                                                                  |
| `Grn`, `GrnItem`, `GrnService`                      | Active.                                                                                                                    |
| `DeliveryOrder`, `DeliveryOrderItem`                | Model map bảng **đã DROP** — union type observer; không query bảng trên DB hiện tại.                                       |
| `DeliveryOrderObserver`                             | Active — nhận `Grn \| DeliveryOrder`, cập nhật cờ qua `GrnRuntime::headerTable()`.                                         |
| `DeliveryOrderDataTable`                            | Active — query qua `GrnRuntime`.                                                                                           |
| `GrnRuntime`, `SalesDoRuntime`                      | `isCutoverEnabled()` **luôn `true`** → luôn bảng mới.                                                                      |
| `config('purchase.do_grn_cutover_enabled')`         | Chủ yếu **`FlowPermission`** (alias quyền); **không** tắt bảng qua Runtime.                                                |

---

## 6) Lệnh Artisan migrate dữ liệu

- `php artisan purchase:sales-do-migrate-data` — cần bảng `sales_shipments` + `sales_shipment_items` (**chỉ** khi restore dump cũ / DB chưa DROP).
- `php artisan purchase:sales-do-migrate-rollback`
- `php artisan purchase:grn-migrate-data` — cần `delivery_orders` + `delivery_order_items` (điều kiện tương tự).
- `php artisan purchase:grn-migrate-rollback`

Trên **local / staging / hub đã xác nhận**: bốn bảng không còn → các lệnh migrate-data **sẽ lỗi** “Required tables not found” — **đúng**, không phải bug deploy.

---

## 7) Audit luồng (gộp)

_Nguồn: audit nội bộ 2026-04-09. Phương pháp: đối chiếu observer, service, config; không thay UAT tay._

### 7.1 Thuật ngữ

| Tên trong UI / tài liệu | Thực thể kỹ thuật                                         |
| ----------------------- | --------------------------------------------------------- |
| **PO**                  | `PurchaseOrder`                                           |
| **GRN / DO nhập**       | `Grn` + inbound; **không** là phiếu giao bán              |
| **SO**                  | `Order`                                                   |
| **Sales DO**            | `SalesDo` — bảng `sales_dos`                              |
| **Invoice**             | `Invoice` — outbound chỉ khi mode `invoice` + service bật |

### 7.2 Nhập kho (mua)

| Kích hoạt                 | Config                                     | Code                                                                           |
| ------------------------- | ------------------------------------------ | ------------------------------------------------------------------------------ |
| PO → inbound              | `WAREHOUSE_INBOUND_FROM_PO_DELIVERED=true` | `PurchaseOrderObserver` → policy → `StockMovementService`                      |
| GRN received → inbound    | `WAREHOUSE_INBOUND_FROM_DO_RECEIVED=true`  | `DeliveryOrderObserver` + idempotent `inbound_stock_applied`                   |
| Cả hai inbound bật        | —                                          | `WarehouseFlowPolicyService` **throw** (fail fast)                             |
| PO delivered + DO cùng lô | Cả hai bật                                 | Observer DO **skip** nếu PO đã delivered + PO inbound bật (double-count guard) |

### 7.3 Xuất kho (bán)

| `WAREHOUSE_SALES_OUTBOUND_MODE` | Ai trừ tồn?                                                                |
| ------------------------------- | -------------------------------------------------------------------------- |
| `shipment`                      | `SalesShipmentStockService` lúc **ship**; invoice không post outbound thêm |
| `invoice`                       | `InvoiceWarehouseStockService` / observer invoice                          |

**Điều kiện:** `WAREHOUSE_SALES_OUTBOUND_ENABLED=true`, module Warehouse, mode hợp lệ trong `WarehouseFlowPolicyService`.

### 7.4 Payment legacy

`PaymentObserver::adjustStock` với `PurchaseStockAdjustment` chỉ nhánh legacy khi **`WAREHOUSE_SALES_OUTBOUND_ENABLED` false**; khi bật true thì bỏ qua để tránh double với `stock_movements`.

### 7.5 SO không tự trừ tồn

Tạo/sửa **Order** không gọi xuất kho trực tiếp trong luồng đã rà; trừ tồn qua **Sales DO ship** hoặc **invoice** (theo mode).

### 7.6 WUP (tóm tắt)

WUP-01…07: `warehouse_type`, availability, reserve/outbound, canonical inbound/outbound, unit conversion, idempotency — chi tiết [`WAREHOUSE_RUNBOOK_AND_UPGRADE_PLAN_VI.md`](../FUNC_IMPROVE/04_WAREHOUSE_RUNBOOK_AND_UPGRADE_PLAN_VI.md) §6.

### 7.7 Rủi ro mở rộng

Nhiều invoice trên một SO; `default_warehouse_id` khách; job không có `user()` — xem [`ERP_SO_PO_DO_INVOICE_WAREHOUSE_QA_VERIFICATION_VI.md`](ERP_SO_PO_DO_INVOICE_WAREHOUSE_QA_VERIFICATION_VI.md).

### 7.8 Kết luận audit

Ổn định khi: (1) một nguồn nhập canonical, (2) một mode xuất (`shipment` hoặc `invoice`), (3) warehouse + migration + quyền đúng. Smoke: `WarehouseUpgradeP0Test`, `PurchaseInboundStockFlowTest`, `InvoiceWarehouseStockScopeBTest`, v.v.

---

## 8) Map tài liệu Markdown

| File                                                                                                                          | Ghi chú                                                    |
| ----------------------------------------------------------------------------------------------------------------------------- | ---------------------------------------------------------- |
| **File này**                                                                                                                  | Master: luồng bán, schema, legacy đã gỡ (3 env), audit gộp |
| [`WAREHOUSE_INDEX.md`](WAREHOUSE_INDEX.md)                                                                                    | Mục lục — trỏ file này                                     |
| [`UAT_CHECKLIST_MUA_BAN_KHO_E2E_VI.md`](UAT_CHECKLIST_MUA_BAN_KHO_E2E_VI.md)                                                  | Checklist UAT E2E canonical                                |
| [`ERP_SO_PO_DO_INVOICE_WAREHOUSE_QA_VERIFICATION_VI.md`](ERP_SO_PO_DO_INVOICE_WAREHOUSE_QA_VERIFICATION_VI.md)                | Bảng issue QA chi tiết                                     |
| [`QUY_TRINH_PO_DO_SO_INVOICE_WAREHOUSE_VI.md`](QUY_TRINH_PO_DO_SO_INVOICE_WAREHOUSE_VI.md)                                    | Hướng dẫn vận hành                                         |
| [`SALES_PURCHASE_FLOW.md`](SALES_PURCHASE_FLOW.md)                                                                            | EN, class-level                                            |
| [`SO_DO_PO_GRN_REFACTOR_VI.md`](../FUNC_IMPROVE/05_SO_DO_PO_GRN_REFACTOR_VI.md)                                                  | Lịch sử quyết định refactor                                |
| [`docs/PHAN_TICH_MODULE_WAREHOUSE_SO_PO_DO_INVOICE_GRN_VI.md`](../docs/PHAN_TICH_MODULE_WAREHOUSE_SO_PO_DO_INVOICE_GRN_VI.md) | Stub trỏ FUNC_LOGIC                                        |

---

## 9) Kết luận ngắn

1. **Luồng bán hiện tại** ghi trên **`sales_dos` / `sales_do_items`**; bảng `sales_shipments*` **đã không còn** trên các môi trường đã xác nhận.
2. **Luồng GRN** ghi trên **`grns` / `grn_items`**; bảng `delivery_orders*` **đã không còn** trên các môi trường đã xác nhận.
3. Cột `delivery_order_item_id` trên `stock_movements` / `invoice_items` vẫn có thể chứa **ID lịch sử** không còn resolve qua bảng đã xóa — xử lý UX / báo cáo theo mục 4.4.
4. **Môi trường / DB dump khác** (chưa chạy DROP): vẫn áp dụng checklist mục 4.2 trước khi DROP.

---

_Nếu `GrnRuntime` / `SalesDoRuntime` bỏ pin cứng và gắn lại `config`, cần sửa lại mục 1, 3, 5._
