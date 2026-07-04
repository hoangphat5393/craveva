# `scripts/` — Audit hiện tại

**Cập nhật:** 2026-06-17
**Mục tiêu:** phân loại script đang còn giá trị, script chạy thủ công cần cẩn trọng, script legacy/one-off có thể dọn sau khi duyệt.

## Nguyên tắc

- Không xóa script deploy, backup, SSH, GCP, DB hoặc migration dữ liệu nếu chưa có người vận hành xác nhận.
- Script không có reference trong Markdown/code **không tự động là rác**; nhiều script được chạy thủ công từ terminal.
- Script có thể sửa DB, quyền file, Cloud VM hoặc LanguagePack phải có usage rõ và nên chạy dry-run nếu có.
- Không commit secret. File `deploy-secrets.local.ps1.example` chỉ là mẫu; file thật phải nằm ngoài git hoặc gitignored.

## Nhóm đang giữ

| Nhóm | File chính | Lý do giữ |
| --- | --- | --- |
| Deploy staging/hub | `upload_staging.ps1`, `upload_hub.ps1` | Quy trình deploy Git-based đầy đủ cho từng môi trường. |
| SSH / GCP | `ssh_staging.ps1`, `ssh_hub.ps1`, `gcloud_start_staging_vm.ps1`, `gcloud_sync_staging_ssh_key.ps1`, `gcloud_resize_*.ps1`, `mysql_staging.ps1` | Vận hành VM, SSH, resize, sync key, kết nối DB. |
| Backup / download staging | `download_staging_db.ps1`, `download_staging_env.ps1`, `download_staging_logs.ps1`, `backup/staging_dump_db.sh` | Lấy DB/env/log từ staging để debug hoặc đồng bộ local. |
| Queue / import worker | `run-queue-worker-all.*`, `run_local_import_queue_worker.ps1`, `queue-worker-preflight.php`, `check_queue_jobs_staging.php` | Kiểm tra/chạy queue liên quan import và worker. |
| Test wrapper | `test.ps1`, `test.sh` | Wrapper test nhanh, đang được Biomixing docs dùng. |
| LanguagePack / translation | `lang_sync_keys_from_en.php`, `lang_apply_ui_translations.php`, `lang_title_case_labels.php`, `lang_audit_purchase.php`, `lang_purchase_erp.*`, `lang_audit_production.php` | Đồng bộ/audit key dịch, Purchase ERP glossary, Production translations. |
| Markdown / AI context | `generate_ai_context.php`, `md_master_sync.ps1`, `md_master_sync.rules.json`, `md_ai_context.ps1`, `preview_for_ai.ps1`, `quick-context.ps1`, `md_to_docx.ps1` | Tạo context theo nhu cầu, kiểm tra docs, xuất tài liệu; `ai-context/` là output local đã ignore. |
| DOCX / report generator | `export_docx.py` | Công cụ xuất Word tổng quát còn được giữ. |
| Maolin / sheet | `read_maolin_project_headers.php`, `peek_maolin_sheet.php` | Đọc header/preview file Excel trong `PROJECT MAOLIN/` hiện tại. |
| Code/doc audit | `audit_orphan_controllers.php`, `audit_company_devtools.php`, `blade_html_comments_to_blade.php`, `lang_audit_purchase.php` | Audit cleanup/code style/language. |
| Data cleanup có kiểm soát | `cleanup_redundant_client_custom_fields.php`, `normalize_product_unit_conversions_uom_pricing.ps1` | Script sửa dữ liệu có scope cụ thể; chỉ chạy sau review tham số. |
| Server tuning | `fpm_scale_pool_apply.sh`, `tune_php83_import_limits.sh`, `staging_fix_storage_permissions.sh`, `fix-module-lang-permissions.ps1` | Tuning PHP-FPM/import/quyền file trên server. |
| Hub git setup | `hub_git_*.sh`, `hub_composer_refresh.sh` | Bootstrap Git bằng SSH deploy key cho hub; thường chỉ chạy khi setup hoặc repair. |
| Demo / rehearsal | `demo-so-do-invoice.ps1`, `staging-pull-keep-htaccess.ps1`, `export_sql_allowlist.ps1` | Demo SO/DO/Invoice, PowerShell pull giữ `.htaccess`, export allowlist khi cần audit lại. |

## Script cần cẩn trọng trước khi chạy

| File | Rủi ro | Gợi ý |
| --- | --- | --- |
| `upload_staging.ps1`, `upload_hub.ps1` | Có thể `git add/commit/push` local rồi SSH deploy. | Dùng `-DeployOnly` nếu chỉ muốn server pull code đã push. |
| `download_staging_env.ps1 -SyncAppKey` | Có thể copy APP_KEY staging vào local `.env`. | Chỉ dùng sau khi hiểu ảnh hưởng decrypt dữ liệu local. |
| `download_staging_db.ps1` | Tạo/download DB dump; dữ liệu nhạy cảm. | Lưu trong `backup/`, không commit dump. |
| `cleanup_redundant_client_custom_fields.php` | Xóa custom fields và data theo danh sách core/trùng nghĩa. | Chạy dry-run trước; dùng `--except-company` đúng tenant cần giữ. |
| `normalize_product_unit_conversions_uom_pricing.ps1` | Có thể sửa dữ liệu UOM/pricing nếu dùng `-Apply`. | Ưu tiên `-DryRun`; backup DB trước khi `-Apply`. |
| `gcloud_resize_*.ps1` | Stop VM và đổi machine type. | Chỉ chạy trong maintenance window. |
| `staging_fix_storage_permissions.sh`, `fix-module-lang-permissions.ps1` | Chỉnh ownership/chmod. | Chạy đúng server/user; không chạy bừa trên local. |
| `lang_title_case_labels.php`, `lang_apply_ui_translations.php` | Sửa nhiều file LanguagePack. | Chạy khi có diff review và test UI/i18n. |
| `blade_html_comments_to_blade.php` | Có thể sửa nhiều Blade files. | Chạy `--dry-run` trước. |

## Quyết định cleanup 2026-07-02

- Giữ `hub_git_setup_remote.sh`, `hub_git_clone_safe.sh`, `hub_git_track_main.sh`: ba bước SSH bootstrap, clone an toàn và repair/track branch có trách nhiệm khác nhau.
- Đã xóa hai generator Verify DOCX vì không còn `REPORT/`, output hoặc tham chiếu sử dụng.
- Đã xóa bốn LanguagePack patch one-off sau khi xác nhận 29 Purchase locale và 30 AI Workspace locale file không thiếu key.
- Đã xóa thư mục `scripts/sql/` trống.

## Legacy đã xử lý

Các tên sau từng xuất hiện trong tài liệu nhưng hiện **không còn file trong repo**:

- `edited_files_partial_preview.ps1` — partial deploy cũ đã ngừng sử dụng.
- `upload_hub_temp.ps1` và case file đi kèm — đã xóa 2026-07-02; dùng deploy Git đầy đủ qua `upload_hub.ps1`.
- Các script `*_maolin_new_*`, `read_maolin_headers.php`, `list_maolin_new_all_sheets.php` — đã xóa 2026-07-02 vì `PROJECT MAOLIN New/` không còn; giữ hai công cụ generic cho `PROJECT MAOLIN/`.
- `generate_verify_issue_report_docx.py`, `generate_verify_issue_report_en_docx.py` — đã xóa 2026-07-02; không còn input/output hoặc consumer.
- `lang_patch_ai_workspace.php`, `lang_patch_purchase_*`, `lang_patch_sku_placeholder.php` — đã xóa 2026-07-02 sau kiểm tra canonical locale keys.
- `hub_pat_clone_once.sh` — đã xóa 2026-07-02; PAT bootstrap cũ được thay bằng SSH deploy key.
- `staging-pull-keep-htaccess.sh` — đã xóa 2026-07-02; không còn phù hợp Nginx/current deploy flow. Bản PowerShell vẫn được giữ.
- `staging_sales_do_rehearsal_gate.sh`
- `staging_phase3_safe_execute.sh`
- `run_staging_phase3_safe_execute.ps1`
- `staging_phase4_cutover_precheck.sh`
- `export-cloudsql-allowlist-report.ps1` — đã đổi thành `export_sql_allowlist.ps1`; snapshot allowlist cũ gộp vào `docs/GCP_INVENTORY.md`.
- `gcloud_staging_move_zone_and_start.ps1` — deprecated, đã xóa; staging hiện dùng zone trong `gcloud_start_staging_vm.ps1` / `ssh_staging.ps1`.

## Thư mục con

| Thư mục | Trạng thái |
| --- | --- |
| `backup/` | Có script dump staging; output backup/dump không được commit. |
| `ssh_config/` | SSH config example cho staging. |

## Kết luận cập nhật 2026-07-02

- Các one-off script đã xác nhận hết giá trị đã được dọn.
- Script vận hành server, backup, queue, test và Hub SSH bootstrap được giữ có chủ đích.
- Không còn ứng viên xóa an toàn nào trong `scripts/` mà không cần quyết định vận hành mới.
