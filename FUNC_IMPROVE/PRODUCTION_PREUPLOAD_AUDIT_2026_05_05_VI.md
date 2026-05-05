# Production Module Pre-upload Audit (2026-05-05)

## Pham vi audit

- Module: `Modules/Production`
- Muc tieu: bat loi truoc khi upload staging, uu tien loi nghiep vu va loi hien thi.

## Loi da fix

### 1) Traceability hien thi raw `reference_type` namespace

- Van de: man hinh `Traceability (RM batches used)` hien thi gia tri raw nhu `Modules\Production\Entities\ProductionBatch`.
- Tac dong: nguoi dung cuoi kho hieu, UI xau.
- Fix:
    - File: `Modules/Production/Resources/views/batches/trace.blade.php`
    - Chuyen sang render nhan than thien bang `class_basename + Str::headline`.
    - Ket qua: hien thi dang `Production Batch` thay vi namespace dai.

### 2) Order bi set `completed` qua som khi con batch chua hoan tat

- Van de: `postFinishedGoodsReceipt()` set `ProductionOrder` thanh `completed` ngay sau khi post FG cho 1 batch.
- Tac dong: neu 1 order co nhieu batch, trang thai order sai nghiep vu (hoan tat som).
- Fix:
    - File: `Modules/Production/Services/ProductionPostingService.php`
    - Them check batch pending:
        - Neu con batch chua co `posted_receipt_at`/`completed_at` => giu order o `in_progress`.
        - Chi set `completed` khi tat ca batch da hoan tat.

## Test da chay

- Lenh: `php artisan test --compact tests/Feature/ProductionPostingServiceTest.php`
- Ket qua: `6 passed (17 assertions)`
- Bo sung regression test:
    - `keeps order in progress when other batches are not completed yet`
    - File: `tests/Feature/ProductionPostingServiceTest.php`

## Format va quality gate

- Da chay: `vendor/bin/pint --dirty --format agent`
- Lint cac file sua: khong co loi.

## Khuyen nghi truoc khi upload

- Chay lai nhanh test lien quan production:
    - `php artisan test --compact tests/Feature/ProductionPostingServiceTest.php`
- Neu can an tam toan he thong, chay full:
    - `php artisan test --compact`
