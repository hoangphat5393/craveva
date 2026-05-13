# Warehouse — Runbook vận hành & kế hoạch nâng cấp (WUP)

## Trạng thái rà soát (2026-04-30)

- Đã triển khai: `WUP-01` -> `WUP-07`.
- Chưa triển khai: `WUP-08`, `WUP-09`.
- Mới bổ sung nhưng chưa có implementation: `WUP-10` (chống invoice trùng SO/DO).
- Khuyến nghị: giữ file này làm **nguồn trạng thái chính** cho nhóm Warehouse/Purchase improve.

### Recheck 2026-05-09 (Specification Reconciliation)

- Xac nhan lai: `WUP-01` -> `WUP-07` van co bang chung code/test trong `Modules/Warehouse/*` va `tests/Feature/*`.
- `WUP-08`, `WUP-09`, `WUP-10` van la backlog (chua co bang chung implementation day du de doi sang Done).
- Quy tac cap nhat: chi danh dau Done khi co du ca 3 lop **route/service(or migration)/test hoac UAT evidence**.

**Ngày gộp:** 2026-04-06  
**Nguồn:** `WAREHOUSE_OPERATION_RUNBOOK_VI.md` + `WAREHOUSE_UPGRADE_PLANE.MD` (tên cũ _PLANE_ đã đổi nội dung vào file này).

---

## Mục lục

1. [Runbook vận hành local / nghiệp vụ](#1-runbook-vận-hành-local--nghiệp-vụ)
2. [Kế hoạch nâng cấp Warehouse (WUP) & trạng thái](#2-kế-hoạch-nâng-cấp-warehouse-wup--trạng-thái)

---

# 1) Runbook vận hành local / nghiệp vụ

## 1) Mục tiêu

Tài liệu này là quy trình vận hành Warehouse theo luồng mới (WUP-01..WUP-07 nền), ưu tiên chạy local để test UI và nghiệp vụ trước khi đồng bộ môi trường khác.

## 2) Cấu hình local khuyến nghị

**Bảng đầy đủ biến `.env` (kho + Purchase / webhook AI):** [`WH_PURCHASE_ENV_REFERENCE_VI.md`](WH_PURCHASE_ENV_REFERENCE_VI.md).

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
6. Webhook AI tạo đơn (`POST /ai-order-webhook/{hash}`): khi company có module **warehouse** trong package và không tắt kiểm tra, hệ thống chặn tạo Order nếu **tổng sellable (base)** không đủ (HTTP 422 + message). Tắt tạm: `check_stock: false` hoặc env `WAREHOUSE_AI_ORDER_WEBHOOK_CHECK_STOCK=false`.

## 7) Vận hành sự cố

- Lỗi `Inbound configuration conflict`: tắt 1 trong 2 cờ inbound PO/DO.
- Lỗi `Missing unit conversion mapping`: thêm mapping `product_unit_conversions` hoặc tạm thời tắt strict mode.
- Số liệu outbound bị lặp: chạy reconciliation report và đối soát duplicate group theo `reference_type/reference_id`.

## 8) Phạm vi local-first

- Ưu tiên xác nhận nghiệp vụ và UI trên local trước.
- Không phụ thuộc hub/staging trong giai đoạn này.

---

# 2) Kế hoạch nâng cấp Warehouse (WUP) & trạng thái

**Nguồn đầu vào:** `FUINC_REPORT/PM report 1.md`, `FUINC_REPORT/PM report 2.md`
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
| WUP-09 | Bin/location (giai đoạn 2)         | Có field location ở movement nhưng chưa triển khai hoàn chỉnh            | Có mô hình location rõ hoặc quyết định postpone chính thức                                             |
| WUP-10 | Chống tạo invoice trùng theo SO/DO | Hiện có thể tạo nhiều invoice cho cùng `order_id`/DO dẫn tới double bill | Có guard nghiệp vụ: chặn hoặc cảnh báo mạnh khi tổng qty/amount invoice vượt phần đã giao/chưa invoice |

### 2.1 Bảng Evidence WUP (P0-07 — ba lớp: code / test / UAT)

Dùng bảng này khi đánh dấu `Done`/`Partial` theo quy tắc `FUNC_IMPROVE/P0_NEXT_ACTION_BIOMIXING_VI.md`. Cột **UAT** điền tham chiếu biên bản hoặc mục checklist §1 runbook / báo cáo mini-UAT.

| Mã     | Trạng thái tổng quát (Apr 2026) | Evidence (code — thư mục / service chính)                                                                | Evidence (test — file gợi ý)                                                      | UAT / Runbook                                  |
| ------ | ------------------------------- | -------------------------------------------------------------------------------------------------------- | --------------------------------------------------------------------------------- | ---------------------------------------------- |
| WUP-01 | Done                            | `Modules/Warehouse` `warehouse_type`, flow policy outbound                                               | `tests/Feature/WarehouseUpgradeP0Test.php`                                        | §1 runbook 3.1 kho `normal` vs locked          |
| WUP-02 | Done                            | `WarehouseAvailabilityService`, `Modules/Warehouse/Routes/api.php`                                       | `tests/Feature/WarehouseAvailabilityApiTest.php`                                  | API §1.6 + checklist §6                        |
| WUP-03 | Done                            | Sales DO lifecycle + `StockReservationService`                                                           | `tests/Feature/SalesDoServiceLifecycleTest.php`                                   | §1.3 reserve → ship → cancel                   |
| WUP-04 | Done                            | Inbound/outbound mutual exclusion, `WarehouseFlowPolicyService`                                          | `tests/Feature/WarehouseInboundMutualExclusionValidationTest.php`                 | §1.3 inbound canonical                         |
| WUP-05 | Done (nền)                      | API availability + AI webhook hooks                                                                      | `tests/Feature/WarehouseAvailabilityApiTest.php`, webhook scenarios trong runbook | §1.6 item 5–6                                  |
| WUP-06 | Done (nền)                      | `WarehouseUnitConversionService`, strict mode config                                                     | `tests/Feature/WarehouseUnitConversionFlowTest.php`                               | §1 §4 unit conversion                          |
| WUP-07 | Done (tối thiểu)                | `stock_movements.idempotency_key`, `warehouse:reconciliation-report`, widget snapshot vs batch (2026-05) | `WarehouseUpgradeP0Test`, lệnh `warehouse:reconciliation-report`                  | §1 §5 + màn **Adjust stock** (widget đối soát) |
| WUP-08 | Backlog                         | —                                                                                                        | —                                                                                 | —                                              |
| WUP-09 | Backlog                         | —                                                                                                        | —                                                                                 | —                                              |
| WUP-10 | Backlog                         | —                                                                                                        | —                                                                                 | Chờ spec guard invoice                         |

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
| Backlog | WUP-09   | Thiết kế/location rollout hoặc chốt chưa làm |

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
    - **Call-site nghiệp vụ:** `POST /ai-order-webhook/{hash}` gọi `validateAiOrderWebhookItems` trước khi tạo Order (theo package + env; có `check_stock`, `warehouse_ids`, `items.*.unit_id`). Các luồng khác có thể gọi cùng service khi cần mở rộng.
- `WUP-06` (P1 nền): **Done (nền)**
    - Bổ sung service conversion về base unit trước reserve/inbound/outbound.
    - Có cấu hình strict/fallback (`WAREHOUSE_STRICT_UNIT_CONVERSION`).
- `WUP-07` (P1 nền): **Done (tối thiểu)**
    - Bổ sung `idempotency_key` cho `stock_movements` để chống post trùng.
    - Có command reconciliation ngày: `warehouse:reconciliation-report` + log bảng đối soát.

### Audit (2026-04-09)

- **Code đối chiếu:** `Modules/Warehouse/Services/WarehouseAvailabilityService`, `AiOrderWebhookController`, `Modules/Warehouse/Routes/api.php`, `Modules/Warehouse/Config/config.php` (`WAREHOUSE_AI_ORDER_WEBHOOK_CHECK_STOCK`).
- **Test đã chạy:** `tests/Feature/WarehouseUpgradeP0Test.php`, `tests/Feature/SalesDoServiceLifecycleTest.php`.
- **Backlog theo kế hoạch gốc:** `WUP-08` (báo cáo vận hành rộng), `WUP-09` (bin/location) — chưa nằm trong phạm vi P0/P1 nền.
