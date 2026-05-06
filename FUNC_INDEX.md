# FUNC Documentation Index

Central index for function-level documentation groups:

- `FUNC_BUG`
- `FUNC_IMPORT`
- `FUNC_IMPROVE`
- `FUNC_LOGIC`

This file is the root navigation layer only. Detailed business flow and module logic stay in module-specific master guides.

## 1) Documentation Structure

| Group          | Purpose                                                                 | Primary entry file      |
| -------------- | ----------------------------------------------------------------------- | ----------------------- |
| `FUNC_BUG`     | Bugs, incidents, diagnostics, environment-specific fixes                | `FUNC_BUG/INDEX.md`     |
| `FUNC_IMPORT`  | Import design, implementation prompts, trackers                         | `FUNC_IMPORT/INDEX.md`  |
| `FUNC_IMPROVE` | Improvement proposals, refactor plans, optimization tasks               | `FUNC_IMPROVE/INDEX.md` |
| `FUNC_LOGIC`   | Master business logic, functional flow, audits, cross-module references | `FUNC_LOGIC/INDEX.md`   |

## 2) Module Master Guides (Single source per module)

Use one master guide per module/domain, then link detailed files under that master:

- Warehouse: `FUNC_LOGIC/WAREHOUSE_MASTER_GUIDE.md`
- Maolin Import domain: `FUNC_LOGIC/MAOLIN_MASTER_GUIDE.md`
- Sales/Fulfillment docs index: `FUNC_LOGIC/SALES_FULFILLMENT_DOCS_INDEX.md`

When a module needs deep details (product/client/production integrations), keep those in focused files and reference them from the module master guide.

## 3) Group Index Files (Level 2 navigation)

- `FUNC_BUG/INDEX.md`
- `FUNC_IMPORT/INDEX.md`
- `FUNC_IMPROVE/INDEX.md`
- `FUNC_LOGIC/INDEX.md`

## 4) Working Rules

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
