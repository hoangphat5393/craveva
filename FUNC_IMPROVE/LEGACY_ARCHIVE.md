# FUNC_IMPROVE — Legacy archive index (2026-05-27 pass 2)

Các file dưới đây **đã xóa** khỏi repo vì hết vòng đời (plan/audit/prototype đã xong hoặc bị thay bằng living doc). **Không** khôi phục từ git trừ khi cần tra cứu lịch sử — dùng `git log -- <path>`.

## Ma trận bảo tồn nghiệp vụ (sau khi xóa)

| Nội dung trong file đã xóa                                                    | Đã ghi ở đâu?                                                                                                                     | Mức độ                                                          |
| ----------------------------------------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------- | --------------------------------------------------------------- |
| Reserve RM tại **Release** (không Draft), Cancel release, consume sau post RM | `FUNC_LOGIC/PRODUCTION_BUSINESS.md`, `19_*_TEST_CASES`, flow `.mmd`                                                     | **Đủ** cho vận hành                                             |
| PM quyết định reserve / lý do không reserve Draft                             | Plan `19_*` (git) — **tóm tắt** trong `PRODUCTION_BUSINESS` §3                                                             | Đủ ngắn                                                         |
| FEFO phân bổ lô khi reserve                                                   | `PRODUCTION_BUSINESS.md` §3                                                                                             | **Đủ** (ngắn)                                                   |
| Gán lô trên batch **không** tăng `reserved_quantity`                          | `PRODUCTION_BUSINESS` §3 + `BIOMIXING_BUSINESS_FLOW_LIVE` §3.6 / §3.3                                                      | **Đủ**                                                          |
| Material shortage (cross-order, công thức available)                          | `PRODUCTION_BUSINESS` §3 + filter UI (mặc định `active`)                                                                   | **Đủ** (2026-05-27)                                             |
| Vá UOM post RM (`convertToBase`)                                              | `PRODUCTION_BUSINESS.md` §3 + `FUNC_BUG/BUG_PRODUCTION_UOM.md`                                               | **Đủ**                                                          |
| Post FG → Inventory ledger P1c                                                | `PRODUCTION_BUSINESS.md` §3                                                                                             | **Đủ**                                                          |
| Checklist batch hiện tại 4 bước, variance, trace                              | `BIOMIXING_BUSINESS_FLOW_LIVE.md` §3                                                                                           | **Đủ**                                                          |
| P0–P2 backlog Gary (P0-3, P1-2, waste %, …)                                   | `BIOMIXING_GAP_STATUS.md` (bảng ID)                                                                                            | **Đủ** (không cần `PHASE2_PM_PLAN`)                             |
| Phase 1 báo giá gap chi tiết                                                  | `BIOMIXING_GAP_STATUS.md`, `PHASE1_QUOTATION_PM_HUMAN.md`                                                                   | Đủ cho go-live P1                                               |
| Nền kho SO/DO/GRN vs gap Production                                           | `FUNC_LOGIC/SALES_FULFILLMENT_QA_CHECKLIST.md`, `QUY_TRINH_*` — **không** còn bảng «đọc baseline 2026» trong `BIOMIXING_BASELINE_PREP` | Thiếu nhẹ — dùng `BIOMIXING_PREP_INDEX_EN` + FUNC_LOGIC         |
| Roadmap CCP / Phase 3–4 kỹ thuật                                              | `BIOMIXING_GAP_STATUS` (P2+), `BIOMIXING_BUSINESS_FLOW` § backlog                                                                 | Tóm tắt; chi tiết phase cũ → `git show` `BIOMIXING_DEV_PLAN.md` |
| PM report kho / Miaolin gap                                                   | `WAREHOUSE_MASTER_GUIDE`, `MAOLIN_MASTER_GUIDE`                                                                                   | Đủ nếu đã maintain guide                                        |

**Khi cần chi tiết plan đã xóa:** `git show HEAD:<đường_dẫn_trong_bảng_pass_1_2>` (file vẫn trong lịch sử git).

## Thay thế bằng (đọc theo thứ tự)

| Nhu cầu                        | Đọc thay                                                                                                                                                                                       |
| ------------------------------ | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Trạng thái Phase 1 & 2 vs code | [`BIOMIXING_GAP_STATUS.md`](./BIOMIXING_GAP_STATUS.md)                                                                                                                                   |
| Luồng nghiệp vụ vận hành       | [`BIOMIXING_BUSINESS_FLOW_LIVE.md`](./BIOMIXING_BUSINESS_FLOW_LIVE.md)                                                                                                                   |
| Production reserve / lifecycle | [`../FUNC_LOGIC/PRODUCTION_BUSINESS.md`](../FUNC_LOGIC/PRODUCTION_BUSINESS.md)                                                                                             |
| UAT / test                     | [`BIOMIXING_UAT_AND_TEST_GUIDE.md`](./BIOMIXING_UAT_AND_TEST_GUIDE.md), [`../FUNC_LOGIC/PRODUCTION_BUSINESS.md`](../FUNC_LOGIC/PRODUCTION_BUSINESS.md) §6 |
| P0 hàng đợi                    | [`P0_BIOMIXING_NEXT_STEPS.md`](./P0_BIOMIXING_NEXT_STEPS.md)                                                                                                                             |

## Pass 1 (2026-05-27)

| File đã xóa                                          | Lý do                                              |
| ---------------------------------------------------- | -------------------------------------------------- |
| `18_PRODUCTION_MATERIAL_SHORTAGE_SUMMARY_PLAN.md` | Đã triển khai → `PRODUCTION_BUSINESS.md` |
| `19_PRODUCTION_RM_RESERVE_AT_RELEASE_PLAN.md`     | Đã triển khai → test cases + flow mmd              |
| `CURSOR_AND_GIT_ACTIVITY_REPORT_*.md`                | Báo cáo lịch sử, không vận hành                    |

## Pass 2 (2026-05-27)

| File đã xóa                                      | Lý do                                                                          |
| ------------------------------------------------ | ------------------------------------------------------------------------------ |
| `15_PRODUCTION_OUTBOUND_UOM_FIX_PLAN.md`      | Vá xong 2026-05-20 → `15_PRODUCTION_OUTBOUND_UOM_GAP.md` (Fixed)            |
| `AUDIT_IMPROVE_2026.md`                       | Audit định kỳ → `DOCUMENTATION_AUDIT_CROSS_FOLDER_2026_05.md`               |
| `BIOMIXING_DOC_AUDIT_2026.md`                 | Trùng cross-folder audit                                                       |
| `BIOMIXING_GAP_ANALYSIS.md`                      | → `BIOMIXING_GAP_STATUS.md`                                                 |
| `BIOMIXING_FLOW_CRACEVA_GAP.md`                  | Gap cũ 2026-02                                                                 |
| `BIOMIXING_PROTOTYPE_PLAN.md`                 | POC Production đã qua                                                          |
| `BIOMIXING_BASELINE_PREP_2026.md`             | Baseline → `BIOMIXING_PREP_INDEX_EN.md` + `BIOMIXING_BUSINESS_FLOW_LIVE.md` |
| `BIOMIXING_DOMAIN_INTEGRATION.md`                | Nội dung trong prep index / playbook                                           |
| `BIOMIXING_MIGRATION_AUDIT_2026.md`           | Migration xong                                                                 |
| `BIOMIXING_PROPOSAL_TECH_MAP.md`              | Trùng `PROJECT BIOMIXING/BIOMIXING_PROPOSAL_REVISED_*`                         |
| `BIOMIXING_DEV_PLAN.md`                          | Roadmap → `BIOMIXING_GAP_STATUS.md` + playbook                              |
| `PHASE2_PM_PLAN.md`                           | Phase 2 SX → `PRODUCTION_BUSINESS.md` + `UI_RUNBOOK_PHASE2_*`        |
| `03_PRODUCTION_PREUPLOAD_AUDIT_2026_05_05.md` | Pre-upload audit một lần                                                       |
| `PHASE1_QUOTATION_PM_GAP_ANALYSIS.md`         | → `BIOMIXING_GAP_STATUS.md`                                                 |
| `P0_NEXT_ACTION_BIOMIXING.md`                 | Trùng `P0_BIOMIXING_NEXT_STEPS.md`                                          |

## Removed report folder (pass 2)

| File đã xóa      | Lý do                                                         |
| ---------------- | ------------------------------------------------------------- |
| `PM report 1.md` | Spec kho sơ bộ — đã có `FUNC_LOGIC/WAREHOUSE_MASTER_GUIDE.md` |
| `PM report 2.md` | Gap Miaolin — đã có `PROJECT MAOLIN/MAOLIN_BUSINESS.md`   |

## Pass 4 (2026-05-27) — gộp file (không xóa living doc)

| File gộp / thay thế                                                      | → Canonical                                  |
| ------------------------------------------------------------------------ | -------------------------------------------- |
| `17_*`, `18_SETTINGS_*`, `18_APP_*`                                      | `UX_MENU_AND_SETTINGS.md`                 |
| `CLIENT_IMPORT_MASTER` + `DETAILS`                                       | `BUG_IMPORT_CLIENT.md`                        |
| `PRODUCT_IMPORT_MASTER` + `DETAILS`                                      | `BUG_IMPORT_PRODUCT.md`                       |
| `MIAOLIN_SALES_ORDER_API_DATABASE_*` (2)                                 | `../docs/MIAOLIN_SO_API_FIELDS.md`          |
| `P0_05_*_EN` + `P0_05_*_VI`                                              | Đã gộp tiếp vào `P0_QA_BA_MASTER_TEST_CASE_TABLE.md` phụ lục P0-05 |
| `CLOUDSQL_ALLOWLIST_*` (2)                                               | Đã gộp tiếp vào `docs/GCP_INVENTORY.md`      |
| `PRODUCTION_BATCH_STEP1_*`, `PRODUCTION_MODULE_AUDIT_*`                  | `PRODUCTION_BUSINESS.md` §4 và §7     |
| `PRODUCT_TYPE_BUYER_VS_INVENTORY_*`                                      | `PRODUCTION_BUSINESS.md` §1          |
| Stub: `FUNC_BUG/INDEX`, `AUDIT_BUG`, `AUDIT_IMPORT`, `FULL_TEST_SUITE_*` | README / REGISTRY / `FUNC_TEST/INDEX`        |

## Pass 5 (2026-05-27)

| File gộp / thay thế                          | → Canonical                        |
| -------------------------------------------- | ---------------------------------- |
| `BIOMIXING_DOCUMENTATION_SYNC_2026_05.md` | `BIOMIXING_DOC_HUB.md`          |
| _(mới)_                                      | `FUNC_BUG/BUG_STAGING_OPS.md` |
| _(mới)_                                      | Report index removed with old report folder |

## Pass 6 (2026-05-27) — xóa legacy đã hoàn thiện

| File đã xóa                                      | Lý do / đọc thay                                                                           |
| ------------------------------------------------ | ------------------------------------------------------------------------------------------ |
| `15_PRODUCTION_OUTBOUND_UOM_GAP.md`           | Fixed → `PRODUCTION_BUSINESS.md` §3, `FUNC_BUG/BUG_PRODUCTION_UOM.md` |
| `16_PRODUCTION_FG_INVENTORY_LEDGER_SYNC.md`   | Done P1c → `PRODUCTION_BUSINESS.md` §3                                           |
| `01_PROD_BOM_FG_POLICY.md`                    | Đã triển khai → `PRODUCTION_BUSINESS.md`, config Production                      |
| `PRODUCTION_MODULE_PROGRESS_REPORT_EN.md`        | Snapshot tiến độ → `BIOMIXING_GAP_STATUS.md`                                            |
| `P0_EXECUTION_LOG.md`                            | Log lịch sử → `P0_BIOMIXING_NEXT_STEPS.md`                                              |
| `BIOMIXING_FULL_PROCESS_AUDIT_2026_05.md`     | Audit một lần → `BIOMIXING_GAP_STATUS.md`                                               |
| `DOCUMENTATION_AUDIT_CROSS_FOLDER_2026_05.md` | Meta audit snapshot removed with old report folder. |
| `19_WAREHOUSE_LABEL_AUDIT.md`                 | Label đã sửa → `docs/platform-help/02-GLOSSARY.md`                                         |
| `IMPORT_PROMPTS_ARCHIVE.md`                   | Import đã triển khai → `IMPORT_SPECS.md` (nay nằm trong `FUNC_IMPROVE`)                  |
| `FUNC_BUG/STAGING_INCIDENTS_ARCHIVE.md`       | Incident cũ → `docs/SERVER_RUNBOOK.md`, `BUG_STAGING_OPS.md`; lịch sử: `git log`   |
| 15× `FUNC_LOGIC/AUDIT_*` + analysis              | Snapshot audit → master guides (xem `FUNC_LOGIC/LEGACY_ARCHIVE.md`)                        |

## Pass 7 (2026-05-27) — archive / gộp backlog

| File đã xóa                                   | Lý do / đọc thay                                                                                |
| --------------------------------------------- | ----------------------------------------------------------------------------------------------- |
| `08_CLIENT_IMPORT_REVIEW_AND_IMPROVEMENTS.md` | Archived → `FUNC_LOGIC/CLIENT_BUSINESS.md`                                                      |
| `02_B2B_PROD_BATCH.md`                     | Kết luận B2B vs Production batch → `WAREHOUSE_MASTER_GUIDE.md`, `BIOMIXING_FLOW_CONCEPTS.md` |
| `SO_AI_WEBHOOK_PROMPTS.md`                 | Rollout xong → `docs/AI_ORDER_REST.md`, `12_AI_THIRDPARTY_SO_OPTIONS.md`         |
| `06_INVENTORY_BUSINESS_IMPROVE.md`            | Gộp opening/FG → `13_OPENING_STOCK_VS_WAREHOUSE_STOCK.md`                                    |
| `10_UX_UI_IMPROVEMENT_BACKLOG.md`             | Gộp → `UX_MENU_AND_SETTINGS.md` Phần D                                                       |

## Pass 8 (2026-05-27)

| File                             | Thay đổi                                               |
| -------------------------------- | ------------------------------------------------------ |
| `05_SO_DO_PO_GRN_REFACTOR.md` | Rút gọn ~780→120 dòng; giữ cutover, Artisan, Phase 4–5 |
| `PROJECT BIOMIXING/` (8 file)    | `PROJECT BIOMIXING/LEGACY_ARCHIVE.md`                  |

## Pass 9 (2026-05-27) — pre-delete audit, Tier 0 only

| File / artifact đã xóa                                          | Lý do                              |
| --------------------------------------------------------------- | ---------------------------------- |
| `FUNC_LOGIC/UI_BACKEND_UX_STANDARD copy.md`                    | Trùng `../docs/UI_BACKEND_UX_STANDARD.md` |
| `public/js/custom copy.js`                                      | Không Mix / không reference        |
| `resources/views/sections/menu.blade.backup-20260116.php`       | Backup view                        |
| `public/css/custom-css/theme-custom.backup-20260330-075832.css` | Backup CSS                         |

## Pass 10 (2026-05-27) — gộp Tier 1 rồi xóa

| File đã xóa                                                  | Gộp vào                                         |
| ------------------------------------------------------------ | ----------------------------------------------- |
| `P0_SHADOW_YIELD_UOM_GOVERNANCE_ROLLUP.md`                | `11_SHADOW_YIELD_UOM_PLANNED_ANALYSIS.md` §8 |
| `FUNC_BUG/ENG_TO_EN_STANDARDIZATION.md`                      | `FUNC_BUG/SO_LOI.md` — Phụ lục I18N-ENG-001   |
| `purchase_lang_audit_report.csv`                             | `scripts/lang_audit_purchase.php` + git history |
| `PROJECT BIOMIXING/PHASE1_QUOTATION_FLOW_DIAGRAM_TABLE.html` | `PHASE1_QUOTATION_FLOW_DIAGRAM.mmd` / `.html`   |
| `PRODUCTION_RELEASE_RESERVE_TEST_FLOW_EN.mmd` + `.html`      | `PRODUCTION_RELEASE_RESERVE_TEST_FLOW_VI.mmd`   |

## Pass 11–12 (2026-05-27) — gộp / rút gọn

| File                                                            | Thay đổi                                                          |
| --------------------------------------------------------------- | ----------------------------------------------------------------- |
| `BIOMIXING_PLAYBOOK_P0P1.md`                                 | Rút gọn ~515→~120 dòng; SSOT → living docs + `git log` bản đầy đủ |
| `09_ORDER_HISTORY_IMPROVE_PLAN.md`                              | Gộp → `FUNC_IMPROVE/IMPORT_POLL_TRACKERS.md` §7                |
| `DIAGRAM/pis_e2e_current_copy.mmd` + `.html`                    | Trùng bản đơn giản; giữ `pis_e2e_current.*`                       |
| `SPECIFICATION/DOCUMENTATION_AUDIT_SPECIFICATION_2026_05.md` | Meta audit; nội dung trong `GCP_AND_CLOUDSQL_SNAPSHOT` + INDEX    |
| `LOG_REPORT/DOCUMENTATION_AUDIT_LOG_REPORT_2026_05.md`       | Meta audit LOC cũ; thư mục `LOG_REPORT/` đã retire 2026-06-21     |
| `FUNC_LOGIC/PRODUCT_IMPORT_SLOWNESS_ANALYSIS.md`                | Rút gọn pass 13, retire pass 17 → `../FUNC_IMPROVE/IMPORT_CHUNK_BULK_QUEUE.md` |

**Audit SSOT:** old report snapshots removed; active cleanup history is summarized in this archive and related living indexes.

## Pass 14 (2026-06-10) — product form baseline retire

| File đã xóa | Lý do / đọc thay |
| ----------- | ---------------- |
| `21_PRODUCT_FORM_PRICING_CURRENT_STATE.md` | Baseline trước P1 đã lỗi thời sau khi drop `purchase_information`; matrix Product Type pricing đã gộp vào `20_BOM_FG_COST_SYNC_IMPLEMENTATION_PLAN.md` §4.1.1 |
| `22_PRODUCT_FORM_UX_SIMPLIFICATION_PLAN.md` | P1 product form visibility đã triển khai phần lớn; matrix visibility và lý do UX đã gộp vào `20_BOM_FG_COST_SYNC_IMPLEMENTATION_PLAN.md` §4.1.2 |
| `PHASE1_PM_STATUS_LIVE.md` | Status snapshot Phase 1 đã gộp vào `BIOMIXING_GAP_STATUS.md` § Phase 1; giải thích PM giữ ở `PHASE1_QUOTATION_PM_HUMAN.md` |

## Pass 15 (2026-06-16) — retire completed implementation plans

| File đã xóa | Lý do / đọc thay |
| ----------- | ---------------- |
| `SALES_DO_SHIP_ACTION_MODAL_PLAN.md` | Ship modal đã Done core; business rules + evidence gộp vào `05_SO_DO_PO_GRN_REFACTOR.md` §6. |
| `19_PRODUCTION_RM_RESERVE_AT_RELEASE_TEST_CASES.md` | Reserve RM tại Release đã Done core; test/UAT checklist gộp vào `FUNC_LOGIC/PRODUCTION_BUSINESS.md` §6 và bản EN tương ứng. |

## Pass 16 (2026-06-16) — retire Biomixing planning/checklist đã gộp

| File đã xóa | Lý do / đọc thay |
| ----------- | ---------------- |
| `BIOMIXING_PLAYBOOK_P0P1.md` | Pre-coding playbook đã hoàn thành; guardrails MVP, lifecycle, migration order, warehouse integration và P2+ backlog đã gộp vào `BIOMIXING_GAP_STATUS.md`; vận hành chi tiết đọc `FUNC_LOGIC/PRODUCTION_BUSINESS.md`. |
| `P0_05_TRACE_BIDIRECTIONAL_UAT_CHECKLIST.md` | Trace Warehouse ↔ Production đã pass dev/QA; checklist + evidence batch #14/#17 đã gộp vào `P0_QA_BA_MASTER_TEST_CASE_TABLE.md` phụ lục P0-05. |
| `P0_VARIANCE_APPROVAL_ROLE_MATRIX.md` | Ma trận quyền duyệt variance đã gộp vào `P0_QA_BA_MASTER_TEST_CASE_TABLE.md` phụ lục P0-02; regression `ProductionVarianceApprovalPermissionTest` vẫn là evidence chính. |

## Pass 17 (2026-06-17) — retire pointer/snapshot cũ

| File đã xóa | Lý do / đọc thay |
| ----------- | ---------------- |
| `FUNC_LOGIC/PRODUCT_IMPORT_SLOWNESS_ANALYSIS.md` | Pointer/rút gọn cũ, không còn nội dung riêng; đọc `FUNC_IMPROVE/IMPORT_CHUNK_BULK_QUEUE.md` và `FUNC_IMPROVE/IMPORT_POLL_TRACKERS.md`. |
| `FUNC_LOGIC/WAREHOUSE_TOM_TAT_NOI_BO.md` | Pointer nội bộ cũ; trạng thái/audit/PM đã chuyển sang `FUNC_LOGIC/WAREHOUSE_INDEX.md`, `FUNC_IMPROVE/04_WH_RUNBOOK_UPGRADE.md`, `FUNC_LOGIC/SALES_FULFILLMENT_QA_CHECKLIST.md`. |
| `LEGACY_PRE_DELETE_AUDIT_2026_05_27.md` | Snapshot pre-delete cũ; đã xóa cùng old report folder. |

## Pass 18 (2026-06-17) — gộp tracker axios migration đã hoàn tất

| File đã xóa | Lý do / đọc thay |
| ----------- | ---------------- |
| `docs/axios-migration/{area}.md` trackers | Các wave đều Completed; summary/status đã gộp vào `docs/axios-migration/README.md`. Giữ `README.md`, `AXIOS_PROMPT.md`, `AJAX_AUDIT.md`; lịch sử chi tiết: `git log -- docs/axios-migration/<old-file>.md`. |
| `docs/PHAN_TICH_MODULE_WAREHOUSE_SO_PO_DO_INVOICE_GRN.md` | Stub pointer cũ; đọc `FUNC_LOGIC/SALES_FULFILLMENT_SCHEMA_MATRIX.md`. |

## Pass 19 (2026-06-17) — retire report/archive đã gộp

| File đã xóa | Lý do / đọc thay |
| ----------- | ---------------- |
| `CLOUDSQL_ALLOWLIST_ARCHIVE.md` | Snapshot allowlist Cloud SQL 2026-04 đã gộp vào `docs/GCP_INVENTORY.md`; file report cũ đã xóa. |
| `BIOMIXING_DOCUMENTATION_SYNC_2026_06_10.md` | Báo cáo sync một lần; flow canonical đọc `FUNC_IMPROVE/BIOMIXING_BUSINESS_FLOW_LIVE.md`, `FUNC_IMPROVE/BIOMIXING_GAP_STATUS.md`, `FUNC_LOGIC/PRODUCTION_BUSINESS.md`. |
