# FUNC_IMPORT — Audit & gộp tài liệu (2026-05-12)

## 1) Mục tiêu

- Giảm số file rời (trước: 10 file gốc trong thư mục).
- Giữ **một hub** (`INDEX.md`) + **ba file nội dung** chính + **một audit** (file này).

## 2) Cấu trúc sau gộp

| File mới / giữ                                  | Nội dung (nguồn gộp)                                                                                      |
| ----------------------------------------------- | --------------------------------------------------------------------------------------------------------- |
| `IMPORT_SPECS_VI.md`                            | Map cột: Product, Client, Inventory, Sale Order, Quotation (`IMPORT_*.md` cũ).                            |
| `IMPORT_POLL_TRACKERS_VI.md`   | Cơ chế import/poll/queue + phụ lục tracker SO/PO–Inventory (`IMPORT_MECHANISMS_*` + `SO_PO_INVENTORY_*`). |
| `IMPORT_PROMPTS_ARCHIVE_VI.md`   | Prompt hand-off đã triển khai: Quotation + Sales history (`PROMPT_*`, `SALES_HISTORY_*`).                 |
| `INDEX.md`                                      | Mục lục điều hướng (cập nhật đường dẫn).                                                                  |
| `AUDIT_IMPORT_2026_VI.md` | Bản audit (file này).                                                                                     |

**Đã xóa (thay bằng nội dung trong các file trên):** `IMPORT_PRODUCT.md`, `IMPORT_CLIENT.md`, `IMPORT_INVENTORY.md`, `IMPORT_SALE_ORDER.md`, `IMPORT_QUOTATION.md`, `IMPORT_MECHANISMS_POLL_AND_QUEUE_VI.md`, `SO_PO_INVENTORY_IMPLEMENTATION_TRACKER.md`, `PROMPT_IMPLEMENT_QUOTATION_IMPORT.md`, `SALES_HISTORY_IMPLEMENTATION_PROMPT.md`.

## 3) Tham chiếu chéo đã cập nhật

- `FUNC_LOGIC/WAREHOUSE_INDEX.md` → tracker nằm trong `IMPORT_POLL_TRACKERS_VI.md` (mục phụ lục A).
- `docs/SERVER_RUNBOOK_VI.md`, `SPECIFICATION/STAGING_HUB_SERVER_INFO_2026-04-06.md`, `FUNC_IMPROVE/06_*`, `FUNC_IMPROVE/09_*` → trỏ `IMPORT_POLL_TRACKERS_VI.md` thay cho chỉ `IMPORT_MECHANISMS_*`.

## 4) Ghi chú đọc tài liệu

- **Sales History** trong engine doc: một sheet đầu + chunked; khác **Sales Order import** (multi-sheet) — xem bảng so sánh trong `IMPORT_POLL_TRACKERS_VI.md`.
- Prompt archive: checklist có thể đã hoàn thành một phần; đối chiếu code/route hiện tại khi làm việc mới.

## 5) Repo khác

- Thư mục báo cáo số dòng backend: **`LOG_REPORT/`** (đổi tên từ `LOC_REPORT/`, cùng ngày). Nội dung CSV/TXT đã gọn bản trùng — xem `LOG_REPORT/DOCUMENTATION_AUDIT_LOG_REPORT_2026_05_VI.md`.
