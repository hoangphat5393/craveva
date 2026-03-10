# Đọc hết code module Product (Purchase)

Tài liệu tóm tắt cấu trúc và logic của **module Product** thuộc **Modules/Purchase** (Operations > Products).

---

## 1. Cấu trúc thư mục & file

### 1.1. Controller
- **`Modules/Purchase/Http/Controllers/PurchaseProductController.php`**  
  Controller chính: CRUD product, import, inventory, quick actions, images.

### 1.2. Entity / Model
- **`Modules/Purchase/Entities/PurchaseProduct.php`**  
  Model dùng bảng `products` (trùng bảng với `App\Models\Product`).
- **`Modules/Purchase/Entities/PurchaseProductHistory.php`**  
  Lịch sử thay đổi product (created/updated), dùng trong Observer.

### 1.3. DataTables
- **`Modules/Purchase/DataTables/PurchaseProductsDataTable.php`**  
  DataTable danh sách product: cột check, category, sub_category, name, price, stock_on_hand, unit_type, allow_purchase, status, action. Có filter category, sub_category, unit_type, product_type, searchText. Export Excel. Custom fields.
- **`Modules/Purchase/DataTables/PurchaseProductTransaction.php`**  
  DataTable tab “Transactions” trong show product (hiện dùng LeadFollowup, logic chưa gắn đúng giao dịch product).

### 1.4. Request validation
- **`Modules/Purchase/Http/Requests/Product/StorePurchaseProductRequest.php`**  
  Validate khi tạo: name (unique theo company), type, selling_price, track_inventory, opening_stock, purchase_price, downloadable_file, custom fields.
- **`Modules/Purchase/Http/Requests/Product/UpdatePurchaseProductRequest.php`**  
  Validate khi sửa: tương tự + name unique trừ chính nó, wholesale_price, price_per_box, employee_price, inventory_type.

### 1.5. Observer
- **`Modules/Purchase/Observers/PurchaseProductObserver.php`**  
  - `saving`: UnitTypeSaveTrait, set `last_updated_by`.  
  - `creating`: set `added_by`, `company_id`.  
  - `created` / `updated`: ghi `PurchaseProductHistory` (Created/Updated).  
  - `deleting`: xóa `files()` trước khi xóa product.  
  Đăng ký trong **`Modules/Purchase/Providers/EventServiceProvider.php`** (`PurchaseProduct::class => PurchaseProductObserver::class`).

### 1.6. Routes (web.php)
- Resource: `Route::resource('purchase-products', PurchaseProductController::class)` → index, create, store, show, edit, update, destroy.
- Thêm: layout, add-images, store-images, change-status, change-purchase-allow, adjust-inventory, update-inventory, apply-quick-action, options.
- Import: GET `purchase-products/import`, POST `purchase-products/import`, POST `purchase-products/import/process`.

### 1.7. Views
- **Layout / trang chính**
  - `purchase-products/index.blade.php` – Danh sách product (filter, DataTable, quick action, Add/Import, cart).
  - `purchase-products/create.blade.php` – Wrapper `@include($view)` (dùng cho create/edit modal).
  - `purchase-products/show.blade.php` – Chi tiết product, tab Overview / Files / History (transactions tab ẩn).
- **Ajax (nội dung modal / tab)**
  - `ajax/create.blade.php` – Form thêm product (type, name, sku, unit, category, subcategory, price, tax, track_inventory, opening_stock, purchase info, downloadable, custom fields).
  - `ajax/edit.blade.php` – Form sửa product (tương tự create).
  - `ajax/overview.blade.php` – Tab overview: thông tin product, ảnh, opening stock, stock on hand, committed stock, available for sale, nút Adjust Stock, Edit, Delete.
  - `ajax/files.blade.php` – Tab hình ảnh/file product.
  - `ajax/history.blade.php` – Tab lịch sử (PurchaseProductHistory).
  - `ajax/transactions.blade.php` – Tab transactions (DataTable PurchaseProductTransaction).
  - `ajax/update_inventory.blade.php` – Modal điều chỉnh tồn kho (date, type, quantity, reason, v.v.).
  - `ajax/import.blade.php` – Form upload file import (dropify, heading toggle), POST import.store.
  - `ajax/import_progress.blade.php` – Include `import.process-form` với headingTitle, processRoute, backRoute, backButtonText, importClassName, và các biến file/columns/importSample...
- **Product files**
  - `product-files/create.blade.php` – Thêm ảnh/file.
  - `product-files/ajax-list.blade.php` – Danh sách file dạng list.
  - `product-files/thumbnail-list.blade.php` – Danh sách file dạng thumbnail.
- **Reasons**
  - `reasons/create.blade.php` – (Liên quan lý do điều chỉnh tồn kho, không phải CRUD product chính.)

### 1.8. Migration (liên quan product)
- **`Database/Migrations/2023_09_04_050707_product_files.php`** – Thêm cột `default_status` cho bảng `product_files`.
- **`Database/Migrations/2023_07_14_102505_product_files_timstamps.php`** – (Tên gợi ý timestamps cho product files.)

---

## 2. Logic nghiệp vụ chính

### 2.1. PurchaseProduct vs App\Models\Product
- **PurchaseProduct** (`Modules\Purchase\Entities\PurchaseProduct`) dùng **bảng `products`** (`protected $table = 'products'`).
- **App\Models\Product** cũng dùng bảng `products`.
- Trong Purchase module, màn Operations > Products dùng **PurchaseProduct** và **PurchaseProductController**; các chỗ khác (Invoice, Order, Lead…) có thể dùng **Product**. Hai model cùng bảng, khác namespace và quan hệ (PurchaseProduct có `inventory()`, `orderItem()`, v.v.).

### 2.2. CRUD
- **index**: PurchaseProductsDataTable, filter category/sub_category/unit_type/product_type/searchText, quyền view_product (all/added).
- **create**: Form create (ajax hoặc full page), taxes, categories, subCategories, unit_types, custom fields (Product), duplicate_product query.
- **store**: Validate StorePurchaseProductRequest, tạo PurchaseProduct; nếu track_inventory thì tạo PurchaseInventory + PurchaseStockAdjustment (opening_stock/rate_per_unit); custom fields qua Product::find($product->id)->updateCustomFieldData.
- **show**: Tab overview/files/history (transactions); overview dùng PurchaseStockAdjustment cho stock on hand, InvoiceItems cho committed stock.
- **edit**: Load product với orderItem, quantityInventory; nếu có purchase bill hoặc invoice items thì disable đổi track_inventory.
- **update**: Giống store (UpdatePurchaseProductRequest), cập nhật product và nếu track_inventory thì cập nhật/tạo inventory + stock adjustment.
- **destroy**: Xóa hết PurchaseStockAdjustment của product → xóa PurchaseInventory không còn stock → xóa product (và observer xóa files).

### 2.3. Quick actions (applyQuickAction)
- **delete**: deleteRecords – xóa từng product (files → stocks → inventory orphan → product).
- **change-status**: update status (active/inactive) theo row_ids.
- **change-purchase**: update allow_purchase theo row_ids.

### 2.4. Import
- **importProduct**: Hiển thị form import (view ajax.import).
- **importStore**: Gọi `importFileProcess($request, ProductImport::class)` (trait ImportExcel), render `import_progress` với `$this->data` → trả response.view.
- **importProcess**: Gọi `importJobProcess($request, ProductImport::class, ImportProductJob::class)` → queue tên `ProductImport`, job **App\Jobs\ImportProductJob** (tạo **App\Models\Product**, không phải PurchaseProduct).  
  → Import Excel thực tế ghi vào bảng `products` qua model **Product**; vì cùng bảng nên danh sách Purchase product vẫn thấy bản ghi mới.

### 2.5. Inventory
- **adjustInventory**: Form điều chỉnh tồn kho (update_inventory view), reasons.
- **updateInventory**: Tạo/cập nhật PurchaseInventory + PurchaseStockAdjustment (date, type, quantity, reason, cost_price…), cập nhật product.purchase_price; dispatch PurchaseInventoryEvent.

### 2.6. Images / Files
- **storeImages**: Upload file vào ProductFiles (product_id), set default_image nếu chọn.
- **layout**: Trả HTML list/thumbnail layout cho product files.
- **addImages**: Form thêm ảnh (product-files create).

### 2.7. Khác
- **changeStatus**: Đổi status (active/inactive) 1 product.
- **changePurchaseAllow**: Đổi allow_purchase 1 product.
- **allPurchaseProductOption**: Trả HTML option các product có inventory > 0 hoặc type service (dùng cho dropdown nơi khác).

---

## 3. Quan hệ Entity

- **PurchaseProduct**
  - `category` → ProductCategory  
  - `subCategory` → ProductSubCategory  
  - `unit` → UnitType  
  - `files` → ProductFiles  
  - `orderItem` → PurchaseItem  
  - `invoiceItem` → InvoiceItems  
  - `inventory` → PurchaseStockAdjustment (hasMany)  
  - `quantityInventory` → PurchaseStockAdjustment (hasOne)  
  - `inventoryAdjustments` → PurchaseInventory (belongsToMany qua purchase_stock_adjustments)

- **PurchaseProductHistory**
  - `user` → User  
  - `products` → PurchaseProduct (belongsTo purchase_product_id)

---

## 4. Permissions (middleware / abort_403)

- Module: `PurchaseSetting::MODULE_NAME` trong `user->modules`.
- **view_product**: all | added (filter added_by khi added).
- **add_product**: all | added (create, store, import, addImages).
- **edit_product**: all | added (edit, update, changeStatus, changePurchaseAllow, adjustInventory một phần).
- **delete_product**: all | added (destroy, deleteRecords, quick action delete).
- **add_inventory** / **edit_inventory**: (adjustInventory, updateInventory).

---

## 5. Ghi chú

- **DataTable Transaction**: PurchaseProductTransaction đang query **LeadFollowup** và request leadId; chưa gắn với giao dịch mua hàng/tồn kho của product. Tab transactions trên UI bị comment ẩn.
- **Import**: Dùng **App\Imports\ProductImport** và **App\Jobs\ImportProductJob** (app), tạo **App\Models\Product**; bảng vẫn là `products` nên danh sách Purchase Products hiển thị đúng bản ghi sau import.
- **Custom fields**: Dùng `Product::CUSTOM_FIELD_MODEL` / `App\Models\Product` và Product::find($product->id) để updateCustomFieldData; PurchaseProduct và Product dùng chung bảng nên dữ liệu custom field khớp.

Đây là toàn bộ phần code module Product (Purchase) đã đọc và tóm tắt.
