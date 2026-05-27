# `docs/` — Audit tài liệu (2026-05-12)

## 1) Mục tiêu

- Ghi lại **cấu trúc** thư mục `docs/` (runbook, staging, upgrade, axios migration) và việc **gộp nhẹ** không phá vỡ liên kết hàng loạt.
- Trỏ **canonical** để tránh nhân đôi nội dung với `FUNC_BUG/` hoặc `FUNC_LOGIC/`.

## 2) Canonical (ưu tiên)

| Chủ đề                                                              | File                                                                                                                                                    |
| ------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------- |
| End-user help ERP (`/account/...`, closed English corpus)           | `docs/platform-help/` — [README.md](platform-help/README.md); `php docs/platform-help/scripts/convert-to-english.php --regenerate`                      |
| Deploy / quyền / queue / import poll                                | `docs/SERVER_RUNBOOK_VI.md`                                                                                                                             |
| Rehearsal staging, zip, SO/DO Phase 3                               | `docs/STAGING_OPERATIONS.md`                                                                                                                            |
| Tiến độ PHP 8.3 + Laravel 11 + incident disk + **recovery tóm tắt** | `docs/STAGING_PHP83_L11_DEPLOY_PROGRESS.md` (mục **Phụ lục — Recovery nhanh**; gộp nội dung từ `STAGING_RECOVERY_LATEST.md` đã xóa)                     |
| easyAjax → axios                                                    | `docs/axios-migration/README.md` + từng `docs/axios-migration/*.md` theo module (**chưa** gộp một file khổng lồ — giữ tracker per-module cho review PR) |

## 3) Thay đổi trong đợt này

- **Xóa** `docs/STAGING_RECOVERY_LATEST.md` (stub ngắn) — nội dung chuyển vào cuối `STAGING_PHP83_L11_DEPLOY_PROGRESS.md`.
- **`STAGING_OPERATIONS.md`:** cập nhật bảng tài liệu liên quan (bỏ dòng file disk recovery không tồn tại trong tree; sửa tên `ENG_AI_MYSQL_CONNECTIVITY_QUESTIONNAIRE.md`).
- **`STAGING_OPERATIONS.md` §5 (2026-05-12):** bỏ lệnh gọi `scripts/staging_*.sh` / `.ps1` không tồn tại trong repo; thay bằng gate/precheck thủ công (Artisan + checklist). Chi tiết dọn `scripts/`: `scripts/AUDIT_2026_VI.md`.
- **`SERVER_RUNBOOK_VI.md` mục 10 (2026-05-12):** gộp toàn bộ mẫu cấu hình từ thư mục `deploy/` (Supervisor, systemd, cron, Nginx PHP, drop-in PHP Hub); **xóa** thư mục `deploy/` khỏi repo.

## 4) Gợi ý sau (không làm tự động)

- **axios-migration:** chỉ gộp khi migration xong toàn repo và không cần bảng trạng thái per-module.
- **LARAVEL*11*\*.md:** giữ tách bản kỹ thuật vs bản “người dùng không kỹ thuật” trừ khi PM yêu cầu một file.

## 5) Liên quan

- Incident staging chi tiết (archive): `FUNC_BUG/STAGING_INCIDENTS_ARCHIVE_VI.md`
- Tra cứu bug: `FUNC_BUG/REGISTRY.md`
- Snapshot đếm dòng backend: `LOG_REPORT/INDEX.md` + `LOG_REPORT/DOCUMENTATION_AUDIT_LOG_REPORT_2026_05_VI.md`
- Đặc tả / snapshot infra app: `SPECIFICATION/INDEX.md` + `SPECIFICATION/DOCUMENTATION_AUDIT_SPECIFICATION_2026_05_VI.md`

---

## 6) Chu kỳ 2026-05-13 — inbound AI Order (REST) + deploy script

| Việc                            | Chi tiết                                                                                                                                                                                                                                        |
| ------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Canonical**                   | Inbound tạo Sales Order từ AI/third-party: **`docs/AI_ORDER_INTEGRATION_REST.md`**, **`docs/AI_ORDER_INTEGRATION_REST_SETUP_VI.md`** (`POST /api/integrations/orders`). Gỡ legacy path: **`FUNC_LOGIC/AI_ORDER_LEGACY_WEBHOOK_REMOVED_VI.md`**. |
| **Audit nhóm FUNC_LOGIC**       | **`FUNC_LOGIC/AUDIT_LOGIC_2026_VI.md`** §10 (đồng bộ tài liệu sau REST + `md_master_sync`).                                                                                                                                                     |
| **Staging deploy (PowerShell)** | **`scripts/upload_staging.ps1`** — mặc định **có** push local (add/commit nếu cần) rồi SSH; dùng **`-DeployOnly`** khi chỉ muốn server `git pull` code đã có trên `origin`.                                                                     |
