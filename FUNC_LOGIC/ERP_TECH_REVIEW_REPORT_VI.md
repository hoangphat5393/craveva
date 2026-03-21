# Báo cáo Review Kỹ thuật (VI) — ERP Laravel Core Modules

**Phạm vi đã rà soát:** Product, Inventory, Client, Order và module Purchase/Warehouse liên quan.  
**Mục tiêu:** Bug, hiệu năng, DB usage, kiến trúc, bảo mật.

## Trạng thái cập nhật sau khi fix

- **Đã fix** bug precedence permission tại `ProjectTemplateMilestoneController::create()`.
- **Đã fix** null property + bổ sung validation status tại `PurchaseInventoryController::changeStatus()`.
- **Đã fix** thiếu tenant-scope trong `ImportInventoryJob` (lookup product theo `company_id`).
- **Đã fix** transaction safety tại `PurchaseInventoryController::store()` (rollback khi lỗi nghiệp vụ trong loop).
- **Đã fix** thiếu authorization + validation ở `PurchaseOrderController::changeStatus()` (dùng `ChangeStatusRequest` mới).
- **Đã fix** null-safe permission check ở `ClientController::edit()`.

---

## A. Critical bugs (must fix)

### 1) Null pointer / undefined property trong đổi trạng thái inventory

- **File:** `Modules/Purchase/Http/Controllers/PurchaseInventoryController.php`
- **Vị trí:** `changeStatus()` — dòng `461`
- **Chi tiết:**
    - Code đang check quyền bằng `$this->product->added_by` nhưng trong method không có `$this->product` được set.
    - Rủi ro: lỗi runtime (`Undefined property`) hoặc truy cập null.
- **Mức độ:** **Critical**
- **Fix gợi ý:** dùng chính `$inventory = PurchaseInventory::findOrFail($request->id)` trước khi check quyền, và check theo `$inventory->added_by`.

### 2) Bug logic permission do sai toán tử phủ định

- **File:** `app/Http/Controllers/ProjectTemplateMilestoneController.php`
- **Vị trí:** dòng `39`
- **Code hiện tại:** `abort_403(! $addProjectMilestonePermission == 'all');`
- **Chi tiết:**
    - Biểu thức bị precedence sai (`!` áp vào biến trước khi so sánh), dẫn tới check quyền không đúng kỳ vọng.
- **Mức độ:** **Critical**
- **Fix gợi ý:** đổi thành `abort_403($addProjectMilestonePermission !== 'all');`

### 3) Vi phạm multi-tenant data isolation trong inventory import

- **File:** `Modules/Purchase/Jobs/ImportInventoryJob.php`
- **Vị trí:** dòng `67`, `75`
- **Chi tiết:**
    - Lookup product không filter theo `company_id`:
        - `PurchaseProduct::where('sku', $sku)->first();`
        - `PurchaseProduct::where('name', $productName)->first();`
    - Có thể match nhầm product của công ty khác khi dữ liệu multi-tenant.
- **Mức độ:** **Critical**
- **Fix gợi ý:** bắt buộc thêm `->where('company_id', $this->company->id)` cho mọi lookup product.

### 4) Transaction mở nhưng return sớm không rollback

- **File:** `Modules/Purchase/Http/Controllers/PurchaseInventoryController.php`
- **Vị trí:** `DB::beginTransaction()` dòng `167`; return sớm dòng `176`
- **Chi tiết:**
    - Trong loop, nếu `quantity < invoicedItem` thì `return Reply::error(...)` ngay khi transaction đang mở.
    - Rủi ro: transaction không được đóng đúng cách, gây trạng thái DB khó lường dưới tải cao.
- **Mức độ:** **Critical**
- **Fix gợi ý:** dùng `DB::transaction(function () { ... })` hoặc đảm bảo `DB::rollBack()` trước mọi early return/error.

### 5) Endpoint đổi trạng thái PO thiếu authorization + validation

- **File:** `Modules/Purchase/Http/Controllers/PurchaseOrderController.php`
- **Vị trí:** `changeStatus()` dòng `706-710`
- **Chi tiết:**
    - Không có check quyền (`permission`) trong method.
    - Không validate `delivery_status` theo enum cho phép.
    - Bất kỳ request hợp lệ route đều có thể set status tùy ý nếu qua middleware module.
- **Mức độ:** **Critical**
- **Fix gợi ý:** thêm FormRequest + permission check (view/edit ownership), whitelist status hợp lệ.

---

## B. Performance issues

### 1) N+1 query khi tính tax trong Order show

- **File:** `app/Http/Controllers/OrderController.php`
- **Vị trí:** vòng lặp dòng `522-529`
- **Chi tiết:**
    - Mỗi tax id gọi `OrderItems::taxbyid($tax)->first()` trong loop item/tax.
    - Với đơn nhiều item + nhiều tax => bùng nổ query.
- **Tác động:** chậm trang chi tiết đơn hàng.
- **Gợi ý:** preload map tax theo id một lần, rồi lookup in-memory.

### 2) N+1 + query lặp khi xóa nhiều product

- **File:** `Modules/Purchase/Http/Controllers/PurchaseProductController.php`
- **Vị trí:** `deleteRecords()` dòng `610-633`
- **Chi tiết:**
    - Mỗi product query stocks riêng (`where('product_id', ...)`) và mỗi inventory lại `exists()` + `find()`.
    - Batch delete lớn sẽ rất chậm.
- **Gợi ý:** gom query theo tập ID (bulk delete + bulk orphan check).

### 3) N+1 query trong lưu inventory theo nhiều product

- **File:** `Modules/Purchase/Http/Controllers/PurchaseInventoryController.php`
- **Vị trí:** dòng `169-172`, `180-182`, `241`
- **Chi tiết:**
    - Mỗi product đều query invoice sum, stock find, product findOrFail.
    - Đơn nhập nhiều dòng sẽ tốn nhiều roundtrip DB.
- **Gợi ý:** preload aggregate/in-memory map trước loop; hạn chế query per-row.

### 4) Import inventory save nhiều lần trên cùng product

- **File:** `Modules/Purchase/Jobs/ImportInventoryJob.php`
- **Vị trí:** dòng `130-133`, `149-151`, `158-160`, `295-297`
- **Chi tiết:**
    - Một row có thể trigger nhiều `save()` trên product (track_inventory, unit_id, description, price).
- **Gợi ý:** gom thay đổi và `save()` một lần.

---

## C. Bad practices / Architecture issues

### 1) Controller quá tải trách nhiệm (fat controller)

- **File:** `Modules/Purchase/Http/Controllers/PurchaseInventoryController.php` (~600+ dòng)
- **Biểu hiện:**
    - Trộn validate nghiệp vụ, transaction, import orchestration, PDF, file handling, quick action trong cùng controller.
- **Hệ quả:** khó test, khó maintain, tăng rủi ro regression.
- **Gợi ý:** tách service layer:
    - `InventoryAdjustmentService`
    - `InventoryImportService`
    - `InventoryStatusService`

### 2) Logic tồn kho bị “gộp record” theo (product_id, warehouse_id)

- **File liên quan:**
    - `Modules/Purchase/Http/Controllers/PurchaseInventoryController.php` dòng `180-182`
    - `database/migrations/2026_01_19_125000_update_indices_on_purchase_stock_adjustments_table.php` dòng `24`
- **Chi tiết:**
    - Unique index `product_id + warehouse_id` trên `purchase_stock_adjustments` khiến dữ liệu điều chỉnh có xu hướng overwrite thay vì lưu lịch sử movement đầy đủ.
- **Hệ quả:** mờ audit trail và khó đối soát lịch sử tồn.
- **Gợi ý:** tách rõ bảng snapshot (`warehouse_product_stock`) và bảng movement history (append-only).

### 3) Inconsistent behavior giữa import Product vs import Client

- **File:**
    - `app/Jobs/ImportProductChunkJob.php` dòng `125-127` (SKU trùng thì skip)
    - `app/Services/ClientImportProcessor.php` dòng `59-72` (client_code trùng thì update)
- **Chi tiết:** cùng “daily sync” nhưng strategy khác nhau (skip vs update), dễ gây sai kỳ vọng nghiệp vụ.
- **Gợi ý:** chuẩn hóa rule upsert per domain và document rõ.

---

## D. Security risks

### 1) Thiếu kiểm soát truy cập ở endpoint thay đổi trạng thái PO

- **File:** `Modules/Purchase/Http/Controllers/PurchaseOrderController.php`
- **Vị trí:** `changeStatus()` dòng `706-710`
- **Rủi ro:** privilege escalation nội bộ (user có quyền truy cập route nhưng không có edit permission vẫn sửa status).

### 2) Thiếu validate input cho `delivery_status`

- **File:** `Modules/Purchase/Http/Controllers/PurchaseOrderController.php`
- **Vị trí:** `changeStatus()` dòng `709`
- **Rủi ro:** lưu giá trị status rác/không hợp lệ, phá workflow downstream.

### 3) Multi-tenant data leakage qua import inventory lookup

- **File:** `Modules/Purchase/Jobs/ImportInventoryJob.php`
- **Vị trí:** dòng `67`, `75`
- **Rủi ro:** mapping nhầm product công ty khác.

### 4) Null-check thiếu trong permission path Client edit

- **File:** `app/Http/Controllers/ClientController.php`
- **Vị trí:** dòng `323`
- **Chi tiết:** dùng `$this->client->clientDetails->added_by` trực tiếp trong điều kiện permission.
- **Rủi ro:** crash nếu dữ liệu client orphan (không có `clientDetails`).

---

## E. Suggested refactor plan (ưu tiên thực thi)

### Phase 1 — Hotfix ngay (1-2 ngày)

1. Sửa `ProjectTemplateMilestoneController` permission expression.
2. Sửa `PurchaseInventoryController::changeStatus()`:
    - bỏ `$this->product`
    - dùng `$inventory->added_by`
    - validate `status`.
3. Sửa `PurchaseOrderController::changeStatus()`:
    - thêm FormRequest validate enum
    - thêm permission/ownership check.
4. Sửa `ImportInventoryJob` lookup có `company_id`.
5. Bịt transaction early-return trong `PurchaseInventoryController::store()`.

### Phase 2 — Performance & data integrity (3-5 ngày)

1. Refactor N+1 trong `OrderController` tax calculation.
2. Batch delete tối ưu cho `PurchaseProductController::deleteRecords()`.
3. Tối ưu loop query trong `PurchaseInventoryController::store()`.
4. Rà soát index theo access path chính:
    - `products(company_id, sku)` (index/unique theo nghiệp vụ)
    - index cho các cột filter thường dùng ở inventory/order history.

### Phase 3 — Architecture hardening (1-2 sprint)

1. Tách service layer cho Inventory/Purchase workflows.
2. Chuẩn hóa upsert strategy cho sync hằng ngày (Product/Client/Inventory).
3. Chuẩn hóa movement model:
    - snapshot table vs movement table append-only
    - tránh overwrite lịch sử tồn kho.

---

## Ghi chú

- Báo cáo này chỉ liệt kê các điểm có bằng chứng trực tiếp trong code đã rà.
- Các mục chưa có bằng chứng đầy đủ đã không đưa vào danh sách critical.
