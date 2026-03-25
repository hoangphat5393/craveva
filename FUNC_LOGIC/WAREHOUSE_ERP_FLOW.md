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
