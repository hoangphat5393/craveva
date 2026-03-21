# Craveva — Project & Hub (Staging VM) Specification

---

## 1. Project specification

### 1.1 Application

| Item                | Specification                                                                |
| ------------------- | ---------------------------------------------------------------------------- |
| **Framework**       | Laravel **10.x**                                                             |
| **PHP**             | **8.2+** (64-bit)                                                            |
| **Shape**           | Web application with **HTTP/REST APIs** and web UI; **modular** codebase     |
| **Runtime**         | **PHP-FPM** behind **Nginx** or **Apache** (reverse proxy, TLS termination)  |
| **Public web root** | Laravel standard: `public/`                                                  |
| **Front-end build** | **Node.js LTS**, **Laravel Mix** / **Webpack** — assets built for deployment |

### 1.2 Data & background processing

| Item                        | Specification                                                                 |
| --------------------------- | ----------------------------------------------------------------------------- |
| **Database**                | **MySQL 8.x** (or MariaDB **10.6+**), **utf8mb4** / **InnoDB**                |
| **File storage**            | Local disk and/or **S3-compatible** object storage (per configuration)        |
| **Cache / session / queue** | Supported via **file**, **database**, and/or **Redis** (per configuration)    |
| **Queues**                  | **Database** or **Redis** drivers; **queue workers** when async jobs are used |
| **Scheduler**               | **Cron**: `php artisan schedule:run` every minute                             |
| **Real-time (if enabled)**  | Broadcast/WebSockets (e.g. **Pusher**-compatible) per configuration           |

### 1.3 PHP extensions (typical)

`openssl`, `pdo`, `pdo_mysql`, `mbstring`, `tokenizer`, `xml`, `ctype`, `json`, `fileinfo`, `bcmath`, `curl`, `zip`, `gd` or `imagick` (as needed); `imap` if mail integration is used; `redis` if Redis is used.

---

## 2. Staging VM specification

Environment: **staging** (single VM hosting the application stack).

| Item             | Value                                            |
| ---------------- | ------------------------------------------------ |
| **Platform**     | Google Compute Engine                            |
| **Machine type** | **e2-medium**                                    |
| **vCPU**         | **2**                                            |
| **Memory**       | **~4 GiB**                                       |
| **Boot disk**    | **20 GiB** (ext4, system root)                   |
| **OS**           | **Ubuntu 22.04 LTS** (Jammy), **x86_64**         |
| **Kernel**       | Linux **6.8** (GCP image)                        |
| **PHP**          | **8.2.30**                                       |
| **MySQL**        | **8.0.x** (server / client packages as deployed) |

Network: inbound **HTTP/HTTPS** for web traffic; outbound **HTTPS** as required by configured integrations.

---

## 3. Database specification

| Item         | Specification                                                                |
| ------------ | ---------------------------------------------------------------------------- |
| **Engine**   | **MySQL 8**                                                                  |
| **Schema**   | Single application database (name configured per environment)                |
| **Encoding** | **utf8mb4**, **InnoDB**                                                      |
| **Hosting**  | **Google Cloud SQL** (MySQL), accessed from the app per server configuration |
