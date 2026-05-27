# Biomixing - Luong nghiep vu Phase 1 + Phase 2 (ban PM)

Ngay cap nhat: 2026-05-24  
Muc tieu: PM/BA doc 1 file la nam duoc luong nghiep vu Phase 1 va Phase 2, khong can mo nhieu tai lieu.

**Luong nghiep vu chuan (LIVE — cap nhat moi dot):** `FUNC_IMPROVE/BIOMIXING_BUSINESS_FLOW_LIVE_VI.md`  
**Tai lieu ky thuat dong bo:** `FUNC_IMPROVE/BIOMIXING_DOCUMENTATION_SYNC_2026_05_VI.md` · **Audit phase:** `FUNC_IMPROVE/BIOMIXING_FULL_PROCESS_AUDIT_2026_05_VI.md`

---

## 1) Boi canh nhanh (de hieu dung scope)

- Khach hang dat mua theo nghia thuong mai (SO), khong dat Production Order truc tiep.
- Phase 1 la luong thuong mai/duyet bao gia.
- Phase 2 la luong lap ke hoach truoc san xuat sau khi da chot SO.

---

## 2) Luong nghiep vu Phase 1 (Order Intake & Recipe Review)

### 2.1 Muc tieu

- Chot duoc de xuat thuong mai hop le de convert sang Sales Order.

### 2.2 Cac buoc

1. **Client Request**: khach gui nhu cau (san pham, so luong, deadline, yeu cau chat luong).
2. **Sales tao Estimate**: tao ban nhap bao gia.
3. **AI check recipe history** (neu bat): tra cuu lich su cong thuc/du lieu lien quan de ho tro quyet dinh.
4. **President Review**: duyet cap chien luoc/rui ro.
5. **VP Pricing Review**: duyet gia/margin/chinh sach gia.
6. **Convert to Sales Order**: khi da duyet thi chuyen thanh SO.

### 2.3 Dau ra can co

- Estimate/Quotation duoc duyet.
- Sales Order duoc tao hop le de chay sang planning.

### 2.4 Trang thai hien tai (tom tat)

- **Partial (~60%)**.
- Da co nen Estimate/Quotation va gate noi bo.
- Chua dong full loop proposal + AI assist ngay tai man estimate theo muc tieu de xuat.

---

## 3) Luong nghiep vu Phase 2 (Planning & Pre-Production)

### 3.1 Muc tieu

- Tu SO da chot, lap ke hoach san xuat de xuong co the thuc thi an toan.

### 3.2 Cac buoc

1. **Create Production Project/Order** tu SO.
2. **Check BOM & Stock** (co the co AI ho tro): xac dinh du/thieu nguyen lieu.
3. **Neu thieu**: tao mua bo sung va nhan nguyen lieu (RM).
4. **Generate Tasks** cho to san xuat.
5. **Print labels & batch info** de san sang vao xuong.

### 3.3 Dau ra can co

- Lenh/ke hoach san xuat da release theo BOM.
- RM planned ro rang.
- Task pre-production day du (nhan lo, nhan, chuan bi).

### 3.4 Trang thai hien tai (tom tat)

- **Gan day du MVP (~85%)**.
- Da co BOM CRUD, order lifecycle, snapshot BOM, planned RM co test, post RM/FG, **P1c** dong bo Inventory sau post FG (`16_PRODUCTION_FG_INVENTORY_LEDGER_SYNC_VI.md`).
- Con backlog multi-batch planning nang cao, UX variance badge (UX-008), UAT sign-off pilot.

---

## 4) Ranh gioi Phase 1 vs Phase 2 (tranh nham khi hop PM)

- **Phase 1** tra loi cau hoi: "Deal nay co duoc duyet de ban khong?"
- **Phase 2** tra loi cau hoi: "Sau khi ban duoc (co SO), xuong can chuan bi gi de san xuat?"
- Moc chuyen pha nghiep vu: **Convert to Sales Order**.

---

## 5) KPI goi y theo doi cho PM

- Thoi gian tu tao Estimate den Approve/Reject.
- Ty le Estimate convert sang SO.
- Thoi gian tu SO den Production Planning ready.
- Ty le don bi tre do thieu RM/BOM/ke hoach.

---

## 6) Tai lieu nguon doi chieu

- `PROJECT BIOMIXING/PHASE1_QUOTATION_FLOW_DIAGRAM.mmd`
- `PROJECT BIOMIXING/PHASE2_PLANNING_PREPRODUCTION.mmd`
- `PROJECT BIOMIXING/PHASE1_TO_3_END_TO_END_FLOW.mmd`
- `PROJECT BIOMIXING/PHASE1_QUOTATION_FLOW_DIAGRAM_TABLE.html`
- `PROJECT BIOMIXING/PHASE_BUSINESS_CONTEXT_AND_APPROVAL_NOTES_VI.md`
- `PROJECT BIOMIXING/BIOMIXING_PHASE1_MANAGEMENT_ONEPAGER_VI.md`
- `FUNC_TEST/01_BIOMIXING_TEST_MATRIX_VI.md`
- `FUNC_IMPROVE/BIOMIXING_DOCUMENTATION_SYNC_2026_05_VI.md`
- `FUNC_LOGIC/PRODUCTION_OPERATIONS_LIVE_VI.md`
- `FUNC_IMPROVE/BIOMIXING_GAP_STATUS_VI.md`
- `FUNC_IMPROVE/P0_MINI_UAT_CHECKLIST_BIOMIXING_VI.md` (luong A–E)
