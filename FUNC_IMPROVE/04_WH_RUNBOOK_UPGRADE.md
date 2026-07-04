# Warehouse — Runbook vận hành & kế hoạch nâng cấp (WUP)

## Trạng thái rà soát (2026-04-30)

- Đã triển khai: `WUP-01` -> `WUP-07`.
- `WUP-09` master bin/location UI: **Not required for current multi-warehouse scope**. Hệ thống hiện chỉ cần quản lý tồn theo kho; bin/location chi tiết giữ làm P2 optional nếu sau này cần kệ/ngăn/vị trí vật lý.
- `WUP-08` (báo cáo vận hành theo kho): Done dev/browser — có tab báo cáo movement/reference trong Purchase Reports, dùng `stock_movements`, filter theo kho/loại/ngày/chứng từ, export DataTable, automated readiness test và browser smoke export pass.
- `WUP-10` (chống invoice trùng SO/DO): Done — có schema/runtime readiness, guard code, unit test và feature UAT cho luồng invoice từ Sales DO.
- Khuyến nghị: giữ file này làm **nguồn trạng thái chính** cho nhóm Warehouse/Purchase improve.

### Recheck 2026-05-09 (Specification Reconciliation)

- Xác nhận lại: `WUP-01` -> `WUP-07` vẫn có bằng chứng code/test trong `Modules/Warehouse/*` và `tests/Feature/*`.
- `WUP-08` đã có báo cáo movement/reference ở Purchase Reports; automated readiness test đã khóa filter/export/reference mapping; browser smoke 2026-06-16 đã xác nhận UI và export Excel. Phần còn lại nếu cần là business sign-off/stock valuation theo kỳ. `WUP-09` đã có movement-ledger readiness cho location id, nhưng master bin/location UI/table riêng được đánh dấu **Not required for current multi-warehouse scope** và giữ P2 optional. `WUP-10` đã đổi sang Done sau khi có feature UAT evidence.
- Quy tắc cập nhật: chỉ đánh dấu Done khi có đủ cả 3 lớp **route/service(or migration)/test hoặc UAT evidence**.

### Recheck 2026-06-13 (theo kế hoạch sau graphify)

- `WUP-08` re-run automated readiness: `php artisan test --compact tests/Feature/PurchaseModuleRoutesTest.php` -> **3 passed / 45 assertions**.
- Kết luận tại thời điểm 2026-06-13: WUP-08 vẫn **Ready for UAT** ở mức dev; trạng thái này đã được cập nhật lại trong recheck 2026-06-16 sau browser/export smoke.
- `P0-07/WUP` re-run automated readiness 2026-06-16: `php artisan test --compact tests\Feature\PurchaseModuleRoutesTest.php tests\Feature\WarehouseAvailabilityApiTest.php tests\Feature\WarehouseUpgradeP0Test.php tests\Feature\SalesDoServiceLifecycleTest.php tests\Feature\WarehouseInboundMutualExclusionValidationTest.php tests\Feature\WarehouseUnitConversionFlowTest.php` -> **28 passed / 113 assertions**.

### Recheck 2026-06-16 (theo thứ tự cleanup kế hoạch)

- `WUP-08` route/view/datatable readiness re-run: `php artisan test --compact tests\Feature\PurchaseModuleRoutesTest.php` -> **3 passed / 45 assertions**.
- Browser smoke sau khi Apache bật lại: login `admin@example.com`, mở `https://craveva-staging.test/account/reports?tab=warehouse-movement-report`, tab report render `warehouse-movements-table`, đủ filter `warehouse/type/reference type/reference id/search`, console error = 0.
- Browser export smoke: `Columns` mở đúng danh sách cột; `Export` tải file `.playwright-mcp/warehouse-movements-2026-06-16-16-24-10.xlsx` thành công.
- Kết luận: đổi `WUP-08` sang **Done dev/browser**; phần còn lại chỉ là business sign-off của kế toán/vận hành hoặc mở rộng valuation theo kỳ nếu nghiệp vụ yêu cầu.
- `WUP-09` location readiness: `StockMovementService` đã propagate `warehouse_location_id` / `warehouse_location_from_id` / `warehouse_location_to_id` vào `stock_movements.warehouse_location_from_id/to_id` khi cột tồn tại; test `tests\Feature\WarehouseUpgradeP0Test.php` -> **9 passed / 22 assertions**.
- Kết luận `WUP-09`: **Not required for current multi-warehouse scope**. Hiện chỉ cần quản lý tồn theo kho; master bin/location UI/table riêng giữ làm P2 optional nếu sau này vận hành cần quản lý vị trí vật lý. Ledger vẫn đã sẵn sàng giữ location id từ các flow nhập/xuất/chuyển khi schema có cột.

**Ngày gộp:** 2026-04-06  
**Nguồn:** `WAREHOUSE_OPERATION_RUNBOOK.md` + `WAREHOUSE_UPGRADE_PLANE.MD` (tên cũ _PLANE_ đã đổi nội dung vào file này).

---

## Mục lục

1. [Runbook vận hành local / nghiệp vụ](#1-runbook-vận-hành-local--nghiệp-vụ)
2. [Kế hoạch nâng cấp Warehouse (WUP) & trạng thái](#2-kế-hoạch-nâng-cấp-warehouse-wup--trạng-thái)

---

# 1) Runbook vận hành local / nghiệp vụ

## 1) Mục tiêu

Tài liệu này là quy trình vận hành Warehouse theo luồng mới (WUP-01..WUP-07 nền), ưu tiên chạy local để test UI và nghiệp vụ trước khi đồng bộ môi trường khác.

## 2) Cấu hình local khuyến nghị

**Bảng đầy đủ biến `.env` (kho + Purchase / webhook AI):** [`../docs/WAREHOUSE_PURCHASE_ENV_REFERENCE.md`](../docs/WAREHOUSE_PURCHASE_ENV_REFERENCE.md).

Trong `.env`:

- `WAREHOUSE_SALES_OUTBOUND_ENABLED=true`
- `WAREHOUSE_SALES_OUTBOUND_MODE=shipment`
- `WAREHOUSE_INBOUND_FROM_PO_DELIVERED=true`
- `WAREHOUSE_INBOUND_FROM_DO_RECEIVED=false`
- `WAREHOUSE_ALLOW_NEGATIVE_STOCK=false`
- `WAREHOUSE_STRICT_UNIT_CONVERSION=false` (bật `true` khi đã map đầy đủ conversion)

Sau khi đổi config:

- `php artisan config:clear`

## 3) Chu kỳ nghiệp vụ cần test trên UI

### 3.1 Kho và loại kho

1. Tạo/Update warehouse với `warehouse_type`:
    - `normal`: bán được
    - `locked`, `scrap`, `transit`: không được reserve/outbound bán hàng
2. Đảm bảo kho bán hàng mặc định của client map vào kho `normal`.

### 3.2 Luồng bán hàng (reserve -> outbound -> release)

1. Tạo Sales DO từ Order.
2. `Confirm`:
    - hệ thống reserve tồn.
3. `Ship`:
    - hệ thống trừ tồn outbound.
    - reservation chuyển sang consumed.
    - Với sản phẩm có batch khả dụng, dòng Sales DO phải có `warehouse_batch_id`; backend tự kiểm tra batch đúng company/kho/sản phẩm và tự ghi batch number/HSD từ batch master.
4. `Cancel`:
    - nếu đã outbound thì reverse outbound.
    - reservation active sẽ release.

### 3.3 Luồng inbound canonical

Chỉ được chọn 1 trong 2:

- PO delivered inbound
- DO received inbound

Nếu bật đồng thời cả 2, hệ thống sẽ guard và báo lỗi conflict để tránh double-count.

## 4) Unit conversion (WUP-06 nền)

### 4.1 Nguyên tắc

- Tất cả reserve/deduct/inbound sẽ convert về base unit của product trước khi xử lý tồn.
- Mapping conversion nằm trong bảng `product_unit_conversions`:
    - `product_id`
    - `unit_id` (đơn vị đầu vào)
    - `factor_to_base` (hệ số nhân về base)

### 4.2 Ví dụ

- Base unit của SKU = `Pcs`
- Đơn vị bán = `Box`, `factor_to_base = 10`
- Nhập 2 Box -> hệ thống xử lý tồn là 20 Pcs.

## 5) Idempotent + reconciliation tối thiểu (WUP-07 nền)

### 5.1 Idempotent stock movement

- `stock_movements.idempotency_key` được dùng để chặn duplicate posting từ cùng sự kiện.
- Các luồng invoice/shipment/inbound mới sẽ truyền key này.

### 5.2 Reconciliation report

Chạy command local:

- `php artisan warehouse:reconciliation-report --date=YYYY-MM-DD`
- `php artisan warehouse:reconciliation-report --date=YYYY-MM-DD --company_id=1`

Kết quả:

- File JSON: `storage/app/warehouse-reconciliation/warehouse-reconciliation-<date>-*.json`
- Bản ghi DB: `warehouse_sync_reconciliation_logs`

## 6) Checklist UAT nhanh

1. Kho `locked/scrap` không ship được.
2. 2 DO reserve gần đồng thời không oversell.
3. Bật sai cờ PO + DO inbound -> bị guard conflict.
4. Cancel DO release reservation đúng.
5. API availability trả đúng:
    - `GET /api/v1/warehouse/availability?company_id=...&product_id=...` (query; `warehouse_ids[]` tùy chọn)
    - Route nằm trong nhóm **`auth:sanctum`** — gọi từ Postman/cần `Authorization: Bearer {token}` (token Sanctum của user/app)
6. Inbound AI tạo đơn (**`POST /api/integrations/orders`**): khi company có module **warehouse** trong package và không tắt kiểm tra, hệ thống chặn tạo Order nếu **tổng sellable (base)** không đủ (HTTP 422 + message). Tắt tạm: `check_stock: false` hoặc env `WAREHOUSE_AI_ORDER_WEBHOOK_CHECK_STOCK=false`.

## 7) Vận hành sự cố

- Lỗi `Inbound configuration conflict`: tắt 1 trong 2 cờ inbound PO/DO.
- Lỗi `Missing unit conversion mapping`: thêm mapping `product_unit_conversions` hoặc tạm thời tắt strict mode.
- Số liệu outbound bị lặp: chạy reconciliation report và đối soát duplicate group theo `reference_type/reference_id`.

## 8) Phạm vi local-first

- Ưu tiên xác nhận nghiệp vụ và UI trên local trước.
- Không phụ thuộc hub/staging trong giai đoạn này.

---

# 2) Kế hoạch nâng cấp Warehouse (WUP) & trạng thái

**Nguồn đầu vào (đã retire):** PM warehouse specs — thay bằng `FUNC_LOGIC/WAREHOUSE_MASTER_GUIDE.md`, `PROJECT MAOLIN/MAOLIN_BUSINESS.md` (xem `FUNC_IMPROVE/LEGACY_ARCHIVE.md`).
**Đã loại bỏ:** các nhận định lỗi thời (ví dụ “không có transfer”, “không có batch/HSD”, …)
**Mục tiêu:** chỉ giữ các hạng mục cần nâng cấp thực sự.

---

## 1) Hiện trạng xác nhận (giữ ngắn gọn)

- Hệ thống đã có nền đa kho: master kho, tồn theo kho, batch/HSD, movement, transfer.
- Hệ thống đã có service reservation, nhưng cần chuẩn hóa cách gắn với luồng bán.
- Cấu hình inbound/outbound đã có cờ env; cần khóa rõ quy trình để tránh trừ/nhập đôi.

---

## 2) Danh sách nâng cấp cần làm (đã lọc)

| Mã     | Hạng mục nâng cấp                  | Vấn đề hiện tại                                                          | Kết quả cần đạt                                                                                        |
| ------ | ---------------------------------- | ------------------------------------------------------------------------ | ------------------------------------------------------------------------------------------------------ |
| WUP-01 | Phân loại kho + rule nghiệp vụ     | Chưa có `warehouse_type` + rule chặn xuất kho không hợp lệ               | Có loại kho (`normal`, `locked`, `scrap`, `transit`...) và rule thực thi khi đặt hàng/xuất kho/chuyển  |
| WUP-02 | Chuẩn hóa sellable/available       | Chưa có định nghĩa dùng chung toàn hệ thống                              | Có công thức và API thống nhất `sellable` để web/AI/đơn hàng dùng chung                                |
| WUP-03 | Chuẩn hóa reserve -> outbound      | Reservation có service nhưng chưa chốt quy trình business nhất quán      | Có flow chuẩn: tạo đơn giữ chỗ, shipped/invoice trừ tồn, cancel/revert trả tồn                         |
| WUP-04 | Khóa cấu hình inbound/outbound     | Dễ lỗi vận hành (PO + DO cùng nhập; invoice + shipment cùng xuất)        | Có policy cấu hình “một nguồn nhập / một nguồn xuất” theo môi trường + checklist bật cờ                |
| WUP-05 | API kiểm tra còn hàng cho AI/Line  | Chưa có endpoint chuẩn cho nghiệp vụ YES/NO                              | Có endpoint/service trả về availability theo kho + quy đổi đơn vị                                      |
| WUP-06 | Quy đổi đơn vị bán hàng            | Chưa chốt một chuẩn conversion xuyên suốt                                | Có base unit chuẩn; trừ kho luôn qua quantity đã convert                                               |
| WUP-07 | Đồng bộ ERP hằng ngày              | Import có nhưng chưa đóng gói chuẩn idempotent + đối soát                | Có job sync ổn định, idempotent, log lỗi và báo cáo chênh lệch                                         |
| WUP-08 | Báo cáo vận hành theo kho          | UI/báo cáo có thể chưa đủ góc nhìn đối soát                              | Có báo cáo tồn theo kho + movement theo reference + export phục vụ kế toán/vận hành                    |
| WUP-09 | Bin/location (giai đoạn 2)         | Không bắt buộc trong scope đa kho hiện tại; ledger đã có readiness location id | P2 optional nếu sau này cần quản lý kệ/ngăn/vị trí vật lý                                             |
| WUP-10 | Chống tạo invoice trùng theo SO/DO | Hiện có thể tạo nhiều invoice cho cùng `order_id`/DO dẫn tới double bill | Có guard nghiệp vụ: chặn hoặc cảnh báo mạnh khi tổng qty/amount invoice vượt phần đã giao/chưa invoice |

### 2.1 Bảng Evidence WUP (P0-07 — ba lớp: code / test / UAT)

Dùng bảng này khi đánh dấu `Done`/`Partial` theo quy tắc `FUNC_IMPROVE/P0_BIOMIXING_NEXT_STEPS.md`. Cột **UAT** điền tham chiếu biên bản hoặc mục checklist §1 runbook / báo cáo mini-UAT.

| Mã     | Trạng thái tổng quát (Apr 2026) | Evidence (code — thư mục / service chính)                                                                | Evidence (test — file gợi ý)                                                      | UAT / Runbook                                  |
| ------ | ------------------------------- | -------------------------------------------------------------------------------------------------------- | --------------------------------------------------------------------------------- | ---------------------------------------------- |
| WUP-01 | Done                            | `Modules/Warehouse` `warehouse_type`, flow policy outbound                                               | `tests/Feature/WarehouseUpgradeP0Test.php`                                        | Dev/QA 2026-06-16 Pass; §1 runbook 3.1 kho `normal` vs locked |
| WUP-02 | Done                            | `WarehouseAvailabilityService`, `Modules/Warehouse/Routes/api.php`                                       | `tests/Feature/WarehouseAvailabilityApiTest.php`                                  | Dev/QA 2026-06-16 Pass; API §1.6 + checklist §6 |
| WUP-03 | Done                            | Sales DO lifecycle + `StockReservationService`                                                           | `tests/Feature/SalesDoServiceLifecycleTest.php`                                   | Dev/QA 2026-06-16 Pass; §1.3 reserve → ship → cancel |
| WUP-04 | Done                            | Inbound/outbound mutual exclusion, `WarehouseFlowPolicyService`                                          | `tests/Feature/WarehouseInboundMutualExclusionValidationTest.php`                 | Dev/QA 2026-06-16 Pass; §1.3 inbound canonical |
| WUP-05 | Done (nền)                      | API availability + AI webhook hooks                                                                      | `tests/Feature/WarehouseAvailabilityApiTest.php`, webhook scenarios trong runbook | Dev/QA 2026-06-16 Pass; §1.6 item 5–6 |
| WUP-06 | Done (nền)                      | `WarehouseUnitConversionService`, strict mode config                                                     | `tests/Feature/WarehouseUnitConversionFlowTest.php`                               | Dev/QA 2026-06-16 Pass; §1 §4 unit conversion |
| WUP-07 | Done (tối thiểu)                | `stock_movements.idempotency_key`, `warehouse:reconciliation-report`, widget snapshot vs batch (2026-05) | `WarehouseUpgradeP0Test`, lệnh `warehouse:reconciliation-report`                  | Dev/QA 2026-06-16 Pass; §1 §5 + màn **Adjust stock** |
| WUP-08 | Done dev/browser                | Purchase Reports tab `warehouse-movement-report`; `WarehouseMovementsDataTable` filter ngày/kho/loại/reference + Excel export | `tests/Feature/PurchaseModuleRoutesTest.php` — 3 passed / 45 assertions (re-run 2026-06-16); browser smoke 2026-06-16 console error 0; export tải `.xlsx` thành công | Chờ business sign-off nếu cần biên bản kế toán/vận hành |
| WUP-09 | Not required for current multi-warehouse scope | `stock_movements.warehouse_location_from_id/to_id`; `StockMovementService` propagate location id khi schema có cột | `tests/Feature/WarehouseUpgradeP0Test.php` — 9 passed / 22 assertions (re-run 2026-06-16) | Không triển khai UI/table riêng hiện tại; giữ P2 optional nếu cần kệ/ngăn/vị trí vật lý |
| WUP-10 | Done                            | `SalesDoInvoiceGuardService` + `InvoiceController::store()` guard; Sales DO schema/runtime readiness    | `tests/Unit/SalesDoInvoiceGuardServiceTest.php`, `tests/Feature/SalesDoInvoiceUatTest.php` | UAT create invoice từ Sales DO + guard vượt qty |

### 2.1.1 Mẫu điền cột UAT (P0-07 — copy vào biên bản hoặc thay `—` trong ticket)

Mỗi dòng WUP-01…07: khi pilot chạy xong mục runbook tương ứng, điền **ngày**, **tester**, **Pass/Fail**, **link** (file ảnh, Google Doc, ticket).

| Mã WUP | Ngày UAT   | Tester | Kết quả      | Link biên bản / ghi chú                                              |
| ------ | ---------- | ------ | ------------ | -------------------------------------------------------------------- |
| WUP-01 | 2026-05-25 | Dev/QA | Pass         | LOCK-UAT (41) + SCRAP-UAT (42); outbound blocked UI + service        |
| WUP-02 | 2026-05-25 | Dev/QA | Pass         | SUGAR @ DFWH sellable YES (272); `WarehouseAvailabilityApiTest`      |
| WUP-03 | 2026-05-25 | Dev/QA | Pass         | Luồng B SS-000001; `SalesDoServiceLifecycleTest`                     |
| WUP-04 | 2026-05-25 | Dev/QA | Pass         | `.env` PO-inbound=true, DO-inbound=false; mutual-exclusion test pass |
| WUP-05 | 2026-05-25 | Dev/QA | Pass (smoke) | Availability YES/NO + `ai_order_webhook_check_stock`                 |
| WUP-06 | 2026-05-25 | Dev/QA | Pass         | Luồng D UOM; `WarehouseUnitConversionFlowTest`                       |
| WUP-07 | 2026-05-25 | Dev/QA | Pass         | `warehouse:reconciliation-report` 3 mov / 0 dup; Adjust Stock UI     |
| WUP-01…07 | 2026-06-16 | Dev/QA | Pass | Automated readiness re-run: 28 passed / 113 assertions; still needs BA/PM operational sign-off if required |

Sau khi điền, **copy một dòng tóm tắt** vào cột **UAT / Runbook** của bảng §2.1 (ví dụ: `UAT 2026-05-20 Pass — see DOC-123`) hoặc giữ bảng chi tiết tại wiki nội bộ và chỉ ghi link ngắn.

---

## 3) Kế hoạch triển khai đề xuất

### Giai đoạn P0 (bắt buộc, chống lỗi nghiệp vụ)

| Sprint   | Hạng mục       | Deliverable                         | Tiêu chí hoàn thành                                                  |
| -------- | -------------- | ----------------------------------- | -------------------------------------------------------------------- |
| Sprint 1 | WUP-01, WUP-04 | Schema + validation + policy config | Không thể xuất từ kho locked/scrap; không còn cấu hình nhập/xuất đôi |
| Sprint 1 | WUP-02         | Service/API `sellable` dùng chung   | Mọi màn kiểm tồn gọi cùng 1 nguồn dữ liệu                            |
| Sprint 2 | WUP-03         | Flow reserve/outbound/release       | Tạo đơn giữ chỗ, giao hàng trừ tồn, hủy đơn trả tồn đúng             |

### Giai đoạn P1 (phục vụ Miaolin vận hành thật)

| Sprint     | Hạng mục       | Deliverable                          | Tiêu chí hoàn thành                                         |
| ---------- | -------------- | ------------------------------------ | ----------------------------------------------------------- |
| Sprint 3   | WUP-05, WUP-06 | API availability + module conversion | AI/Line check hàng đúng, không lệch đơn vị                  |
| Sprint 3-4 | WUP-07         | Job sync ERP + reconciliation report | Sync lặp lại không nhân bản dữ liệu, có log lỗi và đối soát |
| Sprint 4   | WUP-08         | Bộ báo cáo tồn/movement theo kho     | Ops và kế toán tự đối soát được theo chứng từ               |

### Giai đoạn P2 (tối ưu sau khi ổn định)

| Sprint  | Hạng mục | Deliverable                                  |
| ------- | -------- | -------------------------------------------- |
| Optional | WUP-09  | Master bin/location UI/table riêng nếu vận hành cần quản lý vị trí vật lý chi tiết |

### Kế hoạch triển khai WUP-09 nếu sau này cần bin/location

| Bước | Phạm vi | Deliverable | Điều kiện bắt đầu |
| ---- | ------- | ----------- | ----------------- |
| 1 | Chốt mô hình vị trí | Quy ước cấp vị trí: Warehouse -> Aisle/Rack/Bin hoặc Warehouse -> Bin đơn giản | Ops xác nhận thật sự cần quản lý vị trí vật lý |
| 2 | Schema additive | Bảng master `warehouse_locations` hoặc cấu trúc tương đương; không đổi logic tồn theo kho hiện tại | Có mô hình vị trí được duyệt |
| 3 | UI master | Màn quản lý vị trí theo từng kho: tạo/sửa/tắt trạng thái, import nếu cần | Schema đã có và permission được chốt |
| 4 | Nhập/xuất/chuyển | Các form inbound/outbound/transfer có thể chọn vị trí; bắt buộc hay tùy chọn theo cấu hình | Pilot tenant xác nhận flow vận hành |
| 5 | Báo cáo/trace | Báo cáo tồn và movement có filter theo vị trí; trace chứng từ hiển thị vị trí | Có dữ liệu pilot đủ kiểm thử |
| 6 | Regression/UAT | Test movement không lệch tồn kho; UAT nhập -> chuyển vị trí -> xuất -> đối soát | Hoàn tất code + dữ liệu mẫu |

**Không làm trong scope hiện tại:** ép người dùng chọn bin/location khi tạo PO/GRN/DO/SO, chia tồn theo kệ/ngăn, hoặc đổi báo cáo tồn mặc định từ warehouse sang warehouse + bin.

---

## 4) UAT checklist bắt buộc sau nâng cấp

- Không bán được từ kho `locked/scrap`.
- Không oversell khi tạo 2 đơn gần đồng thời.
- Không nhập đôi cùng một lô khi bật sai cờ PO/DO.
- Không trừ đôi khi shipment và invoice cùng chạy.
- Không thể tạo invoice trùng cho cùng SO/DO gây double bill (hoặc phải có bước xác nhận + phân quyền override).
- Một chứng từ phải trace được đầy đủ trên `stock_movements` (warehouse, ref, qty, time).
- API check hàng trả cùng kết quả với tồn thực tế sau quy đổi đơn vị.

---

## 5) Quy tắc dùng báo cáo PM từ nay

- Dùng báo cáo PM như danh sách rủi ro/test case.
- Không dùng lại các kết luận lỗi thời nếu đã có trong code.
- Mỗi sprint cập nhật trạng thái theo mã `WUP-xx` (Done / In Progress / Blocked).

# Tác động theo từng file của bạn

- Quote_inventory.csv → hưởng lợi nhiều nhất

    File này có đủ warehouse_code, batch, HSD, tồn đầu/kỳ/cuối, kho “đặc biệt” (báo phế, in-transit...).
    Khi có warehouse_type + rule + sellable, hệ thống sẽ:
    import đúng tồn theo kho/lô,
    không nhầm kho bán được với kho không bán,
    đối soát ERP dễ hơn.

- Craveva product info.csv → hưởng lợi trung bình-cao

    Có SKU, quy cách, đơn vị, storage days, nhiệt độ...
    Khi chuẩn hóa unit conversion + core field mapping, import sản phẩm sẽ ổn định hơn, ít lệ thuộc CF.

- Craveva product price.csv → hưởng lợi trung bình

    Chủ yếu là bảng giá theo SKU.
    Nâng cấp warehouse không tác động trực tiếp, nhưng có lợi gián tiếp nếu bạn chuẩn hóa SKU/base unit/idempotent import.

- Craveva*Customer Profile*客戶資料.csv → hưởng lợi trung bình

    Có customer_code, payment terms, designated_warehouse_name.
    Khi kho được phân loại/rule rõ, map khách -> kho mặc định sẽ an toàn hơn (tránh map vào kho không bán).

- Last year net sales.xlsx → hưởng lợi thấp (không trực tiếp)

    Đây thiên về phân tích doanh số lịch sử, không phải file master import kho chuẩn.
    Dùng tốt cho forecast/reorder, nhưng không cải thiện nhập tồn trực tiếp.

# Kết luận ngắn

    - Nếu mục tiêu là import vận hành thật cho Miaolin, nâng cấp sẽ giúp mạnh nhất ở:
        - Quote_inventory.csv
        - Craveva product info.csv
        - Craveva_Customer Profile_客戶資料.csv
    Hai file còn lại (product price, last year net sales) cần luồng import/báo cáo riêng, không phụ thuộc 100% vào warehouse core.

File thay đổi:

Modules/warehouse
Modules/Purchase

app/Models/StockMovement.php
tests/

---

## 6) Trạng thái triển khai (Apr 2026)

- `WUP-01` (P0): **Done**
    - Thêm `warehouse_type` (migration + index).
    - Chặn outbound/reserve từ kho không sellable (`locked/scrap/transit`) bằng policy tập trung.
- `WUP-02` (P0): **Done**
    - Có `WarehouseAvailabilityService` chuẩn hóa `on_hand/reserved/available/sellable`.
    - Có API `GET /api/v1/warehouse/availability` trả YES/NO và chi tiết theo kho.
- `WUP-03` (P0): **Done**
    - Chuẩn hóa lifecycle `confirm -> reserve`, `ship -> consume + outbound`, `cancel -> release`.
    - `reverse` từ shipped về confirmed sẽ re-reserve.
- `WUP-04` (P0): **Done**
    - Guard cấu hình inbound canonical (PO vs DO) + warning log khi mâu thuẫn.
    - Guard cấu hình outbound canonical (invoice vs shipment) + fail fast khi mode sai.
- `WUP-05` (P1 nền): **Done (nền + call-site chính)**
    - API `GET /api/v1/warehouse/availability` (Sanctum) + `WarehouseAvailabilityService`.
    - **Call-site nghiệp vụ:** **`POST /api/integrations/orders`** (`AiIntegrationOrdersController`) gọi `validateAiOrderWebhookItems` trước khi tạo Order (theo package + env; có `check_stock`, `warehouse_ids`, `items.*.unit_id`). Các luồng khác có thể gọi cùng service khi cần mở rộng.
- `WUP-06` (P1 nền): **Done (nền)**
    - Bổ sung service conversion về base unit trước reserve/inbound/outbound.
    - Có cấu hình strict/fallback (`WAREHOUSE_STRICT_UNIT_CONVERSION`).
- `WUP-07` (P1 nền): **Done (tối thiểu)**
    - Bổ sung `idempotency_key` cho `stock_movements` để chống post trùng.
    - Có command reconciliation ngày: `warehouse:reconciliation-report` + log bảng đối soát.

### Audit (2026-04-09)

- **Code đối chiếu:** `Modules/Warehouse/Services/WarehouseAvailabilityService`, `AiIntegrationOrdersController`, `Modules/Warehouse/Routes/api.php`, `Modules/Warehouse/Config/config.php` (`WAREHOUSE_AI_ORDER_WEBHOOK_CHECK_STOCK`).
- **Test đã chạy:** `tests/Feature/WarehouseUpgradeP0Test.php`, `tests/Feature/SalesDoServiceLifecycleTest.php`.
- **Backlog theo kế hoạch gốc:** `WUP-09` (bin/location) đã chuyển thành **Not required for current multi-warehouse scope**; giữ P2 optional với bảng kế hoạch triển khai nếu sau này cần quản lý kệ/ngăn/vị trí vật lý. `WUP-08` đã có báo cáo movement/reference trong Purchase Reports, automated readiness test cho filter/export/reference mapping và browser export smoke pass; còn chờ business sign-off hoặc mở rộng valuation theo kỳ nếu kế toán yêu cầu. `WUP-09` đã có movement-ledger readiness cho location id, không cần thêm UI/table trong scope hiện tại.
- **WUP-10:** Done — guard chống invoice vượt tổng Sales DO shipped/delivered trừ phần đã invoice; feature UAT xác nhận form tạo invoice prefill từ Sales DO shipped và route store chặn vượt qty.
