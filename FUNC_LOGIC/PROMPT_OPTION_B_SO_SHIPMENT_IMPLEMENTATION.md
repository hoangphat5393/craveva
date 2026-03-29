# Prompt triển khai Phương án B (SO Shipment riêng) — dùng cho Agent mới

Bạn là Senior Laravel Architect + ERP Domain Engineer + QA Lead.

## Context bắt buộc đọc trước khi code

1. `FUNC_LOGIC/ERP_SO_PO_DO_INVOICE_WAREHOUSE_QA_VERIFICATION_VI.md`
2. `FUNC_LOGIC/QUY_TRINH_PO_DO_SO_INVOICE_WAREHOUSE_VI.md`
3. `PROJECT BIOMIXING/BIOMIXING_FLOW_CRACEVA_GAP.md`
4. `PROJECT BIOMIXING/BIOMIXING_PRODUCTION_DOMAIN_INTEGRATION.md`
5. `FUNC_LOGIC/SALES_PURCHASE_FLOW.md`
6. `app/Models/DeliveryOrder.php`
7. `Modules/Purchase/Observers/DeliveryOrderObserver.php`
8. `Modules/Warehouse/Services/InvoiceWarehouseStockService.php`

## Mục tiêu triển khai

Triển khai **Phương án B**: tạo mô hình chứng từ giao hàng bán riêng (sales shipment), tách khỏi `delivery_orders` hiện dùng cho PO inbound.

### Business intent

- Giữ nguyên luồng mua hiện tại: PO/DO inbound và observer inbound không bị phá.
- Có luồng bán chuẩn: `SO (Order) -> Sales Shipment (partial được) -> Invoice`.
- Stock outbound theo shipment (hoặc orchestration rõ với invoice), không trừ 2 lần.

## Tuyệt đối KHÔNG làm

- Không thêm `order_id` vào `delivery_orders` để reuse luồng inbound hiện có.
- Không sửa phá backward-compat flow PO/DO.
- Không dùng hack "if source == sales" trong observer inbound cũ mà không tách service rõ.

## Scope implementation (bắt buộc)

### 1) Database schema mới

Tạo migration cho các bảng mới (đặt tên thống nhất và dễ hiểu):

- `sales_shipments`
    - `id`, `company_id`, `order_id`, `warehouse_id`, `shipment_number`, `shipment_date`,
    - `status` (`draft|confirmed|shipped|delivered|cancelled`),
    - `outbound_stock_applied` (bool default false),
    - `notes`, `created_by`, `updated_by`, timestamps.
- `sales_shipment_items`
    - `id`, `sales_shipment_id`, `order_item_id`, `product_id`,
    - `quantity_ordered`, `quantity_shipped`, `unit_id`, `batch_number` nullable,
    - timestamps.
- Constraints/indexes:
    - FK chuẩn (`company_id`, `order_id`, `warehouse_id`, ...)
    - index theo (`company_id`, `status`, `shipment_date`)
    - unique `shipment_number` theo `company_id`.

### 2) Models + relationships

- Tạo model mới cho shipment header/items.
- Thêm relation vào `Order` (hasMany shipments).
- Không làm thay đổi semantics model `DeliveryOrder` cũ.

### 3) Service layer tách rõ domain

- Tạo service riêng: ví dụ `SalesShipmentStockService`.
- Chỉ service này được gọi `StockMovementService::recordOutbound` cho shipment.
- Idempotency: dùng `outbound_stock_applied` + transaction.
- Chặn xuất âm theo config warehouse hiện có (không bypass rule).

### 4) Workflow + validation

- Tạo API/controller cho create/update/confirm/ship/cancel shipment.
- Validation:
    - `quantity_shipped` không vượt `remaining qty` theo `order_items`.
    - Không cho ship nếu status không hợp lệ.
    - Không cho sửa line sau khi đã `shipped` trừ khi có action reverse chuẩn.
- Partial shipment:
    - Cho phép nhiều shipment trên một `order_id`.
    - Tính remaining qty chính xác.

### 5) Invoice orchestration (quan trọng)

Chọn 1 trong 2 chế độ và implement rõ bằng config:

- **Mode S (khuyến nghị cho Option B):** outbound theo shipment; invoice không trừ thêm (tránh double).
- **Mode I (legacy):** outbound theo invoice như hiện tại.

Yêu cầu:

- Có config rõ ràng để tránh double deduction.
- Nếu Mode S bật, cần bảo đảm đường invoice không trừ stock lần 2.
- Viết comment kỹ tại điểm quyết định luồng.

### 6) Permissions + UI cơ bản

- Thêm permission tối thiểu: view/create/update/ship/cancel sales shipment.
- Tạo màn list + create/edit/show ở mức MVP (Blade hiện có style thống nhất).
- Nút “Create Shipment from SO” tại màn order/show (nếu phù hợp).

### 7) QA test bắt buộc (automation + checklist)

Viết test (feature/integration) cover:

- Tạo SO 10 qty, shipment lần 1 = 4, lần 2 = 6, không cho lần 3 >0.
- Outbound stock movement tạo đúng kho/số lượng/reference.
- Cancel/reverse (nếu có) không làm lệch tồn.
- Không ảnh hưởng PO/DO inbound hiện hữu.
- Không double outbound khi có invoice ở mode shipment.

## Deliverables bắt buộc

1. Code + migration + tests pass.
2. `FUNC_LOGIC/SALES_SHIPMENT_OPTION_B_IMPLEMENTATION.md`:
    - kiến trúc,
    - bảng mới,
    - state machine,
    - cấu hình mode S/I,
    - checklist UAT.
3. `FUNC_LOGIC/ERP_SO_PO_DO_INVOICE_WAREHOUSE_QA_VERIFICATION_VI.md` cập nhật mục “đã triển khai Option B MVP”.
4. Build production pass.

## Doc cleanup (gộp mềm, không gãy link)

- Giữ làm canonical:
    - `FUNC_LOGIC/ERP_SO_PO_DO_INVOICE_WAREHOUSE_QA_VERIFICATION_VI.md`
    - `FUNC_LOGIC/QUY_TRINH_PO_DO_SO_INVOICE_WAREHOUSE_VI.md`
    - `PROJECT BIOMIXING/BIOMIXING_FLOW_CRACEVA_GAP.md`
- Nếu thấy file trùng nội dung:
    - đổi thành stub 3-6 dòng trỏ về canonical file.
    - không xóa file nếu đang được link từ index/README.

## Output format khi báo cáo

A. Kiến trúc đã chọn và lý do (1 trang)  
B. File changed list  
C. Migration summary  
D. Test results  
E. Known risks + next steps
