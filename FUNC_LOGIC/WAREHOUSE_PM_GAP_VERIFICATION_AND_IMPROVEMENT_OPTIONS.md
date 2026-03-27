# Warehouse Gap Verification va Phuong An Cai Thien

Ngay cap nhat: 2026-03-27
Pham vi: xac minh cac gap PM neu trong `WAREHOUSE_UAT_CHECKLIST_MIAOLIN.md` va de xuat huong xu ly.

---

## 1) Ket luan nhanh

- Danh gia tong the: **PM neu gap la dung** o cac diem critical/high.
- Trang thai khuyen nghi: tiep tuc UAT Warehouse core, nhung **No-Go cho Miaolin inventory-aware sales** cho den khi bo sung sales outbound.

---

## 2) Xac minh tung gap PM (co doi chieu code)

## A. Missing sales outbound integration -> **DUNG (Critical)**

Bang chung:
- Khong tim thay luong sales goi `StockMovementService::recordOutbound` trong `app/Observers`, `app/Http/Controllers`, `Modules/Sales`.
- `InvoiceController` van check ton theo legacy: `PurchaseStockAdjustment::where('product_id', ...)->sum('net_quantity')`.

Tac dong:
- Khong tru ton kho theo warehouse khi ban hang.
- Khong enforce duoc "khong du ton theo kho" mot cach tin cay.
- Ledger `stock_movements` thieu dong outbound sales.

---

## B. Double-count inbound risk (PO + DO) -> **DUNG (High)**

Bang chung:
- `Modules/Warehouse/Config/config.php` co 2 flag inbound:
  - `inbound_from_purchase_order_delivered`
  - `inbound_from_delivery_order_received`
- `PurchaseOrderObserver` co goi inbound khi PO delivered.
- `DeliveryOrderObserver` co goi inbound khi DO received, va trong comment da canh bao risk double-count neu bat ca hai.

Tac dong:
- Phong to ton sai, sai bao cao, sai COGS/downstream.

---

## C. Payment observer mutate legacy stock khong warehouse -> **DUNG (High)**

Bang chung:
- `Modules/Purchase/Observers/PaymentObserver.php`:
  - khi payment created/deleting, goi `adjustStock()`
  - `adjustStock()` sua truc tiep `PurchaseStockAdjustment` theo `product_id` (khong co warehouse_id)

Tac dong:
- Lech voi so cai movement theo warehouse.
- De xung dot voi multi-warehouse va logic reservation/transfer.

---

## D. UI gap batch/expiry cho stock adjustment/transfer -> **DUNG (Medium)**

Bang chung:
- Form stock/transfer hien tai chua capture `batch_number` + `expiry_date`.
- Service co ho tro FEFO theo expiry, nhung dau vao UI chua day du.

Tac dong:
- Van chay duoc, nhung FEFO chua dat hieu qua toi da trong thao tac tay.

---

## E. Ledger deep-link va API stub -> **DUNG (Low)**

- Reference hien thi text/type/id, chua deep-link nguon chung tu.
- Co ghi chu API route stub trong UAT report (khong blocker).

---

## 3) Phuong an giai quyet / cai thien

## P1 (bat buoc truoc sign-off Miaolin)

1. Chot trigger sales outbound
- Lua chon khuyen nghi: tru ton tai su kien fulfillment (VD: delivery confirmed), khong tru theo payment.
- Tranh tru ton qua som (invoice tao) hoac qua muon (payment).

2. Ghi outbound qua `StockMovementService`
- Tao adapter/service cho sales:
  - input: `company_id`, `warehouse_id`, `product_id`, `quantity`, `reference_type`, `reference_id`.
- Moi dong hang -> 1 call outbound (hoac batch strategy).

3. Bo sung reversal flow
- Cancel/void/refund/return -> ghi movement nguoc de tra ton.
- Dam bao idempotent (tranh post 2 lan).

4. Tat mutate stock trong PaymentObserver
- Deprecate logic sua `PurchaseStockAdjustment` trong payment event.
- Neu can giu tam thoi, dat feature flag va log warning ro de theo doi.

## P2 (nen lam som)

5. Hard-lock inbound canonical flow
- Them startup health check: fail-fast neu bat ca 2 flag inbound o production.
- Them admin notice tren settings page neu config xung dot.

6. UAT automation co ban
- Feature test cho:
  - outbound sales tao movement dung kho
  - reversal tra ton
  - no double-count inbound PO/DO

## P3 (cai thien UX/bao tri)

7. Bo sung batch/expiry input cho stock adjustment/transfer UI
- Cho phep user nhap batch/expiry khi can.
- Giu fallback null cho luong don gian.

8. Ledger deep-link
- Reference type/id -> link toi PO/DO/Inventory/Invoice neu user co permission.

9. Tach migration du lieu legacy
- Ke hoach giam phu thuoc `PurchaseStockAdjustment` trong luong kho moi.

---

## 4) Lo trinh de xuat (ngan)

- Sprint A:
  - Chot trigger outbound sales + implement movement outbound + reversal.
  - Disable/replace PaymentObserver stock mutation.
- Sprint B:
  - Config guard cho inbound flags + tests regression.
  - UAT round 2 va sign-off.
- Sprint C (optional):
  - Batch/expiry UI + deep-link ledger.

---

## 5) Tieu chi hoan tat de PM ky

- Sales outbound da ghi stock movement dung kho tren trigger da chot.
- Reversal da co va da test pass.
- Khong con stock mutation khong warehouse trong payment flow.
- Khong con kha nang double-count inbound do config sai.
