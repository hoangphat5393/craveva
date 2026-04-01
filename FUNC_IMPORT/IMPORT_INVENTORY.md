# File nguồn Maolin (đúng)

- **Đường dẫn:** `PROJECT MAOLIN New/Quote_inventory.csv`
- **Định dạng:** CSV phân tách bằng dấu phẩy; dòng đầu là tiêu đề song ngữ (ZH | EN).
- **Lưu ý:** Một số ô số có dấu phẩy nghìn và được bọc ngoặc kép (ví dụ `"1,220"`). Job import đã hỗ trợ `parseImportNumber` cho kiểu này.

# Cột trong file (22 cột, thứ tự theo header dòng 1)

| #   | Tiêu đề (ZH \| EN)                            | Gợi ý map sang import                                                                                                                         |
| --- | --------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------- |
| 1   | 品號 \| SKU                                   | `sku`                                                                                                                                         |
| 2   | 品名 \| Product Name                          | `product_name`                                                                                                                                |
| 3   | 規格 \| Specification                         | `specification` (core); đồng thời CF _Specification_ nếu có trong company                                                                     |
| 4   | 單位 \| unit                                  | `unit` (tạo/khớp UnitType)                                                                                                                    |
| 5   | 小單位 \| Small unit                          | CF `small_unit` → cột `field_{id}`                                                                                                            |
| 6   | 包裝單位 \| Packaging unit                    | CF `packaging_unit` → `field_{id}`                                                                                                            |
| 7   | 批號 \| Batch number                          | `batch_number`                                                                                                                                |
| 8   | 有效日期 \| Expiration Date                   | `expiration_date` (core). Dữ liệu có dạng YYYYMMDD hoặc cần parse tương thích Carbon / Excel date                                             |
| 9   | 製造日期 \| Manufacturing date                | `manufacturing_date`                                                                                                                          |
| 10  | 結案碼 \| Closing Code                        | CF `closing_code` → `field_{id}`                                                                                                              |
| 11  | 庫別 \| warehouse_code                        | `warehouse_code`                                                                                                                              |
| 12  | 庫別名稱 \| warehouse_name                    | `warehouse_name`                                                                                                                              |
| 13  | 期初庫存 \| Beginning Inventory               | CF `beginning_inventory` → `field_{id}`                                                                                                       |
| 14  | 本期入庫 \| Inbound                           | CF `inbound_quantity` → `field_{id}`                                                                                                          |
| 15  | 本期出庫 \| Outbound                          | CF `outbound_quantity` → `field_{id}`                                                                                                         |
| 16  | 期末庫存 \| Ending Inventory                  | `ending_inventory` (ưu tiên hơn `quantity` khi tính tồn)                                                                                      |
| 17  | 期初包裝庫存 \| Beginning Packaging Inventory | CF `beginning_package_inventory` → `field_{id}`                                                                                               |
| 18  | 本期包裝入庫 \| Packaging Inbound Quantity    | CF `packaging_inbound_quantity` → `field_{id}`                                                                                                |
| 19  | 本期包裝出庫 \| Packaging Outbound Quantity   | CF `packaging_outbound_quantity` → `field_{id}`                                                                                               |
| 20  | 期末包裝庫存 \| Ending Packaging Inventory    | **Chưa có** CF chuẩn trong migration gộp (`ending_package_inventory`). Muốn lưu: tạo Custom Field (nhóm Inventory) rồi map sang `field_{id}`. |
| 21  | 最近入庫日 \| Recent Inbound                  | CF `recent_inbound_date` → `field_{id}`                                                                                                       |
| 22  | 批號最近入庫日 \| Batch Recent Inbound        | CF `batch_recent_inbound_date` → `field_{id}`                                                                                                 |

# Trường import core (InventoryImport + ImportInventoryJob)

| Field import                        | Nguồn cột Quote_inventory | Ghi chú                                                       |
| ----------------------------------- | ------------------------- | ------------------------------------------------------------- |
| sku                                 | 1                         | Bắt buộc để khớp/tạo sản phẩm                                 |
| product_name                        | 2                         |                                                               |
| warehouse_code                      | 11                        | Khớp `warehouses.code` trước, sau đó mới `warehouse_name`     |
| warehouse_name                      | 12                        |                                                               |
| ending_inventory                    | 16                        | Ưu tiên cho `net_quantity` / điều chỉnh tồn                   |
| quantity                            | —                         | Chỉ dùng nếu không map `ending_inventory`                     |
| specification                       | 3                         | Có thể ghi vào mô tả sản phẩm nếu đang trống                  |
| batch_number                        | 7                         |                                                               |
| manufacturing_date                  | 9                         |                                                               |
| expiration_date                     | 8                         | Core `expiration_date` trên dòng tồn (không cần CF)           |
| reserved_quantity                   | —                         | Tùy file; map cột nếu có (core `reserved_quantity`)           |
| date, type, cost_price, description | —                         | File không có; job mặc định ngày = hôm nay, `type` = quantity |

Các cột chỉ nằm trong **Custom Field** (sau khi chạy migration / cấu hình company): đặt header template import trùng **nhãn đã dịch** (`__($customField->label)`) hoặc dùng cột `field_{id}` xuất từ màn hình export/template.

# Cột file không có field core tương ứng (chỉ CF hoặc cần thêm CF)

| Nội dung                                                                                                                         | Ghi chú                                                                                 |
| -------------------------------------------------------------------------------------------------------------------------------- | --------------------------------------------------------------------------------------- |
| 小單位, 包裝單位, 結案碼, kỳ đầu/cuối nhập xuất (trừ 期末庫存 đã map `ending_inventory`), đóng gói nhập xuất, ngày nhập gần nhất | Map sang CF đã seed (bảng dưới) qua `field_{id}` hoặc label khớp                        |
| 期末包裝庫存 (cột 20)                                                                                                            | Chưa có trong migration `setup_purchase_custom_fields_merged`; thêm CF thủ công nếu cần |

---

## Core đã bổ sung trong code (thay các CF đã xóa)

Chạy migration rồi dùng các cột sau — **không** cần CF tương ứng:

| Trước đây (CF)         | Core                                                                                                                                                                                                    |
| ---------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Expiry Date**        | `purchase_stock_adjustments.expiration_date`. Import: `expiration_date`.                                                                                                                                |
| **Ending Inventory**   | `purchase_stock_adjustments.net_quantity`. Import: `ending_inventory`. DataTable: cột **Ending Inventory**.                                                                                             |
| **Reserved Quantity**  | `purchase_stock_adjustments.reserved_quantity` (migration `2026_04_01_130000_add_reserved_quantity_to_purchase_stock_adjustments`). Import: `reserved_quantity`. Form điều chỉnh SL: ô **Reserved**.    |
| **Near-Expiry Status** | Không lưu DB: accessor `PurchaseStockAdjustment::near_expiry_status` (từ `expiration_date`; ngưỡng ngày: `config('purchase.inventory_near_expiry_days')`, mặc định 30). DataTable: cột **Near Expiry**. |

**Lưu ý:** `warehouse_product_batches.reserved_quantity` vẫn dùng cho luồng kho `products` + movement; cột mới trên `purchase_stock_adjustments` phục vụ **Purchase Inventory** và import.

### Snapshot ERP (Maolin) — tùy bước sau

- **Beginning / Inbound / Outbound / đóng gói** — có thể giữ CF hoặc thêm cột báo cáo core khi chuẩn hóa.

### CF có thể giữ thêm

- **Closing Code**, **Recent / Batch Recent Inbound**, **Location Code**, **Packaging / Small Unit** — xem nhu cầu từng company.
