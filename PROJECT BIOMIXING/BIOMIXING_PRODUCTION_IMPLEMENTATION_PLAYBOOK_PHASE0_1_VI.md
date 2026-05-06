# Playbook triển khai kỹ thuật — Module Production (Phase 0 + Phase 1 / MVP)

| Thuộc tính           | Giá trị                                                                                                                                                                                                             |
| -------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Đối tượng**        | BA, Tech Lead, dev backend trước khi mở sprint code                                                                                                                                                                 |
| **Phạm vi**          | **Phase 0 + Phase 1** theo `BIOMIXING_PRODUCTION_DEVELOPMENT_PLAN.md` §4 — không thay roadmap; đây là **chi tiết hóa** migration, luồng, điểm chạm code, test và milestone                                          |
| **Out of scope MVP** | CCP cứng, rework workflow, receiving QC, sampling/COA, Quality Lock đầy đủ (thuộc Phase 2–4)                                                                                                                        |
| **Repo**             | `Modules/Production/` — MVP: orders/batches, BOM CRUD, **snapshot BOM khi release**, sinh RM planned từ snapshot (1 batch/đơn), gán SO/Project trên form, post RM/FG, trace, FG policy. Phase 2+ (CCP, QC…) — §1.3. |
| **Cập nhật**         | 2026-05-06 — đồng bộ checklist với code: §1.3, §3.1 (snapshot), §5 luồng, §7 UI, §10 milestone; đối chiếu `FUNC_IMPROVE/01_PRODUCTION_BOM_AND_FG_POLICY_RECOMMENDATION_VI.md` §0                                    |

---

## 1.3 Trạng thái triển khai (đối chiếu §1.1 Acceptance) — **ngắn gọn**

| Tiêu chí §1.1             | Trạng thái   | Ghi chú ngắn                                                                                                                                |
| ------------------------- | ------------ | ------------------------------------------------------------------------------------------------------------------------------------------- |
| **A** BOM FG + version    | **Đủ**       | CRUD BOM + items; FK BOM trên lệnh + **snapshot dòng BOM** (`production_order_bom_snapshot_items`) + qty kế hoạch TP đóng băng khi release. |
| **B** Order + Batch       | **Đủ MVP**   | Draft/release/cancel/completed; batch code; RM/FG warehouses.                                                                               |
| **C** Tiêu thụ RM post    | **Đủ MVP**   | Outbound theo `warehouse_product_batch_id`; idempotency key.                                                                                |
| **D** Nhận FG             | **Đủ MVP**   | Inbound FG + batch_number/expiry/mfg.                                                                                                       |
| **E** Truy xuất tối thiểu | **Đủ MVP**   | Trang `batches/{id}/trace` + movements ref `ProductionBatch`.                                                                               |
| **F** Idempotency / hủy   | **Một phần** | Post idempotent (skip lần 2). **Không** reverse movement sau post; cancel order chỉ khi chưa posted RM/FG (đúng hướng MVP).                 |

### Còn thiếu so với playbook / “full” quy trình nội bộ (làm tiếp trong Phase 1 mỏng hoặc Phase 2)

1. ~~**Snapshot BOM trên lệnh**~~ — **Đã có:** chụp khi release (có BOM + ít nhất 1 dòng BOM).
2. ~~**Nổ BOM → dòng tiêu hao đề xuất**~~ — **Một phần:** nút “Create planned RM lines from BOM snapshot” trên batch khi đơn hàng chỉ có **đúng 1 batch** và chưa có dòng RM; sau đó user **gán lô RM** trước khi post. Chưa auto multi-batch chia tổng.
3. ~~**Gắn `sales_order_id` / `project_id`**~~ — **Đã có:** chọn nullable trên form tạo/sửa lệnh nháp; hiển thị trên order detail (_chưa_ auto từ SO/Observer — vẫn thủ công).
4. **Cân bằng “complete” cứng** — order `completed` khi mọi batch post FG; không chặn partial theo policy nâng cao.
5. **Phase 2+ (ngoài file này):** CCP, receiving QC, rework, quality lock DO, sampling/COA, API read cho AI — playbook **chưa viết** chi tiết Phase 2 → tách file `..._PHASE2_VI.md` khi kickoff.

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

1. **`production_boms`** (+ version) — **đã có trong repo**
2. **`production_bom_items`** — **đã có**
3. **`production_orders`** — header lệnh (gồm `sales_order_id`, `project_id` nullable) — **đã có**
4. **`production_batches`** — **đã có**
5. **`production_batch_consumptions`** — **đã có**
6. **`production_batch_outputs`** — **đã có** (+ cột variance / policy FG theo migration `2026_05_06_120000_*`)
7. **`production_order_bom_snapshot_items`** + cột snapshot trên **`production_orders`** (`bom_snapshot_at`, `bom_snapshot_planned_quantity`) — **đã có** (`2026_05_07_120000_add_production_order_bom_snapshot.php`)
8. **`production_company_fg_policies`** — chính sách FG theo company — **đã có**

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

| Trạng thái    | Ý nghĩa                                                                                                   |
| ------------- | --------------------------------------------------------------------------------------------------------- |
| `draft`       | Sửa BOM / qty / kho / SO–Project; **chưa** có snapshot (snapshot tạo khi **Release**); không ghi nhận tồn |
| `released`    | Đã chụp **BOM snapshot** (nếu có BOM + dòng BOM); header không sửa như draft; batch mở cho RM/FG          |
| `in_progress` | Đã bắt đầu (ít nhất một hoạt động consumption hoặc start timestamp)                                       |
| `completed`   | Đã post receipt FG + consumption balancing (quy tắc balancing spike)                                      |
| `cancelled`   | Chỉ từ `draft`/`released` nếu chưa phát sinh movement                                                     |

### 4.2 `production_batches`

- Gắn với order 1:N; MVP có thể auto-tạo 1 batch khi release.
- `completed_at`, `posted_consumptions_at`, `posted_receipt_at` hoặc tương đương để audit idempotency.

Quy tắc MVP tối thiểu: **không** cho `completed` nếu thiếu consumption posted hoặc thiếu FG inbound (configurable nếu “partial complete” không dùng).

---

## 5. Luồng nghiệp vụ MVP (technical)

**Đã triển khai trong code (bổ sung so với bản gốc):**

```
Draft Order (chọn FG, BOM optional, SO/Project optional, kho RM/FG, planned qty)
  → Release → [Snapshot BOM + planned FG frozen tại thời điểm release]
  → (Optional) “Planned RM từ snapshot” trên batch nếu đúng 1 batch và chưa có dòng RM
  → Gán warehouse RM batch cho từng dòng (manual / form gán lô trên UI)
  → Post consumption (outbound refs)
  → Post FG receipt (inbound refs; kiểm tra FG policy company)
  → Order completed khi mọi batch đã nhận FG đủ luật MVP → Trace batch
```

Biến thể **không BOM / không dùng nút snapshot:** vẫn thêm dòng RM thủ công như trước.

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
- **Hiện trạng repo:** BOM quản lý qua menu **Production BOMs** (`production.boms.*`), không nhét tab trong Product CRUD — chấp nhận được cho MVP; tab Product là tùy chọn UX sau.

---

## 7. UI / API MVP (phạm vi màn hình)

Tối thiểu (admin/tenant) — **trạng thái triển khai:**

1. **Danh sách + filter** Production Orders — **có**
2. **Form tạo / sửa** order (draft) — FG, BOM (optional), **SO / Project (optional)**, kho RM/FG, planned qty — **có**; snapshot **không** chỉnh trên form (tạo lúc Release).
3. **Chi tiết order** — variance tổng FG vs planned, **bảng BOM snapshot** sau release (nếu có) — **có**
4. **Màn chi tiết batch** — dòng RM (planned/actual), **gán lô RM** cho dòng chưa có batch, **nút “Create planned RM lines from BOM snapshot”** (điều kiện: 1 batch, chưa có dòng, đã release + có snapshot) — **có** (multi-batch planned: **chưa**)
5. Nút **Post consumption**, **Post FG receipt** (theo output line) — **có**; complete order theo rule “mọi batch đã post FG” — **có (MVP)**
6. **Báo cáo truy xuất** `batches/{id}/trace` — **có**
7. **Cấu hình FG quantity policy** theo company — **có** (`/account/production/fg-quantity-policy`)

Không MVP: native mobile API đầy đủ — nếu cần, REST mỏng sau khi luồng web ổn.

---

## 8. Kế hoạch test

### 8.1 Feature tests (Pest/Laravel — `tests/Feature`)

| Suite                      | Case                                                                                                                                                  |
| -------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Posting happy path**     | Order released → consume → inbound FG → tồn RM giảm, FG tăng đúng batch (**có** assert snapshot BOM khi release trong `ProductionPostingServiceTest`) |
| **BOM snapshot / planned** | Snapshot khi release; sinh planned consumption từ snapshot (1 batch); từ chối khi nhiều batch — `ProductionPostingServiceTest`                        |
| **FG policy**              | Strict / controlled / flexible — `ProductionFgQuantityPolicyServiceTest`                                                                              |
| **Company isolation**      | User/company khác không thấy/move nhầm (nếu test harness hỗ trợ multi-company)                                                                        |
| **Insufficient stock**     | Outbound báo đúng `WarehouseBusinessException` khi không đủ qty                                                                                       |
| **Draft cancel**           | Không có movement                                                                                                                                     |
| **Idempotency**            | Gọi post consumption / post FG receipt 2 lần: fail rõ hoặc no-op có chủ đích (tránh double inbound).                                                  |
| **Reference isolation**    | Inbound FG có `reference_type` production; không lẫn với inbound GRN/PO trên cùng batch test fixture.                                                 |

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

| Mốc    | Nội dung có thể demo trên staging                                                                 | Đầu ra                                           | **Trạng thái (2026-05-06)**                                                          |
| ------ | ------------------------------------------------------------------------------------------------- | ------------------------------------------------ | ------------------------------------------------------------------------------------ |
| **M1** | Spike kho end-to-end — outbound RM + inbound FG với `reference_type` chốt; policy OK              | Memo kỹ thuật 1–2 trang (wiki hoặc `FUNC_LOGIC`) | **Đã có trong code** (`ProductionBatch`, `StockMovementService`, test posting)       |
| **M2** | Migrations BOM + Orders + Batch + consumption/output — `migrate` sạch; models + factory tối thiểu | PR 1                                             | **Đã có** (+ FG policy migration + BOM snapshot migration)                           |
| **M3** | UI draft/release + snapshot BOM                                                                   | PR 2 — list/detail có thể click                  | **Đã có** (+ SO/Project form, FG policy settings, variance UI)                       |
| **M4** | Post consumption wired tới `StockMovementService`                                                 | PR 3 — tồn RM đổi đúng lô                        | **Đã có** (+ gán lô RM, planned từ snapshot MVP 1-batch)                             |
| **M5** | Post FG inbound + Completed                                                                       | PR 4 — FG batch trong kho, sẵn sàng gắn Sales DO | **Đã có** (+ kiểm tra FG policy khi post)                                            |
| **M6** | Báo cáo truy xuất + test suite + checklist UAT                                                    | PR 5 — bàn giao MVP pilot                        | **Một phần** — trace + Pest có; UAT checklist E2E với Sales DO là bước người dùng/QA |

Điều chỉnh số tuần theo team (1 vs 2 dev) — không trùng lặp số tuần §6 trong `BIOMIXING_PRODUCTION_DEVELOPMENT_PLAN.md`; milestone này là **chia nhỏ nội bộ trong Phase 1**.

---

_Khi MVP chạy production pilot, fork sang tài liệu Phase 2 playbook (CCP, receiving QC, rework) để không phình file này._

---

## 11. Phase 1 proposal readiness (Done / Partial / Missing)

Mục này đối chiếu nhanh giữa proposal Biomixing (Order Intake & Recipe Review + Planning + Production + Fulfillment) với trạng thái hệ thống hiện tại để team chốt sprint.

| Hạng mục proposal Phase 1                                                            | Done | Partial | Missing |
| ------------------------------------------------------------------------------------ | :--: | :-----: | :-----: |
| Production BOM CRUD (version, default/effective date, lock khi đã có order dùng BOM) |  ✅  |         |         |
| Production Order lifecycle (draft/release/cancel/completed)                          |  ✅  |         |         |
| Snapshot BOM tại thời điểm release                                                   |  ✅  |         |         |
| Planned RM từ snapshot + gán lô RM trước khi post                                    |      |   ✅    |         |
| Multi-batch planning nâng cao (chia planned RM theo nhiều batch/order)               |      |         |   ✅    |
| FG quantity policy (strict/controlled/flexible) + variance                           |  ✅  |         |         |
| Workflow approval riêng khi variance vượt ngưỡng (`approved_by`, `approved_at`)      |      |   ✅    |         |
| Yield factor + unit conversion nâng cao trên BOM                                     |      |         |   ✅    |
| Traceability tối thiểu (batch trace RM/FG movements)                                 |  ✅  |         |         |
| UAT E2E sâu với Sales DO/Settlement (bài test vận hành liên phòng ban)               |      |   ✅    |         |
| Estimate approval loop theo proposal (Sales -> President -> VP) ở lớp Sales/Estimate |      |   ✅    |         |
| AI Agent “check recipe history” gắn trực tiếp lúc tạo Estimate                       |      |         |   ✅    |

### Gợi ý ưu tiên sprint sau (an toàn với B2B hiện tại)

1. **Ưu tiên A (ít rủi ro):** UAT E2E sâu + approval riêng cho variance (feature flag theo company).
2. **Ưu tiên B (rủi ro trung bình):** Multi-batch planning (giữ `reference_type` riêng cho Production, không đụng luồng core PO/GRN/DO).
3. **Ưu tiên C (rủi ro cao hơn):** yield/UOM conversion engine; triển khai additive + shadow-mode trước khi bật mặc định.

---

## 12. Gap triển khai, rủi ro và cách khắc phục (để không vỡ B2B hiện tại)

### 12.1 Bảng Gap -> Risk -> Mitigation

| Gap / phần còn thiếu                                      | Rủi ro khi implement                                                                 | Cách khắc phục (bắt buộc trước go-live)                                                                                               |
| --------------------------------------------------------- | ------------------------------------------------------------------------------------ | ------------------------------------------------------------------------------------------------------------------------------------- |
| Multi-batch planning nâng cao                             | Sai phân bổ RM giữa batch, double-post movement, lệch tồn kho                        | Giữ logic mới trong `Modules/Production`; dùng `reference_type` riêng; bật theo feature flag; test idempotency + concurrent posting   |
| Approval workflow variance (`approved_by`, `approved_at`) | Chặn nhầm luồng xuất FG hoặc bypass approval                                         | Mặc định giữ behavior cũ; chỉ enforce approval khi policy company bật; thêm audit log + phân quyền rõ (`approve_production_variance`) |
| Yield factor + UOM conversion nâng cao                    | Sai công thức tính planned/actual, rounding drift, lệch cost/margin                  | Additive schema (không sửa cột cũ); chạy dual-calculation (old/new) ở shadow mode; đối chiếu 2-4 tuần trước khi chuyển mặc định       |
| UAT E2E sâu Sales DO -> Invoice                           | Hở case tích hợp liên module khi lên production                                      | Viết test checklist liên phòng ban + script data chuẩn; bắt buộc pass UAT trên staging trước rollout tenant thật                      |
| Estimate approval loop (Sales -> President -> VP)         | Trùng/đè với luồng Sales Order hiện tại, người dùng tạo SO trực tiếp bỏ qua approval | Đặt gate rõ: SO từ estimate approved hoặc quyền override; ban đầu chỉ áp dụng cho nhóm/tenant pilot; giữ fallback manual có kiểm soát |
| AI recipe history trong lúc tạo estimate                  | Gợi ý sai gây quyết định sai giá/công thức; tăng độ phức tạp vận hành                | Agent ở chế độ "assist-only"; luôn cần human confirm; log prompt/output; rollout pilot với use case hẹp trước khi mở rộng             |

### 12.2 Guardrails kỹ thuật bắt buộc

1. **Backward compatible:** migration kiểu additive, không phá schema/behavior cũ.
2. **Feature flags theo company/tenant:** bật dần, có nút tắt khẩn cấp.
3. **Isolation theo reference_type:** tách rõ Production với PO/GRN/DO để không lẫn stock ledger.
4. **Regression tests B2B:** PO/GRN/DO/Invoice phải chạy pass song song test Production.
5. **Observability:** log event quan trọng (release, post RM, post FG, variance approval) + dashboard lỗi.

### 12.3 Kế hoạch rollout an toàn (đề xuất)

- **Wave 1 (Pilot):** approval variance + UAT E2E sâu (ít rủi ro, tác động nhỏ).
- **Wave 2 (Controlled):** multi-batch planning sau khi có baseline movement ổn định.
- **Wave 3 (Advanced):** yield/UOM conversion + estimate approval + AI assist theo phạm vi tenant pilot.

---

## 13. Chu thich ro 4 phase nghiep vu (de doc nhanh, khong can suy luan)

Muc nay dien giai truc tiep 4 phase trong proposal Biomixing: phase nao la gi, dau ra nghiep vu can co, va he thong hien tai dang o muc nao.

### 13.1 Phase 1 - Order Intake & Recipe Review

**La gi:** Giai doan tiep nhan yeu cau khach hang va duyet de xuat cong thuc/gia truoc khi chot don ban.

**Dau ra mong doi:**

- Estimate/bao gia co cau truc day du.
- Luong duyet ro role (Sales -> President -> VP Pricing).
- Ket qua cuoi: approve de convert sang Sales Order, hoac reject/yeu cau sua.

**Trang thai hien tai (doi chieu proposal):** **Partial**

- Da co nen Sales va du lieu Production/BOM de tham chieu.
- Chua full luong duyet dung mau proposal + AI assist ngay trong man hinh estimate.

### 13.2 Phase 2 - Planning & Pre-Production

**La gi:** Lap ke hoach truoc san xuat: BOM, planned quantity, kho RM/FG, release lenh, san sang batch.

**Dau ra mong doi:**

- Production Order draft/release dung BOM/version.
- Snapshot BOM tai release.
- Planned RM ro rang truoc khi post.

**Trang thai hien tai:** **Gan day du MVP (Done/Partial)**

- Da co: BOM CRUD, Order lifecycle, snapshot, FG policy.
- Con partial: multi-batch planning nang cao.

### 13.3 Phase 3 - Production & QA

**La gi:** Thuc thi san xuat, ghi nhan tieu hao RM, nhap FG, kiem soat quality/compliance.

**Dau ra mong doi:**

- RM consumption posted dung lo.
- FG receipt posted dung batch.
- Kiem soat variance va checkpoint QA truoc xuat.

**Trang thai hien tai:** **Partial (core da co)**

- Da co core: post RM/FG, variance policy, traceability co ban.
- Chua full enterprise QA: approval rieng khi vuot nguong, checklist/quality lock nang cao.

### 13.4 Phase 4 - Fulfillment & Settlement

**La gi:** Giao hang va chot tai chinh.

**Dau ra mong doi:**

- Tao va ship Delivery Order (DO) theo dung FG batch.
- Tao Invoice, doi soat cong no/thanh toan.

**Trang thai hien tai:** **Partial**

- Nen B2B (SO/DO/Invoice) da co.
- Con can UAT E2E sau voi du lieu Production de chot van hanh lien phong ban.

### 13.5 Tong ket 1 dong (de dung trong hop)

**Estimate (duyet) -> Sales Order -> Production Planning -> Production Execution + QA -> DO -> Invoice**

> Luu y quan trong: He thong hien tai manh o Production MVP (Phase 2-3 core), nhung chua full theo proposal o lop commercial approval (Phase 1) va AI layer.

---

## 14. Ke hoach uu tien lam chuan Phase 1 truoc, sau do moi day manh Phase 2

Muc tieu cua section nay: neu team quyet dinh "lam chuan luong Phase 1 truoc" thi van giu duoc lien ket voi Production hien co, khong pha vo B2B core.

### 14.1 Nguyen tac kien truc (bat buoc)

1. **Production khong phu thuoc truc tiep Estimate**; Production chi nhan dau vao tu **Sales Order hop le**.
2. **Phase 1 dong vai tro gate** (duyet thuong mai + cong thuc + gia) truoc khi tao SO.
3. **Backward-compatible:** trong thoi gian chuyen tiep, cho phep co che override co kiem soat de khong dung van hanh.

### 14.2 Scope "Phase 1 First" (lam truoc)

#### A. Workflow thuong mai can co

- Trang thai Estimate ro rang: `draft -> pending_president -> pending_vp_pricing -> approved/rejected`.
- Rule convert:
    - Chi `approved` moi duoc convert sang `Sales Order`.
    - Nhom co quyen override duoc phep convert co audit.
- Audit trail day du: nguoi duyet, thoi diem, ly do reject/chinh sua.

#### B. Du lieu va lien ket

- Bo sung mapping `estimate_id -> sales_order_id` (1-1 hoac 1-n theo policy).
- Chuan hoa truong lien ket de khong refactor lai Phase 2:
    - `company_id`, `sales_order_id`, `project_id`, `reference_type`, `reference_id`.
- Khong doi nghia cac cot Production da chay.

#### C. AI o muc an toan (assist-only)

- AI recipe/pricing chi de **goi y**, khong auto-approve.
- Human confirm la bat buoc.
- Log input/output de review chat luong goi y.

### 14.3 Dieu kien "Definition of Done" cho Phase 1 First

- Co duoc luong duyet Sales -> President -> VP chay duoc tren staging.
- Convert SO bi chan dung rule neu chua duyet.
- Co override role + audit log.
- Co regression test cho Sales/Estimate/convert SO pass.
- Khong phat sinh incident tren luong B2B hien hanh (SO/DO/Invoice).

### 14.4 Sau khi xong Phase 1 moi mo rong Phase 2

Khi da dat DoD o 14.3, tiep tuc backlog Phase 2/3 theo thu tu:

1. Multi-batch planning nang cao (Production).
2. Approval variance day du (`approved_by`, `approved_at` enforcement).
3. Yield/UOM conversion nang cao.
4. UAT E2E sau voi DO -> Invoice tren tenant pilot.

### 14.5 Ke hoach rollout de xuat (1-2-2)

- **Sprint N (Phase 1 core):** workflow duyet + gate convert SO + audit + test.
- **Sprint N+1 (Phase 1 hardening):** AI assist-only + pilot nho + training.
- **Sprint N+2,N+3 (Phase 2/3 hardening):** multi-batch + variance approval + yield/UOM + UAT E2E sau.

> Quyet dinh van hanh de nghi: Chot "Phase 1 First" cho tenant pilot truoc; giu feature flag de tan suat rollout co the dieu chinh ma khong anh huong tenant khac.

### 14.6 Anh huong toi 2 luong core B2B va cach giai quyet

**Cau tra loi ngan:** Ke hoach nay **khong duoc phep** pha vo logic:

- `SO -> DO (ship/outbound) -> Invoice`
- `PO -> GRN (inbound) -> Bill`

Neu co dau hieu xung dot, phai ap dung cac bien phap sau truoc khi merge.

#### A. Bao toan luong `SO -> DO -> Invoice`

Rui ro tiem an:

- Gate moi o Phase 1 chan nham viec tao/chot SO.
- Production outbound bi nham sang logic ship DO.

Bien phap bat buoc:

1. Gate approval chi ap vao **estimate -> convert SO**, khong doi state machine DO/Invoice hien tai.
2. Khong sua contract outbound cua DO; Production dung `reference_type` rieng (`production_*`), DO giu `sales_*`.
3. Regression tests bat buoc pass:
    - tao SO tu flow cu
    - tao DO, ship outbound
    - tao invoice va doi soat so lieu.

#### B. Bao toan luong `PO -> GRN -> Bill`

Rui ro tiem an:

- Inbound FG tu Production bi nham chung nghia voi GRN inbound mua hang.
- Doi policy kho lam anh huong GRN/Bill.

Bien phap bat buoc:

1. Tach ro `reference_type` inbound:
    - GRN: `purchase_*` (hoac ten hien hanh)
    - Production FG receipt: `production_receipt`.
2. Khong thay doi mapping PO/GRN/Bill; chi bo sung additive cho Production.
3. Regression tests bat buoc pass:
    - nhap GRN vao kho
    - tao Bill/Invoice mua hang
    - doi chieu ton va cong no khong lech.

#### C. Co che "phanh an toan" khi rollout

1. Feature flag theo tenant/company cho tat ca logic moi.
2. Canary rollout: 1 tenant pilot truoc, theo doi 1-2 chu ky van hanh.
3. Co nut rollback:
    - tat gate approval moi
    - tat AI assist
    - giu nguyen flow SO/PO core.
4. Dashboard canh bao:
    - sai lech stock movement theo `reference_type`
    - mismatch giua outbound DO va inbound/receipt bat thuong.

#### D. Exit criteria truoc go-live rong

- 0 incident tren 2 luong core B2B trong pilot window.
- Toan bo regression suite B2B + Production pass.
- So lieu doi soat (stock + AR/AP) khop theo checklist finance.
