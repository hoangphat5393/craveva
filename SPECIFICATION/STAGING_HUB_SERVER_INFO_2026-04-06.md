# Staging, Hub & AI — inventory tài nguyên & PHP (snapshot)

**Bảng IP VM / Cloud SQL (snapshot GCP):** `SPECIFICATION/GCP_AND_CLOUDSQL_SNAPSHOT.md`, `docs/GCP_INVENTORY.md` (đối chiếu khi cần).

**Thu thập:** 2026-04-06 (SSH read-only staging/hub). **Cập nhật 2026-05-15:** quét read-only **`gcloud`** + SSH (staging/hub alias; **AI** qua `gcloud compute ssh`). Giá trị **thay đổi theo thời gian** — dùng `free -h`, `uptime` khi điều tra sự cố.

**Ba VM app — SSH & thư mục code:**

| Máy         | Host SSH (`ssh …`)   | Đường dẫn code / app                       | Ghi chú                                                                  |
| ----------- | -------------------- | ------------------------------------------ | ------------------------------------------------------------------------ |
| **Staging** | `craveva-staging`    | `/var/www/craveva-staging/current/craveva` | `scripts/upload_staging.ps1`                                             |
| **Hub**     | `craveva-hub-server` | `/var/www/hub.craveva.com`                 | `scripts/upload_hub.ps1`                                                 |
| **AI**      | xem cột dưới         | `/var/www/ai-app`                          | **https://ai.craveva.com** — Docker Compose; không có script upload repo |

**SSH AI:** trên máy dev, `ssh craveva-ai` có thể **Permission denied (publickey)** nếu chưa gắn key GCP. Dùng read-only:

```bash
gcloud compute ssh craveva-ai --zone=asia-southeast1-a --command="hostname; free -h; uptime"
```

Alias tùy chọn (cùng IP): `craveva-ai.asia-southeast1-a.craveva-org-55934-project` → `136.110.35.154`.

Sau khi đổi RAM / FPM (staging/hub), chạy lại các lệnh ở **mục 7** trên **từng** máy và cập nhật bảng **mục 1** trong file này.

**Cập nhật 2026-04-07 (Hub):** file FPM **`/etc/php/8.3/fpm/conf.d/99-hub-match-aapanel82.ini`** — `memory_limit` **128M → 256M**; `post_max_size` / `upload_max_filesize` **50M** (giữ); `php8.3-fpm` reload OK — chi tiết **mục 3 Hub → Drop-in aaPanel**.

**Redis / phpredis (SSH, 2026-04-06):** **Staging** — trước đó chỉ có **`php8.3-redis`**, chưa có Redis server; đã **`apt install redis-server`** (Ubuntu **6.0.16**), `redis-cli ping` → PONG. **Hub** — Redis (aaPanel) đã chạy **127.0.0.1:6379**; đã cài **`php8.3-redis`** (sury **6.3.0**), bật **CLI + FPM**. Hub: `apt` báo lỗi cấu hình gói **MariaDB client** cũ (không chặn cài phpredis) — nên xử lý khi bảo trì.

**Cập nhật 2026-04-08 (áp dụng trên server):**

- **Staging FPM pool:** `pm.max_children = **4**`, `pm.max_spare_servers = **4**` (`www.conf` + backup `www.conf.bak.fpm_scale_*`). `php-fpm8.3 -t` + **reload** OK.
- **Hub FPM pool:** `pm.max_children = **8**`, `pm.max_spare_servers = **8**`.
- **Hub drop-in** `99-hub-match-aapanel82.ini`: `memory_limit` **256M → 1024M** (đồng bộ hiệu lực FPM với staging; có file `.bak.*` cạnh đó).
- **Dọn dung lượng (không đụng DB):** Staging — xóa **2 file zip cũ nhất** trong `storage/backup/` (giữ 3 bản gần nhất). Hub — xóa thư mục **`/var/www/hub.craveva.com.bak-20251210152231`** (~840M, bản sao code cũ). **Không** xóa `hub.craveva.com_release`, `.git-src`, `/var/www/html` (có thể đang dùng).
- Script tái áp dụng: `scripts/fpm_scale_pool_apply.sh` (chạy trên server: `sudo bash … staging|hub`; từ Windows: `scp` rồi `sed -i 's/\r$//'` nếu CRLF).

**RAM đã nâng:** **~8 GiB (Staging)** / **~15 GiB (Hub)** — đã tăng **`pm.max_children`** theo **mục 9**. Chi tiết điều chỉnh thêm: **mục 9**.

**Hub — drop-in:** trước **2026-04-08** file **`99-hub-match-aapanel82.ini`** ghi **`memory_limit = 256M`** và ghi đè `php.ini`; đã nâng **1024M**.

**Ràng buộc PHP-FPM (`pm = dynamic`):** khi hạ **`pm.max_children`** xuống **2**, bắt buộc **`pm.max_spare_servers` ≤ `pm.max_children`** (và các chỉ số spare/start phải nhất quán). Nếu chỉ sửa `max_children` mà để **`pm.max_spare_servers = 3`**, FPM **không khởi động** (exit **78**) → Nginx **502 Bad Gateway**.

- **2026-04-04:** Hub / staging **`pm.max_spare_servers = 2`** cùng **`pm.max_children = 2`** (sửa 502). **2026-04-08:** staging **4/4**, hub **8/8** — vẫn **`max_spare` ≤ `max_children`**.

**Supervisor (staging):** đã cài **`supervisor`**, program **`craveva-queue-all`** chạy `queue:work` nền (**2026-04-04**). `.env` staging: **`IMPORT_PROGRESS_RUN_QUEUE_WORKER=false`**. Chi tiết: `docs/SERVER_RUNBOOK.md` §4.2 và [phụ lục mẫu Supervisor](../docs/SERVER_RUNBOOK.md#deploy-supervisor-conf).

**Mục đích:** giải thích vì sao sau import / tăng `max_execution_time` / upload limit, máy có thể **load cao hoặc “đơ”**: RAM nhỏ (cũ), swap, và **oversubscription** `memory_limit` PHP-FPM.

---

## 1. Tóm tắt nhanh

**GCP (2026-05-15, `gcloud compute instances list`):** project `craveva-org-55934-project`, zone **`asia-southeast1-a`** cho cả ba VM.

| Máy                    | Loại VM (GCP)       | IP ngoài         | IP nội        | RAM (quét)   | Swap (quét)               | vCPU | Ổ `/` (quét)    | Ghi chú runtime                                                                                                                                                                             |
| ---------------------- | ------------------- | ---------------- | ------------- | ------------ | ------------------------- | ---- | --------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **craveva-staging**    | `n2-standard-2`     | `35.240.198.61`  | `10.148.0.16` | **~7.8 GiB** | 2 GiB (**0** dùng)        | 2    | 20G (**~83%**)  | Laravel + **PHP 8.3 FPM** + Nginx. **`1024M`**, `pm.max_children = 4` (**2026-04-08**). **Supervisor** `craveva-queue-all`. Load **~0.16** (2026-05-15).                                    |
| **craveva-hub-server** | `e2-standard-4`     | `34.126.124.196` | `10.1.0.5`    | **~15 GiB**  | 2 GiB (**~673 MiB** dùng) | 4    | 194G (**~29%**) | Laravel + **PHP 8.3 FPM** (aaPanel). Drop-in **1024M**, `pm.max_children = 8` (**2026-04-08**). Load **~0.34** (2026-05-15).                                                                |
| **craveva-ai**         | `e2-custom-8-16384` | `136.110.35.154` | `10.148.0.7`  | **~15 GiB**  | 2 GiB (**~13 MiB** dùng)  | 8    | 97G (**~38%**)  | **https://ai.craveva.com** — **Docker Compose** (`/var/www/ai-app`), **không** PHP-FPM/Nginx trên host. Load **~6.6–8.5** lúc quét (đang `docker-compose` / `npm ci`). Chi tiết **mục 3b**. |

**Staging — ổ đĩa:** quét **2026-05-15** ~**83%** `/` (20G) — cao hơn snapshot **2026-04-06** (~62%); cân nhắc dọn log/backup trước khi full.

### Redis — Local / Staging / Hub / AI (kiểm **2026-04-08**; AI host **2026-05-15**)

| Môi trường      | Redis server                                                          | PHP **phpredis**   | `redis-cli ping`                                                                                                           | `systemctl` boot                                               |
| --------------- | --------------------------------------------------------------------- | ------------------ | -------------------------------------------------------------------------------------------------------------------------- | -------------------------------------------------------------- |
| **Local (dev)** | Tùy máy — thường **chưa cài**; queue/cache có thể `database` / `file` | Tùy cài đặt        | N/A                                                                                                                        | N/A                                                            |
| **Staging**     | **`redis-server` (apt)** — **active**, **enabled**                    | **Có** (CLI + FPM) | **PONG**                                                                                                                   | `redis-server` **enabled**                                     |
| **Hub**         | **aaPanel** `redis.service` — **active**, **enabled**                 | **Có** (CLI + FPM) | **PONG** (thường cần **`sudo redis-cli ping`** — thư mục `/www/server/redis` hạn quyền; **Laravel không cần** `redis-cli`) | **enabled**                                                    |
| **AI**          | **Không** có `redis-server` trên host (quét **2026-05-15**)           | N/A (stack Node)   | N/A                                                                                                                        | Cache/queue trong **container** hoặc DB ngoài — xem **mục 3b** |

**Không cần cài thêm** Redis trên staging/hub tại thời điểm kiểm **2026-04-08** — dịch vụ đã chạy.

**Git chung 3 nguồn:** Trong repo có `config/database.php` và **`.env.example`**; **`.env`** mỗi máy **không** commit — không làm lệch code. Đồng bộ **tên biến** `REDIS_*`, `QUEUE_CONNECTION`, `IMPORT_BATCH_QUEUE_CONNECTION`. Prefix key Laravel gắn `APP_NAME` — **khác `APP_NAME`** hoặc set **`REDIS_PREFIX`** nếu hai app dùng chung một Redis.

**Redis có tự tắt khi không dùng lâu?** — **Bản tự host (staging/hub) không tự dừng vì idle.** Có `systemctl enable` → khởi động lại máy vẫn lên. Nếu “mất” Redis: kiểm tra `systemctl status`, log `journalctl`, OOM, VM preemptible — không phải do “lâu không gọi”.

---

## 2. Staging (`craveva-staging`) — chi tiết

### Hệ điều hành & kernel

- **Hostname:** `craveva-staging`
- **Kernel:** `Linux 6.8.0-1053-gcp #56~22.04.1-Ubuntu SMP` (x86_64, GCP)

### Bộ nhớ & swap (lệnh `free -h`)

|          | Total  | Used   | Free   | Shared | Buff/cache | Available   |
| -------- | ------ | ------ | ------ | ------ | ---------- | ----------- |
| **Mem**  | 7.8 Gi | 545 Mi | 5.6 Gi | 89 Mi  | 1.6 Gi     | **~6.9 Gi** |
| **Swap** | 2.0 Gi | 114 Mi | 1.9 Gi |        |            |             |

- **Swap device:** `/swapfile` 2G (prio -2)

### CPU

- **vCPU:** 2
- **Model:** Intel(R) Xeon(R) CPU @ 2.20GHz

### Disk

- **`/`:** 20G total, ~12G used, ~7.5G avail (~62%)

### Load (tại thời điểm quét, uptime ~1.5 days)

- `load average: 0.18, 0.18, 0.18`

### PHP 8.3 FPM (`/etc/php/8.3/fpm/`)

| Chỉ số                   | Giá trị                                                     |
| ------------------------ | ----------------------------------------------------------- |
| **memory_limit**         | **1024M**                                                   |
| **max_execution_time**   | **300**                                                     |
| **max_input_time**       | **300**                                                     |
| **Pool `www.conf`**      | `pm = dynamic`                                              |
| **pm.max_children**      | **4** (**2026-04-08**; trước đó 2)                          |
| **pm.start_servers**     | 2                                                           |
| **pm.min_spare_servers** | 1                                                           |
| **pm.max_spare_servers** | **4** (≤ `max_children`; **3** khi children=2 từng gây 502) |

### PHP 8.3 CLI

- **memory_limit:** `-1` (không giới hạn trong ini)

### Redis server & PHP phpredis (quét **2026-04-06**, tái kiểm **2026-04-08**)

| Thành phần             | Trạng thái                                                                                                                 |
| ---------------------- | -------------------------------------------------------------------------------------------------------------------------- |
| **Redis server**       | Gói Ubuntu **`redis-server` 5:6.0.16** (`redis-server.service`), **`active`**, **`enabled`**, `redis-cli ping` → **PONG**. |
| **redis-cli**          | Có (gói `redis-tools`).                                                                                                    |
| **Extension phpredis** | Gói **`php8.3-redis`** (deb.sury.org **6.3.0**), bật cho **CLI** và **FPM** (`php -m` / `php-fpm8.3 -m` có `redis`).       |
| **timeout (server)**   | `CONFIG GET timeout` → **0** (không hết hạn client idle do Redis).                                                         |

---

## 3. Hub (`craveva-hub-server`) — chi tiết

### Hệ điều hành & kernel

- **Hostname:** `craveva-hub-server`
- **Kernel:** `Linux 6.8.0-1053-gcp` (Ubuntu 22.04 family, x86_64, GCP)

### Bộ nhớ & swap

|          | Total  | Used        | Free        | Shared | Buff/cache | Available  |
| -------- | ------ | ----------- | ----------- | ------ | ---------- | ---------- |
| **Mem**  | 15 Gi  | 531 Mi      | 10 Gi       | 222 Mi | 4.3 Gi     | **~14 Gi** |
| **Swap** | 2.0 Gi | **~811 Mi** | **~1.2 Gi** |        |            |            |

- **Swap device:** `/swapfile` 2G — **đang dùng ~40%** (giảm so với 75% trước khi nâng RAM).

### CPU

- **vCPU:** 4
- **Model:** Intel(R) Xeon(R) CPU @ 2.20GHz

### Disk

- **`/`:** 194G total, ~55G used, ~140G avail (~29%)

### Load (tại thời điểm quét, uptime ~1.5 days)

- `load average: 0.66, 0.63, 0.53`

### PHP 8.3 FPM

| Chỉ số                 | Giá trị (sau chỉnh **2026-04-04**)                                                                                                             |
| ---------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------- |
| **memory_limit**       | **1024M** trong `/etc/php/8.3/fpm/php.ini` (snapshot **2026-04-06**); **ghi đè** bởi drop-in aaPanel — xem bảng ngay dưới.                     |
| **max_execution_time** | **300**                                                                                                                                        |
| **max_input_time**     | **300**                                                                                                                                        |
| **Pool**               | `pm = dynamic`: **`pm.max_children = 8`**, **`pm.max_spare_servers = 8`** (**2026-04-08**), `pm.start_servers = 2`, `pm.min_spare_servers = 1` |

#### Drop-in aaPanel (Hub) — cập nhật **2026-04-07**

File: **`/etc/php/8.3/fpm/conf.d/99-hub-match-aapanel82.ini`**

| Chỉ số                  | Giá trị hiện tại | Ghi chú                                                                             |
| ----------------------- | ---------------- | ----------------------------------------------------------------------------------- |
| **memory_limit**        | **1024M**        | **2026-04-08:** **256M → 1024M** (đồng bộ staging / import). Reload **php8.3-fpm**. |
| **post_max_size**       | **50M**          | Giữ nguyên.                                                                         |
| **upload_max_filesize** | **50M**          | Giữ nguyên.                                                                         |

**Giá trị thực tế** của `memory_limit` cho request FPM là giá trị **sau** khi PHP merge toàn bộ `.ini` (thường file `conf.d/…` tải sau `php.ini`). Để xác nhận trên máy: `php-fpm8.3 -i 2>/dev/null | grep memory_limit` hoặc trang `phpinfo()` qua FPM.

#### Khác nhau giữa mục « PHP 8.3 FPM » và file `99-hub-match-aapanel82.ini`?

**Không phải hai bản PHP hay hai pool độc lập** — cùng một binary **`php-fpm8.3`**, cùng các worker FPM, chỉ **khác nguồn cấu hình**:

| Nguồn                                                                      | Vai trò                                                                                                                                                                      |
| -------------------------------------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **`/etc/php/8.3/fpm/php.ini`**                                             | File INI gốc của SAPI **fpm** (snapshot doc ghi **1024M** cho `memory_limit` tại thời điểm quét).                                                                            |
| **`/etc/php/8.3/fpm/conf.d/*.ini`** (gồm **`99-hub-match-aapanel82.ini`**) | Các **snippet** được nối thêm sau `php.ini` (thứ tự tên file; `99-` thường load muộn). Dùng để **ghi đè** (Hub: `memory_limit` **1024M** từ **2026-04-08**, upload **50M**). |
| **`/etc/php/8.3/fpm/pool.d/*.conf`**                                       | Cấu hình **pool** FPM: `pm.max_children`, **`listen`** (socket hoặc TCP), user, v.v. — **không** nằm trong file `99-hub-…ini` trên.                                          |

Mục **### PHP 8.3 FPM** trong tài liệu này mô tả **tổng thể** (pool + giá trị INI lúc quét); mục **Drop-in aaPanel** ghi **riêng** file chỉnh tay / đồng bộ aaPanel để biết **giá trị đang thắng** cho `memory_limit` / upload.

#### Hub đang dùng socket nào?

**Socket (hoặc cổng TCP)** mà Nginx gửi request PHP tới được khai báo bằng directive **`listen`** trong **`/etc/php/8.3/fpm/pool.d/*.conf`** (thường `www.conf` hoặc pool do aaPanel tạo), ví dụ:

- `listen = /run/php/php8.3-fpm.sock`, hoặc
- `listen = /tmp/php-cgi-83.sock`, hoặc
- `listen = 127.0.0.1:9000`

**Không** suy ra từ `99-hub-match-aapanel82.ini` (file đó chỉ là PHP INI).

Trên server Hub, xác nhận bằng:

```bash
grep -hE '^listen\s*=' /etc/php/8.3/fpm/pool.d/*.conf
```

Và khớp với Nginx: `grep -R fastcgi_pass /www/server/panel/vhost/nginx/*.conf` (hoặc đường site thực tế trên máy).

### PHP 8.3 CLI

- **memory_limit:** `-1`

### Redis server & PHP phpredis (quét **2026-04-06**, tái kiểm **2026-04-08**)

| Thành phần             | Trạng thái                                                                                                                                                                                                    |
| ---------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Redis server**       | Dịch vụ **aaPanel** (`redis.service`), **active**, **`enabled`**, **127.0.0.1:6379**. **`sudo redis-cli ping`** → **PONG**.                                                                                   |
| **redis-cli**          | Symlink **`/usr/bin/redis-cli`** → `/www/server/redis/src/redis-cli` — user thường **Permission denied** nếu không `sudo` (quyền thư mục aaPanel). Ứng dụng PHP **không** phụ thuộc `redis-cli`.              |
| **Extension phpredis** | Đã cài **`php8.3-redis`** (ondrej/sury **6.3.0**, kèm **`php8.3-igbinary`**), symlink **`/etc/php/8.3/{cli,fpm}/conf.d/25-redis.ini`** → `mods-available/redis.ini`; **CLI + FPM** đều có module **`redis`**. |

**Ghi chú vận hành Hub:** Sau `apt-get install php8.3-redis`, **dpkg** báo lỗi cấu hình chuỗi **`mariadb-common` / `mariadb-client-*`** (có thể do VM dùng stack DB khác / aaPanel; lỗi **không** ngăn cài đặt phpredis). Khi bảo trì, có thể chạy `sudo dpkg --configure -a` hoặc sửa theo hướng dẫn aaPanel — **xác nhận trước** trên máy production.

---

## 3b. AI (`craveva-ai`) — chi tiết

> **Quét read-only:** 2026-05-15 (`gcloud compute instances describe` + `gcloud compute ssh` — **không** sửa cấu hình, **không** đọc nội dung `.env`).

### Vai trò & URL

- **Production AI / webhook:** [https://ai.craveva.com](https://ai.craveva.com) (LINE/WhatsApp webhook, tích hợp đọc DB hub theo kiến trúc dự án).
- **Khác staging/hub:** không phải Laravel PHP-FPM trên host; app **Node** trong **Docker Compose**.

### Hệ điều hành & kernel

- **Hostname:** `craveva-ai`
- **OS:** Ubuntu **24.04.4 LTS**
- **Kernel:** `Linux 6.17.0-1012-gcp` (x86_64, GCP)
- **Uptime (quét):** ~**10 ngày**; **7** phiên user đăng nhập

### Bộ nhớ & swap (`free -h`, 2026-05-15)

|          | Total   | Used     | Available    |
| -------- | ------- | -------- | ------------ |
| **Mem**  | ~15 GiB | ~1.3 GiB | **~14 GiB**  |
| **Swap** | 2.0 GiB | ~13 MiB  | ~2.0 GiB còn |

- **Swap device:** `/swapfile` 2G (prio -2)

### CPU & load

- **vCPU (GCP):** **8** (`e2-custom-8-16384`)
- **Load (lúc quét):** `6.65, 6.11, 5.50` → sau đó **~8.45** khi có **`docker-compose build`** / **`npm ci`** — không phản ánh steady-state; kiểm tra lại khi không deploy.

### Disk

- **`/`:** **97G** total, **~37G** used, **~61G** avail (**~38%**)
- **`/var/www/ai-app`:** ~**1.5G** (thư mục app)

### Mạng (GCP)

| Trường       | Giá trị             |
| ------------ | ------------------- |
| **Zone**     | `asia-southeast1-a` |
| **External** | `136.110.35.154`    |
| **Internal** | `10.148.0.7`        |
| **Status**   | `RUNNING`           |

Egress VM này thường được **allow** trên authorized networks Cloud SQL hub (`136.110.35.154/32` — xem `SPECIFICATION/GCP_AND_CLOUDSQL_SNAPSHOT.md`).

### Thư mục trên VM (`/var/www`)

| Đường dẫn             | Ghi chú                                                                                 |
| --------------------- | --------------------------------------------------------------------------------------- |
| **`/var/www/ai-app`** | App chính — `docker-compose.yml`, `backend/`, `package.json` (`ai-enterprise-business`) |
| `ai-app-backup-`      | Backup code (tên thư mục cắt)                                                           |
| `ai-app-backups`      | Backup (user `issac`)                                                                   |
| `certbot`             | Certbot / webroot                                                                       |
| `html`                | Mặc định                                                                                |

- **`.env`:** có tại `/var/www/ai-app/.env` (nội dung **không** ghi trong tài liệu này).
- **Git:** không có `.git` tại root app (quét **2026-05-15**).

### Runtime (host)

| Thành phần       | Trạng thái (quét)                             |
| ---------------- | --------------------------------------------- |
| **nginx** (host) | **inactive**                                  |
| **apache2**      | **inactive**                                  |
| **docker**       | **active** (`dockerd`)                        |
| **supervisor**   | **inactive**                                  |
| **pm2**          | không cài / không dùng                        |
| **PHP-FPM**      | **không** (không áp dụng mục FPM staging/hub) |
| **Node (host)**  | **v20.20.0**                                  |
| **Python**       | **3.12.3**                                    |

### Docker Compose (`/var/www/ai-app/docker-compose.yml`)

| Service (compose) | Container (tên trong file) | Image / stack  | Port host (khi stack up) |
| ----------------- | -------------------------- | -------------- | ------------------------ |
| `frontend`        | `craveva-frontend`         | build Node     | **3000:3000**            |
| `backend`         | `craveva-backend`          | build Node     | **5000:5000**            |
| `nginx`           | `craveva-nginx`            | `nginx:alpine` | **80:80**                |
| `nginx-cache`     | (volume/cache)             | —              | —                        |

- **Network:** `craveva-network`
- **Trạng thái lúc quét:** stack đang **build/up** (process `docker-compose`, `npm ci`); `docker ps` có thể chỉ thấy container tạm — xác nhận khi ổn định: `cd /var/www/ai-app && sudo docker compose ps`

### Cloud SQL liên quan AI (snapshot GCP, không đổi VM)

| Instance                  | Engine        | Public IP (snapshot)                       | Vai trò            |
| ------------------------- | ------------- | ------------------------------------------ | ------------------ |
| **`craveva-ai-db`**       | MySQL 8.0     | `34.158.38.112`                            | DB module AI       |
| **`craveva-ai-pgvector`** | PostgreSQL 15 | `136.110.25.28` (+ IP phụ trong inventory) | Vector / embedding |

Chi tiết tier, private IP, firewall: `docs/GCP_INVENTORY.md`.

### Lệnh tái kiểm tra (read-only)

```bash
gcloud compute ssh craveva-ai --zone=asia-southeast1-a --command="hostname; free -h; df -h /; uptime"
gcloud compute ssh craveva-ai --zone=asia-southeast1-a --command="cd /var/www/ai-app && sudo docker compose ps"
```

---

## 4. Liên quan tới import / `max_execution_time` / upload — vì sao “server die / load liên tục”?

1. **`max_execution_time = 300`** không làm **mỗi request ăn thêm RAM cố định**, nhưng cho phép request **sống lâu hơn** → **worker FPM bị giữ lâu hơn** → dễ có **nhiều request nặng đồng thời** → tổng RAM PHP + MySQL client + Opcache tăng.

2. **Staging (trước khi giảm children):** `memory_limit = 1024M` × **5** children → trần lý thuyết **~5 GiB** PHP, vượt RAM **~4 GiB** → dễ **swap / load**. **Hiện tại mục tiêu:** **2** children → trần **~2 GiB** pool FPM.

3. **Hub:** swap **đã dùng ~1.5G** cho thấy hệ thống **thường xuyên hoặc vừa ép RAM**; thêm peak (cron, queue, web) làm swap tiếp → **load không giảm**.

4. **Upload 64M** tăng **kích thước body** request + buffer; với RAM nhỏ vẫn nên tránh **nhiều upload lớn đồng thời**.

---

## 5. Hướng xử lý (vận hành / kiến trúc)

| Hướng                               | Ý nghĩa                                                                                                                                                                                        |
| ----------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **FPM `1024M` + `pm.max_children`** | Trên VM **4G**: **2** children là hợp lý. Trên **8G / 16G**: có thể **tăng children** (ví dụ **4** staging, **6–8** hub) — xem **mục 9**. Luôn **`pm.max_spare_servers` ≤ `pm.max_children`**. |
| **Theo dõi**                        | `free -h`, `swapon --show`, `uptime` khi import — xác nhận swap có nhảy không.                                                                                                                 |
| **Queue / import**                  | Giữ chunk hợp lý; tránh nhiều import song song trên cùng VM nhỏ.                                                                                                                               |
| **Hub: swap cao**                   | Tìm process ăn RAM (`ps aux --sort=-%mem \| head`); cân nhắc **nâng RAM VM** hoặc giảm service trùng.                                                                                          |
| **Quyền / cache**                   | `docs/SERVER_RUNBOOK.md`.                                                                                                                                                                   |
| **Supervisor**                      | Staging: worker queue nền; tránh **`IMPORT_PROGRESS_RUN_QUEUE_WORKER=true`** đồng thời. `docs/SERVER_RUNBOOK.md`.                                                                           |

---

## 6. Khuyến nghị cấu hình VM — mức nào “đủ chạy” dự án?

Ước lượng cho stack hiện tại: **Laravel + Nginx + PHP 8.3 FPM** (`memory_limit` 1024M, `pm.max_children = 2`) + **Supervisor** (`queue:work`) + queue **`database`**. **MySQL/Cloud SQL trên máy khác** (như staging) thì **không** tính RAM DB vào VM app.

| Bối cảnh                                                                  | RAM                                     | vCPU                    | Ổ `/` (gợi ý)                       | Ghi chú                                                                      |
| ------------------------------------------------------------------------- | --------------------------------------- | ----------------------- | ----------------------------------- | ---------------------------------------------------------------------------- |
| **Staging / demo** (ít người, DB ngoài VM, FPM 2 children, có Supervisor) | **~4 GiB**                              | **2**                   | **≥ 30–40 GiB** an toàn hơn **20G** | Mức **sàn hợp lý**; khớp **e2-medium** (~3.75–4G) đang dùng.                 |
| **Production nhỏ** (web + queue cùng VM, DB vẫn riêng)                    | **4–8 GiB**                             | **2–4**                 | **≥ 40–80 GiB**                     | **4G** chạy được với 2×1024M FPM + 1 worker queue; **8G** dư địa peak / log. |
| **Cùng VM chạy thêm MySQL**                                               | **+2–4 GiB** tối thiểu so với hàng trên | **+2 vCPU** nếu DB nặng | Disk DB / log lớn hơn               | Staging/hub app hiện **không** gộp DB trên VM.                               |

**RAM — ý chính:** cần đủ cho OS + Nginx + trần lý thuyết **FPM** (~`max_children` × `memory_limit`) + **một** process `queue:work` + opcache/buffer. **CPU:** import + queue chủ yếu PHP + I/O DB; tải nhiều user / import song song → **4 vCPU** thoải mái hơn **2**.

Đây là **khuyến nghị vận hành**, không phải SLA; xác nhận thực tế bằng `free -h`, load khi import, slow query.

---

## 7. Lệnh tái kiểm tra trên server

```bash
free -h
swapon --show
uptime
grep -E '^memory_limit|^pm.max_children' /etc/php/8.3/fpm/php.ini /etc/php/8.3/fpm/pool.d/www.conf 2>/dev/null
```

### 7a. Redis & PHP phpredis

```bash
# Redis server (Ubuntu package: systemctl status redis-server)
redis-cli ping

# Extension (CLI + FPM pool)
php -m | grep -i '^redis$' || true
php-fpm8.3 -m 2>/dev/null | grep -i '^redis$' || true
```

### 7b. Áp dụng / đồng bộ pool — `1024M` + `pm.max_children = 2` (+ spare khớp)

Chạy **trên server Linux** (bash). Nếu gửi lệnh qua **PowerShell**, tránh nhúng `$(date ...)` trong chuỗi SSH (PowerShell có thể parse nhầm); dùng tên backup cố định hoặc mở session `bash`/`ssh` tương tác.

**`memory_limit` FPM** có thể đặt trong `/etc/php/8.3/fpm/php.ini`. Pool **`www.conf`**:

```bash
sudo cp -a /etc/php/8.3/fpm/pool.d/www.conf /etc/php/8.3/fpm/pool.d/www.conf.bak.before_children2
sudo sed -i 's/^pm.max_children = .*/pm.max_children = 2/' /etc/php/8.3/fpm/pool.d/www.conf
sudo sed -i 's/^pm.max_spare_servers = .*/pm.max_spare_servers = 2/' /etc/php/8.3/fpm/pool.d/www.conf
grep -E '^pm\.(max_children|start_servers|min_spare|max_spare)' /etc/php/8.3/fpm/pool.d/www.conf
grep '^memory_limit' /etc/php/8.3/fpm/php.ini
sudo php-fpm8.3 -t && sudo systemctl restart php8.3-fpm
```

- **`php-fpm8.3 -t`** bắt buộc trước khi coi cấu hình ổn; nếu fail, đọc stderr (thường là spare &gt; children).
- Dùng **`restart`** sau khi FPM đang **failed**; **`reload`** khi service đang chạy và chỉ đổi nhẹ.

(Nếu dùng pool khác `www.conf`, sửa đường dẫn tương ứng.)

---

## 8. Liên quan trong repo

- GCP inventory (gồm AI VM + Cloud SQL): `docs/GCP_INVENTORY.md`
- Scale FPM pool (staging 4 / hub 8): `scripts/fpm_scale_pool_apply.sh`
- PHP ini tuning script: `scripts/tune_php83_import_limits.sh`
- Staging vận hành (Supervisor, deploy): `docs/SERVER_RUNBOOK.md`; rehearsal/zip: `docs/STAGING_OPERATIONS.md`
- Supervisor mẫu cấu hình queue: `docs/SERVER_RUNBOOK.md` ([mục 10.4](../docs/SERVER_RUNBOOK.md#deploy-supervisor-conf))
- Import & poll: `FUNC_IMPROVE/IMPORT_POLL_TRACKERS.md`

---

## 9. Sau khi nâng RAM — có tăng `1024M` / `pm.max_children` cho import (17k dòng, nhiều sheet)?

### 9.1. Tóm tắt quyết định

| Thành phần                                      | Có cần tăng?                                                                            | Ghi chú                                                                                                                                                 |
| ----------------------------------------------- | --------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **`memory_limit` FPM (1024M)**                  | **Thường giữ 1024M**                                                                    | Chỉ cân nhắc **2048M** nếu log / `Allowed memory size exhausted` khi import **qua đúng request FPM** (web).                                             |
| **`pm.max_children`**                           | **Nên cân nhắc tăng** trên 8G/16G                                                       | RAM lớn hơn cho phép **nhiều worker web đồng thời** mà không bó 2 children như VM 4G. Vẫn tránh “bắn” quá cao (oversubscribe).                          |
| **Import nặng (17k client, Excel nhiều sheet)** | **Ưu tiên queue + CLI**                                                                 | Worker `queue:work` dùng **CLI** (`memory_limit` thường **-1** trên staging doc) — phù hợp xử lý từng chunk; FPM chỉ nhận upload / trigger job.         |
| **Upload**                                      | Kiểm tra **post_max_size** / **upload_max_filesize** / **Nginx `client_max_body_size`** | File lớn hoặc nhiều sheet → cần đủ cả PHP và Nginx. Script: `scripts/tune_php83_import_limits.sh` (FPM 64M mặc định script — chỉnh tay nếu file > 64M). |
| **Hub drop-in INI**                             | **Bắt buộc kiểm tra**                                                                   | Nếu `conf.d/99-hub-match-*.ini` vẫn **256M**, mọi request FPM chỉ có **256M** → import web dễ fail trước khi tới 1024M của `php.ini`.                   |

### 9.2. Công thức thô (lập ngân sách RAM cho pool FPM)

- Giữ chừng **~1.5–2.5 GiB** cho OS + Nginx + Redis + **ít nhất một** `queue:work` (Supervisor) + buffer.
- Mỗi child FPM **tệ nhất** có thể gần **`memory_limit`** khi peak (import, Opcache, framework).
- **Trần lý thuyết pool:** `pm.max_children × memory_limit` — không nên gần bằng toàn bộ RAM còn lại; để chừng **30–40%** dự phòng cho spike MySQL client, cache, cron.

**Ví dụ (làm tròn thận trọng):**

- **Staging ~8 GiB:** còn ~5–5.5 GiB cho “app”; với **1024M/child** → **4 children** (~4 GiB trần lý thuyết) thường **an toàn** nếu queue không ăn hết RAM cùng lúc.
- **Hub ~16 GiB:** **6–8 children** @ 1024M có thể chấp nhận nếu không chạy thêm DB nặng trên cùng VM và đã theo dõi `free -h` khi import.

Luôn: **`pm.max_spare_servers` ≤ `pm.max_children`**, **`pm.start_servers`** không vượt **`max_children`** (thường **2** hoặc bằng **min(max_children, 4)**).

### 9.3. Import 17k dòng & Excel nhiều sheet — không chỉ FPM

- **PhpSpreadsheet / đọc toàn bộ sheet:** RAM tăng theo số ô tải; **nhiều sheet** có thể nặng hơn một sheet 17k dòng — ưu tiên **đọc theo chunk**, **queue**, giới hạn sheet/cột nếu nghiệp vụ cho phép.
- **Thời gian:** `max_execution_time = 300` — nếu vẫn **504**, tăng **`fastcgi_read_timeout`** (Nginx) tương ứng; hoặc chuyển hẳn sang **job nền** (không giữ FPM 5 phút).
- **MySQL (Cloud SQL):** với batch insert lớn, xem **`max_allowed_packet`** / timeout phía DB (ngoài phạm vi FPM).

### 9.4. Việc nên làm ngay trên 2 server (SSH)

1. `free -h` / `uptime` — ghi lại vào **mục 1–3** của tài liệu này.
2. Xác nhận **giá trị thực tế** FPM: `php-fpm8.3 -i 2>/dev/null | grep memory_limit` (hoặc `phpinfo()` qua site).
3. Hub: mở **`/etc/php/8.3/fpm/conf.d/99-hub-match-aapanel82.ini`** — đảm bảo **`memory_limit` không thấp hơn** mức bạn mong muốn cho import (khuyến nghị **1024M** đồng bộ staging nếu import qua web).
4. Nếu tăng **`pm.max_children`**: sửa **`www.conf`** (hoặc pool aaPanel), chạy **`sudo php-fpm8.3 -t`** rồi **`systemctl reload php8.3-fpm`**.
5. Sau thay đổi: thử import mẫu (17k dòng) **một phiên** + theo dõi `htop` / `free -h` — tránh nhiều import song song cho đến khi ổn định.
