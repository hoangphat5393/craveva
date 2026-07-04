# Trả hàng mua (Vendor Credit) và tồn kho

**Vị trí trong quy trình tổng:** [`SALES_BUSINESS.md`](SALES_BUSINESS.md) §2.4.

## Luồng

- Khi tạo / cập nhật dòng **`purchase_vendor_items`** loại `item` có `product_id`, số lượng &gt; 0, sản phẩm là hàng (không phải service): hệ thống ghi **xuất kho** qua `StockMovementService::recordOutbound` (reference `PurchaseVendorCredit`).
- **Không** gắn vào `InvoiceWarehouseStockService::isEnabled` / `sales_outbound_enabled` — chỉ cần module **Warehouse** bật và user (nếu có) có quyền warehouse; tránh chặn nhầm tenant chỉ mua hàng.
- **Idempotency:** lần xuất đầu `vendor-credit-outbound:{credit_id}:{item_id}`; sau mỗi lần **hoàn tác** (nhập lại) và ghi xuất mới, khóa tăng dạng `:{n}` (xem `VendorCreditWarehouseStockService`).
- **Hủy / xóa Vendor Credit:** `PurchaseVendorCreditObserver::deleting` gọi `reverseAllOutboundForVendorCredit` — **nhập kho** hoàn tác từng dòng (khóa `vendor-credit-reversal-inbound:…`, có hậu tố khi nhiều vòng).
- **Xóa / sửa dòng:** `PurchaseVendorItemObserver` — `deleting` hoàn tác; `updated` gọi `resyncOutboundForVendorCreditItem` (hoàn tác theo snapshot giá trị cũ rồi xuất lại).

## Kho xuất trả NCC

1. `purchase_vendor_items.warehouse_id` (nullable, migration Warehouse) nếu có và thuộc đúng công ty.
2. `PurchaseVendorCredit` → `bills` → `order` (`PurchaseOrder`) → `warehouse_id`.
3. Không có: kho mặc định công ty (`Warehouse` `is_default` / `active`).

## File chính

- `Modules/Warehouse/Services/VendorCreditWarehouseStockService.php`
- `Modules/Purchase/Observers/PurchaseVendorItemObserver.php`
- `Modules/Purchase/Observers/PurchaseVendorCreditObserver.php` (`deleting`)
- Migration: `Modules/Warehouse/Database/Migrations/2026_04_12_140000_add_warehouse_id_to_purchase_vendor_items_table.php`
