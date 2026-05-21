# FUNC_BUG — Index & trạng thái tài liệu (legacy vs canonical)

Mục tiêu:

- Dùng `FUNC_BUG/` làm nơi ghi nhận **lỗi + nguyên nhân + cách fix** và **archive incident** không còn cần tách từng file.
- Runbook vận hành staging/hub: **`docs/SERVER_RUNBOOK_VI.md`**, **`docs/STAGING_OPERATIONS.md`**. Tiến độ deploy PHP 8.3 / L11 + recovery tóm tắt: **`docs/STAGING_PHP83_L11_DEPLOY_PROGRESS.md`** (có phụ lục recovery).

## 1) Canonical (ưu tiên dùng)

- `docs/SERVER_RUNBOOK_VI.md` — Runbook staging/hub deploy & pitfalls
- `docs/STAGING_OPERATIONS.md` — Staging rehearsal & quy trình thao tác an toàn

## 2) Bug notes (hub + chi tiết)

- `FUNC_BUG/CLIENT_IMPORT_MASTER.md` — tóm tắt lỗi import client → **`FUNC_BUG/CLIENT_IMPORT_DETAILS_VI.md`** (đầy đủ)
- `FUNC_BUG/PRODUCT_IMPORT_MASTER.md` — tóm tắt product import → **`FUNC_BUG/PRODUCT_IMPORT_DETAILS_VI.md`** (đầy đủ)
- `FUNC_BUG/SOCIAL_AUTH_SETTINGS_MAC_INVALID_FIX.md` — `DecryptException: The MAC is invalid.` trên Social Auth Settings (encrypted casts)
- Sau import DB staging: đồng bộ `APP_KEY` — `scripts/download_staging_env.ps1 -SyncAppKey` (xem `backup/README.md`)
- `FUNC_BUG/DEVTOOLS_NO_COMPANY_SETTINGS.md` — package có module nhưng không hiện Settings
- `FUNC_BUG/AFFILIATE_HIDDEN_IN_COMPANIES.md` — module Affiliate active nhưng không thấy ở Companies

## 3) Staging archive (FUNC_BUG)

- **`FUNC_BUG/STAGING_SSH_GCLOUD_METADATA_AND_DEPLOY_SCRIPT_VI.md`** — SSH/metadata GCP + `upload_staging.ps1` (2026-05-13).
- **`FUNC_BUG/STAGING_INCIDENTS_ARCHIVE_VI.md`** — nội dung các file `STAGING_*.md` đã gộp (2026-05-12). Dùng khi cần ngữ cảnh incident cụ thể; không thay runbook `docs/`.

## 4) Gợi ý dọn dẹp (không tự động)

- Nếu muốn **xóa file lỗi thời**, ghi đúng câu: **"DELETE THIS FILE"** và path.
- Nếu **gộp nội dung**, tạo file canonical mới rồi cập nhật link (như đợt 2026-05-12).

## 5) Audit

- **`FUNC_BUG/AUDIT_BUG_2026_VI.md`**
