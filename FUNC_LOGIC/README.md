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

| File                                                           | Nội dung                                        |
| -------------------------------------------------------------- | ----------------------------------------------- |
| [Package_Modules_Commands.md](Package_Modules_Commands.md)     | Lệnh `packages:modules`                         |
| [Package_Modules_Flow.md](Package_Modules_Flow.md)             | Flow Package → module_settings, observer, cache |
| [Libraries_And_Module_Names.md](Libraries_And_Module_Names.md) | Composer / tên module trong app                 |
| [Login_Flow.md](Login_Flow.md)                                 | Đăng nhập (Fortify, session, …)                 |

### Flow nghiệp vụ (FLOW\_\*)

| File                                                       | Nội dung                                   |
| ---------------------------------------------------------- | ------------------------------------------ |
| [FLOW_ADD_CLIENT.md](FLOW_ADD_CLIENT.md)                   | Thêm client                                |
| [FLOW_ADD_PRODUCT.md](FLOW_ADD_PRODUCT.md)                 | Thêm sản phẩm                              |
| [FLOW_ADD_INVENTORY.md](FLOW_ADD_INVENTORY.md)             | Thêm / tồn kho                             |
| [FLOW_Pricing_Module_VI.md](FLOW_Pricing_Module_VI.md)     | Pricing (VI) — bản chuẩn đã hợp nhất EN/VI |
| [FLOW_LanguagePack_Module.md](FLOW_LanguagePack_Module.md) | LanguagePack                               |

### MAOLIN / ERP / B2B

**Mục lục gộp MAOLIN (nên mở trước):** [MAOLIN_INDEX.md](MAOLIN_INDEX.md).

| File                                                                                                       | Nội dung                                                 |
| ---------------------------------------------------------------------------------------------------------- | -------------------------------------------------------- |
| [WAREHOUSE_INDEX.md](WAREHOUSE_INDEX.md)                                                                   | **Mục lục Warehouse** (FLOW, UAT, audit, PM)             |
| [WAREHOUSE_MASTER_GUIDE.md](WAREHOUSE_MASTER_GUIDE.md)                                                     | Tài liệu Warehouse gộp (analysis + UI + DB)              |
| [WAREHOUSE_PRE_UPGRADE_DEPENDENCY_AUDIT_CHECKLIST.md](WAREHOUSE_PRE_UPGRADE_DEPENDENCY_AUDIT_CHECKLIST.md) | Audit Product/PO/DO/Inventory/Invoice trước nâng cấp kho |
| [B2B_ERP_PO_DO_INVOICE_GUIDE.md](B2B_ERP_PO_DO_INVOICE_GUIDE.md)                                           | PO / DO / Invoice B2B                                    |
| [MAOLIN_MASTER_GUIDE.md](MAOLIN_MASTER_GUIDE.md)                                                           | **Bản gộp MAOLIN** (đọc 1 file là đủ)                    |
| [MAOLIN_IMPORT_MAPPING.md](MAOLIN_IMPORT_MAPPING.md)                                                       | Map cột import MAOLIN (ready to use)                     |
| [PROJECT_MAOLIN_NEW_FILES_ANALYSIS.md](PROJECT_MAOLIN_NEW_FILES_ANALYSIS.md)                               | Phân tích chi tiết file `PROJECT MAOLIN New`             |
| [ERP_TECH_REVIEW_REPORT_VI.md](ERP_TECH_REVIEW_REPORT_VI.md)                                               | Tech review (VI)                                         |

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

```bash
php artisan packages:modules list
php artisan packages:modules activate-all
php artisan packages:modules activate-all --package=9
php artisan packages:modules activate --module=clients
php artisan packages:modules enable-custom
```

---

_Cập nhật bảng trên khi thêm hoặc đổi tên file trong `FUNC_LOGIC/`._
