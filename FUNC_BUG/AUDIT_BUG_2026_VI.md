# FUNC_BUG — Audit & gộp tài liệu (2026-05-12)

## 1) Mục tiêu

- Giảm số file rời trong `FUNC_BUG/` (staging + import chi tiết).
- Giữ **hub** `README.md` / `INDEX.md` + **master** import; chi tiết dài gộp vào file archive có mục lục rõ.

## 2) Thay đổi cấu trúc

| Hành động                       | Chi tiết                                                                                                                                       |
| ------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------- |
| **Gộp staging**                 | 8 file `STAGING_*.md` → **`STAGING_INCIDENTS_ARCHIVE_VI.md`** (mỗi case một `##`).                                                             |
| **Gộp product import chi tiết** | `PRODUCT_IMPORT_CUSTOM_FIELDS_AND_SLOW_ANALYSIS.md` + `PRODUCT_IMPORT_1000_SLOW_CAUSES_AND_SOLUTIONS.md` → **`PRODUCT_IMPORT_DETAILS_VI.md`**. |
| **Gộp client import chi tiết**  | `CLIENT_IMPORT_ERRORS.md` + `CLIENT_IMPORT_FILE_NOT_FOUND_STAGING.md` → **`CLIENT_IMPORT_DETAILS_VI.md`**.                                     |

**Canonical vận hành staging** (không thay): `docs/SERVER_RUNBOOK_VI.md`, `docs/STAGING_OPERATIONS.md`.

## 3) Tham chiếu đã chỉnh

- `FUNC_BUG/PRODUCT_IMPORT_MASTER.md` → trỏ `PRODUCT_IMPORT_DETAILS_VI.md` thay cho hai file product cũ.
- `FUNC_BUG/CLIENT_IMPORT_MASTER.md` → trỏ `CLIENT_IMPORT_DETAILS_VI.md` thay cho hai file client cũ.
- `FUNC_BUG/INDEX.md`, `FUNC_BUG/README.md` — cập nhật mục lục.
- `ai-context/core/FUNC_DOCS_INDEX.md` — dòng `FUNC_BUG/STAGING_*` → archive.

## 4) Ghi chú đọc

- Archive staging có thể **trùng ý** runbook trong `docs/` — khi xung đột, ưu tiên **`docs/`**.
