# Staging & Hub — inventory tài nguyên & PHP (snapshot)

**Thu thập:** 2026-04-06 (SSH read-only). Giá trị **thay đổi theo thời gian** — cần `free -h`, `uptime` khi điều tra sự cố.

**Cập nhật FPM (mục tiêu vận hành):** **`memory_limit = 1024M`** + **`pm.max_children = 2`** trên **cả staging và hub** — trần lý thuyết pool PHP web **~2 GiB** (2×1024M), phù hợp VM **~4 GiB RAM (cũ)**; hiện tại RAM đã nâng lên **16GB (Hub)** và **8GB (Staging)** nhưng vẫn giữ `max_children = 2` để an toàn cho import lớn (submit HTTP).

**Ràng buộc PHP-FPM (`pm = dynamic`):** khi hạ **`pm.max_children`** xuống **2**, bắt buộc **`pm.max_spare_servers` ≤ `pm.max_children`** (và các chỉ số spare/start phải nhất quán). Nếu chỉ sửa `max_children` mà để **`pm.max_spare_servers = 3`**, FPM **không khởi động** (exit **78**) → Nginx **502 Bad Gateway**.

- **Hub / staging:** đã đồng bộ **`pm.max_spare_servers = 2`** cùng **`pm.max_children = 2`** (**2026-04-04**).

**Supervisor (staging):** đã cài **`supervisor`**, program **`craveva-queue-all`** chạy `queue:work` nền (**2026-04-04**). `.env` staging: **`IMPORT_PROGRESS_RUN_QUEUE_WORKER=false`**. Chi tiết: `docs/SERVER_RUNBOOK_VI.md`, `deploy/supervisor/craveva-queue-all.conf.example`.

---

## 1. Tóm tắt nhanh

| Máy                    | RAM          | Swap                                                 | CPU (vCPU)              | Ổ `/`            | Ghi chú FPM (mục tiêu)                                                                                                                                                                                     |
| ---------------------- | ------------ | ---------------------------------------------------- | ----------------------- | ---------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **craveva-staging**    | **~7.8 GiB** | 2 GiB (file `/swapfile`, lúc quét: **~114MiB dùng**) | 2 × Intel Xeon @ 2.2GHz | 20G (~62% used)  | **`1024M` + `pm.max_children = 2`** + **`pm.max_spare_servers = 2`**. **Supervisor** `craveva-queue-all` (**2026-04-04**). Trước đó 502 do spare **3** &gt; children **2** — xem đoạn ràng buộc phía trên. |
| **craveva-hub-server** | **~15 GiB**  | 2 GiB (lúc quét: **~811MiB đã dùng**)                | 4 × Intel Xeon @ 2.2GHz | 194G (~29% used) | Cùng bộ **`1024M` + children 2 + max_spare 2** (**2026-04-04**). Hub cũng từng lệch spare **3** → FPM fail nếu không sửa. Swap cao — xem mục **5**.                                                        |

---

## 2. Staging (`craveva-staging`) — Môi trường Development (dev)

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

---

## 3. Hub (`craveva-hub-server`) — Môi trường Production (go Live)

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

---
