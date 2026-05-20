# `scripts/` — Audit (2026-05-12)

Mục tiêu: **rà soát** thư mục, **bỏ artifact tạm**, **sửa tài liệu** trỏ tới file không tồn tại, **rút tên** script dài khi an toàn; **không** gộp hai bản Python VI/EN nếu chưa có thời gian refactor `argparse`.

---

## 1) Thay đổi đã làm (đợt 2026-05-12)

| Việc           | Chi tiết                                                                                                                                                                          |
| -------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Xóa**        | `edited_files_partial_preview.ps1` — danh sách file phiên Cursor, trùng mục đích với default trong `upload_hub_temp.ps1`.                                                         |
| **Đổi tên**    | `export-cloudsql-allowlist-report.ps1` → **`export_sql_allowlist.ps1`** (cùng tham số; output `CLOUDSQL_ALLOWLIST_STATUS_<timestamp>.md`; mặc định `FUNC_REPORT/`).               |
| **Tài liệu**   | `docs/STAGING_OPERATIONS.md` §5.5–5.9: bỏ lệnh `bash scripts/staging_*.sh` / `.ps1` **không có trong repo**; thay bằng gate/precheck **thủ công** (Artisan §5.1–5.4 + checklist). |
| **Tài liệu**   | `FUNC_IMPROVE/05_SO_DO_PO_GRN_REFACTOR_VI.md` — ghi chú lịch sử script đã gỡ + trỏ `STAGING_OPERATIONS`.                                                                          |
| **Tham chiếu** | `FUNC_IMPROVE/AUDIT_IMPROVE_2026_VI.md` §8 — đường dẫn script export allowlist.                                                                                                   |

---

## 2) Nhóm script đang dùng (giữ)

| Nhóm                          | File (ví dụ)                                                                                                                                                                                                                                                                                                            | Ghi chú ngắn                                                                                                                                                                     |
| ----------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Deploy / upload**           | `upload_staging.ps1`, `upload_hub.ps1`, `upload_hub_temp.ps1`                                                                                                                                                                                                                                                           | Zip/rsync-style deploy; temp = partial + `-CaseFile`.                                                                                                                            |
| **GCP / SSH**                 | `gcloud_*.ps1`, `ssh_staging.ps1`, `ssh_hub.ps1`, `mysql_staging.ps1`                                                                                                                                                                                                                                                   | Infra + shell nhanh.                                                                                                                                                             |
| **Queue**                     | `run-queue-worker-all.ps1`/`.sh`, `run_local_import_queue_worker.ps1`, `queue-worker-preflight.php`, `check_queue_jobs_staging.php`                                                                                                                                                                                     | Worker + preflight.                                                                                                                                                              |
| **Hub git (server)**          | `hub_git_*.sh`, `hub_composer_refresh.sh`, …                                                                                                                                                                                                                                                                            | Chạy trên Hub qua SSH.                                                                                                                                                           |
| **Maolin / sheet**            | `read_maolin*.php`, `peek_maolin_sheet.php`, `list_maolin_new_all_sheets.php`                                                                                                                                                                                                                                           | Đọc header / peek xlsx; không gộp vì path & mục đích khác nhau.                                                                                                                  |
| **DOCX / Python**             | `generate_verify_issue_report_*.py`, `generate_maolin_*_docx.py`, `export_docx.py`                                                                                                                                                                                                                                      | Báo cáo tĩnh; **hai bản VI/EN** giữ tách (duplicate structure — refactor sau nếu cần).                                                                                           |
| **Markdown / AI context**     | `md_master_sync.ps1`, `md_master_sync.rules.json`, `md_ai_context.ps1`, `md_to_docx.ps1`, `preview_for_ai.ps1`, `quick-context.ps1`, `generate_ai_context.php`                                                                                                                                                          | Context cho AI / đồng bộ MD.                                                                                                                                                     |
| **Khác**                      | `demo-so-do-invoice.ps1`, `fpm_scale_pool_apply.sh`, `tune_php83_import_limits.sh`, `staging-pull-keep-htaccess.ps1`/`.sh`, `download_staging_logs.ps1`, `fix-module-lang-permissions.ps1`, `patch-ai-workspace-lang.php`, `audit_company_devtools.php`, `deploy-secrets.local.ps1.example`, `export_sql_allowlist.ps1` | Runbook / tuning / audit một lần.                                                                                                                                                |
| **LanguagePack Purchase ERP** | `audit_purchase_lang.php`, `purchase_lang_erp.ps1`, `purchase_lang_erp.sh`                                                                                                                                                                                                                                              | Glossary `FUNC_LOGIC/GLOSSARY_PURCHASE_ERP_VI.json` → audit/apply VI+EN; wrapper gọi `languagepack:sync-keys` + `languagepack:publish-translation` (không có artisan translate). |

---

## 3) `casefiles/`

- Chứa file `.txt` **một đường dẫn repo-relative mỗi dòng** (comment `#`), dùng với `upload_hub_temp.ps1 -CaseFile`.
- **Không** đặt secret trong đây; chỉ path tài liệu/code đã review.

---

## 4) Legacy đã xử lý trong tài liệu (không có file trong repo)

Các tên sau từng xuất hiện trong `docs/` hoặc `FUNC_IMPROVE/` nhưng **không** có trong `scripts/`:

- `staging_sales_do_rehearsal_gate.sh`
- `staging_phase3_safe_execute.sh`
- `run_staging_phase3_safe_execute.ps1`
- `staging_phase4_cutover_precheck.sh`

**Cách làm đúng:** Artisan trong `docs/STAGING_OPERATIONS.md` §5 (baseline + reconcile + tiêu chí pass; precheck thủ công §5.9).

---

## 5) Gợi ý tiếp (không bắt buộc)

- Rút tên thêm các `gcloud_*_staging*.ps1` dài nếu đội thống nhất convention (vd. `gcloud-staging-ram.ps1`).
- Gộp `generate_verify_issue_report_docx.py` + `_en_` thành một entrypoint `--lang vi|en` khi có thời gian kiểm thử output DOCX.

---

_Bản ghi: audit và dọn 2026-05-12._
