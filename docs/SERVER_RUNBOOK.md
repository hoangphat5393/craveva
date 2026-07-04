# Runbook server — Staging & Hub go-live (Craveva)

**Mục đích:** một chỗ cho deploy **hub (live)** và **staging**, quyền `storage`, queue worker, và **các bẫy đã gặp** — tránh lặp lỗi cũ (Permission denied import, hai worker, `sudo php artisan`, DNS IP, v.v.).

| Môi trường   | App (chuẩn tham chiếu)                                              | URL / ghi chú                                                                           |
| ------------ | ------------------------------------------------------------------- | --------------------------------------------------------------------------------------- |
| **Staging**  | `/var/www/craveva-staging/current/craveva`                          | `https://staging.craveva.com` — IP VM có thể đổi sau stop/start; cập nhật DNS A record. |
| **Hub live** | Điền đúng path trên server hub (vd. `/var/www/.../current/craveva`) | Kiểm tra `docs/GCP_INVENTORY.md` / firewall Cloud SQL.                    |

**Tài liệu chuyên sâu (không gộp vào đây):** import/poll/queue trong code (+ tracker SO/PO–Inventory) → `FUNC_IMPROVE/IMPORT_POLL_TRACKERS.md`. Rehearsal Phase 3 SO/DO trên staging → `docs/STAGING_OPERATIONS.md` (chỉ còn mục dài).

---

## 1. Bẫy đã gặp — checklist nhanh

| #   | Triệu chứng / nguyên nhân                                                                                                                      | Việc phải làm                                                                                                                                                                                                                                                                                                                            |
| --- | ---------------------------------------------------------------------------------------------------------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| 1   | **`Permission denied`** `storage/framework/cache/data/...` khi import / queue                                                                  | FPM và **`queue:work` cùng user `www-data`**. Không chạy **`sudo php artisan …`** (tạo file **root**). Dùng **`sudo -u www-data php artisan …`**. Xem [mục 4](#4-quyền-storage-queue-supervisor--systemd).                                                                                                                               |
| 2   | **Hai** process `queue:work` — một user **deploy**, một **`www-data`**                                                                         | Trùng **Supervisor** + **`craveva-queue-all.service` (systemd)**. Chỉ giữ **một** nguồn. Staging: `sudo systemctl disable --now craveva-queue-all.service`. Mẫu unit: [mục 10.5](#deploy-systemd-unit) (đã sửa `User=www-data`; vẫn **không** bật song song Supervisor).                                                                 |
| 3   | **`staging.craveva.com` timeout** sau đổi VM                                                                                                   | DNS A record trỏ **IP cũ**. Lấy IP mới: GCP Console hoặc `gcloud compute instances describe craveva-staging --zone=... --format='value(networkInterfaces[0].accessConfigs[0].natIP)'`.                                                                                                                                                   |
| 4   | **`IMPORT_PROGRESS_RUN_QUEUE_WORKER=true`** + Supervisor                                                                                       | Dư worker, poll HTTP nặng, dễ timeout. Staging/production: **`false`** hoặc unset; dựa Supervisor/cron.                                                                                                                                                                                                                                  |
| 5   | **`chown -R www-data`** lên **cả mã nguồn**                                                                                                    | `git pull` / deploy user **không ghi** được code. Chỉ `chown` **`storage/`**, **`bootstrap/cache`** (và `public/` upload nếu cần).                                                                                                                                                                                                       |
| 6   | RAM nhỏ + import lớn                                                                                                                           | OOM / load cao. Tăng RAM VM; có thể tạm `supervisorctl stop craveva-queue-all:*`.                                                                                                                                                                                                                                                        |
| 7   | **DataTables / SQL 1146** — thiếu `sales_dos`, `grns`, …                                                                                       | Code đã **pin** bảng mới (`SalesDoRuntime`, `GrnRuntime` = `true`). Chạy **`php artisan migrate`**. Nếu báo _Nothing to migrate_ mà bảng vẫn không có → bảng `migrations` **lệch** (đã ghi Ran nhưng bảng chưa từng tạo): xóa dòng migration tương ứng rồi `migrate` lại — xem [mục 9](#9-schema-cutover--sales-do--grn-kiểm-tra-nhanh). |
| 8   | **Language Pack** — Sync keys / **Publish All** — `Permission denied` (`LanguagePack/Languages`, `resources/lang`, `Modules/*/Resources/lang`) | FPM = **www-data**; chỉnh **owner deploy + group www-data**, `chmod ug+rwX`, `g+s` trên các thư mục đó — [mục 4.8](#48-language-pack--sync-keys--publish-all-ghi-file-trong-repo).                                                                                                                                                       |

---

## 2. Hub (live) — checklist deploy & nâng cấp

1. **Chuẩn bị:** code đã merge; ghi SHA commit; đã test staging; `rg "->change\\(|->renameColumn\\(" database/migrations Modules -g "*.php"` không còn migration DBAL nguy hiểm bất ngờ.
2. **Backup bắt buộc:** snapshot VM/volume + dump DB (`mysqldump … > backup_before_YYYYMMDD_HHMM.sql`).
3. **Maintenance:** thông báo; cửa sổ off-peak; một người theo dõi log.
4. **Deploy code:** SSH → `cd <APP>` → `git fetch --all --prune` → `git checkout <branch>` → `git pull --ff-only` → xác nhận `HEAD` = SHA đã chốt.
5. **Composer:** `composer install --no-dev --optimize-autoloader` — **không** `composer update` trên live.
6. **Artisan có ghi cache / storage — luôn user web:**

```bash
# Thay <APP> = đường dẫn thư mục chứa artisan
sudo -u www-data php <APP>/artisan optimize:clear
sudo -u www-data php <APP>/artisan config:cache
sudo -u www-data php <APP>/artisan route:cache
sudo -u www-data php <APP>/artisan view:cache
```

**Không** dùng `php artisan optimize:clear` thuần sau `sudo su` nếu kết quả là process **root** ghi cache.

7. **Migration:** `sudo -u www-data php artisan migrate --pretend --force` → nếu ổn → `sudo -u www-data php artisan migrate --force` → `migrate:status` không còn Pending.
8. **Health:** `curl -I -sS https://<live-domain>` → 200; `systemctl is-active nginx php8.3-fpm`; xem `journalctl -u nginx -u php8.3-fpm -p err -n 50`; log Laravel không lỗi mới nghiêm trọng.
9. **Smoke:** đăng nhập; order/invoice; warehouse; một luồng import nếu có.
10. **Rollback:** `git checkout <last-good-sha>` + `composer install --no-dev` + cache lại bằng **`sudo -u www-data`**; DB restore từ backup nếu lỗi schema/data.

**Ghi log mỗi lần deploy:** thời gian, người deploy, SHA, lệnh, migrate, health, sự cố.

---

## 3. Staging — deploy Git (tóm tắt)

```bash
cd /var/www/craveva-staging/current/craveva
git fetch origin main
git pull --ff-only origin main
APP_ENV=production composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist
sudo -u www-data php artisan migrate --force
sudo -u www-data php artisan optimize:clear
sudo supervisorctl restart craveva-queue-all:*
```

**Quyền sau deploy:** xem [mục 4](#4-quyền-storage-queue-supervisor--systemd). Chi tiết zip `upload_staging.ps1` → `docs/STAGING_OPERATIONS.md`. **Sao lưu Cloud SQL** (`craveva-staging-db`: lịch, 7 bản, xoay vòng, PITR) → `docs/CLOUD_SQL_BACKUP.md`.

---

## 4. Quyền, storage, queue, Supervisor & systemd

### 4.1 Nguyên tắc

- **PHP-FPM** (thường **`www-data`**) và **`queue:work`** phải **cùng quyền ghi** `storage/` và `bootstrap/cache/`.
- **`CACHE_DRIVER=file`** → import metrics ghi dưới `storage/framework/cache/data/` — worker khác user → **Permission denied**.

### 4.2 Supervisor (khuyến nghị worker nền)

- File: `/etc/supervisor/conf.d/craveva-queue-all.conf` (mẫu: [mục 10.4 — Supervisor](#deploy-supervisor-conf)).
- **`user=www-data`**. Danh sách `--queue=` khớp `app/Console/Kernel.php::DATABASE_WORKER_QUEUE_NAMES`.

```bash
sudo supervisorctl status
sudo supervisorctl restart craveva-queue-all:*
```

### 4.3 Không chạy trùng systemd `craveva-queue-all.service`

Nếu vừa **Supervisor** vừa **systemd** với `queue:work` → hai worker; unit cũ từng để **`User=hoangphat5393`** gây lỗi cache. **Chỉ một nguồn:**

```bash
sudo systemctl disable --now craveva-queue-all.service
sudo systemctl daemon-reload
ps aux | grep queue:work | grep -v grep   # chỉ còn www-data
```

### 4.4 Cron `schedule:run`

Nên: `* * * * * cd <APP> && sudo -u www-data /usr/bin/php artisan schedule:run >> /dev/null 2>&1`  
(trong crontab user deploy **hoặc** crontab có `sudo -u www-data`).

### 4.5 Kiểm tra nhanh

```bash
APP=/var/www/craveva-staging/current/craveva   # hoặc path hub
ps aux | grep 'php-fpm: pool' | grep -v grep | head -3
ps aux | grep 'queue:work' | grep -v grep
namei -l "$APP/storage/framework/sessions" 2>/dev/null | tail -8
namei -l "$APP/storage/framework/cache/data" 2>/dev/null | tail -15
ls -la "$APP/storage/framework/sessions" | head -5
sudo -u www-data touch "$APP/storage/framework/sessions/.write_test" && sudo rm -f "$APP/storage/framework/sessions/.write_test" && echo OK_sessions
```

### 4.6 Sửa quyền (staging path mẫu — đổi `APP` trên hub)

```bash
APP=/var/www/craveva-staging/current/craveva
sudo chown -R www-data:www-data "$APP/storage" "$APP/bootstrap/cache"
sudo chmod -R ug+rwX "$APP/storage" "$APP/bootstrap/cache"
sudo find "$APP/storage" "$APP/bootstrap/cache" -type d -exec chmod g+s {} \; 2>/dev/null || true
sudo mkdir -p "$APP/storage/logs"
sudo chown -R www-data:www-data "$APP/storage/logs"
sudo chmod 2777 "$APP/storage/logs"
DEPLOY_USER=$(whoami)
sudo setfacl -R  -m u:www-data:rwX,u:$DEPLOY_USER:rwX "$APP/storage" "$APP/bootstrap/cache" "$APP/storage/logs" 2>/dev/null || true
sudo setfacl -dR -m u:www-data:rwX,u:$DEPLOY_USER:rwX "$APP/storage" "$APP/bootstrap/cache" "$APP/storage/logs" 2>/dev/null || true
sudo -u www-data php "$APP/artisan" cache:clear
sudo systemctl reload php8.3-fpm
```

**Script trong repo:** `scripts/staging_fix_storage_permissions.sh` (chạy trên server với `APP=... bash scripts/staging_fix_storage_permissions.sh`). Deploy từ Windows: `scripts/upload_staging.ps1` cuối script đã chạy bước tương tự + `touch` kiểm tra sessions.

**Tuỳ chọn:** `CACHE_DRIVER=redis` nếu đã có Redis — giảm ghi file cache; vẫn cần quyền `storage/logs`.

### 4.7 PHP 8.3 trên Ubuntu (staging đã dùng)

- Pool FPM: `grep -E '^user|^group' /etc/php/8.3/fpm/pool.d/www.conf`
- Socket: thường `unix:/run/php/php8.3-fpm.sock`; có thể `update-alternatives` cho `php-fpm.sock` trỏ 8.3.
- Sau đổi `memory_limit` FPM: `sudo systemctl reload php8.3-fpm`.

### 4.8 Language Pack — Sync keys & Publish All (ghi file trong repo)

**PHP-FPM (`www-data`)** phải **ghi** được các thư mục sau (deploy giữ **owner**, group **`www-data`**, `ug+rwX`, `g+s` trên thư mục):

| Thao tác UI               | Ghi vào đâu                                                                                 |
| ------------------------- | ------------------------------------------------------------------------------------------- |
| **Sync keys**             | `Modules/LanguagePack/Languages/`                                                           |
| **Publish / Publish All** | `resources/lang/{locale}/` (copy từ pack) và từng `Modules/{Name}/Resources/lang/{locale}/` |

**Hub (đổi `APP` nếu khác; `U=$(whoami)` = user deploy SSH):**

```bash
APP=/var/www/hub.craveva.com
cd "$APP"
U=$(whoami)

sudo chown -R "$U":www-data Modules/LanguagePack/Languages resources/lang
sudo chmod -R ug+rwX Modules/LanguagePack/Languages resources/lang
sudo find Modules/LanguagePack/Languages resources/lang -type d -exec chmod g+s {} \;

for d in Modules/*/Resources/lang; do
  [ -d "$d" ] || continue
  sudo chown -R "$U":www-data "$d"
  sudo chmod -R ug+rwX "$d"
  sudo find "$d" -type d -exec chmod g+s {} \;
done
```

**Nếu có `setfacl`:** có thể bổ sung ACL `www-data` + deploy trên các thư mục trên (tương tự `storage`).

**Không** `chown -R www-data:www-data` cả cây `resources/lang` nếu sau đó `git pull` cần ghi — ưu tiên **`deploy:www-data`** + `g+rwX` như trên.

---

## 5. Git / owner code — tránh vòng “pull lỗi → chown → pull”

- **`git pull` / `composer install`:** user **deploy** (ubuntu, …).
- **Không** `chown -R www-data` lên `app/`, `config/`, `vendor/`, …
- **Chỉ** `storage/`, `bootstrap/cache` (và ACL như [mục 4.6](#46-sửa-quyền-staging-path-mẫu--đổi-app-trên-hub)).

**Khi đã kẹt — gỡ nhanh một lần** (trên server; thay `APP` nếu khác):

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

`bootstrap/` ở trên gồm cả `app.php`; **`bootstrap/cache`** được `chown` lại **`www-data`** ngay sau. Nếu nghi cache lệch: `sudo -u www-data php artisan optimize:clear`.

**Tuỳ chọn lâu dài:** `sudo usermod -aG www-data <user_ssh>` + ACL trên `storage` / `bootstrap/cache` (như mục 4.6 và `scripts/upload_staging.ps1`).

---

## 6. Ngôn ngữ `en` (không dùng `eng`)

Locale chuẩn **`en`**. DB đã migration `eng` → `en`. Dịch cần thư mục `lang/en/` hoặc module `Resources/lang/en/`.

---

## 7. Kiểm tra nhanh sau deploy (staging)

```bash
ssh craveva-staging "df -h /"
ssh craveva-staging "cd /var/www/craveva-staging/current/craveva && sudo -u www-data php artisan about | head -25"
ssh craveva-staging "curl -sI -o /dev/null -w '%{http_code}\n' https://staging.craveva.com/"
```

---

## 8. Liên kết

| Nội dung                                         | File                                                  |
| ------------------------------------------------ | ----------------------------------------------------- |
| Import / poll / queue trong code                 | `FUNC_IMPROVE/IMPORT_POLL_TRACKERS.md`             |
| Rehearsal Phase 3, script zip staging            | `docs/STAGING_OPERATIONS.md`                          |
| Inventory RAM/FPM staging & hub                  | `SPECIFICATION/STAGING_HUB_SERVER_INFO_2026-04-06.md` |
| GCP VM + Cloud SQL + allowlist (snapshot)        | `SPECIFICATION/GCP_AND_CLOUDSQL_SNAPSHOT.md`       |
| GCP VM / IP                                      | `docs/GCP_INVENTORY.md`                 |
| Firewall / Cloud SQL                             | `docs/GCP_INVENTORY.md`          |
| Supervisor mẫu                                   | [Mục 10.4](#deploy-supervisor-conf) (trong file này)  |
| Systemd mẫu (chỉ dùng nếu không dùng Supervisor) | [Mục 10.5](#deploy-systemd-unit) (trong file này)     |

---

## 9. Schema cutover — Sales DO + GRN (kiểm tra nhanh)

Runtime đang **cố định** dùng **`sales_dos` / `sales_do_items`** (Sale Delivery Order) và **`grns` / `grn_items`** (Goods Received Note), không còn đọc bảng legacy `sales_shipments` / `delivery_orders` trong code path chính. Thiếu bảng → lỗi **1146** trên DataTables.

**Lệnh kiểm tra (sau deploy / trên local):**

```bash
php artisan purchase:verify-cutover-schema
```

Lệnh kiểm tra tồn tại các bảng: `sales_dos`, `sales_do_items`, `grns`, `grn_items`, `warehouses`, `stock_movements`, `warehouse_product_stock`.

**Nếu thiếu bảng nhưng `php artisan migrate` báo Nothing to migrate:** không xóa thủ công dòng trong bảng `migrations`. Với DB mới, dựng lại từ baseline migration và seed hiện tại. Với DB đang có dữ liệu, khôi phục backup đã kiểm chứng hoặc viết forward repair migration riêng sau khi đối chiếu schema.

**Copy dữ liệu legacy (một lần, khi vẫn còn bảng nguồn):**

- `php artisan purchase:sales-do-migrate-data` — cần `sales_shipments` + `sales_shipment_items` (xem `--help`, mặc định dry-run).
- `php artisan purchase:grn-migrate-data` — cần `delivery_orders` + `delivery_order_items`.

Nếu DB đã bỏ bảng legacy, hai lệnh trên **không chạy được** — đó là bình thường; tạo chứng từ mới trên UI.

**Tài liệu nghiệp vụ:** `FUNC_IMPROVE/05_SO_DO_PO_GRN_REFACTOR.md`, `FUNC_LOGIC/SALES_FULFILLMENT_SCHEMA_MATRIX.md`.

---

## 10. Phụ lục — Mẫu cấu hình server (trước đây thư mục `deploy/`)

Toàn bộ file dưới đây đã **gộp vào runbook**; không còn thư mục `deploy/` trong repo. Khi cài trên server: sao chép nội dung khối tương ứng vào đường dẫn `/etc/...` (hoặc include Nginx), chỉnh `CHANGE_ME_*` / user / path cho đúng môi trường.

<a id="deploy-php-fpm-snippet"></a>

### 10.1 Nginx — `location` PHP 8.3 FPM (Debian)

```nginx
    location ~ [^/]\.php(/|$)
    {
        try_files $uri =404;
        fastcgi_pass  unix:/run/php/php8.3-fpm.sock;
        fastcgi_index index.php;
        include fastcgi.conf;
        include pathinfo.conf;
    }
```

<a id="deploy-hub-php-ini"></a>

### 10.2 Hub — drop-in `php.ini` (khớp aaPanel PHP 8.2 cũ)

Đặt ví dụ tại `/etc/php/8.3/mods-available/99-craveva-hub.ini` (tên tùy policy server) rồi `phpenmod` / restart FPM theo distro.

```ini
; Hub hub.craveva.com — match former aaPanel PHP 8.2 (/www/server/php/82/etc/php.ini)
memory_limit = 128M
post_max_size = 50M
upload_max_filesize = 50M
max_execution_time = 300
max_input_time = 300
```

<a id="deploy-cron-scheduler"></a>

### 10.3 Cron — Laravel `schedule:run` (ví dụ staging)

**Lưu ý:** thay user `hoangphat5393` và path app bằng user/path thực tế; khuyến nghị đồng bộ với [mục 4.4](#44-cron-schedulerun) (`sudo -u www-data` nếu phù hợp chính sách).

```cron
# Laravel scheduler -> install: sudo cp ... /etc/cron.d/craveva-staging && sudo chmod 644 ...
#
SHELL=/bin/sh
PATH=/usr/local/sbin:/usr/local/bin:/sbin:/bin:/usr/sbin:/usr/bin

# Deploy user must be able to append storage/logs (e.g. member of www-data).
* * * * * hoangphat5393 cd /var/www/craveva-staging/current/craveva && /usr/bin/php artisan schedule:run >> /var/www/craveva-staging/current/craveva/storage/logs/scheduler-cron.log 2>&1
```

<a id="deploy-supervisor-conf"></a>

### 10.4 Supervisor — `craveva-queue-all.conf`

```ini
; 1) Sửa APP_ROOT và user bên dưới cho đúng server.
; 2) sudo tee /etc/supervisor/conf.d/craveva-queue-all.conf < ... (sao chép nội dung khối này)
; 3) sudo mkdir -p /var/log/supervisor && sudo chown www-data:www-data /var/log/supervisor
; 4) sudo supervisorctl reread && sudo supervisorctl update && sudo supervisorctl start craveva-queue-all:*
;
; Danh sách --queue= phải khớp app\Console\Kernel::DATABASE_WORKER_QUEUE_NAMES và
; ImportController::ALLOWED_IMPORT_QUEUE_NAMES (+ default). Thứ tự: import trước, default sau.
;
; Khi .env có IMPORT_BATCH_QUEUE_CONNECTION=redis: cần HAI worker — (1) database --queue=default
; (2) redis --queue=ClientImport,ProductImport,... (không gồm "default"). Xem app\Console\Kernel::schedule().
;
; Staging/production: đặt IMPORT_PROGRESS_RUN_QUEUE_WORKER=false (hoặc unset). Nếu vẫn true
; trong khi Supervisor chạy, mỗi lần poll HTTP cũng gọi queue:work — dư worker, dễ timeout FPM.

[program:craveva-queue-all]
process_name=%(program_name)s_%(process_num)02d
command=php /CHANGE_ME_APP_ROOT/artisan queue:work database --queue=ClientImport,ProductImport,EmployeeImport,ProjectImport,DealImport,LeadImport,ExpenseImport,AttendanceImport,JobApplicationImport,ClientProductPricingImport,PricingTierItemsImport,WarehouseImport,InventoryImport,SalesOrderImport,SalesHistoryImport,default --tries=3 --sleep=3 --max-time=3600
directory=/CHANGE_ME_APP_ROOT
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=CHANGE_ME_USER
numprocs=1
redirect_stderr=true
stdout_logfile=/var/log/supervisor/craveva-queue-all.log
stopwaitsecs=3600

; Nếu backlog queue `default` rất lớn: thêm program thứ hai chỉ --queue=default với numprocs=2.
;
; --- IMPORT_BATCH_QUEUE_CONNECTION=redis (tùy chọn): thêm program thứ hai, ví dụ ---
; [program:craveva-queue-redis-import]
; command=php /CHANGE_ME_APP_ROOT/artisan queue:work redis --queue=ClientImport,ProductImport,EmployeeImport,ProjectImport,DealImport,LeadImport,ExpenseImport,AttendanceImport,JobApplicationImport,ClientProductPricingImport,PricingTierItemsImport,WarehouseImport,InventoryImport,SalesOrderImport,SalesHistoryImport,EstimateImport --tries=3 --sleep=3 --max-time=3600
; ... (cùng directory, user, autostart như trên)
; Và sửa program craveva-queue-all chỉ còn: queue:work database --queue=default ...
```

<a id="deploy-systemd-unit"></a>

### 10.5 systemd — `craveva-queue-all.service` (tuỳ chọn)

**Không** bật đồng thời với Supervisor. Staging khuyến nghị Supervisor; nếu dùng unit này thì `sudo systemctl disable --now` bên kia trước khi bật.

```ini
# systemd unit — OPTIONAL. Staging khuyến nghị dùng Supervisor (mục 10.4).
# Không bật đồng thời Supervisor + unit này — hai worker sẽ giành job; nếu User≠www-data sẽ lỗi
# Permission denied trên storage/framework/cache (FPM + cache thuộc www-data).
#
# Cài đặt:
#   sudo tee /etc/systemd/system/craveva-queue-all.service < ... (sao chép nội dung khối này)
#   sudo systemctl daemon-reload && sudo systemctl enable --now craveva-queue-all
#
# Nếu đã dùng Supervisor: sudo systemctl disable --now craveva-queue-all
#
# Sau deploy: sudo systemctl restart craveva-queue-all  (hoặc php artisan queue:restart)

[Unit]
Description=Craveva Laravel queue worker (import queues + default)
After=network-online.target
Wants=network-online.target

[Service]
Type=simple
User=www-data
Group=www-data
WorkingDirectory=/var/www/craveva-staging/current/craveva
Environment=HOME=/var/www
# Khớp mục 10.4 và Kernel::DATABASE_WORKER_QUEUE_NAMES
ExecStart=/usr/bin/php artisan queue:work database --queue=ClientImport,ProductImport,EmployeeImport,ProjectImport,DealImport,LeadImport,ExpenseImport,AttendanceImport,JobApplicationImport,ClientProductPricingImport,PricingTierItemsImport,WarehouseImport,InventoryImport,SalesOrderImport,SalesHistoryImport,default --tries=3 --sleep=3 --max-time=3600
Restart=always
RestartSec=10
TimeoutStopSec=3600

[Install]
WantedBy=multi-user.target
```

---

_Cập nhật: 2026-04-04 — gộp nội dung từ `LIVE_HUB_L11_UPLOAD_AND_UPGRADE_CHECKLIST.md` và `LARAVEL_PHP_FPM_QUEUE_PERMISSIONS.md` (đã xóa để tránh trùng). 2026-04-05 — mục 9 cutover schema + bẫy #7. **2026-05-12** — gộp thư mục `deploy/` vào mục 10._
