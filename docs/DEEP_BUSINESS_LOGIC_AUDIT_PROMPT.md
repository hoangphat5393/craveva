# Deep Business Logic Audit Prompt

## Vai trò

Bạn là Principal Business Analyst, ERP Solution Architect và CTO có kinh nghiệm audit hệ thống Laravel ERP nhiều tenant.

Mục tiêu không phải review code style. Mục tiêu là xác định hệ thống có sai hoặc đứt logic nghiệp vụ ở tầng sâu hay không, đặc biệt các lỗi chỉ xuất hiện khi retry, chạy đồng thời, đảo giao dịch, xử lý một phần hoặc đi qua nhiều module.

Repository:

```text
E:\web\craveva-staging
```

Chỉ đánh giá source code và môi trường local hiện tại. Không quan tâm hoặc truy cập staging/hub server cũ.

## Câu hỏi audit chính

Phải trả lời có bằng chứng:

1. Các luồng nghiệp vụ chính có đúng từ đầu đến cuối không?
2. Có trạng thái nào tạo dữ liệu dở dang, double posting hoặc không thể rollback không?
3. Tồn kho, giá vốn, doanh thu, công nợ và số lượng có bảo toàn qua các chứng từ không?
4. Retry queue/API/import có tạo giao dịch trùng không?
5. Hai request đồng thời có thể vượt tồn kho, trùng số chứng từ hoặc ghi đè trạng thái không?
6. Reverse/cancel/delete có đảo đủ mọi side effect ban đầu không?
7. Tenant A có thể đọc, tham chiếu hoặc cập nhật dữ liệu tenant B không?
8. Fresh install có tạo đủ schema, config và seed để các luồng nghiệp vụ thật chạy được không?
9. Test hiện tại có thực sự kiểm tra logic hay chỉ pass/skip do fixture và môi trường?
10. Có sự khác nhau giữa tài liệu, UI, validation, service, observer, job và database contract không?

## Nguyên tắc an toàn

- Audit source trước, không sửa code ngay.
- Không ghi vào database local hiện tại.
- Chỉ chạy mutation test trên database disposable có tên chứa `_audit` hoặc `_test`.
- Không SSH, deploy, upload hoặc chạy lệnh trên server.
- Không xóa, truncate hoặc migrate:fresh database hiện tại.
- Không dùng dữ liệu khách hàng làm fixture hoặc ghi dữ liệu nhạy cảm vào báo cáo.
- Không kết luận dựa riêng vào tên file, comment, tài liệu hoặc `rg`.
- Không xem `DB::transaction` là đủ an toàn nếu side effect nằm ngoài transaction.
- Không xem idempotency flag là đủ nếu flag và ledger không được ghi atomically.
- Không sửa lỗi trước khi tạo finding và remediation plan, trừ khi người dùng yêu cầu triển khai.

## Nguồn phải đọc

Đọc theo mức liên quan, không bỏ qua tài liệu nghiệp vụ:

- `AGENTS.md`
- `docs/FULL_SYSTEM_AUDIT_PROMPT.md`
- `docs/audits/full-system-2026-07-02/`
- `PROJECT BIOMIXING/`
- `FUNC_LOGIC/`
- `FUNC_IMPROVE/`
- `FUNC_TEST/`
- `FUNC_BUG/`
- `SPECIFICATION/` nếu tồn tại
- `Modules/*/Routes/`
- Controller, Request, Service, Observer, Job, Model/Entity và Blade liên quan
- `database/migrations/`, schema dump, seed/import scripts
- `tests/Unit/`, `tests/Feature/` và module tests
- Config liên quan warehouse, production, pricing, queue, tenancy và currency

Nếu tài liệu mâu thuẫn với runtime code, ghi nhận mâu thuẫn; không tự chọn tài liệu là đúng.

## Phương pháp audit bắt buộc

### Bước 1: Lập bản đồ domain và ownership

Với mỗi domain, xác định:

- aggregate root;
- bảng sở hữu trạng thái chính;
- bảng line/detail;
- ledger hoặc bảng side effect;
- route/controller entry point;
- service chịu trách nhiệm transaction;
- observer/listener/job chạy ngầm;
- tenant key;
- quyền thực hiện;
- chứng từ trước và sau.

Không cho phép một side effect quan trọng có hai owner mà không có quy tắc phối hợp rõ ràng.

### Bước 2: Dựng state machine từ code

Với mỗi chứng từ, lập bảng:

```text
Current state | Command | Preconditions | Writes | Side effects | Next state | Reverse command
```

Kiểm tra:

- transition không hợp lệ có bị chặn không;
- request lặp lại có no-op hay post lại;
- transition có được lock row trước khi kiểm tra không;
- status đổi trước hay sau side effect;
- exception giữa luồng để lại trạng thái gì;
- cancel/reverse có hợp lệ ở từng state;
- trạng thái terminal có bị mở lại ngoài chủ đích không.

### Bước 3: Xác định business invariants

Mỗi flow phải có invariant đo được. Ví dụ:

#### Tồn kho

```text
On hand = Opening + Inbound - Outbound + Reversal adjustments
Available = On hand - Active reservations
Reserved >= 0
Available >= 0, trừ khi policy cho phép âm
Sum(batch quantity) = product/warehouse stock quantity
```

#### Sales Order và giao hàng

```text
Total shipped per SO line <= ordered quantity, trừ over-delivery policy
Total invoiced per line <= total shipped đủ điều kiện
Cancel/return phải đảo đúng stock, reservation và invoice eligibility
```

#### Purchase và GRN

```text
Total received per PO line <= ordered quantity, trừ over-receipt policy
Một GRN received chỉ post inbound một lần
GRN reverse phải đảo đúng batch, stock và trạng thái PO
Bill quantity/value phải đối chiếu với PO/GRN theo policy
```

#### Production

```text
RM consumption + waste phải giải thích được bởi BOM snapshot và output
Production Order release phải đóng băng đúng BOM/UOM/yield contract
FG receipt chỉ post một lần
Cancel/rework phải đảo reservation và movement đúng mức
Không được ship FG bị quality lock
```

#### Estimate và pricing

```text
Subtotal = sum(line amount)
Discount/tax/total phải dùng cùng rounding rule ở UI, import và backend
Approval snapshot không được thay đổi ngầm sau khi duyệt
Convert Estimate -> SO không được tạo nhiều SO khi retry
```

#### Multi-tenant

```text
Mọi foreign/reference tenant-owned phải cùng company_id
Queue/command không được phụ thuộc auth session để scope tenant
Unique business key phải gồm company_id nếu dữ liệu là tenant-specific
```

Với invariant không xác định được do thiếu policy, ghi câu hỏi BA thay vì tự đoán.

### Bước 4: Trace side effect từ đầu đến cuối

Mỗi command trọng yếu phải trace:

```text
Route -> Middleware/Permission -> Request validation -> Controller
-> Service transaction -> Model/Observer -> Ledger/Job/Event
-> Response/UI refresh
```

Kiểm tra side effect ẩn trong:

- model observers;
- event listeners;
- queued jobs;
- notifications/webhooks;
- import processors;
- scheduled commands;
- global scopes;
- model accessors/mutators;
- Blade/JavaScript tự tính lại total hoặc status.

### Bước 5: Transaction và failure injection

Với mọi flow ghi nhiều bảng:

- liệt kê chính xác transaction boundary;
- xác định write trước/sau transaction;
- xác định external side effect không rollback được;
- chèn exception hợp lệ ở giữa flow trong test;
- xác nhận toàn bộ DB writes rollback;
- xác nhận retry sau failure không post trùng.

Đặc biệt kiểm tra pattern:

```text
check flag -> post ledger -> set flag
```

Pattern chỉ an toàn khi cùng transaction, có row lock và unique idempotency constraint.

### Bước 6: Concurrency audit

Kiểm tra ít nhất các race condition:

- hai request ship cùng Sales DO;
- hai request receive cùng GRN;
- hai request release/post cùng Production Order hoặc batch;
- hai import job xử lý cùng source row;
- hai request sinh cùng document number;
- reserve và outbound cùng sản phẩm/batch;
- update giá trong khi tạo Estimate/SO.

Tìm bằng chứng:

- `lockForUpdate`;
- optimistic version/status predicate;
- database unique constraint;
- atomic increment/decrement;
- idempotency key unique;
- job uniqueness/overlap lock.

Nếu chỉ có application check mà không có DB/lock protection, không xem là concurrency-safe.

### Bước 7: Reverse, cancel, return và delete

Lập cặp action/compensation:

```text
Inbound <-> inbound reversal
Outbound <-> outbound reversal
Reservation <-> release reservation
Invoice <-> credit note/cancel
FG receipt <-> production reversal/rework
Import create <-> retry/upsert/reject
```

So sánh tất cả field và ledger lines. Reverse phải tham chiếu movement gốc và không được đảo nhiều lần.

### Bước 8: UOM, currency, tax và rounding

Kiểm tra:

- quantity base unit và transaction unit;
- conversion factor direction;
- yield/waste order of operations;
- decimal precision trong PHP và MySQL;
- float usage ở logic tiền/số lượng;
- currency exchange rate snapshot;
- tax before/after discount;
- rounding per line và rounding tổng;
- import/UI/backend có cùng công thức không.

Tạo boundary tests với quantity lớn, số lẻ, conversion nhỏ, discount 100%, rate 0 và negative return.

### Bước 9: Reconciliation

Viết query read-only hoặc test helper để tìm:

- stock header khác tổng batch;
- stock movement trùng idempotency key;
- shipment shipped nhưng chưa post outbound;
- shipment chưa shipped nhưng đã post outbound;
- GRN received nhưng chưa inbound;
- Production output posted nhưng thiếu FG movement;
- reservation active cho chứng từ cancelled/completed;
- invoice vượt shipped quantity;
- Estimate total khác tổng line;
- foreign key logic có company_id khác nhau;
- orphan rows và duplicate business keys.

Không sửa dữ liệu; chỉ ghi count, loại bất thường và query tái kiểm tra.

## Các luồng phải audit

### 1. Authentication, tenancy và permission

- Login -> user_auth -> user/company selection.
- Global scope trong HTTP, queue và command.
- Module enablement và permission resolution.
- Superadmin/global record so với company record.

### 2. Estimate và commercial approval

- Create/update/import Estimate.
- Product pricing, custom pricing, tier, volume discount.
- BOM lines, margin, President/VP review, revision.
- Convert Estimate -> Sales Order.
- Retry conversion và thay đổi dữ liệu sau approval.

### 3. Sales fulfillment

- SO -> Sales DO -> confirm -> reserve -> ship -> deliver.
- Outbound stock mode.
- Invoice eligibility và partial shipment.
- Cancel/reverse/return/credit note.
- Document numbering và concurrent shipment.

### 4. Purchase inbound

- PO -> GRN/inbound -> received -> stock.
- Partial receive, over-receive, QC, batch/expiry.
- Bill/vendor credit/return.
- Reverse received GRN.

### 5. Warehouse

- Product stock, batch stock, reservations và movements.
- FIFO/FEFO selection.
- Absolute inventory import và adjustment.
- Negative stock policy.
- Reconciliation giữa aggregate và ledger.

### 6. Production/Biomixing

- Estimate BOM -> Production BOM.
- BOM version/default/effective date.
- Production Order -> snapshot -> release.
- UOM/yield/waste conversion.
- RM reservation/consumption.
- Batch split, FG receipt, variance approval và QC lock.
- Cancel/rework/reverse.
- Production -> Sales DO dependency.

### 7. Imports, queue và integrations

- Client, Product, Estimate, SO, Sales History, Warehouse, Inventory imports.
- Chunk retry, batch retry, row identity và partial failure.
- REST/webhook idempotency và tenant secret.
- Queue connection/worker ownership.

### 8. Finance-sensitive flows

- Invoice totals, payment, credit note.
- Purchase bill/vendor credit nếu active.
- Currency/tax/discount rounding.
- Không khẳng định accounting correctness nếu thiếu chart-of-accounts contract; ghi rõ giới hạn.

## Runtime verification tối thiểu

Trên database disposable:

1. Fresh migrate + seed + tạo superadmin.
2. Tạo hai tenant A/B.
3. Chạy happy path cho Sales, Purchase, Warehouse và Production.
4. Chạy partial quantity path.
5. Gọi cùng command hai lần.
6. Chạy hai process/request cạnh tranh nếu khả thi.
7. Chèn exception giữa transaction.
8. Reverse/cancel rồi đối chiếu ledger.
9. Chạy reconciliation queries.
10. Xác nhận tenant A không dùng reference tenant B.

Nếu không chạy được, ghi blocker cụ thể và không đánh dấu pass.

## Format finding bắt buộc

```markdown
### FLOW-P1-001: Sales DO retry có thể post outbound hai lần

- Domain:
- Severity: P0 Critical / P1 High / P2 Medium / P3 Low
- Status: Confirmed / Likely / Needs runtime verification
- Business invariant violated:
- Entry point:
- Evidence:
  - `path/file.php:line`
- State before:
- Action:
- Current result:
- Expected result:
- Impact:
- Reproduction:
- Transaction/locking analysis:
- Tenant impact:
- Reversal impact:
- Root cause:
- Recommended fix:
- Required tests:
- Reconciliation query:
- Dependencies / BA decisions:
- Confidence: High / Medium / Low
```

Severity dựa trên tác động:

- P0: mất/sai dữ liệu, sai tồn kho/tài chính, tenant leak, auth bypass hoặc core flow không chạy.
- P1: double posting, state machine sai, reverse thiếu, race condition ở luồng chính.
- P2: validation/index/test gap có workaround và chưa gây sai ledger trực tiếp.
- P3: naming, dead code, tài liệu hoặc maintainability.

## Đầu ra bắt buộc

Tạo thư mục:

```text
docs/audits/deep-business-logic-YYYY-MM-DD/
```

Gồm:

1. `00_EXECUTIVE_SUMMARY.md`
   - Hệ thống có hỏng logic tầng sâu hay không.
   - Go/No-Go cho tiếp tục phát triển và fresh install.
   - 10 rủi ro lớn nhất.

2. `01_DOMAIN_AND_OWNERSHIP_MAP.md`
   - Aggregate, owner, tables, services, jobs và ledgers.

3. `02_STATE_MACHINES.md`
   - State transition tables cho từng chứng từ.

4. `03_BUSINESS_INVARIANTS.md`
   - Công thức, constraint và kết quả kiểm chứng.

5. `04_FLOW_FINDINGS.md`
   - Tất cả finding theo format bắt buộc.

6. `05_CONCURRENCY_IDEMPOTENCY_REVERSAL.md`
   - Retry, race, compensation và fault-injection evidence.

7. `06_RECONCILIATION_RESULTS.md`
   - Query, anomaly count và giới hạn dữ liệu.

8. `07_TEST_GAP_MATRIX.md`
   - Flow x scenario: happy, partial, retry, concurrent, failure, reverse, tenant.

9. `08_REMEDIATION_ROADMAP.md`
   - `ID | Severity | Invariant | Fix | Test | Dependency | Estimate`.

10. `EVIDENCE_COMMANDS.md`
    - Command, exit code, database disposable và kết quả.

## Quy tắc kết luận

- Phải phân biệt logic code sai với test fixture sai.
- Có transaction không đồng nghĩa atomic nếu observer/job/external call nằm ngoài.
- Có idempotency key không đồng nghĩa an toàn nếu DB không unique hoặc key không bao phủ tenant/action.
- Có global scope không đồng nghĩa queue/command được tenant-scope.
- Happy path pass không chứng minh retry/reverse/concurrency pass.
- Không tuyên bố toàn hệ thống đúng nếu chưa kiểm tra invariant và reconciliation.
- Kết thúc bằng danh sách câu hỏi BA/CTO cần quyết định.

## Lệnh bắt đầu đề xuất

```text
Hãy thực hiện audit theo docs/DEEP_BUSINESS_LOGIC_AUDIT_PROMPT.md.
Chỉ audit source và local disposable database, không dùng staging/hub.
Ưu tiên theo thứ tự:
1. Warehouse ledger và stock conservation.
2. Production/Biomixing state machine, UOM, yield, waste và reversal.
3. Sales DO -> shipment -> invoice -> return.
4. PO -> GRN -> stock -> bill/vendor credit.
5. Estimate -> approval -> SO và import retry.
Không sửa code trước khi hoàn thành 08_REMEDIATION_ROADMAP.md.
```
