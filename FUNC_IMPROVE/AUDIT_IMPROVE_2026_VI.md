# FUNC_IMPROVE — Documentation Audit (cập nhật 2026-05-12)

Bản rà soát **toàn bộ thư mục gốc** `FUNC_IMPROVE/`: cấu trúc, kiểm kê file, tham chiếu chéo, và quy ước Biomixing vs `PROJECT BIOMIXING/`.

---

## 1) Tóm tắt điều hành

| Chủ đề                                      | Trạng thái                                                                                                                                                                                                                                                                                                                                       |
| ------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| **Cấu trúc Biomixing**                      | **12 file** triển khai (`BIOMIXING_*`, kèm `BIOMIXING_MIGRATION_AUDIT_2026_VI.md`) nằm **cùng cấp** với backlog `01_`–`11_` và `P0_*` — **không** dùng thư mục con `FUNC_IMPROVE/BIOMIXING/` (đã gỡ 2026-05-09). `INDEX.md`, `BIOMIXING_PREP_INDEX_EN.md`, `PROJECT BIOMIXING/README.md` đã đồng bộ. |
| **Tham chiếu `FUNC_IMPROVE` có tiền tố số** | Runbook / refactor / import / pricing / inventory: dùng `04_`, `05_`, … — đã đồng bộ trong repo `.md`.                                                                                                                                                                                                                                           |
| **Đổi tên `09_*`**                          | `09_ORDER_HISTORY_IMPROVE_PLAN.md` (phần mở rộng `.md` thường).                                                                                                                                                                                                                                                                                  |
| **Chỉ mục P0**                              | **Sáu** file trong khối P0 của `INDEX.md` (gồm `BIOMIXING_FULL_DEMO_RUNBOOK_VI.md`); năm file `P0_*.md` + runbook demo.                                                                                                                                                                                                                          |
| **AI → SO webhook**                         | **2026-05-12:** `13_` + `14_` + `15_` gộp thành **`SO_AI_WEBHOOK_PROMPTS_VI.md`** (Part 1–3); `12_AI_*` giữ riêng (phương án dài hạn).                                                                                                                                                                                        |

---

## 2) Kiểm kê `FUNC_IMPROVE/*.md` (33 file tại gốc — 2026-05-12)

| Nhóm                       | Số file | Tệp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                               |
| -------------------------- | ------: | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Chỉ mục & audit**        |       2 | `INDEX.md`, `AUDIT_IMPROVE_2026_VI.md`                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                      |
| **Backlog đánh số**        |      12 | `01_` … `12_*.md` (gồm `12_AI_THIRDPARTY_SO_OPTIONS_VI.md`)                                                                                                                                                                                                                                                                                                                                                                                                                                                                                  |
| **AI → SO webhook (gộp)**  |       1 | `SO_AI_WEBHOOK_PROMPTS_VI.md` — thay `13_`, `14_`, `15_` (Part 1–3)                                                                                                                                                                                                                                                                                                                                                                                                                                                                            |
| **P0 Biomixing (pilot)**   |       6 | `BIOMIXING_FULL_DEMO_RUNBOOK_VI.md`, `P0_EXECUTION_LOG.md`, `P0_MINI_UAT_CHECKLIST_BIOMIXING_VI.md`, `P0_NEXT_ACTION_BIOMIXING_VI.md`, `P0_SHADOW_YIELD_UOM_GOVERNANCE_ROLLUP_VI.md`, `P0_VARIANCE_APPROVAL_ROLE_MATRIX_VI.md`                                                                                                                                                                                                                                                                                                                                    |
| **Biomixing — triển khai** |      12 | `BIOMIXING_MIGRATION_AUDIT_2026_VI.md`, `BIOMIXING_DOC_AUDIT_2026_VI.md`, `BIOMIXING_FLOW_CRACEVA_GAP.md`, `BIOMIXING_GAP_ANALYSIS.md`, `BIOMIXING_BASELINE_PREP_2026_VI.md`, `BIOMIXING_DEV_PLAN.md`, `BIOMIXING_DOMAIN_INTEGRATION.md`, `BIOMIXING_FLOW_CONCEPTS_VI.md`, `BIOMIXING_PLAYBOOK_P0P1_VI.md`, `BIOMIXING_PREP_INDEX_EN.md`, `BIOMIXING_PROTOTYPE_PLAN_VI.md`, `BIOMIXING_PROPOSAL_TECH_MAP_VI.md` |

**Tài liệu không thuần triển khai** (diagram, PDF, proposal PM, demo): **`PROJECT BIOMIXING/`** — xem `PROJECT BIOMIXING/README.md`.

**Không** còn thư mục con `FUNC_IMPROVE/BIOMIXING/`: `Test-Path FUNC_IMPROVE/BIOMIXING` → `False`.

---

## 3) Ràng buộc và liên kết chéo

- **Playbook:** `FUNC_IMPROVE/BIOMIXING_PLAYBOOK_P0P1_VI.md`
- **Cửa vào EN:** `FUNC_IMPROVE/BIOMIXING_PREP_INDEX_EN.md`
- **Proposal PDF:** `PROJECT BIOMIXING/2-4-2026_Biomixing_Proposal_CravevaERP_Formatted.pdf`
- **Shadow/Yield/UOM:** `11_SHADOW_YIELD_UOM_PLANNED_ANALYSIS_VI.md` + `P0_SHADOW_YIELD_UOM_GOVERNANCE_ROLLUP_VI.md` + config Production
- **Warehouse / WUP:** `04_WH_RUNBOOK_UPGRADE_VI.md`

---

## 4) Đề xuất (không bắt buộc)

1. ~~**`INDEX.md`:** Khối P0~~ — **đã có** (2026-05-09).
2. ~~**`FUNC_INDEX.md`:** Trỏ audit `FUNC_IMPROVE` / `FUNC_LOGIC`~~ — **đã thêm** (§6).

---

## 5) Tái kiểm nhanh

```powershell
(Get-ChildItem FUNC_IMPROVE -File -Filter *.md).Count   # 33 (2026-05-12)
Test-Path FUNC_IMPROVE/BIOMIXING                        # False
```

```text
rg "FUNC_IMPROVE/BIOMIXING/" .        # không còn kết quả (trừ lịch sử trong audit nếu có)
rg "BIOMIXING/BIOMIXING_" FUNC_IMPROVE/INDEX.md
```

---

## 6) Audit pass — 2026-05-09 (bổ sung)

- **Kiểm kê:** `30` file `.md` tại gốc `FUNC_IMPROVE/` — khớp bảng §2 **phiên bản 2026-05-09** (2 + 11 + 5 + 12; thiếu đếm `12_` và `BIOMIXING_FULL_*`).
- **Kiểm kê 2026-05-12:** `33` file — khớp §2 đã sửa (2 + 12 + 1 + 6 + 12).
- **Tham chiếu nội bộ:** `rg` trong `FUNC_IMPROVE/*.md` — không còn path sống `FUNC_IMPROVE/BIOMIXING/`; các `04_`…`09_`, `BIOMIXING_*`, `P0_*` trỏ đúng tên file hiện có.
- **Gốc điều hướng:** `FUNC_INDEX.md` → `FUNC_IMPROVE/INDEX.md` — đã thêm dòng tới bản audit (mục 3).

---

---

## 7) Chu kỳ 2026-05-12 — gộp tài liệu AI → SO webhook

| Việc | Chi tiết |
| ---- | -------- |
| **Gộp** | `13_SALE_ORDER_AI_INTEGRATION_ROLLOUT_PROMPT_VI.md` + `14_SALE_ORDER_AI_WEBHOOK_ROLLOUT_PLAN_VI.md` + `15_SALE_ORDER_AI_SETTINGS_GUIDE_AND_RINGFENCE_PROMPT_VI.md` → **`SO_AI_WEBHOOK_PROMPTS_VI.md`** (anchor Part 1–3). |
| **Giữ** | `12_AI_THIRDPARTY_SO_OPTIONS_VI.md` — phương án API dài hạn. |
| **Tham chiếu đã chỉnh** | `FUNC_IMPROVE/INDEX.md`, `FUNC_LOGIC/AUDIT_AI_ORDER_INBOUND_SO_API_VI.md`, `FUNC_LOGIC/PM_READY_AI_WEBHOOK_STAGING_VI.md`, `FUNC_LOGIC/AUDIT_LOGIC_2026_VI.md` (bảng §7.1). |

---

## 8) Đổi tên file rút gọn (2026-05-12, cùng phiên bản repo)

Một loạt `.md` dưới `FUNC_BUG/`, `FUNC_IMPORT/`, `FUNC_IMPROVE/`, `FUNC_LOGIC/`, `FUNC_REPORT/`, `FUNC_TEST/` (và tham chiếu chéo trong `docs/`, `PROJECT BIOMIXING/`, `ai-context/`, v.v.) đã **đổi tên ngắn hơn**, nội dung giữ nguyên. Gợi ý tra cứu nhanh:

- Audit nhóm: `DOCUMENTATION_AUDIT_*` → `AUDIT_BUG_2026_VI.md`, `AUDIT_IMPORT_2026_VI.md`, `AUDIT_IMPROVE_2026_VI.md`, `AUDIT_LOGIC_2026_VI.md`.
- Import engine: `IMPORT_ENGINE_POLL_QUEUE_AND_TRACKERS_VI.md` → `IMPORT_POLL_TRACKERS_VI.md`; archive prompt → `IMPORT_PROMPTS_ARCHIVE_VI.md`.
- Biomixing: bỏ lặp `BIOMIXING_PRODUCTION_*` / `AUDIT_PROJECT_BIOMIXING_*` → tên `BIOMIXING_*` / `BIOMIXING_MIGRATION_AUDIT_2026_VI.md`, `BIOMIXING_PLAYBOOK_P0P1_VI.md`, v.v.
- Logic ERP/QA: `ERP_SO_PO_DO_INVOICE_WAREHOUSE_QA_VERIFICATION_VI.md` → `ERP_SO_PO_DO_INV_WH_QA_VI.md`; `WAREHOUSE_AND_PURCHASE_FLOW_ENV_REFERENCE_VI.md` → `WH_PURCHASE_ENV_REFERENCE_VI.md`; `SCHEMATIC_LAYER_*` → `SCHEMATIC_USERS_CLIENT_1_1_VI.md`; `CUSTOM_FIELDS_SYSTEMWIDE_*` → `CF_SYSTEMWIDE_AUDIT_VI.md`.
- AI webhook (gộp): `SALE_ORDER_AI_WEBHOOK_PLANS_AND_PROMPTS_VI.md` → `SO_AI_WEBHOOK_PROMPTS_VI.md`; backlog `12_*` SO options → `12_AI_THIRDPARTY_SO_OPTIONS_VI.md`.
- Cloud SQL snapshot trong `FUNC_REPORT/`: `CloudSQL_Allowlist_*` → `CLOUDSQL_ALLOWLIST_*`; script `scripts/export_sql_allowlist.ps1` ghi file `CLOUDSQL_ALLOWLIST_STATUS_<timestamp>.md`, mặc định thư mục `FUNC_REPORT/`.
- **`scripts/`:** audit + dọn artifact — xem `scripts/AUDIT_2026_VI.md`.

_Bản ghi: audit 2026-05-09 (layout phẳng, gỡ `BIOMIXING/`); bổ sung §7 + kiểm kê §2 ngày 2026-05-12; §8 đổi tên rút gọn cùng ngày._
