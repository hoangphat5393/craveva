# Staging — tác vụ dài & rehearsal (Craveva)

**Runbook chính (deploy hub/staging, quyền, queue, bẫy go-live):** [`SERVER_RUNBOOK_VI.md`](SERVER_RUNBOOK_VI.md)

**Host SSH:** `craveva-staging`  
**App:** `/var/www/craveva-staging/current/craveva`  
**URL:** `https://staging.craveva.com`

**Nguyên tắc:** Staging **luôn lấy mã từ Git** (`git pull` trên `main`, working tree sạch). **Không** sửa tay / `scp` từng file PHP cho thay đổi tính năng. Cấu hình server-only (cron, systemd, `.env`) giữ tách hoặc document trong `deploy/`.

- **Ngôn ngữ `en` (không `eng`):** xem [SERVER_RUNBOOK_VI §6](SERVER_RUNBOOK_VI.md#6-ngôn-ngữ-en-không-dùng-eng).
- **Git deploy, `sudo -u www-data`, Supervisor, quyền storage:** xem [SERVER_RUNBOOK_VI](SERVER_RUNBOOK_VI.md).
- **Language Pack — Publish / Publish All báo `Permission denied` trên `resources/lang/...`:** PHP-FPM chạy user **`www-data`**; thư mục đích phải **ghi được** theo [SERVER_RUNBOOK_VI §4.8](SERVER_RUNBOOK_VI.md#48-language-pack--sync-keys--publish-all-ghi-file-trong-repo). Script **`scripts/upload_staging.ps1`** và **`scripts/upload_hub.ps1`** (sau chỉnh 2026-04) đã thêm `chmod ug+rwX` + `g+s` cho `resources/lang`, `lang/`, `Modules/LanguagePack/Languages` và `Modules/*/Resources/lang`. Trên server **đã lỗi sẵn**, SSH một lần (đổi `APP` / user deploy nếu khác):

```bash
APP=/var/www/hub.craveva.com   # hoặc staging: /var/www/craveva-staging/current/craveva
U=hoangphat5393
cd "$APP" && sudo mkdir -p lang resources/lang
sudo chown -R "$U":www-data Modules/LanguagePack/Languages resources/lang lang
sudo chmod -R ug+rwX Modules/LanguagePack/Languages resources/lang lang
sudo find Modules/LanguagePack/Languages resources/lang lang -type d -exec chmod g+s {} \;
for d in Modules/*/Resources/lang; do [ -d "$d" ] || continue; sudo chown -R "$U":www-data "$d"; sudo chmod -R ug+rwX "$d"; sudo find "$d" -type d -exec chmod g+s {} \;; done
sudo -u www-data touch "$APP/resources/lang/.write_test" && sudo rm -f "$APP/resources/lang/.write_test" && echo "OK: www-data can write resources/lang"
```

---

## 1. Script `scripts/upload_staging.ps1` (zip từng phần)

- **Mục đích:** đóng gói **danh sách file/thư mục** cục bộ → zip → `scp` → giải nén lên staging.
- **Mặc định an toàn:** biến **`$RemoteWipeAppBeforeUnzip = $false`** — **không** xóa sạch thư mục app trước khi unzip; chỉ đè file có trong zip. Bật `$true` chỉ khi zip chứa **đủ** mã nguồn (kể cả `app/Console/Kernel.php`); trước đây thiếu `app/Console` trong zip + bật wipe dễ làm **mất lịch queue** (`Kernel.php`).
- Script đã thêm **`app/Console`** vào `$DirsToCopy` và kiểm tra **`app/Console/Kernel.php`** trong bước verify.
- **Rủi ro (khi bật wipe):** lệnh **`find ... -exec rm -rf`** xóa gần như mọi thứ **trừ** `.env`, `storage`, `.git`. **Ưu tiên Git deploy** (runbook).
- Danh sách file trong script có thể **trùng lặp**; script đã gom **`Select-Object -Unique`** trước khi copy.
- Thư mục copy nguyên khối: ví dụ `app/Console`, `Modules/LanguagePack/Languages/modules`, `resources/lang` (xem `$DirsToCopy` trong script).

---

## 2. Ổ đĩa & khôi phục nhanh

- Đầy disk / `git pull` lỗi: **`docs/STAGING_RECOVERY_LATEST.md`** (stub) và log chi tiết **`docs/STAGING_DISK_RECOVERY_2026-03-27.md`**.
- Không xóa `vendor/**/.git` lẻ tẻ; nếu vendor hỏng: `rm -rf vendor` + `composer install --no-dev` theo lock.

---

## 3. Kiểm tra nhanh sau deploy (SSH)

```bash
ssh craveva-staging "df -h /"
ssh craveva-staging "cd /var/www/craveva-staging/current/craveva && sudo -u www-data php artisan about | head -25"
ssh craveva-staging "curl -sI -o /dev/null -w '%{http_code}\n' https://staging.craveva.com/"
```

Mục tiêu: root còn **> ~2 GB** trống; HTTP **200**; Laravel boot không lỗi.

### Sau khi đổi Cloud SQL (Authorized networks / IP DB)

`.env` trên staging đã trỏ **`DB_HOST=136.110.52.19`** (instance `craveva-staging-db`). Không cần sửa **App URL** trong admin nếu **`APP_URL=https://staging.craveva.com`** đã đúng trong `.env`.

Trên server (sau khi GCP đã mở IP / allowlist):

```bash
cd /var/www/craveva-staging/current/craveva
sudo -u www-data php artisan db:show | head -15
sudo -u www-data php artisan config:clear
sudo -u www-data php artisan cache:clear
```

Kiểm tra web: `curl -sI -o /dev/null -w '%{http_code}\n' https://staging.craveva.com/` → **200**.

Chi tiết allowlist / AI → DB: **`docs/ENG_AI_MYSQL_CONNECTIVITY_BOSS_REPORT.md`**.

---

## 4. Tài liệu liên quan (không gộp nội dung)

| File                                            | Nội dung                                      |
| ----------------------------------------------- | --------------------------------------------- |
| `docs/SERVER_RUNBOOK_VI.md`                     | Deploy hub/staging, quyền, queue, bẫy         |
| `docs/STAGING_DISK_RECOVERY_2026-03-27.md`      | Nhật ký incident ổ đĩa (2026-03-27)           |
| `docs/STAGING_PHP83_L11_DEPLOY_PROGRESS.md`     | Tiến độ PHP 8.3 / L11, composer, cache        |
| `docs/STAGING_RECOVERY_LATEST.md`               | Stub trỏ về tài liệu này + recovery tối thiểu |
| `docs/ENG_AI_MYSQL_CONNECTIVITY_BOSS_REPORT.md` | DB staging / allowlist / AI connectivity (EN) |
| `scripts/download_staging_logs.ps1`             | Tải log Nginx/PHP từ staging về máy local     |

---

## 5. Rehearsal dữ liệu SO/DO (Phase 3)

Áp dụng khi chuẩn bị cutover refactor `SO -> DO`, mục tiêu là **đối soát số liệu trước/sau** mà **không sửa dữ liệu thật**.

### 5.1 Tạo baseline (dry-run, không mutate)

```bash
cd /var/www/craveva-staging/current/craveva
sudo -u www-data php artisan purchase:sales-do-migration-rehearsal \
  --company_id=10 \
  --sample=50 \
  --output=storage/app/reports/sales-do-baseline-company10.json
```

### 5.2 Chạy reconciliation dựa trên baseline

```bash
cd /var/www/craveva-staging/current/craveva
sudo -u www-data php artisan purchase:sales-do-reconcile-report \
  --company_id=10 \
  --baseline=storage/app/reports/sales-do-baseline-company10.json \
  --sample=50 \
  --output=storage/app/reports/sales-do-reconcile-company10.json
```

### 5.3 Tiêu chí pass nhanh

- `comparison.shipments_count_delta == 0`
- `comparison.items_count_delta == 0`
- `comparison.total_quantity_shipped_delta == 0`
- `comparison.quality_checks_ok.orphan_item_count_is_zero == true`
- `comparison.quality_checks_ok.duplicate_shipment_number_count_is_zero == true`

Nếu có delta khác `0` hoặc quality check fail: **không bật cutover**, lưu report và điều tra trước.

### 5.4 Ghi chú an toàn

- Hai command trên hiện là **read/report only** (không migrate dữ liệu).
- Command rehearsal có `--execute` nhưng hiện chỉ là placeholder, chưa chạy mutate.
- Luôn giữ baseline/reconcile report để audit trước khi làm bước migrate thật.

### 5.5 Script gate tự động (khuyến nghị)

Đã có script chạy trọn gói baseline + reconcile + gate:

```bash
cd /var/www/craveva-staging/current/craveva
bash scripts/staging_sales_do_rehearsal_gate.sh 10 50
```

Gate sẽ fail (exit code `1`) nếu:

- `shipments_count_delta != 0`
- `items_count_delta != 0`
- `total_quantity_shipped_delta != 0`
- hoặc quality check orphan/duplicate không đạt.

### 5.6 Safe one-command cho staging (preflight + backup + gate)

Nếu muốn chạy rehearsal có "hàng rào an toàn" trong một lệnh:

```bash
cd /var/www/craveva-staging/current/craveva
bash scripts/staging_phase3_safe_execute.sh 10 50
```

Script sẽ:

1. Preflight:
    - check dung lượng đĩa `/` (mặc định yêu cầu >= 2048MB trống),
    - check app boot (`php artisan about`),
    - check DB connectivity (`php artisan db:show`).
2. Backup DB MySQL/MariaDB từ cấu hình hiện tại trong `.env`.
3. Chạy gate rehearsal (`staging_sales_do_rehearsal_gate.sh`).

Biến môi trường tùy chọn:

- `NO_BACKUP=1` để bỏ backup (không khuyến nghị).
- `MIN_FREE_MB=3072` để tăng ngưỡng disk check.
- `BACKUP_DIR=storage/app/backups/phase3` để đổi nơi lưu backup.

### 5.7 Chạy từ máy local Windows (PowerShell)

Đã có script wrapper để chạy remote an toàn qua SSH:

```powershell
.\scripts\run_staging_phase3_safe_execute.ps1 -SshHost craveva-staging -CompanyId 10 -Sample 50
```

Điểm an toàn thêm:

- Script sẽ tự chuẩn hóa line-ending shell script trên staging (`sed -i 's/\r$//'`) trước khi chạy để tránh lỗi CRLF.
- Có thể dùng `-NoBackup` nếu cần chạy nhanh, nhưng mặc định vẫn backup trước khi gate.

### 5.8 Migrate thật + rollback (Phase 3 execute window)

Khi đã có rehearsal pass và cần migrate dữ liệu thật sang thực thể mới:

```bash
cd /var/www/craveva-staging/current/craveva
sudo -u www-data php artisan purchase:sales-do-migrate-data \
  --company_id=10 \
  --chunk=200 \
  --execute \
  --force \
  --output=storage/app/reports/sales-do-migrate-execute-company10.json
```

Command sẽ sinh rollback manifest tại `storage/app/reports/sales-do-migrate-manifest-<run-id>.json`.

Dry-run rollback (không xóa dữ liệu):

```bash
sudo -u www-data php artisan purchase:sales-do-migrate-rollback \
  --manifest=storage/app/reports/sales-do-migrate-manifest-<run-id>.json
```

Execute rollback (xóa bản ghi đã tạo theo manifest):

```bash
sudo -u www-data php artisan purchase:sales-do-migrate-rollback \
  --manifest=storage/app/reports/sales-do-migrate-manifest-<run-id>.json \
  --execute \
  --force
```

### 5.9 Precheck gate trước Phase 4 cutover

Dùng script precheck để xác nhận staging sẵn sàng trước khi bật cutover:

```bash
cd /var/www/craveva-staging/current/craveva
bash scripts/staging_phase4_cutover_precheck.sh 20 20
```

Script sẽ kiểm tra:

1. Disk/app/db preflight.
2. Command cần thiết có sẵn (`rehearsal/reconcile/migrate/rollback`).
3. Bảng nguồn + bảng đích (`sales_shipments`, `sales_shipment_items`, `sales_dos`, `sales_do_items`).
4. Reconciliation gate pass.
5. Migrate dry-run report tạo thành công.

Nếu PASS, script sẽ in sẵn lệnh execute migrate và rollback để chạy trong cửa sổ cutover.

---

_Cập nhật: 2026-04-04 — nội dung deploy/quyền/queue chuyển sang `SERVER_RUNBOOK_VI.md`; file này giữ zip upload, disk, Cloud SQL, rehearsal Phase 3._
