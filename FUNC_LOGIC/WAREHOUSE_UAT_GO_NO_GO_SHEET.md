# Warehouse UAT Go/No-Go Sheet (Miaolin)

Ngay cap nhat: 2026-03-28
Nguoi tong hop: AI Assistant

---

## 0) Uoc luong thoi gian (tham chieu)

- **Scope A (chi kho van hanh, sign-off cat bo phan “Miaolin sales outbound”):** ~**1–2 tuan** wall-clock dien hinh (dev ~3–10 ngay + QA ~2–6 ngay), tuy so loi UAT va muc checklist con thieu tren build.
- **Scope B (sign-off day du checklist + outbound ban hang):** them ~**3–5 tuan** sau khi chot quyet dinh nghiep vu (dev ~10–20 ngay + QA ~4–10 ngay).
- **Cursor AI:** ho tro tai lieu/code lap lai; **khong** thay the chu ky UAT — tiet kiem thuc te thuong **~10–25%** phan code/doc, calendar van phu thuoc QA/PM.

Chi tiet bang tieng Anh: `WAREHOUSE_PM_ENG_ALIGNMENT_BRIEF.md` §6. Bang tieng Viet day du: `WAREHOUSE_MIAOLIN_IMPLEMENTATION_PLAN.md` §0.

---

## 1) Trang thai tong quan

- [ ] Go
- [x] No-Go (hien tai — **cho bang chung UAT staging sau khi deploy code Scope B**)

Ly do No-Go hien tai:

- **Code:** Da co luong outbound/reversal/flag (xem `FUNC_LOGIC/WAREHOUSE_SCOPE_B_IMPLEMENTATION_LOG.md`). **Chua** co ket qua UAT checklist + bằng chứng movement/staging.
- **Quy trinh:** Can xac nhan tren staging voi `WAREHOUSE_SALES_OUTBOUND_ENABLED=true`, migration, va khong bat ca hai inbound PO+DO neu khong co y do.

---

## 2) Scope UAT can dat truoc sign-off

- Warehouse master: CRUD + delete guard + import + bulk action
- Stock adjustment: inbound/outbound + guard am ton
- Transfer: hop le/khong hop le + atomic transaction
- Movement ledger: filter/search/reference
- Purchase inbound: chon 1 canonical flow (PO delivered hoac DO received)
- Permissions + company scoping
- Miaolin readiness: sales outbound kho

---

## 3) Ket qua danh gia gap PM (quick verdict)

| Gap PM neu                                              | Ket qua verify code | Muc do   |
| ------------------------------------------------------- | ------------------- | -------- |
| Missing sales outbound integration                      | Dung                | Critical |
| Risk double-count inbound neu bat ca PO+DO              | Dung                | High     |
| Payment observer dang sua ton legacy khong theo kho     | Dung                | High     |
| UI chua nhap batch/expiry cho stock adjustment/transfer | Dung                | Medium   |
| Ledger reference chua co deep-link                      | Dung                | Low      |

---

## 4) Bang dieu kien Go/No-Go

| Dieu kien                                                              | Owner          | Trang thai             | Bang chung                           |
| ---------------------------------------------------------------------- | -------------- | ---------------------- | ------------------------------------ |
| Chon va khoa 1 inbound canonical flow (PO/DO) tren prod                | PM + Tech Lead | [ ]                    | `.env` + smoke test                  |
| Sales outbound duoc ghi qua `StockMovementService` tai trigger da chon | Dev            | [x] code               | **Staging:** movement rows + ton kho |
| Reversal flow (cancel/return/refund) tra ton dung                      | Dev            | [x] code (v1)          | **Staging:** xoa/update invoice      |
| PaymentObserver khong con mutate legacy stock khong warehouse          | Dev            | [x] code (khi flag ON) | unit test + code review              |
| UAT checklist pass >= 95% va khong con blocker                         | QA/UAT         | [ ]                    | UAT evidence sheet                   |

---

## 5) UAT evidence checklist (mau)

| Case                            | Pass/Fail | Screenshot/Link | Ghi chu |
| ------------------------------- | --------- | --------------- | ------- |
| Add stock happy path            |           |                 |         |
| Remove stock insufficient       |           |                 |         |
| Transfer same warehouse blocked |           |                 |         |
| Permission denied               |           |                 |         |
| Missing company context         |           |                 |         |
| Ledger filter/search            |           |                 |         |
| PO/DO inbound no double count   |           |                 |         |
| Sales outbound at trigger       |           |                 |         |

---

## 6) De xuat release gate

- Cho phep deploy hardening Warehouse da lam (validation/error handling/guard).
- Chua sign-off Miaolin Inventory-Aware Sales cho den khi xong outbound sales + reversal.
