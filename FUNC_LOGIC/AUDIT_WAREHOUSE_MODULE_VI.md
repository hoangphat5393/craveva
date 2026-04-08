# Audit module Warehouse

**Phạm vi:** `Modules/Warehouse/` — route web/API, config, service, Artisan; liên kết Purchase (PO/DO), Invoice, Sales DO; menu trong Purchase sidebar.  
**Ngày audit:** 2026-04-08

---

## 1. Tổng quan

| Hạng mục                   | Giá trị                                                                                                                                     |
| -------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------- |
| **Tên nwidart**            | `Warehouse` / alias `warehouse` (`module.json`)                                                                                             |
| **Service provider**       | `WarehouseServiceProvider` — merge `config/warehouse.php`, migrations, views, singleton services, command `warehouse:reconciliation-report` |
| **Route provider**         | `RouteServiceProvider` — web qua middleware `web`; API prefix `api` + middleware `api`                                                      |
| **Observers trong module** | Không có thư mục `Observers` trong Warehouse — luồng tồn kho gắn qua **app** và **Purchase** (xem §6)                                       |
| **Tests trong repo**       | `WarehouseRoutesTest`, `WarehouseUpgradeP0Test`, `WarehouseUnitConversionFlowTest`, GRN/Sales DO tests có `config('warehouse.*')`, v.v.     |

Tài liệu nghiệp vụ / UAT / kiến trúc tổng thể: xem [`WAREHOUSE_INDEX.md`](WAREHOUSE_INDEX.md) và [`WAREHOUSE_MASTER_GUIDE.md`](WAREHOUSE_MASTER_GUIDE.md). File này tập trung **audit code & route**.

---

## 2. Route web (`Modules/Warehouse/Routes/web.php`)

### 2.1. Legacy → `/account/...` (301)

- `GET /warehouse/{path?}` → `/account/warehouse/...` (giữ query string).
- `GET /warehouse-stock/{path?}` → `/account/warehouse-stock/...`.
- `GET /warehouse-movements` → `/account/warehouse-movements`.
- `GET /warehouse-transfer` → `/account/warehouse-transfer`.

**Smoke:** `tests/Feature/WarehouseRoutesTest.php` kiểm tra path named route và một số redirect.

### 2.2. Nhóm tenant (`auth`, `multi-company-select`, `email_verified`, prefix `account`)

| URI / pattern                                                                       | Controller                            | Ghi chú                             |
| ----------------------------------------------------------------------------------- | ------------------------------------- | ----------------------------------- |
| `warehouse/import` (GET/POST), `warehouse/import/process`                           | `WarehouseController`                 | Import kho (chunk job)              |
| `warehouse/update-order`, `warehouse/change-status`, `warehouse/apply-quick-action` | `WarehouseController`                 | POST hỗ trợ UI                      |
| `warehouse`                                                                         | `WarehouseController` (resource)      | CRUD kho                            |
| `warehouse-movements`                                                               | `WarehouseMovementController@index`   | Lịch sử movement                    |
| `warehouse-stock`                                                                   | `WarehouseStockController` (resource) | Điều chỉnh / quản lý tồn theo batch |
| `warehouse-transfer` GET/POST                                                       | `WarehouseTransferController`         | Chuyển kho                          |

**Bảo vệ chung:** middleware trong constructor các controller `AccountBaseController` — `abort_if(! in_array('warehouse', user_modules()), 403)`. Chi tiết quyền từng action: `user()->permission('view_warehouses' | 'view_warehouse_stock' | 'manage_warehouse_transfer' | …)` (khác nhau theo method).

---

## 3. Route API (`Modules/Warehouse/Routes/api.php`)

Nhóm: `auth:sanctum`, prefix `v1`, name prefix `api.`, full path kiểu `/api/v1/...`.

| Method + URI                        | Handler                                | Ghi chú                                                                                      |
| ----------------------------------- | -------------------------------------- | -------------------------------------------------------------------------------------------- |
| `GET api/v1/warehouse`              | Closure trả về `$request->user()`      | **Placeholder / debug** — không nghiệp vụ warehouse; nên rà soát trước khi expose production |
| `GET api/v1/warehouse/availability` | `WarehouseAvailabilityController@show` | Query: `company_id`, `product_id`, `warehouse_ids?`                                          |

### 3.1. Rủi ro bảo mật API availability

`WarehouseAvailabilityController` nhận **`company_id` từ request** và gọi `WarehouseAvailabilityService::availabilityByProduct` **không** so khớp với company của user/token.

- Với token Sanctum hợp lệ, lý thuyết có thể đọc tồn theo **bất kỳ** `company_id` (IDOR nếu không có lớp kiểm tra ở middleware/policy khác).
- **Khuyến nghị:** chỉ cho phép `company_id` trùng context đăng nhập (hoặc bỏ tham số, lấy từ `user()->company_id` / multi-company hiện tại).

---

## 4. Config (`Modules/Warehouse/Config/config.php` → `config('warehouse.*')`)

| Key (env)                               | Ý nghĩa ngắn                                                     |
| --------------------------------------- | ---------------------------------------------------------------- |
| `allow_negative_stock`                  | `WAREHOUSE_ALLOW_NEGATIVE_STOCK`                                 |
| `strict_unit_conversion`                | `WAREHOUSE_STRICT_UNIT_CONVERSION`                               |
| `inbound_from_purchase_order_delivered` | Nhập kho khi PO `delivered` (mặc định bật)                       |
| `inbound_from_delivery_order_received`  | Nhập kho khi DO inbound `received` (mặc định tắt)                |
| `sales_outbound_enabled`                | Bật xuất kho từ invoice / bỏ qua nhánh legacy PaymentObserver    |
| `sales_outbound_mode`                   | `shipment` \| `invoice` — tránh trừ kép giữa shipment và invoice |

**Cảnh báo runtime:** `WarehouseServiceProvider::boot()` ghi **warning log** nếu **cả hai** inbound PO delivered và DO received đều `true` (nguy cơ **nhập đôi**).

`.env.example` có comment các biến sales outbound và inbound (một phần; có thể bổ sung `WAREHOUSE_ALLOW_*` nếu cần đồng bộ doc).

---

## 5. Phân quyền & module flag

### 5.1. Migration thiết lập

- `2026_03_25_120000_setup_warehouse_module_permissions_and_activation.php`: tạo module `warehouse`, permissions `view_warehouses`, `add_warehouses`, `edit_warehouses`, `delete_warehouses`, `view_warehouse_stock`, `add_warehouse_stock`, `edit_warehouse_stock`, `delete_warehouse_stock`, `manage_warehouse_transfer`; gán full cho role **admin**; gắn `warehouse` vào package có `purchase` hoặc `products`; `ModuleSetting` active theo package.
- `2026_03_26_100000_backfill_user_permissions_for_warehouse_module.php`: backfill **user_permissions** từ `permission_role` (sửa lỗi employee chỉ có role nhưng không có quyền warehouse trực tiếp).

### 5.2. Menu (không nằm trong `resources/views/sections/menu.blade.php`)

Mục Warehouse nằm trong **`Modules/Purchase/Resources/views/sections/sidebar.blade.php`** (Operations):

- Điều kiện module: `in_array('warehouse', user_modules())`.
- **Master kho:** `canSeeWarehouseMaster` = `view_warehouses` ≠ none **hoặc** có quyền xem **Inventory** Purchase (`view_inventory`).
- **Stock / movements / transfer UI:** `canSeeWarehouseStockUi` = `view_warehouse_stock` ≠ none **hoặc** cùng fallback Inventory.

→ Employee có Inventory nhưng chưa có permission warehouse riêng vẫn có thể thấy menu (theo comment trong blade).

---

## 6. Luồng tích hợp ngoài module (quan trọng)

| Nguồn                                                                                          | Tác động                                                                                                |
| ---------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------- |
| `Modules/Purchase/Observers/PurchaseOrderObserver`                                             | Inbound qua `StockMovementService::recordInbound` khi `warehouse.inbound_from_purchase_order_delivered` |
| `Modules/Purchase/Observers/DeliveryOrderObserver`                                             | Inbound batch khi DO received + flag DO                                                                 |
| `app/Observers/InvoiceObserver`                                                                | `InvoiceWarehouseStockService::syncInvoiceStock` / reverse — phụ thuộc `sales_outbound_enabled` và mode |
| `Modules/Purchase/Observers/PaymentObserver`                                                   | Nhánh legacy stock khi **không** bật `sales_outbound_enabled`                                           |
| `Modules/Purchase/Services/SalesDoService` + `SalesShipmentStockService`                       | Outbound theo shipment khi `sales_outbound_mode === 'shipment'`                                         |
| `Modules/Purchase/Http/Controllers/PurchaseInventoryController`, `InventoryImportRowProcessor` | `recordOutbound` điều chỉnh tồn                                                                         |

Cấu hình sai (hai inbound cùng lúc, hoặc vừa shipment vừa invoice outbound) dễ dẫn tới **lệch sổ** — đã có log cảnh báo cho inbound đôi; outbound cần vận hành theo một **mode** rõ ràng.

---

## 7. Artisan

| Lệnh                                                        | Mô tả                                                                         |
| ----------------------------------------------------------- | ----------------------------------------------------------------------------- |
| `warehouse:reconciliation-report {--date=} {--company_id=}` | Tổng hợp reconciliation, ghi JSON vào `storage/app/warehouse-reconciliation/` |

---

## 8. Entity chính (`Modules/Warehouse/Entities/`)

`Warehouse`, `WarehouseProductBatch`, `WarehouseProductStock`, `StockReservation`, `InvoiceWarehouseStockPosting`, `WarehouseSyncReconciliationLog`, `ProductUnitConversion` — migration nằm trong `Modules/Warehouse/Database/Migrations/`.

---

## 9. Khuyến nghị / việc nên làm tiếp

1. **API availability:** Ràng buộc `company_id` (hoặc bỏ) để tránh IDOR; thêm test feature với Sanctum + company khác.
2. **GET `api/v1/warehouse`:** Xóa, bảo vệ, hoặc thay bằng endpoint có nghĩa; không để placeholder trong production nếu không chủ đích.
3. **Document:** Giữ `WAREHOUSE_*` env đồng bộ với `config.php` trong runbook vận hành.
4. **Test:** Giữ chạy `WarehouseRoutesTest` + các test upgrade/unit liên quan khi đổi flag warehouse hoặc observer.

---

_Cập nhật khi đổi route, permission, hoặc contract API._
