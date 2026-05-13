# Báo cáo Kế hoạch Phát triển Chức năng Production — Craveva ERP (Laravel/PHP)

| Thuộc tính            | Giá trị                                                                                                                                                                                                                                                                                                                                                                                                                                         |
| --------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Vai trò**           | Senior ERP Architect & Project Manager                                                                                                                                                                                                                                                                                                                                                                                                          |
| **Tham chiếu**        | `BIOMIXING_FLOW_CRACEVA_GAP.md`, `BIOMIXING_GAP_ANALYSIS.md`; **baseline nền 2026:** `BIOMIXING_BASELINE_PREP_2026_VI.md`; **luồng & khái niệm:** `BIOMIXING_FLOW_CONCEPTS_VI.md`; playbook kỹ thuật Phase 0–1 MVP: **`BIOMIXING_PLAYBOOK_P0P1_VI.md`**; rà soát tài liệu cũ: `BIOMIXING_DOC_AUDIT_2026_VI.md`; POC: `BIOMIXING_PROTOTYPE_PLAN_VI.md` |
| **Phạm vi**           | Dự án ERP đa tenant, module `Modules/*`, core `app/`                                                                                                                                                                                                                                                                                                                                                                                            |
| **Trạng thái**        | Bản kế hoạch — cập nhật khi scope khách hàng thay đổi                                                                                                                                                                                                                                                                                                                                                                                           |
| **Cập nhật gần nhất** | 2026-04 — đối chiếu repo; multi-warehouse & batch warehouse đã có trên codebase (khác bản nháp đầu khi nền kho chưa hoàn thiện).                                                                                                                                                                                                                                                                                                                |

---

## 1. Đánh giá hiện trạng

### 1.1 Bối cảnh kỹ thuật

- **Stack:** Laravel (PHP), multi-tenant theo `company_id`, permission theo role/module.
- **Hai lớp mã:** (1) **`nwidart/laravel-modules`** — package dưới `Modules/` (ví dụ `Purchase`, `Warehouse`, `Asset`). (2) **Core `app/`** — Sales/Orders, Project, Product, Delivery, Invoice, User… **không** phải mỗi khối nghiệp vụ đều là một folder `Modules/<Tên>`.
- **Luồng nghiệp vụ chuẩn đã có:** Order → Delivery Order → Invoice → Payment (xem `MASTER_DOCUMENTATION.md`).

### 1.1a Bản đồ nhanh: đâu là `Modules/`, đâu là `app/`

| Khối nghiệp vụ                          | Gói code chính                        | Ghi chú                                                                                            |
| --------------------------------------- | ------------------------------------- | -------------------------------------------------------------------------------------------------- |
| **Purchase**                            | `Modules/Purchase`                    | Module nwidart                                                                                     |
| **Warehouse**                           | `Modules/Warehouse`                   | Module nwidart; đa kho + batch tồn (`warehouse_product_batches`) đã có migration/entity trong repo |
| **Asset**                               | `Modules/Asset`                       | Tùy bật                                                                                            |
| **Sales / Orders / Finance / Delivery** | `app/Models`, controllers core        | CRM, Order, Invoice, Delivery Order…                                                               |
| **Projects**                            | `app/Models/Project.php`, Task…       | Không nhầm với `Modules/ProjectRoadmap`                                                            |
| **Product**                             | `app/Models/Product.php` (`products`) | Chưa có BOM chuẩn — không phải `Modules/Product`                                                   |

**Module `Production`:** Đã có **scaffold** trong `Modules/Production/` (`module.json`, providers, routes trống); **chưa có** domain BOM/lệnh SX/tích hợp kho — triển khai theo playbook Phase 0–1 và §3 dưới đây.

### 1.2 Module hiện có và mức hỗ trợ quy trình sản xuất

| Module                   | Vị trí / thực thể chính                                                                                       | Hỗ trợ sản xuất (Biomixing / HACCP-style)                                                                                                                                                                                                                                                                                                                                             |
| ------------------------ | ------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Sales (CRM)**          | Core `app/`: Leads, Deals, Estimates, **Orders**, Clients                                                     | **Đầu vào đơn hàng:** xác nhận nhu cầu, giá, SLA giao hàng. **Chưa:** gắn “recipe version” đầy đủ nếu chưa có BOM chuẩn hóa.                                                                                                                                                                                                                                                          |
| **Projects**             | Core `app/`: `Project`, `Task`, milestones, timelogs, files                                                   | **Đóng vai “Production Order engine” tạm thời:** template task (cân, trộn, QC) mô phỏng từng bước. **Hạn chế:** không có **batch record** điện tử, không **routing** chuẩn MES, không **CCP gate** cứng.                                                                                                                                                                              |
| **Finance**              | Core `app/`: Invoices, Payments, Expenses                                                                     | **Sau sản xuất:** xuất hóa đơn theo giao hàng. **Không** tham gia trực tiếp shop floor.                                                                                                                                                                                                                                                                                               |
| **Product**              | Core `app/`: `Product`, categories, pricing                                                                   | **Thiếu BOM/Recipe:** chưa quản lý thành phần, tỷ lệ, phiên bản công thức — đây là nút thắt cho “company vs custom formula”.                                                                                                                                                                                                                                                          |
| **Purchase**             | `Modules/Purchase`: `PurchaseOrder`, Vendor, nhập kho liên quan PO                                            | **Đầu vào nguyên liệu:** PO, receipt. **Thiếu:** **Receiving QC** (pass/fail), quarantine, disposition gắn với lô.                                                                                                                                                                                                                                                                    |
| **Warehouse**            | `Modules/Warehouse`: `Warehouse`, movement, transfer, **đa kho**; tồn theo lô qua `warehouse_product_batches` | **Đã có:** nền **multi-warehouse**, tồn theo kho, movement/transfer, batch/HSD trên bảng batch (khác thời điểm bản kế hoạch đầu chưa có nền này). **Vẫn thiếu cho HACCP đầy đủ:** khép kín RM lô → production batch → FG → DO; **FG hold** theo QA; receiving QC; bin/location chi tiết có thể vẫn partial (xem `FUNC_IMPROVE/04_WH_RUNBOOK_UPGRADE_VI.md` — WUP-09). |
| **Delivery / Logistics** | Core `app/`: Delivery Order                                                                                   | **Xuất giao:** đóng vòng Order. **Một phần:** Quality Lock (task QC xong mới cho tạo DO) — theo `BIOMIXING_GAP_ANALYSIS.md` là mục cần **triển khai/hoàn thiện**.                                                                                                                                                                                                                     |
| **Asset** (tùy bật)      | `Modules/Asset`: thiết bị                                                                                     | **Tùy chọn:** đăng ký máy trộn 250KG; **chưa** liên kết chuỗi batch/CCP.                                                                                                                                                                                                                                                                                                              |
| **HR / Roles**           | Core: User, Employee, permission                                                                              | **Phân quyền** vai trò (giám đốc xưởng, QA). **Không** thay thế PRP (log người/xe).                                                                                                                                                                                                                                                                                                   |

**Tóm lại:** Hệ thống đã có **xương sống** Order → Project (task) → Warehouse (**đa kho + batch tồn**) → Delivery → Invoice. Phần **sản xuất có kiểm soát** (recipe, lô khép kín xuyên lệnh sản xuất, CCP, rework, QC đầu vào, sampling) **chưa có domain model riêng** — đang “mượn” Project + Task + ghi chú thủ công; **Production module vẫn là hạng mục phát triển mới**.

---

## 2. Phân tích lỗ hổng (Gap Analysis)

### 2.1 Recipe / BOM (công thức)

| Hiện trạng                                     | Rủi ro                                                                                                                                                  |
| ---------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Product không có cấu trúc BOM/ingredient chuẩn | Không tính được khối lượng nguyên liệu theo đơn; không phiên bản hóa “company formula” vs “custom formula”.                                             |
| **Đề xuất kỹ thuật**                           | Bảng quan hệ `product_bom` (hoặc tương đương): parent product (thành phẩm/bán thành), child product (nguyên liệu), số lượng, đơn vị, version, hiệu lực. |

### 2.2 Batch / Lot traceability (truy xuất nguồn gốc)

| Hiện trạng                                                                                                                                                                                                        | Rủi ro                                                                                                                                                                        |
| ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Warehouse **đã có** batch tồn (`warehouse_product_batches`) và đa kho; **chưa khép kín** trong hệ thống: **nguyên liệu lô A → batch sản xuất B → giao cho khách C** (thiếu domain Production + liên kết chứng từ) | Không đáp ứng đủ HACCP/audit cho đến khi có lệnh sản xuất + tiêu thụ/nhận FG gắn lô.                                                                                          |
| **Đề xuất kỹ thuật**                                                                                                                                                                                              | Mọi nhập kho gắn `batch_number` (và expiry nếu cần); **production consumption** ghi rõ lô tiêu thụ; **FG batch** link với production batch; DO/invoice line trỏ tới FG batch. |

### 2.3 CCP (Critical Control Point) và Rework

| Hiện trạng                                                                                                                  | Rủi ro                                                                                                                                                                                                                                                                        |
| --------------------------------------------------------------------------------------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Không có entity “CCP checkpoint”; không chặn bước sau nếu chưa ghi nhận (ví dụ CCP(2) sàng nguyên liệu trên flow Biomixing) | Vi phạm nguyên tắc HACCP; chỉ dựa vào task thủ công.                                                                                                                                                                                                                          |
| Không workflow **Rework** (lý do, duyệt, link batch gốc)                                                                    | Không kiểm soát hàng tái chế / thừa liệu quay vòng.                                                                                                                                                                                                                           |
| **Đề xuất kỹ thuật**                                                                                                        | `production_operations` hoặc `production_batch_steps` với `step_type` (CCP/OPRP/normal), `completed_at`, `result`, `operator_id`; rule: không cho **packaging / FG receipt / DO** nếu CCP bắt buộc chưa pass. Bảng `rework_orders` link `source_batch`, `reason`, `approval`. |

### 2.4 Receiving QC (kiểm tra chất lượng đầu vào)

| Hiện trạng                                                                                     | Rủi ro                                                                                                                                                               |
| ---------------------------------------------------------------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Nhận hàng chủ yếu theo PO → nhập kho; **không** có trạng thái quarantine / pass-fail tách biệt | Nguyên liệu NG có thể đã vào kho “sạch”.                                                                                                                             |
| **Đề xuất kỹ thuật**                                                                           | Trạng thái dòng nhận (hoặc stock batch): `pending_qc` / `accepted` / `rejected` / `return`; chỉ `accepted` mới putaway vào kho sử dụng; tích hợp `Purchase` receipt. |

### 2.5 Sampling & lab (lấy mẫu, COA)

| Hiện trạng                                                                                 | Rủi ro                                                                                                                                                                                              |
| ------------------------------------------------------------------------------------------ | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Không entity sampling; không upload COA / kết quả lab; QA release không gắn điều kiện file | Không đóng được yêu cầu “ủy quyền kiểm tra bên thứ ba” trên flow khách.                                                                                                                             |
| **Đề xuất kỹ thuật**                                                                       | `quality_samples` (link batch, thời điểm, số mẫu); `quality_documents` (COA PDF); cờ `qa_release` trên FG batch hoặc production batch; tùy chọn **block DO** nếu thiếu COA (config theo SKU/khách). |

### 2.6 Các lỗ hổng khác (rút gọn)

| Hạng mục                                                          | Mức độ                                                                                                                                          |
| ----------------------------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------- |
| Production batch record điện tử (operator, timestamp, actual qty) | Thiếu                                                                                                                                           |
| PRP: log người/xe (ISO 22000 prerequisite)                        | Thiếu                                                                                                                                           |
| Location: phòng nhiệt độ ổn định, tách A棟/B棟                    | **Một phần:** đa kho đã hỗ trợ **tách xưởng/khu** (A/B) bằng nhiều `warehouse`; **bin/location** chi tiết trong kho có thể vẫn backlog (WUP-09) |

---

## 3. Đề xuất kiến trúc Module

### 3.1 Câu hỏi: Tách module **Production** hay mở rộng **Projects** + **Warehouse**?

**Khuyến nghị: Tách module `Production` (hoặc `Manufacturing`) trong `Modules/`, đồng thời mở rộng có kiểm soát `Warehouse` và tích hợp `Purchase` / `Projects`.**

| Tiêu chí              | Chỉ mở rộng Projects                                                                                          | Module Production riêng                                                                                |
| --------------------- | ------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------ |
| **Ranh giới domain**  | Project = generic (IT, dịch vụ, sản xuất); nhồi BOM/CCP/rework làm **Project** phình to, khó test             | Production = bounded context rõ: batch, operation, CCP, consumption — **tách file, policy, migration** |
| **Tái sử dụng**       | Task template có thể giữ cho “milestone”                                                                      | **Work order / production order** link `project_id` _tùy chọn_ (vẫn dùng Project làm container PM)     |
| **Warehouse**         | Stock đã có sẵn; logic tiêu hao/nguyên liệu thuộc **production** không nên nhét hết vào `WarehouseController` | Service layer: `ProductionCompletionService` → gọi `StockMovementService` (đã có hướng batch)          |
| **Bền vững mã nguồn** | Refactor sau 2–3 năm rất đắt vì coupling                                                                      | Module độc lập: version, permission, feature flag; **giảm regression** trên Projects core              |

**Cách triển khai thực tế:**

1. **`Modules/Production`** (đề xuất): entities `ProductionOrder` / `ProductionBatch`, `ProductionOperation` (bước + CCP), link `order_id` hoặc `project_id`, `product_id` (recipe), `warehouse_id`.
2. **Projects:** Giữ làm **lịch / collaboration / task phụ** nếu khách quen UI Project; hoặc sync task từ production steps (read-only mirror).
3. **Warehouse:** Chỉ **mở rộng** — receiving QC status, FG hold, batch link; **không** nhân đôi logic sản xuất vào đây.
4. **Quality (Sampling/COA):** Có thể là **submodule** `Production/Quality` hoặc module `Quality` nhỏ — tránh file 5.000 dòng trong Production.

**Đóng gói thương mại:** Vẫn có thể bán là **“gói Production & Traceability”** một menu — kiến trúc bên trong **chia domain**, không bắt buộc một class God-object.

---

## 4. Lộ trình triển khai (Roadmap)

### Phase 0 — Chuẩn bị (1–2 tuần)

- Chuẩn hóa từ vựng domain (BOM, batch, CCP, rework) với khách.
- Xác định **một** flow pilot (ví dụ manual mix 250KG) làm acceptance criteria.
- Thiết kế ERD nhánh Production ↔ Warehouse ↔ Order.

### Phase 1 — Critical (nền tảng sản xuất có kiểm soát)

| Hạng mục                     | Đầu ra                                                                                   |
| ---------------------------- | ---------------------------------------------------------------------------------------- |
| **Recipe / BOM**             | Schema + UI quản lý BOM; Product FG link BOM version.                                    |
| **Batch traceability (MVP)** | Nhập/xuất/tiêu thụ gắn batch; báo cáo truy xuất một chiều (RM batch → FG batch).         |
| **Production Batch / Order** | Entity production order + batch record cơ bản (planned/actual qty, thời gian, operator). |
| **Tích hợp Warehouse**       | Tiêu hao RM, nhận FG qua service tồn kho hiện có.                                        |

### Phase 2 — High (tuân thủ HACCP / chốt chất lượng)

| Hạng mục                    | Đầu ra                                                                                          |
| --------------------------- | ----------------------------------------------------------------------------------------------- |
| **CCP checkpoints**         | Bước tùy cấu hình; không cho hoàn thành batch / không cho chuyển bước kế nếu CCP bắt buộc fail. |
| **Receiving QC**            | Trạng thái QC trên receipt; quarantine; tích hợp Purchase.                                      |
| **Rework**                  | Luồng rework có duyệt, link batch nguồn; điều chỉnh tồn có kiểm soát.                           |
| **Quality Lock (shipping)** | Validation DO: task QC / cờ QA release / tùy chọn COA (theo mức Phase 3).                       |

### Phase 3 — Medium (vận hành mở rộng)

| Hạng mục                          | Đầu ra                                                                 |
| --------------------------------- | ---------------------------------------------------------------------- |
| **Sampling + COA**                | Lấy mẫu, upload PDF, điều kiện release.                                |
| **Auto Project / template task**  | Observer từ Order → tạo Project hoặc Production Order + task mặc định. |
| **Storage conditions / location** | Custom field hoặc location type (nhiệt độ).                            |

### Phase 4 — Nâng cao (compliance & tối ưu)

| Hạng mục                             | Đầu ra                                        |
| ------------------------------------ | --------------------------------------------- |
| **PRP logs** (người/xe)              | Form / module nhẹ hoặc integration thiết bị.  |
| **Equipment link**                   | Gắn Asset với operation (optional).           |
| **Báo cáo audit**                    | Export chuỗi batch + CCP + COA cho audit ISO. |
| **Phê duyệt qua email / signed URL** | Giảm ma sát phê duyệt công thức/giá.          |

---

## 5. Kết luận

- **Hiện trạng** phù hợp **quản lý dự án + kho + bán hàng**; **chưa đủ** cho **sản xuất thực phẩm có CCP và truy xuất lô** như flow Biomixing.
- **Gap chính:** BOM, batch end-to-end, CCP/rework, receiving QC, sampling/COA.
- **Kiến trúc:** Nên **module Production** (domain riêng), **mở rộng Warehouse/Purchase**, **Quality** tách lớp mỏng; Projects có thể đồng tồn tại làm lớp PM.
- **Roadmap:** Critical → High → Medium → Advanced — ưu tiên **BOM + batch + production batch record** trước khi làm UI phức tạp sampling/PRP.

---

## 6. Ước lượng thời gian & Go-live Hub (cho PM)

**Giả định (cập nhật 2026-04):** Trên codebase **đã có multi-warehouse và tồn theo batch** (`warehouse_product_batches`) — khác bản kế hoạch ban đầu khi nền kho đa site chưa hoàn thiện. Phần **còn lại chủ yếu là code mới:** BOM, `Modules/Production`, CCP/rework, khép chuỗi tiêu hao/nhận FG, v.v. Ước lượng **Phase 1–2** vì thế **không giảm mạnh** chỉ vì đã có kho; lợi ích nằm ở **ít spike tích hợp `warehouse_id` / migration kho zero-to-one**. Giả định nhân sự: **1–2 dev backend full-time** + **0.5 QA** + PM; không tính song song nhiều dự án lớn; deploy **Hub** sau UAT trên staging.

| Mốc         | Nội dung (theo §4)                                   | Ước lượng (lịch) | Ghi chú                                                                                                                                  |
| ----------- | ---------------------------------------------------- | ---------------- | ---------------------------------------------------------------------------------------------------------------------------------------- |
| **Phase 0** | Chuẩn bị, ERD, pilot flow                            | **1–2 tuần**     | Song song với dev chuẩn bị skeleton module                                                                                               |
| **Phase 1** | BOM, batch MVP, Production order/batch, tích hợp kho | **6–10 tuần**    | Phần nặng nhất; tích hợp dựa trên **`warehouse_product_batches` + API stock đã có** — trọng tâm là **schema/UI Production + khép luồng** |
| **Phase 2** | CCP, Receiving QC, Rework, Quality lock DO           | **5–8 tuần**     | Nhiều luồng nghiệp vụ + test hồi quy Warehouse/Purchase                                                                                  |
| **Phase 3** | Sampling/COA, auto project, storage field            | **3–6 tuần**     | Tùy scope sampling/COA; có thể tách wave                                                                                                 |
| **Phase 4** | PRP, audit export, email approve…                    | **3–6 tuần**     | Tùy bắt buộc ISO; có thể làm wave 2                                                                                                      |

**Tổng hợp lịch (wall-clock):**

| Kịch bản                          | Phạm vi go-live Hub                                                                                 | Thời gian phát triển + tích hợp (ước lượng) | + UAT / fix / deploy (buffer) | **Tổng đến go-live**                                          |
| --------------------------------- | --------------------------------------------------------------------------------------------------- | ------------------------------------------- | ----------------------------- | ------------------------------------------------------------- |
| **MVP Production**                | Phase 0 + 1 (BOM + batch + production order + nhập/xuất RM/FG theo batch, **chưa** CCP cứng đầy đủ) | **~8–12 tuần**                              | **+2–3 tuần**                 | **~10–15 tuần** (~2.5–4 tháng)                                |
| **HACCP-ready (Biomixing pilot)** | Phase 0 + 1 + 2 (thêm CCP gate, receiving QC, rework, quality lock DO)                              | **~14–22 tuần**                             | **+3–4 tuần**                 | **~17–26 tuần** (~4–6.5 tháng)                                |
| **Đầy đủ theo roadmap §4**        | Thêm Phase 3 (+ Phase 4 tùy)                                                                        | **+7–13 tuần** sau Phase 2                  | **+2–4 tuần**                 | **~6–9 tháng** từ kickoff đến go-live “đủ tính năng nâng cao” |

**Điều chỉnh so với tình huống “chưa có multi-warehouse / chưa có batch tồn”:** tiết kiệm khoảng **1–2 tuần** ở hạng mục **dựng nền kho + warehouse_id** (đã xảy ra trong codebase hiện tại). **Phase 1–2** vẫn nặng vì **BOM, production batch record, CCP, receiving QC** là phát triển mới — không tự giảm chỉ nhờ đã có kho.

**Mốc go-live gợi ý cho PM:**

1. **Go-live 1 (Hub):** sau **MVP Production** — dùng nội bộ / pilot 1 xưởng, thu thập dữ liệu batch thật.
2. **Go-live 2 (Hub):** sau **Phase 2** — coi là “production module chính thức” cho khách cần HACCP gate + QC đầu vào.
3. **Go-live 3 (tùy):** Phase 3–4 — sampling/COA, PRP, audit export (theo nhu cầu).

**Rủi ro làm trễ:** đổi scope BOM/CCP giữa chừng; master data sản phẩm lộn xộn; thiếu tài liệu nghiệp vụ từ xưởng; một tenant Hub cần migration dữ liệu lô cũ.

**Điều chỉnh scope (cho PM):** Hoãn Phase 3–4 (sampling/COA, PRP…) sang wave sau khi đã go-live MVP/Phase 2 — **rút lịch** so với làm một lần đủ. **Scope và flow đã chốt** (ví dụ `manual_mixing_250kg_flowchart`) giúp **tránh làm lại** — tiết kiệm không cố định, thường **1–3 tuần** nếu trước đó hay đổi yêu cầu.

---

_Tài liệu này bổ sung cho `BIOMIXING_GAP_ANALYSIS.md` và `BIOMIXING_FLOW_CRACEVA_GAP.md` — **đọc kèm** `BIOMIXING_BASELINE_PREP_2026_VI.md` (nền SO/PO/DO/Warehouse 2026), **`BIOMIXING_FLOW_CONCEPTS_VI.md`** (khái niệm luồng & tồn kho), **`BIOMIXING_PLAYBOOK_P0P1_VI.md`** (chi tiết triển khai Phase 0–1 trước khi code), và `BIOMIXING_DOC_AUDIT_2026_VI.md`. Runbook nâng cấp kho: `FUNC_IMPROVE/04_WH_RUNBOOK_UPGRADE_VI.md`. Cập nhật khi chốt scope với khách._
