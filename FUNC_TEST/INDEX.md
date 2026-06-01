# FUNC_TEST Index

Navigation index for test strategy, test cases, and UAT execution evidence.

## Test Documents

- `FUNC_TEST/01_BIOMIXING_TEST_MATRIX_VI.md` — ma trận UAT Bio-TC-001…021 + lệnh Pest cụm Biomixing

## Snapshot test suite (archive 2026-04-08)

Full suite từng báo **15 failed** / 120 passed (DeliveryOrderObserver, ImportBatchQueueConfig, CompanyRegisterGate, ExampleTest, SalesShipmentOptionB…). File chi tiết đã gộp pass 4 — tra `git log -- FUNC_BUG/FULL_TEST_SUITE_FAILURES_SNAPSHOT.md` nếu cần. Sau khi sửa: `php artisan test --compact`.

## Usage

- Thêm file với prefix số: `02_...`, `03_...`
- Cập nhật link trong `FUNC_INDEX.md`

## Status Convention

- `draft` · `ready` · `executing` · `passed` · `blocked`
