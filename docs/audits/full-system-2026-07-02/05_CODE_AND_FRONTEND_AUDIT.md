# Code And Frontend Audit

### CODE-P1-001: Model/schema drift chưa được kiểm soát tự động

- Severity: P1 High
- Status: Confirmed; non-Production mismatches need classification
- Evidence:
  - `Modules/Production/Entities/ProductionOrderBomSnapshotItem.php:19-39`
  - `database/migrations/2000_01_01_000269_create_production_order_bom_snapshot_items_baseline.php:12-29`
- Current behavior: Reflection audit 506 model/entity phát hiện 15 model table không có và 15 fillable field không có trong fresh schema. Ba Production field đã được runtime-confirmed là defect; phần còn lại có thể gồm legacy/optional/accessor cases.
- Expected behavior: Active model table/column contract khớp fresh schema; exception có allowlist và owner.
- Impact: Runtime write/read failure có thể chỉ xuất hiện ở chức năng ít dùng.
- Reproduction/verification: Reflection model metadata đối chiếu `information_schema` trên fresh audit DB; Eloquent write xác nhận Production failure.
- Root cause: Consolidation và model evolution không có schema-contract test.
- Recommended fix: Thêm model-schema audit CI, phân loại từng mismatch trước khi sửa/xóa.
- Required tests: Zero unexplained mismatch; active model CRUD smoke tests.
- Dependencies: DB-P1-004.
- Confidence: High cho Production, Medium cho danh sách còn lại.

### CODE-P1-002: Test code tham chiếu migration đã xóa

- Severity: P1 High
- Status: Confirmed
- Evidence:
  - `tests/Feature/ProductionPostingServiceTest.php:153-165`
  - `Modules/Production/Database/Migrations/.gitkeep`
- Current behavior: Test require năm migration module không tồn tại sau consolidation.
- Expected behavior: Test dựng schema từ active fresh-install contract hoặc fixture schema được version hóa cùng contract.
- Impact: Toàn bộ Production posting suite fail trước assertion.
- Reproduction/verification: `php artisan test tests/Feature/ProductionPostingServiceTest.php` -> 20 failed, 0 assertions.
- Root cause: Migration cleanup không cập nhật consumers trong tests.
- Recommended fix: Chạy test trên active MySQL fresh schema hoặc chuyển setup sang reusable current-schema fixture.
- Required tests: 20 cases chạy assertions và pass; CI kiểm tra mọi `require` target tồn tại.
- Dependencies: DB-P0-002 và TEST-P0-001.
- Confidence: High

## Frontend/AJAX status

- Không còn lời gọi `$.easyAjax` thực tế trong `resources`/`Modules`.
- Chuỗi duy nhất còn lại là comment tại `resources/js/http/apiClient.js:2`.
- Axios bootstrap tại `resources/js/bootstrap.js:9-11`; shared helper tại `resources/js/http/apiClient.js:19`.
- Kết luận static migration: complete. Browser smoke/UAT các URL trong `docs/axios-migration` vẫn là gate riêng.

## Maintainability observations

- 2.186 Blade và 3.369 route tạo blast radius lớn cho shared helper/layout changes.
- Tenancy phân tán giữa global scope, `company()` helper và query explicit.
- Test tự dựng schema dễ drift khỏi baseline thật.
- Không đề xuất refactor diện rộng trước khi Phase 0 database safety hoàn tất.

