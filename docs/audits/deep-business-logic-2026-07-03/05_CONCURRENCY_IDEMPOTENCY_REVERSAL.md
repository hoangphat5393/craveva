# Concurrency, Idempotency And Reversal

## Matrix

| Flow | Sequential retry | Concurrent retry | Reversal | Ket luan |
|---|---|---|---|---|
| Generic inbound/outbound | Unsafe khi goi lai cung key | Unsafe, key khong unique | Caller-dependent | P0 core issue |
| Sales DO ship | Header flag + row lock bao ve sequential | Kha tot o aggregate owner | Reverse/cancel co | Can two-connection test |
| GRN receive | Applied flag chan sequential post | Observer khong lock header | Khong co edit/delete/downgrade reversal | P0 lifecycle gap |
| Production RM | Timestamp chan sequential | Khong lock batch; unsafe | Khong reverse sau In progress | P1 |
| Production FG | Timestamp chan sequential | Khong lock output; unsafe | Khong reverse generic | P1 |
| Production release | Status chan sequential | Khong lock order; unsafe | Cancel chi truoc posting | P1 |
| Invoice sync | Reverse + repost net intent | Khong lock invoice/postings | Co reversal rows | Key reuse lam audit trail sai |
| Credit Note | Movement pre-check | Check khong lock/unique | Delete reversal co | Bi anh huong boi P0 core |
| Vendor Credit | Versioned key counts | Count-then-act race | Co reversal | Can unique command key/lock |
| Estimate import | Khong idempotent | Khong source-row unique | Khong co | P1 |
| Estimate -> SO | Sequential pre-check | Race | Khong tu dong merge | P1 |

## Lock ordering hien tai

- Stock outbound lock batch rows.
- Sales DO lock header roi goi stock/reservation.
- Reservation service lock batch; direct release/consume khong lock reservation row.
- Production post khong lock aggregate owner truoc khi goi stock.
- GRN observer khong lock GRN header khi check/set applied flag.

Can chot thu tu lock thong nhat de tranh deadlock:

1. Aggregate owner (GRN/Sales DO/Production batch/output/invoice).
2. Reservation rows theo ID.
3. Warehouse batch rows theo ID tang dan.
4. Idempotency command row/movement unique claim.
5. Projection/cache rows.

## Fault injection can bo sung

1. Throw sau batch quantity save nhung truoc movement insert: toan transaction phai rollback.
2. Throw sau movement line 1 trong GRN/Production multi-line: khong line nao duoc commit.
3. Throw sau reversal nhung truoc repost invoice: posting cu va stock phai giu nguyen.
4. Throw sau Production snapshot nhung truoc reservation/status: order van Draft, zero snapshot/reservation moi.
5. Kill/retry queue sau Estimate row commit: row thu hai phai no-op/upsert theo policy.

## Reversal contract de xuat

- Posted documents khong duoc hard delete truc tiep.
- Dung explicit `void/correct` command tao movement nguoc, giu source reference va reason/user/time.
- Correction phai version hoa idempotency key theo document revision.
- Khong xoa movement cu; projection co the rebuild tu movement + opening balance.
- Reversal khong duoc tu dong chon batch khac neu can hoan lai batch identity goc.

## Evidence tu test hien tai

Targeted suite pass 47 tests/139 assertions va xac nhan sequential happy/retry o Sales DO, Production, returns va GRN persistence. Khong co test hai DB connection/barrier cho cac race neu tren. Do do khong duoc nang muc ket luan concurrency thanh pass.

