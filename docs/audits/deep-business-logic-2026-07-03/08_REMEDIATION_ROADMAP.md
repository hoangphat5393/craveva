# Remediation Roadmap

Khong sua du lieu that truc tiep. Moi migration unique/backfill phai dry-run va rehearsal tren clone/disposable DB.

## Completed in source 2026-07-04

- FLOW-P0-001, FLOW-P0-002, FLOW-P1-004, FLOW-P1-007, FLOW-P1-008 va FLOW-P2-011.
- FLOW-P1-003 da them aggregate row locks cho RM/FG posting; con thieu test concurrency bang hai DB connection.
- Migration `2026_07_04_000001_create_stock_movement_commands_table` pass tren `craveva_codex06_default_fix_20260702` batch 2.
- Chua backfill DATA-P1-015 va chua sua cac finding Invoice/Estimate/batch identity/legacy projection.

| ID | Severity | Invariant | Fix | Test | Dependency | Estimate |
|---|---|---|---|---|---|---|
| FLOW-P0-001 | P0 | WH-01/WH-05 | Dua idempotency claim len truoc mutation; unique command key | Sequential + concurrent retry | Duplicate-key audit | 2-4 days |
| FLOW-P0-002 | P0 | GRN-02 | Block mutate/delete received truoc; thiet ke void/correction neu can | Edit/delete/downgrade + rollback | BA immutable policy | 1-3 days block; 3-6 days correction |
| DATA-P1-015 | P1 | PROD-03 | Dry-run classify/backfill missing RM ledger, khong mutate physical stock | Idempotent backfill + reconciliation | P0 idempotency fix | 2-4 days |
| FLOW-P1-003 | P1 | PROD-03 | Lock batch/output/order, recheck posted state trong transaction | Two-connection RM/FG | FLOW-P0-001 | 2-4 days |
| FLOW-P1-004 | P1 | PROD-02 | Allocate own reservations first | Exact/excess/cross-owner stock | BA confirm | 1-3 days |
| FLOW-P1-005 | P1 | FIN-01 | Version invoice posting cycle/key, lock postings | 10->7->9 + retry/race | Invoice mode decision | 2-4 days |
| FLOW-P1-006 | P1 | EST-01 | Source batch/row hash unique, upsert/reject | Same file twice + concurrent chunk | BA retry semantics | 2-4 days |
| FLOW-P1-007 | P1 | EST-02 | Lock Estimate + unique 1:1 key | Concurrent conversion | BA cardinality + dedupe audit | 1-2 days |
| FLOW-P1-008 | P1 | PROD-01 | Lock order/recheck Draft; atomic initial batch | Concurrent release/failure injection | FLOW-P1-003 | 1-3 days |
| FLOW-P1-009 | P1 | WH-06 | Normalize nullable batch identity and enforce unique | Null/null + concurrent create | Duplicate batch audit | 2-4 days |
| FLOW-P1-010 | P1 | Canonical inventory | Gate/remove legacy mutation; projection rebuild from warehouse | PO/GRN matrix + rollback | Ledger authority decision | 2-5 days |
| FLOW-P2-011 | P2 | Reservation consistency | Lock reservation then batch, recheck active | release-vs-consume race | Lock order standard | 1-2 days |
| FLOW-P2-012 | P2 | TEN-02 | Tenant execution context for queue/command | HTTP/queue parity | CTO architecture | 3-7 days |
| FLOW-P2-013 | P2 | Unique document refs | Per-company sequence allocator | Concurrent create | Number semantics/dedupe | 2-4 days |
| PROD-P2-014 | P2 | Yield policy | Chot shadow vs operational; expose/validate UI neu active | Yield/waste UAT | BA Production policy | 2-5 days |

## Trinh tu trien khai

### Phase 0 - Stop deterministic corruption

1. FLOW-P0-001 generic stock idempotency.
2. FLOW-P0-002 tam thoi immutable GRN received.
3. Chay full stock/GRN/Production targeted tests va reconciliation.

**Exit:** Retry key khong doi physical stock; received GRN khong the mutate/delete silent.

### Phase 1 - Posting ownership va data repair

1. Lock Production aggregate va fix own reservation.
2. Version invoice sync.
3. Fix Estimate import/conversion.
4. Normalize batch identity.
5. Dry-run va repair DATA-P1-015 sau khi code invariant da dung.

**Exit:** Concurrent tests pass; zero unexplained reconciliation anomalies.

### Phase 2 - Simplify boundaries

1. Chon mot inventory ledger authoritative; legacy tables thanh projection hoac retire.
2. Tenant context cho background execution.
3. Sequence allocator cho document numbers.
4. Chot yield/waste operational contract.

## Rollback rules

- Khong them unique index truoc khi audit/dedupe du lieu.
- Khong backfill movement bang cach goi stock posting service vi se doi physical stock.
- Correction migration phai co dry-run count, checksum va transaction/chunk resume key.
- Neu deploy P0 gap fix that bai, rollback code; khong xoa movement da tao.
