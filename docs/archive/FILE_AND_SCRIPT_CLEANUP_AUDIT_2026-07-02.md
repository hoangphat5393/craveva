# File and Script Cleanup Audit

**Date:** 2026-07-02  
**Scope:** Markdown and operational/development scripts in the local source tree.  
**Final status:** Completed and archived on 2026-07-02.

## Executive conclusion

Cleanup was completed in reviewed groups. Generated output, obsolete one-off scripts, stale deployment helpers, broken documentation navigation, and outdated migration repair instructions were handled without deleting active runtime or server-operation tooling.

## Initial inventory and checks

- Markdown files scanned: **787** (excluding `vendor`, `node_modules`, `storage`, and `bootstrap/cache`).
- Script files scanned under `scripts/` and `database/scripts/`: **80**.
- PHP script syntax: **29/29 passed** `php -l`.
- PowerShell syntax: **27/27 passed** PowerShell AST parsing.
- Python syntax: **6/6 passed** Python AST parsing.
- Shell syntax: **11/13 passed** `bash -n`; two files currently use CRLF despite `.gitattributes` requiring LF.
- Exact duplicate Markdown groups: **8 groups / 46 files**. Most are generated `ai-context` placeholders or package license/contribution files, not accidental authored duplicates.

## High-confidence cleanup candidates

### 1. `graphify-out/`

**Status 2026-07-02:** Completed. The old output was removed, regenerated with `graphify update .`, and removed from Git tracking.

- The regenerated graph contains **27,664 nodes**, **56,518 edges**, and **7,137 communities**.
- The new local output contains **10,328 files** and remains available for Graphify queries.
- Git now tracks **0** files under `graphify-out/`; **8,041** old generated files are staged for deletion from the repository.
- `.gitignore` continues to ignore `graphify-out/`, so future updates remain local.
- This is generated analysis output, not Laravel runtime source.

### 2. `ai-context/`

**Status 2026-07-02:** Completed. Removed 295 generated files from the repository and added `ai-context/` to `.gitignore`.

- `scripts/generate_ai_context.php` is retained for on-demand regeneration.
- Generated output now stays local and is not authoritative documentation.
- The generator overwrites known outputs but does not clear stale files first.
- No application runtime dependency on this directory was found.

### 3. `database/scripts/replay_sales_do_grn_migration_rows.php`

**Status 2026-07-02:** Completed. The destructive one-off replay script was removed.

- Current schema sources now point to table-level `2000_01_01_*_baseline.php` files.
- `purchase:verify-cutover-schema` remains as a safe read-only table health check.
- Runbooks no longer instruct operators to delete rows from the migration registry.

### 4. Temporary Hub partial-deploy files

**Status 2026-07-02:** Completed. The partial deployment script and its stale case file were removed:

- `scripts/upload_hub_temp.ps1`
- `scripts/casefiles/hub-upload-pm-ready-remove-temp-api-doc.txt`

- The default upload list references deleted documentation.
- The case file references a deleted `_VI.md` document.
- Full Git deployment remains available through `scripts/upload_hub.ps1`.

### 5. Maolin one-off spreadsheet readers

**Status 2026-07-02:** Completed. Removed the one-off readers and DOCX generators tied to the missing `PROJECT MAOLIN New/` dataset:

- `scripts/read_maolin_headers.php`
- `scripts/read_maolin_new_folder_headers.php`
- `scripts/list_maolin_new_all_sheets.php`
- two Maolin DOCX generators that reference `PROJECT MAOLIN New/`

- `PROJECT MAOLIN New/` no longer exists.
- `scripts/peek_maolin_sheet.php` is generic and is still referenced; keep it.
- `scripts/read_maolin_project_headers.php` still targets the existing `PROJECT MAOLIN/` folder; keep it unless that workflow is retired.

## Files to fix, not delete

### Platform help corpus

Keep `docs/platform-help/`. It is the intended UI/help/RAG corpus and has generator scripts and documented build steps.

**Status 2026-07-02:** Completed. The generator and converter now calculate page-depth links correctly, the URL-index parser recognizes all 290 padded Markdown rows, 290 existing generated pages were updated without overwriting their content, and all **1,228** checked local links resolve.

### FUNC documentation indexes

**Status 2026-07-02:** Completed.

- Added `FUNC_BUG/INDEX.md` and linked it from `FUNC_INDEX.md`.
- Added the four missing `FUNC_IMPROVE` entries.
- Fixed `md_master_sync.ps1` to accept relative index links and only require a master guide when a routing rule identifies one.
- The checker now reports **64 documents scanned**, **0 missing group links**, **0 missing master-guide links**, and **0 missing group indexes**.

### Shell scripts with invalid working-tree line endings

**Status 2026-07-02:** Both obsolete CRLF shell scripts were removed:

- `scripts/hub_pat_clone_once.sh`: superseded by SSH deploy-key setup.
- `scripts/staging-pull-keep-htaccess.sh`: not used by the current Nginx/deploy flow. The PowerShell counterpart remains.

### Scripts with stale document paths

**Status 2026-07-02:** Completed for the identified scripts.

- `scripts/quick-context.ps1` now uses current Biomixing document names.
- `scripts/staging_fix_storage_permissions.sh` now references `docs/SERVER_RUNBOOK.md`.
- `scripts/md_master_sync.ps1` now scans `FUNC_TEST` instead of the removed `FUNC_IMPORT` group.

Update these paths before relying on the scripts.

## Authored-document links

**Status 2026-07-02:** Completed. The `file.md` occurrence is an example inside a fenced code block, not a navigable link. The missing Biomixing PNG reference was replaced with the existing `PHASE1_TO_3_END_TO_END_FLOW.mmd` source.

## Files and groups that should be kept

- `docs/platform-help/`: runtime/help/RAG documentation corpus.
- Package `LICENSE.md` and `CONTRIBUTING.md` duplicates: required per package and not cleanup noise.
- `scripts/test.ps1` and `scripts/test.sh`: useful cross-platform test wrappers.
- `scripts/run-queue-worker-all.ps1` and `.sh`: intentional OS-specific variants.
- `database/scripts/import_fresh_seed_data.php` and fresh migration audit/generator scripts: part of the consolidated migration maintenance workflow.
- `scripts/peek_maolin_sheet.php`: generic spreadsheet inspection tool with active references.
- Deployment, backup, SSH, GCP, permission, and DB scripts: keep until the operator explicitly retires the server workflow; many are dangerous but still potentially operational.
- `FUNC_IMPROVE/LEGACY_ARCHIVE.md`, `FUNC_LOGIC/LEGACY_ARCHIVE.md`, and `PROJECT BIOMIXING/LEGACY_ARCHIVE.md`: deliberate consolidated history files, not accidental leftovers.

## Completion summary

All cleanup actions identified by this audit were completed. Broader pre-existing source changes in the working tree are separate from this audit and were not reverted or staged wholesale.

Final verification:

- **501** Markdown files scanned; **1,533** local links checked with **0** broken links.
- The only exact Markdown duplicates are package-level `LICENSE.md` and `CONTRIBUTING.md` files, retained intentionally.
- Script syntax passed: PHP **21/21**, PowerShell **26/26**, Python **2/2**, Shell **11/11**.
- FUNC navigation passed: **64** documents, **0** missing group links, **0** missing master-guide links.

## Current decision

The audit is complete. Active server-operation scripts and package legal files remain intentionally retained.
