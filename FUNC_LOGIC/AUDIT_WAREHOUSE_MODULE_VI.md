# Audit module Warehouse

**Phạm vi:** `Modules/Warehouse/` — route web/API, config, service, Artisan; liên kết Purchase (PO/DO), Invoice, Sales DO; menu trong Purchase sidebar.  
**Ngày audit:** 2026-04-08

---

## 1. Tổng quan

| Hạng mục                   | Giá trị                                                                                                                                                                 |
| -------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Tên nwidart**            | `Warehouse` / alias `warehouse` (`module.json`)                                                                                                                         |
| **Service provider**       | `WarehouseServiceProvider` — merge `config/warehouse.php`, migrations, views, singleton services, command `warehouse:reconciliation-report`                             |
| **Route provider**         | `RouteServiceProvider` — web qua middleware `web`; API prefix `api` + middleware `api`                                                                                  |
| **Observers trong module** | Không có thư mục `Observers` trong Warehouse — luồng tồn kho gắn qua **app** và **Purchase** (xem §6)                                                                   |
| **Tests trong repo**       | `WarehouseRoutesTest`, `WarehouseAvailabilityApiTest`, `WarehouseUpgradeP0Test`, `WarehouseUnitConversionFlowTest`, GRN/Sales DO tests có `config('warehouse.*')`, v.v. |

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

| Method + URI                        | Handler                                | Ghi chú                                                                    |
| ----------------------------------- | -------------------------------------- | -------------------------------------------------------------------------- |
| `GET api/v1/warehouse/availability` | `WarehouseAvailabilityController@show` | Query: `product_id` (bắt buộc), `warehouse_ids?`, `company_id?` — xem §3.1 |

### 3.1. API availability — đã siết (2026-04-08)

- **`GET api/v1/warehouse`** (closure trả user) đã **gỡ** khỏi route.
- `company_id` **không** tin tưởng tuyệt đối:
    - Principal **`UserAuth`:** danh sách company từ `users.user_auth_id` (query `DB::table`, tránh scope ẩn Eloquent). Một company → bỏ qua hoặc ép khớp `company_id` request; nhiều company → **bắt buộc** `company_id` và phải thuộc danh sách; sai → **403** JSON.
    - Principal **`User`:** dùng `company_id` bản ghi; nếu client gửi `company_id` thì phải khớp.
- Từ chối truy cập: **`HttpResponseException` JSON 403** (tránh handler API biến `abort(403)` thành 500).
- **Tests:** `tests/Feature/WarehouseAvailabilityApiTest.php`.

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

1. **Document:** Giữ `WAREHOUSE_*` env đồng bộ với `config.php` trong runbook vận hành; client API gọi availability: nếu tài khoản đa company thì luôn gửi `company_id` hợp lệ.
2. **Test:** Giữ chạy `WarehouseRoutesTest`, `WarehouseAvailabilityApiTest` và các test upgrade/unit liên quan khi đổi flag warehouse hoặc observer.
3. **Handler API (toàn app):** Cân nhắc chuẩn hóa JSON cho `abort(403)` / `HttpException` thay vì 500 — hiện warehouse availability đã workaround bằng `HttpResponseException`.

---

_Cập nhật khi đổi route, permission, hoặc contract API._
