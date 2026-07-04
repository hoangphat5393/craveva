# Full System Audit Prompt

Sao chép toàn bộ nội dung bên dưới vào một thread Codex mới khi cần audit lại dự án.

---

Bạn là **CTO, Software Architect, Senior Laravel Engineer, DBA và Security Reviewer**. Hãy audit toàn bộ source code hiện tại của dự án Craveva ERP tại:

```text
E:\web\craveva-staging
```

## Mục tiêu

Xác định hệ thống đang đúng, sai hoặc thiếu ở đâu, đặc biệt sau quá trình thay đổi và hợp nhất database migrations. Kết quả phải đủ cụ thể để lập kế hoạch sửa theo thứ tự rủi ro, không chỉ đưa ra nhận xét chung chung.

## Nguyên tắc bắt buộc

1. Đây là **audit read-only đối với source và dữ liệu hiện hữu**. Không sửa file đang tồn tại. Chỉ được tạo báo cáo mới trong `docs/audits/full-system-YYYY-MM-DD/` và, sau khi xác nhận an toàn, database audit local dùng một lần.
2. Đọc `AGENTS.md` trước và tuân thủ toàn bộ rule của repository.
3. Không SSH, deploy hoặc tác động Hub/Staging/Production. Chỉ audit source và môi trường local.
4. Không chạy `migrate:fresh`, `db:wipe`, `DROP`, `TRUNCATE`, seed hoặc script sửa dữ liệu trên database hiện tại.
5. Nếu cần thử migration, phải tạo một database audit tạm hoàn toàn riêng. Chỉ tiếp tục khi host là local, tên database có hậu tố `_audit_YYYYMMDD`, và đã in connection không chứa password để kiểm tra. Không dùng DB trong `.env` hiện tại.
6. Không đọc hoặc công bố secret trong `.env`. Chỉ báo tên biến cấu hình bị thiếu/sai.
7. Không kết luận từ tên file. Phải đối chiếu migration, schema, model, service, controller, route, validation, UI và test.
8. Mọi finding phải có bằng chứng `file:line`, mức độ ảnh hưởng và cách tái hiện hoặc kiểm chứng.
9. Không tự động sửa sau audit. Dừng lại chờ duyệt kế hoạch remediation.

## Bước 1: Lập bản đồ hệ thống

Đọc và thống kê:

- Laravel version, PHP version và package trong `composer.json`/`composer.lock`.
- Module trong `Modules/`, trạng thái module và service provider.
- Route web/API/console, middleware, permission và menu.
- Models, controllers, services, jobs, observers, events, commands và scheduled tasks.
- Blade/JavaScript/Axios, queue, cache, filesystem và external integrations.
- Tài liệu nghiệp vụ trong `FUNC_LOGIC`, `FUNC_IMPROVE`, `FUNC_TEST`, `PROJECT BIOMIXING`, `docs` và `SPECIFICATION`.

Tạo sơ đồ ngắn:

```text
Domain -> Module -> Route -> Controller -> Service -> Model/Table -> Job/Event -> UI/Test
```

Không dùng tài liệu làm bằng chứng duy nhất. Luôn đối chiếu với code chạy thật.

## Bước 2: Audit Database trước tiên

### 2.1 Migration và fresh install

Kiểm tra toàn bộ `database/migrations` và migration trong `Modules/*/Database/Migrations`:

- Thứ tự chạy và dependency giữa bảng.
- Bảng được tạo nhiều lần hoặc tên bảng trùng nghĩa.
- Foreign key tham chiếu bảng chưa tồn tại.
- Kiểu cột FK không khớp `id` được tham chiếu.
- Cột được code sử dụng nhưng migration không tạo.
- Cột có trong migration nhưng không còn code sử dụng.
- Index, unique constraint và composite index còn thiếu.
- Nullable/default không phù hợp nghiệp vụ.
- Decimal precision, currency, quantity, UOM và timezone.
- Enum/status bị phân tán hoặc dùng giá trị không đồng nhất.
- Soft delete, cascade/restrict/set-null và orphan records.
- Baseline `2000_01_01_*` có phản ánh đúng schema cuối cùng hay không.
- Module migration có bị trùng hoặc xung đột với baseline hay không.
- Fresh seed có tạo đủ dữ liệu bắt buộc để login và sử dụng module hay không.
- Source mới có còn phụ thuộc migration ID hoặc bảng legacy đã bị xóa hay không.

### 2.2 Schema-code contract

Với từng table/model quan trọng, đối chiếu:

- Table name và primary key.
- `$fillable`, `$guarded`, casts, dates và hidden fields.
- Relations và foreign/local key.
- Validation request so với nullability, length và unique constraint.
- Controller/service có thực sự persist mọi field từ form hay không.
- Blade/JavaScript gửi field nhưng backend bỏ qua.
- Backend ghi field nhưng database không có.
- Query dùng alias/cột không tồn tại.
- Raw SQL hoặc query không tương thích MySQL strict mode.
- N+1 query, full table scan và index còn thiếu.

### 2.3 Multi-tenant và toàn vẹn dữ liệu

Kiểm tra đặc biệt:

- Mọi dữ liệu tenant có `company_id` hoặc scope tương đương.
- Không thể đọc/sửa dữ liệu công ty khác bằng cách thay ID trên URL/request.
- Unique constraint có đúng scope công ty hay đang unique toàn hệ thống.
- Jobs, observers, imports, exports và commands có giữ đúng tenant context.
- Transaction boundary cho nghiệp vụ nhiều bảng.
- Idempotency và chống ghi trùng khi retry queue/API.
- Race condition khi cập nhật stock, invoice number, quotation number và sequence.

### 2.4 Luồng nghiệp vụ nhạy cảm

Audit end-to-end tối thiểu các luồng:

```text
Client -> Estimate/Quotation -> Sales Order -> Sales DO -> Ship -> Invoice -> Payment
Vendor -> Purchase Order -> GRN -> Inventory -> Bill -> Payment
Product -> UOM -> Warehouse -> Stock Movement -> Adjustment/Return
Sales Order/Estimate -> BOM -> Production Order -> RM Reserve/Consume -> FG Post
Import -> Queue/Chunk -> Validation -> Persist -> Progress/Failure report
```

Với từng bước, chỉ rõ:

- Table nào được đọc/ghi.
- Trạng thái chuyển từ gì sang gì.
- Service/controller nào chịu trách nhiệm.
- Có transaction, permission, tenant scope và test hay chưa.
- Stock tăng/giảm đúng một lần hay có nguy cơ double posting.

## Bước 3: Audit Backend và kiến trúc

Kiểm tra:

- Business logic nằm sai trong controller, model hoặc Blade.
- Logic bị sao chép giữa core và module.
- Service có side effect không rõ ràng.
- Observer/event/job tạo vòng lặp hoặc ghi dữ liệu ngoài transaction.
- Route trùng, route thiếu controller hoặc method không tồn tại.
- Permission name không đồng bộ giữa migration, seeder, controller và UI.
- Exception bị nuốt, log thiếu context hoặc lộ dữ liệu nhạy cảm.
- Queue job không idempotent, timeout/retry không phù hợp.
- Command/script nguy hiểm không có dry-run hoặc environment guard.
- Package Composer không dùng, package abandoned hoặc constraint xung đột.

## Bước 4: Audit Security

Kiểm tra tối thiểu:

- Authentication, authorization, policies và permission bypass.
- IDOR và tenant data leakage.
- Mass assignment.
- SQL injection và raw query binding.
- XSS trong Blade, `{!! !!}`, HTML từ DB và DataTable render.
- CSRF và method spoofing.
- Upload file: MIME, extension, path traversal, public exposure.
- SSRF, webhook signature, OAuth/API token storage.
- Encryption/decryption fallback và secret logging.
- APP_DEBUG, error pages và stack trace.
- Rate limit cho login, verify, import và public API.

Không khai thác hoặc phá dữ liệu. Chỉ dùng kiểm tra an toàn.

## Bước 5: Audit Frontend và API contract

Kiểm tra:

- Blade route/action có tồn tại và đúng HTTP method.
- Axios request, CSRF, validation error và loading state.
- Không còn `$.easyAjax` ngoài các ngoại lệ được ghi nhận rõ.
- Form field name khớp validation và persistence.
- Modal/right panel không làm mất event hoặc submit hai lần.
- DataTable column khớp backend JSON.
- URL trong tài liệu/help khớp route hiện tại.
- Permission/menu không hiển thị chức năng người dùng không được gọi.

## Bước 6: Audit Test và khả năng dựng mới

Không dùng database hiện tại cho destructive test.

Thực hiện theo thứ tự:

1. PHP syntax và Composer validation.
2. Route registration và Artisan command registration.
3. Unit test không cần DB.
4. Feature test trên database audit riêng.
5. Fresh migration trên database audit riêng.
6. Seed và login smoke test trên database audit riêng.
7. Kiểm tra các luồng nghiệp vụ P0 bằng test có sẵn.

Đánh giá:

- Test nào thực sự kiểm tra behavior, test nào chỉ assert route/file tồn tại.
- Domain quan trọng nào không có test.
- Test phụ thuộc DB cũ hoặc dữ liệu máy dev.
- Factory/seeder có tạo dữ liệu hợp lệ hay không.
- Test có cô lập tenant và transaction hay không.

## Bước 7: Audit vận hành và maintainability

Kiểm tra:

- `.gitignore`, generated output, backup, log và secret.
- Script deploy/backup/permission có còn đúng với Ubuntu + Nginx + PHP-FPM.
- Cache/config/route/view clear sau deploy.
- Queue worker/Supervisor và scheduler.
- Storage permission và ownership.
- Tài liệu nào sai so với code.
- Dead code, orphan controller/view/route/script.
- File legacy có thể xóa nhưng phải chứng minh không còn consumer.

## Phân loại finding

Sử dụng đúng mức độ:

- **P0 Critical:** mất dữ liệu, sai tồn kho/tài chính, tenant leak, auth bypass, fresh install không chạy.
- **P1 High:** luồng chính hỏng, schema-code mismatch, double posting, migration/seed không đáng tin.
- **P2 Medium:** lỗi chức năng có workaround, thiếu index, validation hoặc test quan trọng.
- **P3 Low:** dead code, tài liệu lệch, naming, maintainability.

Không nâng severity chỉ vì code xấu. Severity phải dựa trên tác động thực tế.

## Đầu ra bắt buộc

Tạo thư mục báo cáo:

```text
docs/audits/full-system-YYYY-MM-DD/
```

Gồm các file:

1. `00_EXECUTIVE_SUMMARY.md`
   - Đánh giá tổng thể.
   - 10 rủi ro lớn nhất.
   - Có thể fresh install hay chưa.
   - Có an toàn để tiếp tục phát triển/deploy hay chưa.

2. `01_SYSTEM_MAP.md`
   - Module/domain map.
   - Luồng request và dependency chính.

3. `02_DATABASE_AUDIT.md`
   - Migration/schema/model mismatch.
   - FK/index/tenant/data integrity.
   - Fresh migration và seed evidence.

4. `03_BUSINESS_FLOW_AUDIT.md`
   - Sales, Purchase, Warehouse, Production, Estimate và Import.

5. `04_SECURITY_AUDIT.md`
   - Finding, evidence và exploitability an toàn.

6. `05_CODE_AND_FRONTEND_AUDIT.md`
   - Backend, Blade, Axios, route/API contract và dead code.

7. `06_TEST_AND_OPERATIONS_AUDIT.md`
   - Test coverage, fresh-install test, queue, scheduler và deploy readiness.

8. `07_REMEDIATION_PLAN.md`
   - Bảng `ID | Severity | Finding | Evidence | Impact | Fix | Test | Dependency | Estimate`.
   - Chia Phase P0, P1, P2, P3.
   - Sắp xếp theo dependency và blast radius, không chỉ theo độ dễ.

9. `EVIDENCE_COMMANDS.md`
   - Ghi lệnh đã chạy, exit code và kết quả tóm tắt.
   - Không ghi secret hoặc dữ liệu khách hàng.

## Format finding bắt buộc

Mỗi finding phải theo mẫu:

```markdown
### DB-P1-001: Order model dùng cột không có trong fresh schema

- Severity: P1 High
- Status: Confirmed
- Evidence:
  - `path/to/file.php:123`
  - `database/migrations/file.php:45`
- Current behavior:
- Expected behavior:
- Impact:
- Reproduction/verification:
- Root cause:
- Recommended fix:
- Required tests:
- Dependencies:
- Confidence: High/Medium/Low
```

## Quy tắc kết luận

- Phân biệt rõ **Confirmed**, **Likely**, **Needs runtime verification**.
- Không gọi tài liệu cũ là bằng chứng runtime.
- Không tuyên bố test pass nếu chưa chạy.
- Không tuyên bố package/file không dùng chỉ dựa trên `rg` không có kết quả.
- Nếu audit bị chặn, ghi chính xác blocker và tiếp tục các phần không bị chặn.
- Kết thúc audit bằng danh sách câu hỏi cần BA/CTO quyết định.
- Sau khi tạo báo cáo, dừng lại và chờ duyệt. Không tự triển khai remediation.

---

## Lệnh khởi động gợi ý

```text
Hãy bắt đầu audit theo prompt trong docs/FULL_SYSTEM_AUDIT_PROMPT.md. Đây là audit read-only. Ưu tiên Database Audit và fresh-install contract trước. Không sửa source hoặc database cho đến khi tôi duyệt 07_REMEDIATION_PLAN.md.
```
