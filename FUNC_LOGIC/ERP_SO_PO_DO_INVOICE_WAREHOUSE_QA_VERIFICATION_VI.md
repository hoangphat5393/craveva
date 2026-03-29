# ERP — Kiểm tra SO / PO / DO / Invoice / Multi-Warehouse / Inventory (codebase)

**Ngày:** 2026-03-29  
**Phương pháp:** Phân tích luồng thực tế trong code (Laravel + `Modules/Purchase`, `Modules/Warehouse`), **không** chạy UAT tự động trên DB staging.  
**Tham chiếu:** `FUNC_LOGIC/SALES_PURCHASE_FLOW.md`, `Modules/Warehouse/Config/config.php`, `InvoiceWarehouseStockService`, `PurchaseOrderObserver`, `DeliveryOrderObserver`.

---

## A. 🔴 Critical Functional Bugs (thiết kế / kỳ vọng nghiệp vụ vs code)

| Flow         | Step                               | Issue                                                                                                                                                                                                          | Example                                                                                                       | Impact                                                                             | Fix (hướng)                                                                                                                                         |
| ------------ | ---------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------- | ---------------------------------------------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Sale**     | SO → DO → xuất kho                 | **Không có “Delivery Order bán hàng” gắn `orders.id`.** `delivery_orders` chỉ nhận `purchase_order_id` (nhập mua).                                                                                             | User kỳ vọng: tạo DO từ SO rồi xuất kho.                                                                      | Luồng ERP “SO → DO sales → pick” **không tồn tại** trong model hiện tại.           | Đổi kỳ vọng: xuất kho theo **Invoice** (xem dòng dưới) **hoặc** phát triển entity mới (Sales DO) + FK + observer.                                   |
| **Sale**     | Confirm SO / xuất kho              | **Trừ tồn kho không gắn với trạng thái Order.** `Order` completed **không** gọi `StockMovementService` trong observer order cho outbound.                                                                      | `OrderController::changeStatus` đổi `orders.status` nhưng không trừ `warehouse_product_stocks`.               | Nếu chỉ “confirm SO” mà không lập hóa đơn (hoặc outbound tắt), **tồn không giảm**. | Bật `WAREHOUSE_SALES_OUTBOUND_ENABLED=true` và tạo **Invoice** (không draft); hoặc mở rộng nghiệp vụ reservation từ SO (chưa có trong code đã đọc). |
| **Sale**     | Invoice → kho                      | **Outbound sales tắt mặc định.** `config('warehouse.sales_outbound_enabled')` ← `WAREHOUSE_SALES_OUTBOUND_ENABLED` **default `false`** trong `Modules/Warehouse/Config/config.php`.                            | Staging chưa set `.env` → `InvoiceWarehouseStockService::isEnabled()` → **return false** → không outbound.    | **Hệ thống có module Warehouse nhưng bán hàng có thể không đụng tồn.**             | Set `WAREHOUSE_SALES_OUTBOUND_ENABLED=true` + module Warehouse enabled + user có `warehouse` trong `user_modules()`.                                |
| **Purchase** | PO delivered + DO received         | **Rủi ro nhập kho đơn.** `inbound_from_purchase_order_delivered` **default true**; `inbound_from_delivery_order_received` **default false**. Nếu bật **cả hai** cho cùng một lần nhận hàng → **double-count**. | Comment trong `DeliveryOrderObserver`: _“Double-count risk if both PO delivered and DO received post stock”_. | Tồn kho sai (x2).                                                                  | Chỉ bật **một** nguồn inbound (PO **hoặc** DO) theo quy trình; cấu hình `.env` rõ ràng + UAT.                                                       |
| **Sale**     | 1 SO → nhiều hóa đơn theo đợt giao | **`Order::invoice()` là `hasOne(Invoice)`** — `invoices.order_id` tối đa **một** invoice cho một SO.                                                                                                           | Giao 3 lần, 3 hóa đơn AR cùng SO: **không** được model hóa.                                                   | Thanh toán/lô hàng không tách theo đợt trên cùng SO.                               | Dùng **một** invoice + nhiều `Payment` (partial) **hoặc** hóa đơn không gắn `order_id` (mất liên kết SO).                                           |
| **Purchase** | Supplier invoice                   | **`PurchaseBill` không post stock** (`PurchaseBillObserver` không gọi `StockMovementService`).                                                                                                                 | Kỳ vọng “hóa đơn NCC = nhập kho”.                                                                             | Nhập kho đi theo **PO delivered** hoặc **DO received**, không theo bill.           | Phân tách nghiệp vụ: GRN = PO/DO; bill = kế toán AP.                                                                                                |

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

| Module                           | Issue                                                  | Why it breaks system                                                                      |
| -------------------------------- | ------------------------------------------------------ | ----------------------------------------------------------------------------------------- |
| **Sales DO**                     | Không có `DeliveryOrder` cho `order_id` (bán).         | Quy trình “SO → DO → xuất kho” không khớp code; chỉ có PO-linked DO.                      |
| **SO → tồn**                     | Trừ tồn gắn **Invoice**, không phải SO.                | Báo cáo “đã confirm SO” chưa chắc đã trừ kho.                                             |
| **InvoiceWarehouseStockService** | `isEnabled()` cần `user_modules()` chứa `'warehouse'`. | Ngữ cảnh không có user (job/console) có thể **không** sync (cần xác nhận khi chạy queue). |
| **Partial delivery**             | Không có split SO → nhiều invoice theo `order_id`.     | Giao một phần nhiều lần không map thành nhiều AR trên cùng SO.                            |

---

## D. 🧨 Inventory Issues

| Problem                   | Scenario                                                                                                                     | Fix (hướng)                                                                                          |
| ------------------------- | ---------------------------------------------------------------------------------------------------------------------------- | ---------------------------------------------------------------------------------------------------- |
| **Tồn không đổi khi bán** | Tạo SO + confirm, không tạo invoice **hoặc** invoice `draft` **hoặc** `WAREHOUSE_SALES_OUTBOUND_ENABLED=false`.              | Bật outbound + invoice finalized (non-draft) + kiểm tra `isEnabled()`.                               |
| **Âm tồn**                | `allow_negative_stock` trong `config/warehouse.php` (`WAREHOUSE_ALLOW_NEGATIVE_STOCK`, default false).                       | Nếu `false`, `StockMovementService` sẽ chặn; nếu **true**, cho phép âm — cần policy rõ.              |
| **Đa kho**                | Tồn theo `warehouse_product_stocks` / batch; transfer qua `WarehouseTransferController` / `StockMovementService` (transfer). | UAT: cùng SKU hai kho; **bán** vẫn outbound từ `default_warehouse_id` (client) trừ khi mở rộng code. |
| **Trộn nguồn nhập**       | PO delivered + DO received cùng bật.                                                                                         | Chỉ một đường nhập (xem A).                                                                          |

---

## E. 🛠 Suggested Fix Plan (ưu tiên)

| Step  | Action                                                                                                                                                                                                                      |
| ----- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **1** | **Xác nhận `.env` staging:** `WAREHOUSE_SALES_OUTBOUND_ENABLED`, `WAREHOUSE_INBOUND_FROM_PO_DELIVERED`, `WAREHOUSE_INBOUND_FROM_DO_RECEIVED`, `WAREHOUSE_ALLOW_NEGATIVE_STOCK`.                                             |
| **2** | **UAT theo đúng code:** Sale = SO → **Invoice** (không draft) → kiểm `stock_movements` (outbound `reference_type=Invoice`), `invoice_warehouse_stock_postings`. Purchase = PO delivered **hoặc** DO received **một** nhánh. |
| **3** | **Nếu nghiệp vụ bắt buộc “SO → DO sales”:** thiết kế bảng/FK mới + observer xuất kho hoặc reservation — **không** có trong code hiện tại.                                                                                   |
| **4** | **Ghi nhận giới hạn:** 1 SO → 1 Invoice (`order_id`); partial payment = nhiều `payments` trên cùng invoice.                                                                                                                 |
| **5** | **Kiểm tra** `client_details.default_warehouse_id` và sản phẩm `goods`/`service` trên từng dòng invoice.                                                                                                                    |

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

- **Hệ thống hiện tại không có “phiếu giao hàng bán (DO)” nối Sale Order.** `DeliveryOrder` trong code là **phiếu nhập/giao hàng mua** (liên quan `purchase_order_id`).
- **Xuất kho bán** (khi bật cấu hình) đi theo **Hóa đơn khách (`Invoice`)**, không theo trạng thái **Đơn hàng (`Order`)** đơn thuần.
- **Mặc định env có thể** tắt xuất kho bán — cần bật đúng biến khi kiểm tra.
- **Nhập kho mua:** PO “delivered” và/hoặc DO “received” — **không** bật cả hai cho cùng một quy trình nhận thực tế.
- **Hóa đơn nhà cung cấp (`PurchaseBill`)** không làm tồn kho; tồn theo PO/DO.
- Để **chứng minh end-to-end** trên staging, cần **chạy tay** các bước và kiểm tra bảng `stock_movements`, `warehouse_product_stocks`, `invoice_warehouse_stock_postings`, `invoices.order_id`, `purchase_orders.delivery_status`.

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

- Them config `warehouse.sales_outbound_mode` (`invoice|shipment`).
- Neu mode=`shipment`: invoice khong post outbound (tranh double).
- Neu mode=`invoice`: giu hanh vi legacy invoice outbound.

Pham vi Option B MVP khong sua pha flow PO/DO inbound cu.
