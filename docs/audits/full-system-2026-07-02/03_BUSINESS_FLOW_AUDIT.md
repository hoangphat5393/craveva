# Business Flow Audit

## Confirmed strengths

- Sales DO ship dùng transaction và stock service tại `Modules/Purchase/Services/SalesDoService.php:96-121`.
- Outbound stock lock header, kiểm tra `outbound_stock_applied` và dùng idempotency key tại `Modules/Warehouse/Services/SalesShipmentStockService.php:19-62`.
- GRN giữ header/item/status transition trong transaction tại `Modules/Purchase/Services/GrnService.php:23-50`.
- Production posting dùng transaction và idempotency key cho RM/FG movements.

### BUS-P1-001: Estimate import không idempotent

- Severity: P1 High
- Status: Confirmed
- Evidence:
  - `app/Services/EstimateImportProcessor.php:136-179`
  - `app/Services/EstimateImportProcessor.php:181-202`
- Current behavior: Estimate cũ được lock theo tenant/số báo giá, nhưng mỗi row luôn thêm item và cộng lại total.
- Expected behavior: Retry cùng source row không thay đổi net state.
- Impact: Upload lại cùng file làm trùng line và tăng subtotal/total.
- Reproduction/verification: Static control-flow verification; chưa ghi dữ liệu để runtime reproduce trong audit read-only.
- Root cause: Không có source hash hoặc unique line identity cho imported estimate row.
- Recommended fix: Lưu tenant + source + quotation + line key và dùng upsert/reject duplicate.
- Required tests: Import cùng batch hai lần; concurrent retry; một line sửa giá có contract update rõ ràng.
- Dependencies: BA xác nhận rerun là upsert hay reject.
- Confidence: High

### BUS-P1-002: Inventory import có thể để lại dữ liệu dở dang

- Severity: P1 High
- Status: Resolved 2026-07-02
- Evidence:
  - `Modules/Purchase/Services/InventoryImportRowProcessor.php:113-156`
  - `Modules/Purchase/Services/InventoryImportRowProcessor.php:201-217`
- Current behavior: Product/Unit có thể được save trước khi transaction inventory bắt đầu ở line 203.
- Expected behavior: Mọi mutation của một row commit hoặc rollback cùng nhau.
- Impact: Lỗi warehouse/stock movement vẫn để Product/Unit mới trong DB.
- Reproduction/verification: Static transaction-boundary verification; runtime failure injection chưa chạy vì audit không sửa source.
- Root cause: Transaction chỉ bao quanh inventory/adjustment/stock section, không bao quanh product resolution có write.
- Recommended fix: Bao toàn bộ mutation trong một `DB::transaction`.
- Required tests: Inject exception sau product save và assert zero persisted rows từ row đó.
- Dependencies: Không.
- Confidence: High

Remediation evidence: toàn bộ row được bao bởi `DB::transaction`; failure-injection test xác nhận Product và Unit đều rollback.

### BUS-P1-003: Sales Order import có thể liên kết chéo tenant

- Severity: P1 High
- Status: Resolved 2026-07-02
- Evidence:
  - `app/Jobs/ImportSalesOrderChunkJob.php:115-127`
  - `app/Jobs/ImportSalesOrderChunkJob.php:161-189`
  - `app/Scopes/CompanyScope.php:21-35`
- Current behavior: Client/Product có filter company, nhưng default address ở line 173 và fallback UnitType ở line 186 không có `company_id`. Queue không có authenticated user nên global scope không bảo vệ chắc chắn.
- Expected behavior: Mọi reference tenant-owned phải thuộc company của import job.
- Impact: Order import có thể lưu address/unit của tenant đầu tiên trong DB.
- Reproduction/verification: Static query verification; cần two-tenant runtime test để xác nhận row được chọn trong dữ liệu cụ thể.
- Root cause: Tin vào implicit model scope trong background execution và hai fallback query thiếu filter.
- Recommended fix: Thêm `company_id` explicit; fail nếu tenant không có address/unit hợp lệ.
- Required tests: Hai tenant có default address/unit khác nhau; job tenant B phải không lấy row tenant A.
- Dependencies: Tenant fixture chuẩn.
- Confidence: High

Remediation evidence: hai fallback query đã thêm `company_id`; regression test chèn tenant sai trước và vẫn chọn đúng IDs của tenant hiện tại.

### BUS-P2-004: Background tenancy phụ thuộc kỷ luật từng job

- Severity: P2 Medium
- Status: Confirmed design risk
- Evidence:
  - `app/Traits/HasCompany.php:11-18`
  - `app/Scopes/CompanyScope.php:21-35`
  - `app/Jobs/ImportSalesOrderChunkJob.php:41-60`
- Current behavior: `CompanyScope` chỉ filter khi `auth()->hasUser()`; queue/command phải nhớ thêm company filter thủ công.
- Expected behavior: Tenant execution context hoạt động nhất quán trong HTTP, queue và command.
- Impact: Một query thiếu filter có thể thành tenant leak hoặc sai reference như BUS-P1-003.
- Reproduction/verification: Code path của queue có `company()` nhưng không có authenticated user; scope condition không dùng context đó.
- Root cause: Tenant boundary gắn với authentication thay vì execution context.
- Recommended fix: Thiết kế tenant context cho background jobs và thêm assertion/static checks.
- Required tests: Cùng query trong HTTP và queue phải sinh tenant predicate tương đương.
- Dependencies: Kiến trúc tenancy cần CTO duyệt.
- Confidence: High

## Regression flow bắt buộc

1. Estimate import lần đầu và retry cùng file.
2. Estimate -> Production BOM -> Production Order -> BOM snapshot.
3. PO -> GRN received -> inbound stock.
4. SO -> Sales DO -> ship -> outbound stock -> reverse/cancel.
5. Inventory absolute quantity import rollback khi stock movement lỗi.
6. Chạy các flow trên hai company.
