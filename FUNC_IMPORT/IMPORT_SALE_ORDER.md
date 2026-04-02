# IMPORT SALE ORDER (Last year net sales.xlsx)

## 1) File nguồn và phạm vi

- Nguồn: `PROJECT MAOLIN New/Last year net sales.xlsx`
- File có nhiều sheet theo tháng (`2024-01`, `2024-02`, ..., `2026-02`)
- Luồng import SO mới đọc **tất cả sheet** trong workbook.

## 2) Mapping cột chuẩn

| Cột file                                          | Field import           | Bắt buộc | Ghi chú                                                    |
| ------------------------------------------------- | ---------------------- | -------- | ---------------------------------------------------------- |
| 出貨/銷退日(C10) \| Shipment/Return Date (C10)    | `shipment_return_date` | Yes      | Parse theo date Excel hoặc chuỗi `YYYY/MM/DD`.             |
| 客戶編號 \| Customer Number                       | `customer_number`      | Yes      | Match `client_details.client_code` trong company hiện tại. |
| 產品料號 \| Product part number                   | `product_part_number`  | Yes      | Match `products.sku` trong company hiện tại.               |
| 淨銷售量(交易) \| Net sales volume (transactions) | `net_sales_volume`     | Yes      | Hỗ trợ số âm/dương, dấu phẩy nghìn.                        |
| Net sales (local currency/excluding tax)          | `net_sales_amount`     | No       | Rỗng thì tính theo quantity \* unit_price suy ra.          |

## 3) Quy tắc nghiệp vụ import

### 3.1 Return (âm)

- Nếu `net_sales_volume < 0` hoặc `net_sales_amount < 0` thì coi là dòng trả hàng.
- Tạo SO với `status = refunded`.
- Lưu `quantity` và `amount` theo trị tuyệt đối để tương thích cấu trúc order hiện tại.

### 3.2 Dedupe và idempotent

- Mỗi dòng tạo hash:
    - `company_id|shipment_date|customer_code|sku|net_sales_volume_raw|net_sales_amount_raw`
- Hash lưu vào bảng `order_import_rows` (unique theo `company_id + source_hash`).
- Import lại file không tạo trùng SO cho dòng đã nhập.

### 3.3 Date/timezone

- Date được chuẩn hóa về `Y-m-d` khi lưu `orders.order_date`.
- Import không dùng giờ, nên không lệch timezone hiển thị theo công ty.

### 3.4 Numeric parse

- Hỗ trợ:
    - `1,220`
    - `-1,220`
    - rỗng -> 0 (riêng cột required mà rỗng thì skip/fail theo rule).

## 4) Điều kiện dữ liệu trước khi chạy

- Client phải có `client_code` đúng với `customer_number`.
- Product phải có `sku` đúng với `product_part_number`.
- Nếu thiếu mapping client/product thì job fail theo row và hiển thị ở import exception.

## 5) Checklist chạy import

1. Vào `Orders` -> `Import Excel`.
2. Upload `Last year net sales.xlsx`.
3. Bật `Contains headings`.
4. Match đúng 5 cột theo bảng mapping.
5. Submit và theo dõi progress.
6. Kiểm tra bảng lỗi row (nếu có).

## 6) Thiếu dữ liệu / đề xuất CF (KHÔNG tự tạo)

Hiện tại luồng này **không cần custom field bắt buộc** để import tối thiểu.

Các thông tin có thể cân nhắc bổ sung sau (nếu nghiệp vụ yêu cầu), nhưng **không tự tạo CF**:

1. `return_reason_code` (mã lý do trả hàng)
    - Lý do: phân tích nguyên nhân hoàn trả.
    - Ảnh hưởng: báo cáo chất lượng bán hàng.
    - Gợi ý key/label: `orders.returnReasonCode` / `Return Reason Code`.

2. `source_sheet_month` (tháng nguồn)
    - Lý do: truy vết dữ liệu theo sheet tháng.
    - Ảnh hưởng: đối soát khi một workbook có nhiều kỳ.
    - Gợi ý key/label: `orders.sourceSheetMonth` / `Source Sheet Month`.
