# FUNC_BUG — Lỗi đã gặp & cách xử

Thư mục này lưu **triệu chứng → nguyên nhân → fix** (và archive incident staging). Không thay runbook vận hành trong `docs/`.

## Đọc gì trước?

| Nhu cầu                   | File                                                                                          |
| ------------------------- | --------------------------------------------------------------------------------------------- |
| **Tra cứu nhanh mọi bug** | **[`REGISTRY.md`](REGISTRY.md)**                                                              |
| Import client / product   | [`CLIENT_IMPORT_VI.md`](CLIENT_IMPORT_VI.md) · [`PRODUCT_IMPORT_VI.md`](PRODUCT_IMPORT_VI.md) |
| Staging (một cửa)         | **[`STAGING_QUICK_REF_VI.md`](STAGING_QUICK_REF_VI.md)** → runbook `docs/` · SSH · archive    |

## Cấu trúc

- **`REGISTRY.md`** — bảng tổng hợp + mẫu thêm bug
- **File ticket** — một lỗi một file ngắn (mẫu: `PRODUCTION_RM_OUTBOUND_UOM_VI.md`)
- **Import** — một file mỗi domain (`CLIENT_IMPORT_VI.md`, `PRODUCT_IMPORT_VI.md`)

## Thêm bug mới

1. Dòng vào [`REGISTRY.md`](REGISTRY.md)
2. File riêng nếu > ~15 dòng (Triệu chứng / Nguyên nhân / Fix)

## Lịch sử gộp

| Đợt        | Nội dung                                                                               |
| ---------- | -------------------------------------------------------------------------------------- |
| 2026-05-27 | Pass 6: xóa `STAGING_INCIDENTS_ARCHIVE_VI.md` — incident → `docs/SERVER_RUNBOOK_VI.md` |
| 2026-05-27 | Pass 4: import master+details → `CLIENT_IMPORT_VI.md`, `PRODUCT_IMPORT_VI.md`          |
| 2026-05-27 | Pass 5: thêm `STAGING_QUICK_REF_VI.md` (entry staging một cửa)                         |
