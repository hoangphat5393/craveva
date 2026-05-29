# App sidebar — tách Operations (Phương án A)

**Ngày:** 2026-05-29  
**Trạng thái:** **Done** (UX-009)  
**Ràng buộc:** Giữ menu **2 cấp** (accordion L1 + link L2), không thêm cấp 3.

---

## Vấn đề

Một accordion **Operations** chứa ~16 mục (mua, bán, sản phẩm, kho, sản xuất) → scroll dài, khó scan.

## Giải pháp (Phương án A)

Tách thành **4 accordion cấp 1** (cùng cấp với Home, Work Management, Sales CRM):

| L1 (key)                      | L2                                                         |
| ----------------------------- | ---------------------------------------------------------- |
| `app.menu.procurement`        | Vendor, PO, GRN, Bills, Vendor payments, Vendor credits    |
| `app.menu.salesFulfillment`   | Sales orders, Sales DO, Sales history                      |
| `app.menu.inventoryWarehouse` | Products, Inventory, Warehouses, Stock overview, Movements |
| `app.menu.productionHub`      | Production orders, BOM                                     |

Mỗi nhóm chỉ hiện khi user có **ít nhất một** quyền/mục trong nhóm.

## File

| File                                                          | Ghi chú                                                |
| ------------------------------------------------------------- | ------------------------------------------------------ |
| `Modules/Purchase/Resources/views/sections/sidebar.blade.php` | Implement                                              |
| `Modules/LanguagePack/Languages/app/{en,vi,zh-CN}/app.php`    | Key menu mới                                           |
| `resources/views/sections/menu.blade.php`                     | `@includeIf('purchase::sections.sidebar')` (không đổi) |

Key cũ `app.menu.operations` **giữ** (tài liệu / tương thích); UI không còn dùng một accordion “Operations”.

## Active state

- Procurement: `vendors.*`, `purchase-order.*`, `grn.*`, `delivery-orders.*`, `bills.*`, `vendor-payments.*`, `vendor-credits.*`
- Sales & fulfillment: `orders.*`, `sales-do.*`, `sales-shipments.*`, `sales-history.*`
- Inventory & warehouse: `purchase-products.*`, `purchase-inventory.*`, `warehouse.*`
- Production: `production.*`

## Lịch sử

| Ngày       | Ghi chú                                   |
| ---------- | ----------------------------------------- |
| 2026-05-29 | Implement Phương án A + lang en/vi/zh-CN. |
