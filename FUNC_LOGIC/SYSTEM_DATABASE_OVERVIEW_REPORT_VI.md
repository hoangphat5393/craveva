# Báo cáo tổng quan cơ sở dữ liệu — Hệ thống Craveva

**Ngày lập:** 2026-03-24  
**Phạm vi:** Mô tả kiến trúc dữ liệu theo mã nguồn (migrations), không thay cho audit bảo mật hay số liệu runtime trên server production.

---

## 1. Mục đích báo cáo

Báo cáo này giúp trả lời nhanh các câu hỏi:

- Hệ thống dùng **DB gì**, **quản lý schema** thế nào?
- Dữ liệu được **chia theo miền nghiệp vụ** nào (CRM, kho, mua hàng, nhân sự, …)?
- **Đi đâu** để xem chi tiết quan hệ bảng hoặc luồng PO / DO / Invoice?

Số liệu **dung lượng / số dòng thực tế** trên DB cần chạy truy vấn trên môi trường tương ứng (mục 6).

---

## 2. Tổng quan kỹ thuật

| Hạng mục                       | Giá trị (theo `config/database.php` & codebase)                                               |
| ------------------------------ | --------------------------------------------------------------------------------------------- |
| **Engine mặc định**            | MySQL (`DB_CONNECTION` mặc định `mysql`)                                                      |
| **Bảng / charset**             | InnoDB, `utf8mb4` / `utf8mb4_unicode_ci`                                                      |
| **Quản lý schema**             | Laravel migrations: `database/migrations/` + `Modules/*/Database/Migrations/`                 |
| **Ước lượng quy mô migration** | ~372 file trong `database/migrations`, ~259 file trong các module (tổng ~630+ file migration) |
| **Cấu trúc app**               | Monolith Laravel + **nwidart/laravel-modules** (`Modules/`)                                   |

**Lưu ý:** Một số migration gói nhiều `Schema::create` trong một file (ví dụ bản nâng cấp SaaS), nên **số file migration ≠ số bảng**; tổng số bảng thực tế lớn hơn và nên đối chiếu bằng `information_schema` trên DB (mục 6).

---

## 3. Phân vùng nghiệp vụ (logical domains)

Dữ liệu có thể nhóm theo **miền** sau (mỗi miền gồm nhiều bảng; tên bảng minh họa):

| Miền                              | Vai trò                                           | Ví dụ nhóm bảng / thực thể                                                                                                                                                                                   |
| --------------------------------- | ------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| **Đa tenant / SaaS**              | Công ty, gói, cài đặt toàn cục                    | `companies`, `users`, `packages`, `global_settings`, …                                                                                                                                                       |
| **Khách hàng & bán hàng**         | Client, lead, deal, báo giá, hợp đồng             | `client_details`, `leads`, `deals`, `estimates`, `contracts`, `invoices`, `invoice_items`, …                                                                                                                 |
| **Sản phẩm & định giá**           | SKU, tier, giá theo khách                         | `products`, `pricing_tiers`, `client_product_pricing`, … (module Pricing)                                                                                                                                    |
| **Mua hàng & nhà cung cấp**       | PO, vendor                                        | `purchase_orders`, `purchase_vendors`, `purchase_settings`, … (module Purchase)                                                                                                                              |
| **Kho & tồn**                     | Đa kho, lô, dự trữ, luân chuyển                   | `warehouses`, `warehouse_product_stock`, `warehouse_product_batches`, `stock_reservations`, `stock_movements`, … (module Warehouse)                                                                          |
| **Giao hàng / nhận (Purchase)**   | Phiếu nhận mua (GRN); phiếu giao bán (Sales DO)   | **`grns`**, **`grn_items`**; **`sales_dos`**, **`sales_do_items`** (bảng `delivery_orders*` / `sales_shipments*` legacy đã DROP trên env đã migrate — xem `ERP_SO_PO_DO_GRN_SCHEMA_MATRIX_VI.md`) |
| **Dự án & công việc**             | Project, task, time log                           | `projects`, `tasks`, `timelogs`, …                                                                                                                                                                           |
| **Nhân sự & chấm công**           | Nhân viên, ca, phép, payroll                      | Bảng HR trong core + module Payroll, Biometric, …                                                                                                                                                            |
| **Tuyển dụng, tài sản, tích hợp** | Recruit, Asset, Zoom, SMS, Webhooks, E-Invoice, … | Mỗi module có bảng cấu hình & nghiệp vụ riêng                                                                                                                                                                |

Luồng **B2B: Client → PO → nhập kho → DO → Invoice** được mô tả trong:

- [QUY_TRINH_PO_DO_SO_INVOICE_WAREHOUSE_VI.md](QUY_TRINH_PO_DO_SO_INVOICE_WAREHOUSE_VI.md)

Luồng **multi-warehouse / lô / hạn**:

- [WAREHOUSE_MASTER_GUIDE.md](WAREHOUSE_MASTER_GUIDE.md)

---

## 4. Module Laravel gắn với DB (thư mục `Modules/`)

Các package có `module.json` (ví dụ): **Warehouse**, **Purchase**, **Pricing**, **Payroll**, **Recruit**, **Asset**, **Zoom**, **Webhooks**, **EInvoice**, **Affiliate**, **Biometric**, **CyberSecurity**, **DeveloperTools**, **LanguagePack**, **Letter**, **LineIntegration**, **Onboarding**, **Performance**, **Policy**, **ProjectRoadmap**, **QRCode**, **ServerManager**, **Sms**, **Subdomain**, **Biolinks**.

Mỗi module thường có thêm migrations riêng → **schema tổng hợp = core + các module được bật trên từng tenant**.

---

## 5. Tài liệu chi tiết đã có trong repo

| Tài liệu                                                                                                     | Nội dung                                                                   |
| ------------------------------------------------------------------------------------------------------------ | -------------------------------------------------------------------------- |
| [DATABASE_REPORT_USERS_CLIENT_TABLES_RELATIONSHIPS.md](DATABASE_REPORT_USERS_CLIENT_TABLES_RELATIONSHIPS.md) | Quan hệ `users` ↔ `client_details` / client (có phần kiểm tra dữ liệu mẫu) |
| [Libraries_And_Module_Names.md](Libraries_And_Module_Names.md)                                               | Thư viện Composer, tên module trong `modules` / package                    |
| [README.md](README.md)                                                                                       | Mục lục FUNC_LOGIC                                                         |

---

## 6. Cách lấy số liệu thực tế trên MySQL (gợi ý cho IT / DBA)

Chạy trên **staging hoặc production** (có quyền đọc), thay `your_database`:

```sql
-- Số bảng trong schema
SELECT COUNT(*) AS table_count
FROM information_schema.tables
WHERE table_schema = 'your_database' AND table_type = 'BASE TABLE';

-- Dung lượng theo bảng (ước lượng)
SELECT
  table_name,
  ROUND((data_length + index_length) / 1024 / 1024, 2) AS size_mb_approx,
  table_rows AS row_estimate
FROM information_schema.tables
WHERE table_schema = 'your_database' AND table_type = 'BASE TABLE'
ORDER BY (data_length + index_length) DESC
LIMIT 30;
```

**Lưu ý:** `table_rows` với InnoDB là **ước lượng**; cần số chính xác dùng `COUNT(*)` từng bảng.

---

## 7. Bảo trì & phiên bản schema

- Mọi thay đổi cấu trúc chuẩn nên đi qua **migration** và quy trình deploy (staging → production).
- Khi báo cáo cho lãnh đạo, có thể kèm: **phiên bản app**, **ngày migrate gần nhất**, và **top 10 bảng lớn nhất** (mục 6).

---

## 8. Hạn chế của báo cáo này

- Không liệt kê đủ **tên từng bảng** (quá lớn; nên dùng `information_schema` hoặc công cụ ERD).
- Không thay thế **kiểm tra bảo mật** (quyền user DB, backup, mã hóa).
- Số migration là **snapshot theo mã nguồn** tại ngày lập; nhánh git khác có thể khác.

---

_Tài liệu này có thể đính kèm email nội bộ hoặc chỉnh sửa phần 1–3 thành slide tóm tắt cho sếp._
