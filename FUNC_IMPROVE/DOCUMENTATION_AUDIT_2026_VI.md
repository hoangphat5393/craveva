# FUNC_IMPROVE — Documentation Audit (cập nhật 2026-05-09)

Bản rà soát **toàn bộ thư mục gốc** `FUNC_IMPROVE/`: cấu trúc, kiểm kê file, tham chiếu chéo, và quy ước Biomixing vs `PROJECT BIOMIXING/`.

---

## 1) Tóm tắt điều hành

| Chủ đề                                      | Trạng thái                                                                                                                                                                                                                                                                                                                                       |
| ------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| **Cấu trúc Biomixing**                      | **12 file** triển khai (`BIOMIXING_*`, `AUDIT_PROJECT_BIOMIXING_*`) nằm **cùng cấp** với backlog `01_`–`11_` và `P0_*` — **không** dùng thư mục con `FUNC_IMPROVE/BIOMIXING/` (đã gỡ 2026-05-09). `INDEX.md`, `BIOMIXING_PRODUCTION_PREP_INDEX_EN.md`, `PROJECT BIOMIXING/README.md`, `AUDIT_PROJECT_BIOMIXING_MIGRATION_2026_VI.md` đã đồng bộ. |
| **Tham chiếu `FUNC_IMPROVE` có tiền tố số** | Runbook / refactor / import / pricing / inventory: dùng `04_`, `05_`, … — đã đồng bộ trong repo `.md`.                                                                                                                                                                                                                                           |
| **Đổi tên `09_*`**                          | `09_ORDER_HISTORY_IMPROVE_PLAN.md` (phần mở rộng `.md` thường).                                                                                                                                                                                                                                                                                  |
| **Chỉ mục P0**                              | Năm file `P0_*.md` có **khối riêng** trong `INDEX.md` (song song backlog 01–11); không trùng tên file.                                                                                                                                                                                                                                           |

---

## 2) Kiểm kê `FUNC_IMPROVE/*.md` (30 file tại gốc)

| Nhóm                       | Số file | Tệp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                               |
| -------------------------- | ------: | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Chỉ mục & audit**        |       2 | `INDEX.md`, `DOCUMENTATION_AUDIT_2026_VI.md`                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                      |
| **Backlog đánh số**        |      11 | `01_` … `11_*.md`                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 |
| **P0 Biomixing (pilot)**   |       5 | `P0_EXECUTION_LOG.md`, `P0_MINI_UAT_CHECKLIST_BIOMIXING_VI.md`, `P0_NEXT_ACTION_BIOMIXING_VI.md`, `P0_SHADOW_YIELD_UOM_GOVERNANCE_ROLLUP_VI.md`, `P0_VARIANCE_APPROVAL_ROLE_MATRIX_VI.md`                                                                                                                                                                                                                                                                                                                                                                         |
| **Biomixing — triển khai** |      12 | `AUDIT_PROJECT_BIOMIXING_MIGRATION_2026_VI.md`, `BIOMIXING_DOC_STALE_AUDIT_AND_REPLACEMENTS_2026_VI.md`, `BIOMIXING_FLOW_CRACEVA_GAP.md`, `BIOMIXING_GAP_ANALYSIS.md`, `BIOMIXING_PRODUCTION_BASELINE_AND_PREP_2026_VI.md`, `BIOMIXING_PRODUCTION_DEVELOPMENT_PLAN.md`, `BIOMIXING_PRODUCTION_DOMAIN_INTEGRATION.md`, `BIOMIXING_PRODUCTION_FLOW_CONCEPTS_VI.md`, `BIOMIXING_PRODUCTION_IMPLEMENTATION_PLAYBOOK_PHASE0_1_VI.md`, `BIOMIXING_PRODUCTION_PREP_INDEX_EN.md`, `BIOMIXING_PRODUCTION_PROTOTYPE_PLAN_VI.md`, `BIOMIXING_PROPOSAL_TO_TECH_MAPPING_VI.md` |

**Tài liệu không thuần triển khai** (diagram, PDF, proposal PM, demo): **`PROJECT BIOMIXING/`** — xem `PROJECT BIOMIXING/README.md`.

**Không** còn thư mục con `FUNC_IMPROVE/BIOMIXING/`: `Test-Path FUNC_IMPROVE/BIOMIXING` → `False`.

---

## 3) Ràng buộc và liên kết chéo

- **Playbook:** `FUNC_IMPROVE/BIOMIXING_PRODUCTION_IMPLEMENTATION_PLAYBOOK_PHASE0_1_VI.md`
- **Cửa vào EN:** `FUNC_IMPROVE/BIOMIXING_PRODUCTION_PREP_INDEX_EN.md`
- **Proposal PDF:** `PROJECT BIOMIXING/2-4-2026_Biomixing_Proposal_CravevaERP_Formatted.pdf`
- **Shadow/Yield/UOM:** `11_SHADOW_YIELD_UOM_PLANNED_ANALYSIS_VI.md` + `P0_SHADOW_YIELD_UOM_GOVERNANCE_ROLLUP_VI.md` + config Production
- **Warehouse / WUP:** `04_WAREHOUSE_RUNBOOK_AND_UPGRADE_PLAN_VI.md`

---

## 4) Đề xuất (không bắt buộc)

1. ~~**`INDEX.md`:** Khối P0~~ — **đã có** (2026-05-09).
2. ~~**`FUNC_INDEX.md`:** Trỏ audit `FUNC_IMPROVE` / `FUNC_LOGIC`~~ — **đã thêm** (§6).

---

## 5) Tái kiểm nhanh

```powershell
(Get-ChildItem FUNC_IMPROVE -File -Filter *.md).Count   # 30
Test-Path FUNC_IMPROVE/BIOMIXING                        # False
```

```text
rg "FUNC_IMPROVE/BIOMIXING/" .        # không còn kết quả (trừ lịch sử trong audit nếu có)
rg "BIOMIXING/BIOMIXING_" FUNC_IMPROVE/INDEX.md
```

---

## 6) Audit pass — 2026-05-09 (bổ sung)

- **Kiểm kê:** `30` file `.md` tại gốc `FUNC_IMPROVE/` — khớp bảng §2 (2 + 11 + 5 + 12).
- **Tham chiếu nội bộ:** `rg` trong `FUNC_IMPROVE/*.md` — không còn path sống `FUNC_IMPROVE/BIOMIXING/`; các `04_`…`09_`, `BIOMIXING_*`, `P0_*` trỏ đúng tên file hiện có.
- **Gốc điều hướng:** `FUNC_INDEX.md` → `FUNC_IMPROVE/INDEX.md` — đã thêm dòng tới bản audit (mục 3).

---

_Bản ghi: audit cập nhật 2026-05-09 — layout phẳng gốc `FUNC_IMPROVE/`, gỡ `BIOMIXING/`._
