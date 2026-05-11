# FUNC_LOGIC — Documentation Audit & cleanup (2026-05-09)

Bản rà soát theo yêu cầu: **lỗi thời**, **trùng lặp**, **logic nghiệp vụ cần đọc kèm bối cảnh**, **thiếu triển khai**, **tính năng đã code chưa được ghi**. Kèm **hành động đã làm** và **việc chưa làm / cố ý giữ nguyên**.

---

## 1) Tóm tắt điều hành

| Chủ đề                                                                       | Kết luận ngắn                                                                                                                                                                                                                                                                                                |
| ---------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| **Đường dẫn `FUNC_IMPROVE/*` thiếu tiền tố số**                              | Nhiều file trỏ tới `FUNC_IMPROVE/WAREHOUSE_RUNBOOK…`, `SO_DO_PO_GRN…`, `CLIENT_IMPORT…`, v.v. trong khi file thật là `04_`, `05_`, `08_`, `09_`, `07_`, `06_` — **đã sửa đồng bộ** trong toàn repo (`.md`).                                                                                                  |
| **File “ma” (không tồn tại)**                                                | `IMPORT_CHUNK_AND_BULK_INSERT_ANALYSIS` — không có file; **đã thay** bằng `IMPORT_CHUNK_AND_BULK_INSERT.md`. `PROJECT_MAOLIN_NEW_TIER_PRICING_IMPORT.md`, `MAOLIN_NOTES_YEU_CAU_KHACH_VA_PDF_BAN_HANG_VI.md` — **đã bỏ link**; trỏ về `PROJECT_MAOLIN_NEW_FILES_ANALYSIS.md` §4 và `MAOLIN_MASTER_GUIDE.md`. |
| **Trùng EN/VI thuần bảng**                                                   | `API_DATA_TYPE_LIST_EN.md` và `API_DATA_TYPE_LIST_VI.md` — **gộp**: giữ `API_DATA_TYPE_LIST_VI.md` làm canonical + **xóa EN**.                                                                                                                                                                               |
| **Đã triển khai, doc FUNC_LOGIC thưa**                                       | Batch kho UI + đối soát (WUP-07) có trong runbook `FUNC_IMPROVE/04_*` nhưng **mục lục kho** chưa nhấn — **đã thêm dòng** vào `WAREHOUSE_INDEX.md`.                                                                                                                                                           |
| **Production / Biomixing**                                                   | Logic triển khai **không** trong `FUNC_LOGIC` — `README.md` trỏ `FUNC_IMPROVE/BIOMIXING_PRODUCTION_PREP_INDEX_EN.md` (các file `BIOMIXING_*` cùng cấp tại `FUNC_IMPROVE/`).                                                                                                                                  |
| **`SALES_PURCHASE_FLOW.md` vs `QUY_TRINH_PO_DO_SO_INVOICE_WAREHOUSE_VI.md`** | Hai vai trò khác nhau (EN + trace code / VI + quy trình một trang) — **không gộp**.                                                                                                                                                                                                                          |
| **Các file `AUDIT_*_MODULE_VI.md`**                                          | Bản chụp kiểm tra theo thời điểm — **giữ**; khi đọc cần đối chiếu code/route hiện tại + `ERP_SO_PO_DO_INVOICE_WAREHOUSE_QA_VERIFICATION_VI.md`.                                                                                                                                                              |

---

## 2) Hành động đã thực hiện (changelog)

1. Thay thế toàn văn **`IMPORT_CHUNK_AND_BULK_INSERT_ANALYSIS`** → **`IMPORT_CHUNK_AND_BULK_INSERT.md`** trong các `.md` liên quan.
2. Chuẩn hóa **`FUNC_IMPROVE/04_…`, `05_…`, `06_…`, `07_…`, `08_…`, `09_…`** trong các tham chiếu (gồm `FUNC_LOGIC`, `docs/`, `FUNC_IMPORT`, `FUNC_IMPROVE`).
3. **`MAOLIN_INDEX.md`:** bỏ dòng file ma; gộp “tier pricing” vào `PROJECT_MAOLIN_NEW_FILES_ANALYSIS.md` §4; row 5 → `MAOLIN_MASTER_GUIDE.md` (GAP/phụ lục).
4. **`PROJECT_MAOLIN_NEW_FILES_ANALYSIS.md`:** sửa mục đầu — bỏ pointer tới file không tồn tại.
5. **Xóa** `API_DATA_TYPE_LIST_EN.md`; cập nhật `API_DATA_TYPE_LIST_VI.md` + `INDEX.md`.
6. **`WAREHOUSE_INDEX.md`:** thêm dòng batch / WUP-07 + cập nhật ngày; link runbook `04_`.
7. **`README.md`:** link audit này + dòng **Biomixing/Production** → `FUNC_IMPROVE/BIOMIXING_PRODUCTION_PREP_INDEX_EN.md`.
8. **`FUNC_IMPROVE/DOCUMENTATION_AUDIT_2026_VI.md`:** cập nhật ý runbook (đã đồng bộ tiền tố); **2026-05-09** layout phẳng Biomixing (không thư mục con `BIOMIXING/`).
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
| `ERP_SO_PO_DO_GRN_SCHEMA_AND_LEGACY_MATRIX_VI.md` ↔ `QUY_TRINH_*.md`                   | Schema/master data vs quy trình vận hành |
| `WAREHOUSE_MASTER_GUIDE.md` ↔ `WAREHOUSE_TOM_TAT_NOI_BO.md`                            | Hub kỹ thuật vs tóm PM/QA + prompt       |
| `MAOLIN_MASTER_GUIDE.md` ↔ `MAOLIN_INDEX.md`                                           | Single source vs mục lục điều hướng      |

---

## 4) Lỗi thời có điều kiện — cách đọc

- **`MAOLIN_INDEX.md` § trạng thái 2026-03:** Đa kho đã có **trước** nhiều bản ghi cũ — đọc **luôn kèm** `WAREHOUSE_INDEX.md` và `04_WAREHOUSE_RUNBOOK…` §6 (WUP).
- **`multi_warehouse_audit_report.md`:** Giữ làm **lịch sử / Scope B**; không thay `HUONG_DAN` làm bản mới nhất đầy đủ.
- **`SALES_PURCHASE_FLOW.md`:** Kiểm tra route/controller khi nghi ngờ lệch (đặc biệt sau refactor SO/DO).

---

## 5) Thiếu / nên bổ sung sau (không chặn audit)

| Nhu cầu                                   | Gợi ý                                                                                                      |
| ----------------------------------------- | ---------------------------------------------------------------------------------------------------------- |
| Production (BOM, batch sản xuất, QC hold) | Chuẩn đọc: `FUNC_IMPROVE/BIOMIXING_*.md`, `AUDIT_PROJECT_BIOMIXING_*.md` + `Modules/Production` trong code |
| P0 pilot Biomixing (quyền, UAT)           | `FUNC_IMPROVE/P0_*.md` — có mục trong `FUNC_IMPROVE/INDEX.md`                                              |

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

_Tài liệu này thay cho việc phải đọc diff rải rác; cập nhật khi có đợt dọn `FUNC_LOGIC` lớn tiếp theo._
