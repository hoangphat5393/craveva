# Production - BOM va chinh sach Planned vs Actual FG

Ngay cap nhat: 2026-05-05

## 1) Tom tat van de hien tai

- Man hinh tao `Production order` co truong `BOM (Optional)` nhung dropdown co the rong neu chua co du lieu BOM.
- He thong hien cho phep nhap `FG quantity` lon hon `planned_quantity` vi chua co rule khong che.
- Chua co chinh sach nghiep vu ro rang theo tung company cho overproduction.

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

Can bo sung BOM master CRUD (hien chua day du end-to-end):

- BOM Header:
    - `output_product_id`, `version`, `is_default`, `effective_from`, `effective_to`, `status`
- BOM Items:
    - `component_product_id`, `quantity`, `unit_id`, `sort_order`, `yield_factor` (neu can)
- Rule:
    - 1 FG co nhieu version BOM
    - order/batch chot version tai thoi diem release (snapshot)

## 7) Backlog ky thuat de implement

1. Them config policy theo company (table config hoac module settings)
2. Them validation tong FG trong `StoreProductionBatchOutputRequest` va/or service layer
3. Them cot variance + reason + approval (migration neu chua co)
4. Hien thi KPI variance tren `orders.show` / `batches.show`
5. Tao man hinh BOM CRUD (list/create/edit/versioning)
6. Viet test:
    - strict block
    - controlled within tolerance
    - controlled over tolerance (block/warn)
    - flexible allow + reason

## 8) Tieu chi UAT gon

- Case 1: Planned 20, FG 20 => pass
- Case 2: Planned 20, FG 21 (tolerance 5%) => pass
- Case 3: Planned 20, FG 22 (tolerance 5%) => can ly do/approval hoac block (tuy policy)
- Case 4: BOM rong => user duoc huong dan tao BOM hoac cho chay khong BOM theo policy tenant
- Case 5: Release order xong, doi BOM version cu khong lam thay doi order da release
