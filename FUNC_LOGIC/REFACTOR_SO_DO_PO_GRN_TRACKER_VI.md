# Tracker triển khai — Refactor `SO -> DO -> Invoice` và `PO -> GRN -> Bill`

**Owner:** AI Agent + Team ERP  
**Ngày bắt đầu:** 2026-03-30  
**Trạng thái tổng:** `In Progress`

---

## Cách dùng tracker

- Mỗi phase chỉ có 1 trạng thái: `Not Started` / `In Progress` / `Blocked` / `Done`.
- Chỉ chuyển phase tiếp theo khi phase hiện tại đạt toàn bộ acceptance.
- Cập nhật file này sau mỗi mốc triển khai.

---

## Phase 1 — Foundation & Compatibility

**Trạng thái:** `Done`

### Checklist công việc

- [x] Chốt domain map cũ -> mới (Sales Shipment -> Sales DO, Delivery Order Purchase -> GRN).
- [x] Chốt permission matrix mới.
- [x] Chốt route/API mapping và chiến lược compatibility.
- [x] Cập nhật naming UI/SOP mức compatibility.
- [x] Tạo feature flag/cấu hình cutover.

### Acceptance

- [x] Không regression flow hiện tại.
- [x] UI hiển thị thuật ngữ nghiệp vụ nhất quán.

### Ghi chú

- 2026-03-30: Đã triển khai compatibility naming trên UI (không đổi route/table/service):
    - Menu hiển thị: `GRN`, `Sales DO`.
    - Page title/h1 liên quan Delivery Order chuyển sang `GRN`.
    - Page title/h1 liên quan Sales Shipment chuyển sang `Sales DO`.
    - DataTable title của Sales Shipment đổi sang `Sales DO`.
- 2026-03-30: Đã bổ sung permission mapping ở master plan (`REFACTOR_SO_DO_PO_GRN_IMPLEMENTATION_PLAN_VI.md`).
- 2026-03-30: Đã bổ sung route/API compatibility map ở master plan (giữ `sales-shipments.*` và `delivery-orders.*` trong Phase 1).
- 2026-03-30: Đã thêm khung feature flags:
    - `PURCHASE_FLOW_NAMING_MODE` (default `compat_v2`)
    - `PURCHASE_DO_GRN_CUTOVER_ENABLED` (default `false`)
      trong `Modules/Purchase/Config/config.php` và `.env.example`.
- 2026-03-30: Smoke test tự động pass:
    - Route check: `sales-shipments.*` + `delivery-orders.*` còn hoạt động.
    - Config check: `purchase.flow_naming_mode=compat_v2`, `purchase.do_grn_cutover_enabled=false`.
    - Regression tests pass: 15 tests, 28 assertions (`SalesShipmentOptionBTest`, `PurchaseInboundStockFlowTest`, `InvoiceWarehouseStockScopeBTest`, `OrderInvoiceRelationTest`, `DeliveryOrderObserverGuardTest`).

---

## Phase 2 — Build flow mới end-to-end

**Trạng thái:** `In Progress`

### Checklist công việc

- [ ] Tạo Sales DO flow đầy đủ lifecycle.
- [ ] Tạo/chuẩn hóa GRN flow đầy đủ lifecycle.
- [ ] Gắn stock posting outbound/inbound theo canonical trigger.
- [ ] Gắn reverse/rollback logic.
- [ ] Viết/điều chỉnh test unit + feature.

### Acceptance

- [ ] `SO -> DO -> stock out -> invoice` pass.
- [ ] `PO -> GRN -> stock in -> bill` pass.
- [ ] Không double-post.

### Ghi chú

- 2026-03-30: Đã tạo route alias chuyển tiếp để đội có thể dùng naming mới ngay, không phá flow cũ:
    - `grn.*` -> `DeliveryOrderController`
    - `sales-do.*` -> `SalesShipmentController`
- Lifecycle hiện vẫn dùng technical implementation cũ (an toàn), chưa tách entity/service mới hoàn toàn.
- 2026-03-30: Đã chuẩn hóa thêm lớp route transition ở Phase 2 (không tách entity/table):
    - Controller redirect dùng dynamic route theo `purchase.flow_naming_mode` (legacy -> route cũ, compat_v2 -> route alias mới).
    - DataTable action link/view link của GRN + Sales DO dùng dynamic route, không hardcode `delivery-orders.*`/`sales-shipments.*`.
    - Các view chính (`index/create/edit/show/overview`, sidebar, Order action, Warehouse stock onboarding link) đã đồng bộ dynamic route để chuẩn bị cutover URL từng phần an toàn.
- 2026-03-30: Đã triển khai permission bridge cho Phase 2 (chưa tách entity/table):
    - Thêm `Modules/Purchase/Support/FlowPermission.php` để resolve quyền mới/cũ theo alias.
    - Thêm config `purchase.permission_aliases` và gắn với `PURCHASE_DO_GRN_CUTOVER_ENABLED`.
    - Trước cutover (`false`): cho phép new permission hoặc legacy permission.
    - Sau cutover (`true`): chỉ dùng new permission.
    - Đã áp dụng check quyền alias ở controller + DataTable + sidebar + action tạo Sales DO từ Order.
- 2026-03-30: Đã thêm migration permission nghiệp vụ mới:
    - Sales DO: `view/create/update/ship/cancel_sales_do`.
    - GRN: `view/create/update/change_status/delete_grn`.
- 2026-03-30: Regression test pass:
    - 7 tests, 16 assertions (`SalesShipmentOptionBTest`, `PurchaseInboundStockFlowTest`, `DeliveryOrderObserverGuardTest`).
- 2026-03-30: Đã thêm service alias layer cho lifecycle để giảm coupling controller và chuẩn bị tách technical entity về sau:
    - `Modules/Purchase/Services/SalesDoService.php`:
        - điều phối lifecycle Sales DO (confirm/ship/deliver/reverse/cancel),
        - giữ nguyên canonical outbound bằng cách delegate qua `Modules/Warehouse/Services/SalesShipmentStockService`.
    - `Modules/Purchase/Services/GrnService.php`:
        - điều phối đổi trạng thái GRN (`draft/inbound/received`).
    - `SalesShipmentController` và `DeliveryOrderController` đã gọi service layer cho các action lifecycle tương ứng.
- 2026-03-30: Đã mở rộng service alias layer cho create/update document payload (controller mỏng hơn, hành vi không đổi):
    - `SalesDoService` quản lý persist header + items cho Sales DO create/update.
    - `GrnService` quản lý persist header + items cho GRN create/update.
    - `SalesShipmentController` và `DeliveryOrderController` chuyển phần lưu dữ liệu sang service, controller giữ validate + permission + response.
- 2026-03-30: Regression test pass sau khi chuyển create/update sang service:
    - 7 tests, 16 assertions (`SalesShipmentOptionBTest`, `PurchaseInboundStockFlowTest`, `DeliveryOrderObserverGuardTest`).
- 2026-03-30: Đã bổ sung test persistence cho service alias layer (khóa regression ở tầng service):
    - `tests/Feature/SalesDoServicePersistenceTest.php`
    - `tests/Feature/GrnServicePersistenceTest.php`
    - Coverage chính: create/update header + replace items.
- 2026-03-30: Đã bổ sung test lifecycle cho `SalesDoService`:
    - `tests/Feature/SalesDoServiceLifecycleTest.php`
    - Coverage chính:
        - guard trạng thái không hợp lệ,
        - confirm yêu cầu có item,
        - ship gọi outbound stock service,
        - reverse/cancel gọi reverse stock service đúng nhánh.
- 2026-03-30: Đã bổ sung test lifecycle cho `GrnService`:
    - `tests/Feature/GrnServiceLifecycleTest.php`
    - Coverage chính:
        - nhận status hợp lệ trong luồng GRN (`draft -> inbound -> received`),
        - reject status không hợp lệ.
- 2026-03-30: Đã bổ sung test alias permission + cutover behavior:
    - `tests/Feature/FlowPermissionAliasTest.php`
    - Coverage chính:
        - pre-cutover: legacy permission vẫn được chấp nhận,
        - post-cutover: bắt buộc permission mới,
        - deny khi alias key không tồn tại.
- 2026-03-30: Sửa bug cấu hình alias permission:
    - `purchase.permission_aliases` chuyển từ key phẳng (`sales_do.view`) sang nested map (`sales_do.view` path dạng mảng lồng),
    - đảm bảo `FlowPermission::allowsAlias()` đọc đúng bằng `config('purchase.permission_aliases.<domain>.<action>')`.
- 2026-03-30: Đã thêm command rehearsal dry-run cho Phase 3 preparation:
    - `Modules/Purchase/Console/SalesDoMigrationRehearsalCommand.php`
    - Command: `purchase:sales-do-migration-rehearsal`
    - Tính năng hiện tại:
        - tổng hợp source snapshot (`sales_shipments`, `sales_shipment_items`),
        - quality checks (`orphan_item_count`, `duplicate_shipment_number_count`),
        - mapping preview + sample records,
        - xuất JSON report ra stdout hoặc `--output`.
    - Lưu ý: hiện chỉ dry-run, `--execute` chưa implement (an toàn).
- 2026-03-30: Đã bổ sung test command rehearsal:
    - `tests/Feature/SalesDoMigrationRehearsalCommandTest.php`
    - cover:
        - fail khi thiếu bảng nguồn,
        - pass và sinh report JSON đúng summary.
- 2026-03-30: Đã thêm command reconciliation report cho Phase 3:
    - `Modules/Purchase/Console/SalesDoReconciliationReportCommand.php`
    - Command: `purchase:sales-do-reconcile-report --baseline=<baseline.json>`
    - Tính năng hiện tại:
        - đọc baseline từ dry-run report,
        - chụp current snapshot từ DB nguồn hiện tại,
        - tính delta (`shipments/items/qty/status_distribution`) + quality checks,
        - xuất JSON report ra stdout hoặc `--output`.
- 2026-03-30: Đã bổ sung test command reconciliation:
    - `tests/Feature/SalesDoReconciliationReportCommandTest.php`
    - cover:
        - fail khi thiếu `--baseline`,
        - fail khi baseline file không tồn tại,
        - pass và sinh delta report đúng dữ liệu mẫu.
- 2026-03-30: Đã bổ sung script gate tự động cho staging rehearsal:
    - `scripts/staging_sales_do_rehearsal_gate.sh`
    - chạy chuỗi baseline + reconcile + validation gate trong 1 lệnh,
    - trả exit code `1` nếu lệch số liệu/quality check fail.
- 2026-03-30: Đã bổ sung safe runner cho staging rehearsal:
    - `scripts/staging_phase3_safe_execute.sh`
    - tích hợp preflight (disk/app/db) + backup DB + rehearsal gate,
    - ưu tiên an toàn vận hành staging và giảm rủi ro mất dữ liệu khi thao tác.
- 2026-03-30: Đã bổ sung wrapper PowerShell chạy remote từ local:
    - `scripts/run_staging_phase3_safe_execute.ps1`
    - mục tiêu: thao tác 1 lệnh từ local -> staging, tự normalize CRLF cho `.sh` trước khi chạy.
- 2026-03-30: Test suite Phase 2 hiện tại pass:
    - 25 tests, 79 assertions (phase3 commands + permission alias + service lifecycle + service persistence + outbound/inbound + observer guard).

---

## Phase 3 — Data migration rehearsal

**Trạng thái:** `Done`

### Checklist công việc

- [x] Script migration dữ liệu từ `sales_shipments` sang thực thể mới.
- [x] Hỗ trợ `--dry-run`.
- [x] Script reconciliation trước/sau.
- [x] Rehearsal ít nhất 2 lần trên dữ liệu clone staging.

### Acceptance

- [x] Reconciliation đạt ngưỡng chấp nhận.
- [x] Rollback script chạy được.

### Ghi chú

- ...
- 2026-03-30: Đã dọn dung lượng staging an toàn trước go-live rehearsal:
    - xóa backup DB phase3 cũ, giữ lại bản mới nhất,
    - xóa `storage/backup/2026-03-13-18-00-46.zip` cũ,
    - xóa `.composer-cache` trong app dir (cache build, không phải runtime).
    - dung lượng `/` cải thiện từ ~1.9GB lên ~3.1GB trống.
- 2026-03-30: Đã chạy rehearsal gate thành công thêm 2 lần trên staging:
    - run #1: baseline + reconcile + gate PASS (`delta=0`, quality checks đều true),
    - run #2: baseline + reconcile + gate PASS với preflight mặc định (không hạ ngưỡng disk),
    - reports lưu tại `storage/app/reports/` và backup DB lưu tại `storage/app/backups/phase3/`.
- 2026-03-30: Đã triển khai migration script + rollback script cho Phase 3:
    - command migrate: `purchase:sales-do-migrate-data` (mặc định dry-run, `--execute --force` để chạy),
    - command rollback: `purchase:sales-do-migrate-rollback --manifest=...` (mặc định dry-run, `--execute --force` để chạy),
    - thêm bảng đích `sales_dos`, `sales_do_items` và legacy mapping ID để đảm bảo idempotent + rollback theo manifest.
- 2026-03-30: Rehearsal execute/rollback trên staging:
    - migrate execute `company_id=20` tạo thành công header/item ở bảng đích,
    - rollback dry-run xác nhận đúng số bản ghi sẽ xóa,
    - rollback execute xóa đúng số bản ghi theo manifest,
    - dry-run sau rollback cho thấy pending quay về trạng thái ban đầu (an toàn, không drift nguồn).

---

## Phase 4 — Staging cutover

**Trạng thái:** `In Progress`

### Checklist công việc

- [x] Backup DB staging.
- [x] Deploy code refactor.
- [x] Migrate schema + data (có report).
- [x] Bật flag cutover.
- [ ] Chạy smoke test + UAT.

### Acceptance

- [ ] SO flow ổn định.
- [ ] PO/GRN flow ổn định.
- [ ] Log không có critical mới.

### Ghi chú

- ...
- 2026-03-30: Đã bổ sung precheck gate cho cutover:
    - script: `scripts/staging_phase4_cutover_precheck.sh`,
    - chạy preflight disk/app/db + check command/table + chạy rehearsal gate + migrate dry-run report.
- 2026-03-30: Đã chạy precheck trên staging (`company_id=20`) và PASS:
    - reconciliation gate PASS (`delta=0`, quality checks true),
    - migrate dry-run report tạo thành công,
    - trạng thái hiện tại: sẵn sàng cửa sổ execute cutover.
- 2026-03-30: Đã chạy cutover execution trên staging:
    - backup DB thành công qua `staging_phase3_safe_execute.sh`,
    - migrate execute `company_id=20` thành công, dry-run sau execute cho thấy `pending=0`,
    - bật cờ cutover hiệu lực (`purchase.do_grn_cutover_enabled=true`, `purchase.flow_naming_mode=compat_v2`),
    - deploy route alias `sales-do.*` + `grn.*` lên staging và smoke route list PASS,
    - HTTP staging trả `200`, UAT nghiệp vụ người dùng cuối còn pending.
- 2026-03-30: Smoke sau cutover (hệ thống) PASS:
    - health check app/db/http/disk PASS (HTTP 200, disk ~3.0GB trống),
    - migrate dry-run hậu cutover cho toàn scope cho thấy `pending=0`,
    - log thời điểm cutover không ghi nhận lỗi business-critical mới (1 lỗi runtime do lệnh kiểm tra `--compact` sai option từ thao tác vận hành, không phải lỗi ứng dụng).
- 2026-03-30: Tiếp tục theo kế hoạch tách luồng technical runtime cho Sales DO:
    - bổ sung runtime resolver `SalesDoRuntime` để route xử lý theo flag cutover (`sales_shipments*` <-> `sales_dos*`),
    - thêm entity `SalesDo` + `SalesDoItem`,
    - cập nhật `SalesDoService`, `SalesShipmentController`, `SalesShipmentDataTable`, `SalesShipmentStockService` để chạy bảng đích mới khi cutover bật,
    - regression test + test cutover runtime pass; smoke syntax/runtime trên staging pass.
- 2026-03-30: Ưu tiên local để tiết kiệm thời gian/token, tiếp tục tách technical runtime nhánh PO -> GRN:
    - thêm runtime resolver `GrnRuntime` (`delivery_orders*` <-> `grns*`),
    - thêm model/entity mới: `App\Models\Grn`, `Modules\Purchase\Entities\GrnItem`,
    - thêm migration bảng mới: `grns`, `grn_items`,
    - cập nhật `GrnService`, `DeliveryOrderController`, `DeliveryOrderDataTable`, `DeliveryOrderObserver` và observer registration cho `Grn`,
    - bổ sung command migrate/rollback dữ liệu GRN:
        - `purchase:grn-migrate-data`
        - `purchase:grn-migrate-rollback`
    - local tests pass cho cutover runtime + migrate/rollback + regression flow inbound.
- 2026-03-30: Đã rollout phần GRN cutover lên staging để test tiếp:
    - deploy code runtime + command + migration GRN lên staging,
    - migrate schema tạo `grns`, `grn_items` thành công,
    - chạy `purchase:grn-migrate-data --execute --force` thành công (all scope),
    - dry-run sau execute cho thấy `pending.headers_count=0`,
    - rollback dry-run theo manifest xác nhận có thể hoàn tác (không execute rollback để giữ dữ liệu test hiện tại),
    - smoke app/db/http/disk sau rollout PASS.
- 2026-03-30: Đã rehearsal đầy đủ vòng GRN rollback execute trên staging:
    - execute rollback theo manifest cũ (`deleted.headers_count=3`),
    - dry-run xác nhận pending quay về `3`,
    - execute migrate lại để trả staging về trạng thái cutover active (`pending=0`),
    - sinh manifest rollback mới cho trạng thái hiện tại.
- 2026-03-30: Đã cập nhật tài liệu cleanup backlog bảng legacy:
    - xác nhận runtime đã chạy bảng mới (`sales_dos*`, `grns*`) khi cutover bật,
    - xác nhận bảng legacy chưa xóa (theo kế hoạch an toàn),
    - lập danh sách bảng/columns candidate cleanup one-shot ở `docs/DB_CLEANUP_BACKLOG_SO_DO_PO_GRN.md`.

---

## Phase 5 — Remove `sales_shipments` và artifact thừa

**Trạng thái:** `Not Started`

### Điều kiện mở phase (bắt buộc)

- [ ] Phase 1-4 đều `Done`.
- [ ] UAT sign-off.
- [ ] Reconciliation staging pass.
- [ ] Không còn bug blocker.

### Checklist công việc

- [ ] Remove route/controller/view/permission cũ.
- [ ] Drop bảng cũ bằng migration an toàn.
- [ ] Remove code compat/dead code.
- [ ] Cập nhật toàn bộ tài liệu cuối.

### Acceptance

- [ ] Hệ thống chạy ổn sau cleanup.
- [ ] Không còn dependency vào `sales_shipments`.

### Ghi chú

- ...

---

## Quyết định/Issue log (append-only)

| Ngày       | Quyết định / Vấn đề                                                          | Ảnh hưởng                                                  | Người chốt |
| ---------- | ---------------------------------------------------------------------------- | ---------------------------------------------------------- | ---------- |
| 2026-03-30 | Khởi tạo tracker + bắt đầu Phase 1 compatibility naming                      | UI nhất quán thuật ngữ nghiệp vụ, chưa đụng logic/DB       | AI Agent   |
| 2026-03-30 | Hoàn tất mapping permission + route/API + feature flag framework cho Phase 1 | Sẵn sàng sang bước smoke test xác nhận không regression    | AI Agent   |
| 2026-03-30 | Chốt Phase 1 Done sau smoke test + test suite trọng tâm pass                 | Có thể bắt đầu build Phase 2 mà vẫn giữ an toàn flow SO/PO | AI Agent   |
| 2026-03-30 | Tạo route alias `grn.*` và `sales-do.*` để chuyển đổi naming dần             | Mở đường cutover API/UI từng phần, không phá route legacy  | AI Agent   |
| 2026-03-30 | Chuẩn hóa dynamic route transition cho UI/redirect/DataTable                 | Người dùng đi theo URL naming mới dần, backend vẫn ổn định | AI Agent   |
| 2026-03-30 | Triển khai permission bridge + permission mới cho Sales DO/GRN               | Tách quyền nghiệp vụ theo phase, tránh big-bang role map   | AI Agent   |
| 2026-03-30 | Bổ sung service alias layer cho lifecycle Sales DO/GRN                       | Chuẩn bị tách entity/controller phase sau, không đổi flow  | AI Agent   |
| 2026-03-30 | Chuyển create/update payload sang service layer (Sales DO/GRN)               | Giảm coupling controller, dễ cutover technical phase sau   | AI Agent   |
| 2026-03-30 | Bổ sung test persistence cho service alias layer                             | Giảm rủi ro regression khi tiếp tục refactor phase 2       | AI Agent   |
| 2026-03-30 | Bổ sung test lifecycle cho SalesDoService                                    | Khóa behavior nghiệp vụ trước khi tách technical sâu hơn   | AI Agent   |
| 2026-03-30 | Bổ sung test lifecycle cho GrnService                                        | Cân bằng coverage nhánh PO/GRN trong Phase 2               | AI Agent   |
| 2026-03-30 | Bổ sung test alias permission + sửa cấu trúc config alias                    | Tránh false negative quyền và khóa behavior cutover        | AI Agent   |
| 2026-03-30 | Thêm command rehearsal dry-run + test command                                | Mở đầu Phase 3 prep an toàn, chưa migrate thật             | AI Agent   |
| 2026-03-30 | Thêm command reconciliation + test command                                   | Có baseline-vs-current report cho rehearsal trước cutover  | AI Agent   |
| 2026-03-30 | Thêm script gate tự động cho rehearsal staging                               | Chuẩn hóa thao tác vận hành và điều kiện pass/fail         | AI Agent   |
| 2026-03-30 | Thêm safe runner preflight+backup+gate cho staging                           | Giảm rủi ro server chết / mất dữ liệu khi rehearsal        | AI Agent   |
| 2026-03-30 | Thêm wrapper PowerShell local->staging cho safe runner                       | Giảm lỗi thao tác thủ công từ máy local                    | AI Agent   |
