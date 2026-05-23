# Production - BOM va chinh sach Planned vs Actual FG

Ngay cap nhat: 2026-05-09

## 0) Trang thai trien khai (doi chieu vai code `Modules/Production`)

| Khoan muc                                                                                      | Trang thai                | Ghi chu ngan                                                                                                                                    |
| ---------------------------------------------------------------------------------------------- | ------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------- |
| Chinh sach FG theo company (Strict / Controlled / Flexible + tolerance / ly do chenh lech)     | **Da lam**                | `production_company_fg_policies` + man cau hinh FG quantity policy; `ProductionFgQuantityPolicyService` + validation khi tao output / post FG   |
| Cot variance tren output (`variance_from_planned_*`, `variance_reason`, …)                     | **Da lam**                | Migration `2026_05_06_120000_add_production_fg_policy_and_variance_columns.php`                                                                 |
| Hien thi KPI variance tren order / batch                                                       | **Da lam**                | `orders/show`, `batches/show` (bang output + summary tren order)                                                                                |
| BOM CRUD (header + items, version, khoa khi da co order dung BOM)                              | **Da lam**                | `production.boms.*`                                                                                                                             |
| Snapshot BOM khi **release** (dong bang dinh muc + planned FG tai thoi diem release)           | **Da lam**                | `production_order_bom_snapshot_items`, `bom_snapshot_at`, `bom_snapshot_planned_quantity`; logic trong `ProductionPostingService::releaseOrder` |
| Sinh dong tieu hao RM **planned** tu snapshot (MVP: 1 batch / lenh) + gan lo RM truoc khi post | **Mot phan**              | Nut tren batch + gan `warehouse_product_batch_id` tung dong; **chua** chia tong da batch                                                        |
| Gan `sales_order_id` / `project_id` tren form lenh SX                                          | **Da lam**                | Optional, hien thi tren chi tiet lenh (**chua** auto sinh PO tu SO / observer)                                                                  |
| `yield_factor` tren dong BOM                                                                   | **Da lam (shadow mode)**  | Da co cot + validate + mapping snapshot; dang dung cho shadow/UOM flow co flag, chua enforce dai tra cho moi tenant                             |
| `approved_by` / `approved_at` khi vuot tolerance                                               | **Da lam (co dieu kien)** | Da co cot DB + action approve variance (`outputs/{output}/approve-variance`); enforce phu thuoc cau hinh/tien trinh rollout                     |

## 1) Tom tat van de (truoc khi trien khai — ghi lai lich su)

- ~~Man hinh tao `Production order` co truong `BOM (Optional)` nhung dropdown co the rong neu chua co du lieu BOM.~~ — Van dung: can tao BOM truoc; co hint + link toi menu BOM.
- ~~He thong cho phep nhap FG > planned khong rule.~~ — **Da xu ly:** policy + validation + luu variance.
- ~~Chua co chinh sach theo company.~~ — **Da xu ly:** policy theo company.

## 2) Lam ro khái niem

- `BOM (Bill of Materials)` la cong thuc san xuat (dinh muc RM cho FG), khong phai invoice.
- `Planned quantity` la san luong ke hoach.
- `FG quantity` la san luong thuc te nhap kho theo tung dong output/batch.

## 3) Co duoc phep nhap FG > Planned khong?

Co, trong nhieu doanh nghiep la tinh huong hop le (overshoot theo me/may, hao hut thap hon dinh muc, quy doi don vi, rework...).

## 4) De xuat policy theo company (khong hardcode 1 kieu)

### A. Strict (chan cung)

- Tong `actual_fg` khong duoc vuot `planned_quantity`.
- Vuot => block luu.

### B. Controlled (khuyen nghi mac dinh)

- Cho phep vuot theo nguong:
    - `%`: vi du 5%
    - hoac gia tri tuyet doi: vi du +2 kg
- Vuot nguong => block hoac bat buoc phe duyet.

### C. Flexible (linh hoat)

- Cho phep vuot khong gioi han.
- Bat buoc ly do + audit trail + (tuy chon) approval.

## 5) Khuyen nghi mac dinh cho pilot Biomixing

- Mac dinh: `Controlled`
- Tolerance goi y: `5%`
- Bat buoc luu:
    - `planned_qty`, `actual_fg_total`, `variance_qty`, `variance_percent`
    - `variance_reason` (khi vuot tolerance)
    - `approved_by`, `approved_at` (neu co co che duyet)

## 6) Hoan thien BOM de nguoi dung tu nhap cong thuc

**Da trien khai (MVP):** BOM master CRUD end-to-end trong module Production.

- BOM Header: `output_product_id`, `version`, `code`, `is_default`, `effective_from`, `effective_to`, `notes` (khong co cot `status` rieng — dung version + default + lock khi da co order).
- BOM Items: `component_product_id`, `quantity`, `unit_id`, `sort_order`, `yield_factor` (tu 2026-05; dung cho shadow/UOM flow theo config).
- Rule:
    - 1 FG co nhieu version BOM — **co**
    - Order **chot dinh muc tai release (snapshot)** — **co** (`production_order_bom_snapshot_items`)

## 7) Backlog ky thuat — cap nhat trang thai

| #   | Muc tieu                                         | Trang thai                                                                                                          |
| --- | ------------------------------------------------ | ------------------------------------------------------------------------------------------------------------------- |
| 1   | Config policy theo company                       | **Xong** — `ProductionCompanyFgPolicy` + man `production/fg-quantity-policy`                                        |
| 2   | Validation tong FG (request + service)           | **Xong** — `StoreProductionBatchOutputRequest` + `ProductionFgQuantityPolicyService` + assert khi post FG receipt   |
| 3   | Cot variance + reason (+ approval)               | **Xong co dieu kien** — variance/reason + approved_by/approved_at + action approve da co; enforce theo rollout flag |
| 4   | KPI variance tren `orders.show` / `batches.show` | **Xong**                                                                                                            |
| 5   | Man hinh BOM CRUD                                | **Xong**                                                                                                            |
| 6   | Test (strict / controlled / flexible)            | **Xong** — `tests/Feature/ProductionFgQuantityPolicyServiceTest.php`, `ProductionPostingServiceTest.php`, …         |

**Backlog con lai (goi y):**

- Enforcement/phan quyen duyet variance cho moi tenant (khong chi co action approve).
- Enforce cong thuc Yield/UOM dai tra (hien uu tien shadow + governance).
- ~~**P0 — Post RM quy doi UOM**~~ **Fixed 2026-05-20** — [`15_PRODUCTION_OUTBOUND_UOM_GAP_VI.md`](./15_PRODUCTION_OUTBOUND_UOM_GAP_VI.md).
- Ho tro **nhieu batch / lenh**: chia planned RM theo allocation (hien MVP chi nut snapshot khi **1 batch**).

## 9) Specification Reconciliation Process (bat buoc truoc khi danh dau Done)

Ap dung cho nhom Production P0 de tranh tinh trang "doc noi da xong nhung code chua co" hoac nguoc lai.

1. **Doc -> doi chieu code:** moi claim "Da lam" phai co it nhat 1 bang chung code (migration/service/controller/route).
2. **Doc -> doi chieu test:** uu tien co test feature/unit lien quan; neu chua co test thi danh dau `Partial` + them backlog.
3. **Cap nhat ma tran trang thai:** dung 3 muc `Da lam`, `Da lam (co dieu kien)`, `Mot phan/Chua`.
4. **Ghi ro dieu kien rollout:** cac feature co flag (vd. shadow, enforce approval) phai ghi ro trang thai mac dinh.
5. **Dong bo theo chu ky:** moi lan truoc sprint planning hoac go-live review, cap nhat ngay muc 0 + muc backlog.

## 8) Tieu chi UAT gon

- Case 1: Planned 20, FG 20 => pass
- Case 2: Planned 20, FG 21 (tolerance 5%) => pass
- Case 3: Planned 20, FG 22 (tolerance 5%) => can ly do/approval hoac block (tuy policy)
- Case 4: BOM rong => user duoc huong dan tao BOM hoac cho chay khong BOM theo policy tenant
- Case 5: Release order xong, doi BOM version cu khong lam thay doi order da release
