# PROJECT BIOMIXING — sơ đồ, proposal & tài liệu sản phẩm

Thư mục này giữ **tài liệu không thuần “triển khai kỹ thuật trong repo”**:

- **Sơ đồ** (`.mmd`, bản `.html` render), **PDF proposal**, hình ảnh / PDF minh họa
- **Markdown** mang tính **proposal**, **demo**, **PM**, **bối cảnh Phase / approval** (President, VP Pricing, v.v.)
- **PM gốc (RTF):** [`PM request.rtf`](./PM%20request.rtf) → EN: [`PM_REQUEST.md`](./PM_REQUEST.md) · **VI:** [`PM_REQUEST_VI.md`](./PM_REQUEST_VI.md) (dịch: `python scripts/bulk_translate_file.py ... --mode md-whole`)

## Triển khai chức năng (dev / baseline / playbook / gap)

Toàn bộ **Markdown triển khai** nằm tại:

**[`FUNC_IMPROVE/`](../FUNC_IMPROVE/)** — các file triển khai có tiền tố `BIOMIXING_*` và audit tách `BIOMIXING_MIGRATION_AUDIT_2026_VI.md` (xem [`FUNC_IMPROVE/INDEX.md`](../FUNC_IMPROVE/INDEX.md) mục Biomixing).

- Chỉ mục (EN): [`FUNC_IMPROVE/BIOMIXING_PREP_INDEX_EN.md`](../FUNC_IMPROVE/BIOMIXING_PREP_INDEX_EN.md)
- Audit tách thư mục: [`FUNC_IMPROVE/BIOMIXING_MIGRATION_AUDIT_2026_VI.md`](../FUNC_IMPROVE/BIOMIXING_MIGRATION_AUDIT_2026_VI.md)

## Living documentation (trạng thái code — đọc trước demo/UAT)

| Mục                       | File                                                                                                                                                                                                 |
| ------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Luồng nghiệp vụ LIVE**  | [`FUNC_IMPROVE/BIOMIXING_BUSINESS_FLOW_LIVE_VI.md`](../FUNC_IMPROVE/BIOMIXING_BUSINESS_FLOW_LIVE_VI.md) — SSOT bước/gate/tồn kho; cập nhật mỗi đợt dev                                              |
| **Doc sync (2026-05-24)** | [`FUNC_IMPROVE/BIOMIXING_DOCUMENTATION_SYNC_2026_05_VI.md`](../FUNC_IMPROVE/BIOMIXING_DOCUMENTATION_SYNC_2026_05_VI.md) — manifest đồng bộ tài liệu Biomixing                                        |
| **Audit quy trình phase** | [`FUNC_IMPROVE/BIOMIXING_FULL_PROCESS_AUDIT_2026_05_VI.md`](../FUNC_IMPROVE/BIOMIXING_FULL_PROCESS_AUDIT_2026_05_VI.md)                                                                              |
| **Gap / đã làm gì**       | [`FUNC_IMPROVE/BIOMIXING_GAP_STATUS_VI.md`](../FUNC_IMPROVE/BIOMIXING_GAP_STATUS_VI.md)                                                                                                              |
| **Phase 1 / 2 PM**        | [`FUNC_IMPROVE/PHASE1_PM_STATUS_LIVE_VI.md`](../FUNC_IMPROVE/PHASE1_PM_STATUS_LIVE_VI.md), [`PHASE2_PM_PLAN_VI.md`](../FUNC_IMPROVE/PHASE2_PM_PLAN_VI.md)                                            |
| **UOM + giá (P2)**        | [`FUNC_IMPROVE/P2_PRODUCT_UOM_KIOTVIET_PLAN_VI.md`](../FUNC_IMPROVE/P2_PRODUCT_UOM_KIOTVIET_PLAN_VI.md)                                                                                              |
| **Post RM UOM**           | [`FUNC_IMPROVE/15_PRODUCTION_OUTBOUND_UOM_GAP_VI.md`](../FUNC_IMPROVE/15_PRODUCTION_OUTBOUND_UOM_GAP_VI.md) — **Fixed** 2026-05-20                                                                   |
| **Post FG → Inventory**   | [`FUNC_IMPROVE/16_PRODUCTION_FG_INVENTORY_LEDGER_SYNC_VI.md`](../FUNC_IMPROVE/16_PRODUCTION_FG_INVENTORY_LEDGER_SYNC_VI.md) — **P1c** 2026-05-23; backfill `production:backfill-fg-inventory-ledger` |
| **Opening stock ↔ kho**   | [`FUNC_IMPROVE/13_OPENING_STOCK_VS_WAREHOUSE_STOCK_VI.md`](../FUNC_IMPROVE/13_OPENING_STOCK_VS_WAREHOUSE_STOCK_VI.md)                                                                                |
| **Test & UAT một cửa**    | [`FUNC_IMPROVE/BIOMIXING_UAT_AND_TEST_GUIDE_VI.md`](../FUNC_IMPROVE/BIOMIXING_UAT_AND_TEST_GUIDE_VI.md)                                                                                              |
| **Audit 3 thư mục**       | [`FUNC_IMPROVE/DOCUMENTATION_AUDIT_CROSS_FOLDER_2026_05_VI.md`](../FUNC_IMPROVE/DOCUMENTATION_AUDIT_CROSS_FOLDER_2026_05_VI.md)                                                                      |
| **Luồng kho / SO**        | [`FUNC_LOGIC/SALES_FULFILLMENT_DOCS_INDEX.md`](../FUNC_LOGIC/SALES_FULFILLMENT_DOCS_INDEX.md)                                                                                                        |
