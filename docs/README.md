# Docs

Thư mục này giữ tài liệu vận hành, nâng cấp và tích hợp chung của hệ thống.
Tài liệu nghiệp vụ chi tiết theo module nên ưu tiên đặt trong `FUNC_LOGIC`,
`FUNC_IMPROVE`, `FUNC_BUG` hoặc `docs/platform-help` nếu đó là nội dung help theo route.

## Nhóm tài liệu chính

| Nhóm | File | Mục đích |
| --- | --- | --- |
| Server / staging | `SERVER_RUNBOOK.md` | Runbook deploy, quyền file, queue, supervisor, go-live. |
| Server / staging | `STAGING_OPERATIONS.md` | Rehearsal staging, zip deploy, Phase 3 gate, thao tác an toàn. |
| Server / staging | `STAGING_DEPLOY_PROGRESS.md` | Lịch sử PHP 8.3 / Laravel 11 và các incident staging lớn. |
| GCP / Cloud SQL | `GCP_INVENTORY.md` | Snapshot VM, IP, Cloud SQL, firewall, general log, checklist AI DB connectivity. |
| GCP / Cloud SQL | `CLOUD_SQL_BACKUP.md` | Backup Cloud SQL, PITR, retention. |
| AI / REST | `AI_ORDER_REST.md` | API inbound tạo Sales Order từ AI / third-party. |
| AI / REST | `AI_ORDER_REST_SETUP.md` | Hướng dẫn setup/test Postman, probe, CSRF. |
| AI / REST | `API_SYSTEM_REFERENCE.md` | Khảo sát endpoint API hiện có + data type canonical. |
| AI / REST | `MIAOLIN_SO_API_FIELDS.md` | Field API Sales Order cho Miaolin. |
| System / DB | `DB_SYSTEM_OVERVIEW.md` | Tổng quan database, domain và module. |
| System / DB | `MIGRATION_AUDIT_AND_GROUPS_2026-07-04.md` | Trạng thái migration hiện tại, nhóm file liên quan và rủi ro fresh install. |
| System / DB | `SYSTEM_LIBRARIES_AND_MODULE_NAMES.md` | Thư viện Composer và tên module/package. |
| System / DB | `SYSTEM_MODULE_LANGUAGEPACK_CUSTOM_FIELDS.md` | Package, LanguagePack, custom fields, lệnh vận hành. |
| Language / glossary | `GLOSSARY_PURCHASE_ERP_VI.json` | Từ điển thuật ngữ ERP/Purchase cho script audit LanguagePack. |
| System / DB | `OPS_COMPANY_TRANSACTION_PURGE.md` | Purge giao dịch theo company_id, giữ master data. |
| Documentation | `documentation/README.md` | Quy trình, template và công cụ quản lý Markdown. |
| Environment | `ENV_LOCAL_SERVER_HOSTNAMES.md` | Phân biệt local `.test` và server thật. |
| UI / Frontend | `UI_BACKEND_UX_STANDARD.md` | Chuẩn UI/UX backend khi làm tính năng mới. |
| UI / Frontend | `UI_FRONTEND_LAYOUT_JS.md` | Layout và JS/CSS load theo nhóm màn hình. |
| Laravel 11 | `LARAVEL_11_UPGRADE.md` | Checklist nâng cấp Laravel 11, QA kỹ thuật, lưu ý constructor controller. |
| Laravel 11 | `LARAVEL_11_TOM_TAT.md` | Tóm tắt cho người dùng không chuyên kỹ thuật. |
| Test / CI | `CI_PEST_SAFE.md` | Quy trình chạy Pest an toàn, không ảnh hưởng staging. |
| Performance | `ORDER_CREATE_PERF.md` | Kế hoạch tối ưu màn hình tạo Order. |
| Frontend / Axios | `AXIOS_UPGRADE.md` | Kế hoạch nâng cấp Axios chung. |
| Frontend / Axios | `axios-migration/README.md` | Index riêng cho audit/migration Ajax -> Axios. |
| Cursor / AI work | `CURSOR_USAGE.md` | Ghi chú sử dụng Cursor/Codex, multitask, token. |
| Cursor / AI work | `CURSOR_TASKS.md` | Mẫu cách chia việc/audit doc/browser/SSH. |
| Review / strategy | `ERP_SCALING.md` | Đề xuất scale ERP góc nhìn CTO. |
| Review / strategy | `DBAL_AUDIT.md` | Audit Doctrine DBAL migration. |
| Review / strategy | `ROLE_FULLSTACK.md` | Mô tả phạm vi role fullstack. |

## Quy tắc đặt tên

- Tên file doc đọc trực tiếp nên ngắn, rõ nghĩa, không thêm `_VI` nếu nội dung đã là tiếng Việt.
- Tài liệu sinh theo route trong `docs/platform-help/pages` có thể giữ tên theo route để không gãy index/help.
- Khi đổi tên file, phải cập nhật link Markdown trong các thư mục `FUNC_*`, `SPECIFICATION`, `MASTER_DOCUMENTATION.md` và `docs`.

## Lịch sử dọn tài liệu quan trọng

- 2026-05: gộp deploy/quyền/queue/import poll về `SERVER_RUNBOOK.md`; rehearsal staging và Phase 3 giữ ở `STAGING_OPERATIONS.md`.
- 2026-05: gộp recovery staging vào `STAGING_DEPLOY_PROGRESS.md`; bỏ các stub recovery ngắn.
- 2026-05: inbound AI Order dùng REST canonical `AI_ORDER_REST.md` và `AI_ORDER_REST_SETUP.md`; legacy `/ai-order-webhook/{hash}` đã gỡ khỏi runtime.
- 2026-06: tracker per-module trong `docs/axios-migration` đã retire; giữ `README.md`, `AJAX_AUDIT.md`, `AXIOS_PROMPT.md`.
- 2026-06: gộp firewall, Cloud SQL general log và AI DB connectivity checklist vào `GCP_INVENTORY.md`.
- 2026-06: chuyển tài liệu kỹ thuật nền khỏi `FUNC_LOGIC` sang `docs` để `FUNC_LOGIC` chỉ giữ nghiệp vụ/flow.
