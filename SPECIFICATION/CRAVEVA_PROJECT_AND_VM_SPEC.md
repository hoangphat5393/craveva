# Craveva — Project & Staging VM Specification

**Sources:** `composer.json`, `package.json`, Laravel `config/*`, and staging host measurements (SSH, 2026-03-20).

---

## 1. Project specification

### 1.1 Core runtime

| Item                 | Specification                                                                                                  |
| -------------------- | -------------------------------------------------------------------------------------------------------------- |
| **Framework**        | Laravel **10.x** (`laravel/framework: ^10.0`)                                                                  |
| **PHP**              | **^8.2**                                                                                                       |
| **Application type** | Web application + REST/API (`froiden/laravel-rest-api`), module-based (`nwidart/laravel-modules`, `Modules/*`) |
| **Process model**    | PHP-FPM behind a reverse proxy                                                                                 |

### 1.2 Build & front-end assets

| Item                | Specification                                |
| ------------------- | -------------------------------------------- |
| **Node.js**         | LTS (e.g. 18.x or 20.x)                      |
| **Package manager** | `pnpm` (see `package.json`); `npm` supported |
| **Bundler**         | Laravel Mix **6** / Webpack **5**            |
| **Front-end**       | Bootstrap 4, Laravel Echo, Pusher JS, etc.   |

### 1.3 Data & persistence

| Component        | Specification                                                                             |
| ---------------- | ----------------------------------------------------------------------------------------- |
| **Database**     | **MySQL**, **utf8mb4** / **utf8mb4_unicode_ci**, **InnoDB**; default port **3306**        |
| **File storage** | Local under public upload path; optional **S3** (`league/flysystem-aws-s3-v3`)            |
| **Cache**        | Default **file**; **Redis** supported                                                     |
| **Sessions**     | Default **file**; **Redis** / database supported                                          |
| **Queues**       | **sync** default; **database** / **redis** + workers when configured (`config/queue.php`) |

### 1.4 Integrations (feature-dependent, via `.env`)

Payments (Stripe, PayPal, Razorpay, Mollie, Square, etc.), notifications (email, SMS, Slack, Telegram, OneSignal, …), broadcasting (Pusher), Google APIs, QuickBooks, Zoom, IMAP, Sentry, backups, Excel — as enabled per environment.

### 1.5 PHP extensions

`openssl`, `pdo`, `pdo_mysql`, `mbstring`, `tokenizer`, `xml`, `ctype`, `json`, `fileinfo`, `bcmath`, `gd` or `imagick`, `zip`, `curl`; `imap` if mail features used; `redis` / phpredis if Redis used.

### 1.6 Web server & TLS

Nginx or Apache → PHP-FPM; document root `public/`; `REDIRECT_HTTPS` in `.env` for HTTPS redirect.

### 1.7 Scheduler & queues

Cron: `php artisan schedule:run` each minute. Queue workers: `php artisan queue:work` when `QUEUE_CONNECTION` is not `sync`.

### 1.8 Network

Inbound **80** / **443**; outbound **HTTPS** to configured third-party APIs.

---

## 2. Staging VM specification

Measured on host **`craveva-staging`** (Google Compute Engine).

| Category           | Value                                             |
| ------------------ | ------------------------------------------------- |
| **Cloud**          | Google Compute Engine                             |
| **Machine type**   | **e2-medium**                                     |
| **vCPU**           | **2** (AMD EPYC 7B12, 1 core, 2 threads per core) |
| **RAM**            | **~3.8 GiB**                                      |
| **Swap**           | **2 GiB** configured                              |
| **Root disk**      | **20 GiB** `ext4` on `/`                          |
| **OS**             | **Ubuntu 22.04.5 LTS** (Jammy)                    |
| **Kernel**         | **6.8.0-1048-gcp**                                |
| **Architecture**   | **x86_64**                                        |
| **PHP (CLI)**      | **8.2.30**                                        |
| **MySQL (client)** | **8.0.45** (Ubuntu package)                       |

---

## 3. Staging database specification

Values below come from **`php artisan db:show`** on the staging app (snapshot; live DB may differ slightly).

| Item                                | Value                                                                                                                                            |
| ----------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------ |
| **Engine**                          | **MySQL 8**                                                                                                                                      |
| **Logical database name**           | **`craveva_staging`**                                                                                                                            |
| **Port**                            | **3306**                                                                                                                                         |
| **Host**                            | Set in **`/var/www/craveva-staging/current/craveva/.env`** (`DB_HOST`) — Google **Cloud SQL** (not the MySQL package bound to `localhost` only). |
| **Character set / collation (app)** | **utf8mb4** / **utf8mb4_unicode_ci** (per `config/database.php`)                                                                                 |
| **Approx. footprint**               | **~481** tables, **~1.7 GiB** total data size (measured)                                                                                         |
| **Open connections**                | Varies (example snapshot: single-digit active connections)                                                                                       |

**Note:** The VM also runs **Cloud SQL Auth Proxy** on `127.0.0.1:3306`, but the Laravel app uses **`DB_HOST` from `.env`** (direct Cloud SQL). Do not assume `mysql` without `-h` matches the app database.

### 3.1 Script to open the DB CLI (recommended)

From Windows, same SSH target as `scripts/upload_staging.ps1`:

```powershell
.\scripts\mysql_staging.ps1
```

This runs on the server:

`cd /var/www/craveva-staging/current/craveva && sudo -u www-data php artisan db`

So you use the **same connection as PHP-FPM**, without copying passwords into your shell history locally.

### 3.2 Safe use — avoid breaking staging

- **Prefer read-only work:** `SELECT` with **`LIMIT`**, `EXPLAIN`, `SHOW` — avoid ad-hoc `UPDATE`/`DELETE` without a `WHERE` you have double-checked.
- **No destructive Artisan from habit:** never run `php artisan db:wipe` on staging. Be careful with `migrate:fresh`, `migrate:rollback` on shared environments.
- **Heavy queries:** large scans can slow the app for everyone; run during **low-traffic** windows when possible; avoid unbounded `SELECT *` on huge tables.
- **Locks:** long transactions or DDL during business hours can block web requests; keep maintenance windows short.
- **Backups:** before bulk changes or schema edits, confirm a **recent backup / export** exists (per your ops process).
- **One writer:** avoid running migrations from two places at once; coordinate with teammates.

If you only need **schema / size info** without an interactive shell:

```text
ssh craveva-staging "cd /var/www/craveva-staging/current/craveva && sudo -u www-data php artisan db:show"
```
