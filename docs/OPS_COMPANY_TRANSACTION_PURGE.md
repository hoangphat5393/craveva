# Purge dữ liệu vận hành theo `company_id`

**Cập nhật:** 2026-07-04  
**Command:** `php artisan company:purge-transactions`  
**Code:** `app/Services/Company/CompanyTransactionPurge*.php`, `app/Console/Commands/CompanyPurgeTransactionsCommand.php`
**PowerShell wrapper:** `scripts/purge_company_operational_data.ps1`

---

## 1. Mục tiêu

| Giữ | Xóa |
|-----|-----|
| Client, employee, product, vendor, warehouse, project, task | SO, Invoice, Sales DO, GRN, PO, tồn, lệnh SX |
| `estimate_templates` | `estimates`, `estimate_bom_lines` |
| `product_unit_conversions`, `product_sku_sequences` | `stock_*`, `purchase_stock_adjustments` |
| Bank transaction, time log, product/vendor history | Production BOM khi có `--include-boms` |

`production_boms` và `production_bom_items` mặc định được giữ. Chỉ xóa khi truyền `--include-boms`.

---

## 2. An toàn (bắt buộc)

1. **Mặc định = dry-run** — không có cờ `--execute` thì **không DELETE**.
2. **`--execute`** cần thêm:
   - `COMPANY_PURGE_ALLOW_EXECUTE=true` trong `.env`
   - `--confirm-token=PURGE-{id}-{slug-tên-company}`
   - Xác nhận `yes` trên prompt
3. **Backup** trước khi execute (`backup\*.sql.gz`).
4. **Không** chạy trên production trừ khi PM cho phép.
5. Sau execute: `php artisan cache:clear`.
6. Toàn bộ DELETE chạy trong một DB transaction; lỗi ở bất kỳ phase nào sẽ rollback toàn bộ.
7. Command không tắt foreign keys và không truncate table.

---

## 3. Cách dùng

```powershell
# Chỉ đếm dòng (an toàn — dùng sau import DB)
php artisan company:purge-transactions --company-id=3

# Dry-run gồm cả Production BOM master
php artisan company:purge-transactions --company-id=3 --include-boms

# Xóa thật (chỉ khi đã backup + hiểu rủi ro)
# .env: COMPANY_PURGE_ALLOW_EXECUTE=true
php artisan company:purge-transactions --company-id=3 --execute --confirm-token=PURGE-3-ten-cong-ty

# Xóa thật gồm BOM; token khác để tránh dùng nhầm token dry-run thường
php artisan company:purge-transactions --company-id=3 --include-boms --execute --confirm-token=PURGE-3-ten-cong-ty-WITH-BOMS
```

PowerShell wrapper tương đương:

```powershell
# Dry-run, gồm BOM
.\scripts\purge_company_operational_data.ps1 -CompanyId 3 -IncludeBoms

# Execute: vẫn cần COMPANY_PURGE_ALLOW_EXECUTE=true và prompt xác nhận
.\scripts\purge_company_operational_data.ps1 -CompanyId 3 -IncludeBoms -Execute -ConfirmToken 'PURGE-3-ten-cong-ty-WITH-BOMS'
```

Log execute: `storage/logs/company-purge-{id}_*.log`

---

## 4. Thứ tự phase (trong code)

| Phase | Nhóm |
|-------|------|
| A | Tồn, reservation, inventory adjustment |
| B | Production runtime |
| C | GRN, PO, vendor payments/credits |
| D | Sales DO |
| E | Payments, invoices, credit notes |
| F | Sales orders, carts |
| G | Estimates (+ BOM dòng BG, approval) |
| I | Production BOM master, chỉ khi có `--include-boms` |

Bảng không tồn tại trên DB → bỏ qua (`table_missing`).

## 5. Bảng được giữ ngoài allowlist

Command không xóa: `companies`, users/client/employee, products, vendors, warehouses, projects/tasks, settings, templates, unit conversions, bank transactions, project time logs và product/vendor master histories. Các FK `invoice_id`/`payment_id` nullable trên bank transaction/time log được DB xử lý theo `ON DELETE SET NULL`.

File vật lý trong storage không bị xóa vì command dùng query builder để tránh chạy observer ngoài transaction. Nếu cần dọn file orphan, phải audit và chạy công cụ riêng.

Các bảng ảnh dòng chứng từ (`*_item_images`) được xóa bằng foreign-key cascade khi item cha bị xóa; dry-run không cộng riêng số dòng cascade này vào tổng matched.

---

## 6. Test script không làm hư DB

```powershell
# Feature test dùng SQLite :memory: — không đụng MySQL local
php artisan test --compact tests/Feature/CompanyPurgeTransactionsCommandTest.php
```

Không chạy `--execute` trên DB vừa restore trừ khi đã backup và cố ý purge.

---

## 7. Liên quan

- [`SALES_FULFILLMENT_SCHEMA_MATRIX.md`](../FUNC_LOGIC/SALES_FULFILLMENT_SCHEMA_MATRIX.md)
- [`BUG_SOCIAL_AUTH_MAC.md`](../FUNC_BUG/BUG_SOCIAL_AUTH_MAC.md) — lỗi APP_KEY sau import DB
