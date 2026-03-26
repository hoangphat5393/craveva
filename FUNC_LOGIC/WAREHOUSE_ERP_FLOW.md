# Luồng ERP kho (Purchase-centric)

Tài liệu ghi lại sơ đồ **luồng text (Purchase-centric)** làm chuẩn tham chiếu khi thiết kế UI, quyền và tầng service (ghi nhận tồn qua `StockMovementService`, không ghi thẳng batch/tồn từ controller).

## Sơ đồ text

```
Master data (Product, Client, Vendor)
        │
        ▼
    Warehouse (master kho)
        │
        ├──────────────────┬──────────────────┬──────────────────┐
        ▼                  ▼                  ▼                  ▼
  Purchase Order    Delivery Order      Inventory         (Sales / DO xuất — nếu bật)
        │                  │                  │
        └──────────────────┴──────────────────┘
                           │
                           ▼
              StockMovementService
         (inbound / outbound / transfer)
                           │
              ┌────────────┴────────────┐
              ▼                         ▼
   warehouse_product_batches    warehouse_product_stock
   (theo lô / FEFO)             (tổng theo kho × SP — legacy mirror)
```

## Ghi chú ngắn

- **Nguồn phát sinh tồn** (Purchase-centric): PO nhận hàng, DO nhập kho, Inventory điều chỉnh/nhập; mọi thay đổi số lượng nên đi qua **một** lớp ghi sổ (`StockMovementService`) để đồng bộ batch + tồn tổng + dòng `stock_movements`.
- **Đọc lịch sử**: UI “Lịch sử xuất nhập” đọc từ bảng `stock_movements` (read-only), lọc theo kho / loại / sản phẩm.
- **Mở rộng**: Luồng bán hàng / DO xuất kho nối vào nhánh outbound tương tự, vẫn qua cùng service để tránh lệch sổ.

## Ghi chú DB: Add Stock / Transfer Stock ghi vào bảng nào

### 1) Add Stock (Stock adjustment)

Khi thao tác từ màn `Add Stock` (action add/remove), service sẽ ghi theo thứ tự:

1. `warehouse_product_batches`
    - tăng/giảm số lượng theo lô (batch), theo FEFO khi outbound.
2. `warehouse_product_stock`
    - đồng bộ tồn tổng theo cặp `warehouse_id × product_id` (legacy mirror).
3. `stock_movements`
    - thêm dòng lịch sử inbound/outbound (append-only, phục vụ audit).

Lưu ý:

- `action = add` -> tạo movement `inbound`.
- `action = remove` -> tạo movement `outbound`.
- Không ghi đè dòng cũ trong `stock_movements`; luôn tạo dòng mới.

### 2) Transfer Stock

Khi thao tác `Transfer Stock`, service chạy trong **một transaction** và ghi:

1. `warehouse_product_batches` (kho nguồn)
    - giảm tồn theo batch (outbound).
2. `warehouse_product_batches` (kho đích)
    - tăng tồn theo batch (inbound).
3. `warehouse_product_stock`
    - sync lại tồn tổng cho cả kho nguồn và kho đích.
4. `stock_movements`
    - tạo **2 dòng**:
        - 1 dòng `outbound` cho kho nguồn
        - 1 dòng `inbound` cho kho đích

Lưu ý:

- Transfer không tạo dòng `transfer` riêng ở ledger hiện tại; ledger chuẩn là cặp outbound/inbound.
- Nếu một bước fail, transaction rollback toàn bộ để tránh lệch tồn.
