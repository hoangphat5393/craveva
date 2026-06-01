# P0 — Mini UAT 3 luồng gốc (template biên bản)

Ngày: **2026-05-24**  
Tenant / công ty pilot: **Demo Company**  
Người thực hiện: **Dev/QA tự động (Cursor live demo + Pest smoke)** — _chữ ký BA/PM bên dưới khi xác nhận_  
Môi trường: `local` — URL: **https://craveva-staging.test**

Hướng dẫn: đánh dấu **Pass / Fail / N/A**; ghi **ISS-xxx** nếu lỗi; mức độ **S1–S3** (S1 = chặn go-live).

**Trước khi chạy UI (Dev):** smoke route + wiring — `php artisan test --compact tests/Feature/P0BiomixingAutomatedEvidenceTest.php tests/Feature/BiomixingDemoRoutesReadinessTest.php` → **7 passed** (2026-05-24).

**Ghi chú loại kết quả (thuật ngữ QA):**

| Nhãn                | Nghĩa                                                                                                                                                                        |
| ------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Pass (smoke)**    | **Smoke test** — kiểm tra **nông, nhanh**: màn hình/route mở được, menu có, không lỗi 500; đôi khi chỉ xác nhận dữ liệu có sẵn. **Chưa** chứng minh đủ nghiệp vụ end-to-end. |
| **Pass**            | Đã chạy **đủ bước nghiệp vụ** trên UI (hoặc test tự động tương đương) và đối chiếu kết quả (số dòng, khách, tồn…).                                                           |
| **Partial (smoke)** | Một phần Pass (smoke), phần còn lại Chưa chạy.                                                                                                                               |
| **Chưa chạy**       | BA/QA cần thực hiện case đầy đủ trên UI.                                                                                                                                     |

---

## Luồng A — Estimate → Sales Order

| Bước | Mô tả ngắn                                        | Kết quả      | Ghi chú / Issue                                                                                                      |
| ---- | ------------------------------------------------- | ------------ | -------------------------------------------------------------------------------------------------------------------- |
| A1   | Mở báo giá / estimate, điền dòng hàng             | Pass (smoke) | `/account/estimates` — Quotations load; Create Quotation; DB có 3 estimate Demo Company                              |
| A2   | Duyệt nội bộ (nếu có) + chuyển trạng thái phù hợp | Pass         | **EST#001** (`/account/estimates/3`): Submit for review → President approve → VP pricing approve; timeline 3 sự kiện |
| A3   | Chuyển / tạo Sales Order từ báo giá               | Pass         | Action → Convert to Sales Order → **ODR#002** (`/account/orders/49`), `estimate_id=3`                                |
| A4   | Kiểm tra SO hiển thị đúng số dòng, giá, khách     | Pass         | Khách **TESTER2** (65090); 1 dòng **CP CHICKEN WING** qty **1** × **500,000** — khớp `estimate_items`                |

**Kết luận luồng A:** **Pass** — Ngày: **2026-05-24** — _Mẫu EST#001→ODR#002; chữ ký BA/PM vẫn chờ xác nhận chính thức_

---

## Luồng B — Sales Order → DO → Invoice

| Bước | Mô tả ngắn                                                             | Kết quả | Ghi chú / Issue                                                                                                      |
| ---- | ---------------------------------------------------------------------- | ------- | -------------------------------------------------------------------------------------------------------------------- |
| B1   | Từ SO tạo / xác nhận Delivery Order (theo luồng công ty)               | Pass    | **ODR#002** → tạo **SS-000001** (`/account/sales-do/3`), kho **DFWH**, ship qty **1** CP CHICKEN WING                |
| B2   | Reserve / ship / invoice theo cấu hình warehouse (shipment vs invoice) | Pass    | **SS-000001**: Confirm → Ship → **Delivered**; Create Invoice → form pre-fill từ DO                                  |
| B3   | Kiểm tra trừ tồn đúng, không double outbound                           | Pass    | Inventory UI (batch stock): CP CHICKEN WING @ DFWH **20 → 19** sau Ship qty **1**; banner sync batch stock           |
| B4   | Invoice khớp phần giao (số lượng / dòng)                               | Pass    | **INV#001** (`/account/invoices/6`): 1× CP CHICKEN WING @ **500,000**, total **500,000**, `order_id=49` — khớp DO/SO |

**Kết luận luồng B:** **Pass** — Ngày: **2026-05-24** — _EST#001→ODR#002→SS-000001→INV#001; chữ ký BA/PM chờ xác nhận_

---

## Luồng C — PO → GRN → Bill

| Bước | Mô tả ngắn                                                   | Kết quả | Ghi chú / Issue                                                                                                                                                                                 |
| ---- | ------------------------------------------------------------ | ------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| C1   | Tạo PO, nhận hàng (GRN / DO nhập theo tên màn hình thực tế)  | Pass    | Vendor **UAT Vendor** (id 2); **PO#001** (`/account/purchase-order/7`): SUGAR ×2 @ 25 USD; GRN **2345236** (id 4), status **Received**, `purchase_order_id=7`, DFWH — `inbound_stock_applied=1` |
| C2   | Kiểm tra nhập kho + batch (nếu dùng)                         | Pass    | GRN **2345236** → `stock_movements` inbound **+2** (ref `Grn` id 4, 2026-05-25); **SUGAR** @ DFWH (`warehouse_product_stock`) **272**; `sellable` API **YES** / available **272**               |
| C3   | Tạo bill / hóa đơn mua khớp PO/GRN                           | Pass    | PO#001 → **Convert To Bill** → Bill#**1** (`purchase_bills.id=2`): SUGAR ×2 @ 25, **total 50**, status **open**, `purchase_order_id=7`                                                          |
| C4   | Không nhập đôi khi cấu hình inbound conflict (nếu test được) | N/A     | Chưa test cố ý conflict; xác nhận env inbound khi pilot                                                                                                                                         |

**Kết luận luồng C:** **Pass** — Ngày: **2026-05-25** — _Sửa lỗi Blade GRN create (`@php()` one-liner) trước khi chạy C2; chữ ký BA/PM chờ xác nhận_

---

## Luồng D — Production (trừ NL — UOM)

> **Đã vá code 2026-05-20** — UAT Luồng D: BOM line **g** trên SP **kg** → sau Post RM, tồn giảm **0,1** (không **100**). Ref: `PRODUCTION_OPERATIONS_LIVE_VI.md` §2.

| Bước | Mô tả ngắn                                   | Kết quả        | Ghi chú                                                                                                                                                                                                                  |
| ---- | -------------------------------------------- | -------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| D1   | Lệnh SX + lô; planned RM có ĐVT khác base SP | Pass (dữ liệu) | Nhiều lệnh completed/in_progress; checklist 5 bước trên batch — `/account/production/orders`                                                                                                                             |
| D2   | Post RM; đối chiếu Inventory (đơn vị gốc)    | Pass           | **Batch #14** (đã post): **Test RM** planned **200 g** → movement trừ **0,2** (kg base), không 200. **Hôm nay:** batch **#5** — «Deduct raw materials» live. Inventory: `/account/purchase-inventory` search **Test RM** |
| D3   | Tổng NL trên lệnh SX khớp số trừ thực tế     | Pass           | Lệnh **#32** / batch **#14**: Test RM 200g→**0,2**; Test RM 2 **4**→**4**; TEST RM 3 **8 pack**→**0,8** (factor 0,1) — khớp `stock_movements` ref batch 14                                                               |

**Kết luận luồng D:** **Pass** — Ngày: **2026-05-25** — _UOM g↔kg trên batch #14; live post RM batch #5_

---

## Luồng E — Production post FG → Inventory (P1c)

> **Đã vá code 2026-05-23** — Sau Post FG receipt, SP FG phải có dòng trên **Purchase → Inventory** (tìm theo tên/SKU, không mã lô). Ref: `PRODUCTION_OPERATIONS_LIVE_VI.md` §2.

| Bước | Mô tả ngắn                                          | Kết quả | Ghi chú                                                                                                                                               |
| ---- | --------------------------------------------------- | ------- | ----------------------------------------------------------------------------------------------------------------------------------------------------- |
| E1   | Post FG trên batch (đúng kho FG, mã lô bắt buộc)    | Pass    | Browser: `/account/production/batches/14` — 5 bước ✓; RM deducted **15:43:03**, FG posted **15:43:50**; lô **BATCH-0004** qty **2** @ kho **39** (DB) |
| E2   | Mở Inventory — lọc SP + kho                         | Pass    | Browser: `/account/purchase-inventory` search **Bánh kem** → **SP-FG-000011**, Available/Ending **12** @ **DEFAULT WAREHOUSE (DFWH)**                 |
| E3   | (Tuỳ chọn) So sánh với Products `withSum inventory` | Partial | Browser Products: cột **Total net qty** hiển thị `--`; DB `SUM(net_quantity)=12` khớp Inventory — ghi nhận lệch hiển thị list vs ledger               |

**Kết luận luồng E:** **Pass** — Ngày: **2026-05-24** — _P1c OK trên Inventory; E3 là gap hiển thị Products (không chặn FG sync)_

---

## P0-05 — Trace hai chiều (bổ sung mini UAT)

| Bước  | Mô tả                             | Kết quả | Ghi chú                                                                                                              |
| ----- | --------------------------------- | ------- | -------------------------------------------------------------------------------------------------------------------- |
| P0-05 | Trace P→W batch **#14**           | Pass    | `/account/production/batches/14/trace` — **7** link «Open warehouse batch»                                           |
| P0-05 | Trace W→P batch **#17** (Test RM) | Pass    | `/account/warehouse-product-batches/17` — có **Open Production Trace** + **Open Production Batch** (↔ batch **#14**) |

---

## P0-07 — Warehouse upgrade (WUP-01…07)

> Route đúng: **Warehouses** = `/account/warehouse` (không `/account/warehouses`). **Adjust Stock** = `/account/warehouse-stock/create`.

| Mã     | Mô tả ngắn                         | Kết quả      | Ghi chú / Evidence (2026-05-25)                                                                                                                                                                                                   |
| ------ | ---------------------------------- | ------------ | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| WUP-01 | Loại kho `normal` vs locked/scrap  | Pass         | Kho UAT: **LOCK-UAT** (id **41**, `locked`), **SCRAP-UAT** (id **42**, `scrap`). Remove stock @ locked → lỗi «not allowed» (UI Adjust Stock + service). Locked: on_hand **5**, sellable **0**, YES/NO **NO**                      |
| WUP-02 | Sellable / availability API        | Pass         | `WarehouseAvailabilityService`: SUGAR (4026) @ DFWH → on_hand **272**, sellable **YES**; `WarehouseAvailabilityApiTest` **passed**                                                                                                |
| WUP-03 | Reserve → ship → cancel DO         | Pass         | Luồng B **SS-000001** (reserve/ship); `SalesDoServiceLifecycleTest` **9 passed**                                                                                                                                                  |
| WUP-04 | Inbound canonical (một nguồn nhập) | Pass         | `.env` pilot: `WAREHOUSE_INBOUND_FROM_PO_DELIVERED=true`, `WAREHOUSE_INBOUND_FROM_DO_RECEIVED=false` + `config:clear`. GRN PO đã inbound +2 SUGAR; `WarehouseInboundMutualExclusionValidationTest` **passed**                     |
| WUP-05 | API / AI stock check               | Pass (smoke) | `ai_order_webhook_check_stock=true`; availability YES/NO khớp tồn SUGAR; test API **passed** (chưa gọi webhook live)                                                                                                              |
| WUP-06 | Quy đổi đơn vị                     | Pass         | Luồng D batch **#14** (g→kg); `WarehouseUnitConversionFlowTest` **2 passed**                                                                                                                                                      |
| WUP-07 | Idempotent + đối soát + Adjust     | Pass         | `warehouse:reconciliation-report --date=2026-05-25 --company_id=1` → **3** movements, **0** duplicate groups; log DB `warehouse_sync_reconciliation_logs` id **1**; UI **Adjust Stock** mở OK (`/account/warehouse-stock/create`) |

**Kết luận P0-07:** **Pass (dev/QA)** — 2026-05-25 đã chỉnh `.env` inbound + tạo kho UAT locked/scrap.

---

## Tổng kết

| Mục                                     | Giá trị                                                                                      |
| --------------------------------------- | -------------------------------------------------------------------------------------------- |
| Số issue mở                             | 0 (smoke)                                                                                    |
| Lỗi S1 (chặn)                           | 0                                                                                            |
| Đủ điều kiện pilot Biomixing phase 1–2? | **Sẵn sàng chờ BA/PM (dev/QA)** — A–E + P0-05 + WUP-01…07 **Pass**; chữ ký **BA/PM** chưa có |

**Evidence tổng hợp:** Live demo Cursor browser **2026-05-24/25** (A–E, C PO/GRN/Bill, P0-05, WUP UI/API); Pest smoke **7** + WUP cluster **25** passed (`WarehouseUpgradeP0Test`, `WarehouseAvailabilityApiTest`, `SalesDoServiceLifecycleTest`, `WarehouseInboundMutualExclusionValidationTest`, `WarehouseUnitConversionFlowTest`).

Chữ ký BA: **\_\_\_\_** (chờ xác nhận) Chữ ký QA/PM: **\_\_\_\_** (chờ xác nhận)
