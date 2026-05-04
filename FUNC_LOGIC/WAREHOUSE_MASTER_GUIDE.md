# Warehouse Master Guide

**Doc hub (2026-04):** Nghiệp vụ & luồng → [`WAREHOUSE_FLOW_VA_NGHIEP_VU_VI.md`](WAREHOUSE_FLOW_VA_NGHIEP_VU_VI.md) · Runbook & WUP → [`WAREHOUSE_RUNBOOK_AND_UPGRADE_PLAN_VI.md`](../FUNC_IMPROVE/WAREHOUSE_RUNBOOK_AND_UPGRADE_PLAN_VI.md) · Trạng thái PM/QA → [`WAREHOUSE_TOM_TAT_NOI_BO.md`](WAREHOUSE_TOM_TAT_NOI_BO.md) · Mục lục → [`WAREHOUSE_INDEX.md`](WAREHOUSE_INDEX.md) · **UAT E2E** → [`UAT_CHECKLIST_MUA_BAN_KHO_E2E_VI.md`](UAT_CHECKLIST_MUA_BAN_KHO_E2E_VI.md).

---

Tai lieu ky thuat tong hop cho pham vi Warehouse, thay cho cac file:

- `WAREHOUSE_ANALYSIS_AND_PLAN.md`
- `WAREHOUSE_CUSTOM_FIELDS_RATIONALIZATION.md`
- `WAREHOUSE_ERP_FLOW.md`
- `WAREHOUSE_ISSUES_FIXES_AND_NEXT_STEPS.md`
- `WAREHOUSE_MODULE_INTEGRATION_RUNBOOK.md`
- `WAREHOUSE_UI_OPERATIONS_GUIDE.md`

---

## 1) Muc tieu va pham vi

- Chuan hoa nghiep vu da kho theo huong Purchase-centric.
- Dung `StockMovementService` lam cong ghi so ton kho duy nhat (inbound/outbound/transfer).
- Giu ton kho vat ly nhat quan giua movement ledger, batch stock, va tong ton theo kho.

---

## 2) Kien truc luong kho (tom tat)

```
Master data (Product, Client, Vendor)
        |
        v
   Warehouse master
        |
   PO / DO / Inventory / Transfer`
        |
        v
   StockMovementService
        |
   +-------------------------------+
   | warehouse_product_batches     |
   | warehouse_product_stock       |
   | stock_movements               |
   +-------------------------------+
```

Nguyen tac:

- Add/Remove stock: append movement moi, khong ghi de movement cu.
- Transfer: tao cap outbound (kho nguon) + inbound (kho dich) trong 1 transaction.
- Loi 1 buoc -> rollback toan bo.

---

## 3) Ghi chu DB quan trong (dev ops)

### Add Stock ghi vao:

1. `warehouse_product_batches`
2. `warehouse_product_stock`
3. `stock_movements` (1 dong inbound/outbound)

### Transfer Stock ghi vao:

1. `warehouse_product_batches` kho nguon (giam)
2. `warehouse_product_batches` kho dich (tang)
3. `warehouse_product_stock` (sync ca hai kho)
4. `stock_movements` (2 dong: outbound + inbound)

Luu y:

- Khong nen xoa movement trong production.
- Neu dev can reset local DB, can dong bo lai ton tong sau khi xoa tay.

---

## 4) Van hanh UI (quick guide)

URL chinh:

- `/warehouse` - danh sach kho
- `/warehouse-stock` - stock adjustment list
- `/warehouse-transfer` - transfer
- `/warehouse-movements` - movement ledger

Nguyen tac van hanh:

- Tao kho o `All warehouses`.
- Nhap/xuat ton nhanh qua `Stock adjustment`.
- Chuyen kho qua `Transfer stock`.
- Doi soat lich su qua `Stock movements`.

UI hien tai:

- Add Stock va Transfer Stock mo bang right popup.
- Trong sidebar Operations khong hien item menu Add/Transfer rieng (chi de nut trong man stock).

---

## 5) Module + Permission + Entitlement runbook

Muc tieu:

- Dong bo 2 lop:
    - Nwidart module status
    - DB entitlement (`modules`, `module_settings`, `module_in_package`, permission)

Migration da dung:

- `Modules/Warehouse/Database/Migrations/2026_03_25_120000_setup_warehouse_module_permissions_and_activation.php`

Checklist rollout:

1. Backup DB.
2. Deploy code.
3. Chay migrate.
4. `php artisan optimize:clear`.
5. Verify module + permission + module settings.
6. Smoke test warehouse CRUD + stock + transfer.

---

## 6) Hardening va rang buoc an toan

Da ap dung:

- Company context bat buoc.
- Guard warehouse/product phai thuoc company hien tai.
- Transfer from != to.
- Quantity > 0.
- Chan outbound khi khong du ton (thong bao ro available/requested).
- Chan xoa kho khi con stock/batch/movement/reservation.
- Tra loi user-friendly cho ca ajax/non-ajax (khong generic "Something went wrong").

---

## 7) Custom fields - quyet dinh gon

Nguyen tac:

- Du lieu ton kho da kho/lot/expiry phai o core DB, khong dung custom field lam source of truth.
- Custom field chi giu cho metadata BI/legacy neu khong trung cot core.

Khuyen nghi:

- Inventory CF trung voi `warehouse_id`, `batch_number`, `expiration_date`, snapshot ky -> nen bo.
- Product CF trung cot core (`brand`, `product_grade`, `product_source`, ...) -> nen bo.
- Client CF nghiep vu kinh doanh (khong trung core) -> co the giu.

---

## 8) Van de da gap va trang thai hien tai

Da xu ly:

- UI action dropdown warehouse theo pattern Product.
- Sidebar Operations tu dong mo khi vao route warehouse.
- Add Stock/Transfer popup submit duoc.
- Hardening loi nghiep vu va thong bao ro rang.

Con theo doi:

- Bo test tu dong (feature/integration) cho stock flows.
- Scope B (invoice outbound) da trien khai v1 — xem `WAREHOUSE_FLOW_VA_NGHIEP_VU_VI.md` va `WAREHOUSE_TOM_TAT_NOI_BO.md`; van can UAT staging va co the mo rong trigger/kho theo PM.

---

## 9) Checklist test tay de ban giao

- Happy path add/remove/transfer.
- Transfer same warehouse -> bi chan.
- Insufficient stock -> message ro.
- Delete warehouse blocked cases.
- Permission denied.
- Missing company context.
- Popup ajax validation + success redirect.

---

## 10) Lich su tinh gon

- 2026-03: Hoan thien warehouse multi-flow + UI/UX + hardening.
- 2026-03: Gom tai lieu warehouse ve 1 file master de giam roi.
