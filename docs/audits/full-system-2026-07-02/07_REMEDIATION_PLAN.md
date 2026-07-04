# Remediation Plan

Không triển khai thay đổi dưới đây cho đến khi được duyệt. Mọi database change phải rehearsal trên clone/disposable DB và có rollback evidence.

## Completed locally 2026-07-02

Completed: TEST-P0-001, DB-P0-001, DB-P0-002, TEST-P1-002, BUS-P1-002, BUS-P1-003 và OPS-P1-001. Các finding còn lại giữ nguyên thứ tự ưu tiên bên dưới.

## Remediation register

| ID | Severity | Finding | Evidence | Impact | Fix | Test | Dependency | Estimate |
|---|---|---|---|---|---|---|---|---|
| TEST-P0-001 | P0 | PHPUnit không khóa DB test | `phpunit.xml:11-20` | Có thể ghi nhầm DB thật | `.env.testing` + fail-closed DB guard | Unsafe target bị chặn trước discovery | None | 0.5-1 day |
| DB-P0-001 | P0 | Dump xung đột baseline | dump `:7-23`, baseline first migration | Fresh install fail | Chọn một schema source active | Default migrate on empty MySQL | DB-P1-004 decision | 1-2 days |
| DB-P0-002 | P0 | Fresh Production thiếu 3 cột | baseline `:12-29`, service `:112-122` | Production release fail | Sửa authoritative schema và regen baseline | Production fresh smoke | DB-P1-004 | 1-2 days |
| DB-P1-003 | P1 | Existing DB thấy 505 pending | `migrate:status`, migration README | Deploy migration unsafe | Fingerprint + registry bridge | Clone rehearsal | DB-P1-004 | 2-4 days |
| DB-P1-004 | P1 | 142 column definitions drift | schema comparison | Fresh/current behavior khác | Classify diff và regen | Zero unexplained diff | BA/CTO schema decision | 3-7 days |
| DB-P1-005 | P1 | Seed/installer contract chia đôi | Seeder `:17-43`, plan `:136-138` | Cài đặt không deterministic | Unified installer; no key generation in seed | CLI/browser parity | DB-P0-001 | 1-3 days |
| TEST-P1-002 | P1 | Production suite require file đã xóa | test `:153-165` | 20 tests không chạy logic | Dùng active schema fixture | 20 cases pass/assert | TEST-P0-001, DB-P0-002 | 1-2 days |
| TEST-P1-003 | P1 | Critical DB tests silently skip | fixture `:24-63` | False green CI | Deterministic fixtures | No critical skips | TEST-P0-001 | 1-2 days |
| TEST-P1-004 | P1 | Không có fresh-install CI | migration docs | Regression không bị chặn | MySQL contract pipeline | Migrate/seed/login/flows | DB P0 fixes | 2-3 days |
| BUS-P1-001 | P1 | Estimate import không idempotent | processor `:136-202` | Duplicate lines/totals | Source line key + upsert/reject | Same batch twice | BA retry contract | 2-4 days |
| BUS-P1-002 | P1 | Inventory mutation ngoài transaction | processor `:113-203` | Partial row data | One row/one transaction | Failure injection rollback | None | 1-2 days |
| BUS-P1-003 | P1 | SO import fallback thiếu tenant filter | job `:173`, `:186` | Cross-tenant reference | Explicit company filters | Two-tenant import | Tenant fixture | 0.5-1 day |
| SEC-P1-001 | P1 | OAuth state/token/callback unsafe | route `:63`, controller `:20-57` | Redirect/token/setting risk | Nonce state + server context | OAuth negative tests | Google test app | 2-4 days |
| OPS-P1-001 | P1 | Scheduler và Supervisor worker overlap | Kernel `:203-220`, runbook | Dư worker/load | Env-gated single strategy | Process/queue smoke | Ops decision | 1 day |
| DB-P2-006 | P2 | Tenant key/index/FK lệch | fresh information_schema | Integrity/performance debt | Normalize after orphan audit | EXPLAIN + clone migrate | DB-P1-004 | 2-5 days |
| DB-P2-007 | P2 | Pivot không unique | fresh information_schema | Duplicate relation | Dedupe + composite unique | Duplicate insert test | BA decision | 1-2 days |
| BUS-P2-004 | P2 | Queue tenancy phụ thuộc auth/manual filters | CompanyScope `:21-35` | Repeat tenant bugs | Background tenant context | HTTP/queue parity | CTO architecture | 3-7 days |
| SEC-P2-002 | P2 | Raw SQL email interpolation | UserAuth `:159-163` | Injection risk | Query builder/bindings | Login state matrix | None | 0.5 day |
| SEC-P2-004 | P2 | Debug gate chưa enforced | `.env.example:4-7` | Exception disclosure | Deploy preflight | Environment matrix | Deployment scripts | 0.5 day |
| OPS-P2-002 | P2 | Composer warnings | `composer.json` | Upgrade friction | Package-by-package cleanup | Dry-run + isolated suite | Stable tests | 1-3 days |
| SEC-P3-003 | P3 | Auth logs chứa PII | Fortify provider `:193-217` | Sensitive log data | Redact/remove | Log assertions | Logging policy | 0.5 day |

Estimates là engineering time thô, chưa gồm UAT/approval/deployment window.

## Phase P0 - Safety gate

1. Làm TEST-P0-001 trước để mọi verification sau an toàn.
2. Chốt schema authoritative và xử lý DB-P0-001.
3. Sửa DB-P0-002 rồi chạy Production smoke trên fresh DB.
4. Không chạy baseline trên current/staging/hub trong phase này.

**Exit:** default fresh migrate pass; Production release pass; tests không thể trỏ nhầm DB.

## Phase P1 - Contract reliability

1. Phân loại DB-P1-004 và xây DB-P1-003 bridge.
2. Hợp nhất seed/installer contract.
3. Repair Production tests và thêm fresh-install CI.
4. Sửa ba import findings.
5. Hardening OAuth và chọn một queue worker strategy.

**Exit:** clone upgrade rehearsal pass; fresh CI pass; import retry/rollback/tenant tests pass.

## Phase P2 - Integrity and architecture

1. Chuẩn hóa tenant keys/index/FK sau orphan audit.
2. Chốt pivot uniqueness.
3. Thiết kế background tenant context.
4. Parameterize login SQL, thêm debug deployment gate, xử lý Composer theo batch.

## Phase P3 - Hygiene

1. Redact auth telemetry.
2. Cập nhật runbook/install docs chỉ sau khi command thực tế đã pass.
3. Không xóa legacy model/file chỉ từ kết quả static scan; cần consumer/runtime evidence.

## Rollback rules

- Không rollback bằng drop table/column trên DB thật.
- Không ghi migration registry nếu schema fingerprint chưa khớp.
- Schema remediation phải có clone rehearsal và backup restore test.
- Nếu Production columns đã tồn tại, bridge phải kiểm tra type/index trước khi đánh dấu trạng thái.

## BA/CTO decisions required

1. Authoritative schema source.
2. Supported installer path.
3. Estimate retry semantics.
4. Production queue worker ownership.
5. Pivot duplicate semantics.
