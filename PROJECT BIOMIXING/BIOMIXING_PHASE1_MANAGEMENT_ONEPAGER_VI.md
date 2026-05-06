# Biomixing Phase 1 - Management One Pager

Ngay cap nhat: 2026-05-06
Pham vi: Production + luong lien quan tu de xuat Biomixing (Estimate -> Production -> DO -> Invoice)

## 1) Muc tieu kinh doanh

- Rut ngan thoi gian duyet don/cong thuc tu vai ngay xuong cung ngay.
- So hoa luong san xuat (BOM, lenh SX, batch, tieu hao RM, nhap FG) de trace duoc.
- Giu an toan cho luong B2B dang chay (SO/DO/Invoice/Kho) khi mo rong tinh nang.

## 2) Trang thai hien tai (tom tat)

- **Da co va chay duoc (Production MVP):**
    - BOM CRUD + version.
    - Production Order: draft/release/cancel/completed.
    - Snapshot BOM khi release.
    - Post RM consumption / Post FG receipt.
    - FG policy (strict/controlled/flexible) + variance.
    - Traceability co ban theo batch.
- **Chua full theo proposal tong the:**
    - Multi-batch planning nang cao.
    - Approval rieng khi vuot tolerance (approved_by/approved_at).
    - Yield factor + UOM conversion nang cao.
    - Estimate approval loop theo proposal (Sales -> President -> VP) va AI recipe assist chua full.

## 3) Khoang trong quan trong (Gap)

- Gap A (it rui ro): UAT E2E sau voi Sales DO -> Invoice + approval variance.
- Gap B (trung binh): Multi-batch planning.
- Gap C (cao hon): Yield/UOM conversion + estimate approval + AI assist.

## 4) Rui ro neu implement nhanh khong co guardrail

- Lech ton kho hoac double-post movement (neu xu ly multi-batch sai).
- Vo luong B2B hien tai (PO/GRN/DO/Invoice) neu dung chung logic ma khong tach reference.
- Sai tinh planned/actual khi them yield/UOM conversion.
- User bo qua approval neu gate khong ro (vua co SO cu, vua co estimate approval moi).

## 5) Cach giam rui ro (bat buoc)

- Migration additive, khong pha schema/behavior cu.
- Feature flag theo company/tenant, rollout dan.
- Tach `reference_type` rieng cho Production.
- Bat buoc regression test B2B song song test Production.
- Co log/audit cho release, post RM, post FG, variance approval.

## 6) Quyet dinh can chot trong tuan nay

1. Co dong y rollout theo 3 wave (A -> B -> C) khong?
2. Co cho phep enforce approval variance ngay trong pilot tenant khong?
3. Team co uu tien multi-batch truoc hay estimate approval truoc?

## 7) Ke hoach 3 wave de de trien khai

- **Wave 1 (Pilot, 1-2 sprint):**
    - UAT E2E sau, approval variance (flag-on cho pilot tenant).
- **Wave 2 (Controlled, 1 sprint):**
    - Multi-batch planning + hardening idempotency.
- **Wave 3 (Advanced, 1-2 sprint):**
    - Yield/UOM conversion + estimate approval loop + AI assist (assist-only, human confirm).

## 8) KPI de theo doi

- Lead time duyet don (Estimate -> SO).
- Ty le order dung han giao.
- So lan loi stock movement/idempotency.
- Ty le variance FG vuot nguong va thoi gian approve.
- So incident anh huong luong B2B (muc tieu: 0).
