# PROJECT BIOMIXING — sơ đồ, proposal & tài liệu sản phẩm

Thư mục **collateral sản phẩm** (diagram, proposal, PM gốc, UI runbook). **Triển khai / gap / UAT:** [`FUNC_IMPROVE/`](../FUNC_IMPROVE/) — hub [`BIOMIXING_DOC_HUB.md`](../FUNC_IMPROVE/BIOMIXING_DOC_HUB.md).

**File đã retire:** [`LEGACY_ARCHIVE.md`](LEGACY_ARCHIVE.md)

---

## Living docs (đọc trước)

| Mục                 | File                                                                                       |
| ------------------- | ------------------------------------------------------------------------------------------ |
| Luồng vận hành SSOT | [`BIOMIXING_BUSINESS_FLOW_LIVE.md`](../FUNC_IMPROVE/BIOMIXING_BUSINESS_FLOW_LIVE.md) |
| Trạng thái code     | [`BIOMIXING_GAP_STATUS.md`](../FUNC_IMPROVE/BIOMIXING_GAP_STATUS.md)                 |
| Demo kỹ thuật Hub   | [`BIOMIXING_FULL_DEMO_RUNBOOK.md`](../FUNC_IMPROVE/BIOMIXING_FULL_DEMO_RUNBOOK.md)   |
| UAT / test          | [`BIOMIXING_UAT_AND_TEST_GUIDE.md`](../FUNC_IMPROVE/BIOMIXING_UAT_AND_TEST_GUIDE.md) |
| Production ops      | [`PRODUCTION_BUSINESS.md`](../FUNC_LOGIC/PRODUCTION_BUSINESS.md)       |

---

## PM & bối cảnh nghiệp vụ

| File                                                                                         | Vai trò                                                             |
| -------------------------------------------------------------------------------------------- | ------------------------------------------------------------------- |
| [`BIOMIXING_COMPANY_PROFILE.md`](./BIOMIXING_COMPANY_PROFILE.md)                       | **Hồ sơ công ty** (deck + Shennong/NetSuite + mô hình B2B/gia công) |
| [`BIOMIXING_QUOTATION_EXAMPLES.md`](./BIOMIXING_QUOTATION_EXAMPLES.md)                 | **Ví dụ báo giá gia công** (Oldtown đơn giản + FreshTea có COA/nhãn lô) |
| [`PM_YEU_CAU_TONG_HOP.md`](./PM_YEU_CAU_TONG_HOP.md)                                   | Yêu cầu PM gốc (gộp Gary + Phase 1 OEM)                             |
| [`BIOMIXING_PHASES_1_4_SUMMARY.md`](./BIOMIXING_PHASES_1_4_SUMMARY.md)                 | Bản đồ Phase 1→4 một trang                                          |
| [`PHASE_BUSINESS_CONTEXT_EXAMPLE.md`](./PHASE_BUSINESS_CONTEXT_EXAMPLE.md)                   | President / VP / approval context                                   |

---

## UI runbook (thao tác Hub)

| Phase               | File                                                                                                 |
| ------------------- | ---------------------------------------------------------------------------------------------------- |
| 1 — Báo giá → SO    | [`UI_RUNBOOK_PHASE1_QUOTATION_TO_SO.md`](./UI_RUNBOOK_PHASE1_QUOTATION_TO_SO.md)               |
| 2 — Planning pre-SX | [`UI_RUNBOOK_PHASE2_PLANNING_PREPRODUCTION.md`](./UI_RUNBOOK_PHASE2_PLANNING_PREPRODUCTION.md) |

---

## SOP khách / demo

| File                                                                                                       | Vai trò                                  |
| ---------------------------------------------------------------------------------------------------------- | ---------------------------------------- |
| [`PRODUCTION_MODULE_SOP.md`](./PRODUCTION_MODULE_SOP.md) | SOP Production gửi khách / nội bộ vận hành |
| [`BIOMIXING_DEMO_SCRIPT.md`](./BIOMIXING_DEMO_SCRIPT.md)                                                   | Kịch bản demo ERP+AI (overlay marketing) |
| [`BIOMIXING_PROPOSAL_REVISED.md`](./BIOMIXING_PROPOSAL_REVISED.md) · PDF proposal                          | Positioning / scope ban đầu              |

---

## Diagram & assets

- `.mmd` + `.html`: `PHASE1_*`, `PHASE2_*`, `PHASE3_*`, `PHASE1_TO_3_*`, `FULL_FLOW_DIAGRAM.html`
- Test flow reserve: `PRODUCTION_RELEASE_RESERVE_TEST_FLOW_*.mmd`

Regenerate Word SOP: `pandoc PRODUCTION_MODULE_SOP.md -o PRODUCTION_MODULE_SOP_VI.docx`
