# Test And Operations Audit

### TEST-P0-001: PHPUnit không khóa database test

- Severity: P0 Critical
- Status: Resolved 2026-07-02
- Evidence:
  - `phpunit.xml:11-20`
- Current behavior: `APP_ENV=testing` được set nhưng SQLite connection/database bị comment; test không override connection sẽ dùng `.env` database.
- Expected behavior: Test chỉ chạy trên disposable DB và fail closed nếu target không có tên/host được phép.
- Impact: Test có migration/truncate/write có thể phá development hoặc staging data.
- Reproduction/verification: Config inspection; audit không chạy destructive test trên current DB.
- Root cause: Test isolation phụ thuộc từng test tự cấu hình connection.
- Recommended fix: `.env.testing` + bootstrap database guard + dedicated test DB lifecycle.
- Required tests: Trỏ test vào `craveva_staging` phải fail trước discovery; disposable target phải pass.
- Dependencies: Không; đây là remediation đầu tiên nên làm.
- Confidence: High

Remediation evidence: `phpunit.xml` dùng SQLite `:memory:` và `tests/TestCase.php` fail closed với database name không chứa test/testing/audit.

### TEST-P1-002: Production posting suite đỏ hoàn toàn

- Severity: P1 High
- Status: Resolved 2026-07-02
- Evidence:
  - `tests/Feature/ProductionPostingServiceTest.php:153-165`
- Current behavior: 20/20 case fail trước assertion vì require migration file đã bị xóa.
- Expected behavior: Suite chạy trên active schema và kiểm tra RM/FG/idempotency/variance/reservation.
- Impact: Không có regression gate đáng tin cho Production stock posting.
- Reproduction/verification: `php artisan test tests/Feature/ProductionPostingServiceTest.php` -> exit 1, 20 failed, 0 assertions.
- Root cause: Test fixture không được migrate cùng consolidation.
- Recommended fix: Repair schema setup sau khi DB-P0-002 được chốt.
- Required tests: Toàn bộ 20 case có assertion và pass trên clean environment.
- Dependencies: TEST-P0-001, DB-P0-002.
- Confidence: High

Remediation evidence: test dùng current schema fixture, không require migration đã xóa; 20 tests/72 assertions pass.

### TEST-P1-003: DB-dependent Production tests có thể xanh nhờ skip

- Severity: P1 High
- Status: Confirmed
- Evidence:
  - `tests/Support/ProductionTenantFlowFixtures.php:24-63`
  - `tests/Unit/ProductionOrderBomSnapshotItemUnitRelationTest.php:18-30`
- Current behavior: Fixture skip khi thiếu table/company/FG/RM. Fresh seed không có FG/RM nên case DB bị skip.
- Expected behavior: Critical contract test tự tạo fixture tối thiểu hoặc fail khi precondition sai.
- Impact: CI có thể xanh mà không chạy flow cần bảo vệ.
- Reproduction/verification: Trên fresh audit DB: 1 pass, 1 skipped; case write snapshot không chạy.
- Root cause: Test dựa vào dữ liệu môi trường thay vì deterministic fixture.
- Recommended fix: Tự tạo tenant/user/FG/RM/warehouse trong transaction.
- Required tests: Fresh empty seeded DB phải chạy, không skip critical path.
- Dependencies: TEST-P0-001.
- Confidence: High

### TEST-P1-004: Thiếu fresh-install contract test end-to-end

- Severity: P1 High
- Status: Confirmed
- Evidence:
  - `database/migrations/README.md:9-19`
  - `docs/MIGRATION_CONSOLIDATION_PLAN.md:119-138`
- Current behavior: Không có CI test chạy migrate mặc định + seed + superadmin + login + core flows + schema fingerprint.
- Expected behavior: Mỗi thay đổi migration/model phải qua fresh MySQL contract.
- Impact: DB-P0-001 và DB-P0-002 không bị phát hiện trước khi source được xem là hoàn tất.
- Reproduction/verification: Audit manual đã phát hiện cả hai failure bằng disposable DB.
- Root cause: Verification consolidation chỉ kiểm tra generated output tại thời điểm đó, không trở thành repeatable CI gate.
- Recommended fix: Containerized MySQL fresh-install job và artifact schema diff.
- Required tests: Contract sáu bước được liệt kê trong remediation plan.
- Dependencies: TEST-P0-001, DB-P0-001, DB-P0-002.
- Confidence: High

### OPS-P1-001: Scheduled worker và Supervisor có thể chạy đồng thời

- Severity: P1 High
- Status: Resolved in local source 2026-07-02
- Evidence:
  - `app/Console/Kernel.php:203-220`
  - `docs/SERVER_RUNBOOK.md:92-103`
- Current behavior: Scheduler luôn tạo queue workers mỗi phút; runbook triển khai dùng Supervisor và cảnh báo không chạy hai nguồn.
- Expected behavior: Một worker ownership strategy cho mỗi queue/environment.
- Impact: Dư process/load, retry/observability khó kiểm soát.
- Reproduction/verification: `php artisan schedule:list` hiển thị database default worker và Redis import worker mỗi phút. Không SSH server trong audit này.
- Root cause: Queue fallback qua scheduler không có env feature flag tách khỏi daemon deployment.
- Recommended fix: Production dùng Supervisor; scheduled workers chỉ bật explicit cho môi trường không có daemon.
- Required tests: Preflight process/config check; queue smoke; failed-job retry monitoring.
- Dependencies: Deployment owner xác nhận strategy.
- Confidence: High về config, Medium về runtime duplication.

Remediation evidence: schedule worker được gate bằng `queue.run_workers_in_scheduler`; default `schedule:list` không còn `queue:work`.

### OPS-P2-002: Composer metadata có warning

- Severity: P2 Medium
- Status: Confirmed
- Evidence:
  - `composer.json`
- Current behavior: `composer validate` exit 0 nhưng cảnh báo exact constraints và `psr/http-factory-implementation:*` unbound.
- Expected behavior: Dependency constraints có chủ đích và validate sạch hoặc warning có decision record.
- Impact: Nâng cấp/resolution khó dự đoán; chưa phải runtime failure.
- Reproduction/verification: Chạy `composer validate`.
- Root cause: Legacy pinning và virtual package constraint.
- Recommended fix: Xử lý từng package theo batch nhỏ sau Phase 0, không chạy mass update.
- Required tests: `composer update --dry-run -W`, targeted package regression và full suite isolated.
- Dependencies: Test suite phải ổn định trước.
- Confidence: High

## Check results

| Check | Result |
|---|---|
| `composer validate` | Exit 0, warnings |
| 506 migration `php -l` | Exit 0, 506 pass after remediation |
| `php artisan route:list --json` | Exit 0, 3.369 routes |
| Duplicate route key/name | 0 / 0 |
| `php artisan schedule:list` | Exit 0, default config không còn scheduled `queue:work` |
| ProductionPostingServiceTest | Exit 0, 20 passed, 72 assertions after remediation |
| Inventory/SO import remediation tests | Exit 0, 3 passed, 10 assertions |
| Snapshot relation test trên audit DB | Exit 0, 1 pass, 1 skip |

## Remediation test status

- Unit suite: **160 passed, 1.123 assertions, 7 skipped**. Không còn failure.
- Targeted fresh/Production/import suite: **25 passed, 88 assertions**.
- Feature suite: chưa sạch trên SQLite; nhiều integration tests vẫn giả định có schema/data tenant sẵn thay vì tự tạo fixture.
- Một lần thử clone local DB sang `craveva_codex_test_20260702` bị timeout/import SQL dở; database disposable này không được dùng làm test target.
