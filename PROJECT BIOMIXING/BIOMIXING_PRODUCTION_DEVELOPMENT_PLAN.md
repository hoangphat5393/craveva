# Báo cáo Kế hoạch Phát triển Chức năng Production — Craveva ERP (Laravel/PHP)

| Thuộc tính     | Giá trị                                                      |
| -------------- | ------------------------------------------------------------ |
| **Vai trò**    | Senior ERP Architect & Project Manager                       |
| **Tham chiếu** | `BIOMIXING_FLOW_CRACEVA_GAP.md`, `BIOMIXING_GAP_ANALYSIS.md` |
| **Phạm vi**    | Dự án ERP đa tenant, module `Modules/*`, core `app/`         |
| **Trạng thái** | Bản kế hoạch — cập nhật khi scope khách hàng thay đổi        |

---

## 1. Đánh giá hiện trạng

### 1.1 Bối cảnh kỹ thuật

- **Stack:** Laravel (PHP), multi-tenant theo `company_id`, permission theo role/module.
- **Mở rộng:** `nwidart/laravel-modules` — các tính năng nặng (Purchase, Warehouse) nằm dưới `Modules/`.
- **Luồng nghiệp vụ chuẩn đã có:** Order → Delivery Order → Invoice → Payment (xem `MASTER_DOCUMENTATION.md`).

### 1.2 Module hiện có và mức hỗ trợ quy trình sản xuất

| Module                   | Vị trí / thực thể chính                                                              | Hỗ trợ sản xuất (Biomixing / HACCP-style)                                                                                                                                                                |
| ------------------------ | ------------------------------------------------------------------------------------ | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Sales (CRM)**          | Leads, Deals, Estimates, **Orders**, Clients                                         | **Đầu vào đơn hàng:** xác nhận nhu cầu, giá, SLA giao hàng. **Chưa:** gắn “recipe version” đầy đủ nếu chưa có BOM chuẩn hóa.                                                                             |
| **Projects**             | `Project`, `Task`, milestones, timelogs, files                                       | **Đóng vai “Production Order engine” tạm thời:** template task (cân, trộn, QC) mô phỏng từng bước. **Hạn chế:** không có **batch record** điện tử, không **routing** chuẩn MES, không **CCP gate** cứng. |
| **Finance**              | Invoices, Payments, Expenses                                                         | **Sau sản xuất:** xuất hóa đơn theo giao hàng. **Không** tham gia trực tiếp shop floor.                                                                                                                  |
| **Product**              | `Product`, categories, pricing                                                       | **Thiếu BOM/Recipe:** chưa quản lý thành phần, tỷ lệ, phiên bản công thức — đây là nút thắt cho “company vs custom formula”.                                                                             |
| **Purchase**             | `PurchaseOrder`, Vendor, nhập kho liên quan PO                                       | **Đầu vào nguyên liệu:** PO, receipt. **Thiếu:** **Receiving QC** (pass/fail), quarantine, disposition gắn với lô.                                                                                       |
| **Warehouse**            | `Warehouse`, stock movements, transfer; tiến tới batch (`warehouse_product_batches`) | **Tồn kho thô + chuyển kho:** phù hợp RM/FG. **Một phần:** traceability theo lô đang được bổ sung; **chưa:** location kiểu phòng nhiệt độ ổn định, FG hold theo QA, receiving disposition.               |
| **Delivery / Logistics** | Delivery Order (core app)                                                            | **Xuất giao:** đóng vòng Order. **Một phần:** Quality Lock (task QC xong mới cho tạo DO) — theo `BIOMIXING_GAP_ANALYSIS.md` là mục cần **triển khai/hoàn thiện**.                                        |
| **Asset** (tùy bật)      | Thiết bị                                                                             | **Tùy chọn:** đăng ký máy trộn 250KG; **chưa** liên kết chuỗi batch/CCP.                                                                                                                                 |
| **HR / Roles**           | User, Employee, permission                                                           | **Phân quyền** vai trò (giám đốc xưởng, QA). **Không** thay thế PRP (log người/xe).                                                                                                                      |

**Tóm lại:** Hệ thống đã có **xương sống** Order → Project (task) → Warehouse → Delivery → Invoice. Phần **sản xuất có kiểm soát** (recipe, lô, CCP, rework, QC đầu vào, sampling) **chưa có domain model riêng** — đang “mượn” Project + Task + ghi chú thủ công.

---

## 2. Phân tích lỗ hổng (Gap Analysis)

### 2.1 Recipe / BOM (công thức)

| Hiện trạng                                     | Rủi ro                                                                                                                                                  |
| ---------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Product không có cấu trúc BOM/ingredient chuẩn | Không tính được khối lượng nguyên liệu theo đơn; không phiên bản hóa “company formula” vs “custom formula”.                                             |
| **Đề xuất kỹ thuật**                           | Bảng quan hệ `product_bom` (hoặc tương đương): parent product (thành phẩm/bán thành), child product (nguyên liệu), số lượng, đơn vị, version, hiệu lực. |

### 2.2 Batch / Lot traceability (truy xuất nguồn gốc)

| Hiện trạng                                                                                                                            | Rủi ro                                                                                                                                                                        |
| ------------------------------------------------------------------------------------------------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Warehouse đang có hướng batch (`warehouse_product_batches`); chưa khép kín **nguyên liệu lô A → batch sản xuất B → giao cho khách C** | Không đáp ứng yêu cầu thực phẩm / HACCP khi audit.                                                                                                                            |
| **Đề xuất kỹ thuật**                                                                                                                  | Mọi nhập kho gắn `batch_number` (và expiry nếu cần); **production consumption** ghi rõ lô tiêu thụ; **FG batch** link với production batch; DO/invoice line trỏ tới FG batch. |

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

| Hạng mục                                                          | Mức độ                                                             |
| ----------------------------------------------------------------- | ------------------------------------------------------------------ |
| Production batch record điện tử (operator, timestamp, actual qty) | Thiếu                                                              |
| PRP: log người/xe (ISO 22000 prerequisite)                        | Thiếu                                                              |
| Location: phòng nhiệt độ ổn định, tách A棟/B棟                    | Thiếu / một phần (multi-warehouse hoặc location)                   |
| AI API (ước lượng margin, check tồn theo BOM)                     | Theo `BIOMIXING_GAP_ANALYSIS.md` — Critical cho gói Biomixing + AI |

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

### Phase 3 — Medium (vận hành & AI)

| Hạng mục                          | Đầu ra                                                                                |
| --------------------------------- | ------------------------------------------------------------------------------------- |
| **Sampling + COA**                | Lấy mẫu, upload PDF, điều kiện release.                                               |
| **Auto Project / template task**  | Observer từ Order → tạo Project hoặc Production Order + task mặc định.                |
| **AI API**                        | Endpoint inventory/BOM/estimate history cho agent (theo `BIOMIXING_GAP_ANALYSIS.md`). |
| **Storage conditions / location** | Custom field hoặc location type (nhiệt độ).                                           |

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
- **Kiến trúm:** Nên **module Production** (domain riêng), **mở rộng Warehouse/Purchase**, **Quality** tách lớp mỏng; Projects có thể đồng tồn tại làm lớp PM.
- **Roadmap:** Critical → High → Medium → Advanced — ưu tiên **BOM + batch + production batch record** trước khi làm UI phức tạp sampling/PRP.

---

_Tài liệu này bổ sung cho `BIOMIXING_GAP_ANALYSIS.md` (tiếng Anh, gap pilot) và `BIOMIXING_FLOW_CRACEVA_GAP.md` (đối chiếu từng bước flow). Cập nhật ngày khi chốt scope với khách hàng._
