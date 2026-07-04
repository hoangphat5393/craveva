# FUNC_LOGIC — Legacy index (pass 6, 2026-05-27)

Các file dưới đây **đã xóa** — chức năng đã có trong living doc hoặc chỉ là snapshot audit một lần. Tra cứu lịch sử: `git log -- <path>`.

## Pass 26 (2026-07-04) — gộp module hub và xóa index trùng

| File đã xóa | Đọc thay |
| ----------- | -------- |
| `WAREHOUSE_INDEX.md` | `MODULE_WAREHOUSE.md`, `WAREHOUSE_BUSINESS.md`, `WAREHOUSE_MASTER_GUIDE.md` |

## Pass 6 — module audit snapshots (đã xóa)

| File đã xóa                             | Đọc thay                                                    |
| --------------------------------------- | ----------------------------------------------------------- |
| `AUDIT_WAREHOUSE_MODULE.md`          | `WAREHOUSE_MASTER_GUIDE.md`, `SALES_FULFILLMENT_QA_CHECKLIST.md` |
| `AUDIT_PURCHASE_MODULE.md`           | `SALES_BUSINESS.md`                |
| `AUDIT_SALES_DO_FUNCTIONAL.md`       | `INDEX.md`, `SALES_FULFILLMENT_QA_CHECKLIST.md`              |
| `AUDIT_REPORTS_ERP.md`               | `../FUNC_BUG/REVIEW_ERP_TECH.md`                              |
| `AUDIT_BILLING_MODULE.md`            | —                                                           |
| `AUDIT_PERFORMANCE_MODULE.md`        | —                                                           |
| `AUDIT_WEBHOOKS_MODULE.md`           | `docs/AI_ORDER_REST.md`, `docs/AI_ORDER_REST_SETUP.md` |
| `AUDIT_AI_ORDER_INBOUND_SO_API.md`   | `docs/AI_ORDER_REST.md`                         |
| `AUDIT_LOGIC_2026.md`                | `INDEX.md`                                                  |
| `AI_ORDER_REST_API_RUNTIME_AUDIT.md` | `docs/AI_ORDER_REST.md`                         |
| `CF_SYSTEMWIDE_AUDIT.md`             | `../docs/SYSTEM_MODULE_LANGUAGEPACK_CUSTOM_FIELDS.md`      |
| `SUPERADMIN_PACKAGE_AUDIT.md`        | `../docs/SYSTEM_MODULE_LANGUAGEPACK_CUSTOM_FIELDS.md`      |
| `multi_warehouse_audit_report.md`       | `WAREHOUSE_MASTER_GUIDE.md`                                 |
| `PROJECT_MAOLIN_NEW_FILES_ANALYSIS.md`  | `MAOLIN_BUSINESS.md`                                    |
| `DEVELOPER_TOOLS_AUDIT_AND_FLOW.md`  | `../FUNC_IMPROVE/DEVTOOLS_DB_LOGGING_PLAN.md`                               |

## Pass 13 (2026-05-27) — rút gọn

| File                                  | Thay đổi                                                                              |
| ------------------------------------- | ------------------------------------------------------------------------------------- |
| `PRODUCT_IMPORT_SLOWNESS_ANALYSIS.md` | Rút gọn, sau đó retire 2026-06-17 → `../FUNC_IMPROVE/IMPORT_CHUNK_BULK_QUEUE.md`, `FUNC_IMPROVE/IMPORT_POLL_TRACKERS.md` |

## FUNC_IMPROVE / FUNC_BUG retirements

Xem [`../FUNC_IMPROVE/LEGACY_ARCHIVE.md`](../FUNC_IMPROVE/LEGACY_ARCHIVE.md).

## Pass 23 (2026-06-22) — chuẩn hóa tên file business logic

Các file dưới đây **không xóa nội dung**, chỉ đổi tên để file nghiệp vụ chính có chữ `BUSINESS` và dễ phân biệt với schema/checklist/runbook.

| Tên cũ | Tên mới |
| ------ | ------- |
| `SALES_FULFILLMENT_PROCESS.md` | `SALES_BUSINESS.md` |
| `WAREHOUSE_BUSINESS_FLOW.md` | `WAREHOUSE_BUSINESS.md` |
| `CLIENT_CREATE_FLOW.md` | `CLIENT_BUSINESS.md` |
| `PRODUCT_CREATE_FLOW.md` | `PRODUCT_BUSINESS.md` |
| `INVENTORY_ADJUSTMENT_FLOW.md` | `INVENTORY_BUSINESS.md` |
| `PRICING_MODULE_FLOW.md` | `PRICING_BUSINESS.md` |
| `PRODUCTION_BUSINESS.md` | `PRODUCTION_BUSINESS.md` |
| `PRODUCTION_PRODUCT_TYPES.md` | `PRODUCTION_BUSINESS.md` |
| `SALES_RETURN_STOCK_FLOW.md` | `SALES_RETURN_BUSINESS.md` |
| `PURCHASE_RETURN_STOCK_FLOW.md` | `PURCHASE_RETURN_BUSINESS.md` |
| `MAOLIN_MASTER_GUIDE.md` | `MAOLIN_BUSINESS.md` |

## Pass 24 (2026-06-22) — gộp Production type vào Production business

| File đã xóa | Đọc thay |
| ----------- | -------- |
| `PRODUCTION_TYPES_BUSINESS.md` | `PRODUCTION_BUSINESS.md` §1 |

## Pass 18 (2026-06-17) — retire AI legacy webhook note

| File đã xóa | Đọc thay |
| ----------- | -------- |
| `AI_ORDER_LEGACY_WEBHOOK_REMOVED.md` | `docs/AI_ORDER_REST.md`, `docs/AI_ORDER_REST_SETUP.md`, `AI_ORDER_REST_RUNBOOK.md` |

## Pass 19 (2026-06-20) — retire rollout notes đã nhập vào living doc

| File đã xóa | Đọc thay |
| ----------- | -------- |
| `CLIENT_INLINE_VALIDATION_ROLLOUT.md` | `../docs/UI_BACKEND_UX_STANDARD.md` §12 |

## Pass 20 (2026-06-20) — xóa bản EN để giữ FUNC_LOGIC tiếng Việt

| File đã xóa | Đọc thay |
| ----------- | -------- |
| `PRODUCTION_BUSINESS_EN.md` | `PRODUCTION_BUSINESS.md` |
| `PRODUCTION_PRODUCT_TYPES_EN.md` | `PRODUCTION_BUSINESS.md` |
| `CUSTOMER_API_REQUIREMENTS_EN.md` | Đã bỏ cùng `CUSTOMER_AI_DATA_REQUIREMENTS.md`; nội dung API/data dùng `../docs/API_SYSTEM_REFERENCE.md` |

## Pass 21 (2026-06-20) — gộp file nhỏ vào living doc

| File đã xóa | Đọc thay |
| ----------- | -------- |
| `CLIENT_USER_1_1_SCHEMA_NOTE.md` | `CLIENT_BUSINESS.md` §11 |
| `DEMO_SO_DO_PO_INVOICE.md` | `SALES_PURCHASE_WAREHOUSE_UAT_CHECKLIST.md` Phụ lục D |
| `SALES_FULFILLMENT_REFACTOR_PROMPT.md` | `FUNC_IMPROVE/05_SO_DO_PO_GRN_REFACTOR.md`, `SALES_FULFILLMENT_SCHEMA_MATRIX.md` |

## Pass 22 (2026-06-20) — chuyển file không thuộc nghiệp vụ ra khỏi FUNC_LOGIC

| File cũ trong `FUNC_LOGIC` | Đọc thay |
| ----------- | -------- |
| `DEVTOOLS_DB_LOGGING_PLAN.md` | `../FUNC_IMPROVE/DEVTOOLS_DB_LOGGING_PLAN.md` |
| `IMPORT_CHUNK_BULK_QUEUE.md` | `../FUNC_IMPROVE/IMPORT_CHUNK_BULK_QUEUE.md` |
| `MAOLIN_DIGIWIN_ORDER_EXPORT_TEMPLATE.md` | `../FUNC_IMPROVE/MAOLIN_DIGIWIN_ORDER_EXPORT_TEMPLATE.md` |
| `SECURITY_REVIEW_VERIFICATION.md` | `../FUNC_BUG/BUG_SECURITY_REVIEW.md` |
| `SYSTEM_ERP_TECH_REVIEW.md` | `../FUNC_BUG/REVIEW_ERP_TECH.md` |
| `API_SYSTEM_REFERENCE.md` | `../docs/API_SYSTEM_REFERENCE.md` |
| `DB_SYSTEM_OVERVIEW.md` | `../docs/DB_SYSTEM_OVERVIEW.md` |
| `ENV_LOCAL_SERVER_HOSTNAMES.md` | `../docs/ENV_LOCAL_SERVER_HOSTNAMES.md` |
| `OPS_COMPANY_TRANSACTION_PURGE.md` | `../docs/OPS_COMPANY_TRANSACTION_PURGE.md` |
| `SYSTEM_LIBRARIES_AND_MODULE_NAMES.md` | `../docs/SYSTEM_LIBRARIES_AND_MODULE_NAMES.md` |
| `SYSTEM_MODULE_LANGUAGEPACK_CUSTOM_FIELDS.md` | `../docs/SYSTEM_MODULE_LANGUAGEPACK_CUSTOM_FIELDS.md` |
| `UI_BACKEND_UX_STANDARD.md` | `../docs/UI_BACKEND_UX_STANDARD.md` |
| `UI_FRONTEND_LAYOUT_JS.md` | `../docs/UI_FRONTEND_LAYOUT_JS.md` |
| `MIAOLIN_SO_API_FIELDS.md` | `../docs/MIAOLIN_SO_API_FIELDS.md` |
| `FILE_CLASSIFICATION.md` | `INDEX.md` |
| `GLOSSARY_PURCHASE_ERP_VI.json` | `../docs/GLOSSARY_PURCHASE_ERP_VI.json` |
| `CUSTOMER_AI_DATA_REQUIREMENTS.md` | Đã bỏ; nội dung khách hàng/AI không còn giữ trong `FUNC_LOGIC` |

## Pass 23 (2026-06-21) — gộp index nhỏ vào tài liệu canonical

| File đã xóa | Đọc thay |
| ----------- | -------- |
| `PRODUCTION_TERMINOLOGY_CODE_UI.md` | `PRODUCTION_BUSINESS.md` §1 |
| `SALES_FULFILLMENT_DOCS_INDEX.md` | `INDEX.md` mục Sales / Purchase / Warehouse |

## Pass 24 (2026-06-21) — gộp Maolin về 2 file canonical

| File đã xóa | Đọc thay |
| ----------- | -------- |
| `MAOLIN_INDEX.md` | `MAOLIN_BUSINESS.md` |
| `MAOLIN_IMPORT_SEQUENCE.md` | `MAOLIN_BUSINESS.md` §9 |

## Pass 25 (2026-06-21) — rút gọn Sales/Warehouse và chuyển tài liệu kỹ thuật ra `docs`

| File cũ trong `FUNC_LOGIC` | Đọc thay |
| ----------- | -------- |
| `SALES_PURCHASE_STOCK_FLOW.md` | `SALES_BUSINESS.md` §5.1, `SALES_FULFILLMENT_SCHEMA_MATRIX.md` |
| `WAREHOUSE_BASIC_AND_BIN_LOCATION_GUIDE.md` | `WAREHOUSE_BUSINESS.md`, `WAREHOUSE_MASTER_GUIDE.md` §8 |
| `WAREHOUSE_PURCHASE_ENV_REFERENCE.md` | `../docs/WAREHOUSE_PURCHASE_ENV_REFERENCE.md` |
| `AUTH_USERS_CLIENT_FLOW.md` | `../docs/AUTH_USERS_CLIENT_FLOW.md` |
