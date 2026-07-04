# Test Gap Matrix

Legend: `P` pass/covered, `G` gap, `L` limited/static only.

| Flow | Happy | Partial/failure | Retry | Concurrent | Reverse/delete | Tenant | Ket luan |
|---|---:|---:|---:|---:|---:|---:|---|
| Generic stock inbound/outbound | P | L | G | G | G | P | Thieu test idempotency bao physical mutation |
| Sales DO lifecycle | P | P | P sequential | G | P sequential | P | Can two-connection test |
| GRN receive | P | P create | L | G | G | P | Khong test edit/delete received |
| Purchase canonical source | P config | L | G | G | G | P | Legacy projection parity chua cover |
| Production release/reserve | P | P rollback basic | P sequential | G | P cancel pre-post | P | Thieu exact-stock own reservation |
| Production RM post | P | P | P sequential | G | No reversal | P | Core race gap |
| Production FG post | P | P multi-output | P sequential | G | No reversal | P | Core race gap |
| Invoice stock sync | P limited | L | G multi-edit | G | P basic | P | Chua assert complete ledger history |
| Credit Note return | P | L | P sequential | G | P | P | Generic P0 van anh huong |
| Vendor Credit return | P | L | P sequential | G | P | P | Count-then-act race gap |
| Estimate totals/review | P | P validation | N/A | G review race | L | P | Can lock state transition |
| Estimate import | P single run | L | G | G | N/A | P explicit | Finding cu chua fix |
| Estimate -> SO | P | P gate | P sequential | G | N/A | P | Thieu unique/race test |
| Pricing | P targeted | P validation | L | G | N/A | P | Khong thay confirmed deep bug |
| Queue/import tenant | P vai job | P inventory rollback | P SO/history | G | N/A | L per-job | Can tenant context contract |

## Test files da chay trong audit

- `tests/Unit/StockMovementServiceTest.php`
- `tests/Feature/ProductionPostingServiceTest.php`
- `tests/Feature/PurchaseInboundStockFlowTest.php`
- `tests/Feature/SalesShipmentOptionBTest.php`
- `tests/Feature/GrnServicePersistenceTest.php`
- `tests/Feature/GrnServiceLifecycleTest.php`
- `tests/Feature/EstimateConvertToSalesOrderTest.php`
- `tests/Unit/SalesDoInvoiceGuardServiceTest.php`
- `tests/Feature/CreditNoteSalesReturnStockTest.php`
- `tests/Feature/VendorCreditPurchaseReturnStockTest.php`

Ket qua: **47 passed, 1 skipped, 139 assertions**.

## Test bat buoc truoc khi dong P0/P1

1. Stock retry cung key khong doi batch/cache/ledger lan hai.
2. Concurrent stock command chi commit mot lan.
3. Received GRN edit/delete/status downgrade bi block hoac correction atomic.
4. Production exact stock = own reservation van post duoc.
5. Concurrent Production release/RM/FG chi post mot lan.
6. Invoice edit nhieu lan tao ledger version day du va net stock dung.
7. Estimate import retry va concurrent chunk.
8. Estimate conversion concurrent.
9. Reservation release-vs-consume race.
10. Moi test tren chay cho hai company de chung minh isolation.

