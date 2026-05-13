# FUNC_LOGIC — Documentation Audit & cleanup (2026-05-09; chu kỳ 2026-05-12; cập nhật 2026-05-12 §8 FUNC_IMPORT / LOG_REPORT)

Bản rà soát theo yêu cầu: **lỗi thời**, **trùng lặp**, **logic nghiệp vụ cần đọc kèm bối cảnh**, **thiếu triển khai**, **tính năng đã code chưa được ghi**. Kèm **hành động đã làm** và **việc chưa làm / cố ý giữ nguyên**. Chu kỳ **2026-05-12** (mục §7): inbound AI webhook — đồng bộ `FUNC_IMPROVE` ↔ `PM_READY` / `client_code`·`client_id`.

---

## 1) Tóm tắt điều hành

| Chủ đề                                                                       | Kết luận ngắn                                                                                                                                                                                                                                                                                                |
| ---------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| **Đường dẫn `FUNC_IMPROVE/*` thiếu tiền tố số**                              | Nhiều file trỏ tới `FUNC_IMPROVE/WAREHOUSE_RUNBOOK…`, `SO_DO_PO_GRN…`, `CLIENT_IMPORT…`, v.v. trong khi file thật là `04_`, `05_`, `08_`, `09_`, `07_`, `06_` — **đã sửa đồng bộ** trong toàn repo (`.md`).                                                                                                  |
| **File “ma” (không tồn tại)**                                                | `IMPORT_CHUNK_AND_BULK_INSERT_ANALYSIS` — không có file; **đã thay** bằng `IMPORT_CHUNK_AND_BULK_INSERT.md`. `PROJECT_MAOLIN_NEW_TIER_PRICING_IMPORT.md`, `MAOLIN_NOTES_YEU_CAU_KHACH_VA_PDF_BAN_HANG_VI.md` — **đã bỏ link**; trỏ về `PROJECT_MAOLIN_NEW_FILES_ANALYSIS.md` §4 và `MAOLIN_MASTER_GUIDE.md`. |
| **Trùng EN/VI thuần bảng**                                                   | `API_DATA_TYPE_LIST_EN.md` và `API_DATA_TYPE_LIST_VI.md` — **gộp**: giữ `API_DATA_TYPE_LIST_VI.md` làm canonical + **xóa EN**.                                                                                                                                                                               |
| **Đã triển khai, doc FUNC_LOGIC thưa**                                       | Batch kho UI + đối soát (WUP-07) có trong runbook `FUNC_IMPROVE/04_*` nhưng **mục lục kho** chưa nhấn — **đã thêm dòng** vào `WAREHOUSE_INDEX.md`.                                                                                                                                                           |
| **Production / Biomixing**                                                   | Logic triển khai **không** trong `FUNC_LOGIC` — `README.md` trỏ `FUNC_IMPROVE/BIOMIXING_PREP_INDEX_EN.md` (các file `BIOMIXING_*` cùng cấp tại `FUNC_IMPROVE/`).                                                                                                                                             |
| **`SALES_PURCHASE_FLOW.md` vs `QUY_TRINH_PO_DO_SO_INVOICE_WAREHOUSE_VI.md`** | Hai vai trò khác nhau (EN + trace code / VI + quy trình một trang) — **không gộp**.                                                                                                                                                                                                                          |
| **Các file `AUDIT_*_MODULE_VI.md`**                                          | Bản chụp kiểm tra theo thời điểm — **giữ**; khi đọc cần đối chiếu code/route hiện tại + `ERP_SO_PO_DO_INV_WH_QA_VI.md`.                                                                                                                                                                                      |

---

## 2) Hành động đã thực hiện (changelog)

1. Thay thế toàn văn **`IMPORT_CHUNK_AND_BULK_INSERT_ANALYSIS`** → **`IMPORT_CHUNK_AND_BULK_INSERT.md`** trong các `.md` liên quan.
2. Chuẩn hóa **`FUNC_IMPROVE/04_…`, `05_…`, `06_…`, `07_…`, `08_…`, `09_…`** trong các tham chiếu (gồm `FUNC_LOGIC`, `docs/`, `FUNC_IMPORT`, `FUNC_IMPROVE`).
3. **`MAOLIN_INDEX.md`:** bỏ dòng file ma; gộp “tier pricing” vào `PROJECT_MAOLIN_NEW_FILES_ANALYSIS.md` §4; row 5 → `MAOLIN_MASTER_GUIDE.md` (GAP/phụ lục).
4. **`PROJECT_MAOLIN_NEW_FILES_ANALYSIS.md`:** sửa mục đầu — bỏ pointer tới file không tồn tại.
5. **Xóa** `API_DATA_TYPE_LIST_EN.md`; cập nhật `API_DATA_TYPE_LIST_VI.md` + `INDEX.md`.
6. **`WAREHOUSE_INDEX.md`:** thêm dòng batch / WUP-07 + cập nhật ngày; link runbook `04_`.
7. **`README.md`:** link audit này + dòng **Biomixing/Production** → `FUNC_IMPROVE/BIOMIXING_PREP_INDEX_EN.md`.
8. **`FUNC_IMPROVE/AUDIT_IMPROVE_2026_VI.md`:** cập nhật ý runbook (đã đồng bộ tiền tố); **2026-05-09** layout phẳng Biomixing (không thư mục con `BIOMIXING/`).
9. **Audit pass (2026-05-09, follow-up):** Quét lại `FUNC_LOGIC/*.md` — link tới `FUNC_IMPROVE/` khớp file phẳng (`04_`…`09_`, `BIOMIXING_*`, không còn `FUNC_IMPROVE/BIOMIXING/`). `API_DATA_TYPE_LIST_VI.md`: mở đầu không nhấn tên file EN đã xóa. `README.md`: thêm dòng mục lục tới `SALES_FULFILLMENT_DOCS_INDEX.md` (đồng bộ `INDEX.md`).

### Audit pass — 2026-05-09 (kiểm tra lại)

- **Link:** Toàn bộ `FUNC_LOGIC/*.md` — không phát hiện đường dẫn `FUNC_IMPROVE/BIOMIXING/` (thư mục con); tham chiếu `FUNC_IMPROVE` có tiền tố số hoặc tên file `BIOMIXING_*` / `P0_*` / `AUDIT_*` khớp tree hiện tại.
- **File đã xóa:** Không còn markdown link tới `API_DATA_TYPE_LIST_EN.md`; chỉ còn nhắc trong bảng lịch sử §2 và lệnh `Test-Path` §6 của audit này (mục đích xác minh).
- **Chỉnh sửa nhỏ:** `API_DATA_TYPE_LIST_VI.md` (mô tả gộp EN), `README.md` (thêm hub `SALES_FULFILLMENT_DOCS_INDEX.md`).

---

## 3) Trùng lặp / chồng chéo có chủ đích (không gộp)

| Cặp / nhóm                                                                             | Lý do giữ tách                           |
| -------------------------------------------------------------------------------------- | ---------------------------------------- |
| `HUONG_DAN_KHO_BAN_CO_BAN_VA_PHAN_MO_RONG_VI.md` ↔ `WAREHOUSE_FLOW_VA_NGHIEP_VU_VI.md` | Cơ bản vs chi tiết module kho            |
| `ERP_SO_PO_DO_GRN_SCHEMA_MATRIX_VI.md` ↔ `QUY_TRINH_*.md`                              | Schema/master data vs quy trình vận hành |
| `WAREHOUSE_MASTER_GUIDE.md` ↔ `WAREHOUSE_TOM_TAT_NOI_BO.md`                            | Hub kỹ thuật vs tóm PM/QA + prompt       |
| `MAOLIN_MASTER_GUIDE.md` ↔ `MAOLIN_INDEX.md`                                           | Single source vs mục lục điều hướng      |

---

## 4) Lỗi thời có điều kiện — cách đọc

- **`MAOLIN_INDEX.md` § trạng thái 2026-03:** Đa kho đã có **trước** nhiều bản ghi cũ — đọc **luôn kèm** `WAREHOUSE_INDEX.md` và `04_WH_RUNBOOK_UPGRADE_VI.md` §6 (WUP).
- **`multi_warehouse_audit_report.md`:** Giữ làm **lịch sử / Scope B**; không thay `HUONG_DAN` làm bản mới nhất đầy đủ.
- **`SALES_PURCHASE_FLOW.md`:** Kiểm tra route/controller khi nghi ngờ lệch (đặc biệt sau refactor SO/DO).

---

## 5) Thiếu / nên bổ sung sau (không chặn audit)

| Nhu cầu                                   | Gợi ý                                                                                                              |
| ----------------------------------------- | ------------------------------------------------------------------------------------------------------------------ |
| Production (BOM, batch sản xuất, QC hold) | Chuẩn đọc: `FUNC_IMPROVE/BIOMIXING_*.md`, `BIOMIXING_MIGRATION_AUDIT_2026_VI.md` + `Modules/Production` trong code |
| P0 pilot Biomixing (quyền, UAT)           | `FUNC_IMPROVE/P0_*.md` — có mục trong `FUNC_IMPROVE/INDEX.md`                                                      |

---

## 6) Tái kiểm nhanh

```text
# File EN data type đã xóa — không còn trong tree
Test-Path FUNC_LOGIC/API_DATA_TYPE_LIST_EN.md   # phải False

# Không còn tên ma (sau khi replace)
rg "IMPORT_CHUNK_AND_BULK_INSERT_ANALYSIS|PROJECT_MAOLIN_NEW_TIER_PRICING_IMPORT|MAOLIN_NOTES_YEU_CAU" FUNC_LOGIC

# Tham chiếu improve có tiền tố số
rg "FUNC_IMPROVE/(WAREHOUSE_RUNBOOK|SO_DO_PO_GRN|CLIENT_IMPORT|ORDER_HISTORY|PRICING_MODULE|INVENTORY_BUSINESS)[^.]*\.md" .
```

---

---

## 7) Chu kỳ 2026-05-12 — AI Order Webhook (audit cuối + dọn nợ tài liệu)

### 7.1 Documentation Audit (phát hiện)

| Nguồn                                                                    | Vấn đề                                                                                                                                                                                                   | Mức độ     |
| ------------------------------------------------------------------------ | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ---------- |
| `FUNC_IMPROVE/SO_AI_WEBHOOK_PROMPTS_VI.md` (Part 2 — nội dung cũ `14_…`) | Bảng tóm tắt ghi bắt buộc `client_id` và mô tả PM*READY là «URL mẫu staging» — **lệch** so với `StoreAiOrderWebhookRequest` (`client_code` **hoặc** `client_id`) và `PM_READY*\*` đã bỏ secret hardcode. | Trung bình |
| `FUNC_IMPROVE/13_…`, `15_…`                                              | Prompt / bối cảnh nhấn secret «toàn instance» **chưa** nhấn cột `companies.ai_order_webhook_secret` + UI đã có.                                                                                          | Nhỏ        |
| `FUNC_IMPROVE/12_…`                                                      | Liên kết runbook mang nhãn «staging» trong khi runbook là **chung môi trường** (placeholder URL/secret).                                                                                                 | Nhỏ        |
| `AI_ORDER_WEBHOOK_SECRET_VA_CLIENT_CODE_VI.md` §6                        | Ghi «runbook … staging» trong khi PM_READY không còn cố định host staging.                                                                                                                               | Nhỏ        |
| `PM_READY_AI_WEBHOOK_STAGING_VI.md` (trước chu kỳ)                       | URL + secret tạm lộ trong repo — **đã xử lý** ở PR/chu kỳ trước (không tái diễn trong audit này).                                                                                                        | Đã đóng    |

**Kết luận audit:** Không có xung đột logic **code** trong đợt này; nợ chủ yếu là **đồng bộ tài liệu FUNC_IMPROVE ↔ FUNC_LOGIC** sau khi hoàn thiện ringfence + runbook.

### 7.2 Technical Debt Cleanup (Documentation) — đã thực hiện

1. Cập nhật `FUNC_IMPROVE/14_…` (bảng kiến trúc, payload, bảng liên kết, Pha 5 backlog secret theo company, ghi chú UAT 422).
2. Cập nhật `FUNC_IMPROVE/13_…` (bảng route + prompt: secret per company + fallback env; payload khách).
3. Cập nhật `FUNC_IMPROVE/12_…`, `15_…` (mô tả runbook / secret không còn framing «chỉ staging» hoặc «chỉ env» sai lệch).
4. Cập nhật `AI_ORDER_WEBHOOK_SECRET_VA_CLIENT_CODE_VI.md` (liên kết tới PM_READY: runbook curl/HTTP, bỏ nhãn staging gây hiểu nhầm).
5. Cập nhật `FUNC_LOGIC/INDEX.md` (mô tả dòng PM_READY / AI_ORDER).
6. Cập nhật `AUDIT_AI_ORDER_INBOUND_SO_API_VI.md` (ghi chú vệ sinh tài liệu PM_READY).

### 7.3 Tái kiểm nhanh (sau dọn)

```text
rg "stg-ai-order|9fA2mK" FUNC_LOGIC FUNC_IMPROVE docs
# Kỳ vọng: chỉ còn nhắc lịch sử trong PM_READY (một dòng cập nhật) hoặc không còn.
```

---

## 8) Chu kỳ 2026-05-12 — `FUNC_IMPORT` gộp file + `LOG_REPORT`

### 8.1 `FUNC_IMPORT` (không nằm trong tree `FUNC_LOGIC` nhưng hay đọc kèm)

| Việc                           | Chi tiết                                                                                                                                                 |
| ------------------------------ | -------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Gộp spec map cột**           | Năm file `IMPORT_PRODUCT/CLIENT/INVENTORY/SALE_ORDER/QUOTATION` → một `FUNC_IMPORT/IMPORT_SPECS_VI.md`.                                                  |
| **Gộp engine + tracker**       | `IMPORT_MECHANISMS_POLL_AND_QUEUE_VI.md` + `SO_PO_INVENTORY_IMPLEMENTATION_TRACKER.md` → `FUNC_IMPORT/IMPORT_POLL_TRACKERS_VI.md` (tracker = phụ lục A). |
| **Gộp prompt đã triển khai**   | `PROMPT_IMPLEMENT_QUOTATION_IMPORT` + `SALES_HISTORY_IMPLEMENTATION_PROMPT` → `FUNC_IMPORT/IMPORT_PROMPTS_ARCHIVE_VI.md`.                                |
| **Audit riêng**                | `FUNC_IMPORT/AUDIT_IMPORT_2026_VI.md`                                                                                                                    |
| **Tham chiếu nội bộ đã chỉnh** | `WAREHOUSE_INDEX.md`, `docs/SERVER_RUNBOOK_VI.md`, `SPECIFICATION/STAGING_HUB_SERVER_INFO_2026-04-06.md`, `FUNC_IMPROVE/06_*`, `09_*`.                   |

### 8.2 Thư mục báo cáo LOC

- Đổi tên **`LOC_REPORT/`** → **`LOG_REPORT/`** (vẫn là báo cáo **số dòng code** backend, không phải log runtime ứng dụng). `LOG_REPORT/README.md` có ghi chú đổi tên.

---

## 9) Chu kỳ 2026-05-12 (tiếp) — `FUNC_BUG` gộp + `docs/` audit nhẹ

| Việc                 | Chi tiết                                                                                                                                                                                                                                                                                          |
| -------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **FUNC_BUG**         | 8× `STAGING_*.md` → `STAGING_INCIDENTS_ARCHIVE_VI.md`; chi tiết import client/product → `CLIENT_IMPORT_DETAILS_VI.md`, `PRODUCT_IMPORT_DETAILS_VI.md`. Audit: `FUNC_BUG/AUDIT_BUG_2026_VI.md`.                                                                                                    |
| **docs/**            | Stub `STAGING_RECOVERY_LATEST.md` gộp vào cuối `STAGING_PHP83_L11_DEPLOY_PROGRESS.md` (phụ lục recovery); sửa bảng trong `STAGING_OPERATIONS.md`. **2026-05-12:** gộp `deploy/` vào `SERVER_RUNBOOK_VI.md` mục 10 và xóa thư mục `deploy/`. Audit: `docs/DOCUMENTATION_AUDIT_DOCS_2026_05_VI.md`. |
| **axios-migration**  | Giữ từng file tracker theo module (chưa gộp một file).                                                                                                                                                                                                                                            |
| **LOG_REPORT**       | Xóa 6 file snapshot trùng (`*_full*`, `*_lp_by_filename*`, `*_no_languagepack*`); thêm `INDEX.md` + `DOCUMENTATION_AUDIT_LOG_REPORT_2026_05_VI.md`.                                                                                                                                               |
| **SPECIFICATION**    | Gộp `GCP_RESOURCE_INVENTORY_*` + `CLOUDSQL_HUB_STAGING_FIREWALL_SETTINGS.md` → `GCP_AND_CLOUDSQL_SNAPSHOT_VI.md`; thêm `INDEX.md` + `DOCUMENTATION_AUDIT_SPECIFICATION_2026_05_VI.md`; sửa link runbook tới `STAGING_HUB_SERVER_INFO`.                                                            |
| **FUNC_IMPROVE**     | Gộp `13_` + `14_` + `15_` (AI → SO webhook) → `SO_AI_WEBHOOK_PROMPTS_VI.md`; cập nhật `AUDIT_IMPROVE_2026_VI.md` §2/§7 và `INDEX.md`.                                                                                                                                                             |
| **FUNC\_\* rút tên** | Nhiều `.md` dài dưới `FUNC_BUG/`, `FUNC_IMPORT/`, `FUNC_IMPROVE/`, `FUNC_LOGIC/`, `FUNC_REPORT/`, `FUNC_TEST/` → tên ngắn (cùng ngày); bảng đối chiếu: `FUNC_IMPROVE/AUDIT_IMPROVE_2026_VI.md` §8.                                                                                                |
| **`scripts/`**       | Xóa `edited_files_partial_preview.ps1`; đổi tên export allowlist → `export_sql_allowlist.ps1`; `STAGING_OPERATIONS` + `05_SO_DO_PO_GRN_REFACTOR_VI` bỏ tham chiếu shell không có trong repo. Audit: `scripts/AUDIT_2026_VI.md`.                                                                   |

_Tài liệu này thay cho việc phải đọc diff rải rác; cập nhật khi có đợt dọn `FUNC_LOGIC` lớn tiếp theo._
