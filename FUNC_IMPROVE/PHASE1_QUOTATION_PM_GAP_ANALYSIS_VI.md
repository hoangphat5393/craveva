# Phase 1 — Báo giá / Estimate: đối chiếu PM feedback vs ERP hiện tại

_Cập nhật: 2026-05-15. Bỏ qua phần AI (theo yêu cầu PM). Không cần module Estimate riêng — nâng cấp module **Estimates (Quotation)** hiện có._

| Tài liệu                              | Đối tượng đọc   | Nội dung                                                    |
| ------------------------------------- | --------------- | ----------------------------------------------------------- |
| **`PHASE1_PM_STATUS_LIVE_VI.md`**     | **PM, sếp**     | **Tiến độ live** — đánh dấu ✅/🟡/⬜, không thuật ngữ dev   |
| **`PHASE1_QUOTATION_PM_HUMAN_VI.md`** | PM, BA, dev mới | PM muốn gì — tiếng Việt, ví dụ OEM, đã có/chưa có (dễ hiểu) |
| **File này**                          | Dev triển khai  | Gap #, URL, file code, sprint P0/P1/P2, DoD                 |

**Đọc nhanh:** §2 = đã có code. §3 = còn thiếu + trang triển khai. §3.2 = layout workspace đích. §3.3 = map trạng thái/nút PM. §4 = việc làm + thứ tự ưu tiên. §3.1 = sơ đồ URL. **PM:** xung đột task Production vs bán hàng thường → **`PHASE1_PM_STATUS_LIVE_VI.md` §F**.

---

## 1. Kết luận nhanh

| PM nói                            | Thực tế code                                                                                                       |
| --------------------------------- | ------------------------------------------------------------------------------------------------------------------ |
| Chỉ có create / send / convert SO | **Không đúng hoàn toàn** — đã có **President + VP Pricing review**, gate convert SO, **Estimate Request** (intake) |
| Thiếu toàn bộ approval workflow   | **Partial** — có 2 cửa duyệt + banner, **chưa** có workflow engine / timeline / role riêng                         |
| Cần module Estimate mới           | **Không cần** — dùng `estimates` + mở rộng                                                                         |
| Trang detail = trading quotation  | **Đúng** — dòng báo giá = sản phẩm/giá, **chưa** có BOM/recipe workspace                                           |

Luồng đích PM:

```text
Client request → Create estimate → (recipe/BOM) → Approval → Pricing approval → Convert SO
```

Luồng code hôm nay:

```text
Estimate Request (tùy chọn) → Estimate (dòng SP + giá) → Send/status thương mại
  → president_review_* + vp_pricing_review_* → Convert SO (nếu gate bật & đủ duyệt)
  → Production Order (module Production, tách biệt, qua SO)
```

---

## 2. Đã có (không cần làm lại từ đầu)

### 2.1 Module & thương mại

| Hạng mục                              | Ghi chú                                                | Trang hiện tại                                            |
| ------------------------------------- | ------------------------------------------------------ | --------------------------------------------------------- |
| Module **Estimates** (= Quotation)    | CRUD, draft / waiting / accepted / declined / canceled | `/account/estimates`, create, edit, show                  |
| Gửi báo giá, PDF, chữ ký khách        | Flow Worksuite                                         | Show → Send; PDF download; public `/estimate/{hash}`      |
| **Convert to Sales Order**            | Copy items → order                                     | Show + List → action; `orders.show`                       |
| **estimate_id** trên `orders`         | Trace SO ↔ báo giá                                     | `orders.show` (link ngược tùy UI)                         |
| Trường quotation mở rộng              | `quotation_date`, payment terms…                       | Create/Edit — `partials/quotation-extra-fields.blade.php` |
| Khách, dự án, currency, discount, tax | Form chuẩn                                             | Create/Edit estimate                                      |

### 2.2 Intake (bước 1 PM — partial)

| Hạng mục             | Ghi chú                                               | Trang hiện tại                                    |
| -------------------- | ----------------------------------------------------- | ------------------------------------------------- |
| **Estimate Request** | client, mô tả, budget, `early_requirement` → estimate | `/account/estimate-request` (index, create, show) |
| Quyền request        | permissions riêng                                     | Cùng module estimate-request                      |

### 2.3 Approval Phase 1 (bước 4–5 PM — partial)

| Hạng mục        | Ghi chú                                     | Trang hiện tại                                                                   |
| --------------- | ------------------------------------------- | -------------------------------------------------------------------------------- |
| Cột DB          | `president_review_*`, `vp_pricing_review_*` | —                                                                                |
| API             | `president-review`, `vp-pricing-review`     | Gọi từ **Show** (AJAX)                                                           |
| UI duyệt        | Banner + dropdown actions                   | **`estimates/{id}`** — `ajax/show.blade.php`, `internal-review-banner.blade.php` |
| Gate convert SO | Cả hai approve mới convert                  | Show/List action; module `estimates_phase1_review`                               |
| List badge      | President/VP badge                          | **`estimates/index`** — DataTable                                                |
| Test            | Feature + unit                              | `tests/Feature/Estimate*`                                                        |

**Nghiệp vụ duyệt (PM, không phải chỉ “bấm nút”):**

| Cửa            | PM duyệt cái gì                                               | Code hiện tại                |
| -------------- | ------------------------------------------------------------- | ---------------------------- |
| **President**  | Công thức / định mức / làm được không (sau này gắn BOM lines) | `president_review_*` + note  |
| **VP Pricing** | Giá chào, margin, có đủ lợi nhuận không                       | `vp_pricing_review_*` + note |

### 2.4 Phase 2–3 (ngoài scope trang báo giá nhưng đã có nền)

| Hạng mục                                      | Ghi chú                                                |
| --------------------------------------------- | ------------------------------------------------------ |
| Production BOM, Production Order, batch RM/FG | Module `Production` — **không** gắn trực tiếp estimate |
| SO → Production Order                         | Link qua `sales_order_id` trên production order        |

---

## 3. Còn thiếu (PM đúng — chưa có hoặc gần như không)

**Menu:** Finance / Sales → **Estimates** (UI thường gọi Quotation). URL mẫ local: `/account/estimates`, `/account/estimates/create`, `/account/estimates/{id}`. Intake: **Estimate Request** → `/account/estimate-request`.

| #   | PM yêu cầu                                      | Trạng thái                                                                       | Triển khai trên trang / module (khi làm)                                                                                                                                                                     |
| --- | ----------------------------------------------- | -------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| 1   | **BOM lines** (NL, qty, UOM, cost)              | **✅ Done (2026-05-15)** — `estimate_bom_lines` + `partials/bom-lines.blade.php` | Create/Edit/Show; đơn vị từ Product Unit Type; chưa PDF; chưa snapshot khi President approve                                                                                                                 |
| 2   | **Recipe / formulation** (MOQ, packaging, OEM…) | Không có entity gắn estimate                                                     | **Create / Edit** — block “Recipe & product” (`quotation-extra-fields` hoặc partial mới); cột/json trên `estimates` hoặc bảng `estimate_recipe_details`. **Show** — read-only cùng block.                    |
| 3   | **Liên kết Production BOM**                     | BOM chỉ ở Production                                                             | **Create / Edit estimate** — dropdown “Copy from BOM” (`production_boms`); lưu `production_bom_id` trên estimate. Tham chiếu master: **Production → Bill of Materials** (`/account/production/boms`).        |
| 4   | **Cost simulation** (material + margin)         | Chỉ sub_total/total thủ công                                                     | **Show estimate** — panel phải/dưới “Financial summary”; **Create/Edit** — tính live khi sửa BOM lines. Backend: service costing (không chỉ `EstimateTotalsCalculator` trên dòng SP).                        |
| 5   | **Margin validation** (VP)                      | VP chỉ approve/reject + note                                                     | **Show estimate** — khi VP bấm approve (`estimates.vp_pricing_review`): modal cảnh báo/chặn. Cấu hình ngưỡng: **Settings** (company) — tương tự pattern `production` FG policy.                              |
| 6   | **Workflow status thống nhất**                  | Tách `status` + 2 cột review                                                     | **List** `estimates/index` + **EstimatesDataTable** — 1 cột “Stage”; **Show** — badge đầu trang thay vì chỉ `waiting/accepted`. Map từ `president_review_*` + `vp_pricing_review_*` + `status`.              |
| 7   | **Approval timeline / audit trail**             | Chỉ lần duyệt cuối                                                               | **Show estimate** — tab/panel “Activity” dưới banner duyệt; bảng `estimate_approval_events`. Không bắt buộc trên list (chỉ icon/tooltip).                                                                    |
| 8   | **Submit for review**                           | Pending implicit khi save/send                                                   | **Show + Edit** (draft) — nút “Submit for review”; route mới `POST estimates/{id}/submit-for-review`. Không đặt trên public estimate link.                                                                   |
| 9   | **Return to sales** sau reject                  | reject → `declined`                                                              | **Show** — sau President/VP reject: trạng thái `revision_required`, nút Sales “Edit & resubmit”. **Edit** mở lại khi revision. Thông báo: notification/email (optional).                                     |
| 10  | **Role-based approver**                         | Cùng `edit_estimates`                                                            | **Settings → Roles & Permissions** — quyền mới `approve_estimate_president`, `approve_estimate_vp_pricing`. **Show** — menu duyệt chỉ hiện đúng role. Không hardcode tên chức danh.                          |
| 11  | **Recipe history search** (không AI)            | Không                                                                            | **Show + Edit estimate** — panel “Similar recipes / BOMs” (search theo NL + khách); query `production_boms` + estimates cũ. Không cần trang riêng.                                                           |
| 12  | **OEM workspace layout** (3 vùng)               | Layout quotation chuẩn                                                           | Chủ yếu **Show** `estimates/ajax/show.blade.php` — chia: Commercial \| Recipe & BOM \| Approval \| Financial. **Create/Edit** — cùng section, form 1 cột. **Index** chỉ thêm cột stage, không redesign full. |

**Không triển khai trên:** trang public ký estimate (`/estimate/{hash}`), **Orders**, **Production batch** (Phase 2+), trừ khi copy snapshot BOM sang SO/PO sau convert.

### 3.1 Sơ đồ màn hình (Phase 1 — nơi user thao tác)

```text
[Estimate Request]  /account/estimate-request
        │  (optional intake — bổ sung field OEM ở create/show request)
        ▼
[List Estimates]    /account/estimates          ← cột Stage, filter duyệt (#6)
        │
        ├─► [Create]  /account/estimates/create   ← Recipe header, BOM lines, copy BOM (#1–3, #11)
        │
        └─► [Detail]  /account/estimates/{id}     ← WORKSPACE CHÍNH (#4–12)
                ├─ Commercial (khách, số báo giá, dòng SP bán)
                ├─ Recipe & BOM lines + similar search
                ├─ Financial summary (cost, margin)
                ├─ Approval banner + timeline (#7)
                ├─ Actions: Submit review (#8) | President / VP (#5, #10) | Convert SO
                └─ Link → Sales Order /account/orders/{id} (sau convert)

[Settings] Roles & permissions + (tùy chọn) margin threshold cho VP (#5, #10)

[Production BOM master]  /account/production/boms   ← chỉ chọn/copy; không thay thế BOM trên estimate
```

| Gap #      | Màn hình chính                   | Màn hình phụ / backend                  |
| ---------- | -------------------------------- | --------------------------------------- |
| 1–4, 11–12 | **Estimate Show**                | Create, Edit (nhập liệu); PDF sau cùng  |
| 5          | **Estimate Show** (VP approve)   | Settings company                        |
| 6          | **Estimate List** + Show header  | `EstimatesDataTable`                    |
| 7          | **Estimate Show** (tab Activity) | `estimate_approval_events` + controller |
| 8–9        | **Estimate Show** + **Edit**     | Route submit / reject → revision        |
| 10         | **Settings → Roles**             | Ẩn/hiện menu trên Show                  |
| 3 (master) | Create/Edit estimate (dropdown)  | Production → BOM index                  |

### 3.2 Cấu trúc trang đích — OEM workspace (`estimates.show`)

PM muốn **một màn** `/account/estimates/{id}` gồm 4 vùng (không chỉ bảng dòng bán như invoice):

| Vùng UI                              | Nội dung                                                                              | Gap §3 liên quan          |
| ------------------------------------ | ------------------------------------------------------------------------------------- | ------------------------- |
| **Commercial** (trên)                | Khách, số báo giá, sales, ngày, **stage** duyệt, dòng SP bán (qty × giá)              | #6, #12 (phần commercial) |
| **Recipe & BOM** (giữa)              | Header MOQ/packaging/SKU; **BOM lines** (NL, qty/UOM, cost); copy BOM; similar search | #1, #2, #3, #11           |
| **Approval** (cạnh hoặc dưới recipe) | Banner President/VP; **timeline**; nút Submit / Approve / Reject / Return             | #5, #7, #8, #9, #10       |
| **Financial** (dưới)                 | Tổng material cost, giá bán, margin %; VP rule                                        | #4, #5                    |

**Tách bắt buộc:** dòng `estimate_items` = thứ khách **mua** (vd. 10,000 gói @ 2.50). `estimate_bom_lines` = **cách làm 1 gói** (đường, kem, cà phê…).

### 3.3 Map trạng thái & nút PM → gap / code

**Trạng thái PM mong muốn** (hiển thị 1 label “Stage” cho user — §6):

| Stage (PM)         | Ý nghĩa                   | Gần với code hiện tại / cần thêm                   |
| ------------------ | ------------------------- | -------------------------------------------------- |
| Draft              | Sales đang soạn           | `status = draft`                                   |
| Pending President  | Chờ TGĐ duyệt công thức   | `president_review_status = pending`                |
| Pending VP Pricing | Chờ VP duyệt giá          | President approved + `vp_pricing_status = pending` |
| Approved           | Đủ duyệt, được convert SO | Cả hai `approved`                                  |
| Revision required  | Từ chối, Sales sửa lại    | **Chưa có** — hiện reject → `declined` (#9)        |
| Converted          | Đã tạo SO                 | `orders.estimate_id` có giá trị                    |

**Nút PM → gap:**

| Nút (PM)                         | Gap #       | Ghi chú triển khai                     |
| -------------------------------- | ----------- | -------------------------------------- |
| Gửi duyệt                        | #8          | Route `submit-for-review`; chỉ draft   |
| Approve / Reject (President)     | #10, #7     | Đã có AJAX; thêm quyền + timeline      |
| Approve pricing / Trả Sales (VP) | #5, #9, #10 | Đã có VP review; thêm margin rule (#5) |
| Convert to SO                    | (gate có)   | Sau approved; message nếu chặn         |

---

## 4. Cần bổ sung (ưu tiên theo Phase 1 PM)

### Thứ tự implement khuyến nghị (đồng bộ `PHASE1_QUOTATION_PM_HUMAN_VI.md` §7)

| Bước  | Việc                                                         | §4 chi tiết | Lý do                                      |
| ----- | ------------------------------------------------------------ | ----------- | ------------------------------------------ |
| **1** | BOM lines + DB + UI create/edit/show                         | §4.2        | Không có recipe thì duyệt/costing vô nghĩa |
| **2** | Recipe header (MOQ, packaging…)                              | §4.2        | Bổ sung §3.2 giữa                          |
| **3** | Workflow: stage, submit review, timeline, quyền, reject loop | §4.1        | Hoàn thiện partial đã có                   |
| **4** | Cost / margin panel + rule VP                                | §4.3        | Financial summary                          |
| **5** | Copy Production BOM + similar search                         | §4.2, §4.3  | Tiện Sales                                 |
| **6** | Đổi tên menu OEM / dịch nút duyệt                            | §4.4        | UX cuối                                    |

Màn hình trọng tâm mọi bước: **`/account/estimates/{id}`**.

### 4.1 P0 — Workflow & gate (hoàn thiện partial hiện có)

| Việc                      | Mô tả ngắn                       | Trang / file triển khai              | Gap §3        |
| ------------------------- | -------------------------------- | ------------------------------------ | ------------- |
| Trạng thái workflow rõ    | `workflow_stage` hoặc map review | **List** + **Show** header           | #6            |
| Nút **Submit for review** | Sales gửi duyệt chủ động         | **Show/Edit** + route mới            | #8            |
| Quyền riêng               | President / VP permission        | **Settings → Roles** + **Show** menu | #10           |
| Audit log                 | `estimate_approval_events`       | **Show** tab Activity                | #7            |
| Reject loop               | `revision_required`              | **Show** + **Edit**                  | #9            |
| Chặn convert SO           | Message rõ                       | **Show/List** convert action         | (gate có sẵn) |

### 4.2 P0 — Dữ liệu recipe trên estimate (core manufacturing)

| Việc                                   | Mô tả ngắn                              | Trang / file triển khai                                                                 | Map gap §3     |
| -------------------------------------- | --------------------------------------- | --------------------------------------------------------------------------------------- | -------------- |
| Section **BOM lines**                  | RM/semi/packaging, qty/UOM/cost         | **Create/Edit/Show:** `partials/bom-lines.blade.php`; `EstimateController` store/update | #1, #12        |
| Header recipe                          | MOQ, packaging, target price…           | **Create/Edit:** `quotation-extra-fields.blade.php`; **Show:** block tóm tắt            | #2             |
| **Snapshot BOM** khi President approve | Đóng băng công thức                     | `presidentReview()`; **Show** — “BOM snapshot at approval”                              | #2, #4         |
| Link `production_bom_id`               | Chọn BOM master                         | **Create/Edit** dropdown; list từ `/account/production/boms`                            | #3             |
| Intake OEM (request)                   | MOQ, recipe text trước khi tạo estimate | `estimate-requests/ajax/create`, `edit`, `show`                                         | #2 (bước 1 PM) |

### 4.3 P1 — Costing & pricing review

| Việc                    | Mô tả ngắn      | Trang / file triển khai        | Gap §3  |
| ----------------------- | --------------- | ------------------------------ | ------- |
| Material cost, margin % | Panel tài chính | **Show** + **Edit**            | #4, #12 |
| Rule VP                 | Ngưỡng margin   | **Show** VP approve + Settings | #5      |
| So sánh BOM/estimate cũ | Search thủ công | **Show/Edit** panel Similar    | #11     |

### 4.4 P2 — UX / naming (PM)

| Việc                                          | Mô tả ngắn                                    | Trang / file triển khai                                                        |
| --------------------------------------------- | --------------------------------------------- | ------------------------------------------------------------------------------ |
| Menu/label **Sales Estimate / OEM Quotation** | Language Pack                                 | `Modules/LanguagePack/.../modules.php` (estimates); sidebar menu lang          |
| Layout 3–4 vùng                               | Commercial \| Recipe \| Approval \| Financial | Chủ yếu refactor **`estimates/ajax/show.blade.php`**; create/edit cùng section |
| Nút duyệt dịch                                | Bỏ hardcode EN                                | **`show.blade.php`** menu + `modules.estimates.*` trong Language Pack          |

---

## 5. Cần sửa (đã có nhưng chưa đúng ý PM)

| Vấn đề                   | Hiện trạng                      | Hướng sửa                    | Trang / file sửa                                   |
| ------------------------ | ------------------------------- | ---------------------------- | -------------------------------------------------- |
| PM nghĩ "chỉ convert SO" | Review có nhưng không lộ        | Badge stage + filter         | **List** `index` + DataTable; **Create** hint text |
| Hai lớp status gây rối   | `status` + `president_review_*` | 1 label “Stage” cho user     | **Show** header, **List** cột                      |
| Legacy estimate          | null review = coi đã duyệt      | Giữ banner legacy            | `partials/internal-review-banner.blade.php`        |
| Dòng SP ≠ nguyên liệu    | Chỉ FG/product lines            | `estimate_bom_lines`         | **Create/Edit/Show** — tách khỏi bảng dòng SP      |
| Estimate Request mỏng    | description + budget            | MOQ, recipe, attachment      | `estimate-requests/ajax/create`, `edit`, `show`    |
| Production tách module   | BOM chỉ Production              | Copy snapshot khi convert SO | **Estimate approve** + **Orders**                  |
| Totals estimate          | Calculator trên items           | Gồm BOM cost                 | `show.blade.php` + store/update estimate           |

---

## 6. Bảng map bước PM → ERP

| Bước PM                 | Đã có            | Thiếu / cần làm              | Trang vận hành chính                         |
| ----------------------- | ---------------- | ---------------------------- | -------------------------------------------- |
| 1 Client request        | Estimate Request | Form OEM đủ field            | `/account/estimate-request` (create/show)    |
| 2 Sales create estimate | Estimate CRUD    | Recipe + BOM lines           | `/account/estimates/create`, `/edit`         |
| 3 AI recipe check       | —                | **Bỏ qua**                   | —                                            |
| 4 President review      | API + UI banner  | Timeline; role; snapshot BOM | `/account/estimates/{id}` (show)             |
| 5 VP pricing review     | API + UI         | Margin panel + rule          | Cùng **show estimate**                       |
| 6 Convert SO            | Có + gate        | Link SO sau convert          | **Show** + **Orders** `/account/orders/{id}` |

---

## 7. Không làm (theo PM / kiến trúc repo)

- Module **Estimate** tách riêng khỏi Quotation.
- AI auto-approve / AI layer trong scope tài liệu này.
- Đổi contract **SO → DO → Invoice** hoặc **PO → GRN** (giữ nguyên B2B core).
- Production phụ thuộc trực tiếp `estimate_id` — chỉ qua **Sales Order** đã duyệt.

---

## 8. Definition of Done gợi ý (Phase 1 quotation workspace)

| #   | Tiêu chí                                           | Kiểm trên trang                                                         |
| --- | -------------------------------------------------- | ----------------------------------------------------------------------- |
| 1   | **BOM lines + cost + margin + stage** cùng một màn | `/account/estimates/{id}` (show) — 4 vùng layout                        |
| 2   | President → VP → convert SO + **timeline**         | Show: duyệt + tab Activity; List: badge stage                           |
| 3   | Convert SO chặn + message                          | Show/List khi chưa approve; thử `convert_to_sales_order`                |
| 4   | Regression 3 loại estimate                         | Legacy (null review), trading (không BOM), OEM (full BOM) — create/show |
| 5   | Nhãn **Sales Estimate / OEM**                      | Menu sidebar + Language Pack; refresh lang cache                        |

---

## 9. File tham chiếu code

| Màn hình (route name)                   | View chính                                  | Controller                                 |
| --------------------------------------- | ------------------------------------------- | ------------------------------------------ |
| List quotations (`estimates.index`)     | `resources/views/estimates/index.blade.php` | `EstimateController@index`                 |
| Create (`estimates.create`)             | `estimates/ajax/create.blade.php`           | `EstimateController@create/store`          |
| Edit (`estimates.edit`)                 | `estimates/ajax/edit.blade.php`             | `EstimateController@edit/update`           |
| **Detail workspace** (`estimates.show`) | **`estimates/ajax/show.blade.php`**         | `EstimateController@show` + review/convert |
| Estimate request                        | `estimate-requests/ajax/*.blade.php`        | `EstimateRequestController`                |
| Production BOM (master)                 | `Modules/Production/Resources/views/boms/*` | `ProductionBomController`                  |

| Khu vực                     | Path                                                                                           |
| --------------------------- | ---------------------------------------------------------------------------------------------- |
| Model estimate              | `app/Models/Estimate.php`, `EstimateItem.php`, `EstimateRequest.php`                           |
| Banner duyệt                | `resources/views/estimates/partials/internal-review-banner.blade.php`                          |
| Routes review / convert     | `routes/web.php` — `estimates.president_review`, `vp_pricing_review`, `convert_to_sales_order` |
| Migration review            | `database/migrations/2026_05_06_150500_add_internal_review_columns_to_estimates_table.php`     |
| Playbook                    | `FUNC_IMPROVE/BIOMIXING_PLAYBOOK_P0P1_VI.md` §13–14                                            |
| PM (dễ đọc)                 | `FUNC_IMPROVE/PHASE1_QUOTATION_PM_HUMAN_VI.md`                                                 |
| President vs VP (nghiệp vụ) | `PROJECT BIOMIXING/PHASE_BUSINESS_CONTEXT_EXAMPLE.md`                                          |

---

_Tóm lại: **không thiếu module báo giá**; **đã có** President + VP + gate convert SO; **thiếu lớn nhất** BOM lines + costing + workspace layout + timeline/workflow rõ. Chi tiết nghiệp vụ: `PHASE1_QUOTATION_PM_HUMAN_VI.md`. Chi tiết kỹ thuật: file này._
