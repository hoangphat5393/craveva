# PROJECT BIOMIXING — sơ đồ, proposal & tài liệu sản phẩm

Thư mục **collateral sản phẩm** (diagram, proposal, PM gốc, UI runbook). **Triển khai / gap / UAT:** [`FUNC_IMPROVE/`](../FUNC_IMPROVE/) — hub [`BIOMIXING_DOC_HUB_VI.md`](../FUNC_IMPROVE/BIOMIXING_DOC_HUB_VI.md).

**File đã retire:** [`LEGACY_ARCHIVE.md`](LEGACY_ARCHIVE.md)

---

## Living docs (đọc trước)

| Mục                 | File                                                                                       |
| ------------------- | ------------------------------------------------------------------------------------------ |
| Luồng vận hành SSOT | [`BIOMIXING_BUSINESS_FLOW_LIVE_VI.md`](../FUNC_IMPROVE/BIOMIXING_BUSINESS_FLOW_LIVE_VI.md) |
| Trạng thái code     | [`BIOMIXING_GAP_STATUS_VI.md`](../FUNC_IMPROVE/BIOMIXING_GAP_STATUS_VI.md)                 |
| Demo kỹ thuật Hub   | [`BIOMIXING_FULL_DEMO_RUNBOOK_VI.md`](../FUNC_IMPROVE/BIOMIXING_FULL_DEMO_RUNBOOK_VI.md)   |
| UAT / test          | [`BIOMIXING_UAT_AND_TEST_GUIDE_VI.md`](../FUNC_IMPROVE/BIOMIXING_UAT_AND_TEST_GUIDE_VI.md) |
| Production ops      | [`PRODUCTION_OPERATIONS_LIVE_VI.md`](../FUNC_LOGIC/PRODUCTION_OPERATIONS_LIVE_VI.md)       |

---

## PM & bối cảnh nghiệp vụ

| File                                                                         | Vai trò                                 |
| ---------------------------------------------------------------------------- | --------------------------------------- |
| [`PM_YEU_CAU_TONG_HOP_VI.md`](./PM_YEU_CAU_TONG_HOP_VI.md)                   | Yêu cầu PM gốc (gộp Gary + Phase 1 OEM) |
| [`BIOMIXING_PHASES_1_4_SUMMARY_VI.md`](./BIOMIXING_PHASES_1_4_SUMMARY_VI.md) | Bản đồ Phase 1→4 một trang              |
| [`PHASE_BUSINESS_CONTEXT_EXAMPLE.md`](./PHASE_BUSINESS_CONTEXT_EXAMPLE.md)   | President / VP / approval context       |

---

## UI runbook (thao tác Hub)

| Phase               | File                                                                                                 |
| ------------------- | ---------------------------------------------------------------------------------------------------- |
| 1 — Báo giá → SO    | [`UI_RUNBOOK_PHASE1_QUOTATION_TO_SO_VI.md`](./UI_RUNBOOK_PHASE1_QUOTATION_TO_SO_VI.md)               |
| 2 — Planning pre-SX | [`UI_RUNBOOK_PHASE2_PLANNING_PREPRODUCTION_VI.md`](./UI_RUNBOOK_PHASE2_PLANNING_PREPRODUCTION_VI.md) |

---

## SOP khách / demo

| File                                                                                                       | Vai trò                                  |
| ---------------------------------------------------------------------------------------------------------- | ---------------------------------------- |
| [`PRODUCTION_MODULE_SOP_VI.md`](./PRODUCTION_MODULE_SOP_VI.md) / [`_EN.md`](./PRODUCTION_MODULE_SOP_EN.md) | SOP Production gửi khách                 |
| [`BIOMIXING_DEMO_SCRIPT.md`](./BIOMIXING_DEMO_SCRIPT.md)                                                   | Kịch bản demo ERP+AI (overlay marketing) |
| [`BIOMIXING_PROPOSAL_REVISED.md`](./BIOMIXING_PROPOSAL_REVISED.md) · PDF proposal                          | Positioning / scope ban đầu              |

---

## Diagram & assets

- `.mmd` + `.html`: `PHASE1_*`, `PHASE2_*`, `PHASE3_*`, `PHASE1_TO_3_*`, `FULL_FLOW_DIAGRAM.html`
- Test flow reserve: `PRODUCTION_RELEASE_RESERVE_TEST_FLOW_*.mmd`

Regenerate Word SOP EN: `pandoc PRODUCTION_MODULE_SOP_EN.md -o PRODUCTION_MODULE_SOP_EN.docx`
