# SO/PO Inventory Implementation Tracker (Staging)

## 1) Muc tieu

- Tach ro hai man hinh:
    - Inventory Balance: hien ton hien tai theo SKU x Warehouse (hoac SKU x Warehouse x Batch).
    - Inventory Transactions: hien lich su phieu (GRN, Adjust, SO Delivery, ...).
- Dam bao quy trinh:
    - PO -> GRN lam ton kho tang (+).
    - SO -> Delivery (shipped) lam ton kho giam (-).

## 2) Scope ky thuat

### Phase A - UX clarification (nhanh)

- [ ] Doi ten man hien tai thanh `Inventory Transactions`.
- [ ] Hien thi cot `Items count` (khong chi 1 SKU dai dien).
- [ ] Loai bo/doi ten cot de tranh hieu nham (`Available`, `Ending`) tren man Transactions.
- [ ] Chinh style label detail de de doc hon.
- [ ] An custom field rong (`--`) o detail (co option bat lai neu can).

### Phase B - Them man Inventory Balance

- [ ] Tao route + menu `Inventory Balance`.
- [ ] Query aggregate tu `warehouse_product_batches` theo:
    - `product_id`, `warehouse_id` (batch optional).
- [ ] Cot can co:
    - SKU, Product, Warehouse, On hand, Reserved, Available, Nearest expiry, Health.
- [ ] Them filter:
    - warehouse, product, expiry status, stock status.
- [ ] Them drill-down:
    - tu Balance sang Transactions theo SKU/kho.

### Phase C - Dong bo import

- [ ] Xac dinh import hien tai dang ghi vao Transactions.
- [ ] Sau import, refresh/aggregate balance.
- [ ] Khong doi API import neu khong can.
- [ ] Bo sung test regression cho import.

## 3) Data mapping (expected)

- Balance row key:
    - Default: `product_id + warehouse_id`
    - Neu bat batch-level: `product_id + warehouse_id + batch_id`
- Transactions:
    - 1 phieu co nhieu dong item.
- Quyet dinh quan trong:
    - Balance la derived data.
    - Transactions la source of truth.

## 4) Browser test plan (SO/PO -> Inventory + / -)

### Preconditions

- [ ] Da login staging.
- [ ] Co san it nhat 1 SKU track inventory.
- [ ] Co warehouse mac dinh.

### Test T1: PO -> GRN -> Inventory tang (+)

- [ ] Ghi nhan baseline Available cua SKU tai Inventory Balance.
- [ ] Tao/chon PO co SKU do.
- [ ] Tao GRN tu PO, save thanh cong.
- [ ] Verify Available tang dung theo qty nhap.

### Test T2: SO -> Delivery (shipped) -> Inventory giam (-)

- [ ] Ghi nhan baseline sau T1.
- [ ] Tao/chon SO co SKU do.
- [ ] Tao Delivery Order, ship qty > 0, save shipped.
- [ ] Verify Available giam dung theo qty ship.

### Test T3: End-to-end consistency

- [ ] Tong bien dong = (+ inbound) - (outbound).
- [ ] Transactions co du ban ghi GRN va SO Delivery.
- [ ] Khong co am ton bat thuong (neu business rule cam am ton).

## 5) Test execution log (staging)

| Time             | Test ID | Step                          | Result | Evidence URL                                            | Note                                                                                                                                                                                          |
| ---------------- | ------- | ----------------------------- | ------ | ------------------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| 2026-04-24 14:14 | T1      | PO -> GRN -> Inventory +      | PASS   | /account/grn, /account/sales-do/13                      | GRN `002` chuyen `Received`; tao movement inbound id `7099`, qty `+10`, product `8448`, warehouse `78`.                                                                                       |
| 2026-04-24 14:12 | T2      | SO -> Delivery -> Inventory - | PASS   | /account/orders/13, /account/sales-do/13                | SO `ODR#004` tao DO `SS-000013`, confirm + ship (Delivered); tao movement outbound id `7098`, qty `-1`, product `8448`, warehouse `78`.                                                       |
| 2026-04-24 14:14 | T3      | Reconcile + / -               | PASS   | DB `stock_movements`                                    | Cung product `8448` va warehouse `78`: net bien dong moi = `+10 - 1 = +9`, dung logic PO inbound / SO outbound.                                                                               |
| 2026-04-24 14:50 | T4      | PO(COM123) -> GRN -> Bill     | PASS   | /account/purchase-order/8, /account/grn, /account/bills | PO `PO#002` co SKU `COM123` qty `2`; GRN `003` doi `Received`; stock movement inbound id `7102` qty `+2` product `8449` kho `78`; Bill `BL#002` tao thanh cong (khong tao them movement kho). |

## 6) Acceptance criteria

- [ ] Cung SKU cung kho chi hien 1 dong tren Balance.
- [ ] Tao them phieu khong tao dong duplicate tren Balance.
- [ ] So lieu ton tren Balance khop voi bien dong Transactions.
- [ ] User khong con hieu nham giua ton hien tai va lich su phieu.

## 7) Trigger matrix (da xac minh)

| Flow                | Document | Status trigger    | Kho thay doi | Ghi chu                                                   |
| ------------------- | -------- | ----------------- | ------------ | --------------------------------------------------------- |
| PO -> GRN -> Bill   | GRN      | `received`        | `+` inbound  | Cong kho khi GRN chuyen `Received`.                       |
| PO -> GRN -> Bill   | Bill     | `open/draft/paid` | khong doi    | Bill la chung tu tai chinh, khong post stock movement.    |
| SO -> DO -> Invoice | Sales DO | `shipped`         | `-` outbound | Tru kho tai buoc Ship DO (status `shipped`).              |
| SO -> DO -> Invoice | Invoice  | `unpaid/paid`     | khong doi    | Invoice khong truc tiep tru/cong kho trong flow hien tai. |
