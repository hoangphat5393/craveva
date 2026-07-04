# Database Audit

### DB-P0-001: Default fresh migration không chạy được

- Severity: P0 Critical
- Status: Resolved 2026-07-02
- Evidence:
  - `database/schema/mysql-schema.dump:7-23`
  - `database/schema/mysql-schema.dump:4670`
  - `database/migrations/2000_01_01_000001_create_accept_estimates_baseline.php:9-30`
- Current behavior: Laravel nạp schema dump legacy rồi baseline tạo lại `accept_estimates`, gây `SQLSTATE[42S01] Table already exists`. Máy không có `mysql` CLI lỗi sớm hơn ở bước load dump.
- Expected behavior: Lệnh fresh migration mặc định chạy được trên MySQL trống, chỉ từ một nguồn schema authoritative.
- Impact: Không thể fresh install bằng lệnh Laravel chuẩn.
- Reproduction/verification: Trên `craveva_codex02_audit_20260702`, chạy `php artisan migrate --force`; dump hoàn tất rồi migration đầu tiên fail.
- Root cause: `database/schema/mysql-schema.dump` và 505 consolidated migrations cùng active nhưng chứa hai migration history khác nhau.
- Recommended fix: Chọn baseline PHP hoặc schema dump làm nguồn duy nhất; xóa/di chuyển/regen nguồn còn lại sau khi có approval.
- Required tests: CI MySQL trống chạy đúng lệnh cài đặt tài liệu hóa, không truyền workaround `--schema-path`.
- Dependencies: Phải chốt schema authoritative ở DB-P1-004 trước khi regen.
- Confidence: High

Remediation evidence: `database/schema/mysql-schema.dump` đã được regenerate với 506 migration records; default migrate trên `craveva_codex06_default_fix_20260702` load dump và báo `Nothing to migrate`.

### DB-P0-002: Fresh Production schema thiếu ba cột runtime

- Severity: P0 Critical
- Status: Resolved 2026-07-02
- Evidence:
  - `database/migrations/2000_01_01_000269_create_production_order_bom_snapshot_items_baseline.php:12-29`
  - `Modules/Production/Entities/ProductionOrderBomSnapshotItem.php:19-28`
  - `Modules/Production/Services/ProductionPostingService.php:112-122`
  - `Modules/Production/Services/ProductionPlannedConsumptionFromSnapshotService.php:109-125`
- Current behavior: Fresh table không có `unit_id`, `yield_factor`, `quantity_per_fg_unit_base_shadow`, trong khi release Production Order ghi cả ba.
- Expected behavior: Fresh schema phải đáp ứng toàn bộ model/service contract đang active.
- Impact: Release Production Order có BOM snapshot lỗi ngay khi ghi dữ liệu.
- Reproduction/verification: Eloquent create theo payload của service trên `craveva_codex03_audit_20260702` lỗi `Unknown column 'unit_id' in 'field list'`.
- Root cause: Baseline được sinh từ schema thiếu các thay đổi Phase 2 UOM/yield, trong khi source code vẫn giữ logic đó.
- Recommended fix: Bổ sung đúng type/index/FK cho ba cột trong schema authoritative rồi sinh lại baseline.
- Required tests: Fresh MySQL smoke test Estimate -> BOM -> Production Order -> release -> planned consumption.
- Dependencies: DB-P1-004 và TEST-P1-002.
- Confidence: High

Remediation evidence: corrective migration `000505` thêm đủ ba cột; fresh schema có 12 cột và Production suite pass 20 tests/72 assertions.

### DB-P1-003: Không có upgrade contract cho database đang tồn tại

- Severity: P1 High
- Status: Confirmed
- Evidence:
  - `database/migrations/README.md:9-19`
  - `docs/MIGRATION_CONSOLIDATION_PLAN.md:121-138`
- Current behavior: `php artisan migrate:status` trên DB local hiện tại báo 505 baseline migration pending; tài liệu chỉ cảnh báo không chạy baseline trên DB có dữ liệu.
- Expected behavior: DB hiện hữu có migration registry/bridge rõ ràng và deploy command không cố tạo lại bảng.
- Impact: Deploy chạy `php artisan migrate` có thể dừng ngay ở duplicate table và để trạng thái rollout không xác định.
- Reproduction/verification: Chỉ chạy read-only `php artisan migrate:status`; không chạy migrate trên DB hiện tại.
- Root cause: Consolidation thay toàn bộ migration history nhưng không cung cấp transition cho DB đã chạy legacy migrations.
- Recommended fix: Tạo bridge có schema fingerprint; chỉ ghi baseline registry sau khi toàn bộ precondition pass.
- Required tests: Rehearsal trên clone của current DB; dry-run không có destructive DDL; `migrate:status` không còn pending sai.
- Dependencies: DB-P1-004.
- Confidence: High

### DB-P1-004: Baseline không phản ánh schema/source hiện hành

- Severity: P1 High
- Status: Confirmed
- Evidence:
  - `database/migrations/2000_01_01_000269_create_production_order_bom_snapshot_items_baseline.php:12-29`
  - `docs/MIGRATION_CONSOLIDATION_PLAN.md:100-113`
- Current behavior: Current và fresh đều có 503 application tables, nhưng compare cho 73 changed table definitions; 79 `SHOW CREATE` khác; 142 column definitions khác; current có 3 cột fresh thiếu.
- Expected behavior: Baseline có fingerprint khớp schema contract được code sử dụng, trừ các diff được duyệt rõ ràng.
- Impact: Ngoài lỗi Production đã confirmed, precision/null/default drift có thể gây overflow hoặc hành vi khác giữa khách cài mới và môi trường hiện hữu.
- Reproduction/verification: So sánh `information_schema.columns` và normalized `SHOW CREATE TABLE` giữa current DB và `craveva_codex03_audit_20260702`.
- Root cause: Nguồn baseline consolidation không còn đồng bộ với schema phát triển sau đó hoặc không phải schema runtime đầy đủ.
- Recommended fix: Lập decision log cho 142 diff, chọn contract đúng, regen baseline và lưu schema fingerprint trong CI.
- Required tests: Schema diff = 0 unexplained; boundary tests cho monetary precision/default/nullability.
- Dependencies: BA/CTO phải chốt current schema hay code contract là nguồn ưu tiên khi chúng xung đột.
- Confidence: High

### DB-P1-005: Seed contract bị chia đôi và có side effect

- Severity: P1 High
- Status: Confirmed
- Evidence:
  - `database/seeders/DatabaseSeeder.php:17-43`
  - `docs/MIGRATION_CONSOLIDATION_PLAN.md:121-138`
  - `database/seeders/data/full_20260701/_README.md:1-15`
- Current behavior: `DatabaseSeeder` gọi `key:generate`; CLI fresh contract dùng JSON importer riêng; browser installer chưa import JSON seed.
- Expected behavior: Mọi supported install path tạo cùng reference data và seed không thay đổi application key.
- Impact: Cài qua browser/CLI cho kết quả khác nhau; chạy seed nhầm có thể đổi key và làm encrypted data không đọc được.
- Reproduction/verification: JSON importer pass 121 file/3.458 row; tài liệu xác nhận browser installer chưa thực hiện bước này.
- Root cause: Consolidation thêm importer nhưng chưa hợp nhất installer/DatabaseSeeder lifecycle.
- Recommended fix: Tách key generation khỏi seeding; tạo một orchestrator fresh-install dùng chung cho CLI/installer.
- Required tests: Cài CLI và installer tạo cùng schema/reference row counts; rerun seed không đổi APP_KEY.
- Dependencies: DB-P0-001.
- Confidence: High

### DB-P2-006: Tenant key, index và FK chưa đồng nhất

- Severity: P2 Medium
- Status: Confirmed
- Evidence:
  - Fresh `information_schema.columns`, `statistics`, `key_column_usage` trên `craveva_codex03_audit_20260702`.
- Current behavior: Năm bảng dùng `company_id bigint unsigned` trong khi `companies.id` là `int unsigned` và không có FK; năm bảng khác có `company_id` nhưng không index.
- Expected behavior: Tenant key cùng type và có index/FK ở nơi lifecycle cho phép.
- Impact: Tenant integrity phụ thuộc code; truy vấn tenant có thể chậm khi dữ liệu tăng.
- Reproduction/verification: Affected type/FK tables: `order_import_rows`, `pricing_tiers`, `sales_histories`, `sales_history_lines`, `stock_movements`. Missing-index tables: `purchase_settings`, `purchase_vendor_notes`, `purchase_vendors`, `razorpay_subscriptions`, `recruit_settings`.
- Root cause: Schema phát triển qua nhiều module/migration generation conventions.
- Recommended fix: Review lifecycle rồi chuẩn hóa type/index; thêm FK chỉ sau orphan audit.
- Required tests: Orphan query, EXPLAIN tenant queries, migration rehearsal trên clone.
- Dependencies: DB-P1-004.
- Confidence: High

### DB-P2-007: Hai pivot table không ngăn duplicate association

- Severity: P2 Medium
- Status: Confirmed
- Evidence:
  - Fresh `information_schema.table_constraints` và `statistics` trên `craveva_codex03_audit_20260702`.
- Current behavior: `recruit_job_addresses` và `user_zoom_meeting` không có primary key hoặc composite unique.
- Expected behavior: Một cặp association chỉ tồn tại một lần nếu business contract không cho duplicate.
- Impact: Retry/race có thể tạo relation trùng và làm count/notification sai.
- Reproduction/verification: Schema inspection cho thấy chỉ có index/FK riêng lẻ.
- Root cause: Pivot schema không khai báo uniqueness contract.
- Recommended fix: Audit duplicate hiện có, dedupe có approval, sau đó thêm composite unique.
- Required tests: Insert duplicate phải fail hoặc upsert không tăng row count.
- Dependencies: BA xác nhận duplicate association có bao giờ hợp lệ hay không.
- Confidence: Medium

## Fresh schema integrity evidence

| Kiểm tra | Kết quả |
|---|---:|
| Migration khi chủ động bỏ qua dump | 505/505 pass |
| Application tables | 503 |
| Foreign keys | 1.214 |
| Declared FK type mismatch | 0 |
| `FOREIGN_KEY_CHECKS` sau migrate | 1 |
| Seed JSON | 121 files / 3.458 rows pass |
| Superadmin + password hash | Pass |
| Fortify callback authentication | Pass |

Disposable evidence DB được giữ lại, không tự xóa: `craveva_codex01_audit_20260702`, `craveva_codex02_audit_20260702`, `craveva_codex03_audit_20260702`.
