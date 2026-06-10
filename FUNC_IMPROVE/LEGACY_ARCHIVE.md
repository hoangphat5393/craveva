# FUNC_IMPROVE — Legacy archive index (2026-05-27 pass 2)

Các file dưới đây **đã xóa** khỏi repo vì hết vòng đời (plan/audit/prototype đã xong hoặc bị thay bằng living doc). **Không** khôi phục từ git trừ khi cần tra cứu lịch sử — dùng `git log -- <path>`.

## Ma trận bảo tồn nghiệp vụ (sau khi xóa)

| Nội dung trong file đã xóa                                                    | Đã ghi ở đâu?                                                                                                                     | Mức độ                                                          |
| ----------------------------------------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------- | --------------------------------------------------------------- |
| Reserve RM tại **Release** (không Draft), Cancel release, consume sau post RM | `FUNC_LOGIC/PRODUCTION_OPERATIONS_LIVE_VI.md`, `19_*_TEST_CASES`, flow `.mmd`                                                     | **Đủ** cho vận hành                                             |
| PM quyết định reserve / lý do không reserve Draft                             | Plan `19_*` (git) — **tóm tắt** trong `PRODUCTION_OPERATIONS_LIVE` §2                                                             | Đủ ngắn                                                         |
| FEFO phân bổ lô khi reserve                                                   | `PRODUCTION_OPERATIONS_LIVE_VI.md` §2                                                                                             | **Đủ** (ngắn)                                                   |
| Gán lô trên batch **không** tăng `reserved_quantity`                          | `PRODUCTION_OPERATIONS_LIVE` §2 + `BIOMIXING_BUSINESS_FLOW_LIVE` §2.6 / §3.3                                                      | **Đủ**                                                          |
| Material shortage (cross-order, công thức available)                          | `PRODUCTION_OPERATIONS_LIVE` §3 + filter UI (mặc định `active`)                                                                   | **Đủ** (2026-05-27)                                             |
| Vá UOM post RM (`convertToBase`)                                              | `PRODUCTION_OPERATIONS_LIVE_VI.md` §2 + `FUNC_BUG/PRODUCTION_RM_OUTBOUND_UOM_VI.md`                                               | **Đủ**                                                          |
| Post FG → Inventory ledger P1c                                                | `PRODUCTION_OPERATIONS_LIVE_VI.md` §2                                                                                             | **Đủ**                                                          |
| Checklist batch hiện tại 4 bước, variance, trace                              | `BIOMIXING_BUSINESS_FLOW_LIVE_VI.md` §3                                                                                           | **Đủ**                                                          |
| P0–P2 backlog Gary (P0-3, P1-2, waste %, …)                                   | `BIOMIXING_GAP_STATUS_VI.md` (bảng ID)                                                                                            | **Đủ** (không cần `PHASE2_PM_PLAN`)                             |
| Phase 1 báo giá gap chi tiết                                                  | `BIOMIXING_GAP_STATUS_VI.md`, `PHASE1_QUOTATION_PM_HUMAN_VI.md`                                                                   | Đủ cho go-live P1                                               |
| Nền kho SO/DO/GRN vs gap Production                                           | `FUNC_LOGIC/ERP_SO_PO_DO_INV_WH_QA_VI.md`, `QUY_TRINH_*` — **không** còn bảng «đọc baseline 2026» trong `BIOMIXING_BASELINE_PREP` | Thiếu nhẹ — dùng `BIOMIXING_PREP_INDEX_EN` + FUNC_LOGIC         |
| Roadmap CCP / Phase 3–4 kỹ thuật                                              | `BIOMIXING_GAP_STATUS` (P2+), `BIOMIXING_BUSINESS_FLOW` § backlog                                                                 | Tóm tắt; chi tiết phase cũ → `git show` `BIOMIXING_DEV_PLAN.md` |
| PM report kho / Miaolin gap                                                   | `WAREHOUSE_MASTER_GUIDE`, `MAOLIN_MASTER_GUIDE`                                                                                   | Đủ nếu đã maintain guide                                        |

**Khi cần chi tiết plan đã xóa:** `git show HEAD:<đường_dẫn_trong_bảng_pass_1_2>` (file vẫn trong lịch sử git).

## Thay thế bằng (đọc theo thứ tự)

| Nhu cầu                        | Đọc thay                                                                                                                                                                                       |
| ------------------------------ | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Trạng thái Phase 1 & 2 vs code | [`BIOMIXING_GAP_STATUS_VI.md`](./BIOMIXING_GAP_STATUS_VI.md)                                                                                                                                   |
| Luồng nghiệp vụ vận hành       | [`BIOMIXING_BUSINESS_FLOW_LIVE_VI.md`](./BIOMIXING_BUSINESS_FLOW_LIVE_VI.md)                                                                                                                   |
| Production reserve / lifecycle | [`../FUNC_LOGIC/PRODUCTION_OPERATIONS_LIVE_VI.md`](../FUNC_LOGIC/PRODUCTION_OPERATIONS_LIVE_VI.md)                                                                                             |
| UAT / test                     | [`BIOMIXING_UAT_AND_TEST_GUIDE_VI.md`](./BIOMIXING_UAT_AND_TEST_GUIDE_VI.md), [`19_PRODUCTION_RM_RESERVE_AT_RELEASE_TEST_CASES_VI.md`](./19_PRODUCTION_RM_RESERVE_AT_RELEASE_TEST_CASES_VI.md) |
| P0 hàng đợi                    | [`P0_BIOMIXING_NEXT_STEPS_VI.md`](./P0_BIOMIXING_NEXT_STEPS_VI.md)                                                                                                                             |

## Pass 1 (2026-05-27)

| File đã xóa                                          | Lý do                                              |
| ---------------------------------------------------- | -------------------------------------------------- |
| `18_PRODUCTION_MATERIAL_SHORTAGE_SUMMARY_PLAN_VI.md` | Đã triển khai → `PRODUCTION_OPERATIONS_LIVE_VI.md` |
| `19_PRODUCTION_RM_RESERVE_AT_RELEASE_PLAN_VI.md`     | Đã triển khai → test cases + flow mmd              |
| `CURSOR_AND_GIT_ACTIVITY_REPORT_*.md`                | Báo cáo lịch sử, không vận hành                    |

## Pass 2 (2026-05-27)

| File đã xóa                                      | Lý do                                                                          |
| ------------------------------------------------ | ------------------------------------------------------------------------------ |
| `15_PRODUCTION_OUTBOUND_UOM_FIX_PLAN_VI.md`      | Vá xong 2026-05-20 → `15_PRODUCTION_OUTBOUND_UOM_GAP_VI.md` (Fixed)            |
| `AUDIT_IMPROVE_2026_VI.md`                       | Audit định kỳ → `DOCUMENTATION_AUDIT_CROSS_FOLDER_2026_05_VI.md`               |
| `BIOMIXING_DOC_AUDIT_2026_VI.md`                 | Trùng cross-folder audit                                                       |
| `BIOMIXING_GAP_ANALYSIS.md`                      | → `BIOMIXING_GAP_STATUS_VI.md`                                                 |
| `BIOMIXING_FLOW_CRACEVA_GAP.md`                  | Gap cũ 2026-02                                                                 |
| `BIOMIXING_PROTOTYPE_PLAN_VI.md`                 | POC Production đã qua                                                          |
| `BIOMIXING_BASELINE_PREP_2026_VI.md`             | Baseline → `BIOMIXING_PREP_INDEX_EN.md` + `BIOMIXING_BUSINESS_FLOW_LIVE_VI.md` |
| `BIOMIXING_DOMAIN_INTEGRATION.md`                | Nội dung trong prep index / playbook                                           |
| `BIOMIXING_MIGRATION_AUDIT_2026_VI.md`           | Migration xong                                                                 |
| `BIOMIXING_PROPOSAL_TECH_MAP_VI.md`              | Trùng `PROJECT BIOMIXING/BIOMIXING_PROPOSAL_REVISED_*`                         |
| `BIOMIXING_DEV_PLAN.md`                          | Roadmap → `BIOMIXING_GAP_STATUS_VI.md` + playbook                              |
| `PHASE2_PM_PLAN_VI.md`                           | Phase 2 SX → `PRODUCTION_OPERATIONS_LIVE_VI.md` + `UI_RUNBOOK_PHASE2_*`        |
| `03_PRODUCTION_PREUPLOAD_AUDIT_2026_05_05_VI.md` | Pre-upload audit một lần                                                       |
| `PHASE1_QUOTATION_PM_GAP_ANALYSIS_VI.md`         | → `BIOMIXING_GAP_STATUS_VI.md`                                                 |
| `P0_NEXT_ACTION_BIOMIXING_VI.md`                 | Trùng `P0_BIOMIXING_NEXT_STEPS_VI.md`                                          |

## FUNC_REPORT (pass 2)

| File đã xóa      | Lý do                                                         |
| ---------------- | ------------------------------------------------------------- |
| `PM report 1.md` | Spec kho sơ bộ — đã có `FUNC_LOGIC/WAREHOUSE_MASTER_GUIDE.md` |
| `PM report 2.md` | Gap Miaolin — đã có `PROJECT MAOLIN/MAOLIN_MASTER_GUIDE.md`   |

## Pass 4 (2026-05-27) — gộp file (không xóa living doc)

| File gộp / thay thế                                                      | → Canonical                                  |
| ------------------------------------------------------------------------ | -------------------------------------------- |
| `17_*`, `18_SETTINGS_*`, `18_APP_*`                                      | `UX_MENU_AND_SETTINGS_VI.md`                 |
| `CLIENT_IMPORT_MASTER` + `DETAILS`                                       | `CLIENT_IMPORT_VI.md`                        |
| `PRODUCT_IMPORT_MASTER` + `DETAILS`                                      | `PRODUCT_IMPORT_VI.md`                       |
| `MIAOLIN_SALES_ORDER_API_DATABASE_*` (2)                                 | `MIAOLIN_SALES_ORDER_API_FIELDS.md`          |
| `P0_05_*_EN` + `P0_05_*_VI`                                              | `P0_05_TRACE_BIDIRECTIONAL_UAT_CHECKLIST.md` |
| `CLOUDSQL_ALLOWLIST_*` (2)                                               | `CLOUDSQL_ALLOWLIST_ARCHIVE.md`              |
| `PRODUCTION_BATCH_STEP1_*`, `PRODUCTION_MODULE_AUDIT_*`                  | `PRODUCTION_OPERATIONS_LIVE_VI.md` §7–§8     |
| `PRODUCT_TYPE_BUYER_VS_INVENTORY_*`                                      | `PRODUCTION_PRODUCT_TYPES_VI.md` §0          |
| Stub: `FUNC_BUG/INDEX`, `AUDIT_BUG`, `AUDIT_IMPORT`, `FULL_TEST_SUITE_*` | README / REGISTRY / `FUNC_TEST/INDEX`        |

## Pass 5 (2026-05-27)

| File gộp / thay thế                          | → Canonical                        |
| -------------------------------------------- | ---------------------------------- |
| `BIOMIXING_DOCUMENTATION_SYNC_2026_05_VI.md` | `BIOMIXING_DOC_HUB_VI.md`          |
| _(mới)_                                      | `FUNC_BUG/STAGING_QUICK_REF_VI.md` |
| _(mới)_                                      | `FUNC_REPORT/INDEX.md`             |

## Pass 6 (2026-05-27) — xóa legacy đã hoàn thiện

| File đã xóa                                      | Lý do / đọc thay                                                                           |
| ------------------------------------------------ | ------------------------------------------------------------------------------------------ |
| `15_PRODUCTION_OUTBOUND_UOM_GAP_VI.md`           | Fixed → `PRODUCTION_OPERATIONS_LIVE_VI.md` §2, `FUNC_BUG/PRODUCTION_RM_OUTBOUND_UOM_VI.md` |
| `16_PRODUCTION_FG_INVENTORY_LEDGER_SYNC_VI.md`   | Done P1c → `PRODUCTION_OPERATIONS_LIVE_VI.md` §2                                           |
| `01_PROD_BOM_FG_POLICY_VI.md`                    | Đã triển khai → `PRODUCTION_OPERATIONS_LIVE_VI.md`, config Production                      |
| `PRODUCTION_MODULE_PROGRESS_REPORT_EN.md`        | Snapshot tiến độ → `BIOMIXING_GAP_STATUS_VI.md`                                            |
| `P0_EXECUTION_LOG.md`                            | Log lịch sử → `P0_BIOMIXING_NEXT_STEPS_VI.md`                                              |
| `BIOMIXING_FULL_PROCESS_AUDIT_2026_05_VI.md`     | Audit một lần → `BIOMIXING_GAP_STATUS_VI.md`                                               |
| `DOCUMENTATION_AUDIT_CROSS_FOLDER_2026_05_VI.md` | Meta audit → `FUNC_REPORT/DOCUMENTATION_CLEANUP_AUDIT_2026_05_27.md`                       |
| `19_WAREHOUSE_LABEL_AUDIT_VI.md`                 | Label đã sửa → `docs/platform-help/02-GLOSSARY.md`                                         |
| `FUNC_IMPORT/IMPORT_PROMPTS_ARCHIVE_VI.md`       | Import đã triển khai → `IMPORT_SPECS_VI.md`                                                |
| `FUNC_BUG/STAGING_INCIDENTS_ARCHIVE_VI.md`       | Incident cũ → `docs/SERVER_RUNBOOK_VI.md`, `STAGING_QUICK_REF_VI.md`; lịch sử: `git log`   |
| 15× `FUNC_LOGIC/AUDIT_*` + analysis              | Snapshot audit → master guides (xem `FUNC_LOGIC/LEGACY_ARCHIVE.md`)                        |

## Pass 7 (2026-05-27) — archive / gộp backlog

| File đã xóa                                   | Lý do / đọc thay                                                                                |
| --------------------------------------------- | ----------------------------------------------------------------------------------------------- |
| `08_CLIENT_IMPORT_REVIEW_AND_IMPROVEMENTS.md` | Archived → `FUNC_LOGIC/FLOW_ADD_CLIENT.md`                                                      |
| `02_B2B_PROD_BATCH_VI.md`                     | Kết luận B2B vs Production batch → `WAREHOUSE_MASTER_GUIDE.md`, `BIOMIXING_FLOW_CONCEPTS_VI.md` |
| `SO_AI_WEBHOOK_PROMPTS_VI.md`                 | Rollout xong → `docs/AI_ORDER_INTEGRATION_REST.md`, `12_AI_THIRDPARTY_SO_OPTIONS_VI.md`         |
| `06_INVENTORY_BUSINESS_IMPROVE.md`            | Gộp opening/FG → `13_OPENING_STOCK_VS_WAREHOUSE_STOCK_VI.md`                                    |
| `10_UX_UI_IMPROVEMENT_BACKLOG.md`             | Gộp → `UX_MENU_AND_SETTINGS_VI.md` Phần D                                                       |

## Pass 8 (2026-05-27)

| File                             | Thay đổi                                               |
| -------------------------------- | ------------------------------------------------------ |
| `05_SO_DO_PO_GRN_REFACTOR_VI.md` | Rút gọn ~780→120 dòng; giữ cutover, Artisan, Phase 4–5 |
| `PROJECT BIOMIXING/` (8 file)    | `PROJECT BIOMIXING/LEGACY_ARCHIVE.md`                  |

## Pass 9 (2026-05-27) — pre-delete audit, Tier 0 only

| File / artifact đã xóa                                          | Lý do                              |
| --------------------------------------------------------------- | ---------------------------------- |
| `FUNC_LOGIC/DESIGN_BACKEND_UI_UX_VI copy.md`                    | Trùng `DESIGN_BACKEND_UI_UX_VI.md` |
| `public/js/custom copy.js`                                      | Không Mix / không reference        |
| `resources/views/sections/menu.blade.backup-20260116.php`       | Backup view                        |
| `public/css/custom-css/theme-custom.backup-20260330-075832.css` | Backup CSS                         |

## Pass 10 (2026-05-27) — gộp Tier 1 rồi xóa

| File đã xóa                                                  | Gộp vào                                         |
| ------------------------------------------------------------ | ----------------------------------------------- |
| `P0_SHADOW_YIELD_UOM_GOVERNANCE_ROLLUP_VI.md`                | `11_SHADOW_YIELD_UOM_PLANNED_ANALYSIS_VI.md` §8 |
| `FUNC_BUG/ENG_TO_EN_STANDARDIZATION.md`                      | `FUNC_BUG/REGISTRY.md` — Phụ lục I18N-ENG-001   |
| `purchase_lang_audit_report.csv`                             | `scripts/audit_purchase_lang.php` + git history |
| `PROJECT BIOMIXING/PHASE1_QUOTATION_FLOW_DIAGRAM_TABLE.html` | `PHASE1_QUOTATION_FLOW_DIAGRAM.mmd` / `.html`   |
| `PRODUCTION_RELEASE_RESERVE_TEST_FLOW_EN.mmd` + `.html`      | `PRODUCTION_RELEASE_RESERVE_TEST_FLOW_VI.mmd`   |

## Pass 11–12 (2026-05-27) — gộp / rút gọn

| File                                                            | Thay đổi                                                          |
| --------------------------------------------------------------- | ----------------------------------------------------------------- |
| `BIOMIXING_PLAYBOOK_P0P1_VI.md`                                 | Rút gọn ~515→~120 dòng; SSOT → living docs + `git log` bản đầy đủ |
| `09_ORDER_HISTORY_IMPROVE_PLAN.md`                              | Gộp → `FUNC_IMPORT/IMPORT_POLL_TRACKERS_VI.md` §7                 |
| `DIAGRAM/pis_e2e_current_copy.mmd` + `.html`                    | Trùng bản đơn giản; giữ `pis_e2e_current.*`                       |
| `SPECIFICATION/DOCUMENTATION_AUDIT_SPECIFICATION_2026_05_VI.md` | Meta audit; nội dung trong `GCP_AND_CLOUDSQL_SNAPSHOT` + INDEX    |
| `LOG_REPORT/DOCUMENTATION_AUDIT_LOG_REPORT_2026_05_VI.md`       | Meta audit; ghi trong `LOG_REPORT/README.md`                      |
| `FUNC_LOGIC/PRODUCT_IMPORT_SLOWNESS_ANALYSIS.md`                | Rút gọn pass 13 → `IMPORT_CHUNK_AND_BULK_INSERT.md`               |

**Audit SSOT:** [`../FUNC_REPORT/LEGACY_PRE_DELETE_AUDIT_2026_05_27.md`](../FUNC_REPORT/LEGACY_PRE_DELETE_AUDIT_2026_05_27.md) · [`../FUNC_REPORT/LEGACY_PHP_AND_ASSET_CANDIDATES_2026_05_27.md`](../FUNC_REPORT/LEGACY_PHP_AND_ASSET_CANDIDATES_2026_05_27.md)

## Pass 14 (2026-06-10) — product form baseline retire

| File đã xóa | Lý do / đọc thay |
| ----------- | ---------------- |
| `21_PRODUCT_FORM_PRICING_CURRENT_STATE_VI.md` | Baseline trước P1 đã lỗi thời sau khi drop `purchase_information`; matrix Product Type pricing đã gộp vào `20_BOM_FG_COST_SYNC_IMPLEMENTATION_PLAN_VI.md` §4.1.1 |
| `22_PRODUCT_FORM_UX_SIMPLIFICATION_PLAN_VI.md` | P1 product form visibility đã triển khai phần lớn; matrix visibility và lý do UX đã gộp vào `20_BOM_FG_COST_SYNC_IMPLEMENTATION_PLAN_VI.md` §4.1.2 |
| `PHASE1_PM_STATUS_LIVE_VI.md` | Status snapshot Phase 1 đã gộp vào `BIOMIXING_GAP_STATUS_VI.md` § Phase 1; giải thích PM giữ ở `PHASE1_QUOTATION_PM_HUMAN_VI.md` |
