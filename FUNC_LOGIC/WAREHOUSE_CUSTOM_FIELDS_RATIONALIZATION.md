# Multi-Warehouse Custom Fields Rationalization (Chi tiet cot nen giu/bo)

Tai lieu nay chot ro: sau khi da co multi-warehouse, cot nao da co trong DB core, cot nao con nen de custom field, va cot nao nen bo.

Phạm vi tham chiếu:

- `PROJECT MAOLIN New/Craveva customer.xlsx`
- `PROJECT MAOLIN New/Craveva product.xlsx`
- `PROJECT MAOLIN New/Quote, unit price, inventory.xlsx` (sheet `產品價格表`, `產品庫存表`)
- `PROJECT MAOLIN New/Craveva full inventory.xlsx` (sheet `庫存明細總表`)

---

## 1) Inventory (quan trong nhat)

### 1.1 Cot core DB da co (khong can de custom field nua)

Bang: `purchase_stock_adjustments`

| Cot                  | Trang thai | Ghi chu                                  |
| -------------------- | ---------- | ---------------------------------------- |
| `warehouse_id`       | Da co core | Khoa kho chinh cho luong multi-warehouse |
| `batch_number`       | Da co core | Da them/cap nhat de thay custom field    |
| `manufacturing_date` | Da co core | Cot ngay SX                              |
| `expiration_date`    | Da co core | Cot han su dung                          |
| `net_quantity`       | Da co core | So luong ton theo dong dieu chinh        |

=> 4 cot warehouse/batch/date tren can duoc coi la nguon du lieu chinh (source of truth), khong dung custom field de van hanh tay.

### 1.2 Custom field nen BO (de thong nhat luong movement)

Nhung field duoi day la snapshot/report theo ky, khong phai input core cho movement:

- `beginning_inventory`
- `inbound_quantity`
- `outbound_quantity`
- `reserved_quantity`
- `near_expiry_status`
- `recent_inbound_date` (neu co)
- `batch_recent_inbound_date` (neu co)
- `beginning_package_inventory`
- `packaging_inbound_quantity`
- `packaging_outbound_quantity`
- `closing_code` (neu khong co quy trinh nghiep vu ro rang)

### 1.3 Custom field GIU TAM (neu dang can cho import legacy)

- `warehouse_code`
- `warehouse_name`

Luu y: hai field nay chi de ho tro import file cu. Muc tieu cuoi cung van la map ve `warehouse_id`.

### 1.4 Custom field co the GIU neu co quy trinh that

- `location_code` (chi giu neu co quan ly bin/shelf/location that su)
- `specification`, `packaging_unit`, `small_unit` (neu team nghiep vu su dung trong kho)

---

## 2) Client

### 2.1 Cot core DB da co

Bang: `client_details`

| Cot                    | Trang thai | Ghi chu                     |
| ---------------------- | ---------- | --------------------------- |
| `client_code`          | Da co core | Khoa import chinh cho khach |
| `pricing_tier_id`      | Da co core | Lien ket pricing tier       |
| `default_warehouse_id` | Da co core | Kho uu tien cua client      |

### 2.2 Nen giu custom field (neu can BI/phan khuc)

- `salesperson`
- `department`
- `sales_assistant_name`
- `customer_grade`
- `channel_type`
- `business_type`
- `last_transaction_at`
- `payment_terms`
- `business_closure_date`

### 2.3 Nen map vao core, khong de text custom field lau dai

- `designated_warehouse_code/name` -> `default_warehouse_id`

---

## 3) Product

### 3.1 Cot da la core (khong can custom field)

Theo code hien tai da co cac cot thuong dung:

- `storage_condition`
- `certification`
- `inventory_type`
- `shelf_life_days`
- `specification`
- `brand`
- `product_grade`

=> Khong tao lai cac cot nay duoi dang custom field.

---

## 4) Quyet dinh de thuc thi ngay

### 4.1 Danh sach xoa custom fields Inventory (khuyen nghi)

1. `beginning_inventory`
2. `inbound_quantity`
3. `outbound_quantity`
4. `reserved_quantity`
5. `near_expiry_status`
6. `recent_inbound_date`
7. `batch_recent_inbound_date`
8. `beginning_package_inventory`
9. `packaging_inbound_quantity`
10. `packaging_outbound_quantity`
11. `closing_code` (neu khong co rule)

### 4.2 Danh sach chua xoa ngay

- `warehouse_code`, `warehouse_name` (giu tam den khi import pipeline hoan toan dung `warehouse_id`)
- `location_code` (chi xoa neu xac nhan khong su dung)

---

## 5) Checklist truoc khi xoa

- [ ] Backup DB
- [ ] Xac nhan form Inventory da nhap/luu duoc `warehouse_id`, `batch_number`, `manufacturing_date`, `expiration_date`
- [ ] Chay test import 1 file mau (>= 50 dong) khong dung cac field se xoa
- [ ] Xac nhan bao cao ton theo kho khong con phu thuoc custom field snapshot

---

## 6) Tinh than van hanh sau khi don

- Multi-warehouse van hanh theo core:
    - kho: `warehouse_id`
    - lo: `batch_number`
    - han: `expiration_date`
    - movement: `stock_movements` + `warehouse_product_batches`
- Custom fields chi giu cho thong tin phu/BI, khong giu vai tro du lieu nghiep vu cot loi.
