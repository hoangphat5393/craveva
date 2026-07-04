# Ma trận test case — Đề xuất Biomixing (Phase 1 + Phase 2)

| Thuộc tính          | Giá trị                                                                                             |
| ------------------- | --------------------------------------------------------------------------------------------------- |
| **Status**          | `ready` — ma trận UAT + ánh xạ test tự động                                                         |
| **Phạm vi**         | Production + Purchase + Sales (khớp playbook Biomixing)                                             |
| **Cập nhật**        | 2026-05-07 — chốt `planned_quantity`; shadow tuỳ chọn (`yield_uom_shadow_enabled` = false mặc định) |
| **Kết quả tự động** | Xem §7 (cập nhật sau mỗi lần `php artisan test` theo cụm file bên dưới).                            |

---

## 1) Mục tiêu

- Xác nhận mức độ hoàn thành chức năng so với proposal và playbook.
- Regression an toàn cho hai luồng lõi:
    - `SO → DO → Invoice`
    - `PO → GRN → Bill`
- Cung cấp checklist UAT cho PM/QA và **ánh xạ rõ** tới test Pest đã có trong repo.

---

## 2) Ma trận test case (UAT / chức năng)

| ID         | Nhóm              | Kịch bản                                               | Điều kiện tiên quyết                         | Bước (tóm tắt)           | Kết quả mong đợi                                           | Ưu tiên  | Ghi chú                                               |
| ---------- | ----------------- | ------------------------------------------------------ | -------------------------------------------- | ------------------------ | ---------------------------------------------------------- | -------- | ----------------------------------------------------- |
| BIO-TC-001 | BOM               | Tạo BOM với FG + ≥2 RM hợp lệ                          | Có SKU FG, RM                                | Tạo BOM, thêm dòng, lưu  | BOM + dòng lưu đúng tenant                                 | High     | Có Pest (§3)                                          |
| BIO-TC-002 | BOM               | Không cho RM trùng FG                                  | Form BOM                                     | Chọn FG=A, RM=A          | UI chặn; submit báo lỗi                                    | High     | Chủ yếu **manual/UI** (+ guard server nếu có)         |
| BIO-TC-003 | BOM / Menu        | Nhãn menu BOM thân thiện                               | Module Production bật                        | Mở menu BOM              | Hiển thị "Bill of Materials"                               | Medium   | Manual/UI                                             |
| BIO-TC-004 | Order             | Tạo Production Order draft                             | BOM active                                   | Tạo lệnh draft           | Lưu OK, status draft                                       | High     | Có Pest HTTP (§3)                                     |
| BIO-TC-005 | Order             | Release → snapshot BOM                                 | Order draft có BOM                           | Release                  | `bom_snapshot_*` + freeze planned qty                      | High     | Có Pest (§3)                                          |
| BIO-TC-006 | RM Planning       | Sinh planned RM từ snapshot                            | Released, đúng 1 batch                       | Apply snapshot → batch   | `planned_quantity` = định mức × TP (không bắt buộc shadow) | High     | Có Pest (§3)                                          |
| BIO-TC-007 | RM Posting        | Post tiêu hao RM đủ tồn 1 lô                           | Batch RM đủ                                  | Post consumption         | Outbound, trừ tồn đúng                                     | High     | Có Pest (§3)                                          |
| BIO-TC-008 | RM Posting        | Chia RM qua nhiều warehouse batch                      | Nhiều lô mỗi lô không đủ tổng đủ             | Post consumption         | Split allocation, không "insufficient" sai                 | High     | Có Pest (§3)                                          |
| BIO-TC-009 | RM Posting        | Idempotency post RM                                    | Đã post 1 lần                                | Post lần 2               | Không trừ tồn thêm                                         | High     | Có Pest (§3)                                          |
| BIO-TC-010 | FG                | Ghi FG output                                          | RM đã post                                   | Thêm output              | Lưu output OK                                              | High     | Cùng flow Pest RM→FG (§3)                             |
| BIO-TC-011 | FG Policy         | Variance vượt ngưỡng, chưa approve                     | `enforce_variance_approval` on               | Post FG receipt          | Bị chặn + message approval                                 | High     | Có Pest (§3)                                          |
| BIO-TC-012 | FG Policy         | Approve variance rồi post FG                           | Output đã approve                            | Post receipt             | Inbound FG OK                                              | High     | Có Pest (§3)                                          |
| BIO-TC-013 | Traceability      | Truy xuất batch                                        | Batch đã RM/FG                               | Mở trace                 | Đầy đủ ref movement (manual hoặc query)                    | High     | Chưa có Pest dedicates; kiểm UAT/UI                   |
| BIO-TC-014 | Receiving QC      | GRN: QC rejected/pending không inbound                 | `receiving_qc_enforced` on                   | Receive với QC rejected  | Không tăng tồn dòng đó                                     | High     | Có Pest (§3)                                          |
| BIO-TC-015 | Receiving QC      | GRN: QC accepted inbound                               | Cùng cấu hình                                | QC accepted              | Inbound đúng                                               | High     | Có Pest inbound (§3)                                  |
| BIO-TC-016 | Rework            | Tạo yêu cầu rework                                     | Batch tồn tại                                | Submit request           | `requested` + qty/reason                                   | Medium   | Có Pest (§3)                                          |
| BIO-TC-017 | Rework            | Approve/reject/complete rework                         | Có rework requested                          | Transitions              | Đúng state machine                                         | Medium   | Có Pest (§3)                                          |
| BIO-TC-018 | Sales Lock        | Chặn ship DO khi production chưa complete              | `enforce_quality_lock_sales_do`              | Ship DO liên SO          | Ship bị chặn                                               | High     | Có Pest (§3)                                          |
| BIO-TC-019 | Sales Lock        | Cho ship khi linked production đã complete             | Production complete                          | Ship DO                  | Ship OK                                                    | High     | Có Pest (§3)                                          |
| BIO-TC-020 | E2E / Phase 1     | Estimate draft / approval → SO → …                     | Permission + data                            | Luồng đầy đủ             | Theo business sign-off (**Critical**)                      | Critical | Estimate draft có Pest (§3); full E2E **manual/UAT**  |
| BIO-TC-021 | Shadow (tuỳ chọn) | `planned_quantity_shadow` + UOM/yield chỉ khi flag bật | `yield_uom_shadow_enabled` = true + data UOM | Release + apply snapshot | Shadow khớp công thức trong test                           | Medium   | Có Pest; **mặc định không chạy trên tenant tắt flag** |

---

## 3) Ánh xạ test tự động (Pest / `tests/Feature`)

| BIO-TC-ID                     | File test                                   | Case (`it(...)`)                                                                                                                                          |
| ----------------------------- | ------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------- |
| BIO-TC-001                    | `ProductionBomPersistenceTest.php`          | `persists BOM items for a tenant bom`                                                                                                                     |
| BIO-TC-004, 005               | `ProductionBomAndOrderTenantFlowTest.php`   | `creates BOM and draft production order over HTTP…` (+ bước release nếu test mở rộng; hiện tập trung create draft + BOM HTTP)                             |
| BIO-TC-005, 006, 007–010, 009 | `ProductionPostingServiceTest.php`          | `does not create BOM snapshot…`, `creates planned consumption lines…`, `posts RM consumption then FG receipt…`, `skips posting consumptions again…`, v.v. |
| BIO-TC-008                    | `ProductionPostingServiceTest.php`          | `splits RM consumption across multiple warehouse batches when needed`                                                                                     |
| BIO-TC-009                    | `ProductionPostingServiceTest.php`          | `skips posting consumptions again when batch already posted`                                                                                              |
| BIO-TC-011, 012               | `ProductionPostingServiceTest.php`          | `requires variance approval…`, `posts FG receipt after variance is approved`                                                                              |
| FG policy chi tiết            | `ProductionFgQuantityPolicyServiceTest.php` | Các case strict/controlled/flexible                                                                                                                       |
| BIO-TC-014                    | `PurchaseInboundStockFlowTest.php`          | `skips inbound stock for rejected QC lines…`                                                                                                              |
| BIO-TC-015                    | `PurchaseInboundStockFlowTest.php`          | `posts inbound stock when inbound DO is received…`                                                                                                        |
| BIO-TC-016, 017               | `ProductionReworkWorkflowTest.php`          | `stores and transitions rework order statuses`                                                                                                            |
| BIO-TC-018, 019               | `SalesDoServiceLifecycleTest.php`           | `blocks ship when linked production order is not completed`, `allows ship when linked production orders are completed`                                    |
| BIO-TC-020 (partial)          | `EstimateStoreDraftTest.php`                | `stores estimate draft when first item row is blank…`                                                                                                     |
| BIO-TC-021                    | `ProductionPostingServiceTest.php`          | `computes planned_quantity_shadow using UOM conversion…`                                                                                                  |

---

## 4) Phiếu chạy UAT (copy theo đợt)

| ID         | Owner | Môi trường                   | Pass/Fail/Blocked     | Bằng chứng (URL/screenshot/log)                                                                                                                                                            | Ghi chú                        |
| ---------- | ----- | ---------------------------- | --------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ | ------------------------------ |
| BIO-TC-001 |       | Local/Staging                |                       |                                                                                                                                                                                            |                                |
| BIO-TC-002 | Agent | craveva-staging.test (local) | **Pass**              | `https://craveva-staging.test/account/production/boms/create` — FG=Computer → thử RM=cùng id; MCP `select_option` không giữ được RM trùng FG; validator server `StoreProductionBomRequest` | MCP Browser                    |
| BIO-TC-003 | Agent | craveva-staging.test (local) | **Pass**              | `https://craveva-staging.test/account/production/boms/create` — sidebar **Bill of Materials** (EN: `menuBillOfMaterials` → "Bill of Materials")                                            | VI locale khác label           |
| BIO-TC-004 |       |                              |                       |                                                                                                                                                                                            |                                |
| BIO-TC-005 |       |                              |                       |                                                                                                                                                                                            |                                |
| BIO-TC-006 |       |                              |                       |                                                                                                                                                                                            |                                |
| BIO-TC-007 |       |                              |                       |                                                                                                                                                                                            |                                |
| BIO-TC-008 |       |                              |                       |                                                                                                                                                                                            |                                |
| BIO-TC-009 |       |                              |                       |                                                                                                                                                                                            |                                |
| BIO-TC-010 |       |                              |                       |                                                                                                                                                                                            |                                |
| BIO-TC-011 |       |                              |                       |                                                                                                                                                                                            |                                |
| BIO-TC-012 |       |                              |                       |                                                                                                                                                                                            |                                |
| BIO-TC-013 | Agent | craveva-staging.test (local) | **Pass**              | `https://craveva-staging.test/account/production/batches/11/trace` — RM consumptions→warehouse batch; Stock movements Outbound RM; Inbound FG                                              | Batch 11 đã post RM+FG (DB)    |
| BIO-TC-014 |       |                              |                       |                                                                                                                                                                                            |                                |
| BIO-TC-015 |       |                              |                       |                                                                                                                                                                                            |                                |
| BIO-TC-016 |       |                              |                       |                                                                                                                                                                                            |                                |
| BIO-TC-017 |       |                              |                       |                                                                                                                                                                                            |                                |
| BIO-TC-018 |       |                              |                       |                                                                                                                                                                                            |                                |
| BIO-TC-019 |       |                              |                       |                                                                                                                                                                                            |                                |
| BIO-TC-020 | Agent | craveva-staging.test (local) | **Blocked / Partial** | `https://craveva-staging.test/account/proposals` — danh sách + link Create Proposal; **chưa** chạy Estimate→approve→SO→DO→Invoice E2E                                                      | Cần phiên UAT riêng + sign-off |
| BIO-TC-021 |       |                              |                       |                                                                                                                                                                                            | Chỉ khi bật shadow             |

---

## 5) Gaps / backlog sau Phase 1–2 (`planned_quantity`)

- **Yield/UOM nâng cao / shadow:** `FUNC_IMPROVE/11_SHADOW_YIELD_UOM_PLANNED_ANALYSIS.md` — chỉ bật khi có xác nhận.
- Rework: state machine lõi; role matrix chi tiết có thể bổ sung sau.
- UAT liên phòng ban (finance, warehouse, sales): biên bản sign-off.
- Dashboard theo `reference_type`: backlog go-live.

---

## 6) Definition of Done (Phase 1 + 2 với `planned_quantity`)

- Mọi case **High** + **Critical** Pass trên staging (UAT).
- **0** blocker regression hai luồng B2B lõi.
- Biên bản UAT + rollback/feature-flag đã kiểm chứng.
- **Không** yêu cầu BIO-TC-021 Pass để đóng phase nếu shadow đang tắt theo chính sách.

---

## 7) Kết quả chạy test tự động (cụm Biomixing — cập nhật sau khi chạy CI/local)

**Lệnh đề xuất (PowerShell, một dòng):**

```powershell
php artisan test --compact `
  tests/Feature/ProductionPostingServiceTest.php `
  tests/Feature/ProductionReworkWorkflowTest.php `
  tests/Feature/PurchaseInboundStockFlowTest.php `
  tests/Feature/SalesDoServiceLifecycleTest.php `
  tests/Feature/ProductionBomPersistenceTest.php `
  tests/Feature/ProductionBomAndOrderTenantFlowTest.php `
  tests/Feature/ProductionFgQuantityPolicyServiceTest.php `
  tests/Feature/EstimateStoreDraftTest.php
```

**Lần chạy gần nhất:**

| Ngày       | Môi trường | Tests            | Passed | Failed | Ghi chú                                             |
| ---------- | ---------- | ---------------- | ------ | ------ | --------------------------------------------------- |
| 2026-05-07 | local      | 40 case (8 file) | **40** | **0**  | `php artisan test --compact` cụm §7; 122 assertions |
