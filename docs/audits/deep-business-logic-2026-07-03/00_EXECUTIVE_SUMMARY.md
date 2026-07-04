# Deep Business Logic Audit - Executive Summary

**Ngay audit:** 2026-07-03  
**Pham vi:** source local va truy van doi soat chi doc; khong dung staging/hub; khong sua logic nghiep vu trong giai doan audit.

## Ket luan ngan

He thong **khong hong toan bo logic tang sau**, nhung co cac lo hong nhat quan o bien stock posting, retry, concurrent request va reversal. Happy path duoc test kha tot, nhung mot so thao tac binh thuong nhu sua/xoa GRN da received co the lam chung tu va ton kho lech nhau. Vi vay:

- Tiep tuc phat trien tren source local: **GO co dieu kien**.
- Fresh install ky thuat: **GO**, dua tren audit fresh-install 2026-07-02 da pass.
- Go-live cac flow Warehouse/Production/Purchase: **NO-GO** cho den khi deploy migration P0 tren moi truong dich, chay test concurrency hai connection va phan loai/backfill DATA-P1-015.
- Khong duoc dung ket qua 47 test pass de ket luan retry/concurrency/reversal da an toan; cac scenario nay dang thieu test.

## Remediation update 2026-07-04

- FLOW-P0-001: **Resolved in source** - stock command key duoc claim trong transaction truoc physical mutation; migration unique command key da pass tren DB disposable.
- FLOW-P0-002: **Resolved by immutable policy** - GRN received/applied bi chan update, delete va status downgrade o service/controller/observer; UI an/disable action.
- FLOW-P1-003 va FLOW-P1-008: **Implemented, concurrency runtime test pending** - release/cancel/post RM/post FG lock aggregate owner va recheck state trong transaction.
- FLOW-P1-004: **Resolved** - Production duoc consume reservation cua chinh order; exact-stock regression test pass.
- FLOW-P2-011: **Resolved in source** - release/consume lock fresh reservation row truoc khi thay doi batch reserved quantity.
- Chua xu ly DATA-P1-015, Invoice resync, Estimate import/conversion, nullable batch identity va legacy PO projection.

## 10 rui ro lon nhat

| Hang | ID | Severity | Rui ro |
|---|---|---|---|
| 1 | FLOW-P0-001 | P0 | `StockMovementService` kiem tra idempotency sau khi da tang/giam batch; DB khong co unique key cho idempotency. |
| 2 | FLOW-P0-002 | P0 | GRN received van co the sua, downgrade status hoac xoa ma khong reverse stock. |
| 3 | FLOW-P1-003 | P1 | Production post RM/FG khong lock batch/output header; hai request dong thoi co the post stock hai lan. |
| 4 | FLOW-P1-004 | P1 | Production khong cho dung phan ton da reserve boi chinh lenh dang post khi ton vua du. |
| 5 | FLOW-P1-005 | P1 | Invoice resync reverse va post lai nhung tai su dung outbound idempotency key, lam ledger thieu movement moi. |
| 6 | FLOW-P1-006 | P1 | Retry import Estimate them lai line va cong lai total. |
| 7 | FLOW-P1-007 | P1 | Hai request convert cung Estimate co the tao hai Sales Order; DB chi co index thuong tren `orders.estimate_id`. |
| 8 | FLOW-P1-008 | P1 | Production release kiem tra status ngoai transaction va khong lock Production Order. |
| 9 | FLOW-P1-009 | P1 | Unique batch identity chua bao ve dong batch/expiry NULL trong MySQL; create race co the tao duplicate batch. |
| 10 | DATA-P1-015 | P1 | DB local co 8 consumption line thuoc 5 Production batch da post nhung khong co outbound movement tuong ung. |

## Diem manh da xac nhan

- Config local chon mot nguon purchase inbound: PO delivered `false`, GRN received `true`.
- Sales outbound mode la `shipment`; Sales DO lock header va dung `outbound_stock_applied` trong service.
- Stock outbound lock cac batch row va chan ton am theo config.
- Production co BOM snapshot, reservation, UOM conversion, FG variance approval va tenant guard o controller.
- 10 nhom targeted test dat **47 passed, 1 skipped, 139 assertions**.
- Doi soat local khong thay duplicate movement idempotency key, duplicate batch identity, GRN quantity mismatch, Sales DO applied thieu movement, hay active reservation mismatch tai thoi diem audit.

## Rui ro kien truc

`CompanyScope` chi ap dung khi co authenticated user. HTTP flow co nhieu explicit company guard, nhung queue/command phai tu nho them `company_id`; day la nguy co lap lai loi cross-tenant.

Production yield/UOM moi o che do shadow va dang tat. Waste duoc luu/tinh nhung UI mac dinh an. Khong nen mo ta he thong la da van hanh day du yield-aware cho den khi BA phe duyet policy va UAT.

## Quyet dinh BA/CTO can chot

1. GRN da received co immutable khong, hay cho phep correction bang reversal + repost?
2. Retry Estimate import la skip, reject hay upsert theo source line key?
3. Mot Estimate chi duoc tao toi da mot Sales Order hay cho phep version/revision?
4. Production consumption co duoc dung reservation cua chinh Production Order do khong? Khuyen nghi: co.
5. Invoice stock mode co con duoc support that hay shipment mode la contract duy nhat?
6. Yield factor va waste la shadow/reporting hay phai tac dong planned consumption chinh thuc?
