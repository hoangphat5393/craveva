# FUNC Documentation Index

Central index for function-level documentation groups:

- `FUNC_BUG`
- `FUNC_IMPORT`
- `FUNC_IMPROVE`
- `FUNC_LOGIC`
- `FUNC_TEST`

This file is the root navigation layer only. Detailed business flow and module logic stay in module-specific master guides.

## 1) Documentation Structure

| Group          | Purpose                                                                 | Primary entry file      |
| -------------- | ----------------------------------------------------------------------- | ----------------------- |
| `FUNC_BUG`     | Bugs, incidents, diagnostics, environment-specific fixes                | `FUNC_BUG/INDEX.md`     |
| `FUNC_IMPORT`  | Import design, implementation prompts, trackers                         | `FUNC_IMPORT/INDEX.md`  |
| `FUNC_IMPROVE` | Improvement proposals, refactor plans, optimization tasks               | `FUNC_IMPROVE/INDEX.md` |
| `FUNC_LOGIC`   | Master business logic, functional flow, audits, cross-module references | `FUNC_LOGIC/INDEX.md`   |
| `FUNC_TEST`    | Test strategy, test cases, UAT execution matrix                         | `FUNC_TEST/INDEX.md`    |

**Báo cáo số dòng code (không thuộc nhóm FUNC\_\*):** `LOG_REPORT/README.md` — thư mục **`LOG_REPORT/`** (đổi tên từ `LOC_REPORT/`, 2026-05-12). Mục lục / audit gọn file: `LOG_REPORT/INDEX.md`, `LOG_REPORT/DOCUMENTATION_AUDIT_LOG_REPORT_2026_05_VI.md`.

## 2) Module Master Guides (Single source per module)

Use one master guide per module/domain, then link detailed files under that master:

- Warehouse: `FUNC_LOGIC/WAREHOUSE_MASTER_GUIDE.md`
- Maolin Import domain: `FUNC_LOGIC/MAOLIN_MASTER_GUIDE.md`
- Sales/Fulfillment docs index: `FUNC_LOGIC/SALES_FULFILLMENT_DOCS_INDEX.md`

When a module needs deep details (product/client/production integrations), keep those in focused files and reference them from the module master guide.

## 3) Group Index Files (Level 2 navigation)

- `FUNC_BUG/INDEX.md` — **audit nhóm bug/incident:** `FUNC_BUG/AUDIT_BUG_2026_VI.md`
- `FUNC_IMPORT/INDEX.md` — **audit nhóm import:** `FUNC_IMPORT/AUDIT_IMPORT_2026_VI.md`
- `FUNC_IMPROVE/INDEX.md` — cải tiến & backlog; **audit tài liệu nhóm:** `FUNC_IMPROVE/AUDIT_IMPROVE_2026_VI.md`
- `FUNC_LOGIC/INDEX.md` — **audit tài liệu nhóm:** `FUNC_LOGIC/AUDIT_LOGIC_2026_VI.md`
- `FUNC_TEST/INDEX.md`
- `docs/DOCUMENTATION_AUDIT_DOCS_2026_05_VI.md` — audit thư mục `docs/` (runbook, staging, axios-migration; không gộp 40+ file tracker)
- `LOG_REPORT/INDEX.md` — snapshot LOC backend; **audit:** `LOG_REPORT/DOCUMENTATION_AUDIT_LOG_REPORT_2026_05_VI.md`
- `SPECIFICATION/INDEX.md` — spec + snapshot infra; **audit:** `SPECIFICATION/DOCUMENTATION_AUDIT_SPECIFICATION_2026_05_VI.md`

1. Keep this index updated when adding new major doc groups or new module master guides.
2. Do not duplicate full content here; only route users to source documents.
3. For each new `.md` file, apply the standard header template in `MD_HEADER_TEMPLATE.md`.
4. If a file becomes obsolete, mark status as `deprecated` and point to replacement.
5. Prefer additive updates and preserve historical context in dedicated archival files.

## 5) Suggested Status Values

- `draft`: Work in progress
- `active`: Current source of truth
- `review`: Awaiting validation
- `deprecated`: Replaced by newer document
- `archived`: Historical reference only

## 6) Maintenance Checklist

- [ ] New document categorized under correct `FUNC_*` group
- [ ] Header metadata completed (module, status, owner, updated date)
- [ ] Linked from related module master guide
- [ ] Cross-links validated
- [ ] Deprecated docs redirected to replacement file
