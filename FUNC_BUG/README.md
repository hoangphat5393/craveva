# FUNC_BUG — Lỗi đã gặp & cách xử

Thư mục này lưu **triệu chứng → nguyên nhân → fix** (và archive incident staging). Không thay runbook vận hành trong `docs/`.

## Đọc gì trước?

| Nhu cầu                   | File                                                                                                                             |
| ------------------------- | -------------------------------------------------------------------------------------------------------------------------------- |
| **Tra cứu nhanh mọi bug** | **[`REGISTRY.md`](REGISTRY.md)** ← bắt đầu ở đây                                                                                 |
| Import client / product   | [`CLIENT_IMPORT_MASTER.md`](CLIENT_IMPORT_MASTER.md) · [`PRODUCT_IMPORT_MASTER.md`](PRODUCT_IMPORT_MASTER.md)                    |
| Staging SSH / deploy      | [`STAGING_SSH_GCLOUD_METADATA_AND_DEPLOY_SCRIPT_VI.md`](STAGING_SSH_GCLOUD_METADATA_AND_DEPLOY_SCRIPT_VI.md)                     |
| Incident staging cũ (dài) | [`STAGING_INCIDENTS_ARCHIVE_VI.md`](STAGING_INCIDENTS_ARCHIVE_VI.md) — nếu lệch `docs/`, ưu tiên **`docs/SERVER_RUNBOOK_VI.md`** |

## Cấu trúc file

- **`REGISTRY.md`** — bảng tổng hợp + mẫu thêm bug.
- **`*_MASTER.md`** — tóm tắt nhóm lỗi (import).
- **`*_DETAILS_VI.md`** — phân tích dài (chỉ khi cần đào sâu).
- **File đơn lẻ** — một ticket; giữ ngắn (mẫu: `PRODUCTION_RM_OUTBOUND_UOM_VI.md`).

## Thêm / cập nhật bug

1. Thêm dòng vào [`REGISTRY.md`](REGISTRY.md).
2. Nếu > ~15 dòng: tạo hoặc cập nhật file riêng (tiếng Việt, có **Triệu chứng / Nguyên nhân / Fix**).
3. Không nhân bản runbook — trỏ `docs/` cho checklist server.

## Lịch sử gộp file (2026-05-12)

- 8× `STAGING_*.md` → `STAGING_INCIDENTS_ARCHIVE_VI.md`
- Product import chi tiết → `PRODUCT_IMPORT_DETAILS_VI.md`
- Client import chi tiết → `CLIENT_IMPORT_DETAILS_VI.md`

`INDEX.md` và `AUDIT_BUG_2026_VI.md` đã gộp vào README + REGISTRY (2026-05-27).
