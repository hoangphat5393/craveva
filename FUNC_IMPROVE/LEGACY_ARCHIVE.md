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
| Vá UOM post RM (`convertToBase`)                                              | `15_PRODUCTION_OUTBOUND_UOM_GAP_VI.md` (Fixed) + `FUNC_BUG/PRODUCTION_RM_OUTBOUND_UOM_VI.md`                                      | **Đủ**                                                          |
| Post FG → Inventory ledger P1c                                                | `16_PRODUCTION_FG_INVENTORY_LEDGER_SYNC_VI.md`                                                                                    | **Đủ**                                                          |
| Checklist 5 bước lô, variance, trace                                          | `BIOMIXING_BUSINESS_FLOW_LIVE_VI.md` §3                                                                                           | **Đủ**                                                          |
| P0–P2 backlog Gary (P0-3, P1-2, waste %, …)                                   | `BIOMIXING_GAP_STATUS_VI.md` (bảng ID)                                                                                            | **Đủ** (không cần `PHASE2_PM_PLAN`)                             |
| Phase 1 báo giá gap chi tiết                                                  | `PHASE1_PM_STATUS_LIVE_VI.md`, `PHASE1_QUOTATION_PM_HUMAN_VI.md`                                                                  | Đủ cho go-live P1                                               |
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
| Doc sync 3 thư mục             | [`DOCUMENTATION_AUDIT_CROSS_FOLDER_2026_05_VI.md`](./DOCUMENTATION_AUDIT_CROSS_FOLDER_2026_05_VI.md)                                                                                           |
| P0 hàng đợi                    | [`P0_BIOMIXING_NEXT_STEPS_VI.md`](./P0_BIOMIXING_NEXT_STEPS_VI.md), [`P0_EXECUTION_LOG.md`](./P0_EXECUTION_LOG.md)                                                                             |

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
| `PHASE1_QUOTATION_PM_GAP_ANALYSIS_VI.md`         | → `PHASE1_PM_STATUS_LIVE_VI.md`                                                |
| `P0_NEXT_ACTION_BIOMIXING_VI.md`                 | Trùng `P0_BIOMIXING_NEXT_STEPS_VI.md`                                          |

## FUNC_REPORT (pass 2)

| File đã xóa      | Lý do                                                         |
| ---------------- | ------------------------------------------------------------- |
| `PM report 1.md` | Spec kho sơ bộ — đã có `FUNC_LOGIC/WAREHOUSE_MASTER_GUIDE.md` |
| `PM report 2.md` | Gap Miaolin — đã có `PROJECT MAOLIN/MAOLIN_MASTER_GUIDE.md`   |
