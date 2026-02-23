# Báo cáo Phân tích Import Inventory (Cập nhật)

**Nguồn dữ liệu:** `FUNC_CUS_RESQUEST/import_Inventory.xlsx`
**Chức năng đối chiếu:** Purchase > Add Inventory (Stock Adjustment)
**Trạng thái hệ thống:** Đã cập nhật System Fields & Custom Fields (2026-01-26)

## Cấu trúc File Import (Import File Structure)

Thứ tự các cột trong file Excel mẫu (Template) được sắp xếp như sau:

### 1. Các trường hệ thống (Standard Fields)

Các trường mặc định của hệ thống, xuất hiện đầu tiên trong file mẫu.

| STT | Trường Excel (Label)   | Tên Hệ thống (System Name) | Bắt buộc | Ghi chú                                                            |
| :-- | :--------------------- | :------------------------- | :------- | :----------------------------------------------------------------- |
| 1   | **SKU**                | `sku`                      | No       | Mã sản phẩm. Hệ thống map qua SKU để tìm Product ID.               |
| 2   | **Product Name**       | `product_name`             | Yes      | Tên sản phẩm. Dùng để map nếu không có SKU.                        |
| 3   | **Date**               | `date`                     | Yes      | Ngày chứng từ / Ngày ghi nhận tồn kho.                             |
| 4   | **Type**               | `type`                     | Yes      | Loại điều chỉnh: `quantity` (Số lượng) hoặc `value` (Giá trị).     |
| 5   | **Quantity**           | `quantity`                 | No       | Số lượng điều chỉnh (nếu Type = quantity).                         |
| 6   | **Price**              | `cost_price`               | No       | Đơn giá / Giá trị thay đổi (nếu Type = value).                     |
| 7   | **Description**        | `description`              | No       | Mô tả / Diễn giải chi tiết.                                        |
| 8   | **Unit Type**          | `unit`                     | No       | Đơn vị tính.                                                       |
| 9   | **Specification**      | `specification`            | No       | Quy cách sản phẩm. Tự động điền vào Product Description nếu trống. |
| 10  | **Manufacturing Date** | `manufacturing_date`       | No       | Ngày sản xuất (製造日期).                                          |
| 11  | **Expiration Date**    | `expiration_date`          | No       | Hạn sử dụng (有效日期).                                            |

### 2. Các trường tùy chỉnh (Custom Fields)

Các trường được cấu hình thêm, xuất hiện sau các trường hệ thống. Thứ tự sắp xếp dựa trên cấu hình Custom Field Group.

| STT (Custom) | Trường Excel (Label)                          | Tên trường (Name)             | Ghi chú                                         |
| :----------- | :-------------------------------------------- | :---------------------------- | :---------------------------------------------- |
| 1            | **庫別 (Warehouse Code)**                     | `warehouse_code`              | Mã kho lưu trữ.                                 |
| 2            | **庫別名稱 (Warehouse Name)**                 | `warehouse_name`              | Tên kho lưu trữ.                                |
| 3            | **批號 (Batch number)**                       | `batch_number`                | Số lô hàng.                                     |
| 4            | **結案碼 (Closing Code)**                     | `closing_code`                | Mã kết thúc (Y/N).                              |
| 5            | **包裝單位 (Packaging unit)**                 | `packaging_unit`              | Đơn vị đóng gói (ví dụ: Thùng, Kiện).           |
| 6            | **小單位 (Small unit)**                       | `small_unit`                  | Đơn vị nhỏ nhất (ví dụ: Cái, Hộp).              |
| 7            | **期初庫存 (Beginning Inventory)**            | `beginning_inventory`         | Số lượng tồn kho đầu kỳ.                        |
| 8            | **本期入庫 (Inbound)**                        | `inbound_quantity`            | Tổng nhập trong kỳ.                             |
| 9            | **本期出庫 (Outbound)**                       | `outbound_quantity`           | Tổng xuất trong kỳ.                             |
| 10           | **期末庫存 (Ending Inventory)**               | `ending_inventory`            | Số lượng tồn kho cuối kỳ.                       |
| 11           | **期初包裝庫存 (Beginning Pkg Inv)**          | `beginning_package_inventory` | Tồn kho đầu kỳ quy đổi theo đơn vị đóng gói.    |
| 12           | **本期包裝入庫 (Packaging Inbound)**          | `packaging_inbound_quantity`  | Nhập kho trong kỳ quy đổi theo đơn vị đóng gói. |
| 13           | **本期包裝出庫 (Packaging Outbound)**         | `packaging_outbound_quantity` | Xuất kho trong kỳ quy đổi theo đơn vị đóng gói. |
| 14           | **期末包裝庫存 (Ending Packaging Inventory)** | `ending_package_inventory`    | Tồn kho cuối kỳ quy đổi theo đơn vị đóng gói.   |
| 15           | **最近入庫日 (Recent Inbound)**               | `recent_inbound_date`         | Ngày nhập kho gần nhất của sản phẩm.            |
| 16           | **批號最近入庫日 (Batch Recent Inbound)**     | `batch_recent_inbound_date`   | Ngày nhập kho gần nhất của lô này.              |

---

## Giải thích thuật ngữ chi tiết (Detailed Explanation)

### 1. 結案碼 (Closing Code / Mã kết thúc)

- **Mục đích:** Dùng để đánh dấu trạng thái "đóng" của một lô hàng hoặc một dòng nhập kho.
- **Ý nghĩa:**
    - Thường có giá trị như "Y" (Yes - Đã đóng) hoặc "N" (No - Chưa đóng).
    - Khi mã này được bật ("Y"), hệ thống ERP thường sẽ không cho phép phát sinh thêm giao dịch xuất/nhập nào liên quan đến lô/dòng này nữa.
    - Giúp lọc dữ liệu tồn kho hiện hành (Active) và tồn kho lịch sử (Historical) để tối ưu hiệu suất báo cáo.

### 2. 期初包裝庫存 (Beginning Package Inventory / Tồn kho đầu kỳ theo bao bì)

- **Mục đích:** Theo dõi số lượng tồn kho đầu kỳ nhưng quy đổi theo đơn vị đóng gói (thùng, kiện, pallet) thay vì đơn vị cơ sở (cái, chiếc).
- **Ý nghĩa:**
    - Ví dụ: Tồn kho là 100 cái, quy cách đóng gói là 20 cái/thùng.
    - `Beginning Inventory` (Tồn đầu kỳ): 100.
    - `Beginning Package Inventory` (Tồn đầu kỳ bao bì): 5 (thùng).
    - Hữu ích cho bộ phận kho vận (Logistics) để ước lượng không gian lưu trữ và kế hoạch vận chuyển.

### 3. 批號最近入庫日 (Batch Recent Inbound Date / Ngày nhập kho gần nhất của lô)

- **Mục đích:** Ghi nhận ngày nhập kho gần nhất của một số lô (Batch Number) cụ thể.
- **Ý nghĩa:**
    - Khác với "Ngày nhập kho gần nhất" của sản phẩm (SKU), trường này gắn liền với **Số lô (Batch Number)**.
    - Giúp theo dõi tuổi thọ của từng lô hàng, phục vụ việc quản lý FIFO (Nhập trước xuất trước) hoặc FEFO (Hết hạn trước xuất trước).

### 4. 本期包裝入庫 (Packaging Inbound Quantity / Nhập kho theo bao bì)

- **Mục đích:** Ghi nhận số lượng nhập kho trong kỳ theo đơn vị đóng gói.
- **Ý nghĩa:**
    - Tương tự như `Inbound Quantity` nhưng tính theo đơn vị đóng gói (thùng/kiện).
    - Giúp kiểm soát luân chuyển hàng hóa theo quy cách vận chuyển.

### 5. 本期包裝出庫 (Packaging Outbound Quantity / Xuất kho theo bao bì)

- **Mục đích:** Ghi nhận số lượng xuất kho trong kỳ theo đơn vị đóng gói.
- **Ý nghĩa:**
    - Tương tự như `Outbound Quantity` nhưng tính theo đơn vị đóng gói (thùng/kiện).
    - Giúp kiểm soát luân chuyển hàng hóa theo quy cách vận chuyển.

### 7. 期末庫存 (Ending Inventory / Tồn kho cuối kỳ)

- **Mục đích:** Ghi nhận số lượng tồn kho cuối kỳ (theo đơn vị cơ sở).
- **Ý nghĩa:**
    - Là kết quả của: `Tồn đầu kỳ` + `Nhập` - `Xuất`.
    - Dùng để kiểm kê và đối chiếu thực tế.

### 6. 期末包裝庫存 (Ending Package Inventory / Tồn kho cuối kỳ theo bao bì)

- **Mục đích:** Theo dõi số lượng tồn kho cuối kỳ quy đổi theo đơn vị đóng gói.
- **Ý nghĩa:**
    - Công thức thường gặp: `Tồn đầu kỳ` + `Nhập` - `Xuất` = `Tồn cuối kỳ`.
    - Giúp đối chiếu nhanh số lượng thực tế tại kho (đếm theo thùng/kiện) với số liệu sổ sách.
