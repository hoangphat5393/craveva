# FUNC_LOGIC — logic nghiệp vụ & lưu ý vận hành

Thư mục này chỉ giữ tài liệu giúp hiểu **nghiệp vụ**, **luồng sử dụng chức năng**, và **lưu ý khi vận hành/UAT**.

Không để trong thư mục này:

- kế hoạch dev chưa làm xong → `FUNC_IMPROVE/`
- bug, audit lỗi, security review → `FUNC_BUG/`
- tài liệu kỹ thuật nền, UI standard, API/schema/system ops → `docs/`
- collateral dự án / proposal / hình / sơ đồ gốc → thư mục dự án tương ứng

## Điểm vào chính

| Nhu cầu | Mở file |
| --- | --- |
| Mua / bán / kho tổng thể | `SALES_BUSINESS.md` |
| SO / PO / Invoice / Stock theo code | `SALES_BUSINESS.md` §5.1 |
| Schema và cutover Sales DO / GRN | `SALES_FULFILLMENT_SCHEMA_MATRIX.md` |
| UAT mua · bán · kho | `SALES_PURCHASE_WAREHOUSE_UAT_CHECKLIST.md` |
| QA hiện trạng SO / PO / DO / Invoice / Warehouse | `SALES_FULFILLMENT_QA_CHECKLIST.md` |
| Module logic theo từng module | `MODULE_INDEX.md`, rồi mở `MODULE_*.md` |
| Warehouse | `MODULE_WAREHOUSE.md`, rồi `WAREHOUSE_BUSINESS.md` |
| Client / Product / Inventory / Pricing | `CLIENT_BUSINESS.md`, `PRODUCT_BUSINESS.md`, `INVENTORY_BUSINESS.md`, `PRICING_BUSINESS.md`; auth/schema kỹ thuật ở `../docs/AUTH_USERS_CLIENT_FLOW.md` |
| Production / BOM | `PRODUCTION_BUSINESS.md` |
| Maolin / Miaolin | `MAOLIN_BUSINESS.md`, chi tiết mapping ở `MAOLIN_IMPORT_MAPPING.md` |
| AI order REST | `AI_ORDER_REST_RUNBOOK.md` |

## Tài liệu đã chuyển ra ngoài

| Loại | Nơi đọc |
| --- | --- |
| Import chunk/bulk, dev plan, unfinished improvements | `FUNC_IMPROVE/` |
| Bug/security/tech review | `FUNC_BUG/` |
| API reference, DB/system overview, UI standard, LanguagePack/custom fields, ops scripts | `docs/` |

## Bảo trì

- Cập nhật mục lục ngắn ở `INDEX.md`.
- Khi xóa/gộp file, ghi vào `LEGACY_ARCHIVE.md`.
- Không thêm plan/audit kỹ thuật mới vào đây nếu không phải logic nghiệp vụ hoặc lưu ý vận hành chức năng.
