# Playbook Production — Phase 0–1 (rút gọn, 2026-05-27)

| Thuộc tính                 | Giá trị                                                                                                                                                                                                                                          |
| -------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| **Đối tượng**              | BA / Tech Lead cần **spike kỹ thuật** chưa có trong living doc                                                                                                                                                                                   |
| **SSOT vận hành**          | [`BIOMIXING_BUSINESS_FLOW_LIVE_VI.md`](./BIOMIXING_BUSINESS_FLOW_LIVE_VI.md) · [`../FUNC_LOGIC/PRODUCTION_OPERATIONS_LIVE_VI.md`](../FUNC_LOGIC/PRODUCTION_OPERATIONS_LIVE_VI.md) · [`BIOMIXING_GAP_STATUS_VI.md`](./BIOMIXING_GAP_STATUS_VI.md) |
| **Bản đầy đủ (~515 dòng)** | `git show HEAD~N:FUNC_IMPROVE/BIOMIXING_PLAYBOOK_P0P1_VI.md` hoặc lịch sử trước pass 11 — xem [`LEGACY_ARCHIVE.md`](./LEGACY_ARCHIVE.md)                                                                                                         |

**Repo:** `Modules/Production/` · **Shadow UOM:** mặc định tắt — [`11_SHADOW_YIELD_UOM_PLANNED_ANALYSIS_VI.md`](./11_SHADOW_YIELD_UOM_PLANNED_ANALYSIS_VI.md) §8.

---

## 0. Đọc trước (thứ tự)

1. `BIOMIXING_DOC_HUB_VI.md` / `BIOMIXING_PREP_INDEX_EN.md`
2. `BIOMIXING_BUSINESS_FLOW_LIVE_VI.md` — luồng bước–cửa–tồn
3. `BIOMIXING_GAP_STATUS_VI.md` — Done/Partial/Missing theo phase
4. `BIOMIXING_FLOW_CONCEPTS_VI.md` — RM/FG, BOM, reserve DO
5. `FUNC_LOGIC/ERP_SO_PO_DO_INV_WH_QA_VI.md` · `WAREHOUSE_INDEX.md`
6. UAT: `BIOMIXING_UAT_AND_TEST_GUIDE_VI.md` · `19_PRODUCTION_RM_RESERVE_AT_RELEASE_TEST_CASES_VI.md`

---

## 1. MVP đã triển khai (tóm tắt)

| Hạng mục                  | Trạng thái | Chi tiết                                          |
| ------------------------- | ---------- | ------------------------------------------------- |
| BOM + snapshot @ release  | Done       | `production_order_bom_snapshot_items`             |
| Order lifecycle + batch   | Done MVP   | draft → released → in_progress → completed        |
| Post RM / FG + trace      | Done MVP   | UOM convert §2 `PRODUCTION_OPERATIONS_LIVE_VI.md` |
| Reserve @ release         | Done       | Không reserve ở Draft                             |
| Multi-batch planned RM    | Partial    | Equal-split 1-batch MVP; backlog multi-batch      |
| Variance / FG policy      | Done       | `ProductionFgQuantityPolicyServiceTest`           |
| Reverse movement sau post | Không MVP  | Cancel draft/released chưa post                   |

---

## 2. Spike tích hợp Warehouse (giữ — chưa nhân bản ở live doc)

| Rủi ro                | Hướng xử lý                                                                              |
| --------------------- | ---------------------------------------------------------------------------------------- |
| **Outbound policy**   | Chốt `reference_type` production trong `WarehouseFlowPolicyService` — đọc trước khi code |
| **Batch chọn tay**    | MVP chọn lô RM; payload `warehouse_product_batch_id`                                     |
| **FG expiry**         | Map `expiration_date` / `manufacturing_date` inbound                                     |
| **Tenant module**     | `packages:modules` + `module_settings` — không chỉ `module:enable`                       |
| **Inbound trùng GRN** | `reference_type` riêng `production_receipt`; idempotency key                             |

**Nguyên tắc:** Production orchestrate; Warehouse là sự thật tồn — `StockMovementService` only.

---

## 3. ERD / migration (thứ tự đã có trong repo)

1. `production_boms` / `production_bom_items`
2. `production_orders` (+ `sales_order_id`, `project_id`)
3. `production_batches` · consumptions · outputs
4. `production_order_bom_snapshot_items` + snapshot columns on orders
5. `production_company_fg_policies`

Mọi bảng: `company_id`, audit timestamps. Chi tiết cột: migration trong `Modules/Production/Database/Migrations/`.

---

## 4. State machine (MVP)

**Order:** `draft` → `released` (snapshot) → `in_progress` → `completed` | `cancelled` (chưa post movement).

**Batch:** `posted_consumptions_at`, `posted_receipt_at` — không `completed` order nếu thiếu post FG theo rule MVP.

Luồng kỹ thuật đầy đủ: `BIOMIXING_BUSINESS_FLOW_LIVE_VI.md` §3.

---

## 5. Test (pointer)

| Suite              | File gợi ý                                       |
| ------------------ | ------------------------------------------------ |
| Posting / snapshot | `tests/Feature/ProductionPostingServiceTest.php` |
| FG policy          | `ProductionFgQuantityPolicyServiceTest.php`      |
| P0 evidence        | `P0BiomixingAutomatedEvidenceTest.php`           |
| Matrix             | `FUNC_TEST/01_BIOMIXING_TEST_MATRIX_VI.md`       |

Regression B2B song song: `WarehouseUpgradeP0Test`, `PurchaseInboundStockFlowTest`, …

---

## 6. Gap → mitigation (rollout an toàn)

| Gap                    | Mitigation bắt buộc                                            |
| ---------------------- | -------------------------------------------------------------- |
| Multi-batch planning   | Feature flag; `reference_type` riêng; test idempotency         |
| Variance approval      | Policy company; quyền `approve_production_variance`; audit log |
| Yield/UOM              | Shadow trước enforce — `11_SHADOW_*` §8                        |
| UAT E2E SO→DO→Invoice  | `P0_MINI_UAT_CHECKLIST_BIOMIXING_VI.md`                        |
| Estimate approval loop | Gate convert SO; không đổi DO/Invoice state machine            |

**Guardrails:** migration additive · feature flags · tách `reference_type` Production vs PO/GRN/DO · regression B2B pass trước pilot rộng.

**Rollout gợi ý:** Wave 1 — variance + UAT · Wave 2 — multi-batch · Wave 3 — yield/UOM + estimate approval + AI assist.

---

## 7. Bảo toàn B2B (SO→DO→Invoice · PO→GRN→Bill)

1. Gate Phase 1 chỉ trên **estimate → SO**; không sửa ship DO / Invoice.
2. Production inbound/outbound: `production_*` reference — không nhầm GRN mua hàng.
3. Pilot 1 tenant + `queue:restart` / feature flag rollback.

**4 phase (1 dòng):** Estimate (duyệt) → SO → Planning/Production → DO → Invoice. Chi tiết phase map: `BIOMIXING_GAP_STATUS_VI.md` · `PROJECT BIOMIXING/BIOMIXING_PHASES_1_4_SUMMARY_VI.md`.

---

## 8. Phase 1-first backlog (khi PM chốt)

- Workflow estimate: `draft → pending_president → pending_vp_pricing → approved`
- Chỉ `approved` convert SO (override có audit)
- AI assist-only trên estimate — human confirm bắt buộc

**DoD:** staging pass regression Sales + convert SO; không incident B2B core trong pilot window.

Chi tiết sprint scope (§14 bản cũ): `git log -1 -- FUNC_IMPROVE/BIOMIXING_PLAYBOOK_P0P1_VI.md` trước pass 11.
