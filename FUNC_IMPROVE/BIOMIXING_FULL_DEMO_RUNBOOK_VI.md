# Biomixing — Runbook demo đầy đủ trên Hub (kỹ thuật)

| Thuộc tính     | Giá trị                                                                                                                                                                                      |
| -------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Mục đích**   | Chuẩn bị và chạy **demo end-to-end** trên một tenant: Purchase → Kho → **Production** (BOM, lệnh, batch, RM, FG) → Bán giao (**SO → DO → Invoice**) — giảm lỗi cấu hình / route / migration. |
| **Không phải** | Kịch bản marketing + AI trong `PROJECT BIOMIXING/BIOMIXING_DEMO_SCRIPT.md` (ERP+AI storytelling thay Production module); dùng file đó chỉ làm overlay nội dung khách hàng nếu cần.           |
| **Cập nhật**   | 2026-05-24                                                                                                                                                                                   |

Đọc thêm khái niệm RM/FG: `BIOMIXING_FLOW_CONCEPTS_VI.md`. Checklist ERP 3 luồng cơ bản: `P0_MINI_UAT_CHECKLIST_BIOMIXING_VI.md`.

---

## 0. Xác nhận môi trường (trước khi mở browser)

1. **Migration đầy đủ:** `php artisan migrate` (đặc biệt bảng Production + `warehouse_*`, `production_*`).
2. **Assets gốc (Mix):** từ thư mục repo root: `pnpm run production` hoặc `pnpm run dev` — nếu UI thiếu JS/CSS sau khi pull.
3. **Kiểm tra route & wiring tự động (không cần DB login):**

```bash
php artisan test --compact tests/Feature/BiomixingDemoRoutesReadinessTest.php
```

4. **Gói test nghiệp vụ lõi Production + Kho (khuyến nghị trước demo):**

```bash
php artisan test --compact tests/Feature/ProductionPostingServiceTest.php tests/Feature/ProductionFgQuantityPolicyServiceTest.php tests/Unit/ProductionFgInventoryLedgerSyncTest.php tests/Feature/WarehouseReconciliationServiceInventorySnapshotTest.php tests/Feature/WarehouseProductBatchRoutesTest.php
```

(Nếu tất cả pass, coi là “sân” sạch lỗi regression đã được test trong repo.)

---

## 1. Bật module Production cho công ty pilot

Production chỉ vào được khi tenant có quyền module **`production`** (menu + `ProductionTenantAccess`).

- **Cách 1 (UI):** Superadmin / cài đặt module công ty — bật **Production** (`module_settings`: `module_name = production`, `is_allowed = 1`, `status = active`, `type` khớp `admin` và/hoặc `employee`).
- **Cách 2 (SQL ví dụ — chỉ dùng khi bạn đã hiểu bảng `module_settings`):** nhân đôi pattern từ các module khác (`purchase`, …) sang `production`.

User demo cần quyền tối thiểu:

- **`view_*` / `add_*` / `edit_*` production** (order, BOM) theo vai trò thực tế.
- Kho: **`view_warehouse_stock`** cho trang stock, batch inventory, và widget đối soát.

Chi tiết policy FG (controlled / variance): **`/account/production/fg-quantity-policy`** (`production.fg-quantity-policy.index`).

---

## 2. Luồng demo kỹ thuật đề xuất (theo đúng thứ tự nghiệp vụ)

### Phần A — Chuẩn bị RM (mua vào kho)

1. **PO → nhận hàng / GRN** (theo luồng công ty: PO delivered và/hoặc DO nhận — tránh nhập đôi: xem `config('warehouse.inbound_from_purchase_order_delivered')` và `inbound_from_delivery_order_received` trong `Modules/Warehouse/Config/config.php`).
2. Kiểm tra **`/account/warehouse-product-batches`** (`warehouse.product-batches.index`): có **lô RM** và số lượng > 0 tại **kho RM** sẽ dùng cho lệnh SX.

### Phần B — Master BOM + Lệnh + Batch

1. **`/account/production/boms`** — tạo BOM cho **SKU FG**, ít nhất một dòng **component** (RM).
2. **`/account/production/orders/create`** — tạo **Production Order**: chọn BOM, FG, **kho RM / kho FG**, `planned_quantity` > 0, tạo **ít nhất một batch** (mã lô).
3. **Release order** (`production.orders.release`) — có **snapshot BOM** + qty TP đông băng.
4. Mở **batch** (`production.batches.show`):
    - **Create planned RM lines from BOM snapshot** (chia đều theo số batch nếu nhiều lô — xem playbook §1.3).
    - **Gán lô RM** từng dòng (**Assign batch**).
    - **Post consumptions** (trừ tồn RM).
    - **Thêm FG output** và (nếu bật) **Approve variance** rồi **Post FG receipt** (nhập TP + lô FG).

### Phần C — Trace & reconciliation

1. **`/account/production/batches/{id}/trace`** — kiểm tra link sang **Warehouse batch**.
2. **`/account/warehouse-product-batches/{id}`** — movements có link **`openProductionTrace`** khi reference là Production batch.
3. **`/account/warehouse-stock`** — widget **đối soát snapshot vs tổng batch** (ngưỡng: `WAREHOUSE_INVENTORY_RECONCILIATION_*` trong `.env` / `config/warehouse.php`).

### Phần D — Bán và giao (B2B chung Hub)

Áp **`P0_MINI_UAT_CHECKLIST_BIOMIXING_VI.md`** (Estimate→SO; SO→DO→Invoice với shipment/invoice mode; PO→GRN→Bill).

- Lưu ý **`config('warehouse.sales_outbound_mode')`**: **`shipment`** vs **`invoice`** — tránh mong đợi sai chỗ “trừ tồn”.
- Phase2 **quality lock DO** (Production chưa complete chặn ship): `production.phase2.enforce_quality_lock_sales_do` trong `Modules/Production/Config/config.php` — demo nếu bật cần **complete** lệnh SX trước khi ship DO gắn SO đó.

### Phần E — Post FG → Inventory (P1c — bắt buộc kiểm tra sau demo nhập TP)

Sau **Post FG receipt**, tồn phải xuất hiện ở **hai nơi** (khác SSOT):

1. **Warehouse** — `/account/warehouse-product-batches` (lô FG, mã batch ví dụ `PB-xxx`).
2. **Purchase → Inventory** — `/account/purchase-products` hoặc màn **Inventory** (dòng ledger `purchase_inventory_adjustment`; tìm theo **tên SP / SKU**, không theo mã lô).

| Bước | Hành động                                            | Kỳ vọng                                                                                      |
| ---- | ---------------------------------------------------- | -------------------------------------------------------------------------------------------- |
| E1   | Post FG trên batch (đủ policy / variance nếu bật)    | Movement inbound + lô FG trên kho đã chọn                                                    |
| E2   | Mở Inventory list, lọc SP FG + kho `fg_warehouse_id` | Có dòng tồn; `net_quantity` khớp on-hand warehouse (làm tròn hiển thị có thể lệch ε)         |
| E3   | (Ops) Dữ liệu cũ trước P1c                           | `php artisan production:backfill-fg-inventory-ledger --dry-run` rồi chạy thật nếu thiếu dòng |

**Living doc:** `FUNC_IMPROVE/16_PRODUCTION_FG_INVENTORY_LEDGER_SYNC_VI.md` · UAT: `P0_MINI_UAT_CHECKLIST_BIOMIXING_VI.md` **Luồng E**.

### Phần F — Tuỳ chọn Phase 2 (chỉ khi chủ động bật)

- **`production.phase2.enforce_variance_approval`**: FG vượt ngưỡng cần **Approve variance** trước khi post receipt (badge UX: `10_UX_UI_IMPROVEMENT_BACKLOG.md` UX-008).
- **`production.phase2.yield_uom_shadow_enabled`**: chỉ bật khi có sign-off governance (đã có test service khi flag bật).

---

## 3. Troubleshooting ngắn

| Hiện tượng                                          | Hướng xử lý                                                                                                          |
| --------------------------------------------------- | -------------------------------------------------------------------------------------------------------------------- |
| 403 trên `/account/production/*`                    | Bật module `production` + quyền role; có thể cần re-login để rebuild `user_modules`.                                 |
| Không vào được batch / stock                        | Kiểm tra `company_id` và quyền `view_*` tương ứng.                                                                   |
| Planned RM không nút BOM                            | Order phải **released**, có snapshot, batch **chưa** có dòng consumption đã sinh tay.                                |
| Trừ tồn RM lỗi                                      | Kiểm tra `warehouse.allow_negative_stock`, lô RM đủ số trong **đúng kho RM**.                                        |
| Reconciliation báo mismatch liên tục                | Tune `equality_epsilon` / `warning_absolute_delta`; nhớ có thể có làm tròn số hiển thị vs DB.                        |
| FG có trên Stock batches nhưng không thấy Inventory | Đã vá P1c — chạy backfill; tìm SP theo tên/SKU không phải mã lô; xem `16_PRODUCTION_FG_INVENTORY_LEDGER_SYNC_VI.md`. |

---

## 4. Liên kết nhanh trong repo

- **Doc sync Biomixing:** `BIOMIXING_DOCUMENTATION_SYNC_2026_05_VI.md`
- **Audit phase + go-live:** `BIOMIXING_FULL_PROCESS_AUDIT_2026_05_VI.md`
- Playbook Phase 0–1: `BIOMIXING_PLAYBOOK_P0P1_VI.md`
- P0 hành động tuần: `P0_NEXT_ACTION_BIOMIXING_VI.md`
- Demo script stakeholder (ERP+AI): `PROJECT BIOMIXING/BIOMIXING_DEMO_SCRIPT.md`

---

_Biểu mẫu header chuẩn: khi chỉnh nội dung đáng kể, cập nhật dòng **Cập nhật** và kiểm tra lại mục 0._
