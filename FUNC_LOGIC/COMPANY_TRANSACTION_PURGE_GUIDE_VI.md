# Purge giao dịch theo `company_id` (GIỮ master · XÓA chứng từ/tồn)

**Cập nhật:** 2026-05-21  
**Command:** `php artisan company:purge-transactions`  
**Code:** `app/Services/Company/CompanyTransactionPurge*.php`, `app/Console/Commands/CompanyPurgeTransactionsCommand.php`

---

## 1. Mục tiêu

| Giữ | Xóa |
|-----|-----|
| Client, employee, product, vendor, warehouse, **production_boms** | SO, Invoice, Sales DO, GRN, PO, tồn, lệnh SX |
| `estimate_templates` | `estimates`, `estimate_bom_lines` |
| `product_unit_conversions`, `product_sku_sequences` | `stock_*`, `purchase_stock_adjustments` |

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

---

## 3. Cách dùng

```powershell
# Chỉ đếm dòng (an toàn — dùng sau import DB)
php artisan company:purge-transactions --company-id=3

# Xóa thật (chỉ khi đã backup + hiểu rủi ro)
# .env: COMPANY_PURGE_ALLOW_EXECUTE=true
php artisan company:purge-transactions --company-id=3 --execute --confirm-token=PURGE-3-ten-cong-ty
```

Log execute: `storage/logs/company-purge-{id}_*.log`

---

## 4. Thứ tự phase (trong code)

| Phase | Nhóm |
|-------|------|
| A | Tồn, reservation, inventory adjustment |
| B | Production runtime (giữ BOM master) |
| C | GRN, PO, vendor payments/credits |
| D | Sales DO |
| E | Payments, invoices, credit notes |
| F | Sales orders, carts |
| G | Estimates (+ BOM dòng BG, approval) |
| H | Purchase histories |

Bảng không tồn tại trên DB → bỏ qua (`table_missing`).

---

## 5. Test script không làm hư DB

```powershell
# Feature test dùng SQLite :memory: — không đụng MySQL local
php artisan test --compact tests/Feature/CompanyPurgeTransactionsCommandTest.php
```

Không chạy `--execute` trên DB vừa restore trừ khi đã backup và cố ý purge.

---

## 6. Liên quan

- [`ERP_SO_PO_DO_GRN_SCHEMA_MATRIX_VI.md`](./ERP_SO_PO_DO_GRN_SCHEMA_MATRIX_VI.md)
- [`SOCIAL_AUTH_SETTINGS_MAC_INVALID_FIX.md`](../FUNC_BUG/SOCIAL_AUTH_SETTINGS_MAC_INVALID_FIX.md) — lỗi APP_KEY sau import DB
