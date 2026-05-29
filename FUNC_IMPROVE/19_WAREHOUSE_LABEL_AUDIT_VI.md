# Warehouse module — ERP label audit & UI fixes

**Ngày:** 2026-05-29  
**Phạm vi:** `Modules/Warehouse` (menu, list, form, stock, transfer, flow settings, messages).  
**Chuẩn:** `docs/platform-help/02-GLOSSARY.md`, `FUNC_LOGIC/DESIGN_BACKEND_UI_UX_VI.md` §12.

---

## Đã sửa (code)

| Hạng mục   | Thay đổi                                                                                                                                      |
| ---------- | --------------------------------------------------------------------------------------------------------------------------------------------- |
| JS         | Bỏ `const getReadableApiError` trong ajax form (gây lỗi khi mở modal lần 2); dùng `$.handleApiFormError` + partial `ajax-form-submit-script`. |
| Swal       | Toast success sau create/update/transfer/adjustment; flow settings save.                                                                      |
| Controller | `Reply::redirect(..., message key)`; attribute validation dùng `warehouse::app.*`.                                                            |
| Form       | Trang create full-page thêm **Phân loại kho** (`warehouse_type`).                                                                             |

---

## Review label — Current | Suggested | Reason

| Key / vị trí                       | Current (EN)                | Suggested              | Reason                                           |
| ---------------------------------- | --------------------------- | ---------------------- | ------------------------------------------------ |
| `warehouseType`                    | Category                    | Warehouse type         | ERP: “category” dễ nhầm product category.        |
| `warehouseType` (VI)               | Phân loại                   | Loại kho               | Rõ nghiệp vụ kho.                                |
| `flow_inbound_do_received`         | Post inbound… GRN… Received | _(giữ)_                | Đúng compat_v2; không dùng “Delivery Order” mua. |
| `linkDeliveryOrders`               | Open goods receipts (GRN)   | _(giữ)_                | Đúng thuật ngữ GRN.                              |
| `reference_sales_shipment`         | Sales Delivery Order        | _(giữ)_                | Khớp glossary Sales DO.                          |
| `adjustStock`                      | Warehouse Stock Overview    | _(giữ)_                | Phân biệt với product stock chung.               |
| `movementType`                     | Stock In/Out Type           | Movement type          | Ngắn gọn trên filter.                            |
| `description`                      | Notes                       | Notes                  | OK; field mô tả kho.                             |
| `err_module_not_warehouse` (zh-CN) | 当前模块不是仓库模块        | 当前公司未启用仓库模块 | Đúng nghĩa permission/module company.            |
| `warehouseFlowSettingsMenu` (VI)   | Luồng kho & tồn             | Cài đặt kho & tồn kho  | Khớp Settings menu EN.                           |

**Giữ nguyên (OK):** `goods receipt`, `Sales DO`, `stock adjustment`, `transfer`, validation keys `validation_*`, success messages.

---

## Cơ chế LanguagePack

1. Sửa: `Modules/LanguagePack/Languages/modules/Warehouse/{en,vi,zh-CN}/app.php`
2. Publish: `Copy-Item` → `Modules/Warehouse/Resources/lang/{locale}/app.php` hoặc `php artisan languagepack:publish-translation`
3. Form AJAX: `warehouse::partials.ajax-form-submit-script`

---

## Lịch sử

| Ngày       | Ghi chú                                    |
| ---------- | ------------------------------------------ |
| 2026-05-29 | Audit + JS/Swal + label VI/zh-CN một phần. |
