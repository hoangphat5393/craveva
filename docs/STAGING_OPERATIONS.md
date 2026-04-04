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

**Quan trọng — quyền `storage/` sau deploy:** mọi lệnh `artisan` có ghi cache/config (**`optimize:clear`**, **`cache:clear`**) nên chạy **`sudo -u www-data`**, không chạy thuần `sudo php artisan ...` (dễ tạo file/dir dưới `storage/framework/cache` **owner root** → import Client / queue báo _Permission denied_). Nếu vẫn lỗi: `chown` + `setfacl` như `docs/LARAVEL_PHP_FPM_QUEUE_PERMISSIONS_VI.md` (mục Import Client). Queue worker và Supervisor nên **`user=www-data`** trùng với PHP-FPM.

**Không** chạy `composer update` trên staging (chỉ `install` theo `composer.lock` đã commit). Chi tiết: `docs/STAGING_PHP83_L11_DEPLOY_PROGRESS.md`.

**Trước khi pull:** trên server thường có file `M` dưới `storage/` — có thể cần `git stash` hoặc chỉ checkout phần code, tránh kẹt merge.

### Tránh vòng lặp “pull lỗi → chown → pull lại”

Chu kỳ đó **không phải bình thường** — thường do **một lần** đã **`chown -R www-data`** (hoặc **root**) lên **cả mã nguồn** Git (`app/`, `config/`, …) nên user deploy **không ghi** được khi `git pull` / `composer`.

**Phòng (làm đúng thì ít phải sửa tay):**

1. **Không** chạy `sudo chown -R www-data:www-data` trên **toàn bộ** thư mục app — chỉ **`storage/`**, **`bootstrap/cache`**, và (nếu cần) **`public/`** như `scripts/upload_staging.ps1`.
2. Mọi **`php artisan`** có ghi cache: **`sudo -u www-data php artisan …`** (không `sudo php` thuần).
3. **`git pull`** và **`composer install`**: luôn user **deploy** (`ubuntu`, `hoangphat5393`, …).

**Khi đã kẹt — gỡ nhanh một lần** (trên server, đứng trong thư mục app; thay `APP` nếu khác):

```bash
APP=/var/www/craveva-staging/current/craveva
cd "$APP"
U=$(whoami)
for d in app bootstrap config database resources routes Modules public vendor; do
  [ -d "$APP/$d" ] && sudo chown -R "$U:$U" "$APP/$d"
done
for f in artisan composer.json composer.lock package.json vite.config.js webpack.mix.js; do
  [ -f "$APP/$f" ] && sudo chown "$U:$U" "$APP/$f"
done
sudo chown -R www-data:www-data "$APP/storage" "$APP/bootstrap/cache"
sudo chmod -R ug+rwX "$APP/storage" "$APP/bootstrap/cache"
git pull --ff-only origin main
```

`bootstrap/` ở trên gồm cả `app.php`; **`bootstrap/cache`** được `chown` lại **`www-data`** ngay sau — file cache đã build vẫn do web ghi. Nếu nghi ngờ cache lệch: `sudo -u www-data php artisan optimize:clear`.

**Tùy chọn lâu dài:** thêm user deploy vào nhóm **`www-data`** (`sudo usermod -aG www-data hoangphat5393`) và đặt **setgid + ACL** trên `storage` / `bootstrap/cache` (giống cuối `upload_staging.ps1`) — vẫn **giữ owner code là deploy** để `git pull` nhẹ.

### Supervisor — worker queue nền (staging)

**Đã bật trên VM** (gói `supervisor`, program **`craveva-queue-all`**). File cấu hình: **`/etc/supervisor/conf.d/craveva-queue-all.conf`** (tạo từ `deploy/supervisor/craveva-queue-all.conf.example` — danh sách `--queue=` khớp `app/Console/Kernel.php::DATABASE_WORKER_QUEUE_NAMES`).

```bash
sudo supervisorctl status
sudo supervisorctl restart craveva-queue-all:*
sudo tail -f /var/log/supervisor/craveva-queue-all.log
```

Sau deploy lớn (đổi code queue): **`sudo supervisorctl restart craveva-queue-all:*`**.

**`.env`:** nên **`IMPORT_PROGRESS_RUN_QUEUE_WORKER=false`** (hoặc không set). Nếu **`true`** trong khi Supervisor đã chạy: mỗi lần poll import vẫn gọi thêm `queue:work` trong request HTTP → **dư worker**, tăng tải, dễ **timeout FPM/Nginx**; job vẫn được xử lý nhưng không cần thiết.

Cron `schedule:run` vẫn có thể chạy `queue:work --stop-when-empty` trong `Kernel` — trùng một phần với Supervisor; có thể gỡ dòng đó sau khi Supervisor ổn định để tránh hai nguồn worker.

### Users & quyền — bảng tham chiếu (staging)

**App path:** `/var/www/craveva-staging/current/craveva`  
Trên GCP Ubuntu, user SSH thường là **`ubuntu`** (hoặc tài khoản deploy bạn dùng). **PHP-FPM pool** và **Supervisor worker** nên là **`www-data`**.

| Thành phần                                                                                                            | User / nhóm chạy                                                                          | Ghi chú                                                                                                                                                                                                                                                              |
| --------------------------------------------------------------------------------------------------------------------- | ----------------------------------------------------------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **SSH, `git pull`**                                                                                                   | User đăng nhập (**thường `ubuntu`**)                                                      | File **được Git cập nhật** (tracked) mang **owner = user chạy `git`** (`ubuntu:ubuntu`, …). **Không** tự động thành `www-data`. Code trong `app/`, `resources/`, … sau pull vẫn đọc được vì world/other thường có quyền đọc; **ghi** (hiếm) phụ thuộc quyền thư mục. |
| **Nginx**                                                                                                             | master **root**, worker thường **`www-data`**                                             | Chỉ đọc `public/`; PHP không chạy trực tiếp trong process nginx.                                                                                                                                                                                                     |
| **PHP 8.3 FPM**                                                                                                       | **`www-data`** (pool `www.conf`: `user` / `group`)                                        | Mọi request web (trình duyệt, upload, poll import) chạy PHP với quyền này → ghi `storage/`, `bootstrap/cache` nếu thư mục cho phép.                                                                                                                                  |
| **`composer install` / `composer update`**                                                                            | Nên user **deploy (`ubuntu`)** hoặc root rồi **sửa owner** phần cần web ghi               | **`vendor/`** thường **755** + owner là user chạy composer → **FPM vẫn đọc được** (execute + read). Tránh để `vendor` chỉ root đọc (700). Trên staging khuyến nghị: **`composer install`** (theo lock), không `update` lung tung.                                    |
| **`php artisan …` có ghi cache / `storage`** (`optimize:clear`, `cache:clear`, `view:clear`, `migrate` nếu ghi, v.v.) | **`sudo -u www-data php …`**                                                              | Chạy **`sudo php`** → file/dir mới dưới `storage/framework/cache`, `storage/logs`, `bootstrap/cache` có thể thành **root:root** → FPM / queue **Permission denied**.                                                                                                 |
| **Supervisor `queue:work`**                                                                                           | **`user=www-data`** trong `/etc/supervisor/conf.d/craveva-queue-all.conf`                 | Job import / queue ghi cache, log → cùng “ý” với FPM.                                                                                                                                                                                                                |
| **Cron `schedule:run`**                                                                                               | Nên cấu hình chạy **`www-data`** hoặc `cd … && sudo -u www-data php artisan schedule:run` | Nếu cron là **root** mà lệnh gọi `php` không qua `-u www-data`, mọi thứ artisan tạo ra có thể **root** → lỗi tương tự.                                                                                                                                               |

**`storage/` và `bootstrap/cache` — ai được ghi, file mới ra sao**

| Thư mục (tiêu biểu)                                              | Owner mong muốn         | File / thư mục mới                                                                                                                                      |
| ---------------------------------------------------------------- | ----------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **`storage/logs`**                                               | **`www-data:www-data`** | Log Laravel do **FPM** hoặc **queue** ghi. Script zip deploy đặt `chmod 2777` + ACL để log rotate / tạo file mới ít kẹt (`scripts/upload_staging.ps1`). |
| **`storage/framework/cache`**                                    | **`www-data:www-data`** | Cache file (metrics import, v.v.). Thư mục con do Laravel tạo — cần **g+w** hoặc **ACL default** để `www-data` luôn ghi được.                           |
| **`storage/framework/sessions`**, **`views`**, **`storage/app`** | **`www-data:www-data`** | Session file, compiled view cache (nếu dùng file), upload.                                                                                              |
| **`bootstrap/cache`** (`config.php`, `routes-v7.php`, …)         | **`www-data:www-data`** | `php artisan config:cache` nếu chạy **`sudo -u www-data`** thì file thuộc `www-data`; chạy root → FPM không ghi đè được.                                |

**Sau `git pull`:** chỉ các file **thuộc repo** đổi owner theo user pull; **`storage/`** (thường gitignore) và file đã có trong `bootstrap/cache` **không** bị Git đổi owner. Nếu vừa pull xong và chạy **`sudo php artisan optimize:clear`** → nguy cơ **root** sở hữu cache mới → nên **`sudo -u www-data php artisan optimize:clear`**.

**View (Blade) — file mới:** với `CACHE_DRIVER=file`, view compiled có thể nằm **`storage/framework/views`**. User tạo file = process chạy PHP (**FPM** hoặc **CLI**). **Trình duyệt** không tạo file trực tiếp; **request qua FPM** (`www-data`) mới compile view → file mới thường **`www-data`**. Nếu compile chạy CLI sai user → owner sai.

**Khi “ngày sau” import / log lại báo Permission denied:** thường do **file hoặc thư mục mới** (`storage/logs/laravel-*.log`, file cache metrics, thư mục `storage/framework/cache/data/...`) bị tạo bởi **root** hoặc user khác **`www-data`**, hoặc thư mục cha **không có g+w / ACL default**. **Phòng:** không chạy `sudo php artisan` thuần; giữ **ACL + setgid** trên `storage` (như `upload_staging.ps1`). **Sửa một lần:** dùng khối lệnh dưới (gom cả **log**).

**Khắc phục nhanh khi lệch quyền (trên server):**

```bash
APP=/var/www/craveva-staging/current/craveva
sudo chown -R www-data:www-data $APP/storage $APP/bootstrap/cache
sudo chmod -R ug+rwX $APP/storage $APP/bootstrap/cache
sudo find $APP/storage $APP/bootstrap/cache -type d -exec chmod g+s {} \;
sudo mkdir -p $APP/storage/logs
sudo chown -R www-data:www-data $APP/storage/logs
sudo chmod 2777 $APP/storage/logs
sudo find $APP/storage/logs -maxdepth 1 -type f -name '*.log' -exec chmod 666 {} + 2>/dev/null || true
DEPLOY_USER=$(whoami)
sudo setfacl -R -m u:www-data:rwX,u:$DEPLOY_USER:rwX $APP/storage $APP/bootstrap/cache $APP/storage/logs 2>/dev/null || true
sudo setfacl -dR -m u:www-data:rwX,u:$DEPLOY_USER:rwX $APP/storage $APP/bootstrap/cache $APP/storage/logs 2>/dev/null || true
```

Sau đó (tùy): `sudo supervisorctl restart craveva-queue-all:*` và thử import lại. Chi tiết: `docs/LARAVEL_PHP_FPM_QUEUE_PERMISSIONS_VI.md`.

### Một lần cấu hình — “chung quyền” mà không cần nhớ nhiều user

**Thực tế:** **Ubuntu không gộp** `ubuntu` / `hoangphat5393` với `www-data` thành **một** user. Cách gọn nhất là **chỉ nhớ 2 vai trò** và **cài một lần** cho vùng ghi Laravel:

| Vai trò                       | User                 | Việc gì                                                               |
| ----------------------------- | -------------------- | --------------------------------------------------------------------- |
| **Chạy web + queue**          | **`www-data`**       | PHP-FPM pool + Supervisor — **giữ mặc định**, **đừng** đổi lung tung. |
| **Git + composer + sửa code** | **User SSH của bạn** | `git pull`, `composer install`, mở editor.                            |

**Để bạn và `www-data` cùng ghi được `storage/` + `bootstrap/cache` (ít phải `chown` lại):**

1. **Thêm user deploy vào nhóm `www-data`** (một lần; thay `hoangphat5393` bằng đúng user SSH):

    ```bash
    sudo usermod -aG www-data hoangphat5393
    ```

    **Đăng xuất SSH / mở session mới** (hoặc `newgrp www-data`) để nhóm có hiệu lực.

2. **Chạy khối ACL + `storage` ở mục “Khắc phục nhanh” phía trên** (hoặc deploy bằng `upload_staging.ps1` — đã có `setfacl`). **ACL default** (`-dR`) khiến **file/thư mục mới** trong các thư mục đó vẫn cho **cả `www-data` lẫn user deploy** ghi được.

3. **Chỉ cần nhớ thêm một quy tắc:** mọi `php artisan` **có ghi cache/config** → **`sudo -u www-data php artisan …`** (không `sudo php` thuần). Có thể đặt **alias** trên server: `alias art='sudo -u www-data php artisan'`.

**Kết quả:** FPM và Supervisor **vẫn** là `www-data` (đúng chuẩn Ubuntu); bạn **không** phải đổi owner code mỗi lần cho web; vùng **Laravel cần ghi** được **chia sẻ** bằng **nhóm + ACL** thay vì nhớ từng user từng lệnh.

_(Đổi cả PHP-FPM pool sang user deploy — ví dụ `ubuntu` — được nhưng phải sửa `www.conf`, dễ lệch với tài liệu/hosting; **không khuyến nghị** trừ khi bạn chủ động chuẩn hóa toàn VM.)_

---

## 3. Script `scripts/upload_staging.ps1` (zip từng phần)

- **Mục đích:** đóng gói **danh sách file/thư mục** cục bộ → zip → `scp` → giải nén lên staging.
- **Mặc định an toàn:** biến **`$RemoteWipeAppBeforeUnzip = $false`** — **không** xóa sạch thư mục app trước khi unzip; chỉ đè file có trong zip. Bật `$true` chỉ khi zip chứa **đủ** mã nguồn (kể cả `app/Console/Kernel.php`); trước đây thiếu `app/Console` trong zip + bật wipe dễ làm **mất lịch queue** (`Kernel.php`).
- Script đã thêm **`app/Console`** vào `$DirsToCopy` và kiểm tra **`app/Console/Kernel.php`** trong bước verify.
- **Rủi ro (khi bật wipe):** lệnh **`find ... -exec rm -rf`** xóa gần như mọi thứ **trừ** `.env`, `storage`, `.git`. **Ưu tiên Git deploy** (mục 2).
- Danh sách file trong script có thể **trùng lặp**; script đã gom **`Select-Object -Unique`** trước khi copy để gọn và dễ đọc.
- Thư mục copy nguyên khối: ví dụ `app/Console`, `Modules/LanguagePack/Languages/modules`, `resources/lang` (xem `$DirsToCopy` trong script).

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
