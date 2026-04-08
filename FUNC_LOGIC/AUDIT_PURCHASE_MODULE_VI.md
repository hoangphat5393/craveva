# Audit module Purchase

**Phạm vi:** `Modules/Purchase/` — route web, quyền & alias Phase-2 (GRN / Sales DO), menu Operations, observer/event, Artisan migrate/cutover, liên kết Warehouse & Orders.  
**Ngày audit:** 2026-04-08

---

## 1. Tổng quan

| Hạng mục                   | Giá trị                                                                                                                                                                                                  |
| -------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Tên nwidart**            | `Purchase` / alias `purchase` (`module.json`)                                                                                                                                                            |
| **Hằng module (settings)** | `PurchaseManagementSetting::MODULE_NAME` và `PurchaseSetting::MODULE_NAME` = `'purchase'`                                                                                                                |
| **Service providers**      | `PurchaseServiceProvider`, `EventServiceProvider`                                                                                                                                                        |
| **Config merge**           | `config/purchase.php` (từ `Modules/Purchase/Config/config.php`): `flow_naming_mode`, `do_grn_cutover_enabled`, `permission_aliases`, `inventory_max_custom_field_joins`, `inventory_near_expiry_days`, … |
| **API**                    | `Modules/Purchase/Routes/api.php` — **rỗng** (chỉ nhóm `api` middleware qua `RouteServiceProvider`; không có endpoint REST trong file)                                                                   |
| **Tests trong repo**       | Nhiều file Pest/PHPUnit: GRN/Sales DO migrate & rollback, `FlowPermission`, `GrnService`, `SalesDoService`, inbound stock / Warehouse, v.v. (xem §8)                                                     |

---

## 2. Route web (`Routes/web.php`)

- **Nhóm:** `middleware => auth`, `prefix => account` (full path kiểu `/account/purchase-products`, …).
- **Không** thấy `Route::has` ở định nghĩa; bảo vệ chủ yếu ở controller (`abort_403`, `FlowPermission`, `user()->permission(...)`).

### 2.1. Khối chức năng (tóm tắt)

| Khu vực              | URI chính                                     | Ghi chú                                                                                      |
| -------------------- | --------------------------------------------- | -------------------------------------------------------------------------------------------- |
| Sản phẩm mua         | `purchase-products/*`                         | import, ảnh, inventory adjust, resource CRUD                                                 |
| Lý do điều chỉnh tồn | `adjustment-reasons`                          | resource                                                                                     |
| Thanh toán NCC       | `vendor-payments/*`                           | download, fetch bills, quick action                                                          |
| Liên hệ              | `purchase-contacts`                           | resource + quick action                                                                      |
| Vendor credit        | `vendor-credits/*`                            | apply to bill, download; route name `vendor-credits.get_bills` map URI **`getbiils`** (typo) |
| Tồn kho Purchase     | `purchase-inventory/*`                        | import, files, download                                                                      |
| File inventory       | `inventory-files`                             | resource                                                                                     |
| Cài đặt              | `purchase-settings`, `purchase-smtp-settings` | resource + update prefix                                                                     |
| Hóa đơn mua          | `bills/*`                                     | send, download                                                                               |
| Nhà cung cấp         | `vendors`                                     | resource; PO liên quan qua `PurchaseBillController`                                          |
| **Danh mục NCC**     | `vendor-cateogory`                            | **typo** “cateogory” → URL và tên route resource là `vendor-cateogory.*`                     |
| Đơn đặt hàng (PO)    | `purchase-order/*`                            | status, gửi mail, download                                                                   |
| GRN / Delivery Order | `delivery-orders/*` **và** alias `grn/*`      | Cùng `DeliveryOrderController`; tên route song song                                          |
| Sales DO / Shipment  | `sales-shipments/*` **và** alias `sales-do/*` | Cùng `SalesShipmentController`                                                               |
| File PO              | `purchase-order-file`                         | resource                                                                                     |
| Ghi chú NCC          | `vendor-notes/*`                              | mật khẩu xem note                                                                            |
| Báo cáo              | `reports`, `order-report`                     | resource                                                                                     |

### 2.2. Đặc biệt: hai route `change_status` cho delivery-orders

- `GET delivery_orders/change_status/{id}` → `delivery_orders.change_status`
- `POST delivery-orders/change-status/{id}` → `delivery-orders.changeStatus`

Cùng action `changeStatus` — khác method HTTP và tên route; cần gọi đúng verb khi maintain.

### 2.3. Phase-2 naming (`config('purchase.flow_naming_mode')`)

- `legacy`: menu dùng label/route **`delivery-orders`**, **`sales-shipments`**.
- `compat_v2` (mặc định env): label GRN / Sales DO, route **`grn.*`**, **`sales-do.*`** (vẫn cùng controller).

`DeliveryOrderController` / `SalesShipmentController` chọn prefix route qua `flow_naming_mode`.

---

## 3. Phân quyền

### 3.1. `FlowPermission` (`Support/FlowPermission.php`)

- `allowsAlias('grn.view' | 'grn.create' | …)` và `allowsAlias('sales_do.view' | …)` đọc `config('purchase.permission_aliases')`.
- Trước cutover (`do_grn_cutover_enabled` = false): **new OR legacy** permission được chấp nhận (ví dụ `view_grn` hoặc `view_purchase_order`).
- Sau cutover: chỉ còn permission **new**.

### 3.2. Migration permission (mẫu)

- `2023_05_03_*` — bộ permission cốt lõi: vendor, PO, bill, payment, credit, inventory, report, …
- `2024_01_18_*` — `view_purchase_setting`
- `2026_03_29_*` — `view_sales_shipment`, `create_sales_shipment`, …
- `2026_03_30_*` — `view_sales_do`, `ship_sales_do`, `view_grn`, `change_status_grn`, `delete_grn`, …

### 3.3. Menu (`purchase::sections.sidebar`)

- Include từ `resources/views/sections/menu.blade.php` qua `@includeIf('purchase::sections.sidebar')`.
- Hiển thị khi `PurchaseManagementSetting::MODULE_NAME` ∈ `user_modules()` **và** (tổ hợp permission view vendor/PO/bill/credit/inventory/report/payment **hoặc** alias GRN/Sales DO **hoặc** products/orders cross-links).
- **Reports:** mục `reports.index` có điều kiện kết thúc bằng **`&& false`** → luôn ẩn (có thể cố ý tắt tạm).

### 3.4. Rủi ro align menu ↔ controller

- Một số mục menu chỉ kiểm tra `user_modules()` + permission sản phẩm/đơn hàng; controller vẫn nên kiểm tra quyền từng hành động — cần rà từng controller khi harden (ngoài phạm vi audit từng dòng).

---

## 4. Observer, event, Warehouse

- **`EventServiceProvider`:** đăng ký observer cho `PurchaseVendor`, `PurchaseOrder`, `DeliveryOrder`, `Grn`, `PurchaseBill`, `Payment`, `Currency`, … và listeners cho `NewCompanyCreatedEvent`, email/notification purchase.
- **Warehouse:** `DeliveryOrderObserver`, `PurchaseOrderObserver` tương tác inbound stock theo `config('warehouse.inbound_from_purchase_order_delivered')` (đã có test `PurchaseInboundStockFlowTest`, `DeliveryOrderObserverGuardTest`, …).

---

## 5. Artisan (Purchase)

| Lệnh                                                                    | Mục đích (khái quát)           |
| ----------------------------------------------------------------------- | ------------------------------ |
| `purchase:activate`                                                     | Kích hoạt module               |
| `purchase:grn-migrate-data` / `purchase:grn-migrate-rollback`           | Migrate dữ liệu GRN + manifest |
| `purchase:sales-do-migrate-data` / `purchase:sales-do-migrate-rollback` | Migrate Sales DO               |
| `purchase:sales-do-migration-rehearsal`                                 | Diễn tập migration             |
| `purchase:sales-do-reconcile-report`                                    | Đối soát báo cáo               |
| `purchase:verify-cutover-schema`                                        | Kiểm tra schema cutover        |

---

## 6. i18n & tùy biến

- Bản dịch module: `Modules/LanguagePack/Languages/modules/Purchase/*/`.
- XSS: merge `purchase::xss_ignore` từ `Config/xss_ignore.php`.

---

## 7. Typos / nợ kỹ thuật (không sửa trong audit trừ khi có task riêng)

| Vị trí                                      | Ghi chú                                                                         |
| ------------------------------------------- | ------------------------------------------------------------------------------- |
| `vendor-cateogory`                          | URL + route name sai chính tả; đổi sẽ **breaking** — cần redirect/alias nếu sửa |
| `vendor-credits/getbiils/{id}`              | URI typo “biils”; route name `vendor-credits.get_bills`                         |
| `PurchaseSetting` migration default         | `vendor_credit_number_seprator` — typo “seprator” trong DB default (lịch sử)    |
| `SuperAdminPaypalController` (ngoài module) | `payWithPaypalRecurrring` — ba chữ **r** (không thuộc Purchase)                 |

---

## 8. Tests đã có (tham chiếu nhanh)

- `FlowPermissionAliasTest`, `GrnMigrateDataCommandTest`, `GrnMigrateRollbackCommandTest`, `GrnServiceLifecycleTest`, `GrnServicePersistenceTest`, `GrnCutoverRuntimeTest`
- `SalesDoMigrateDataCommandTest`, `SalesDoMigrateRollbackCommandTest`, `SalesDoMigrationRehearsalCommandTest`, `SalesDoReconciliationReportCommandTest`, `SalesDoServiceLifecycleTest`, `SalesDoServicePersistenceTest`, `SalesDoCutoverRuntimeTest`, `SalesShipmentOptionBTest`
- `PurchaseInventoryCoreFieldsTest`, `PurchaseInboundStockFlowTest`
- `WarehouseUpgradeP0Test`, `DeliveryOrderObserverGuardTest`, `CompanyObserverPackageModulesTest` (package + purchase)

**Bổ sung sau audit:** `tests/Feature/PurchaseModuleRoutesTest.php` — smoke `Route::has()` cho các route index cốt lõi (GRN/Sales DO theo naming, PO, bills, vendors).

---

## 9. Checklist hành động tùy ưu tiên

- [ ] Quyết định có bật lại menu Reports hay không (bỏ `&& false` hoặc thay bằng permission thật).
- [ ] Kế hoạch đổi `vendor-cateogory` → `vendor-category` (alias route + cập nhật view).
- [ ] Chuẩn hóa URI `getbiils` → `get-bills` (redirect 301 hoặc giữ cả hai).
- [ ] Khi bật `PURCHASE_DO_GRN_CUTOVER_ENABLED=true`, chạy `purchase:verify-cutover-schema` và rehearsal trên staging trước production.

---

_Tài liệu bổ sung cho `SPECIFICATION/MENU_ROUTES_AND_CACHE.md` (route module + cache) và các flow Warehouse/GRN trong `FUNC_LOGIC/` nếu có._
