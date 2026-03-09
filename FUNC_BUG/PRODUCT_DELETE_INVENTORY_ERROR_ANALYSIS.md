# Phân tích lỗi: "Cannot delete product(s) that still have inventory"

**Lỗi hiển thị (staging.craveva.com):**

```
Cannot delete product(s) that still have inventory. Please remove or adjust inventory first. purchase::app.product: Craveva Onsite Service / Day
```

**Ngữ cảnh:** User cố xóa sản phẩm "Craveva Onsite Service / Day" (SKU: QOQWF/QCQWF) trên trang Products (Operations > Products), nhưng bị chặn với thông báo trên.

---

## 1. Kết quả kiểm tra trên server staging

### 1.1. Dữ liệu đã truy vấn (2026-03-09)

**Bảng `products`:**
| id | name | sku | company_id |
|----|----------------------------|-------|------------|
| 1 | Craveva Onsite Service / Day | QCQWF | 20 |

**Bảng `purchase_stock_adjustments` (cho product_id = 1):**
| id | product_id | inventory_id | changed_value | net_quantity | reason_id | created_at |
|----|------------|--------------|---------------|--------------|-----------|---------------------|
| 4 | 1 | null | 0 | **2** | null | 2026-01-26 12:30:36 |

---

## 2. Nguyên nhân

### 2.1. Logic chặn xóa sản phẩm

Trong **`Modules/Purchase/Http/Controllers/PurchaseProductController.php`** (dòng 482–484):

```php
if (PurchaseStockAdjustment::where('product_id', $product->id)->exists()) {
    return Reply::error(__('purchase::messages.productHasInventory') . ' ' . __('purchase::app.product') . ': ' . $product->name);
}
```

- Điều kiện chặn: **chỉ cần có bất kỳ bản ghi nào** trong `purchase_stock_adjustments` với `product_id` tương ứng → không cho xóa.
- Không kiểm tra `net_quantity` (0 hay > 0), chỉ kiểm tra tồn tại record.

### 2.2. Kết quả trên staging

- Sản phẩm "Craveva Onsite Service / Day" (id = 1) có **1 bản ghi** trong `purchase_stock_adjustments`:
    - `id = 4`, `net_quantity = 2`, `inventory_id = null`
- Vì vậy `PurchaseStockAdjustment::where('product_id', 1)->exists()` trả về `true` → lỗi khi xóa sản phẩm là **đúng theo logic hiện tại**.

### 2.3. Vì sao giao diện hiển thị "Stock On Hand: --"?

- Cột "Stock On Hand" có thể lấy từ nguồn/aggregation khác với bảng `purchase_stock_adjustments`.
- Hoặc query/DataTable cho trang Products chưa join đúng với stock adjustments.
- Trên DB thì **có tồn tại inventory** (net_quantity = 2) cho sản phẩm này, nên logic backend là thống nhất.

---

## 3. Tại sao “đã xóa rồi mà vẫn sót” bản ghi này?

### 3.1. Bản ghi có `inventory_id = null` (orphan)

- Bản ghi `purchase_stock_adjustments` id=4 có **inventory_id = null** → không thuộc bất kỳ phiếu **Inventory** (purchase_inventory_adjustment) nào.
- Trong app, danh sách **Operations > Inventory** (Purchase Inventory) chỉ hiển thị các bản ghi **purchase_inventory_adjustment** và các stock **gắn với chúng** (qua `inventory_id`).
- Stock có **inventory_id = null** **không nằm trong danh sách Inventory** → user không thấy, không xóa được qua màn hình Inventory.

### 3.2. Nguồn tạo ra bản ghi “orphan”

Trong **`Modules/Purchase/Observers/PurchaseOrderObserver.php`**:

- Khi **Purchase Order** được đánh dấu **delivered** (`delivery_status === 'delivered'`), observer **tạo hoặc cập nhật** `PurchaseStockAdjustment` theo từng sản phẩm trong đơn:
    - Tìm: `PurchaseStockAdjustment::where('product_id', $item->product_id)->...->first()`.
    - Nếu chưa có thì **tạo mới** với `product_id`, `warehouse_id` (nếu có), `net_quantity` — **không gán `inventory_id`** (để null).
- Code tạo stock từ **Product** (PurchaseProductController) hoặc **Inventory** (PurchaseInventoryController) thì có gán `inventory_id`; riêng luồng **Purchase Order** không gán → tạo ra bản ghi **orphan** (inventory_id = null).

### 3.3. Khi user “xóa inventory” thì cái gì bị xóa?

Trong **`PurchaseInventoryController::deleteRecords()`**:

- Chỉ xóa các **PurchaseInventory** (purchase_inventory_adjustment) được chọn (theo `row_ids`).
- Với mỗi inventory: `$inventory->stocks()->each(... delete)` → chỉ xóa các **PurchaseStockAdjustment** có **inventory_id = id của inventory đó**.
- **PurchaseStockAdjustment** có **inventory_id = null** không thuộc bất kỳ inventory nào → **không bao giờ bị xóa** trong bước này.

Kết luận: User đã xóa các phiếu Inventory (và stock gắn với chúng), nhưng bản ghi stock **id=4** (inventory_id=null, tạo từ Purchase Order) **không nằm trong bất kỳ phiếu nào** nên không hiện trong danh sách, không bị xóa → “đã xóa rồi mà vẫn sót” đúng với bản ghi này.

### 3.4. Tóm tắt nguyên nhân sót

| Yếu tố                   | Giải thích                                                                                                                   |
| ------------------------ | ---------------------------------------------------------------------------------------------------------------------------- |
| **inventory_id = null**  | Bản ghi do luồng **Purchase Order (delivered)** tạo, không gắn với phiếu Inventory nào.                                      |
| **Xóa từ màn Inventory** | Chỉ xóa stock có `inventory_id` trùng với phiếu được chọn → stock orphan không bao giờ bị xóa.                               |
| **Không thấy trên UI**   | Danh sách Inventory chỉ hiển thị theo purchase_inventory_adjustment + stocks có inventory_id → stock orphan không xuất hiện. |

---

## 4. Flow xử lý xóa sản phẩm

```
User bấm xóa product "Craveva Onsite Service / Day"
    ↓
PurchaseProductController::destroy($id)
    ↓
PurchaseStockAdjustment::where('product_id', $product->id)->exists()
    ↓ true (có bản ghi id=4)
    ↓
return Reply::error('Cannot delete product(s) that still have inventory...')
    ↓
Không cho xóa, hiển thị lỗi
```

---

## 5. Theo logic: Purchase Order đã delivered thì nên làm gì?

### 5.1. Hai hướng hợp lý

| Hướng                    | Ý nghĩa                                                                                                    | Hành động                                                                                   |
| ------------------------ | ---------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------- |
| **Giữ sản phẩm**         | Đơn mua đã giao → sản phẩm đã “vào kho” (dù ghi nhận dạng orphan). Xóa sản phẩm sẽ mất dữ liệu lịch sử PO. | **Không xóa product.** Chỉ cần ngừng dùng (status Inactive) hoặc ẩn khỏi danh sách nếu cần. |
| **Bắt buộc xóa product** | Trường hợp đặc biệt (tạo nhầm, test, dọn data).                                                            | Xử lý stock **orphan** (tạo từ PO) trước, sau đó mới xóa product.                           |

### 5.2. Nếu chọn “bắt buộc xóa product”

- Stock tạo từ PO có **inventory_id = null** → **không nằm trong màn Inventory** → không xóa được qua UI.
- Có thể làm một trong hai:
    1. **Xóa trực tiếp trên DB** (chỉ khi chấp nhận mất audit trail cho dòng này):
        - `DELETE FROM purchase_stock_adjustments WHERE product_id = <product_id>;`
        - Sau đó vào app xóa product như bình thường.
    2. **Chờ/develop tính năng** hiển thị và xóa (hoặc điều chỉnh về 0) các stock **orphan** theo product (ví dụ: màn Product detail có danh sách “Stock từ PO” và nút xóa/adjust), rồi thực hiện qua UI.

### 5.3. Khuyến nghị dài hạn (khi sửa code)

- Khi PO **delivered**, observer nên **tạo hoặc gắn** `PurchaseStockAdjustment` với một **purchase_inventory_adjustment** (gán `inventory_id`), thay vì để null.
- Khi đó stock sẽ xuất hiện trong **Operations > Inventory**, user có thể xem, điều chỉnh hoặc xóa qua UI, và không còn tình trạng “đã xóa inventory rồi mà vẫn sót” do orphan.

---

## 6. Hướng xử lý (không sửa file, chỉ tham khảo)

| Hướng                                   | Mô tả                                                                                                                                                                |
| --------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **1. Xóa/adjust inventory trước**       | Vào **Operations > Inventory**, tìm các bản ghi liên quan product (chỉ thấy được stock có inventory_id). Stock **orphan** (từ PO) không hiện ở đây.                  |
| **2. Xóa trực tiếp trên DB (khẩn cấp)** | `DELETE FROM purchase_stock_adjustments WHERE product_id = <id>;` rồi mới xóa product trên app. Cần backup và chấp nhận mất audit trail cho dòng stock đó.           |
| **3. Sửa logic (nếu muốn nới lỏng)**    | Thay `exists()` bằng kiểm tra tổng quantity (ví dụ `SUM(net_quantity) > 0`) để cho phép xóa product khi “không còn tồn” theo logic nghiệp vụ (cần rõ ràng về audit). |

---

## 7. Tóm tắt

- **Nguyên nhân:** Sản phẩm có ít nhất 1 bản ghi trong `purchase_stock_adjustments` (id=4, net_quantity=2).
- **Logic hiện tại:** Chỉ cần có record trong `purchase_stock_adjustments` với `product_id` trùng → không cho xóa.
- **File liên quan:** `Modules/Purchase/Http/Controllers/PurchaseProductController.php`, `Modules/Purchase/Resources/lang/eng/messages.php` (productHasInventory).
- **Bảng DB:** `products` (id=1), `purchase_stock_adjustments` (id=4, product_id=1, net_quantity=2).
