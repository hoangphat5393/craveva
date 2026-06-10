# Documentation Cleanup Candidates — 2026-06-10

## Mục tiêu

Rà soát đợt tiếp theo sau pass cleanup 2026-05-27 để xác định file Markdown nào có thể bỏ / gộp khi chức năng đã hoàn thành.

Nguyên tắc:

- Không xóa living doc, SOP, UAT evidence, hoặc file còn backlog mở.
- File planning đã hoàn tất chỉ nên giữ nếu còn chứa quyết định nghiệp vụ / lý do triển khai chưa có ở living doc.
- Trước khi xóa, gộp phần "tại sao triển khai như vậy" vào file canonical rồi ghi vào `LEGACY_ARCHIVE.md`.

## Kết luận nhanh

Pass này đã retire được baseline product-form cũ `21_PRODUCT_FORM_PRICING_CURRENT_STATE_VI.md` sau khi gộp phần cần giữ vào `20_BOM_FG_COST_SYNC_IMPLEMENTATION_PLAN_VI.md`.

Còn một nhóm **có thể retire sau khi gộp ngắn / sign-off**:

| File | Đề xuất | Lý do |
| ---- | ------- | ----- |
| `FUNC_IMPROVE/22_PRODUCT_FORM_UX_SIMPLIFICATION_PLAN_VI.md` | **Đã gộp + retire 2026-06-10** | Product form visibility matrix và quyết định UX đã gộp vào `20_BOM_FG_COST_SYNC_IMPLEMENTATION_PLAN_VI.md` §4.1.2. |
| `FUNC_IMPROVE/PHASE1_PM_STATUS_LIVE_VI.md` | **Đã gộp + retire 2026-06-11** | Status snapshot Phase 1 đã gộp vào `BIOMIXING_GAP_STATUS_VI.md` § Phase 1; giải thích PM giữ ở `PHASE1_QUOTATION_PM_HUMAN_VI.md`. |
| `FUNC_IMPROVE/BIOMIXING_PLAYBOOK_P0P1_VI.md` | Giữ ngắn hạn; retire sau khi không còn spike kỹ thuật mở | Đã rút gọn pass 11, nhưng vẫn còn section spike Warehouse / rollout guardrails chưa nhân bản ở living doc. |

## Nhóm nên giữ

| File | Lý do giữ |
| ---- | -------- |
| `FUNC_IMPROVE/04_WH_RUNBOOK_UPGRADE_VI.md` | WUP-08, WUP-09, WUP-10 còn backlog; đồng thời là runbook vận hành Warehouse. |
| `FUNC_IMPROVE/05_SO_DO_PO_GRN_REFACTOR_VI.md` | Phase 5 retirement còn Not Started. |
| `FUNC_IMPROVE/07_PRICING_MODULE_DEV_TASKS.md` | Chính file ghi backlog chưa implement. |
| `FUNC_IMPROVE/11_SHADOW_YIELD_UOM_PLANNED_ANALYSIS_VI.md` | Governance / feature flag shadow UOM, chưa bật mặc định. |
| `FUNC_IMPROVE/12_AI_THIRDPARTY_SO_OPTIONS_VI.md` | Quyết định kiến trúc REST AI -> SO; không chỉ là plan. |
| `FUNC_IMPROVE/13_OPENING_STOCK_VS_WAREHOUSE_STOCK_VI.md` | Có phần Done nhưng còn P2 / policy khi Warehouse off. |
| `FUNC_IMPROVE/14_CLIENT_LISTING_TABLE_UX_PLAN_VI.md` | UX-006 / client listing còn mở. |
| `FUNC_IMPROVE/20_BOM_FG_COST_SYNC_IMPLEMENTATION_PLAN_VI.md` | P1 code shipped nhưng UAT, tenant flag, P2/P3 còn mở; giữ như rollout tracker. |
| `FUNC_IMPROVE/P2_PRODUCT_UOM_KIOTVIET_PLAN_VI.md` | Triển khai A-C xong nhưng UAT còn lại; P2-UOM chưa đóng hoàn toàn. |
| `FUNC_IMPROVE/P0_BIOMIXING_NEXT_STEPS_VI.md` | Hàng đợi UAT/sign-off P0 còn được nhiều checklist trỏ tới. |
| `PROJECT BIOMIXING/*SOP*`, `UI_RUNBOOK_*`, quotation examples | SOP / runbook / ví dụ nghiệp vụ cho PM, QA, khách; không phải plan trung gian. |

## Đã retire trong pass này

| File | Đã gộp vào | Ghi chú |
| ---- | ---------- | ------- |
| `FUNC_IMPROVE/21_PRODUCT_FORM_PRICING_CURRENT_STATE_VI.md` | `FUNC_IMPROVE/20_BOM_FG_COST_SYNC_IMPLEMENTATION_PLAN_VI.md` §4.1.1 | Baseline trước P1; giữ lại matrix Product Type pricing và lý do bỏ legacy cost flag. |
| `FUNC_IMPROVE/22_PRODUCT_FORM_UX_SIMPLIFICATION_PLAN_VI.md` | `FUNC_IMPROVE/20_BOM_FG_COST_SYNC_IMPLEMENTATION_PLAN_VI.md` §4.1.2 | Giữ lại matrix visibility theo Product Type và quyết định UX chính. |
| `FUNC_IMPROVE/PHASE1_PM_STATUS_LIVE_VI.md` | `FUNC_IMPROVE/BIOMIXING_GAP_STATUS_VI.md` § Phase 1 | Gộp tóm tắt PM, tiến độ, cấu hình nhanh, phần không làm. |

## Candidate cần gộp nội dung trước khi xóa

### 1) Product form pricing docs

Canonical đề xuất:

- Product type / BOM: `FUNC_LOGIC/PRODUCTION_PRODUCT_TYPES_VI.md`
- Product form cost from BOM: `FUNC_IMPROVE/20_BOM_FG_COST_SYNC_IMPLEMENTATION_PLAN_VI.md` hoặc một file live mới nếu P1/P2 đã đóng.

Nội dung cần bảo tồn trước khi retire `21_*` / `22_*`:

- Vì sao bỏ `purchase_information`.
- Matrix Product Type -> Selling price / Cost price / UOM.
- Quyết định `goods` có Custom / cost from BOM.
- Service không có cost, RM/Semi/Packaging là cost-only.

### 2) Phase 1 status docs

Canonical đề xuất:

- `FUNC_IMPROVE/BIOMIXING_GAP_STATUS_VI.md`
- `PROJECT BIOMIXING/UI_RUNBOOK_PHASE1_QUOTATION_TO_SO_VI.md`
- `FUNC_IMPROVE/PHASE1_QUOTATION_PM_HUMAN_VI.md`

Đã retire `PHASE1_PM_STATUS_LIVE_VI.md` sau khi:

- `BIOMIXING_GAP_STATUS_VI.md` đã chứa trạng thái cuối và phần "không làm".
- Link trong `BIOMIXING_DOC_HUB_VI.md`, `PM_YEU_CAU_TONG_HOP_VI.md`, `PHASE1_QUOTATION_PM_HUMAN_VI.md` đã cập nhật.

## Checklist trước khi xóa file doc

1. Search link trực tiếp tới file.
2. Gộp phần nghiệp vụ / quyết định còn cần vào canonical.
3. Thêm dòng vào `FUNC_IMPROVE/LEGACY_ARCHIVE.md` hoặc archive tương ứng.
4. Chạy link grep lại để không còn reference sống.
5. Chỉ sau đó mới xóa file.

## Lệnh audit đã dùng

```powershell
Get-ChildItem -Path 'FUNC_IMPROVE','FUNC_LOGIC','FUNC_BUG','FUNC_REPORT','PROJECT BIOMIXING' -Recurse -Filter *.md -File |
  Select-String -Pattern 'đã triển khai','Done','Fixed','kế hoạch','plan','implementation','roadmap','backlog','gộp vào'
```

## Kết luận cho pass kế tiếp

Ưu tiên thấp-rủi-ro kế tiếp là xem xét rút gọn `BIOMIXING_PLAYBOOK_P0P1_VI.md`, nhưng chỉ sau khi phần spike kỹ thuật còn dùng được gộp vào living docs.

Không nên xóa các file WUP, P0, P2-UOM, Pricing, SO/DO/GRN trước khi backlog hoặc sign-off tương ứng đóng.
