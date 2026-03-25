# Warehouse Module Integration Runbook

## 1) Muc tieu

Dong bo `Warehouse` theo dung kien truc 2 lop module:

1. Global module status (Nwidart `modules_statuses.json`)
2. Package entitlement theo company (`modules`, `module_settings`, `packages.module_in_package`)

Khong thay doi nghiep vu ton kho va khong can thiep vao cac bang stock business.

---

## 2) Nguyen nhan mismatch truoc khi sua

- `Warehouse` da ton tai o lop Nwidart (global on/off).
- Nhung chua duoc setup day du trong DB layer:
    - thieu record `modules.module_name = warehouse`
    - thieu `module_settings` theo company/role
    - thieu permission theo module
    - thieu lien ket vao `module_in_package`
- He thong package sync doc tu DB layer nen Warehouse bi bo sot.

---

## 3) Patch da ap dung

### File migration moi

- `Modules/Warehouse/Database/Migrations/2026_03_25_120000_setup_warehouse_module_permissions_and_activation.php`

### Noi dung migration

1. Tao/bao dam record `warehouse` trong bang `modules` (idempotent).
2. Tao/bao dam permission cua Warehouse (idempotent):
    - `view_warehouses`
    - `add_warehouses`
    - `edit_warehouses`
    - `delete_warehouses`
    - `view_warehouse_stock`
    - `add_warehouse_stock`
    - `edit_warehouse_stock`
    - `delete_warehouse_stock`
    - `manage_warehouse_transfer`
3. Gan cac permission tren cho role `admin` thong qua `permission_role` (idempotent).
4. Bo sung `warehouse` vao `packages.module_in_package` neu package da co `purchase` hoac `products`.
5. Tao `module_settings` cho moi company voi type `admin`, `employee`:
    - `is_allowed = 1`, `status = active` neu package co `warehouse`
    - nguoc lai `is_allowed = 0`, `status = deactive`

### Tinh an toan

- Tat ca thao tac insert deu co check ton tai (`firstOrCreate` / `firstOrNew` / `exists`).
- Khong xoa du lieu.
- Khong sua schema cac bang nghiep vu kho (`warehouses`, `warehouse_product_stock`, `warehouse_product_batches`, `stock_reservations`).

---

## 4) Ket qua verify tren local

### Truoc migrate

- `modules_has_warehouse = false`
- `module_settings_warehouse = 0`
- `packages_with_warehouse_like = 0`

### Sau migrate

- `modules_has_warehouse = true`
- `module_settings_warehouse = 18`
- `module_settings_warehouse_active = 18`
- `permissions_warehouse = 9`
- `packages_with_warehouse_like = 8`
- `warehouses_rows = 0`
- `warehouse_product_stock_rows = 0`

Luu y: so dong bang stock khong doi trong qua trinh patch.

---

## 5) Checklist rollout staging/hub (an toan)

1. Backup DB truoc khi chay:
    - backup full schema + data
    - co diem khoi phuc ro rang
2. Deploy code.
3. Chay duy nhat migration moi:
    - `php artisan migrate --path="Modules/Warehouse/Database/Migrations/2026_03_25_120000_setup_warehouse_module_permissions_and_activation.php" --force`
4. Clear cache:
    - `php artisan optimize:clear`
5. Verify nhanh:
    - `modules` co `warehouse`
    - `module_settings` co `warehouse` theo company
    - package phu hop co `warehouse` trong `module_in_package`
    - vao giao dien package/company de check module sync hien thi dung
6. Smoke test:
    - tao/sua kho
    - nhap/xuat/chuyen kho
    - man hinh stock van hoat dong binh thuong

---

## 6) Rollback strategy

Migration `down()` de rong co chu dich de tranh xoa nham du lieu production.

Neu can rollback, dung backup DB da tao truoc rollout.

---

## 7) Ghi chu kien truc

- Global toggle va package entitlement la hai lop khac nhau.
- Patch nay chi bo sung Warehouse vao DB entitlement layer de dong bo voi logic package/company hien huu.
- Khong thay doi business logic multi-warehouse.

---

## 8) Ghi chu tam thoi (TODO)

**Trang thai:** Integration DB/package (muc 3–5 cua runbook) da co migration; **chuc nang nghiep vu Warehouse van dang hoan thien.**

**Ghi nho khi tiep tuc:**

- Hoan thien luong UI/UX, menu, permission gan voi controller (neu can `user()->permission(...)` cho tung man).
- Kiem tra gan permission vao route/middleware neu chua dung ten permission da tao trong migration.
- Sau khi code xong: smoke test staging (kho, ton kho, chuyen kho, company khac nhau).
- Neu them package/rule moi cho Warehouse: cap nhat `module_in_package` hoac dieu chinh dieu kien append trong migration (chi khi thay doi chinh sach ban hang).

_Cap nhat: tam thoi luu lai de quay lai sau khi hoan thien module Warehouse._
