# FUNC_LOGIC Index

Mục lục rút gọn cho tài liệu nghiệp vụ và lưu ý sử dụng chức năng.

## Module Business Logic

- `MODULE_INDEX.md` — điểm vào cho toàn bộ 26 module.
- `MODULE_PLAYBOOK.md` — nguyên tắc cập nhật file `MODULE_*.md`.
- `MODULE_WAREHOUSE.md`, `MODULE_PURCHASE.md`, `MODULE_PRODUCTION.md`, `MODULE_PRICING.md` — hub module ưu tiên cho luồng kho, mua, sản xuất, pricing.

## Sales / Purchase / Warehouse

- Đọc nhanh theo thứ tự: `SALES_BUSINESS.md` → `WAREHOUSE_BUSINESS.md` → `SALES_FULFILLMENT_SCHEMA_MATRIX.md` → `SALES_FULFILLMENT_QA_CHECKLIST.md` → `SALES_PURCHASE_WAREHOUSE_UAT_CHECKLIST.md`.
- `SALES_BUSINESS.md` — quy trình PO / GRN / SO / Sales DO / Invoice / Warehouse.
- `SALES_FULFILLMENT_SCHEMA_MATRIX.md` — schema canonical, Sales DO/GRN, legacy/cutover.
- `SALES_FULFILLMENT_QA_CHECKLIST.md` — trạng thái QA và test coverage hiện tại.
- `SALES_PURCHASE_WAREHOUSE_UAT_CHECKLIST.md` — checklist UAT end-to-end.
- `SALES_RETURN_BUSINESS.md` — trả hàng bán / Credit Note và tồn kho.
- `PURCHASE_RETURN_BUSINESS.md` — trả hàng mua / Vendor Credit và tồn kho.
- Sơ đồ E2E: `DIAGRAM/pis_e2e_current.mmd` (đầy đủ) và `DIAGRAM/pis_e2e.mmd` (rút gọn).

## Warehouse

- `MODULE_WAREHOUSE.md` — điểm vào Warehouse theo chuẩn module.
- `WAREHOUSE_BUSINESS.md` — nghiệp vụ kho, inbound/outbound, hạn chế.
- `WAREHOUSE_MASTER_GUIDE.md` — master guide vận hành/kỹ thuật Warehouse.

## Master Data

- `CLIENT_BUSINESS.md` — tạo/import client, custom field, duplicate `client_code`.
- `PRODUCT_BUSINESS.md` — tạo/import product, loại hàng, SKU, custom field.
- `INVENTORY_BUSINESS.md` — thêm/sửa phiếu điều chỉnh tồn.
- `PRICING_BUSINESS.md` — pricing tier, client/product pricing, priority.
- `../docs/AUTH_USERS_CLIENT_FLOW.md` — users/client/login flow và schema kỹ thuật.

## Production

- `PRODUCTION_BUSINESS.md` — loại sản phẩm, BOM, lifecycle, reserve/release/consume/complete, quy tắc thuật ngữ code vs UI.

## Maolin / AI

- `MAOLIN_BUSINESS.md` — điểm vào và master guide Maolin/Miaolin.
- `MAOLIN_IMPORT_MAPPING.md` — mapping import Maolin.
- `AI_ORDER_REST_RUNBOOK.md` — inbound AI/third-party order REST.

## Maintenance

- `README.md` — nguyên tắc thư mục.
- `LEGACY_ARCHIVE.md` — lịch sử file đã gộp/xóa/chuyển nơi khác.
