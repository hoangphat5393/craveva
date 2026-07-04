# Security Audit

### SEC-P1-001: Google OAuth callback và token transport không an toàn

- Severity: P1 High
- Status: Confirmed code path; exploitability needs runtime mode verification
- Evidence:
  - `routes/web-settings.php:63-65`
  - `app/Http/Controllers/GoogleAuthController.php:15-25`
  - `app/Http/Controllers/GoogleAuthController.php:49-61`
- Current behavior: Callback nằm ngoài auth group, dùng request `state` làm redirect target, đưa access token vào query string và có nhánh nhận token/profile từ request để update setting.
- Expected behavior: State là nonce một lần gắn session; redirect nội bộ; token không xuất hiện trong URL; callback bound với user/company khởi tạo.
- Impact: Có thể open redirect, lộ token qua history/proxy/referrer hoặc sửa setting ngoài đúng tenant/session tùy runtime mode.
- Reproduction/verification: Static path confirmed; không gửi OAuth request thật để tránh thay đổi external account.
- Root cause: `state` đang kiêm redirect URL và không thấy bước verify state/session.
- Recommended fix: Dùng cryptographic state nonce, server-side callback context và route allowlist.
- Required tests: Invalid/reused state rejected; external redirect rejected; unauthenticated direct callback cannot mutate setting; token absent from URL/log.
- Dependencies: Cần test credentials Google riêng và xác nhận `isNonCraveva()` behavior.
- Confidence: High về code smell, Medium về full exploitability.

### SEC-P2-002: Raw SQL interpolation trong login validation

- Severity: P2 Medium
- Status: Confirmed
- Evidence:
  - `app/Models/UserAuth.php:153-164`
  - `app/Providers/FortifyServiceProvider.php:193-207`
- Current behavior: Ba câu SQL nối `$userAuth->email` trực tiếp; Fortify path có email validation nhưng method có thể được gọi từ path khác hoặc dữ liệu đã lưu.
- Expected behavior: Mọi value dùng bound parameter/query builder.
- Impact: Tăng SQL injection risk và làm code khó audit.
- Reproduction/verification: Static sink/source verification; không thử payload trên login.
- Root cause: Legacy raw SQL thay vì query builder.
- Recommended fix: Chuyển sang query builder hoặc positional binding.
- Required tests: Email bình thường/special characters; company active/unapproved/inactive behavior không đổi.
- Dependencies: Không.
- Confidence: High

### SEC-P3-003: Auth telemetry ghi PII không cần thiết

- Severity: P3 Low
- Status: Confirmed
- Evidence:
  - `app/Providers/FortifyServiceProvider.php:193-217`
- Current behavior: Mỗi login attempt log email, auth ID và result.
- Expected behavior: Log tối thiểu, redact identifier, dùng correlation ID.
- Impact: Tăng dữ liệu nhạy cảm trong log.
- Reproduction/verification: Static logging call verification.
- Root cause: Debug logging được giữ trong production auth callback.
- Recommended fix: Bỏ hoặc hash/redact PII; hạ log volume.
- Required tests: Login pass/fail không phụ thuộc log; log assertion không chứa email/password/token.
- Dependencies: Logging/incident policy.
- Confidence: High

### SEC-P2-004: Non-local environment có nguy cơ bật debug

- Severity: P2 Medium
- Status: Needs deployment verification
- Evidence:
  - `.env.example:4-7`
  - Current local audit environment reports `APP_DEBUG=true`.
- Current behavior: Local hostname audit đang bật full debug; ảnh lỗi staging trước đây cũng hiển thị stack page nhưng không được xem là runtime proof hiện tại.
- Expected behavior: Mọi staging/production deployment bắt buộc `APP_DEBUG=false`.
- Impact: Exception có thể lộ path, stack, SQL và cấu hình.
- Reproduction/verification: Chỉ xác nhận local `.env` hiện tại; chưa SSH/runtime verify server.
- Root cause: Không có deployment gate dựa trên URL/environment.
- Recommended fix: Preflight fail nếu non-local host bật debug.
- Required tests: Deploy/preflight matrix cho local, staging, production.
- Dependencies: Deployment scripts.
- Confidence: Medium

## Negative findings

- API group có `ThrottleRequests:api` tại `app/Http/Kernel.php:73-77`; limiter 60/min tại `app/Providers/RouteServiceProvider.php:52-56`.
- AI order integration yêu cầu per-company secret tại `app/Http/Middleware/AuthenticateAiOrderIntegration.php:19-42`.
- Route inventory không có duplicate method+URI hoặc duplicate route name.
- 530 Blade file có `{!! !!}` cần dataflow review; con số này không đồng nghĩa 530 XSS.

