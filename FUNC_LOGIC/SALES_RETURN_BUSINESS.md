# Trả hàng bán (Credit Note) và tồn kho

**Vị trí trong quy trình tổng:** [`SALES_BUSINESS.md`](SALES_BUSINESS.md) §4.5. Trả hàng mua (Vendor Credit → xuất kho): [`PURCHASE_RETURN_BUSINESS.md`](PURCHASE_RETURN_BUSINESS.md).

## Luồng

- Khi tạo dòng **Credit Note** (`credit_note_items`) loại `item` có `product_id`, số lượng &gt; 0, sản phẩm là hàng (không phải service): hệ thống ghi **nhập kho** qua `StockMovementService::recordInbound`.
- **Không** dùng `InvoiceWarehouseStockService::syncInvoiceStock` cho bước này; với `sales_outbound_mode = shipment`, invoice vốn không post outbound nên không phát sinh trừ kép.
- **Idempotency:** `stock_movements.idempotency_key` dạng `credit-note-inbound:{credit_note_id}:{credit_note_item_id}`.
- **Hủy / xóa credit note:** trước khi xóa header, `CreditNoteObserver::deleting` gọi `CreditNoteWarehouseStockService::reverseInboundForCreditNote` — **xuất kho** hoàn tác với key `credit-note-reversal-outbound:{credit_note_id}:{credit_note_item_id}`.

## Kho nhận hàng trả

1. `credit_note_items.warehouse_id` (nullable, migration Warehouse) nếu có và thuộc đúng công ty.
2. Nếu `sales_outbound_mode = shipment` và invoice có `order_id`: **Sales DO** đã `outbound_stock_applied` có dòng cùng `product_id`, lấy `warehouse_id` của DO mới nhất.
3. Ngược lại: cùng logic mặc định như invoice — `InvoiceWarehouseStockService::resolveDefaultWarehouseIdForInvoice`.

## QC / chặn nhập kho (TODO)

- Interface `Modules\Warehouse\Contracts\SalesReturnInboundGateInterface`: `allowInboundPosting(CreditNoteItem $item)`.
- Mặc định bind `AllowAllSalesReturnInboundGate` (luôn `true`). Có thể thay binding khi có workflow QC (accepted / reject / scrap).

## File chính

- `Modules/Warehouse/Services/CreditNoteWarehouseStockService.php`
- `app/Observers/CreditNoteItemObserver.php` (created)
- `app/Observers/CreditNoteObserver.php` (deleting — reversal)
- Migration: `Modules/Warehouse/Database/Migrations/2026_04_12_120000_add_warehouse_id_to_credit_note_items_table.php`
