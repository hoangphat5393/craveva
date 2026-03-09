# Chỗ chọn / nhập thuế công ty (Company Tax)

## 1. Thuế **công ty** (company tax – 統一編號)

Thuế công ty được cấu hình ở **hai nơi**:

### A. E-Invoice Settings (dùng cho E-Invoice)

- **Đường dẫn:** Menu → **E-Invoice** → **E-Invoice Settings** (hoặc Settings → E-Invoice Settings tùy menu).
- **Trường:** **Company ID (Tax ID / 統一編號)** và **Company ID Scheme**.
- Đây là mã số thuế công ty dùng khi xuất E-Invoice.

### B. Business Address (địa chỉ kinh doanh)

- **Đường dẫn:** **Settings** → **Business Addresses** → Thêm/Sửa địa chỉ.
- **Trường:** **Tax** (`tax_number`) và **Tax Name** (`tax_name`).
- Dùng cho địa chỉ công ty, in hóa đơn, v.v.

---

## 2. Import Client – Cột "統一編號 | Tax ID" (thuế **khách hàng**)

Khi import client, nếu file Excel có cột **統一編號** hoặc **Tax ID** (mã số thuế **khách hàng**):

- Trong bước map cột, chọn **"GST/VAT Number (Tax ID)"** trong dropdown **Column Name**.
- Trường hệ thống tương ứng là `gst_number` (thuế khách hàng), không phải thuế công ty.

---

## Tóm tắt

| Mục đích              | Nơi cấu hình                         | Trường / Option                         |
|------------------------|---------------------------------------|-----------------------------------------|
| Thuế công ty (E-Invoice) | E-Invoice Settings                    | Company ID (Tax ID / 統一編號)           |
| Thuế công ty (địa chỉ)  | Settings → Business Addresses         | Tax, Tax Name                           |
| Thuế khách hàng (import) | Import Client → map cột               | Chọn **GST/VAT Number (Tax ID)**        |
