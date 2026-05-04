# Playbook triển khai kỹ thuật — Module Production (Phase 0 + Phase 1 / MVP)

| Thuộc tính           | Giá trị                                                                                                                                                                    |
| -------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Đối tượng**        | BA, Tech Lead, dev backend trước khi mở sprint code                                                                                                                        |
| **Phạm vi**          | **Phase 0 + Phase 1** theo `BIOMIXING_PRODUCTION_DEVELOPMENT_PLAN.md` §4 — không thay roadmap; đây là **chi tiết hóa** migration, luồng, điểm chạm code, test và milestone |
| **Out of scope MVP** | CCP cứng, rework workflow, receiving QC, sampling/COA, Quality Lock đầy đủ (thuộc Phase 2–4)                                                                               |
| **Repo**             | Đã có scaffold `Modules/Production/` (`module.json`, providers, routes rỗng). Logic nghiệp vụ **chưa có**.                                                                 |
| **Cập nhật**         | 2026-05 — bổ sung §2.3 (tích hợp PO/DO); sửa bảng milestone §10                                                                                                            |

---

## 0. Tài liệu phải đọc trước (thứ tự)

1. `BIOMIXING_PRODUCTION_BASELINE_AND_PREP_2026_VI.md` — nền Hub SO/PO/DO/Warehouse + điểm tích hợp §5–6.
2. **`BIOMIXING_PRODUCTION_FLOW_CONCEPTS_VI.md`** — khái niệm RM/FG, BOM version, trừ–cộng tồn, PO vs Receive FG, reserve DO (onboarding PM/BA/dev).
3. `BIOMIXING_PRODUCTION_DEVELOPMENT_PLAN.md` — kiến trúc §3, roadmap Phase 0–1 §4, ước lượng §6.
4. `FUNC_LOGIC/ERP_SO_PO_DO_INVOICE_WAREHOUSE_QA_VERIFICATION_VI.md` — batch tồn, DO, outbound.
5. `FUNC_LOGIC/WAREHOUSE_INDEX.md`, `FUNC_LOGIC/QUY_TRINH_PO_DO_SO_INVOICE_WAREHOUSE_VI.md`.
6. Khi spike tích hợp kho: `Modules/Warehouse/Services/StockMovementService.php` và test tham chiếu dưới §9.

---

## 1. Định nghĩa “Done” cho MVP (Phase 1)

**Pilot gợi ý:** một quy trình **một SKU thành phẩm** (ví dụ mix tay 250 kg một lần — map với flowchart khách đã có).

### 1.1 Tiêu chí chấp nhận (Acceptance — kiểm chứng được trên staging)

| #   | Tiêu chí                                | Ý nghĩa                                                                                                                                                                  |
| --- | --------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| A   | **BOM version** cho `Product` FG        | Ít nhất 1 BOM “đang hoạt động”; thành phần là `Product` RM với qty + đơn vị base; có khóa phiên bản (số/version + effective dates _hoặc_ `is_default` đơn giản cho MVP). |
| B   | **Production Order + Production Batch** | Tạo lệnh SX gắn `company_id`, output `product_id` (FG), `warehouse_id` xưởng (issue/receipt), qty kế hoạch; có **production batch number** hiển thị/lưu được.            |
| C   | **Tiêu thụ RM**                         | Khi **post consumption** (một nút/action), tồn RM **giảm** trong `warehouse_product_batches` đúng lô được chọn; có bản ghi movement/ledger Warehouse (qua service).      |
| D   | **Nhận FG**                             | Sau complete, FG **xuất hiện** trên một kho (có thể cùng kho xưởng hoặc kho FG — chốt 1 luồng trong Phase 0) với **batch_number + expiry (nếu có)** do Production tạo.   |
| E   | **Truy xuất tối thiểu**                 | Trên một màn báo cáo / query nội bộ MVP: **FG batch → các dòng RM batch đã tiêu** (hoặc ngược chiều). Không yêu cầu báo cáo audit Phase 4.                               |
| F   | **Idempotency / hủy**                   | Spike + quyết định: post consumption/receipt có **reverse** được không trong MVP hay chỉ **draft → posted** không sửa; ít nhất một path **cancel draft** không đụng tồn. |

### 1.2 Ngoài phạm vi MVP (ghi rõ để tránh scope creep)

- CCP checklist, không chặn bước tự động theo HACCP.
- Receiving QC / quarantine (Purchase extension).
- Rework có approval.
- Chặn DO theo QA release / COA.
- Tự động sinh Production Order từ `Order` (có thể **link** nullable `order_id` nhưng auto-observer là Phase 3 gợi ý trong plan).

---

## 2. Ranh giới domain & rủi ro tích hợp

### 2.1 Nguyên tắc

- **`Modules/Production`** giữ **lệnh SX, BOM, allocation/complete**, và **ghi nhận nghiệp vụ**.
- **`Modules/Warehouse`** giữ **sự thật tồn kho**. Production chỉ **gọi** `StockMovementService` (hoặc API nội bộ cùng cấp) — **không** nhân đôi cập nhật `warehouse_product_batches`/ledger.
- **`app/Models/Product`** và bảng `products` là **golden master SKU** — BOM và FG vẫn trỏ về đây (migration mới ở core hoặc module tùy convention repo; playbook giả định migration có thể nằm `Modules/Production` hoặc `database/migrations` — **spike Phase 0** chốt 1 nơi để không trùng migrates).

### 2.2 Rủi ro cần spike sớm (0.5–1.5 ngày)

| Rủi ro                         | Chi tiết                                                                                                                                                       | Hướng xử lý                                                                                                                                                                                                                                  |
| ------------------------------ | -------------------------------------------------------------------------------------------------------------------------------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Outbound policy**            | `StockMovementService::executeOutboundMovement` gọi `WarehouseFlowPolicyService::assertSellableOutboundWarehouse(...)` cho nhiều `reference_type`              | Chốt `reference_type` cho tiêu hao RM (vd `production_issue` hoặc tương đương) và whitelist trong policy **hoặc** dùng kho được đánh dấu “xuất được cho SX” trong cấu hình flow — **không đoán** trước khi đọc `WarehouseFlowPolicyService`. |
| **Batch chọn tay vs FEFO**     | MVP: thường **chọn lô RM** tay (audit); service hiện hỗ trợ outbound theo batch rows                                                                           | Payload outbound: chỉ định `batch_id` / `batch_number` + `warehouse_id` theo contract `resolveOutboundRows`; spike với một RM có 2 lô.                                                                                                       |
| **FG expiry / mfg date**       | Inbound hỗ trợ optional `expiration_date`, `manufacturing_date` trên payload                                                                                   | Chuẩn hóa trường trên Production Batch (FG output) và map vào inbound.                                                                                                                                                                       |
| **Tenant & module flag**       | Bật `production` trong gói + `module_settings` + cache plugin                                                                                                  | Theo `FUNC_LOGIC/FLOW_Modules_Package_LanguagePack_CustomFields_VI.md` — không chỉ `php artisan module:enable` trong môi trường Craveva đa tenant.                                                                                           |
| **Inbound trùng nghĩa vật lý** | GRN nhập RM và Receive FG đều có thể gọi cùng kiểu **inbound** trong `StockMovementService` — nếu thiếu `reference_type`/`reference_id` rõ hoặc gọi post 2 lần | Chốt enum `reference_type` riêng cho **production_receipt** (tên cụ thể spike); test **idempotency**; không dùng GRN/PO để “giả lập” nhận FG. Chi tiết nghiệp vụ: `BIOMIXING_PRODUCTION_FLOW_CONCEPTS_VI.md`.                                |

### 2.3 Tích hợp với PO/GRN và Sales DO (không mở rộng scope MVP code Purchase)

- **Upstream RM:** MVP **không** bắt build thêm luồng PO; giả định RM đã vào kho qua **PO/GRN hiện có** (dùng chung với B2B mua hàng). Production chỉ **consume** tồn đã có.
- **Downstream bán:** Sau Receive FG, giao hàng vẫn qua **Sales DO / Invoice** hiện tại (reserve ở confirm DO, trừ FG ở ship — theo baseline Hub). Playbook không nhân đôi controller DO; chỉ cần **FG batch sẵn sàng** để gắn dòng DO.

---

## 3. ERD MVP — bảng dữ liệu đề xuất và thứ tự migration

Tên giữ **English** trong schema. Dưới đây là **đề xuất khởi đầu**; Tech Lead chỉnh sau spike khóa FK với codebase thực tế (`products`, `warehouses`, v.v.).

### 3.1 Thứ tự migration (gợi ý)

1. **`production_boms`** (+ version)
2. **`production_bom_items`** (`bom_id`, `component_product_id`, qty, `unit_id` nếu cần, sort order)
3. **`production_orders`** — header lệnh
4. **`production_batches`** — 1:N với order (MVP có thể 1:1 một batch cho pilot)
5. **`production_batch_consumptions`** — dòng RM: link batch, qty planned/actual, `warehouse_product_batch_id` hoặc (`batch_number` + product + warehouse snapshot)
6. **`production_batch_outputs`** — FG nhận: qty, FG `batch_number`, expiry, warehouse_id đích (hoặc gộp vào một bản ghi inbound reference)

Chi tiết cột và index: chỉnh trong task “ERD Phase 0” — nên có ít nhất:

- Global `company_id` trên toàn bộ bảng (align với các bảng kho/order).
- `created_by`, `updated_by`, timestamps (và optional soft deletes cho order draft).
- Unique business key theo tenant nếu cần: ví dụ `(company_id, production_batch_no)` uniqueness.

### 3.2 Liên kết nghiệp vụ

- `production_orders.product_id` (FG output SKU) hoặc `production_batches.output_product_id` — chốt 1 chỗ làm SSOT trong Phase 0.
- Optional FK: `order_id`, `project_id` nullable để báo cao không bắt buộc MVP.
- Mọi dòng có **Warehouse** FK phải thuộc **cùng company** — mirror validation trong `StockMovementService` (warehouse/product belong to company).

---

## 4. State machine (MVP đơn giản)

### 4.1 `production_orders`

| Trạng thái    | Ý nghĩa                                                                  |
| ------------- | ------------------------------------------------------------------------ |
| `draft`       | Sửa BOM snapshot / qty / kho; không ghi nhận tồn                         |
| `released`    | Locked để issued; không cho đổi BOM (hoặc version mới chỉ cho order mới) |
| `in_progress` | Đã bắt đầu (ít nhất một hoạt động consumption hoặc start timestamp)      |
| `completed`   | Đã post receipt FG + consumption balancing (quy tắc balancing spike)     |
| `cancelled`   | Chỉ từ `draft`/`released` nếu chưa phát sinh movement                    |

### 4.2 `production_batches`

- Gắn với order 1:N; MVP có thể auto-tạo 1 batch khi release.
- `completed_at`, `posted_consumptions_at`, `posted_receipt_at` hoặc tương đương để audit idempotency.

Quy tắc MVP tối thiểu: **không** cho `completed` nếu thiếu consumption posted hoặc thiếu FG inbound (configurable nếu “partial complete” không dùng).

---

## 5. Luồng nghiệp vụ MVP (technical)

```
Draft Order → Release → Allocate RM batches (manual) → Post consumption (outbound refs)
→ Post FG receipt (inbound refs) → Mark completed → (optional report trace)
```

**Ghi nhận kho (conceptual payloads)** — chỉ guideline; payload đầy đủ spike từ `StockMovementService` + các caller hiện có (Purchase/Delivery):

- **Outbound RM (consumption):** `company_id`, `warehouse_id`, `product_id`, `quantity`, `batch_number` / `batch_id` nếu API hỗ trợ, `reference_type`, `reference_id` (ProductionBatch id khuyến nghị), `company_id`-aware.
- **Inbound FG:** dùng `recordInbound` / `recordInboundBatch` với FG `batch_number`, optional `expiry`/`expiration_date`, `reference_type`, `reference_id`.

---

## 6. Điểm chạm code Craveva (checklist để chia task)

### 6.1 Trong `Modules/Production/` (phần lớn phát triển mới)

| Thành phần                                                        | Ghi chú                                                                    |
| ----------------------------------------------------------------- | -------------------------------------------------------------------------- |
| `Database/Migrations/*`                                           | Theo §3                                                                    |
| `Entities/` hoặc `App/Models/` (theo convention chốt trong spike) | Eloquent models, `CompanyScope` nếu đồng nhất codebase                     |
| `Http/Controllers/`, Routes                                       | CRUD MVP + AJAX post actions                                               |
| `Services/ProductionPostingService.php` (tên có thể đổi)          | Orchestrate: validation state → consume → inbound FG trong transaction cha |
| `Policies`, `Observers`                                           | Nếu cần authorize theo permission mới                                      |
| `Config/config.php`                                               | Flags: FG warehouse default, reference_type strings                        |

### 6.2 Core / cross-cutting (cần rà và có thể sửa)

| Khu vực                                                                                   | Việc                                                                     |
| ----------------------------------------------------------------------------------------- | ------------------------------------------------------------------------ |
| **Menu sidebar** (`resources/views/...`) hoặc nơi khai báo menu — TBD theo frontend stack | Hiển thị “Production” khi `production` được phép và module active        |
| **Permissions (`entrust`/policies)**                                                      | Keys mới ví dụ `manage_production`, `manage_production_order`            |
| **Package definition** (`packages`/Super Admin UI)                                        | Thêm `production` vào danh mụch module của gói + `packages:modules` sync |
| **`ModuleSetting`** / seed defaults                                                       | Tenant mới không bật nhầm nếu gói không có module                        |

### 6.3 Tích hợp Warehouse (đọc không sửa nếu đủ)

- `Modules\Warehouse\Services\StockMovementService` — inbound/outbound/transactions như §2 spike.
- Có thể cần **một** chỉnh nhỏ ở `WarehouseFlowPolicyService` cho `reference_type` mới (TBD spike).

### 6.4 Product BOM

- Mở rộng **`products`** chỉ là foreign keys từ `production_boms`/`production_bom_items` — không nhân đôi bảng product.
- Ước chừ **`Product`** model và admin UI (DataTable / CRUD chỗ chỉnh product) có thể cần tab “BOM” — task UI riêng.

---

## 7. UI / API MVP (phạm vi màn hình)

Tối thiểu (admin/tenant):

1. **Danh sách + filter** Production Orders.
2. **Form tạo / sửa** order (draft) — chọn FG, BOM version/snapshot, kho làm việc, qty.
3. **Màn chi tiết batch:** chọn RM lines + lô có sẵn (read tồn Warehouse).
4. Nút **Post consumption**, **Post FG receipt**, **Complete** (hoặc gộp theo spike).
5. **Báo cáo truy xuất** đơn giản — có thể trang blade + query, chưa cần export ISO.

Không MVP: native mobile API đầy đủ — nếu cần, REST mỏng sau khi luồng web ổn.

---

## 8. Kế hoạch test

### 8.1 Feature tests (Pest/Laravel — `tests/Feature`)

| Suite                   | Case                                                                                                  |
| ----------------------- | ----------------------------------------------------------------------------------------------------- |
| **Posting happy path**  | Order released → consume → inbound FG → tồn RM giảm, FG tăng đúng batch                               |
| **Company isolation**   | User/company khác không thấy/move nhầm (nếu test harness hỗ trợ multi-company)                        |
| **Insufficient stock**  | Outbound báo đúng `WarehouseBusinessException` khi không đủ qty                                       |
| **Draft cancel**        | Không có movement                                                                                     |
| **Idempotency**         | Gọi post consumption / post FG receipt 2 lần: fail rõ hoặc no-op có chủ đích (tránh double inbound).  |
| **Reference isolation** | Inbound FG có `reference_type` production; không lẫn với inbound GRN/PO trên cùng batch test fixture. |

Tham chiếu pattern test có sẵn: tests đề cập Warehouse/Sales DO upgrade/migration rehearsal trong repo (`WarehouseUpgradeP0Test`, `PurchaseInboundStockFlowTest`, v.v.) — search `StockMovementService` trong `tests/`.

### 8.2 UAT thủ công

- Áp vào **`FUNC_LOGIC/UAT_CHECKLIST_MUA_BAN_KHO_E2E_VI.md`** phần kho/Xuất-nhập: sau khi receive FG có thể tạo **Sales DO line** và gắn batch FG như QA doc (end-to-end mỏng).

---

## 9. Appendix — Payload keys (ghi nhớ spike)

Đọc thực tế trong `StockMovementService` và các service gọi nó:

- **`recordInbound`** / **`recordOutbound`**: trong transaction wrapper; outbound phải thỏa policy flow sellable/non-sellable.
- **`requireCompanyId`**: `company_id` bắt buộc trên payload.
- Các khóa thường gặp: `warehouse_id`, `product_id`, `quantity`, `batch_number`, `expiry_date`/`expiration_date` (chuẩn hóa tên trong spike giữa service và callers), `reference_type`, `reference_id`.

---

## 10. Milestone gợi ý (4–6 slot làm việc; không phải hợp đồng SLA)

| Mốc    | Nội dung có thể demo trên staging                                                                 | Đầu ra                                           |
| ------ | ------------------------------------------------------------------------------------------------- | ------------------------------------------------ |
| **M1** | Spike kho end-to-end — outbound RM + inbound FG với `reference_type` chốt; policy OK              | Memo kỹ thuật 1–2 trang (wiki hoặc `FUNC_LOGIC`) |
| **M2** | Migrations BOM + Orders + Batch + consumption/output — `migrate` sạch; models + factory tối thiểu | PR 1                                             |
| **M3** | UI draft/release + BOM snapshot                                                                   | PR 2 — list/detail có thể click                  |
| **M4** | Post consumption wired tới `StockMovementService`                                                 | PR 3 — tồn RM đổi đúng lô                        |
| **M5** | Post FG inbound + Completed                                                                       | PR 4 — FG batch trong kho, sẵn sàng gắn Sales DO |
| **M6** | Báo cáo truy xuất + test suite + checklist UAT                                                    | PR 5 — bàn giao MVP pilot                        |

Điều chỉnh số tuần theo team (1 vs 2 dev) — không trùng lặp số tuần §6 trong `BIOMIXING_PRODUCTION_DEVELOPMENT_PLAN.md`; milestone này là **chia nhỏ nội bộ trong Phase 1**.

---

_Khi MVP chạy production pilot, fork sang tài liệu Phase 2 playbook (CCP, receiving QC, rework) để không phình file này._
