# Craveva — Project Specification & Minimum Requirements

**Sources:** `composer.json`, `package.json`, Laravel `config/*`.

---

## 1. Project specification

### 1.1 Core runtime

| Item                 | Specification                                                                                                  |
| -------------------- | -------------------------------------------------------------------------------------------------------------- |
| **Framework**        | Laravel **11.x** (`laravel/framework: ^11.0`)                                                                  |
| **PHP**              | **8.3.x**                                                                                                      |
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

| Component        | Specification                                                                           |
| ---------------- | --------------------------------------------------------------------------------------- |
| **Database**     | **MySQL 8.0+**, **utf8mb4** / **utf8mb4_unicode_ci**, **InnoDB**; default port **3306** |
| **File storage** | Local under public upload path; optional **S3** (`league/flysystem-aws-s3-v3`)          |
| **Cache**        | Default **file**; **Redis** supported (Recommended for performance)                     |
| **Sessions**     | Default **file**; **Redis** / database supported                                        |
| **Queues**       | **sync** default; **database** / **redis** + workers khi cấu hình (`config/queue.php`)  |

### 1.4 PHP extensions (Required)

`openssl`, `pdo`, `pdo_mysql`, `mbstring`, `tokenizer`, `xml`, `ctype`, `json`, `fileinfo`, `bcmath`, `gd` or `imagick`, `zip`, `curl`; `imap` (nếu dùng tính năng mail); `redis` (nếu dùng Redis).

---

## 2. Minimum Hardware Requirements

Yêu cầu tối thiểu để vận hành hệ thống ERP với các tác vụ xử lý dữ liệu lớn (Import/Export).

| Component | Minimum Requirement | Recommended (for Large Data) |
| --------- | ------------------- | ---------------------------- |
| **vCPU**  | 2 Cores             | 4 Cores+                     |
| **RAM**   | 4 GiB               | 8 GiB - 16 GiB               |
| **Disk**  | 20 GiB (SSD)        | 40 GiB+ (SSD)                |

### 2.1 PHP-FPM Optimized Configuration

Để xử lý các file dữ liệu lớn (>10k dòng), cấu hình PHP cần đảm bảo:

- **`memory_limit`**: Tối thiểu **1024M**.
- **`post_max_size`**: Tối thiểu **64M**.
- **`upload_max_filesize`**: Tối thiểu **64M**.
- **`max_execution_time`**: Tối thiểu **300** (seconds).

### 2.2 Background Workers

Hệ thống yêu cầu chạy các tiến trình nền để xử lý Queue và Schedule:

- **Cron Job**: `php artisan schedule:run` chạy mỗi phút.
- **Queue Worker**: `php artisan queue:work` (Khuyên dùng **Supervisor** để quản lý).
