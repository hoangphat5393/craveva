# Business Invariants

## Warehouse

| ID | Invariant | Ket qua |
|---|---|---|
| WH-01 | Moi physical stock delta phai co movement cung transaction. | **Fail by design** khi retry key trung: batch doi truoc khi movement no-op. |
| WH-02 | `warehouse_product_stock = SUM(batch.quantity)` theo warehouse/product. | Pass tai thoi diem doi soat: 0 mismatch. |
| WH-03 | `batch.reserved_quantity = SUM(active reservations)` theo identity. | Pass tai thoi diem doi soat: 0 mismatch. |
| WH-04 | `0 <= reserved <= quantity` tru khi policy ro rang cho phep khac. | Code reserve co guard; can them reconciliation dinh ky. |
| WH-05 | Mot idempotency key chi duoc tao mot movement. | DB **khong enforce unique**; current data 0 duplicate group. |
| WH-06 | Mot batch identity chi co mot row. | Unique key co nullable columns, khong du an toan trong MySQL. |

## Purchase / GRN

| ID | Invariant | Ket qua |
|---|---|---|
| GRN-01 | Received accepted quantity = inbound movement quantity. | Current data 0 mismatch. |
| GRN-02 | Sua/xoa/downgrade received phai reverse hoac bi chan. | **Fail**. UI/service cho phep va khong compensation. |
| GRN-03 | Chi mot canonical purchase event tang physical stock. | Config pass: PO false, GRN true; legacy adjustment van tang khi PO delivered. |

## Sales

| ID | Invariant | Ket qua |
|---|---|---|
| SAL-01 | Confirm reserve; ship outbound mot lan; invoice khong outbound lai trong shipment mode. | Happy path pass; current data khong co applied-without-movement. |
| SAL-02 | Reverse/cancel dua physical va reservation ve trang thai hop le. | Sequential tests pass; concurrent test thieu. |
| SAL-03 | Invoice quantity khong vuot shipped quantity. | Guard co tenant/order/product aggregation; race giua hai invoice chua duoc serialize. |

## Production

| ID | Invariant | Ket qua |
|---|---|---|
| PROD-01 | Draft khong reserve; Released reserve; Post RM consume reservation. | Happy path pass khi co excess stock. |
| PROD-02 | Lenh duoc phep dung reservation cua chinh no. | **Fail edge case** khi stock vua bang reserved quantity. |
| PROD-03 | RM/FG moi line post toi da mot lan. | App timestamp/key co, nhung thieu row lock va DB unique. |
| PROD-04 | Completed chi khi moi batch/output hoan tat. | Sequential tests pass. |
| PROD-05 | Cancel released chua post release reservation; da post thi block/reverse ro rang. | Pass theo code; khong co reverse cho In progress. |
| PROD-06 | Planned RM = base qty x planned FG x (1 + waste%). | Implemented va round 6 decimals. |
| PROD-07 | Yield factor tac dong operational consumption neu business yeu cau. | Chua: chi shadow va config dang false. |

## Estimate / finance

| ID | Invariant | Ket qua |
|---|---|---|
| EST-01 | Retry cung import row khong doi net state. | **Fail**. |
| EST-02 | Mot Estimate convert mot lan neu contract 1:1. | App check co; DB/transaction lock khong co. |
| EST-03 | Header total bang tong line sau discount/tax theo rounding policy. | Targeted calculator tests pass; import tu cong cong total va khong tax. |
| FIN-01 | Reversal + repost phai de lai audit trail du va net stock dung. | Invoice net co the dung, ledger trail fail do key tai su dung. |

## Tenant

| ID | Invariant | Ket qua |
|---|---|---|
| TEN-01 | Moi reference owned phai cung company. | Nhieu explicit guard pass; queue phu thuoc ky luat tung job. |
| TEN-02 | Tenant scope phai nhat quan HTTP/queue/command. | **Design gap**: CompanyScope chi chay khi auth co user. |

