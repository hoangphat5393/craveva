# Audit toàn bộ quy trình BIOMIXING theo phase

**Ngày audit:** 2026-05-24  
**Phạm vi:** Đối chiếu tài liệu PM (`PROJECT BIOMIXING/`, `FUNC_IMPROVE/BIOMIXING_*`) với code `app/`, `Modules/Production`, `Modules/Purchase`, `Modules/Warehouse`, Estimate Phase 1.  
**Phương pháp:** Đọc gap/playbook/P0 log + chạy bundle test tự động (không thay thế UAT thủ công trên tenant pilot).

**Tài liệu liên quan:** [`BIOMIXING_GAP_STATUS_VI.md`](./BIOMIXING_GAP_STATUS_VI.md) · [`P0_EXECUTION_LOG.md`](./P0_EXECUTION_LOG.md) · [`P0_QA_BA_MASTER_TEST_CASE_TABLE_VI.md`](./P0_QA_BA_MASTER_TEST_CASE_TABLE_VI.md) · [`BIOMIXING_UAT_AND_TEST_GUIDE_VI.md`](./BIOMIXING_UAT_AND_TEST_GUIDE_VI.md)

---

## 1. Khung phase (tránh nhầm tên)

| Tên PM / Proposal       | Nội dung nghiệp vụ                         | Code / module chính                  | Trạng thái tổng (audit này)                |
| ----------------------- | ------------------------------------------ | ------------------------------------ | ------------------------------------------ |
| **Phase 1**             | Báo giá OEM, duyệt President/VP, chuyển SO | Estimate + `estimates_phase1_review` | **~95%** — đủ pilot; UAT A chưa ký         |
| **Phase 2 (planning)**  | Lệnh SX, BOM, tổng NL, SO → lệnh SX        | `Modules/Production`                 | **~80–85%** MVP; backlog multi-batch, CCP  |
| **Phase 2 (thực thi)**  | Lô SX: trừ NL, nhập TP, trace              | Production + Warehouse               | **~85%** sau P1c FG→Inventory; UX variance |
| **Nền Bán / Mua / Kho** | SO→DO→Invoice; PO→GRN→Bill                 | Core + Purchase + Warehouse          | **Đã có** (baseline 2026); UAT B/C chưa ký |
| **Phase 3+**            | QA shop floor, CCP, COA, sampling          | Roadmap                              | **Chưa** — chỉ mảnh rework/trace/policy    |

**Mốc chuyển phase PM:** SO được tạo từ Estimate đã duyệt → bắt đầu planning/sản xuất.

---

## 2. Phase 1 — Báo giá / Quotation (OEM)

### 2.1 Luồng nghiệp vụ (chuẩn PM)

1. Client request → Estimate
2. (Tuỳ chọn) AI / recipe history
3. President review → VP pricing review
4. Convert Sales Order (chặn nếu chưa duyệt)

### 2.2 Đối chiếu code

| Hạng mục                       | Trạng thái | Bằng chứng                                                        |
| ------------------------------ | ---------- | ----------------------------------------------------------------- |
| BOM trên báo giá               | ✅         | `estimate_bom_lines`, tests `EstimateBomLinesTest`                |
| Gate duyệt 2 cấp               | ✅         | `EstimatesPhase1ReviewGateTest`, permissions `approve_estimate_*` |
| Chặn SO khi chưa duyệt         | ✅         | `Estimate::isCommercialConversionAllowed()`                       |
| Revision / VP margin           | ✅         | `EstimateRevisionRequiredTest`, `EstimateVpMarginPolicyTest`      |
| Copy BOM Production → Estimate | ✅         | `EstimateProductionBomCopier`                                     |
| PDF có BOM                     | ✅         | 2026-05-20 partial PDF                                            |
| Bật theo tenant                | ✅         | `estimates_phase1_review` module setting                          |

### 2.3 Test tự động (2026-05-24)

```text
.\scripts\test.ps1 phase1  → 27 passed (99 assertions)
```

### 2.4 Gap / UAT

| ID          | Hạng mục                                   | Mức         | Ghi chú                         |
| ----------- | ------------------------------------------ | ----------- | ------------------------------- |
| P1-OPT      | Email template riêng từng bước duyệt       | Thấp        | Notification chung đủ pilot     |
| P1-OPT      | Snapshot BOM vào PDF lúc President approve | Thấp        | PDF = dữ liệu hiện tại          |
| **P0-08-A** | UAT luồng A trên UI tenant                 | **Chưa ký** | `P0_MINI_UAT_CHECKLIST` — BA/QA |

**Kết luận Phase 1:** **Sẵn sàng pilot kỹ thuật**; đóng phase sau biên bản UAT A.

---

## 3. Phase 2 — Planning & Production (MVP)

### 3.1 Luồng nghiệp vụ (chuẩn playbook)

```text
Draft order (FG, BOM?, kho RM/FG, planned qty, SO?)
  → Release (snapshot BOM)
  → Batch: sinh planned RM → gán lô RM → Post RM
  → Add finished product (batch TP, qty, kho) → Post FG
  → Order completed; Trace; Inventory list (sau P1c)
```

### 3.2 Đối chiếu code — đã có

| Hạng mục                                               | Trạng thái   | Ghi chú                                                                                          |
| ------------------------------------------------------ | ------------ | ------------------------------------------------------------------------------------------------ |
| BOM CRUD, FG vs RM dropdown                            | ✅           | UX-001 in progress                                                                               |
| Lệnh SX lifecycle                                      | ✅           | draft / released / in_progress / completed                                                       |
| Snapshot BOM @ release                                 | ✅           | `production_order_bom_snapshot_items`                                                            |
| Kho RM / kho TP (`rm_warehouse_id`, `fg_warehouse_id`) | ✅           | Trừ NL / nhập TP theo kho logic, không “xưởng vật lý”                                            |
| Planned RM + gán lô + post consumption                 | ✅           | MVP 1 batch/lệnh typical                                                                         |
| Post FG + batch_number bắt buộc                        | ✅           | `StoreProductionBatchOutputRequest`                                                              |
| FG policy (strict/controlled/flexible)                 | ✅           | Config + `/account/production/fg-quantity-policy`                                                |
| Variance columns + approve route                       | ✅           | `production.outputs.approve-variance`                                                            |
| Tổng NL trên lệnh + shortfall                          | ✅           | P0-3 `ProductionOrderMaterialRequirementsSummary`                                                |
| Tạo lệnh SX từ SO                                      | ✅           | P1-1 prefill `sales_order_id`                                                                    |
| Checklist 5 bước trên batch                            | ✅           | P0-4                                                                                             |
| % hao hụt BOM                                          | ✅           | P1-3                                                                                             |
| Prefill SO → lệnh SX                                   | ✅           | P1-4                                                                                             |
| UOM post RM (g→kg)                                     | ✅ **Fixed** | [`15_PRODUCTION_OUTBOUND_UOM_GAP_VI.md`](./15_PRODUCTION_OUTBOUND_UOM_GAP_VI.md)                 |
| FG → Inventory ledger                                  | ✅ **P1c**   | [`16_PRODUCTION_FG_INVENTORY_LEDGER_SYNC_VI.md`](./16_PRODUCTION_FG_INVENTORY_LEDGER_SYNC_VI.md) |
| Trace 2 chiều P↔W                                      | ✅ code      | UAT P0-05 chưa ký                                                                                |
| Rework cơ bản                                          | ✅           | Route + entity                                                                                   |
| SKU auto (Purchase)                                    | ✅           | P2-SKU 2026-05-21                                                                                |

### 3.3 Test tự động (2026-05-24)

| Bundle                                                                                                         | Kết quả       |
| -------------------------------------------------------------------------------------------------------------- | ------------- |
| `ProductionPostingServiceTest` + FG policy + material summary + warehouse batch/recon                          | **34 passed** |
| `P0BiomixingAutomatedEvidenceTest` + `BiomixingDemoRoutesReadinessTest` + variance permission + FG ledger unit | **11 passed** |

### 3.4 Gap còn lại (Phase 2)

| ID         | Hạng mục                                   | Mức                    | Khuyến nghị                                                                                                           |
| ---------- | ------------------------------------------ | ---------------------- | --------------------------------------------------------------------------------------------------------------------- |
| **P0-02**  | UAT variance approval + role matrix signed | Trung                  | BA 2 user (có/không `edit_production_orders`)                                                                         |
| **UX-VAR** | Badge variance                             | ✅ **Done 2026-05-24** | `outputVarianceApprovalUiState` — «Không yêu cầu» / «Chờ» / «Đã duyệt»; xem `BIOMIXING_BUSINESS_FLOW_LIVE_VI.md` §3.2 |
| **P0-05**  | UAT trace 2 chiều + screenshot             | Trung                  | Checklist `P0_05_TRACE_*`                                                                                             |
| P2+        | Nhiều batch / chia planned RM              | Trung                  | Backlog MVP                                                                                                           |
| P2-UOM     | UAT Oldtown + Luồng D trên UI              | Trung                  | Code done; biên bản D                                                                                                 |
| —          | CCP / receiving QC / COA / sampling        | Cao (phase sau)        | `BIOMIXING_GAP_STATUS_VI.md`                                                                                          |
| —          | Reverse movement sau post                  | Thấp                   | MVP idempotent only                                                                                                   |

### 3.5 Nghiệp vụ kho — đã vá (ghi cho audit)

| Triệu chứng                      | Nguyên nhân (đã xác định)                      | Trạng thái                          |
| -------------------------------- | ---------------------------------------------- | ----------------------------------- |
| Trace có TP, Inventory không có  | Post FG không tạo `purchase_stock_adjustments` | ✅ P1c auto-sync + backfill command |
| Opening stock ≠ tồn kho          | Hai sổ (product vs warehouse)                  | ✅ P1 opening sync + backfill       |
| Inventory search “GAGA” không ra | GAGA = mã lô, list filter theo SP              | **Đúng thiết kế** — tìm tên SP/SKU  |

**Backfill sau deploy:** `php artisan production:backfill-fg-inventory-ledger`

---

## 4. Nền platform — Bán · Mua · Kho (P0-07, P0-08 B/C)

Không thuộc “Phase 1–2 Biomixing” riêng nhưng **bắt buộc** cho pilot Oldtown.

| Luồng               | Tài liệu                                              | Code baseline        | UAT                                 |
| ------------------- | ----------------------------------------------------- | -------------------- | ----------------------------------- |
| SO → DO → Invoice   | `FUNC_LOGIC/QUY_TRINH_*`, `ERP_SO_PO_DO_INV_WH_QA_VI` | ✅ 2026-04 QA        | **P0-08-B** chưa ký                 |
| PO → GRN → Bill     | Cùng                                                  | ✅ inbound canonical | **P0-08-C** chưa ký                 |
| Warehouse WUP-01…07 | `04_WH_RUNBOOK_UPGRADE_VI` §2.1.1                     | ✅                   | **P0-07** ~85% — bảng UAT chưa điền |

**Test smoke route:** `P0BiomixingAutomatedEvidenceTest` (Estimate/SO/DO/Invoice/PO/GRN/Bill) — pass trong bundle 11 tests.

---

## 5. Bảng P0 Biomixing (từ execution log)

| Task  | Mô tả                           | Code                    | UAT / PM             |
| ----- | ------------------------------- | ----------------------- | -------------------- |
| P0-01 | FG policy pilot (controlled 5%) | ✅ config defaults      | TC-P0-01 — chưa ký   |
| P0-02 | Variance approval + role        | ✅ 95%                  | **Blocked sign-off** |
| P0-03 | Shadow yield/UOM OFF            | ✅                      | N/A (OFF)            |
| P0-04 | Warehouse batch list            | ✅ Done                 | Tùy pilot            |
| P0-05 | Trace 2 chiều                   | ✅ 97%                  | **Screenshot UAT**   |
| P0-06 | Reconciliation widget           | ✅ Done                 | TC-P0-06             |
| P0-07 | WH runbook WUP                  | ✅ doc                  | **Bảng 2.1.1**       |
| P0-08 | Mini UAT A/B/C/D                | ✅ smoke + Luồng D code | **Biên bản trống**   |

---

## 6. Ma trận “sẵn sàng go-live pilot”

| Tiêu chí                          | Đánh giá                                                                       |
| --------------------------------- | ------------------------------------------------------------------------------ |
| Phase 1 kỹ thuật                  | ✅                                                                             |
| Phase 2 MVP kỹ thuật              | ✅ (sau P1c + UOM fix)                                                         |
| UAT BA signed (A–D, P0-02, P0-05) | ❌ **Chưa**                                                                    |
| Dữ liệu cũ FG trên Inventory      | ⚠️ Cần **backfill** command                                                    |
| Multi-tenant / quyền              | Xem [`BIOMIXING_MULTITENANT_RISKS_VI.md`](./BIOMIXING_MULTITENANT_RISKS_VI.md) |
| Phase 3 (CCP/QA đầy đủ)           | ❌ Ngoài scope pilot                                                           |

**Verdict:** **Đủ điều kiện pilot kỹ thuật nội bộ / staging**; **chưa đủ** ký go-live sản xuất hàng loạt cho đến khi hoàn tất UAT trong `P0_QA_BA_MASTER_TEST_CASE_TABLE_VI.md` + backfill tenant.

---

## 7. Hành động ưu tiên (sau audit)

1. **BA/QA:** Chạy bảng master test case + mini UAT A–D; điền Pass/Fail; đính kèm evidence.
2. **Ops:** `production:backfill-fg-inventory-ledger` trên staging/production.
3. **PM:** Ký P0-02 role matrix; chốt có tắt `enforce_variance_approval` trên pilot hay giữ.
4. **Dev (backlog):** UX variance badge; optional Inventory list SSOT từ warehouse (`06_INVENTORY` P0).
5. **PM sign-off Phase 1:** Sau luồng A pass.

---

## 8. Lệnh kiểm tra nhanh (dev/QA)

```powershell
.\scripts\test.ps1 phase1

php artisan test --compact `
  tests/Feature/P0BiomixingAutomatedEvidenceTest.php `
  tests/Feature/BiomixingDemoRoutesReadinessTest.php `
  tests/Feature/ProductionVarianceApprovalPermissionTest.php `
  tests/Feature/ProductionPostingServiceTest.php `
  tests/Feature/ProductionFgQuantityPolicyServiceTest.php `
  tests/Unit/ProductionFgInventoryLedgerSyncTest.php `
  tests/Feature/WarehouseProductBatchRoutesTest.php

php artisan production:backfill-fg-inventory-ledger --dry-run
```

---

## 9. Lịch sử file audit

| Ngày       | Ghi chú                                                      |
| ---------- | ------------------------------------------------------------ |
| 2026-05-24 | Audit lần đầu — test bundles pass; tổng hợp phase + P0 + P1c |
