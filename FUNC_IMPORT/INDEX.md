# FUNC_IMPORT Index

Navigation index for import-related specifications, runtime mechanics, and archived implementation prompts.

## Canonical documents (đọc theo thứ tự này)

1. **`FUNC_IMPORT/IMPORT_SPECS_VI.md`** — Map cột Maolin → Craveva: Product, Client, Inventory (Purchase), Sale Order, Quotation (Estimates).
2. **`FUNC_IMPORT/IMPORT_POLL_TRACKERS_VI.md`** — Chunk vs 1-dòng/job, poll + `config/app.php` (`import_progress_*`), CF map, **phụ lục** tracker SO/PO ↔ Inventory (staging).
3. **`FUNC_IMPORT/IMPORT_PROMPTS_ARCHIVE_VI.md`** — Prompt hand-off **đã triển khai** (Quotation import, Sales history); giữ để tái sử dụng pattern.

## Audit & maintenance

- **`FUNC_IMPORT/AUDIT_IMPORT_2026_VI.md`** — Lịch sử gộp file 2026-05-12 + danh sách file đã thay thế.
- Giữ `INDEX.md` làm **route map**; khi thêm domain import mới: cập nhật `IMPORT_SPECS_VI.md` (mục mới) hoặc tách file chuyên sâu nếu > ~400 dòng và link từ đây.

## Liên quan `FUNC_LOGIC`

- Chuỗi import Maolin tổng thể: `FUNC_LOGIC/MAOLIN_IMPORT_READINESS_AND_SEQUENCE.md`, `FUNC_LOGIC/MAOLIN_MASTER_GUIDE.md`.
- Báo cáo số dòng backend (không phải log ứng dụng): `LOG_REPORT/README.md` · `LOG_REPORT/INDEX.md` · audit: `LOG_REPORT/DOCUMENTATION_AUDIT_LOG_REPORT_2026_05_VI.md`.
