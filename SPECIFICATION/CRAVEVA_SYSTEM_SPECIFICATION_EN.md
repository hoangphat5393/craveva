# Craveva System Specification

This document provides a comprehensive overview of the Craveva project's technical specifications and server infrastructure requirements.

---

## 1. Project Specification

### 1.1 Core Runtime

| Item                 | Specification                                         |
| -------------------- | ----------------------------------------------------- |
| **Framework**        | Laravel **11.x** (`laravel/framework: ^11.0`)         |
| **PHP**              | **8.3.30**                                            |
| **Application Type** | Web application + REST/API, module-based architecture |
| **Process Model**    | PHP-FPM behind Nginx reverse proxy                    |

### 1.2 Build & Frontend Assets

| Item                | Specification                        |
| ------------------- | ------------------------------------ |
| **Node.js**         | LTS (18.x or 20.x)                   |
| **Package Manager** | `pnpm` (primary) or `npm`            |
| **Bundler**         | Laravel Mix 6 / Webpack 5            |
| **Frontend Stack**  | Bootstrap 4, Laravel Echo, Pusher JS |

### 1.3 Data & Persistence

| Component        | Specification                                |
| ---------------- | -------------------------------------------- |
| **Database**     | **MySQL 8.0+**, utf8mb4 / utf8mb4_unicode_ci |
| **File Storage** | Local storage / Optional AWS S3              |
| **Cache**        | File (default) / Redis (recommended)         |
| **Sessions**     | File / Redis / Database                      |
| **Queues**       | Database / Redis (managed by Supervisor)     |

### 1.4 PHP Extensions (Required)

`openssl`, `pdo`, `pdo_mysql`, `mbstring`, `tokenizer`, `xml`, `ctype`, `json`, `fileinfo`, `bcmath`, `gd` or `imagick`, `zip`, `curl`, `imap`, `redis`.

---

## 2. Infrastructure Requirements

### 2.1 Minimum Hardware Requirements

| Component | Minimum Requirement | Recommended (for Large Data) |
| --------- | ------------------- | ---------------------------- |
| **vCPU**  | 2 Cores             | 4 Cores+                     |
| **RAM**   | 4 GiB               | 8 GiB - 16 GiB               |
| **Disk**  | 20 GiB (SSD)        | 40 GiB+ (SSD)                |

### 2.2 PHP-FPM Optimized Configuration

To handle large data imports (>10k rows), the following settings are required:

- **`memory_limit`**: Minimum **1024M**.
- **`post_max_size`**: Minimum **64M**.
- **`upload_max_filesize`**: Minimum **64M**.
- **`max_execution_time`**: Minimum **300** seconds.

---

## 3. Server Environment Inventory (Snapshot 2026-04-06)

### 3.1 Staging Environment (`craveva-staging`)

- **Role**: Development and testing environment.
- **CPU**: 2 × Intel Xeon @ 2.2GHz.
- **RAM**: **~7.8 GiB** (Upgraded).
- **Disk**: 20G total (~62% used).
- **PHP-FPM Pool**: `pm.max_children = 2`, `pm.max_spare_servers = 2`.
- **Background Processes**: Supervisor running `craveva-queue-all`.

### 3.2 Hub Environment (`craveva-hub-server`)

- **Role**: Production (Go-Live) environment.
- **CPU**: 4 × Intel Xeon @ 2.2GHz.
- **RAM**: **~15 GiB** (Upgraded).
- **Disk**: 194G total (~29% used).
- **PHP-FPM Pool**: `pm.max_children = 2`, `pm.max_spare_servers = 2`.
- **Swap**: 2 GiB (Usage decreased significantly after RAM upgrade).

---

## 4. Background Workers & Maintenance

- **Scheduler**: `php artisan schedule:run` executes every minute via Cron.
- **Queue Workers**: `php artisan queue:work` managed by **Supervisor**.
- **Optimization**: `php artisan optimize:clear` should be run after every deployment to ensure translation and config updates are applied.
