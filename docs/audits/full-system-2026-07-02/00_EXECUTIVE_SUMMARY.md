# Full System Audit - Executive Summary

**Ngày audit:** 2026-07-02  
**Phạm vi:** read-only source audit, ưu tiên Database Audit và fresh-install contract.  
**Kết luận sau remediation local:** Fresh install mặc định và ba lỗi P0 đã được sửa. Hệ thống chưa thể xem là hoàn toàn release-ready cho đến khi schema drift và installer contract còn lại được xử lý.

## Remediation update 2026-07-02

- DB-P0-001: **Resolved** - default `php artisan migrate --force` pass trên MySQL trống và load 506 migration records.
- DB-P0-002: **Resolved** - corrective migration thêm ba cột Production; fresh schema và Production tests pass.
- TEST-P0-001: **Resolved** - PHPUnit dùng SQLite memory và chặn database không có marker test/testing/audit.
- TEST-P1-002: **Resolved** - 20 Production posting cases pass với 72 assertions.
- BUS-P1-002: **Resolved** - inventory row nằm trọn trong transaction; failure-injection rollback test pass.
- BUS-P1-003: **Resolved** - SO import scope address/unit theo company; two-tenant assertions pass.
- OPS-P1-001: **Resolved** - scheduled queue workers mặc định tắt và chỉ bật bằng env flag.

## Kết quả chính

| ID | Mức độ | Kết luận |
|---|---|---|
| DB-P0-001 | P0 Critical | `php artisan migrate --force` trên database trống nạp `database/schema/mysql-schema.dump`, sau đó baseline tạo lại cùng bảng và lỗi `Table already exists`. |
| DB-P0-002 | P0 Critical | Baseline Production thiếu `unit_id`, `yield_factor`, `quantity_per_fg_unit_base_shadow`; code runtime đang ghi/đọc cả ba cột. Fresh schema sẽ lỗi khi release Production Order. |
| TEST-P0-001 | P0 Critical | `phpunit.xml` không khóa test vào database cô lập. Một test không tự đổi connection có thể ghi vào database từ `.env`. |
| DB-P1-003 | P1 High | Database local hiện tại báo toàn bộ 505 baseline migration là pending. Không có bridge/registry contract an toàn cho database đã tồn tại. |
| DB-P1-004 | P1 High | So sánh schema local hiện hành với fresh baseline cho thấy 73 bảng thay đổi theo công cụ compare, 79 bảng khác `SHOW CREATE`, 142 định nghĩa cột khác nhau. |
| TEST-P1-002 | P1 High | 20/20 case `ProductionPostingServiceTest` lỗi trước assertion vì vẫn require các module migration đã bị xóa khi consolidation. |
| BUS-P1-001 | P1 High | Import Estimate không có row idempotency; chạy lại cùng file sẽ thêm item và cộng total lần nữa. |
| BUS-P1-003 | P1 High | Sales Order import lấy default company address và fallback unit mà không thêm `company_id`, có thể liên kết dữ liệu tenant khác. |
| SEC-P1-001 | P1 High | Google OAuth callback nằm ngoài `auth`, dùng `state` làm redirect URL, đưa access token lên query string và có đường cập nhật setting từ request. |
| OPS-P1-001 | P1 High | Scheduler luôn chạy `queue:work` mỗi phút trong khi runbook triển khai dùng Supervisor; cấu hình thực tế có nguy cơ chạy hai nhóm worker. |

## Fresh-install đã kiểm chứng

1. Audit ban đầu: database trống + migration mặc định **FAIL** do schema dump/baseline xung đột.
2. Sau remediation: database trống + migration mặc định **PASS**, schema dump nạp 506 migration records và không chạy lặp baseline.
3. Import seed JSON: **PASS**, 121 file, 3.458 dòng và checksum hợp lệ.
4. Tạo superadmin và gọi Fortify authentication callback: **PASS**.
5. Sau remediation, fresh `ProductionOrderBomSnapshotItem` có đủ 12 cột và Production posting tests **PASS**.

Kết quả này chứng minh "migration chạy hết" chưa đủ để xác nhận fresh install dùng được.

## Điểm tốt đã xác nhận

- 506 file migration đều qua `php -l` sau remediation.
- Fresh schema không có foreign key sai kiểu trong các FK đã khai báo.
- Route inventory có 3.369 route, không có method+URI hoặc route name trùng.
- API group đã có `throttle:api`; không ghi nhận thiếu rate limit ở lớp route group.
- Sales DO, GRN và Production posting có transaction/idempotency/row lock ở các service stock chính.
- Không còn lời gọi `$.easyAjax` thực tế trong Blade/JS; chỉ còn một dòng mô tả trong comment của Axios helper.

## Quyết định cần duyệt

Phase 0 local đã hoàn tất. Tiếp theo ưu tiên DB-P1-004, DB-P1-005, BUS-P1-001 và SEC-P1-001 trong [07_REMEDIATION_PLAN.md](07_REMEDIATION_PLAN.md).

## Đánh giá tổng thể

- Có thể tiếp tục đọc/audit và phát triển trên branch cô lập: **Có**.
- Có thể fresh install kỹ thuật từ source hiện tại: **Có**, theo CLI contract đã kiểm chứng.
- Có thể áp baseline lên database legacy có dữ liệu: **Không**; baseline chỉ dành cho database mới.
- Có thể tin toàn bộ test suite là release gate: **Chưa**; targeted remediation tests pass nhưng full suite chưa sạch.

## Câu hỏi BA/CTO cần quyết định

1. Schema authoritative là current database, code contract, hay baseline đã consolidation?
2. Browser installer còn là supported customer install path hay chỉ hỗ trợ CLI/deployment automation?
3. Estimate import retry phải upsert, bỏ qua hay báo duplicate?
4. Production dùng Supervisor độc quyền hay vẫn cần scheduled worker fallback?
5. Hai pivot association có cho phép duplicate theo nghiệp vụ không?
