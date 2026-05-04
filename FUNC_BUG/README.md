# FUNC_BUG — Index & trạng thái tài liệu (legacy vs canonical)

Mục tiêu:
- Dùng `FUNC_BUG/` làm nơi ghi nhận **lỗi + nguyên nhân + cách fix**.
- Với tài liệu “runbook/operations” bị trùng (staging/hub), ưu tiên **canonical** trong `docs/` để tránh phân mảnh.

## 1) Canonical (ưu tiên dùng)

- `docs/SERVER_RUNBOOK_VI.md` — Runbook staging/hub deploy & pitfalls
- `docs/STAGING_OPERATIONS.md` — Staging rehearsal & quy trình thao tác an toàn

## 2) Bug notes (vẫn còn hiệu lực)

- `FUNC_BUG/CLIENT_IMPORT_MASTER.md` — gộp lỗi import client (data + staging) + link canonical
- `FUNC_BUG/PRODUCT_IMPORT_MASTER.md` — gộp product import (mapping/custom field/performance) + link canonical
- `FUNC_BUG/SOCIAL_AUTH_SETTINGS_MAC_INVALID_FIX.md` — `DecryptException: The MAC is invalid.` trên Social Auth Settings (encrypted casts)
- `FUNC_BUG/DEVELOPER_TOOLS_MISSING_COMPANY_SETTINGS_DESPITE_PACKAGE.md` — package có module nhưng không hiện Settings
- `FUNC_BUG/AFFILIATE_MODULE_ACTIVE_BUT_NOT_VISIBLE_IN_COMPANIES.md` — module Affiliate active nhưng không thấy ở Companies

## 3) Legacy / vận hành (có thể trùng nội dung)

Các file sau có thể trùng với `docs/SERVER_RUNBOOK_VI.md` hoặc `docs/STAGING_OPERATIONS.md`. Không xóa tự động; dùng để tham chiếu lịch sử:

- `FUNC_BUG/STAGING_ACCESS_VIA_GOOGLE_CLOUD.md`
- `FUNC_BUG/STAGING_CHECK_WHY_SERVER_DOWN.md`
- `FUNC_BUG/STAGING_DB_COPY_TO_LOCAL_MYSQL.md`
- `FUNC_BUG/STAGING_IMPORT_SERVER_SHUTDOWN.md`
- `FUNC_BUG/STAGING_INCIDENT_CHECK_COMMANDS.md`
- `FUNC_BUG/STAGING_NGINX_TIMEOUT_IMPORT.md`
- `FUNC_BUG/STAGING_PHP_UPLOAD_LIMIT.md`

## 4) Gợi ý dọn dẹp (không tự động)

- Nếu bạn muốn **xóa file lỗi thời**, hãy ghi đúng câu: **"DELETE THIS FILE"** và chỉ rõ path file cần xóa.
- Nếu bạn muốn **gộp nội dung**, hướng an toàn nhất là tạo 1 “canonical doc” mới và thêm link trỏ từ file cũ sang file mới.
