# Opening stock vs tồn kho Warehouse — backlog cải tiến

**Ngày ghi:** 2026-05-20  
**Nguồn:** Phân tích case Test RM (product `4051`, SKU `SP-RM-000001`) — Opening stock 100 trên form sản phẩm nhưng Inventory list = 0 (warehouse `--`), Production order báo shortfall Available = 0.  
**Liên quan:** [`06_INVENTORY_BUSINESS_IMPROVE.md`](06_INVENTORY_BUSINESS_IMPROVE.md) (mục Opening stock vs Add Inventory), [`multi_warehouse_audit_report.md`](../FUNC_LOGIC/multi_warehouse_audit_report.md) (hai sổ tồn).

---

## 1. Tóm tắt vấn đề (không phải user nhập sai)

| Lớp                              | Nguồn dữ liệu                                                                                                             | Ai đọc                                                                                                   |
| -------------------------------- | ------------------------------------------------------------------------------------------------------------------------- | -------------------------------------------------------------------------------------------------------- |
| **Opening stock** (form Product) | `products.opening_stock` + `purchase_stock_adjustments.net_quantity` (thường **không** `warehouse_id`)                    | Master sản phẩm, legacy Purchase                                                                         |
| **Tồn kho vận hành**             | `warehouse_product_stock` / `warehouse_product_batches` (+ `StockMovementService` khi Add Inventory / GRN / PO delivered) | Màn **Purchase Inventory**, **Production** (cột Available tại `rm_warehouse_id`), bán/xuất theo cấu hình |

**Kết luận:** Nhập Opening stock **đúng theo form hiện tại** nhưng **chưa đủ** để Inventory list và lệnh SX thấy tồn — đó là **khoảng trống đồng bộ / UX gây hiểu nhầm**, không phải lỗi validation một lần nhập.

**Code tham chiếu:**

- Lưu opening stock (không set warehouse): `Modules/Purchase/Http/Controllers/PurchaseProductController.php` (store/update, block `track_inventory`).
- Inventory list join batch theo `warehouse_id`: `Modules/Purchase/DataTables/PurchaseInventoryDataTable.php`.
- Production Available: `Modules/Production/Services/ProductionOrderMaterialRequirementsSummary.php` → `WarehouseProductStock` tại `rm_warehouse_id`.
- Add Inventory bắt buộc warehouse khi module/tables tồn tại: `PurchaseInventoryController::store` (~dòng 158–160).

**UX gây nhầm:** Popover field Opening stock dùng key `purchase::app.availableStock` (“Tồn khả dụng”) trong `product-form-fields.blade.php` — người dùng tưởng đó là tồn kho thật.

---

## 2. Ba nhóm khách hàng (policy đề xuất)

| Nhóm  | Mô tả                                             | Opening stock                                            | Tồn vận hành                      |
| ----- | ------------------------------------------------- | -------------------------------------------------------- | --------------------------------- |
| **A** | Không bật module `warehouse`                      | Có thể dùng tồn đầu kỳ legacy                            | `purchase_stock_adjustments`      |
| **B** | Một kho (đơn kho) + Production/Purchase inventory | Tuỳ chọn; **nên** có bước ghi vào **một** kho            | Add Inventory / GRN / PO → kho đó |
| **C** | Đa kho                                            | Bootstrap / cảnh báo; không thay phiếu có `warehouse_id` | Luôn chọn kho trên phiếu          |

**Không** khuyến nghị coi Opening stock thay cho Add Inventory khi đã bật Warehouse hoặc Production.

---

## 3. Khách từng dùng kho, sau tắt module Warehouse

| Hạng mục                                                         | Ảnh hưởng                                                                                     |
| ---------------------------------------------------------------- | --------------------------------------------------------------------------------------------- |
| Bảng `warehouses`, `warehouse_product_stock`, batches, movements | **Không tự xóa** khi `ModuleSetting` warehouse = deactive                                     |
| Menu / `user_modules()`                                          | Ẩn menu kho; Production **ẩn** cột Available/Shortfall nếu không có `warehouse` trong modules |
| Lệnh SX cũ (`rm_warehouse_id`, batch consumption)                | ID và dữ liệu cũ **còn**; mở lại module có thể thấy lại                                       |
| Legacy `purchase_stock_adjustments`                              | Có thể **lệch** tổng so với warehouse nếu trước đó chỉ cập nhật một nhánh                     |
| Add Inventory                                                    | Nếu bảng `warehouses` vẫn tồn tại → vẫn có thể **bắt buộc** chọn kho khi tạo phiếu            |

**Khuyến nghị vận hành:** Ưu tiên **một kho mặc định** thay vì tắt module giữa chừng; nếu tắt — chốt một nguồn báo cáo và đối soát, không purge `warehouse_*` không có kế hoạch.

---

## 4. Lộ trình cải tiến (ưu tiên)

### P0 — UX / tài liệu (ít rủi ro, làm trước)

**Mục tiêu:** Giảm hiểu nhầm; **không** đổi logic tồn kho trong phase này.

| Hạng mục                      | Việc làm                                                                                                                                                    | **Không** bao gồm                                       |
| ----------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------- |
| Nhãn / help trên form Product | Key i18n mới (giữ key cũ): popover/help Opening stock = _“Tồn đầu kỳ (hồ sơ sản phẩm)”_ + help _“Chưa ghi vào kho vật lý cho đến khi Add Inventory / GRN.”_ | **Không** thêm `select` kho trên form sản phẩm trong P0 |
| Overview / readonly field     | Đồng bộ nhãn trên tab overview sản phẩm nếu cần                                                                                                             |                                                         |
| Onboarding (doc)              | Checklist: **Tạo kho** (nếu chưa có) → **Tạo SP** → (tuỳ chọn opening) → **Add Inventory chọn kho** nếu bật `warehouse` hoặc `production` → Production dùng kho NL trùng lệnh | Không thay flow Save product                            |

**File code khi implement P0:**

- `Modules/Purchase/Resources/views/purchase-products/partials/product-form-fields.blade.php` — đổi `:popover` từ `availableStock` sang key mới.
- `Modules/LanguagePack/Languages/modules/Purchase/{en,vi}/app.php` — thêm key mới (không đổi nghĩa `availableStock` ở chỗ khác).
- Tuỳ chọn: alert/info nhỏ dưới field khi `in_array('warehouse', user_modules())`.

**Phù hợp:** Mọi công ty, kể cả không đa kho.

**Checklist onboarding (P0 — không cần kho mặc định):**

1. Bật module **Warehouse** (và/hoặc **Production**) nếu cần tồn vận hành / lệnh SX.
2. Tạo ít nhất **một kho active** tại **Warehouses** (nếu chưa có — form SP sẽ cảnh báo).
3. **Tạo sản phẩm** + bật Track inventory; nhập Opening stock nếu muốn (hồ sơ sản phẩm).
4. **Operations → Inventory → Add Inventory** — chọn kho, sản phẩm, số lượng (bước bắt buộc để Inventory list / Production thấy tồn).
5. Lệnh **Production**: chọn **kho nguyên liệu** trùng kho đã Add Inventory.

---

### P1 — Đồng bộ có điều kiện (code, medium) — **Done**

Chỉ khi `warehouse` active **và** công ty có **kho mặc định** (một kho duy nhất hoặc setting `default_inventory_warehouse_id` / kho NL mặc định Production):

- Khi lưu sản phẩm + Opening stock → ghi `warehouse_product_stock` (+ batch nếu policy) tại **một** kho mặc định.
- Công ty không bật Warehouse → giữ legacy, không ghi `warehouse_*`.
- Legacy lines thiếu `warehouse_id` → backfill qua artisan (xem mục 8).

**Code:** `ProductOpeningStockWarehouseSync`, `EnsureDefaultWarehouseService`, `PurchaseProductController::persistTrackInventoryFromRequest()`.

**Có thể** dùng kho mặc định từ setting — **không** bắt buộc dropdown kho trên form Product nếu chỉ có một kho; nếu **đa kho** và muốn chọn lúc onboarding → **P1b** (tuỳ chọn): thêm select kho **chỉ khi** `warehouse` active và `warehouses.count > 1`.

---

### P2 — Chế độ inventory theo công ty (tuỳ chọn)

- `inventory_mode`: `legacy` | `single_warehouse` | `multi_warehouse`.
- `single_warehouse`: auto `warehouse_id` trên phiếu đơn giản; ẩn chọn kho thừa trên UI đơn giản.
- Production khi tắt Warehouse: nhánh đọc legacy (hiện vẫn bắt `rm_warehouse_id` khi tạo lệnh — cần thiết kế riêng).

---

## 5. FAQ triển khai

### “P0 UX có phải thêm select chọn kho trên form sản phẩm không?”

**Không** — P0 chỉ **đổi chữ / help / checklist tài liệu**. Kho vẫn chọn tại **Operations → Inventory → Add Inventory** (đã có `warehouse_id` bắt buộc khi module Warehouse hoạt động).

Select kho trên form Product chỉ xem xét ở **P1b** nếu product team muốn ghi opening thẳng vào kho khi công ty có **nhiều** kho và không dùng kho mặc định.

### “Warehouses trống thì sao?”

- Form sản phẩm vẫn lưu được (Opening stock ghi hồ sơ legacy).
- **Add Inventory không lưu được** — controller bắt buộc `warehouse_id` khi bảng `warehouses` tồn tại (`PurchaseInventoryController::store`).
- P0 hiển thị **alert** trên form SP khi module warehouse/production bật nhưng **0 kho active**, hướng dẫn tạo kho trước.

### “Công ty một kho thì sao?”

- **P0:** Hướng dẫn Add Inventory vào kho duy nhất đó.
- **P1:** Auto-post opening stock vào kho duy nhất (không cần select thêm trên form Product).

---

## 6. Test / verify khi làm P1+

1. Tạo SP raw material, opening 100, warehouse module on, 1 default warehouse → Inventory list cùng kho hiển thị 100.
2. Đa kho: opening không auto hoặc chọn kho P1b → Production order `rm_warehouse_id` trùng kho có tồn → Available > 0, không shortfall giả.
3. Module warehouse off: opening vẫn lưu legacy; không crash Production nếu có policy P2.

---

## 7. Trạng thái

| Phase                     | Trạng thái   | Ghi chú                                                                                       |
| ------------------------- | ------------ | --------------------------------------------------------------------------------------------- |
| P0 UX / doc               | **Done**     | Key `openingStockPopoverHelp`, `openingStockFieldHelp*`, alert 0 kho; UX-005 Done             |
| P1 sync default warehouse | **Done**     | `EnsureDefaultWarehouseService`, sync on product save, backfill command; xem mục 8            |
| P2 inventory_mode         | **Deferred** |                                                                                               |

**Backlog UX:** `FUNC_IMPROVE/10_UX_UI_IMPROVEMENT_BACKLOG.md` — ID **UX-005**.

---

## 8. Artisan commands (P1)

Chạy trên server / local sau migrate Warehouse module.

| Lệnh | Mục đích |
| ---- | -------- |
| `php artisan warehouse:ensure-default-for-companies` | Mỗi công ty active: tạo hoặc chuẩn hoá **một** kho mặc định (`is_default = true`). |
| `php artisan warehouse:ensure-default-for-companies --dry-run` | Báo cáo only — không ghi DB. |
| `php artisan warehouse:ensure-default-for-companies --company=ID` | Chỉ xử lý một `company_id`. |
| `php artisan warehouse:backfill-opening-stock-to-default` | Gắn `warehouse_id` lên dòng opening stock legacy (`purchase_stock_adjustments` thiếu kho) và post tồn vào kho mặc định. |
| `php artisan warehouse:backfill-opening-stock-to-default --dry-run` | Báo cáo `would_sync` — không ghi. |
| `php artisan warehouse:backfill-opening-stock-to-default --company=ID` | Backfill theo công ty. |

**Thứ tự triển khai đề xuất:**

1. `warehouse:ensure-default-for-companies` (hoặc `--dry-run` trước).
2. `warehouse:backfill-opening-stock-to-default --dry-run` → kiểm tra → chạy không `--dry-run`.
3. Lưu sản phẩm mới có opening stock sẽ tự sync qua `ProductOpeningStockWarehouseSync` (cần kho mặc định; nếu thiếu → message `openingStockNoDefaultWarehouse`).
