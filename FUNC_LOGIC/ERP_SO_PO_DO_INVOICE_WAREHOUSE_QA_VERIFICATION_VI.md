# ERP — Kiểm tra SO / PO / DO / Invoice / Multi-Warehouse / Inventory (codebase)

**Ngày cập nhật:** 2026-03-30  
**Phương pháp:** Phân tích luồng thực tế trong code (Laravel + `Modules/Purchase`, `Modules/Warehouse`), **không** chạy UAT tự động trên DB staging.  
**Chốt flow bán (mặc định code):** `WAREHOUSE_SALES_OUTBOUND_MODE=shipment` → xuất kho theo **Sales Shipment**; invoice không post outbound (trừ khi đặt mode `invoice`).  
**Tham chiếu:** `FUNC_LOGIC/SALES_PURCHASE_FLOW.md`, `Modules/Warehouse/Config/config.php`, `SalesShipmentStockService`, `InvoiceWarehouseStockService`, `PurchaseOrderObserver`, `DeliveryOrderObserver`.

---

## A. 🔴 Critical Functional Bugs (thiết kế / kỳ vọng nghiệp vụ vs code)

| Flow         | Step                              | Issue                                                                                                                                                                                 | Example                                                         | Impact                                     | Fix (hướng)                                                                                                             |
| ------------ | --------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | --------------------------------------------------------------- | ------------------------------------------ | ----------------------------------------------------------------------------------------------------------------------- |
| **Sale**     | SO → “DO bán” → xuất kho          | **`delivery_orders` không gắn `order_id`.** Luồng bán dùng **`sales_shipments`** (Option B), không tái dùng bảng DO mua.                                                              | User kỳ vọng tạo DO từ SO như PO.                               | SOP cũ “DO bán” không khớp DB.             | SOP: **SO → Sales Shipment → ship** (trừ tồn khi mode `shipment`) → **Invoice** (AR).                                   |
| **Sale**     | Confirm SO / xuất kho             | **Trừ tồn không gắn trạng thái SO.** Đổi `orders.status` không tự gọi outbound.                                                                                                       | Chỉ confirm SO, chưa shipment/invoice (tùy mode).               | Tồn có thể không đổi.                      | Mode `shipment`: **ship** shipment; mode `invoice`: invoice finalized; hoặc mở rộng reservation từ SO (chưa có).        |
| **Sale**     | Shipment / Invoice → kho          | **Điều phối bằng `WAREHOUSE_SALES_OUTBOUND_MODE`.** Mặc định code: **`shipment`** → `InvoiceWarehouseStockService` **không** post outbound; chỉ `SalesShipmentStockService` khi ship. | Team nghĩ “có invoice là trừ tồn” trong khi mode đang shipment. | Đối soát tồn sai kỳ vọng.                  | Chốt env + SOP; xem `QUY_TRINH_PO_DO_SO_INVOICE_WAREHOUSE_VI.md`. `WAREHOUSE_SALES_OUTBOUND_ENABLED` mặc định **true**. |
| **Purchase** | PO delivered + DO received        | **Rủi ro nhập đôi** nếu bật đồng thời `WAREHOUSE_INBOUND_FROM_PO_DELIVERED` và `WAREHOUSE_INBOUND_FROM_DO_RECEIVED` cho cùng lần nhận.                                                | Hai movement inbound cho cùng lượng.                            | Tồn x2.                                    | Một nguồn inbound canonical; đã có guard trong `DeliveryOrderObserver` khi PO đã delivered + PO inbound bật.            |
| **Sale**     | 1 SO → nhiều hóa đơn              | DB cho phép nhiều `invoices.order_id`; model có `Order::invoices()` và `Order::invoice()` = invoice **mới nhất** (`latestOfMany`).                                                    | UI/luồng tạo invoice lần 2, 3 từ SO có thể chưa đầy đủ.         | Thanh toán theo đợt chưa khép kín trên UI. | Hoàn thiện màn hình/luồng tạo nhiều invoice gắn SO + đối soát với shipment.                                             |
| **Purchase** | Supplier invoice (`PurchaseBill`) | **Bill NCC không post stock** — `PurchaseBillObserver` không gọi `StockMovementService`.                                                                                              | Kỳ vọng “nhập kho khi có hóa đơn NCC”.                          | Tồn vật lý ≠ thời điển ghi AP.             | **Đúng thiết kế hiện tại:** nhập kho theo **PO delivered** hoặc **DO received**; bill = AP. Chi tiết mục **L**.         |

---

## B. ⚠️ Data Inconsistencies (rủi ro dữ liệu)

| Tables / fields                       | Problem                                                                                                                                                                                          | Example                                                                                    | Fix                                                                                            |
| ------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ | ------------------------------------------------------------------------------------------ | ---------------------------------------------------------------------------------------------- |
| `orders` vs `invoices`                | Invoice từ SO copy từ `makeOrderInvoice` — nếu sửa SO sau khi đã có invoice, **đồng bộ tổng** phụ thuộc luồng chỉnh sửa; không có FK bắt buộc “số lượng SO line = invoice line” sau mỗi lần sửa. | Sửa `order_items.quantity` sau khi tạo invoice.                                            | Quy trình: khóa sửa SO sau invoice / tái tạo invoice / kiểm tra policy `OrderController` edit. |
| `invoice_items`                       | Outbound chỉ xử lý `type === 'item'` và `product_id` khác null; **service** bỏ qua.                                                                                                              | Dòng gắn dịch vụ không trừ kho (đúng) nhưng dòng thiếu `product_id` → **không** trừ stock. | Đảm bảo dòng hàng hóa có `product_id` khi cần trừ kho.                                         |
| `client_details.default_warehouse_id` | **Một** warehouse mặc định cho outbound invoice (resolve trong `InvoiceWarehouseStockService::resolveWarehouseId`). Không có “per-line warehouse” từ SO trong service đã đọc.                    | Bán từ kho A nhưng client default kho B.                                                   | Cập nhật default warehouse / mở rộng model (line-level warehouse) nếu nghiệp vụ yêu cầu.       |
| `purchase_orders` + `delivery_orders` | Cùng lần nhận: PO `delivery_status=delivered` và DO `status=received` + cả hai flag inbound → **double** movement.                                                                               | Hai bản ghi `stock_movements` cho cùng lượng.                                              | Một cấu hình inbound duy nhất (xem mục A).                                                     |

---

## C. ⚡ Missing or Broken Logic (so với mô tả ERP đầy đủ)

| Module                           | Issue                                                                                  | Why it breaks system                                                                      |
| -------------------------------- | -------------------------------------------------------------------------------------- | ----------------------------------------------------------------------------------------- |
| **Sales DO**                     | Không có `DeliveryOrder` cho `order_id` (bán).                                         | Quy trình “SO → DO → xuất kho” không khớp code; chỉ có PO-linked DO.                      |
| **SO → tồn**                     | Trừ tồn theo **mode**: `shipment` = shipment ship; `invoice` = invoice; không phải SO. | Báo cáo “đã confirm SO” chưa chắc đã trừ kho.                                             |
| **InvoiceWarehouseStockService** | `isEnabled()` cần `user_modules()` chứa `'warehouse'`.                                 | Ngữ cảnh không có user (job/console) có thể **không** sync (cần xác nhận khi chạy queue). |
| **Partial delivery**             | Không có split SO → nhiều invoice theo `order_id`.                                     | Giao một phần nhiều lần không map thành nhiều AR trên cùng SO.                            |

---

## D. 🧨 Inventory Issues

| Problem                   | Scenario                                                                                                                                                         | Fix (hướng)                                                                                          |
| ------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------- | ---------------------------------------------------------------------------------------------------- |
| **Tồn không đổi khi bán** | Mode `shipment`: chưa **ship** shipment **hoặc** outbound tắt. Mode `invoice`: chưa invoice finalized / draft **hoặc** `WAREHOUSE_SALES_OUTBOUND_ENABLED=false`. | Theo mode: ship shipment hoặc finalize invoice + kiểm tra `isEnabled()` và env.                      |
| **Âm tồn**                | `allow_negative_stock` trong `config/warehouse.php` (`WAREHOUSE_ALLOW_NEGATIVE_STOCK`, default false).                                                           | Nếu `false`, `StockMovementService` sẽ chặn; nếu **true**, cho phép âm — cần policy rõ.              |
| **Đa kho**                | Tồn theo `warehouse_product_stocks` / batch; transfer qua `WarehouseTransferController` / `StockMovementService` (transfer).                                     | UAT: cùng SKU hai kho; **bán** vẫn outbound từ `default_warehouse_id` (client) trừ khi mở rộng code. |
| **Trộn nguồn nhập**       | PO delivered + DO received cùng bật.                                                                                                                             | Chỉ một đường nhập (xem A).                                                                          |

---

## E. 🛠 Suggested Fix Plan (ưu tiên)

| Step  | Action                                                                                                                                                                                                                                                                                                                   |
| ----- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| **1** | **Xác nhận `.env` staging:** `WAREHOUSE_SALES_OUTBOUND_ENABLED`, `WAREHOUSE_INBOUND_FROM_PO_DELIVERED`, `WAREHOUSE_INBOUND_FROM_DO_RECEIVED`, `WAREHOUSE_ALLOW_NEGATIVE_STOCK`.                                                                                                                                          |
| **2** | **UAT theo đúng code:** Sale mặc định = SO → **Sales Shipment** → **ship** → kiểm `stock_movements` (outbound `reference_type=SalesShipment`). Nếu `WAREHOUSE_SALES_OUTBOUND_MODE=invoice`: SO → Invoice (không draft) → `invoice_warehouse_stock_postings`. Purchase = PO delivered **hoặc** DO received **một** nhánh. |
| **3** | **SO → phiếu giao bán:** đã có **Option B** (`sales_shipments`); không dùng `delivery_orders` cho bán.                                                                                                                                                                                                                   |
| **4** | **Nhiều invoice / SO:** DB + `Order::invoices()` hỗ trợ nhiều bản ghi; cần hoàn thiện luồng tạo invoice từ SO trên UI nếu nghiệp vụ bắt buộc.                                                                                                                                                                            |
| **5** | **Kiểm tra** `client_details.default_warehouse_id` và sản phẩm `goods`/`service` trên từng dòng invoice.                                                                                                                                                                                                                 |

---

## F. Bảng Delivery riêng cho SO vs thêm `order_id` vào `delivery_orders`?

**Bối cảnh:** Bảng `delivery_orders` hiện tại gắn **`purchase_order_id`**, observer nhập kho (`DeliveryOrderObserver`) và entity dòng (`DeliveryOrderItem`) nằm trong luồng **Purchase / nhập hàng**. Đây không phải “phiếu giao hàng bán”.

| Phương án                                                                                            | Ưu điểm                                                                                                                                                                                             | Rủi ro / nhược điểm                                                                                                                                                                                                                                   |
| ---------------------------------------------------------------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **A. Thêm `order_id` nullable** vào `delivery_orders` (cùng bảng với PO)                             | Một màn “Delivery” trong UI (nếu gom chung); ít bảng về mặt DB.                                                                                                                                     | Hai cha có thể cùng null / cùng set nếu không ràng buộc chặt; observer phải phân nhánh **nhập vs xuất**; dễ **double stock** hoặc **quên nhánh**; `inbound_stock_applied` không tên được cho xuất bán; module Purchase và App\Models lẫn trách nhiệm. |
| **B. Bảng mới cho bán** (ví dụ `order_shipments` / `sales_delivery_orders` + `order_shipment_items`) | **Tách nghiệp vụ:** GRN nhập (PO/DO hiện tại) vs **phiếu xuất/giao bán** (SO); FK rõ `order_id`; rule xuất kho (khi `shipped`? khi đối chiếu invoice?) viết riêng; ít phá luồng Purchase đang chạy. | Trùng khái niệm “delivery” ở UI (cần đặt tên rõ: “Phiếu nhận hàng mua” vs “Phiếu giao hàng bán”); thêm migration + màn hình + test.                                                                                                                   |
| **C. Không thêm DO bán** — chỉ dùng **Invoice + `InvoiceWarehouseStockService`** (như hiện tại)      | Không dev thêm; phù hợp nếu **xuất kho = lúc ghi nhận doanh thu / hóa đơn**.                                                                                                                        | Không có bước pick/pack/giao tách khỏi hóa đơn; giao nhiều lần khó map nếu sau này cần nhiều invoice trên một SO.                                                                                                                                     |

### Khuyến nghị (thực dụng)

1. **Ngắn hạn (ổn định vận hành):**
    - **Không** nên chỉ “thêm cột `order_id`” vào `delivery_orders` rồi tái dùng observer nhập — **rủi ro cao**, dễ lỗi tồn và khó bảo trì.
    - Nếu chấp nhận nghiệp vụ **xuất kho theo hóa đơn:** bật và UAT **`WAREHOUSE_SALES_OUTBOUND_ENABLED`**, chuẩn hóa **`client_details.default_warehouse_id`**, quy trình **SO → Invoice → trừ tồn**.

2. **Trung hạn (nếu bắt buộc có “phiếu giao / xuất kho” trước hoặc tách khỏi invoice):**
    - **Chọn phương án B — bảng mới** gắn `orders.id`, có `warehouse_id`, dòng theo `order_items` (số lượng giao từng đợt), cờ kiểu `outbound_stock_applied` (hoặc sync với `stock_movements` / posting riêng).
    - Quyết định rõ **khoảnh khắc trừ tồn:** ví dụ khi trạng thái `shipped` / `delivered`, và **có hay không** vẫn đồng bộ với Invoice để không trừ hai lần (một lớp điều phối duy nhất).

3. **Nếu vẫn muốn “một bảng delivery đa năng”:**
    - Cần thiết kế **polymorphic** (`source_type` + `source_id` hoặc hai FK nullable **với CHECK** chỉ một trong hai được set) + **hai service** inbound/outbound tách file — vẫn nặng hơn B và dễ sót case; **chỉ nên làm khi có lý do sản phẩm mạnh** (một API WMS chung chẳng hạn).

**Tóm một câu:** Để **hoàn thiện SO + PO đúng nghĩa ERP**, **PO/DO nhập** giữ như hiện tại; **bán** nên **bảng shipment / sales delivery riêng** (hoặc tạm thời chỉ Invoice), **không** chỉ thêm `order_id` vào bảng `delivery_orders` mà không refactor toàn bộ luồng và ràng buộc.

---

## G. Áp dụng cho **PROJECT MAOLIN New** — chọn phương án nào?

**Căn cứ tài liệu nội bộ:** `FUNC_LOGIC/MAOLIN_MASTER_GUIDE.md`, `FUNC_LOGIC/PROJECT_MAOLIN_NEW_FILES_ANALYSIS.md` (DigiWin là ERP chính Phase 1, sync file sáng/tối; master khách có **kho chỉ định** 指定庫別名稱; tồn theo **kho/lô** trong `Quote, unit price, inventory.xlsx`).

| Giai đoạn / mục tiêu                                                                                                                    | Phương án khuyến nghị                                                                                                                                                   | Lý do ngắn                                                                                                          |
| --------------------------------------------------------------------------------------------------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------- |
| **Phase 1 — DigiWin vẫn là nguồn fulfillment/kế toán chính**, Craveva nhận import + báo cáo + (tuỳ) bán lẻ trong app                    | **Ưu tiên C** (SO → **Invoice** → xuất kho khi bật `WAREHOUSE_SALES_OUTBOUND_ENABLED`; đồng thời map **`client_details.default_warehouse_id`** từ ý nghĩa **指定庫別**) | Tránh xây thêm chứng từ giao bán khi luồng thật vẫn chạy qua DigiWin; giảm rủi ro dev trùng với sync file.          |
| **Sau Phase 1 — Craveva là kênh B2B đặt hàng + kho phân phối**, cần **phiếu giao / giao nhiều đợt** tách khỏi hóa đơn, đối soát với WMS | **B** (bảng shipment bán riêng gắn `orders.id`)                                                                                                                         | Đúng mô hình ERP phân tách **nhập (DO hiện tại)** vs **xuất bán**; không nhét `order_id` vào `delivery_orders` mua. |
| **A** (thêm `order_id` vào `delivery_orders`)                                                                                           | **Không** khuyến nghị cho Miaolin                                                                                                                                       | Rủi ro nhầm nhập/xuất và conflict với PO/DO đã mô tả ở mục F.                                                       |

**Kết luận một câu cho dự án Miaolin:**

- **Hiện và giai đoạn sync DigiWin:** chốt **C** + cấu hình kho khách + UAT theo `QUY_TRINH_PO_DO_SO_INVOICE_WAREHOUSE_VI.md`.
- **Khi BRD/hợp đồng yêu cầu chứng từ “giao hàng bán” trong Craveva** (không chỉ hóa đơn): lên kế hoạch **B**, không dùng **A**.

---

## Ghi chú (tóm tắt tiếng Việt)

- **Không có “DO bán” trên bảng `delivery_orders`.** Đó là **phiếu nhập mua** (`purchase_order_id`). Bán dùng **`sales_shipments`** (Sales Shipment).
- **Xuất kho bán (mặc định code):** mode **`shipment`** → trừ tồn khi **ship** shipment, **không** trừ theo invoice. Mode **`invoice`** → trừ khi invoice (legacy). Trạng thái SO đơn thuần **không** tự trừ tồn.
- **`WAREHOUSE_SALES_OUTBOUND_ENABLED`** mặc định **true**; vẫn có thể tắt bằng env nếu tenant không dùng outbound tự động.
- **Nhập kho mua:** PO `delivered` và/hoặc DO inbound `received` — chỉ **một** nguồn trigger cho cùng lần nhận; đã có guard giảm double inbound.
- **"Bật cờ" là gì?** Là cấu hình trong file **`.env`**:
    - `WAREHOUSE_INBOUND_FROM_PO_DELIVERED=true|false`
    - `WAREHOUSE_INBOUND_FROM_DO_RECEIVED=true|false`
    - Sau khi đổi env cần `php artisan optimize:clear` (và restart worker nếu có queue).
- **Không bật đồng thời 2 cờ inbound trong production:**  
  Chọn **1 inbound canonical event** theo tenant để tránh lệch tồn:
    - **Mode A (legacy):** PO delivered post inbound (`PO=true`, `DO=false`)
    - **Mode B (ERP chuẩn GRN):** DO received post inbound (`PO=false`, `DO=true`)
- **Liên quan với outbound mode:**  
  `WAREHOUSE_SALES_OUTBOUND_MODE=shipment|invoice` chỉ điều phối **xuất kho bán hàng (SO)**, **không** điều khiển inbound PO/DO.
    - `shipment`: trừ tồn khi Sales Shipment ship
    - `invoice`: trừ tồn khi invoice finalize (legacy)
- **Hóa đơn NCC (`PurchaseBill`)** = chứng từ **kế toán AP**, **không** nhập kho — xem mục **L**.
- **End-to-end staging:** chạy tay và đối chiếu `stock_movements`, `warehouse_product_stocks`, `sales_shipments.outbound_stock_applied`, `invoice_warehouse_stock_postings` (nếu mode invoice), `purchase_orders.delivery_status`.

---

_Document này là ghi chú QA kiến trúc; không thay thế kiểm thử chức năng trên môi trường có dữ liệu thật._

---

## H. Da trien khai Option B MVP (Sales Shipment rieng)

Da bo sung Option B theo huong tach domain ban khoi inbound DO:

- Tao bang moi: `sales_shipments`, `sales_shipment_items`.
- Tao model moi va relation `Order::salesShipments()`.
- Tao service outbound rieng: `SalesShipmentStockService` (idempotent qua `outbound_stock_applied` + transaction lock).
- Tao workflow shipment MVP: list/create/edit/show + action `confirm|ship|cancel`.
- Cho phep partial shipment tren cung `order_id`, validate remaining qty theo `order_items`.
- Them nut tao shipment tu man `orders.show`.

Orchestration xuat kho:

- Them config `warehouse.sales_outbound_mode` (`invoice|shipment`). **Mac dinh code: `shipment`** (flow moi: xuat kho theo Sales Shipment).
- Neu mode=`shipment`: invoice khong post outbound (tranh double).
- Neu mode=`invoice`: giu hanh vi legacy invoice outbound.

Pham vi Option B MVP khong sua pha flow PO/DO inbound cu.

---

## I. Kiểm tra lại end-to-end theo checklist ERP (2026-03-29, sau Option B)

### A. 🔴 Critical Functional Bugs

- **Flow:** Sale  
  **Step:** SO -> DO -> xuất kho  
  **Issue:** `delivery_orders` vẫn chỉ gắn `purchase_order_id`; luồng bán dùng `sales_shipments`, không có DO bán trong bảng cũ.  
  **Example:** Không thể “Create Delivery Order from SO” theo nghĩa `delivery_orders`.  
  **Impact:** Checklist nghiệp vụ cũ kỳ vọng DO bán sẽ fail nếu không đổi sang Sales Shipment.  
  **Fix:** Chuẩn hóa SOP: SO -> Sales Shipment -> Invoice; đổi label nghiệp vụ từ DO bán sang Sales Shipment.

- **Flow:** Purchase  
  **Step:** PO delivered + DO received  
  **Issue:** Nếu bật đồng thời `WAREHOUSE_INBOUND_FROM_PO_DELIVERED=true` và `WAREHOUSE_INBOUND_FROM_DO_RECEIVED=true` có rủi ro nhập đôi.  
  **Example:** Hai movement inbound cho cùng lô nhận hàng.  
  **Impact:** Lệch tồn kho và sai báo cáo giá trị tồn.  
  **Fix:** Chốt duy nhất 1 nguồn inbound canonical theo tenant.

- **Flow:** Invoice  
  **Step:** Outbound mode orchestration  
  **Issue:** Cấu hình sai `WAREHOUSE_SALES_OUTBOUND_MODE` có thể dẫn tới kỳ vọng vận hành sai.  
  **Example:** Team vận hành nghĩ invoice trừ tồn trong khi mode đang `shipment`.  
  **Impact:** Sai quy trình thao tác và tranh cãi số tồn.  
  **Fix:** Chốt mode ở staging/prod, cập nhật SOP và đào tạo user.

### B. ⚠️ Data Inconsistencies

- **Tables:** `orders`, `invoices`  
  **Problem:** DB cho phép nhiều invoice cùng `order_id`; `Order::invoices()` đã có, nhưng luồng UI “tạo invoice lần 2+” từ SO có thể chưa đủ.  
  **Example:** Nhiều AR theo đợt giao trên cùng SO.  
  **Fix:** Hoàn thiện UI/controller tạo invoice gắn SO + rule đối soát với `sales_shipments`.

- **Tables:** `purchase_stock_adjustments` vs `stock_movements`  
  **Problem:** PO observer cập nhật cả tồn legacy (`purchase_stock_adjustments.net_quantity`) và tồn warehouse (`stock_movements`) khi delivered.  
  **Example:** Hai nguồn “tồn” cùng tồn tại, dễ lệch nếu báo cáo trộn sai nguồn.  
  **Fix:** Chốt rõ nguồn dữ liệu chuẩn cho báo cáo vận hành (warehouse tables).

- **Tables:** `sales_shipment_items`  
  **Problem:** `product_id` cho phép null theo schema; line null product sẽ không tạo outbound.  
  **Example:** Shipment line chỉ có mô tả nhưng thiếu product mapping.  
  **Fix:** Policy nhập liệu: line cần trừ tồn bắt buộc có `product_id`.

### C. ⚡ Missing or Broken Logic

- **Module:** Sales  
  **Issue:** Không có “DO bán” trên `delivery_orders`; đã thay bằng `sales_shipments`.  
  **Why it breaks system:** Nếu tài liệu/đào tạo vẫn dùng thuật ngữ DO bán sẽ gây nhầm luồng.

- **Module:** Purchase Bill  
  **Issue:** `PurchaseBillObserver` không post stock inbound (cố ý tách nghiệp vụ).  
  **Why it breaks system:** Nếu kỳ vọng “có bill NCC thì tồn tăng” sẽ lệch thực tế code — xem **mục L**.

- **Module:** Invoice warehouse hook  
  **Issue:** `InvoiceWarehouseStockService::isEnabled()` phụ thuộc `user_modules()` ngữ cảnh runtime.  
  **Why it breaks system:** Context không có user (job/console) có thể không post stock nếu gọi sai luồng.

### D. 🧨 Inventory Issues

- **Problem:** Double inbound risk  
  **Scenario:** PO delivered và DO received cùng bật.  
  **Fix:** Bật một cờ inbound duy nhất.

- **Problem:** Double outbound risk do sai mode nhận thức  
  **Scenario:** Mode `shipment` nhưng user vẫn cố đối chiếu tồn theo invoice outbound.  
  **Fix:** Khóa mode + phát hành SOP + checklist UAT bắt buộc.

- **Problem:** Xuất âm (nếu bật)  
  **Scenario:** `WAREHOUSE_ALLOW_NEGATIVE_STOCK=true`.  
  **Fix:** Giữ false ở môi trường vận hành chuẩn, chỉ bật khi có phê duyệt nghiệp vụ.

### E. 🛠 Suggested Fix Plan

- **Step 1:** Chốt config tenant (`WAREHOUSE_SALES_OUTBOUND_MODE`, inbound flags, negative stock), lưu trong runbook triển khai.
- **Step 2:** QA chạy checklist theo `FUNC_LOGIC/WAREHOUSE_RUNBOOK_AND_UPGRADE_PLAN_VI.md` và ký nhận kết quả từng TC.
- **Step 3:** Cập nhật tài liệu đào tạo user: thay toàn bộ “DO bán” thành “Sales Shipment” và hướng dẫn đối soát tồn theo mode đã chốt.

---

## J. Vòng test tiếp theo + cập nhật fix (2026-03-29)

_Kết quả chạy test tự động mới nhất (2026-03-30): **mục M**._

### Các fix đã áp dụng trong vòng này

- Thêm guard chống double inbound tại `DeliveryOrderObserver`:
    - Khi PO linked đã `delivery_status=delivered` và PO inbound đang bật, DO inbound sẽ skip để tránh nhập đôi.
- Siết validation `SalesShipmentController`:
    - Không cho `quantity_shipped > 0` nếu line không có `product_id`.
- Điều chỉnh `InvoiceWarehouseStockService::isEnabled()`:
    - Trong ngữ cảnh không có user (job/console), vẫn cho phép sync nếu module + config đã bật.
- **Chốt flow mới (staging, chưa production):** mặc định `warehouse.sales_outbound_mode` = **`shipment`** trong `Modules/Warehouse/Config/config.php`; invoice không post outbound trừ khi đặt env `WAREHOUSE_SALES_OUTBOUND_MODE=invoice`.

### Kết quả test (tham chiếu mới nhất)

Xem **mục M** — lần chạy `php artisan test` toàn bộ repo và mapping phạm vi SO/PO/DO/kho/invoice.

---

## K. Trạng thái fix cho nhóm Critical Bugs (mục A, dòng 8-19)

| Bug mục A                                                           | Trạng thái                                    | Ghi chú                                                                                                                                                   |
| ------------------------------------------------------------------- | --------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Sale: SO -> DO -> xuất kho (`delivery_orders` không gắn `order_id`) | **Đã xử lý theo Option B (thay thế)**         | Không sửa `delivery_orders`; đã triển khai `sales_shipments` cho luồng bán.                                                                               |
| Sale: Confirm SO không tự trừ tồn                                   | **Tạm bỏ qua theo quyết định nghiệp vụ**      | Outbound trigger theo `sales shipment` hoặc `invoice` tùy mode, không theo trạng thái SO.                                                                 |
| Sale: Outbound sales default false                                  | **Đã sửa**                                    | Đổi default config `sales_outbound_enabled` sang true (vẫn có thể override bằng env).                                                                     |
| Purchase: PO delivered + DO received gây double inbound             | **Đã giảm rủi ro bằng guard code**            | `DeliveryOrderObserver` đã chặn inbound DO khi PO linked đã delivered và PO inbound đang bật.                                                             |
| Sale: 1 SO -> nhiều invoice                                         | **Đã xử lý tương thích quan hệ (partial)**    | Đã thêm `Order::invoices()` (`hasMany`) và đổi `Order::invoice()` trả invoice mới nhất; luồng tạo nhiều invoice theo nghiệp vụ sẽ làm ở bước kế tiếp.     |
| Purchase: Supplier invoice không post stock                         | **Không coi là bug — đúng thiết kế hiện tại** | Nhập kho theo **PO delivered** / **DO inbound received**; `PurchaseBill` = AP. Nếu nghiệp vụ bắt buộc “bill → tồn” thì là **ép mở rộng mới** (mục **L**). |

---

## L. Hóa đơn nhà cung cấp (`PurchaseBill`) — vì sao “không post stock”?

### Đây có phải lỗi không?

**Không.** Trong codebase hiện tại, đây là **tách nghiệp vụ có chủ đích**:

| Chứng từ / sự kiện            | Vai trò trong hệ thống                                  | Tồn kho (`stock_movements` / `warehouse_product_stocks`)          |
| ----------------------------- | ------------------------------------------------------- | ----------------------------------------------------------------- |
| **PO delivered**              | Hoàn tất mua hàng (theo cấu hình)                       | Có thể **nhập kho** nếu bật `WAREHOUSE_INBOUND_FROM_PO_DELIVERED` |
| **DO inbound `received`**     | GRN / nhận hàng vào kho                                 | Có thể **nhập kho** nếu bật `WAREHOUSE_INBOUND_FROM_DO_RECEIVED`  |
| **`PurchaseBill` (bill NCC)** | Ghi nhận **công nợ / AP**, đối chiếu thanh toán với NCC | **Không** được nối với `StockMovementService` trong observer bill |

Kỳ vọng sai thường gặp: _“Kế toán đã nhập hóa đơn NCC thì kho phải có hàng.”_  
Trong ERP chuẩn, **hàng về kho** (GRN) và **hóa đơn NCC** có thể **lệch thời gian** (nhận trước, bill sau hoặc ngược lại). Code hiện tại chọn **GRN = PO/DO**, **bill = kế toán**.

### “Lỗi tiếp theo” là gì nếu muốn làm tiếp?

Không phải sửa bug, mà là **quyết định sản phẩm**:

1. **Giữ như hiện tại (khuyến nghị nếu chưa có BRD rõ):** đào tạo user — nhập kho khi **nhận hàng** (PO/DO), bill chỉ để **AP**.
2. **Mở rộng sau (nếu bắt buộc):** thiết kế **3-way match** hoặc rule rõ ràng: bill chỉ post stock khi đã có GRN và số lượng khớp, tránh nhập đôi với PO/DO — cần spec, migration/event và test riêng.

Tóm lại: mục **“Purchase: Supplier invoice không post stock”** trong bảng audit là **ghi nhận kỳ vọng vs thiết kế**, không phải defect bắt buộc phải sửa trừ khi PM chốt nghiệp vụ “bill kích hoạt tồn”.

---

## M. Chạy test tự động toàn bộ — SO / PO / DO / kho / invoice (2026-03-30)

### Lệnh và kết quả

- **Lệnh:** `php artisan test` (toàn bộ thư mục `tests/`).
- **Kết quả (cập nhật sau khi bổ sung `PurchaseInboundStockFlowTest`):** **46 passed**, **1 skipped**, **0 failed**, **402 assertions**, thời gian ~**53s** (môi trường dev cục bộ).

**Skipped (không phải lỗi ERP):** `Tests\Feature\LoginTest` — case _inactive user is logged out_ (ứng dụng hiện tại cho phép hoặc xử lý khác; test được đánh dấu skip).

**Không có test failed** trong lần chạy này.

### Test tự động trùng với nghiệp vụ SO · PO · DO · Inventory · Warehouse · Invoice

| Nhóm nghiệp vụ (ý nghĩa vận hành)                           | File test                                    | Ghi chú ngắn                                                                                                                                                                              |
| ----------------------------------------------------------- | -------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **SO + xuất bán (Sales Shipment, Option B)**                | `Tests\Feature\SalesShipmentOptionBTest`     | Outbound theo shipment, idempotent, over-shipment, reverse, `shouldPostOutboundFromInvoice` false khi mode shipment.                                                                      |
| **Invoice + warehouse outbound (Scope B)**                  | `Tests\Unit\InvoiceWarehouseStockScopeBTest` | Config outbound, `InvoiceWarehouseStockService`, draft/CN, seeding skip sync/reverse.                                                                                                     |
| **Order ↔ Invoice (nhiều hóa đơn / SO)**                    | `Tests\Unit\OrderInvoiceRelationTest`        | `Order::invoices()` + `invoice()` latest.                                                                                                                                                 |
| **DO nhập + PO (guard double inbound)**                     | `Tests\Unit\DeliveryOrderObserverGuardTest`  | Khi PO đã delivered + inbound PO bật, DO inbound không post trùng.                                                                                                                        |
| **DO nhập → tồn (happy path)**                              | `Tests\Feature\PurchaseInboundStockFlowTest` | `DeliveryOrderObserver` + `recordInboundBatch`: DO `received`, cờ `WAREHOUSE_INBOUND_FROM_DO_RECEIVED`, cập nhật `warehouse_product_stock` + `stock_movements` + `inbound_stock_applied`. |
| **PO delivered → tồn (đường `recordPurchaseOrderInbound`)** | `Tests\Feature\PurchaseInboundStockFlowTest` | Reflection gọi private method tương đương nhánh PO delivered + `WAREHOUSE_INBOUND_FROM_PO_DELIVERED`; kiểm tra bỏ qua khi flag false.                                                     |
| **Kho: movement, âm tồn, FEFO**                             | `Tests\Unit\StockMovementServiceTest`        | `StockMovementService` — chặn âm tồn mặc định, override, FEFO.                                                                                                                            |
| **Cấu hình warehouse phase 3**                              | `Tests\Unit\WarehousePhase3ConfigTest`       | Inbound flags default, `recordInboundBatch`, reservation service.                                                                                                                         |
| **Import master warehouse (chunk)**                         | `Tests\Feature\WarehouseImportChunkJobTest`  | Upsert warehouse theo company/code, skip dòng thiếu tên/mã.                                                                                                                               |

### Phần chưa có test HTTP/E2E tự động (vẫn cần UAT tay hoặc bổ sung sau)

Repo **chưa** có (hoặc chưa thấy trong `tests/`) các suite kiểu:

- Full **OrderController** / tạo SO / đổi trạng thái SO end-to-end qua HTTP.
- Full **Purchase Order** / **Delivery Order** qua **HTTP** (form submit, quyền user, `company()`): đã có test **observer + tồn** trong `PurchaseInboundStockFlowTest` (SQLite in-memory), chưa có test request qua controller.
- **InvoiceController** tạo/sửa invoice qua request thật + đối chiếu `stock_movements`.
- Module **Inventory** (màn hình tồn, điều chỉnh) nếu tách khác `StockMovementService`.

**Kết luận cho PM/QA:** lần chạy này **không phát hiện regression** trong phạm vi test đã có; **luồng ERP đầy đủ trên UI** vẫn nên chạy theo `FUNC_LOGIC/WAREHOUSE_RUNBOOK_AND_UPGRADE_PLAN_VI.md` và checklist PO/DO/inbound trong tài liệu quy trình.

---

## N. Recommended `.env` by tenant (copy/paste nhanh)

> Mục tiêu: chọn **1 inbound canonical event** + **1 outbound canonical event** để tránh double-count.

### 1) Khuyến nghị hiện tại (ERP flow mới)

- Purchase inbound theo DO `received`
- Sales outbound theo Sales Shipment `shipped`

```env
WAREHOUSE_SALES_OUTBOUND_ENABLED=true
WAREHOUSE_SALES_OUTBOUND_MODE=shipment
WAREHOUSE_INBOUND_FROM_PO_DELIVERED=false
WAREHOUSE_INBOUND_FROM_DO_RECEIVED=true
WAREHOUSE_ALLOW_NEGATIVE_STOCK=false
```

### 2) Tenant legacy (chưa chuyển DO inbound canonical)

- Purchase inbound theo PO `delivered`
- Sales outbound theo invoice (legacy)

```env
WAREHOUSE_SALES_OUTBOUND_ENABLED=true
WAREHOUSE_SALES_OUTBOUND_MODE=invoice
WAREHOUSE_INBOUND_FROM_PO_DELIVERED=true
WAREHOUSE_INBOUND_FROM_DO_RECEIVED=false
WAREHOUSE_ALLOW_NEGATIVE_STOCK=false
```

### 3) Cấu hình không khuyến nghị (chỉ để debug tạm thời)

```env
WAREHOUSE_INBOUND_FROM_PO_DELIVERED=true
WAREHOUSE_INBOUND_FROM_DO_RECEIVED=true
```

- Có guard giảm rủi ro cộng tồn đôi, nhưng **không nên dùng production**.
- Sau khi đổi `.env`: chạy `php artisan optimize:clear` và restart queue worker (nếu có).

---

## O. Backlog de xuat (luu de can nhac sau) — Chuan hoa ten nghiep vu: `SO -> DO`, `PO -> GRN`

**Nguon de xuat:** trao doi voi user ngay 2026-03-30.
**Tài liệu quyết định + tracker:** `FUNC_LOGIC/SO_DO_PO_GRN_REFACTOR_VI.md`.

### Muc tieu nghiep vu mong muon

- Ban hang: `SO -> DO -> Invoice` (de user van hanh de hieu va quen thuoc).
- Mua hang: `PO -> GRN -> Bill`.

### Cach lam khuyen nghi (an toan, it rui ro)

1. **Pha 1 (UI/SOP rename, khong doi logic/DB):**
    - Hien thi `Sales Shipment` duoi ten nghiep vu: **Sales DO** / **Delivery Note**.
    - Hien thi `Delivery Orders` (module Purchase) duoi ten nghiep vu: **GRN / Goods Receipt**.
    - Cap nhat menu, title trang, label form, tai lieu huong dan va UAT.
    - Giu nguyen route, table, service hien tai de tranh regression.

2. **Pha 2 (neu can refactor ky thuat sau):**
    - Doi ten technical artifact (route/key/view) theo DO/GRN that su.
    - Bo sung migration mapping/backward compatibility neu doi cau truc.
    - Yeu cau test regression full luong SO/PO/DO/Shipment/Invoice/Warehouse.

### Quy tac an toan khi trien khai de xuat nay

- Khong thay doi co che post kho:
    - outbound van theo `WAREHOUSE_SALES_OUTBOUND_MODE` (`shipment` hoac `invoice`);
    - inbound van chon 1 canonical (`PO delivered` hoac `DO received`), khong bat dong thoi 2 co.
- Uu tien pha 1 truoc de dat hieu qua van hanh nhanh, sau do moi danh gia pha 2.
