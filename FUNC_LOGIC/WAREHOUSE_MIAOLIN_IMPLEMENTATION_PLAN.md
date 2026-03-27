# Warehouse x Miaolin - Implementation Plan (Sprint-Based)

Ngay cap nhat: 2026-03-27
Muc tieu: dong cac gap critical/high de dat sign-off UAT cho Miaolin inventory-aware sales.

---

## 0) Uoc luong thoi gian Scope A vs B (tham chieu — khong phai hop dong)

**Ghi chu:** So lieu duoi day la **person-day** (mot dev backend lam full ngay) + QA/UAT **chi bieu thi muc do**, **lich wall-clock** phu thuoc hop PM, so loi UAT, staging.

**Dung Cursor AI:** giup nhanh phan viet tai lieu, skeleton test, mot so doan code lap lai; **khong** thay the QA, quyet dinh nghiep vu, hay giam thoi gian cho UAT chay lai. Thuc te thuong tiet kiem **~10–25%** thoi gian **code/doc** neu team dung AI co chu dich; **lich calendar** it khi giam tuong ung vi cho sign-off.

| Pham vi                                                                                                                         | Dev (ngay)                                                                    | QA/UAT (ngay, chi thi) | Wall-clock dien hinh (1 dev + QA ban thoi gian)            |
| ------------------------------------------------------------------------------------------------------------------------------- | ----------------------------------------------------------------------------- | ---------------------- | ---------------------------------------------------------- |
| **A — Kho van hanh** (cau hinh, quyen, checklist B–G + H, fix loi UAT; **khong** bao gom ban hang tru ton kho)                  | **~3–10** (rong hon neu checklist co muc chua co tren build: bulk/import kho) | **~2–6**               | **~1–2 tuan** neu it bat ngo                               |
| **B — Miaolin day du** (A + outbound ban hang + reversal + xu ly legacy payment + kiem tra ton invoice theo kho + test hoi quy) | **~10–20**                                                                    | **~4–10**              | **~3–5 tuan** sau khi chot quyet dinh nghiep vu (Sprint 0) |
| **A roi B lien tiep**                                                                                                           | Tong dev ~**13–30** (co overlap tai lieu)                                     | Tong QA ~**6–16**      | Thuong **~4–7 tuan** + buffer neu quyet dinh PM tre        |

Chi tiet bang tieng Anh gui PM: `WAREHOUSE_PM_ENG_ALIGNMENT_BRIEF.md` (muc 6).

---

## 1) Executive summary

- Trang thai hien tai: Warehouse core da on dinh hon (validation/guard/error handling), nhung Miaolin con blocker o sales outbound.
- Muc tieu release:
    - Phase 1 (Go cho Warehouse core): tiep tuc UAT va deploy hardening da xong.
    - Phase 2 (Go cho Miaolin inventory-aware sales): bo sung sales outbound + reversal + bo xung guard config.

---

## 2) Scope va nguyen tac

### In scope

- Sales outbound via `StockMovementService`.
- Reversal flows (cancel/return/refund/void).
- Remove legacy stock mutation tai payment flow.
- Guard config tranh double-count inbound (PO/DO).
- UAT + regression test cho luong moi.

### Out of scope (defer)

- Deep-link ledger reference (nice-to-have).
- Full legacy data migration off `PurchaseStockAdjustment` (can ke hoach rieng).

### Nguyen tac

- Mot nguon su that ton kho: movement-based.
- Idempotent theo su kien nghiep vu (khong post trung).
- Uu tien su kien fulfillment thay vi payment de post outbound.

---

## 3) Ke hoach theo sprint

## Sprint 0 (0.5-1 ngay) - Alignment + design lock

### Muc tieu

- Chot trigger outbound sales va luat chon kho.

### Cong viec

- Chot trigger outbound (khuyen nghi: delivery confirmed / shipment completed).
- Chot warehouse selection rule:
    - Option A: per-line warehouse (uu tien)
    - Option B: invoice-level warehouse fallback
    - Option C: client default warehouse fallback cuoi
- Chot reversal matrix (cancel, return, refund partial/full).
- Chot idempotency key strategy (`reference_type + reference_id + line_id + action`).

### Deliverables

- Decision record 1 trang (PM + Tech Lead approved).
- Sequence diagram ngan cho outbound/reversal.

### Estimation

- Dev: 0.5d
- PM/BA: 0.5d
- QA input: 0.25d

---

## Sprint 1 (2-3 ngay) - Critical implementation

### Muc tieu

- Co outbound sales movement dung kho, khong con mutate ton kho theo payment legacy.

### Cong viec ky thuat

1. Tao SalesStockService (hoac adapter) goi `StockMovementService::recordOutbound`.
2. Hook vao trigger da chot (observer/service layer), post outbound theo tung item.
3. Them idempotency guard de tranh post trung khi update/retry webhook.
4. Cap nhat/disable `Modules/Purchase/Observers/PaymentObserver.php` khong mutate `PurchaseStockAdjustment` nua.
5. Log business-friendly + dev context cho event fail.

### Acceptance criteria

- Sales event hop le -> stock giam dung kho.
- Co movement outbound du reference.
- Retry event khong tao duplicate movement.
- Payment create/delete khong con thay doi stock legacy.

### Estimation

- Dev backend: 2.0-2.5d
- Code review + hardening: 0.5d

---

## Sprint 2 (1.5-2 ngay) - Reversal + policy safety

### Muc tieu

- Hoan thien reversal va loai bo rui ro double-count inbound config.

### Cong viec ky thuat

1. Implement reversal flows:

- cancel/void invoice
- return goods
- refund (neu co tac dong kho theo business)

2. Them startup/config guard:

- canh bao/fail-fast neu bat dong thoi 2 inbound flags tren production.

3. Them admin warning UI (settings/help text) ve canonical inbound flow.

### Acceptance criteria

- Reversal tao movement nguoc, stock ve dung.
- Prod config khong the silent-run voi 2 inbound flags = true.

### Estimation

- Dev backend: 1.5d
- Config/UI warning: 0.5d

---

## Sprint 3 (1.5-2 ngay) - UAT stabilization + test automation

### Muc tieu

- Dat tieu chi sign-off UAT va co test regression can ban.

### Cong viec

1. Feature tests:

- sales outbound success
- idempotency
- reversal flow
- no double-count inbound policy

2. UAT execution theo `WAREHOUSE_UAT_GO_NO_GO_SHEET.md`.
3. Fix nhanh cac bug UAT blocker.

### Acceptance criteria

- UAT pass >= 95%, khong con blocker critical/high.
- Test suite pass on CI.

### Estimation

- Dev + QA: 1.5-2d

---

## 4) Backlog sau sign-off (P3)

- Bo sung batch/expiry input o stock adjustment/transfer UI.
- Deep-link ledger reference den PO/DO/Inventory/Invoice.
- Ke hoach giam phu thuoc `PurchaseStockAdjustment` dai han.

---

## 5) Resource plan

- 1 Backend dev chinh (Warehouse/Sales integration)
- 1 Reviewer (part-time)
- 1 QA/UAT owner
- PM/BA chot rule nghiep vu va outbound trigger

Tong effort uoc tinh (khong tinh buffer): 5.5-8 ngay cong.
Khuyen nghi planning: 2 sprint (1 tuan + 1 tuan) de co buffer UAT.

---

## 6) Risk register + mitigation

1. Khong thong nhat trigger outbound

- Mitigation: Sprint 0 bat buoc sign-off decision truoc coding.

2. Duplicate posting do event fired nhieu lan

- Mitigation: idempotency key + unique guard + regression test.

3. Regression legacy reports dang doc `PurchaseStockAdjustment`

- Mitigation: mapping impact list, theo doi song song 1 thoi gian, feature flag rollout.

4. Prod config sai (bat 2 inbound)

- Mitigation: config guard + startup warning + checklist deploy.

---

## 7) Release strategy

## Release A (som)

- Deploy hardening Warehouse core da xong.
- Continue UAT warehouse core ngay.

## Release B (Miaolin readiness)

- Deploy Sprint 1 + 2 + 3.
- Gate: pass Go/No-Go sheet + PM sign-off.

---

## 8) Definition of Done (DoD)

- Sales outbound duoc ghi movement dung kho tai trigger da chot.
- Reversal da co va da test pass.
- Payment observer khong mutate stock legacy khong warehouse.
- Khong co inbound double-count do config conflict.
- UAT checklist pass, evidence day du, PM chap nhan.
