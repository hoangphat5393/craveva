# P0 Biomixing + Warehouse — bảng test case QA/BA (một lượt)

**Mục đích:** Gom **một bảng** để QA/BA chạy tuần tự, ghi **Pass / Fail / N/A** và **evidence** (URL, screenshot, ticket). Phần Dev (route smoke, Pest wiring) đã có trong repo — **không** thay thế các bước UI dưới đây.

**Trước khi chạy UI (Dev đã làm xong — QA có thể nhờ Dev xác nhận):**

```bash
php artisan test --compact tests/Feature/BiomixingDemoRoutesReadinessTest.php tests/Feature/P0BiomixingAutomatedEvidenceTest.php tests/Feature/ProductionPostingServiceTest.php tests/Feature/ProductionFgQuantityPolicyServiceTest.php tests/Feature/ProductionVarianceApprovalPermissionTest.php tests/Unit/ProductionFgInventoryLedgerSyncTest.php tests/Feature/WarehouseReconciliationServiceInventorySnapshotTest.php tests/Feature/WarehouseProductBatchRoutesTest.php
```

**Phiên bản:** 2026-06-16
**Dev regression gần nhất:** P0 bundle hiện tại → **43 passed / 161 assertions** (2026-06-16). Focused P0-02 variance permission → `php artisan test --compact tests\Feature\ProductionVarianceApprovalPermissionTest.php` → **2 passed / 7 assertions** (2026-06-16). Focused Products stock-on-hand regression → `php artisan test tests\Unit\PurchaseProductsDataTableTest.php` → **5 passed / 12 assertions** (2026-06-14). BA/PM sign-off vẫn cần xác nhận riêng nếu dùng làm biên bản pilot.
**Tài liệu chi tiết (tham chiếu):** `P0_MINI_UAT_CHECKLIST_BIOMIXING.md` (luồng A–E), `BIOMIXING_DOC_HUB.md`, file này (phụ lục P0-02/P0-05 bên dưới), `PRODUCTION_BUSINESS.md` §3, `04_WH_RUNBOOK_UPGRADE.md` (§1 + §3.1.1).

---

## Cách test (tóm tắt)

1. **Chuẩn bị:** Cài app local (xem `BIOMIXING_LOCAL_DEV_SETUP.md`), đăng nhập **tenant pilot**, bật module **Production** + **Warehouse**, có user đủ quyền + (nếu cần) **hai user** cho TC-P0-02 (một user **không** có `edit_production_orders`, một user **có**).
2. **Điền khối «Thông tin phiên chạy»** (ngày, URL, company, tester).
3. **Bước tự động (không bắt buộc nhưng nên chạy 1 lần):** trong thư mục gốc repo, chạy lệnh `php artisan test` ở đầu file — xác nhận route + wiring không gãy sau build.
4. **Phần A — test trên trình duyệt:** đi **theo thứ tự dòng** trong bảng (TC-P0-01-01 → … → TC-P0-08-E). Với mỗi dòng: đọc **Điều kiện** → làm **Bước thực hiện** trên UI → so **Kỳ vọng** → ghi **P** (Pass) / **F** (Fail) / **N** (N/A) và cột **Evidence** (screenshot, URL, mã issue).
5. **Phần B (WUP):** mở `04_WH_RUNBOOK_UPGRADE.md` §1 theo cột **Tham chiếu runbook**, thực hiện từng kịch bản tương ứng WUP-01…07 → ghi P/F/N; đồng thời **điền bảng §2.1.1** trong file runbook (hoặc ghi link biên bản ngoài rồi dán link vào cột Evidence ở đây).
6. **Kết thúc:** làm mục **Sau khi chạy xong** (tổng kết, cập nhật `P0_BIOMIXING_NEXT_STEPS.md`, chữ ký).

**Ghi chú:** Các dòng **TC-P0-08-A/B/C/E** chỉ tóm tắt; chi tiết A1–C4, D1–D3, E1–E3 trong `P0_MINI_UAT_CHECKLIST_BIOMIXING.md`. Trace hai chiều và variance permission đã gộp vào phụ lục P0-05/P0-02 trong file này.

---

## Thông tin phiên chạy (điền trước khi test)

| Trường                                 | Giá trị                                  |
| -------------------------------------- | ---------------------------------------- |
| Ngày bắt đầu / kết thúc                | 2026-05-24; P0 bundle regression 2026-06-16; focused P0-02/P0-03 2026-06-16; focused Products stock 2026-06-14 |
| Môi trường (local / staging) + URL gốc | local — https://craveva-staging.test     |
| Tenant / Company pilot                 | Demo Company                             |
| Tester QA                              | Dev (Cursor live demo + automated smoke) |
| Tester BA / PM                         | _(chờ ký)_                               |
| Build / commit (nếu có)                | working tree 2026-06-16                  |

---

## Phần A — P0 Biomixing (Production + luồng hub)

| Mã TC       | P0    | Tiêu đề                                          | Điều kiện tiên quyết                                                                     | Bước thực hiện (tóm tắt)                                                                                                      | Kỳ vọng                                                                                                  | Kết quả (P/F/N) | Evidence (link / ảnh / ISS)            |
| ----------- | ----- | ------------------------------------------------ | ---------------------------------------------------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------- | -------------------------------------------------------------------------------------------------------- | --------------- | -------------------------------------- |
| TC-P0-01-01 | P0-01 | Policy FG pilot (config mặc định)                | Module Production bật; user có quyền xem cài đặt FG                                      | Mở `/account/production/fg-quantity-policy` (hoặc menu tương đương) → đọc mode / tolerance / require reason                   | Hiển thị khớp quyết định pilot (controlled, ~5%, có require reason ngoài tolerance nếu cấu hình như vậy) |                 |                                        |
| TC-P0-02-01 | P0-02 | Duyệt variance — user **không** được sửa lệnh SX | Có lệnh/batch + output FG chờ duyệt; chuẩn bị user **không** có `edit_production_orders` | Đăng nhập user đó → mở batch có output chờ variance → thử **Approve variance** (hoặc gọi API nếu QA có công cụ)               | Không thực hiện được (ẩn nút hoặc 403 / thông báo rõ)                                                    |                 |                                        |
| TC-P0-02-02 | P0-02 | Duyệt variance — user **có** quyền               | Cùng dữ liệu; user **có** `edit_production_orders` (all/added/owned/both)                | Đăng nhập → Approve variance → (nếu policy) Post FG                                                                           | Duyệt thành công; có `approved_at`; post FG thành công khi đủ điều kiện policy                           |                 |                                        |
| TC-P0-03-01 | P0-03 | Shadow Yield/UOM — pilot **OFF**                 | Baseline dev/QA giữ shadow OFF; PM chưa yêu cầu bật shadow                                | Xác nhận với Dev: `production.phase2.yield_uom_shadow_enabled` = false trên pilot; không yêu cầu nghiệp vụ dựa vào cột shadow | Không bắt buộc dữ liệu shadow cho quyết định pilot                                                       | P               | Runtime config false; baseline OFF test 1 passed / 5; shadow ON capability test 1 passed / 4 |
| TC-P0-05-01 | P0-05 | Trace P→W — mở lệnh và batch                     | Có order + batch đã post RM hoặc có movement gắn lô                                      | Production → Orders → chọn order → mở **batch**                                                                               | Trang batch load được                                                                                    | P               | batch/14, order/32                     |
| TC-P0-05-02 | P0-05 | Trace P→W — mở trang trace                       | TC-P0-05-01 Pass                                                                         | Từ batch → **Trace** (`production.batches.trace`)                                                                             | Trang trace hiển thị dòng movement / link                                                                | P               | `/account/production/batches/14/trace` |
| TC-P0-05-03 | P0-05 | Trace P→W — link sang Warehouse batch            | Trên trace có `warehouse_product_batch_id` hiển thị link                                 | Bấm **Open warehouse batch** (hoặc tương đương)                                                                               | URL dạng `/account/warehouse-product-batches/{id}`; đúng lô / SP / kho                                   | P               | 7× «Open warehouse batch» trên trace   |
| TC-P0-05-04 | P0-05 | Trace W→P — danh sách lô                         | User có `view_warehouse_stock` (hoặc tương đương)                                        | Warehouse → **Product batches** → mở một lô có tham chiếu Production                                                          | Chi tiết lô load được                                                                                    | P               | Warehouse batch #17 load OK            |
| TC-P0-05-05 | P0-05 | Trace W→P — link sang Production trace           | Lô có movement `reference_type` Production batch                                         | Trên chi tiết lô → bấm link **Production trace** (nếu có)                                                                     | Mở `production.batches.trace` đúng batch                                                                 | P               | Batch #17 có Open Production Trace + Open Production Batch |
| TC-P0-05-06 | P0-05 | Vòng kín tham chiếu                              | TC-P0-05-03 và TC-P0-05-05 Pass                                                          | Đối chiếu mã lệnh / mã batch / số lô giữa hai chiều                                                                           | Khớp nghiệp vụ kỳ vọng pilot                                                                             | P               | W↔P khớp batch #14 theo mini UAT P0-05 |
| TC-P0-06-01 | P0-06 | Widget đối soát tồn vs batch (tùy pilot)         | Đã bật module Warehouse; có quyền xem stock                                              | Mở màn **Warehouse stock** (index) → xem vùng đối soát / cảnh báo (nếu cấu hình)                                              | Widget hiển thị; không lỗi 500; số liệu có thể giải thích được (ghi chú nếu chênh lệch do nghiệp vụ)     | N               | Chưa mở warehouse-stock hôm nay        |
| TC-P0-08-A  | P0-08 | Luồng Estimate → Sales Order                     | Có quyền Estimate + Order; có báo giá mẫu hoặc tạo mới                                   | Làm theo `P0_MINI_UAT_CHECKLIST_BIOMIXING.md` luồng A (A1–A4)                                                              | Kết luận luồng A: Pass/Fail                                                                              | P               | Mini UAT A Pass; regression 43 passed  |
| TC-P0-08-B  | P0-08 | Luồng SO → DO → Invoice                          | Có SO + tồn đủ (nếu cần)                                                                 | Checklist luồng B (B1–B4)                                                                                                     | Kết luận luồng B: Pass/Fail; không double outbound                                                       | P               | Mini UAT B Pass; regression 43 passed  |
| TC-P0-08-C  | P0-08 | Luồng PO → GRN → Bill                            | Có vendor + PO                                                                           | Checklist luồng C (C1–C4)                                                                                                     | Kết luận luồng C: Pass/Fail                                                                              | P               | Mini UAT C Pass; regression 43 passed  |
| TC-P0-08-D  | P0-08 | Luồng Production — Post RM (UOM)                 | BOM g↔kg hoặc case tương tự; lệnh + batch released                                       | Checklist luồng D (D1–D3) — `PRODUCTION_BUSINESS.md` §3                                                             | Post RM trừ đúng đơn vị gốc; không lỗi 100×                                                              | P               | Mini UAT D Pass; regression 43 passed  |
| TC-P0-08-E  | P0-08 | Luồng Production — Post FG → Inventory (P1c)     | Batch đã post FG; user có quyền xem Purchase Inventory                                   | Checklist luồng E (E1–E3) — `PRODUCTION_BUSINESS.md` §3                                                             | Inventory có dòng SP FG; `net_quantity` khớp warehouse; Products listing hiển thị ledger qty đúng cho tracked/non-tracked | P               | batch/14; Bánh kem qty 12; Products stock focused test 5 passed (2026-06-14) |

---

## Phần B — P0-07 (WUP-01…07, đối chiếu runbook)

**Hướng dẫn:** Mỗi dòng = một mã WUP. Chạy mục runbook tương ứng trong `04_WH_RUNBOOK_UPGRADE.md` §1; sau đó **điền bảng §2.1.1** trong cùng file (copy ngày/tester/kết quả vào đó hoặc ghi link biên bản dưới đây).

| Mã TC        | P0    | WUP    | Tiêu đề                    | Tham chiếu runbook            | Kỳ vọng ngắn                                                                                  | Kết quả (P/F/N) | Evidence |
| ------------ | ----- | ------ | -------------------------- | ----------------------------- | --------------------------------------------------------------------------------------------- | --------------- | -------- |
| TC-P0-07-W01 | P0-07 | WUP-01 | Phân loại kho + chặn xuất  | §1 mục 3.1                    | Kho locked/scrap không dùng để bán đúng rule pilot                                            |                 |          |
| TC-P0-07-W02 | P0-07 | WUP-02 | Sellable / availability    | §1 mục 6 (API) nếu pilot dùng | Phản hồi availability nhất quán (hoặc N/A nếu pilot không dùng API)                           |                 |          |
| TC-P0-07-W03 | P0-07 | WUP-03 | Reserve → ship → cancel DO | §1 mục 3.2                    | Reserve/ship/cancel đúng tồn; không oversell trong kịch bản pilot                             |                 |          |
| TC-P0-07-W04 | P0-07 | WUP-04 | Cấu hình inbound canonical | §1 mục 3.3 + env              | Không nhập đôi khi cố tình bật sai tổ hợp (nếu test được) hoặc xác nhận cấu hình đúng 1 nguồn |                 |          |
| TC-P0-07-W05 | P0-07 | WUP-05 | API / AI stock check       | §1 mục 6                      | Endpoint hoặc webhook chặn/cho phép đúng kỳ vọng pilot (hoặc N/A)                             |                 |          |
| TC-P0-07-W06 | P0-07 | WUP-06 | Quy đổi đơn vị             | §1 mục 4                      | Không lệch tồn sau quy đổi trong case pilot                                                   |                 |          |
| TC-P0-07-W07 | P0-07 | WUP-07 | Idempotent + đối soát      | §1 mục 5 + widget P0-06       | Command/report hoặc widget không báo lỗi nghiệp vụ sai (ghi rõ môi trường)                    |                 |          |

---

## Sau khi chạy xong

1. **Tổng kết:** đếm Pass / Fail / N/A; liệt kê issue S1–S3 (S1 = chặn pilot).
2. **Cập nhật** `FUNC_IMPROVE/P0_BIOMIXING_NEXT_STEPS.md` (các mục P0-02, P0-05, P0-07, P0-08) khi đủ bằng chứng.
3. **Chữ ký:** BA / PM xác nhận cuối bảng (dòng dưới đây).

| Vai trò | Họ tên                 | Ngày       | Ghi chú                        |
| ------- | ---------------------- | ---------- | ------------------------------ |
| QA lead | Dev (smoke 2026-05-24; P0 bundle regression 2026-06-16; focused P0-02/P0-03 2026-06-16; Products stock focused regression 2026-06-14) | 2026-06-16 | Chờ QA lead ký sau BA full UAT |
| BA / PM |                        |            | Chờ ký                         |

---

## Phụ lục P0-02 — Duyệt variance FG theo quyền

**Kết luận nghiệp vụ hiện tại:** hệ thống dùng quyền `edit_production_orders` làm cổng duyệt variance FG. Chưa có quyền riêng kiểu `approve_fg_variance`; nếu BA/PM muốn người chỉ được duyệt variance nhưng không được sửa lệnh SX thì cần mở backlog permission mới.

| Hành động | Điều kiện bắt buộc | Kết quả kỳ vọng |
| --------- | ------------------ | --------------- |
| Approve variance (`production.outputs.approve-variance`) | Module Production bật; output cùng company; output chưa `posted_at`; user có `edit_production_orders` theo scope all/added/owned/both | Set `approved_by`, `approved_at` |
| User không có `edit_production_orders` | Cùng dữ liệu nhưng thiếu quyền sửa lệnh SX | Ẩn action hoặc trả 403, không set approval |
| Post FG khi policy yêu cầu approval | Đã approve variance; policy `enforce_variance_approval` yêu cầu duyệt | Post FG thành công |

**Regression evidence:** `php artisan test --compact tests\Feature\ProductionVarianceApprovalPermissionTest.php` → **2 passed / 7 assertions** (2026-06-16).

**Code refs:** `ProductionBatchController::approveOutputVariance`, helper permission `assertEditProductionOrders`.

## Phụ lục P0-05 — Trace Warehouse ↔ Production

**Điều kiện:** module Warehouse + Production bật; user có quyền xem production order/batch và warehouse batch; dữ liệu có production batch liên kết warehouse batch qua movements.

**Dev auto:** `php artisan test --compact tests/Feature/P0BiomixingAutomatedEvidenceTest.php`

| Chiều | Bước kiểm tra | Kỳ vọng |
| ----- | ------------- | ------- |
| Production → Warehouse | Mở Production order → batch detail | Batch load được, đúng company/context |
| Production → Warehouse | Mở Trace (`production.batches.trace`) | Có movement và link warehouse batch |
| Production → Warehouse | Bấm warehouse batch link (`warehouse.product-batches.show`) | URL `/account/warehouse-product-batches/{id}` và đúng lô |
| Warehouse → Production | Mở Warehouse → Product batches → batch detail | Batch warehouse load được |
| Warehouse → Production | Bấm link Production trace/batch nếu có | Quay lại đúng production batch/trace |
| Round-trip | Đối chiếu mã batch, order, warehouse batch | Tham chiếu hai chiều khớp |

**Evidence dev/QA:** 2026-05-24 local `https://craveva-staging.test` Pass, 0 blocking. Mini UAT: Production batch #14 trace `/account/production/batches/14/trace` có 7 link “Open warehouse batch”; warehouse product batch #17 có link “Open Production Trace” + “Open Production Batch” quay lại batch #14.
