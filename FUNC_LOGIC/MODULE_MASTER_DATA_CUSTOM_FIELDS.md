# Product Master Data – Custom Fields (Product)

Tài liệu này mô tả các **custom field** được bổ sung cho **Product (Master Data)** nhằm đáp ứng thiết kế hệ thống cho F&B B2B. Các field này đều được implement dưới dạng **CustomField** gắn với model `App\Models\Product`.

## 1. Storage Condition (Điều kiện bảo quản)

-   **Tên field (label):** Storage Condition
-   **Tên kỹ thuật (name):** `storage_condition`
-   **Kiểu dữ liệu:** `select` – giá trị chọn sẵn: `Frozen`, `Chilled`, `Ambient`
-   **Mục đích:**
    -   Xác định điều kiện bảo quản chuẩn cho SKU (đông lạnh, mát, nhiệt độ phòng).
    -   Là base cho các bước xử lý kho (warehouse), vận chuyển (delivery) và kiểm soát chất lượng.
-   **Phạm vi sử dụng:**
    -   Hiển thị trên Product master để buyer / planner nắm được điều kiện bảo quản.
    -   Có thể dùng làm tiêu chí filter/report sau này (ví dụ: thống kê doanh số theo storage condition).

## 2. Certification (Chứng nhận)

-   **Tên field (label):** Certification
-   **Tên kỹ thuật (name):** `certification`
-   **Kiểu dữ liệu:** `text` (nhập tự do)
-   **Mục đích:**
    -   Lưu các chuẩn/chứng nhận liên quan đến sản phẩm, ví dụ: Halal, ISO, HACCP,…
    -   Hỗ trợ phòng QA/QC và Sales khi cần chứng minh sản phẩm đạt tiêu chuẩn nào cho khách hàng.
-   **Phạm vi sử dụng:**
    -   Hiển thị trên Product master, có thể show trên báo cáo hoặc export dữ liệu cho QA.

## 3. Batch / Lot Tracking Enabled (Kích hoạt quản lý lô/batch)

-   **Tên field (label):** Batch / Lot Tracking Enabled
-   **Tên kỹ thuật (name):** `batch_tracking_enabled`
-   **Kiểu dữ liệu:** `select` – giá trị: `yes`, `no`
-   **Mục đích:**
    -   Bật/tắt chế độ **quản lý lô/batch** cho SKU.
    -   Khi `yes` → mọi luồng nhập/xuất liên quan SKU này phải đi kèm thông tin batch/lot.
    -   Khi `no` → SKU vẫn là hàng hóa bình thường nhưng hệ thống không bắt buộc theo dõi batch chi tiết.
-   **Phạm vi sử dụng:**
    -   Là cờ cấu hình ở cấp Product, để Warehouse/Inventory module có thể quyết định yêu cầu batch trên các chứng từ (PO, GRN, Delivery…).

### Minh họa nhanh

Giả sử sản phẩm **Yoghurt A**:

```text
Ngày 01/01  Nhập 100 hộp  → Batch #A (EXP 31/03)
Ngày 01/02  Nhập  80 hộp  → Batch #B (EXP 30/04)
```

-   Nếu `batch_tracking_enabled = yes`:
    -   Hệ thống lưu riêng:
        -   Batch #A: 100 hộp (EXP 31/03)
        -   Batch #B: 80 hộp (EXP 30/04)
    -   Có thể biết tồn theo từng lô, batch nào bán cho khách nào, hỗ trợ recall.
-   Nếu `batch_tracking_enabled = no`:
    -   Hệ thống chỉ biết: "Yoghurt A còn 180 hộp" → không phân biệt A hay B.

## 4. FEFO / FIFO Rule (Quy tắc xuất kho mặc định)

-   **Tên field (label):** FEFO / FIFO Rule
-   **Tên kỹ thuật (name):** `inventory_issue_rule`
-   **Kiểu dữ liệu:** `select` – giá trị: `FIFO`, `FEFO`
-   **Mục đích:**
    -   Định nghĩa **quy tắc xuất kho mặc định** cho từng SKU:
        -   **FIFO (First In First Out):** ưu tiên xuất các lô nhập trước.
        -   **FEFO (First Expired First Out):** ưu tiên xuất các lô **sắp hết hạn trước**, phù hợp ngành F&B.
    -   Cho phép một số sản phẩm dùng FIFO (không quá nhạy với expiry), sản phẩm khác dùng FEFO (hạn dùng quan trọng).
-   **Phạm vi sử dụng:**
    -   Warehouse / Inventory module dùng field này làm rule khi chọn lô xuất.
    -   Pricing module có thể tham chiếu rule này khi xây near-expiry pricing logic.

### Minh họa FIFO vs FEFO (với Batch Tracking = yes)

Vẫn với ví dụ **Yoghurt A**, nơi **Batch Tracking = yes**:

```text
Batch #A: nhập 01/01, EXP 31/03,   100 hộp
Batch #B: nhập 15/01, EXP 28/02,   100 hộp

Ngày 10/02: cần xuất 50 hộp
```

-   Nếu `inventory_issue_rule = FIFO` (First In First Out):
    -   So sánh **ngày nhập** → Batch #A cũ hơn → xuất 50 hộp từ Batch #A.
-   Nếu `inventory_issue_rule = FEFO` (First Expired First Out):
    -   So sánh **ngày hết hạn** → Batch #B hết hạn sớm hơn → xuất 50 hộp từ Batch #B.

Như vậy:

-   FIFO ưu tiên luân chuyển theo **thứ tự nhập kho**.
-   FEFO ưu tiên lô **gần hết hạn** trước, phù hợp ngành F&B để giảm hàng hủy.

## 5. Near-Expiry Days Threshold (ngưỡng số ngày trước hết hạn - cận date)

-   **Tên field (label):** Near-Expiry Days Threshold
-   **Tên kỹ thuật (name):** `near_expiry_days_threshold`
-   **Kiểu dữ liệu:** `number` (số ngày)
-   **Mục đích:**
    -   Xác định **ngưỡng số ngày** trước hạn dùng để hệ thống coi là “cận date/near-expiry”.
    -   Ví dụ: đặt `30` nghĩa là nếu lô hàng còn ≤ 30 ngày trước hạn thì được đánh dấu near-expiry.
-   **Phạm vi sử dụng:**
    -   Warehouse module dùng để flag các lô near-expiry.
    -   Pricing module có thể dùng để auto áp dụng chính sách giảm giá near-expiry.

### Minh họa Near-Expiry Days Threshold (với Batch Tracking = yes)

Ví dụ 1 (ngưỡng 30 ngày):

-   Lô sữa: EXP = 31/03/2026
-   `near_expiry_days_threshold = 30`

```text
Ngày 28/02/2026  → còn 31 ngày  → CHƯA near-expiry
Ngày 01/03/2026  → còn 30 ngày  → BẮT ĐẦU near-expiry
Từ 01/03 → 31/03 → lô này được coi là near-expiry
```

Ví dụ 2 (shelf life rất ngắn, ngưỡng 2 ngày):

-   Sữa tươi pasteurised:
    -   `shelf_life_days = 7`
    -   `near_expiry_days_threshold = 2`
-   Lô cụ thể: MFG = 01/03 → EXP = 08/03

```text
Ngày 05/03  → còn 3 ngày  → CHƯA near-expiry
Ngày 06/03  → còn 2 ngày  → BẮT ĐẦU near-expiry
Ngày 07/03  → còn 1 ngày  → near-expiry (ưu tiên bán/discount)
Sau 08/03  → quá hạn theo policy công ty
```

## 6. Near-Expiry Discount Eligible (Có được áp dụng chiết khấu near-expiry hay không)

-   **Tên field (label):** Near-Expiry Discount Eligible
-   **Tên kỹ thuật (name):** `near_expiry_discount_eligible`
-   **Kiểu dữ liệu:** `select` – giá trị: `yes`, `no`
-   **Mục đích:**
    -   Cho biết SKU này **có được phép** áp dụng chiết khấu near-expiry hay không.
    -   Một số sản phẩm có thể không được phép discount theo chính sách công ty hoặc nhà cung cấp.
-   **Phạm vi sử dụng:**
    -   Pricing module sử dụng field này để quyết định có build rule giảm giá cho lô near-expiry của SKU.

## 7. ERP SKU Mapping (Map mã SKU giữa Craveva ERP và hệ thống ERP hiện có)

-   **Tên field (label):** ERP SKU Mapping
-   **Tên kỹ thuật (name):** `erp_sku_mapping`
-   **Kiểu dữ liệu:** `text`
-   **Mục đích:**
    -   Lưu mapping SKU tương ứng ở hệ thống **ERP hiện có** (nếu ERP dùng mã khác với mã trong Craveva ERP).
    -   Giúp các integration job (import/export) đối chiếu chính xác sản phẩm giữa 2 hệ thống.
-   **Phạm vi sử dụng:**
    -   Integration layer (ETL/API) dùng field này để map Product → ERP SKU.
    -   Hạn chế hard-code mapping ở code, tất cả mapping tập trung ở Product master.

### Minh họa ERP SKU Mapping

Giả sử sản phẩm **Heineken Lager 330ml**:

-   Trong hệ thống này (Craveva):
    -   `sku` (mã nội bộ) = `HN-330`
-   Trong hệ thống ERP (ví dụ SAP):
    -   Mã sản phẩm = `P00012345`

Khi đó:

-   **ERP SKU Mapping = `P00012345`**

Ý nghĩa:

-   Khi gửi đơn hàng từ Craveva sang ERP, hệ thống sử dụng `erp_sku_mapping` (`P00012345`) để ERP hiểu đúng sản phẩm.
-   Nếu khách đổi mã bên ERP, chỉ cần sửa ERP SKU Mapping ở Product, không cần đổi code.

## 8. WMS SKU Mapping (Map mã SKU giữa Craveva ERP và hệ thống WMS)

-   **Tên field (label):** WMS SKU Mapping
-   **Tên kỹ thuật (name):** `wms_sku_mapping`
-   **Kiểu dữ liệu:** `text`
-   **Mục đích:**
    -   Lưu mapping SKU với hệ thống **Warehouse Management System (WMS)** nếu WMS dùng mã khác.
    -   Tách riêng với ERP mapping để hỗ trợ trường hợp ERP và WMS là hai hệ thống khác nhau.
-   **Phạm vi sử dụng:**
    -   Integration giữa Craveva ERP và WMS (nhập/xuất kho, stock sync…).

### Minh họa WMS SKU Mapping

Tiếp tục với sản phẩm **Heineken Lager 330ml**:

-   Craveva: `sku` = `HN-330`
-   ERP: `erp_sku_mapping` = `P00012345`
-   WMS (hệ thống kho chuyên dụng): dùng mã `BEER-0001`

Khi đó:

-   **WMS SKU Mapping = `BEER-0001`**

Luồng sử dụng:

-   Khi đẩy phiếu xuất kho sang WMS, hệ thống dùng `wms_sku_mapping` (`BEER-0001`) để WMS nhận dạng đúng sản phẩm.
-   Khi nhận báo cáo tồn kho từ WMS với mã `BEER-0001`, integration dùng trường `wms_sku_mapping` để map ngược lại vào Product `HN-330` trong Craveva.

Như vậy, `sku` là mã chuẩn nội bộ, còn `erp_sku_mapping` và `wms_sku_mapping` đóng vai trò **từ điển mã** để nói chuyện đúng với từng hệ thống bên ngoài.

## 9. Brand (Nhãn hiệu, thương hiệu)

-   **Tên field (label):** Brand
-   **Tên kỹ thuật (name):** `brand`
-   **Kiểu dữ liệu:** `text`
-   **Mục đích:**
    -   Lưu nhãn hiệu/brand của sản phẩm (ví dụ: Coca-Cola, Pepsi, Heineken…).
    -   Phục vụ nhu cầu phân tích doanh số, tồn kho theo brand.
-   **Phạm vi sử dụng:**
    -   Lọc, group trong report (Sales, Inventory) theo brand.
    -   Là một reporting attribute nên không tác động trực tiếp tới logic hạch toán.

## 10. Shelf Life (Days) (Tuổi thọ tiêu chuẩn, tính theo ngày)

-   **Tên field (label):** Shelf Life (Days)
-   **Tên kỹ thuật (name):** `shelf_life_days`
-   **Kiểu dữ liệu:** `number` (số ngày)
-   **Mục đích:**
    -   Lưu **tuổi thọ tiêu chuẩn** của sản phẩm tính theo ngày kể từ ngày sản xuất.
    -   Là tham số quan trọng cho các rule về expiry, near-expiry và lập kế hoạch tồn kho.
    -   Có thể dùng shelf life để hiểu sản phẩm “nhạy cảm” mức nào: 7 ngày = cực kỳ nhạy, cần ưu tiên FEFO, near-expiry alert sớm.
-   **Phạm vi sử dụng:**
    -   Có thể dùng để:
        -   Tự động gợi ý hạn dùng khi nhập kho nếu biết ngày sản xuất.
        -   Kết hợp với Near-Expiry Days Threshold để tính toán cảnh báo sớm.

### Minh họa Shelf Life (Days)

Ví dụ 1 – Sữa tươi (shelf life ngắn):

-   SKU: Sữa tươi tiệt trùng 1L
-   Nhà sản xuất quy định: dùng trong 7 ngày kể từ ngày sản xuất.
-   Cấu hình: `shelf_life_days = 7`

Lô cụ thể:

-   Lô 1: MFG = 01/03 → hệ thống có thể suy ra EXP = 08/03
-   Lô 2: MFG = 05/03 → EXP = 12/03

Ví dụ 2 – Bia (shelf life dài):

-   SKU: Heineken Lager 330ml
-   Nhà sản xuất: hạn dùng 12 tháng.
-   Cấu hình: `shelf_life_days = 365`

Nếu MFG = 01/01/2026 thì EXP chuẩn ≈ 01/01/2027.

Khi kết hợp với `near_expiry_days_threshold` (ví dụ 30 ngày), ta có thể tính chính xác thời điểm lô chuyển sang trạng thái near-expiry để cảnh báo hoặc áp dụng discount.

---

## Tóm tắt mapping với system-design

-   **Batch / Lot Tracking (enabled flag)** → `batch_tracking_enabled`
-   **FEFO / FIFO rule (default rule per product)** → `inventory_issue_rule`
-   **Near-Expiry rules (days threshold, discount eligibility)** → `near_expiry_days_threshold`, `near_expiry_discount_eligible`
-   **Integration Touchpoints (SKU mapping to ERP, WMS)** → `erp_sku_mapping`, `wms_sku_mapping`
-   **Reporting attributes (category, brand, shelf life)** → `category_id` / `sub_category_id` (core), `brand`, `shelf_life_days` (custom field)

Các field này đều là **metadata ở cấp Product (Master Data)**, không dùng để lưu quantity hay warehouse-specific availability; phần số lượng và tồn kho vẫn được quản lý ở các module Inventory/Warehouse riêng theo đúng design ban đầu.
