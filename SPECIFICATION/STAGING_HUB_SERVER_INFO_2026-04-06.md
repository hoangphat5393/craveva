# Staging & Hub — inventory tài nguyên & PHP (snapshot)

**Thu thập:** 2026-04-06 (SSH read-only). Giá trị **thay đổi theo thời gian** — cần `free -h`, `uptime` khi điều tra sự cố.

**Redis / phpredis (SSH, 2026-04-06):** **Staging** — trước đó chỉ có **`php8.3-redis`**, chưa có Redis server; đã **`apt install redis-server`** (Ubuntu **6.0.16**), `redis-cli ping` → PONG. **Hub** — Redis (aaPanel) đã chạy **127.0.0.1:6379**; đã cài **`php8.3-redis`** (sury **6.3.0**), bật **CLI + FPM**. Hub: `apt` báo lỗi cấu hình gói **MariaDB client** cũ (không chặn cài phpredis) — nên xử lý khi bảo trì.

**Cập nhật FPM (mục tiêu vận hành):** **`memory_limit = 1024M`** + **`pm.max_children = 2`** trên **cả staging và hub** — trần lý thuyết pool PHP web **~2 GiB** (2×1024M), phù hợp VM **~4 GiB RAM (cũ)**; hiện tại RAM đã nâng lên **16GB (Hub)** và **8GB (Staging)** nhưng vẫn giữ `max_children = 2` để an toàn cho import lớn (submit HTTP).

**Ràng buộc PHP-FPM (`pm = dynamic`):** khi hạ **`pm.max_children`** xuống **2**, bắt buộc **`pm.max_spare_servers` ≤ `pm.max_children`** (và các chỉ số spare/start phải nhất quán). Nếu chỉ sửa `max_children` mà để **`pm.max_spare_servers = 3`**, FPM **không khởi động** (exit **78**) → Nginx **502 Bad Gateway**.

- **Hub / staging:** đã đồng bộ **`pm.max_spare_servers = 2`** cùng **`pm.max_children = 2`** (**2026-04-04**).

**Supervisor (staging):** đã cài **`supervisor`**, program **`craveva-queue-all`** chạy `queue:work` nền (**2026-04-04**). `.env` staging: **`IMPORT_PROGRESS_RUN_QUEUE_WORKER=false`**. Chi tiết: `docs/SERVER_RUNBOOK_VI.md`, `deploy/supervisor/craveva-queue-all.conf.example`.

**Mục đích:** giải thích vì sao sau import / tăng `max_execution_time` / upload limit, máy có thể **load cao hoặc “đơ”**: RAM nhỏ (cũ), swap, và **oversubscription** `memory_limit` PHP-FPM.

---

## 1. Tóm tắt nhanh

| Máy                    | RAM          | Swap                                                 | CPU (vCPU)              | Ổ `/`            | Ghi chú FPM (mục tiêu)                                                                                                                                                                                     |
| ---------------------- | ------------ | ---------------------------------------------------- | ----------------------- | ---------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **craveva-staging**    | **~7.8 GiB** | 2 GiB (file `/swapfile`, lúc quét: **~114MiB dùng**) | 2 × Intel Xeon @ 2.2GHz | 20G (~62% used)  | **`1024M` + `pm.max_children = 2`** + **`pm.max_spare_servers = 2`**. **Supervisor** `craveva-queue-all` (**2026-04-04**). Trước đó 502 do spare **3** &gt; children **2** — xem đoạn ràng buộc phía trên. |
| **craveva-hub-server** | **~15 GiB**  | 2 GiB (lúc quét: **~811MiB đã dùng**)                | 4 × Intel Xeon @ 2.2GHz | 194G (~29% used) | Cùng bộ **`1024M` + children 2 + max_spare 2** (**2026-04-04**). Hub cũng từng lệch spare **3** → FPM fail nếu không sửa. Swap cao — xem mục **5**.                                                        |

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

| Chỉ số                   | Giá trị                                                            |
| ------------------------ | ------------------------------------------------------------------ |
| **memory_limit**         | **1024M**                                                          |
| **max_execution_time**   | **300**                                                            |
| **max_input_time**       | **300**                                                            |
| **Pool `www.conf`**      | `pm = dynamic`                                                     |
| **pm.max_children**      | **2**                                                              |
| **pm.start_servers**     | 2                                                                  |
| **pm.min_spare_servers** | 1                                                                  |
| **pm.max_spare_servers** | **2** (bắt buộc ≤ `max_children`; **3** làm FPM không start → 502) |

### PHP 8.3 CLI

- **memory_limit:** `-1` (không giới hạn trong ini)

### Redis server & PHP phpredis (quét **2026-04-06**)

| Thành phần             | Trạng thái                                                                                                           |
| ---------------------- | -------------------------------------------------------------------------------------------------------------------- |
| **Redis server**       | Gói Ubuntu **`redis-server` 5:6.0.16** (`redis-server.service`), **`active`**, `redis-cli ping` → **PONG**.          |
| **redis-cli**          | Có (gói `redis-tools`).                                                                                              |
| **Extension phpredis** | Gói **`php8.3-redis`** (deb.sury.org **6.3.0**), bật cho **CLI** và **FPM** (`php -m` / `php-fpm8.3 -m` có `redis`). |

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

| Chỉ số                 | Giá trị (sau chỉnh **2026-04-04**)                                                                                            |
| ---------------------- | ----------------------------------------------------------------------------------------------------------------------------- |
| **memory_limit**       | **1024M**                                                                                                                     |
| **max_execution_time** | **300**                                                                                                                       |
| **max_input_time**     | **300**                                                                                                                       |
| **Pool**               | `pm = dynamic`: **`pm.max_children = 2`**, **`pm.max_spare_servers = 2`**, `pm.start_servers = 2`, `pm.min_spare_servers = 1` |

### PHP 8.3 CLI

- **memory_limit:** `-1`

### Redis server & PHP phpredis (quét **2026-04-06**)

| Thành phần             | Trạng thái                                                                                                                                                                                                       |
| ---------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Redis server**       | Dịch vụ **aaPanel** (`redis.service`, init script), binary **`/www/server/redis/src/redis-server`**, cấu hình **`/www/server/redis/redis.conf`**, lắng nghe **127.0.0.1:6379**, trạng thái **active (running)**. |
| **redis-cli**          | **`/usr/bin/redis-cli`** → symlink tới `/www/server/redis/src/redis-cli`.                                                                                                                                        |
| **Extension phpredis** | Đã cài **`php8.3-redis`** (ondrej/sury **6.3.0**, kèm **`php8.3-igbinary`**), symlink **`/etc/php/8.3/{cli,fpm}/conf.d/25-redis.ini`** → `mods-available/redis.ini`; **CLI + FPM** đều có module **`redis`**.    |

**Ghi chú vận hành Hub:** Sau `apt-get install php8.3-redis`, **dpkg** báo lỗi cấu hình chuỗi **`mariadb-common` / `mariadb-client-*`** (có thể do VM dùng stack DB khác / aaPanel; lỗi **không** ngăn cài đặt phpredis). Khi bảo trì, có thể chạy `sudo dpkg --configure -a` hoặc sửa theo hướng dẫn aaPanel — **xác nhận trước** trên máy production.

---

## 4. Liên quan tới import / `max_execution_time` / upload — vì sao “server die / load liên tục”?

1. **`max_execution_time = 300`** không làm **mỗi request ăn thêm RAM cố định**, nhưng cho phép request **sống lâu hơn** → **worker FPM bị giữ lâu hơn** → dễ có **nhiều request nặng đồng thời** → tổng RAM PHP + MySQL client + Opcache tăng.

2. **Staging (trước khi giảm children):** `memory_limit = 1024M` × **5** children → trần lý thuyết **~5 GiB** PHP, vượt RAM **~4 GiB** → dễ **swap / load**. **Hiện tại mục tiêu:** **2** children → trần **~2 GiB** pool FPM.

3. **Hub:** swap **đã dùng ~1.5G** cho thấy hệ thống **thường xuyên hoặc vừa ép RAM**; thêm peak (cron, queue, web) làm swap tiếp → **load không giảm**.

4. **Upload 64M** tăng **kích thước body** request + buffer; với RAM nhỏ vẫn nên tránh **nhiều upload lớn đồng thời**.

---

## 5. Hướng xử lý (vận hành / kiến trúc)

| Hướng                                   | Ý nghĩa                                                                                                                                                               |
| --------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **FPM `1024M` + `pm.max_children = 2`** | **Nên giữ** trên VM ~4G để tránh oversubscribe RAM; luôn chỉnh **`pm.max_spare_servers` ≤ `pm.max_children`** (mục **7b**). Đã áp **hub + staging** (**2026-04-04**). |
| **Theo dõi**                            | `free -h`, `swapon --show`, `uptime` khi import — xác nhận swap có nhảy không.                                                                                        |
| **Queue / import**                      | Giữ chunk hợp lý; tránh nhiều import song song trên cùng VM nhỏ.                                                                                                      |
| **Hub: swap cao**                       | Tìm process ăn RAM (`ps aux --sort=-%mem \| head`); cân nhắc **nâng RAM VM** hoặc giảm service trùng.                                                                 |
| **Quyền / cache**                       | `docs/SERVER_RUNBOOK_VI.md`.                                                                                                                                          |
| **Supervisor**                          | Staging: worker queue nền; tránh **`IMPORT_PROGRESS_RUN_QUEUE_WORKER=true`** đồng thời. `docs/SERVER_RUNBOOK_VI.md`.                                                  |

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

- PHP ini tuning script: `scripts/tune_php83_import_limits.sh`
- Staging vận hành (Supervisor, deploy): `docs/SERVER_RUNBOOK_VI.md`; rehearsal/zip: `docs/STAGING_OPERATIONS.md`
- Supervisor mẫu cấu hình queue: `deploy/supervisor/craveva-queue-all.conf.example`
- Import & poll: `FUNC_IMPORT/IMPORT_MECHANISMS_POLL_AND_QUEUE_VI.md`
