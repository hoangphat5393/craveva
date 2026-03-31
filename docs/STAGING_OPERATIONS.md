# Staging — vận hành & deploy (Craveva)

**Host SSH:** `craveva-staging`  
**App:** `/var/www/craveva-staging/current/craveva`  
**URL:** `https://staging.craveva.com`

**Nguyên tắc:** Staging **luôn lấy mã nguồn từ Git** (`git pull` trên `main`, working tree sạch). **Không** sửa tay / `scp` từng file PHP lên server cho thay đổi tính năng (dễ lệch commit, `git pull` báo conflict hoặc “untracked would be overwritten”). Chỉnh trên máy local → commit → push → rồi pull trên staging. Cấu hình server-only (cron, systemd, `.env`) giữ tách khỏi tree hoặc document trong `deploy/`.

Tài liệu này gộp quy trình chuẩn; log sự cố theo ngày vẫn giữ riêng trong repo (xem mục _Tài liệu liên quan_).

---

## 1. Ngôn ngữ: dùng `en`, không dùng `eng`

- **Chuẩn mã ngôn ngữ:** **`en`** (ISO 639-1), khớp `APP_LOCALE` / `LanguageSetting.language_code`.
- **`eng`** là tên cũ; DB đã có migration chuẩn hóa `eng` → `en` (`database/migrations/2026_03_13_100000_standardize_language_code_eng_to_en.php`).
- File dịch Laravel đọc theo locale hiện tại: với `locale=en` cần có **`lang/en/`** hoặc **`Modules/.../Resources/lang/en/`**. Thư mục chỉ có `eng/` sẽ **không** được dùng tự động.
- **Đồng bộ module với Language Pack (nguồn):** `Modules/LanguagePack/Languages/modules/<Module>/en/` → copy/publish sang `Modules/<Module>/Resources/lang/en/` khi cần (hoặc `php artisan languagepack:publish-translation` với quyền và DB đúng).

---

## 2. Cách deploy khuyến nghị: Git (bắt buộc ưu tiên)

Luôn **`git pull`** để đồng bộ với `origin/main`. Trước khi pull: `git status` — nếu có file **modified** hoặc **untracked** trùng đường dẫn với bản trên Git, xử lý (`git checkout -- <file>`, `stash`, hoặc `mv` file tạm ra ngoài) để **không** chặn merge.

```bash
cd /var/www/craveva-staging/current/craveva
git fetch origin main
git pull --ff-only origin main
```

Sau đó (production):

```bash
APP_ENV=production composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist
sudo -u www-data php artisan migrate --force
sudo -u www-data php artisan optimize:clear
```

**Không** chạy `composer update` trên staging (chỉ `install` theo `composer.lock` đã commit). Chi tiết: `docs/STAGING_PHP83_L11_DEPLOY_PROGRESS.md`.

**Trước khi pull:** trên server thường có file `M` dưới `storage/` — có thể cần `git stash` hoặc chỉ checkout phần code, tránh kẹt merge.

---

## 3. Script `scripts/upload_staging.ps1` (zip từng phần)

- **Mục đích:** đóng gói **danh sách file/thư mục** cục bộ → zip → `scp` → giải nén lên staging.
- **Rủi ro:** lệnh remote có đoạn **`find ... -maxdepth 1 ... -exec rm -rf`** (xóa gần như mọi thứ trong app **trừ** `.env`, `storage`, `.git`). Chỉ dùng khi hiểu rõ hậu quả; **ưu tiên Git deploy** (mục 2).
- Danh sách file trong script có thể **trùng lặp**; script đã gom **`Select-Object -Unique`** trước khi copy để gọn và dễ đọc.
- Thư mục copy nguyên khối: ví dụ `Modules/LanguagePack/Languages/modules`, `resources/lang` (xem biến `$DirsToCopy` trong script).

---

## 4. Ổ đĩa & khôi phục nhanh

- Đầy disk / `git pull` lỗi: **`docs/STAGING_RECOVERY_LATEST.md`** (stub) và log chi tiết **`docs/STAGING_DISK_RECOVERY_2026-03-27.md`**.
- Không xóa `vendor/**/.git` lẻ tẻ; nếu vendor hỏng: `rm -rf vendor` + `composer install --no-dev` theo lock.

---

## 5. Kiểm tra nhanh sau deploy (SSH)

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

## 6. Tài liệu liên quan (không gộp nội dung)

| File                                            | Nội dung                                      |
| ----------------------------------------------- | --------------------------------------------- |
| `docs/STAGING_DISK_RECOVERY_2026-03-27.md`      | Nhật ký incident ổ đĩa (2026-03-27)           |
| `docs/STAGING_PHP83_L11_DEPLOY_PROGRESS.md`     | Tiến độ PHP 8.3 / L11, composer, cache        |
| `docs/STAGING_RECOVERY_LATEST.md`               | Stub trỏ về tài liệu này + recovery tối thiểu |
| `docs/ENG_AI_MYSQL_CONNECTIVITY_BOSS_REPORT.md` | DB staging / allowlist / AI connectivity (EN) |
| `scripts/download_staging_logs.ps1`             | Tải log Nginx/PHP từ staging về máy local     |

---

## 7. Rehearsal dữ liệu SO/DO (Phase 3)

Áp dụng khi chuẩn bị cutover refactor `SO -> DO`, mục tiêu là **đối soát số liệu trước/sau** mà **không sửa dữ liệu thật**.

### 7.1 Tạo baseline (dry-run, không mutate)

```bash
cd /var/www/craveva-staging/current/craveva
sudo -u www-data php artisan purchase:sales-do-migration-rehearsal \
  --company_id=10 \
  --sample=50 \
  --output=storage/app/reports/sales-do-baseline-company10.json
```

### 7.2 Chạy reconciliation dựa trên baseline

```bash
cd /var/www/craveva-staging/current/craveva
sudo -u www-data php artisan purchase:sales-do-reconcile-report \
  --company_id=10 \
  --baseline=storage/app/reports/sales-do-baseline-company10.json \
  --sample=50 \
  --output=storage/app/reports/sales-do-reconcile-company10.json
```

### 7.3 Tiêu chí pass nhanh

- `comparison.shipments_count_delta == 0`
- `comparison.items_count_delta == 0`
- `comparison.total_quantity_shipped_delta == 0`
- `comparison.quality_checks_ok.orphan_item_count_is_zero == true`
- `comparison.quality_checks_ok.duplicate_shipment_number_count_is_zero == true`

Nếu có delta khác `0` hoặc quality check fail: **không bật cutover**, lưu report và điều tra trước.

### 7.4 Ghi chú an toàn

- Hai command trên hiện là **read/report only** (không migrate dữ liệu).
- Command rehearsal có `--execute` nhưng hiện chỉ là placeholder, chưa chạy mutate.
- Luôn giữ baseline/reconcile report để audit trước khi làm bước migrate thật.

### 7.5 Script gate tự động (khuyến nghị)

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

### 7.6 Safe one-command cho staging (preflight + backup + gate)

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

### 7.7 Chạy từ máy local Windows (PowerShell)

Đã có script wrapper để chạy remote an toàn qua SSH:

```powershell
.\scripts\run_staging_phase3_safe_execute.ps1 -SshHost craveva-staging -CompanyId 10 -Sample 50
```

Điểm an toàn thêm:

- Script sẽ tự chuẩn hóa line-ending shell script trên staging (`sed -i 's/\r$//'`) trước khi chạy để tránh lỗi CRLF.
- Có thể dùng `-NoBackup` nếu cần chạy nhanh, nhưng mặc định vẫn backup trước khi gate.

### 7.8 Migrate thật + rollback (Phase 3 execute window)

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

### 7.9 Precheck gate trước Phase 4 cutover

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

_Cập nhật: 2026-03-30 — bổ sung runbook rehearsal Phase 3 (baseline + reconcile), script gate tự động, và safe one-command (preflight + backup + gate)._
