# FUNC_BUG — Sổ lỗi đã gặp

Thư mục này lưu lỗi thực tế đã gặp trong dự án theo cấu trúc **Triệu chứng → Nguyên nhân → Cách xử lý → File/code liên quan**. Tài liệu vận hành dài vẫn nằm trong `docs/`; thư mục này chỉ dùng để dev tra lỗi nhanh.

## Đọc gì trước?

| Nhu cầu | File |
| --- | --- |
| **Tra cứu nhanh mọi lỗi** | **[`SO_LOI.md`](SO_LOI.md)** |
| Module bật nhưng không thấy menu | [`BUG_MODULE_MENU.md`](BUG_MODULE_MENU.md) |
| Import khách hàng / sản phẩm | [`BUG_IMPORT_CLIENT.md`](BUG_IMPORT_CLIENT.md) · [`BUG_IMPORT_PRODUCT.md`](BUG_IMPORT_PRODUCT.md) |
| Staging / SSH / deploy | [`BUG_STAGING_OPS.md`](BUG_STAGING_OPS.md) · [`BUG_STAGING_SSH.md`](BUG_STAGING_SSH.md) |
| Security / tech review | [`BUG_SECURITY_REVIEW.md`](BUG_SECURITY_REVIEW.md) · [`REVIEW_ERP_TECH.md`](REVIEW_ERP_TECH.md) |

## Cấu trúc

- **`SO_LOI.md`** — sổ lỗi tổng hợp theo nhóm.
- **File chi tiết** — chỉ tách riêng khi cần hơn 15 dòng hoặc có nhiều bước xử lý.
- **Tên file bug chi tiết** — dùng tiếng Việt không dấu, ngắn, có prefix `BUG_`, không thêm hậu tố `_VI`.

## Thêm bug mới

1. Thêm dòng vào [`SO_LOI.md`](SO_LOI.md).
2. Nếu cần file riêng, dùng mẫu: `BUG_TEN_NGAN_DE_NHO.md`.
3. Nội dung tối thiểu: Triệu chứng, Nguyên nhân, Cách xử lý, File/code liên quan.

## Lịch sử gộp

| Đợt        | Nội dung                                                                               |
| ---------- | -------------------------------------------------------------------------------------- |
| 2026-05-27 | Pass 6: xóa `STAGING_INCIDENTS_ARCHIVE.md` — incident → `docs/SERVER_RUNBOOK.md` |
| 2026-05-27 | Pass 4: import master+details → `BUG_IMPORT_CLIENT.md`, `BUG_IMPORT_PRODUCT.md`          |
| 2026-05-27 | Pass 5: thêm `BUG_STAGING_OPS.md` (entry staging một cửa)                         |
| 2026-05-27 | Pass 10: gộp `ENG_TO_EN_STANDARDIZATION.md` → `SO_LOI.md` Phụ lục I18N-ENG-001       |
| 2026-06-17 | Đổi tên file sang tiếng Việt ngắn; gộp Affiliate/Pricing/Developer Tools vào `BUG_MODULE_MENU.md` |
| 2026-06-20 | Thêm prefix `BUG_` cho các file lỗi chi tiết; giữ `README.md`, `SO_LOI.md`, `REVIEW_DEVTOOLS.md` làm index/review |
| 2026-06-20 | Chuyển security/ERP tech review từ `FUNC_LOGIC` sang `FUNC_BUG`. |
