# FUNC_LOGIC – Tài liệu logic & flow (Craveva)

Thư mục lưu **ghi chú kỹ thuật**, **flow**, **phân tích MAOLIN/B2B**, không thay cho code.

## Có nên gộp nhiều file thành một?

| Cách làm                                                  | Ưu                             | Nhược                                                                           |
| --------------------------------------------------------- | ------------------------------ | ------------------------------------------------------------------------------- |
| **Một file khổng lồ**                                     | Một chỗ mở                     | Rất khó tìm đoạn, merge Git conflict, không ai muốn review                      |
| **Gộp theo chủ đề** (ví dụ mọi thứ về warehouse → 1 file) | Ít file hơn                    | File vẫn dài; tài liệu “đóng vai” khác nhau (plan vs hướng dẫn vận hành) dễ lẫn |
| **Giữ nhiều file + mục lục (README này)**                 | Mỗi file một mục đích; diff rõ | Cần cập nhật index khi thêm/xóa file                                            |
| **Chia subfolder** (`maolin/`, `flows/`, …)               | Rõ phạm vi                     | Đổi path, cần cập nhật link nội bộ                                              |

**Khuyến nghị:** không gộp toàn bộ; có thể gộp **chỉ** các bản gần trùng (ví dụ EN + VI cùng một chủ đề → một file hai mục). Còn lại dùng **README làm mục lục** (bảng dưới).

---

## Mục lục theo chủ đề

### Package, module, đăng nhập

| File                                                                                                         | Nội dung                                                                   |
| ------------------------------------------------------------------------------------------------------------ | -------------------------------------------------------------------------- |
| [SUPERADMIN_PACKAGE_AUDIT_VI.md](SUPERADMIN_PACKAGE_AUDIT_VI.md)                                             | Super Admin **Packages**: JSON module, DataTable, đồng bộ company          |
| [DEVELOPER_TOOLS_AUDIT_AND_FLOW_VI.md](DEVELOPER_TOOLS_AUDIT_AND_FLOW_VI.md)                                 | Developer Tools: gateway DB, quyền UI, AI/SQL, `developertools:audit`      |
| [FLOW_Modules_Package_LanguagePack_CustomFields_VI.md](FLOW_Modules_Package_LanguagePack_CustomFields_VI.md) | **Gộp:** `packages:modules` (nwidart), LanguagePack, custom fields + audit |
| [Libraries_And_Module_Names.md](Libraries_And_Module_Names.md)                                               | Composer / tên module trong app                                            |
| [Login_Flow.md](Login_Flow.md)                                                                               | Đăng nhập (Fortify, session, …)                                            |

### Flow nghiệp vụ (FLOW\_\*)

| File                                                                                                         | Nội dung                                   |
| ------------------------------------------------------------------------------------------------------------ | ------------------------------------------ |
| [FLOW_ADD_CLIENT.md](FLOW_ADD_CLIENT.md)                                                                     | Thêm client                                |
| [FLOW_ADD_PRODUCT.md](FLOW_ADD_PRODUCT.md)                                                                   | Thêm sản phẩm                              |
| [FLOW_ADD_INVENTORY.md](FLOW_ADD_INVENTORY.md)                                                               | Thêm / tồn kho                             |
| [FLOW_Pricing_Module_VI.md](FLOW_Pricing_Module_VI.md)                                                       | Pricing (VI) — bản chuẩn đã hợp nhất EN/VI |
| [FLOW_Modules_Package_LanguagePack_CustomFields_VI.md](FLOW_Modules_Package_LanguagePack_CustomFields_VI.md) | Package / LanguagePack / CF (đã gộp)       |

### MAOLIN / ERP / B2B

**Mục lục gộp MAOLIN (nên mở trước):** [MAOLIN_INDEX.md](MAOLIN_INDEX.md).

| File                                                                                                       | Nội dung                                                                                        |
| ---------------------------------------------------------------------------------------------------------- | ----------------------------------------------------------------------------------------------- |
| [WAREHOUSE_INDEX.md](WAREHOUSE_INDEX.md)                                                                   | **Mục lục Warehouse** (FLOW, UAT, audit, PM)                                                    |
| [UAT_CHECKLIST_MUA_BAN_KHO_E2E_VI.md](UAT_CHECKLIST_MUA_BAN_KHO_E2E_VI.md)                                 | **UAT E2E** Mua · Bán · Kho (gộp checklist Miaolin; stub: `WAREHOUSE_UAT_CHECKLIST_MIAOLIN.md`) |
| [ERP_SO_PO_DO_GRN_SCHEMA_AND_LEGACY_MATRIX_VI.md](ERP_SO_PO_DO_GRN_SCHEMA_AND_LEGACY_MATRIX_VI.md)         | **Master:** luồng bán, DROP legacy, audit gộp (§7); schema `grns`/`sales_dos` vs legacy         |
| [WAREHOUSE_AND_PURCHASE_FLOW_ENV_REFERENCE_VI.md](WAREHOUSE_AND_PURCHASE_FLOW_ENV_REFERENCE_VI.md)         | **Biến `.env`** kho + PO/GRN/Sales DO/webhook AI (tham chiếu một chỗ)                           |
| [AUDIT_WAREHOUSE_MODULE_VI.md](AUDIT_WAREHOUSE_MODULE_VI.md)                                               | Audit **code** module Warehouse (route, quyền, API, liên kết Purchase/Invoice)                  |
| [QUY_TRINH_PO_DO_SO_INVOICE_WAREHOUSE_VI.md](QUY_TRINH_PO_DO_SO_INVOICE_WAREHOUSE_VI.md)                   | Quy trình PO / DO / SO / Invoice / Kho (VI, một chỗ)                                            |
| [WAREHOUSE_MASTER_GUIDE.md](WAREHOUSE_MASTER_GUIDE.md)                                                     | Tài liệu Warehouse gộp (analysis + UI + DB)                                                     |
| [WAREHOUSE_TOM_TAT_NOI_BO.md](WAREHOUSE_TOM_TAT_NOI_BO.md) §10–11                                          | Audit trước nâng cấp kho + prompt Cursor UAT (đã gộp)                                           |
| [B2B_ERP_PO_DO_INVOICE_GUIDE.md](B2B_ERP_PO_DO_INVOICE_GUIDE.md)                                           | Stub → trỏ `QUY_TRINH_…` + `SALES_PURCHASE_FLOW`                                                |
| [MAOLIN_MASTER_GUIDE.md](MAOLIN_MASTER_GUIDE.md)                                                           | **Bản gộp MAOLIN** (đọc 1 file là đủ)                                                           |
| [MAOLIN_IMPORT_MAPPING.md](MAOLIN_IMPORT_MAPPING.md)                                                       | Map cột import MAOLIN (ready to use)                                                            |
| [CUSTOM_FIELDS_GO_BO_TRUNG_COT_PO_DO_SO_CLIENT_VI.md](CUSTOM_FIELDS_GO_BO_TRUNG_COT_PO_DO_SO_CLIENT_VI.md) | CF nên gỡ (trùng cột chuẩn): PO, DO, SO, Client + link migration                                |
| [CUSTOM_FIELDS_SYSTEMWIDE_AUDIT_TABLE_VI.md](CUSTOM_FIELDS_SYSTEMWIDE_AUDIT_TABLE_VI.md)                   | **Bảng CF toàn hệ** — slug seed vs nghiệp vụ vs core + SQL xuất DB thực tế                      |
| [PROJECT_MAOLIN_NEW_FILES_ANALYSIS.md](PROJECT_MAOLIN_NEW_FILES_ANALYSIS.md)                               | Phân tích chi tiết file `PROJECT MAOLIN New`                                                    |
| [ERP_TECH_REVIEW_REPORT_VI.md](ERP_TECH_REVIEW_REPORT_VI.md)                                               | Tech review (VI)                                                                                |

### Import / client / DB

| File                                                                                                                     | Nội dung                                                        |
| ------------------------------------------------------------------------------------------------------------------------ | --------------------------------------------------------------- |
| [CLIENT_IMPORT_REVIEW_AND_IMPROVEMENTS.md](CLIENT_IMPORT_REVIEW_AND_IMPROVEMENTS.md)                                     | Review import client                                            |
| [CLIENT_IMPORT_LOG_UX_PROPOSAL.md](CLIENT_IMPORT_LOG_UX_PROPOSAL.md)                                                     | UX log import                                                   |
| [IMPORT_CHUNK_AND_BULK_INSERT_ANALYSIS.md](IMPORT_CHUNK_AND_BULK_INSERT_ANALYSIS.md)                                     | Chunk & bulk insert                                             |
| [PRODUCT_IMPORT_SLOWNESS_ANALYSIS.md](PRODUCT_IMPORT_SLOWNESS_ANALYSIS.md)                                               | Import chậm                                                     |
| (đã gộp)                                                                                                                 | MAOLIN/Miaolin legacy + contract → xem `MAOLIN_MASTER_GUIDE.md` |
| [SYSTEM_DATABASE_OVERVIEW_REPORT_VI.md](SYSTEM_DATABASE_OVERVIEW_REPORT_VI.md)                                           | Báo cáo tổng quan DB (MySQL, miền nghiệp vụ, SQL gợi ý)         |
| [DATABASE_REPORT_USERS_CLIENT_TABLES_RELATIONSHIPS.md](DATABASE_REPORT_USERS_CLIENT_TABLES_RELATIONSHIPS.md)             | Quan hệ users / client                                          |
| [SCHEMATIC_LAYER_USERS_CLIENT_DETAILS_1_1_REASON_AND_FIX.md](SCHEMATIC_LAYER_USERS_CLIENT_DETAILS_1_1_REASON_AND_FIX.md) | Layer users–client_details                                      |

### Pricing / dev tools

| File                                                                       | Nội dung                        |
| -------------------------------------------------------------------------- | ------------------------------- |
| [PRICING_MODULE_DEV_TASKS.md](PRICING_MODULE_DEV_TASKS.md)                 | Task dev Pricing                |
| [DEVELOPER_TOOLS_LOGGING_EXT_PLAN.md](DEVELOPER_TOOLS_LOGGING_EXT_PLAN.md) | Mở rộng logging Developer Tools |
| [DeveloperTools_FullAccess_Demo.md](DeveloperTools_FullAccess_Demo.md)     | Demo full access                |

---

## Lệnh nhanh (Package & Module)

Chi tiết đầy đủ: [FLOW_Modules_Package_LanguagePack_CustomFields_VI.md](FLOW_Modules_Package_LanguagePack_CustomFields_VI.md).

```bash
php artisan packages:modules list
php artisan packages:modules activate-all
php artisan packages:modules activate-all --package=9
php artisan packages:modules activate --module=clients
php artisan packages:modules enable-custom
php artisan languagepack:publish-translation
php artisan custom-fields:audit
php artisan developertools:audit
```

---

_Cập nhật bảng trên khi thêm hoặc đổi tên file trong `FUNC_LOGIC/`._
