# Evidence Commands

Các command dưới đây đã được chạy từ `E:\web\craveva-staging`. Secret/password không được ghi vào report.

## Environment và inventory

```powershell
php -v
composer --version
php artisan --version
composer validate
php artisan route:list --json
php artisan schedule:list
```

| Command | Exit code | Summary |
|---|---:|---|
| `php -v` | 0 | PHP 8.3.30 |
| `composer --version` | 0 | Composer 2.9.5 |
| `php artisan --version` | 0 | Laravel 11.54.0 |
| `composer validate` | 0 | Valid, có constraint warnings |
| `php artisan route:list --json` | 0 | 3.369 routes |
| `php artisan schedule:list` | 0 | Schedule load thành công; có 2 worker commands mỗi phút |

## Migration syntax

```powershell
$bad = @()
Get-ChildItem database/migrations -File -Filter *.php | ForEach-Object {
    $out = & php -l $_.FullName 2>&1
    if ($LASTEXITCODE -ne 0) { $bad += $_.Name }
}
```

Exit code: 0. Result: 505 files, 0 syntax failures.

## Default fresh-install reproduction

Disposable DB 1 không tìm thấy `mysql` CLI khi Laravel cố load schema dump. Sau khi thêm MySQL client vào PATH, disposable DB 2 chạy:

```powershell
$env:DB_DATABASE = 'craveva_codex02_audit_20260702'
php artisan migrate --force
```

Exit code: 1. Result: schema dump load xong, sau đó fail tại `2000_01_01_000001_create_accept_estimates_baseline` với `Table 'accept_estimates' already exists`.

## Baseline-only reproduction

```powershell
$env:DB_DATABASE = 'craveva_codex03_audit_20260702'
php artisan migrate --force --schema-path=database/schema/__audit_no_schema__.dump
php database/scripts/import_fresh_seed_data.php --input=database/seeders/data/full_20260701
php artisan fresh-install:create-superadmin audit-superadmin@example.invalid
```

Password được tạo ngẫu nhiên trong process và không log. Result:

- 505 migrations pass trong khoảng 18 giây.
- 503 application tables + migrations table.
- 121 seed files, 3.458 rows, checksum pass.
- Superadmin/auth hash/Fortify callback pass.

Exit codes: migrate 0, importer 0, create-superadmin 0, authentication probe 0.

## Current database read-only status

```powershell
php artisan migrate:status
```

Exit code: 0. Result: 505 consolidated migrations pending, 0 baseline migration ran. Không chạy `migrate` trên database hiện tại.

## Production schema contract reproduction

Trên disposable DB 3, kiểm tra `information_schema.columns` và thử Eloquent create với payload từ `ProductionPostingService`.

Schema inspection exit code 0; intentional write probe exit code 1. Result: table thiếu `unit_id`, `yield_factor`, `quantity_per_fg_unit_base_shadow`; write lỗi `Unknown column 'unit_id' in 'field list'`.

## Tests

```powershell
php artisan test tests/Feature/ProductionPostingServiceTest.php
$env:DB_DATABASE = 'craveva_codex03_audit_20260702'
php artisan test tests/Unit/ProductionOrderBomSnapshotItemUnitRelationTest.php
```

Results:

- Production posting: 20 failed, 0 assertions; missing legacy module migration file.
- Snapshot relation: 1 passed, 1 skipped; fresh seed thiếu FG/RM fixture.

Exit codes: Production posting 1; snapshot relation 0.

## Static scans

```powershell
rg -n '\$\.easyAjax' resources Modules --glob '*.blade.php' --glob '*.js'
rg -n 'axios|window\.axios' resources/js resources/views Modules --glob '*.js' --glob '*.blade.php'
rg -n 'DB::transaction|idempotency_key|outbound_stock_applied|inbound_stock_applied' app Modules --glob '*.php'
rg -n 'DB::select|whereRaw|selectRaw|DB::raw' app Modules --glob '*.php'
```

## Retained disposable databases

- `craveva_codex01_audit_20260702`: first failed attempt.
- `craveva_codex02_audit_20260702`: dump/baseline conflict evidence.
- `craveva_codex03_audit_20260702`: baseline-only migrated/seeded evidence.
- `craveva_codex04_fix_20260702`: 506 migrations replayed without schema dump.
- `craveva_codex05_default_fix_20260702`: failed proof before canonical `.dump` was regenerated.
- `craveva_codex06_default_fix_20260702`: default migrate, seed and superadmin verification passed after remediation.

Không database nào bị drop trong audit này.

## Remediation verification 2026-07-02

```powershell
php artisan migrate --force
php database/scripts/import_fresh_seed_data.php --input=database/seeders/data/full_20260701
php artisan test tests/Feature/ProductionPostingServiceTest.php
php artisan test tests/Feature/InventoryImportTransactionTest.php tests/Feature/ImportSalesOrderChunkJobTest.php
php artisan schedule:list
```

Results:

- Default fresh migrate: exit 0; schema dump loaded; 506 migration records; nothing pending.
- Seed importer: exit 0; 121 files and 3.458 rows verified.
- Fresh superadmin hash/link verification: exit 0.
- Production posting: exit 0; 20 tests, 72 assertions.
- Inventory rollback and tenant-scoped SO import: exit 0; 3 tests, 10 assertions.
- Schedule list: exit 0; no `queue:work` entry under default config.

Additional suite evidence:

- `php artisan test tests/Unit`: exit 0; 160 passed, 7 skipped, 1.123 assertions.
- `php artisan test tests/Feature --stop-on-failure`: exit 1; integration tests still depend on pre-existing users/companies/module data when run on SQLite memory.
- Local DB clone attempt to `craveva_codex_test_20260702`: timed out and produced a partial disposable database; PHPUnit configuration was not pointed to it.
