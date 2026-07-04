# Flow Findings

## FLOW-P0-001: Idempotency stock duoc kiem tra sau physical mutation

- Domain: Warehouse core, anh huong moi caller.
- Severity: P0 Critical.
- Status: Resolved in source 2026-07-04; command-key migration and sequential retry tests pass.
- Business invariant violated: WH-01, WH-05; retry khong duoc doi net stock lan hai.
- Entry point: `StockMovementService::recordInbound`, `recordOutbound`.
- Evidence: `Modules/Warehouse/Services/StockMovementService.php:55-76`, `:89-136`, `:352-404`; `database/schema/mysql-schema.dump:10400-10408`.
- State before: Idempotency key da co movement tu lan truoc.
- Action: Goi lai inbound/outbound cung key.
- Current result: Batch quantity van tang/giam; `createMovement()` moi phat hien key ton tai va return.
- Expected result: No-op truoc bat ky stock mutation nao.
- Impact: Physical stock va ledger lech; downstream reconciliation, Production, returns va invoice co the sai.
- Reproduction: Goi `recordInbound` hai lan voi cung payload/key; lan hai khong them movement nhung quantity tang.
- Transaction/locking analysis: Transaction chi atomic cho tung call; khong co unique constraint va check khong lock key.
- Tenant impact: Query key co company, nhung khong giai quyet race trong cung tenant.
- Reversal impact: Reversal retry co the thay doi physical stock lan hai.
- Root cause: Idempotency dat o movement insert thay vi bao toan bo command.
- Recommended fix: Claim command key truoc mutation bang unique `(company_id, movement_type, idempotency_key)` hoac idempotency table; duplicate phai no-op toan transaction.
- Required tests: sequential retry inbound/outbound/reversal; two-connection concurrent retry; rollback after movement insert failure.
- Reconciliation query: So sanh batch/cache voi tong authoritative movements theo cutover/opening balance.
- Dependencies/BA decisions: Chot migration/backfill cho duplicate/null key.
- Confidence: High.

## FLOW-P0-002: GRN received co the mutate/xoa ma khong reversal

- Domain: Purchase inbound / Warehouse.
- Severity: P0 Critical.
- Status: Resolved 2026-07-04 by immutable-after-received policy.
- Business invariant violated: GRN-02.
- Entry point: GRN update, change-status, destroy.
- Evidence: `Modules/Purchase/Http/Controllers/DeliveryOrderController.php:99-121`, `:165-175`; `Modules/Purchase/Services/GrnService.php:11-18`, `:53-60`; `Modules/Purchase/Observers/DeliveryOrderObserver.php:54-60`.
- State before: GRN `received`, `inbound_stock_applied=true`, stock da tang.
- Action: Sua quantity/items, doi ve draft/inbound, hoac delete.
- Current result: Observer bo qua vi flag true; item co the bi delete/create lai; khong co outbound compensation.
- Expected result: Received immutable, hoac reverse movement cu va repost correction trong mot transaction.
- Impact: Chung tu khong con khop ton; delete van de lai stock.
- Reproduction: Tao received GRN qty 5, sua qty 3 hoac delete, doi chieu movement/batch.
- Transaction/locking analysis: Update transaction khong bao gom reversal; observer chay truoc `syncItems` trong update.
- Tenant impact: Controller scope theo company; corruption nam trong tenant.
- Reversal impact: Khong co reversal implementation.
- Root cause: Posted document lifecycle khong duoc thiet ke immutable/correction.
- Recommended fix: Chot policy; uu tien block edit/delete/status downgrade, sau do them explicit void/correction service neu can.
- Required tests: received edit/delete/downgrade; partial QC correction; exception rollback.
- Reconciliation query: accepted GRN item sum vs inbound movement sum and applied flag.
- Dependencies/BA decisions: Immutable hay correction workflow.
- Confidence: High.

## FLOW-P1-003: Production posting khong serialize cung batch/output

- Domain: Production/Warehouse.
- Severity: P1 High.
- Status: Aggregate row locking implemented 2026-07-04; two-connection runtime verification pending.
- Business invariant violated: PROD-03, WH-01.
- Entry point: post RM va post FG routes.
- Evidence: `ProductionPostingService.php:193-227`, `:238-323`; checks timestamp truoc transaction, khong `lockForUpdate` batch/output.
- State before: Hai request cung doc timestamp null.
- Action: Hai request post dong thoi.
- Current result: Ca hai co the mutate stock; idempotency core hien tai khong bao ve physical mutation.
- Expected result: Mot request post, request con lai no-op sau row lock.
- Impact: Double consume/double receive va state completion sai.
- Reproduction: Barrier test voi hai DB connections tren cung batch/output.
- Transaction/locking analysis: Nested stock transactions khong lock aggregate owner truoc state check.
- Tenant impact: Same-tenant race.
- Reversal impact: Production In progress khong co automatic compensation.
- Root cause: Optimistic timestamp check khong co DB serialization.
- Recommended fix: Lock fresh batch/output/order trong outer transaction, recheck state, sau do post.
- Required tests: concurrent RM, concurrent FG, one worker fail mid-loop.
- Reconciliation query: posted consumption/output flags without matching key movements.
- Dependencies/BA decisions: None.
- Confidence: High.

## FLOW-P1-004: Production khong dung duoc reservation cua chinh lenh khi ton vua du

- Domain: Production reservation/posting.
- Severity: P1 High.
- Status: Resolved 2026-07-04; exact-stock own-reservation test pass.
- Business invariant violated: PROD-02.
- Evidence: reservation tang `reserved_quantity`; posting allocator tinh `available = quantity - reserved_quantity` tai `ProductionPostingService.php:388-439`.
- State before: Stock 50, Production Order reserve 50.
- Action: Post RM 50.
- Current result: Allocator thay available 0 va bao thieu batch.
- Expected result: Lenh duoc consume 50 da reserve boi chinh no, trong khi reservation cua lenh khac van bi bao ve.
- Impact: Core production flow bi block dung luc ton vua du.
- Reproduction: Release order voi required=on_hand, sau do post batch.
- Transaction/locking analysis: Khong phai race; sai ownership cua reservation trong cong thuc available.
- Tenant impact: Same tenant.
- Reversal impact: Khong ap dung.
- Root cause: Allocator tru tat ca reservation, khong cong lai active reservation cua order hien tai.
- Recommended fix: Allocate from own reservations first hoac compute available-to-owner.
- Required tests: exact stock, excess stock, reservation cua Sales DO, hai Production Order.
- Reconciliation query: Released orders required <= own reserved nhung posting fail shortage.
- Dependencies/BA decisions: Xac nhan own reservation duoc phep consume.
- Confidence: High.

## FLOW-P1-005: Invoice resync tai su dung outbound key lam thieu ledger history

- Domain: Invoice-mode warehouse.
- Severity: P1 High.
- Status: Confirmed by static sequence.
- Business invariant violated: FIN-01, WH-01.
- Evidence: `InvoiceWarehouseStockService.php:88-154`, `:157-189`.
- Current result: Sync reverse posting cu, sau do outbound lai voi key `invoice-outbound:{invoice}:{item}` cu. Generic service co the giam stock nhung bo qua movement trung.
- Expected result: Moi correction co cap reversal/post versioned va net/audit trail khop nhau.
- Impact: Ledger khong giai thich duoc physical stock sau invoice edit.
- Recommended fix: Lock invoice/postings; version posting cycle hoac update-delta command voi unique command key.
- Required tests: Edit quantity 10->7->9; retry moi cycle; concurrent observer sync.
- Dependencies/BA decisions: Co tiep tuc support invoice stock mode khong.
- Confidence: High.

## FLOW-P1-006: Estimate import retry duplicate line va total

- Domain: Estimate import.
- Severity: P1 High.
- Status: Confirmed.
- Business invariant violated: EST-01.
- Evidence: `EstimateImportProcessor.php:136-179`, `:181-202`.
- Current result: Existing estimate bi cong amount va luon tao EstimateItem moi.
- Expected result: Retry same source row no-op/upsert/reject theo policy.
- Impact: Bao gia va margin sai.
- Recommended fix: Source batch/row hash unique theo tenant; transaction upsert.
- Required tests: same file twice, duplicate row in same file, concurrent chunk retry.
- Dependencies/BA decisions: Retry semantics.
- Confidence: High.

## FLOW-P1-007: Estimate to Sales Order 1:1 chi duoc bao ve bang pre-check

- Domain: Estimate/Sales.
- Severity: P1 High.
- Status: Resolved in source 2026-07-04 by locking Estimate and rechecking existing Order inside the transaction.
- Business invariant violated: EST-02.
- Evidence: `EstimateController.php:772-830`; `orders.estimate_id` chi co index tai schema dump `:5581`.
- Current result: Hai request co the cung khong thay order, roi cung insert.
- Expected result: Lock estimate + unique company/estimate neu contract 1:1.
- Impact: Duplicate SO va downstream shipment/invoice/production.
- Recommended fix: Unique constraint sau dedupe audit; transaction lock estimate; bat duplicate exception va redirect existing.
- Required tests: concurrent conversion va sequential retry.
- Dependencies/BA decisions: Xac nhan 1:1.
- Confidence: High.

## FLOW-P1-008: Production release co race tren status/snapshot/reservation

- Domain: Production.
- Severity: P1 High.
- Status: Resolved in source 2026-07-04 by locking/rechecking Production Order in transaction.
- Business invariant violated: PROD-01, PROD-03.
- Evidence: `ProductionPostingService.php:33-54`; status check ngoai transaction, khong lock order.
- Impact: Hai release co the cung delete/create snapshot va release/recreate reservation; controller con co the tao batch sau service transaction.
- Recommended fix: Lock Production Order, recheck Draft trong transaction; tao initial batch trong cung command hoac unique order constraint.
- Required tests: concurrent release, failure sau snapshot, failure truoc batch creation.
- Confidence: High.

## FLOW-P1-009: Batch identity unique khong bao ve NULL va create race

- Domain: Warehouse.
- Severity: P1 High.
- Status: Confirmed schema risk.
- Business invariant violated: WH-06.
- Evidence: `StockMovementService.php:311-333`; unique `(company_id, warehouse_id, product_id, batch_number, expiration_date)` tai schema dump `:11836`.
- Current result: MySQL cho phep nhieu row khi nullable batch/expiry; hai creator khong co row de lock.
- Impact: FEFO, reserve, cache total va reconciliation bi phan manh.
- Recommended fix: Non-null normalized identity/generated columns hoac batch identity key; retry unique violation.
- Required tests: concurrent no-batch inbound, null/null duplicate.
- Confidence: High.

## FLOW-P1-010: PO delivered van ghi legacy stock ngoai canonical warehouse policy

- Domain: Purchase/Warehouse migration boundary.
- Severity: P1 High.
- Status: Confirmed.
- Business invariant violated: Mot nguon inventory authoritative.
- Evidence: `PurchaseOrderObserver.php:163-187`, config check chi nam trong `recordPurchaseOrderInbound()` tai `:308-339`.
- Current result: Khi PO delivered, `PurchaseStockAdjustment` tang bat ke GRN la canonical; warehouse movement co the khong tang.
- Impact: Cac man hinh/report doc hai nguon co the hien ton khac nhau; khong co reversal khi status roi delivered.
- Recommended fix: Xac dinh legacy ledger chi la projection va sync tu warehouse, hoac gate cung canonical policy.
- Required tests: PO delivered voi GRN canonical; status rollback; report parity.
- Dependencies/BA decisions: Ledger authoritative.
- Confidence: High.

## FLOW-P2-011: Direct reservation release/consume khong lock reservation row

- Domain: Warehouse reservation.
- Severity: P2 Medium.
- Status: Resolved in source 2026-07-04; fresh reservation row is locked and status rechecked.
- Evidence: `StockReservationService.php:61-110`; status check tren object truoc transaction, chi lock batch.
- Impact: Concurrent release/consume stale object co the tru `reserved_quantity` hai lan va lam anh huong reservation khac.
- Recommended fix: Lock fresh reservation row, recheck active, sau do lock batch theo thu tu co dinh.
- Required tests: release-vs-consume race.
- Confidence: High.

## FLOW-P2-012: Tenant scope cua background execution phu thuoc tung query

- Domain: Multi-tenant/queue/command.
- Severity: P2 Medium.
- Status: Confirmed design risk.
- Evidence: `app/Scopes/CompanyScope.php:21-35`.
- Impact: Job moi thieu `company_id` co the doc/lien ket tenant khac.
- Recommended fix: Tenant execution context + fail-closed repository assertions + two-tenant job tests.
- Confidence: High.

## FLOW-P2-013: Document number generation khong serialize

- Domain: Sales/finance documents.
- Severity: P2 Medium.
- Status: Confirmed design risk; current local data co 7 duplicate `company_id + order_number` groups.
- Evidence: `EstimateController.php:808`; `Order::lastOrderNumber()` tai `app/Models/Order.php:162-165`; schema khong unique order number.
- Impact: Duplicate display/reference number, integration ambiguity.
- Recommended fix: Per-company sequence row lock hoac DB sequence allocator; chot y nghia `order_number` va `original_order_number`.
- Required tests: 20 concurrent creates per company.
- Confidence: Medium; duplicate current data co the gom ca legacy semantics.

## DATA-P1-015: Production posted flag thieu RM movement tren local data

- Domain: Production reconciliation.
- Severity: P1 High data anomaly.
- Status: Confirmed read-only query.
- Evidence: 8 consumption lines thuoc batch IDs 1, 2, 6, 7, 8; posted 2026-05-05 den 2026-05-07.
- Impact: Khong the chung minh RM stock deduction tu ledger cho cac batch nay.
- Recommended fix: Phan loai pre-cutover/post-cutover; backfill chi sau khi doi chieu physical stock va source documents tren clone/disposable DB.
- Required tests: Backfill idempotent, dry-run count, no physical stock mutation.
- Confidence: High ve missing movement; Medium ve nguyen nhan.
