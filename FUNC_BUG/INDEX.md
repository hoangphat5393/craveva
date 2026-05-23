# FUNC_BUG Index

Navigation index for bug, incident, and troubleshooting documents.

## Primary entry

- Group overview: `FUNC_BUG/README.md`
- **Audit (gộp file 2026-05-12):** `FUNC_BUG/AUDIT_BUG_2026_VI.md`
- Product import hub: `FUNC_BUG/PRODUCT_IMPORT_MASTER.md` → chi tiết: `FUNC_BUG/PRODUCT_IMPORT_DETAILS_VI.md`
- Client import hub: `FUNC_BUG/CLIENT_IMPORT_MASTER.md` → chi tiết: `FUNC_BUG/CLIENT_IMPORT_DETAILS_VI.md`

## Staging & infrastructure (archive trong FUNC_BUG)

- **`FUNC_BUG/STAGING_SSH_GCLOUD_METADATA_AND_DEPLOY_SCRIPT_VI.md`** — SSH staging + GCP `ssh-keys` + `upload_staging.ps1` (Permission denied, Admin vs hoangphat5393, FETCH_HEAD, lỗi bash deploy). **Cập nhật 2026-05.**
- **`FUNC_BUG/STAGING_INCIDENTS_ARCHIVE_VI.md`** — gộp các ghi chép `STAGING_*` cũ (lệnh SSH, nginx timeout, PHP upload, module missing, …). **Ưu tiên đọc:** `docs/SERVER_RUNBOOK_VI.md`, `docs/STAGING_OPERATIONS.md`. Mục SSH cũ trong file này có thể lệch thời điểm so với file SSH ở trên.

## Module, permissions, and behavior

- `FUNC_BUG/AFFILIATE_HIDDEN_IN_COMPANIES.md`
- `FUNC_BUG/DEVTOOLS_NO_COMPANY_SETTINGS.md`
- `FUNC_BUG/DEVELOPER_TOOLS_MODULE_REVIEW.md`
- `FUNC_BUG/PRICING_VISIBLE_STAGING_NOT_LOCAL.md`
- `FUNC_BUG/SOCIAL_AUTH_SETTINGS_MAC_INVALID_FIX.md`

## Production / warehouse (known functional gaps)

- `FUNC_BUG/PRODUCTION_RM_OUTBOUND_UOM_VI.md` — Post RM consumption không quy đổi UOM (Open P0); spec: `FUNC_IMPROVE/15_PRODUCTION_OUTBOUND_UOM_GAP_VI.md`

## QA, performance, and data quality

- `FUNC_BUG/CLIENT_DATATABLE_PERFORMANCE.md`
- `FUNC_BUG/FULL_TEST_SUITE_FAILURES_SNAPSHOT.md`
- `FUNC_BUG/ENG_TO_EN_STANDARDIZATION.md`
- `FUNC_BUG/RECRUIT_SOURCE_SETTING_ARRAY_TO_STRING_VI.md`

## Maintenance notes

- Keep this file as navigation only; avoid duplicating full analysis.
- For every new bug document, add one line under the right section.
- If superseded, mark deprecated with replacement link.
