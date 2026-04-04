# PHP-FPM, queue worker và quyền thư mục Laravel

Tài liệu tham chiếu nhanh: **user nào** chạy web/worker và **thư mục nào** cần quyền ghi để app (cache file, log, import, v.v.) hoạt động ổn định.

---

## PHP-FPM dùng user nào?

**Không cố định** — phụ thuộc **pool** trong cấu hình FPM (thường `/etc/php/*/fpm/pool.d/www.conf` hoặc file pool riêng theo site):

- Trên **Ubuntu/Debian**, mặc định thường là **`www-data`** (cả `user` và `group` trong pool).
- Có thể đổi sang user khác (ví dụ `nginx`, `deploy`) tùy hosting.

**Cách kiểm tra trên server:** xem `user` / `group` trong file pool FPM đang phục vụ site, hoặc `ps aux | grep php-fpm` và xem process worker FPM chạy dưới user nào.

---

## Queue worker dùng user nào?

**User của process** thực sự chạy `php artisan queue:work` / `php artisan schedule:run`:

| Cách chạy                                   | User thường gặp |
| ------------------------------------------- | --------------- |
| Cron trong crontab của user `alice`         | **`alice`**     |
| Supervisor có `user=www-data`               | **`www-data`**  |
| `sudo -u www-data php artisan schedule:run` | **`www-data`**  |

Worker **có thể khác** PHP-FPM nếu không chủ ý đồng bộ — đây là nguyên nhân hay gặp **lỗi ghi `storage/`** (một bên tạo file `www-data`, một bên không ghi được).

---

## Thư mục cần quyền ghi (Laravel)

| Thư mục                | Mục đích                                                                                                              |
| ---------------------- | --------------------------------------------------------------------------------------------------------------------- |
| **`storage/`**         | Log, file cache (`storage/framework/cache`), session (nếu driver `file`), file tạm, một số luồng upload/cache của app |
| **`bootstrap/cache/`** | Cache config / routes / views đã compile                                                                              |

**Thư mục thường gặp thêm** (nếu app ghi trực tiếp):

| Thư mục                                                     | Ghi chú                              |
| ----------------------------------------------------------- | ------------------------------------ |
| **`public/user-uploads/`** (hoặc path upload trong project) | Chỉ khi thực sự lưu file user tại đó |

---

## Nguyên tắc vận hành

1. **Cùng “ý” user giữa web và worker:** User chạy **PHP-FPM** và user chạy **queue worker** (và cron `schedule:run` nếu worker nằm trong schedule) nên **cùng quyền ghi** tới `storage/` và `bootstrap/cache/`.
2. **Cách phổ biến:** `chown` owner **`www-data:www-data`** (hoặc đúng user/group của pool FPM) cho `storage/` và `bootstrap/cache/`; worker/cron cũng chạy dưới **`www-data`** (ví dụ `sudo -u www-data php artisan schedule:run`), **hoặc** cùng **group** + `chmod` ghi nhóm (ví dụ thư mục `2775` / `775`).
3. Sau khi thêm user deploy vào group `www-data` (`usermod -aG www-data`), cần **phiên đăng nhập mới** để group có hiệu lực với cron/SSH.

---

## Lệnh tham chiếu (điều chỉnh đường dẫn app)

```bash
sudo chown -R www-data:www-data /var/www/craveva-staging/current/craveva/storage /var/www/craveva-staging/current/craveva/bootstrap/cache
sudo chmod -R ug+rwx /var/www/craveva-staging/current/craveva/storage /var/www/craveva-staging/current/craveva/bootstrap/cache
```

_(Nếu FPM không dùng `www-data`, thay `www-data` bằng user/group trong pool.)_

---

## Staging (`craveva-staging`): PHP 8.3, socket và extension

- **Nginx** site staging dùng **`fastcgi_pass unix:/run/php/php8.3-fpm.sock`** (không phụ thuộc `php-fpm.sock` mặc định).
- **`update-alternatives`** cho `php-fpm.sock` nên trỏ **`/run/php/php8.3-fpm.sock`** để script/monitor không nhầm sang 8.4:
    - `sudo update-alternatives --set php-fpm.sock /run/php/php8.3-fpm.sock`
    - Kiểm tra: `readlink -f /etc/alternatives/php-fpm.sock`
- **CLI** `php` đã ưu tiên **8.3** (`/usr/bin/php` → `php8.3`).
- **Đồng bộ extension với máy dev (Windows / PHP cài sẵn):** trên staging đã cài **`php8.3-sqlite3`** để có **`sqlite3`** và **`pdo_sqlite`** (trước đó staging chỉ có MySQL). Các extension khác (bcmath, curl, gd, intl, mbstring, xml, xsl, zip, redis, v.v.) đã có trong **8.3**; bật qua symlink trong `/etc/php/8.3/fpm/conf.d/` (không cần bật thủ công từng file nếu đã `phpenmod`/APT).
- **`memory_limit` PHP 8.3 FPM:** tăng trong `/etc/php/8.3/fpm/php.ini` (ví dụ **1024M**) rồi `sudo systemctl reload php8.3-fpm`.
- Sau khi đổi extension hoặc ini: **reload** `php8.3-fpm`; `nginx -t` nếu sửa vhost.

---

## Tóm tắt một dòng

Xác định user **PHP-FPM** trên server; cho **worker + cron `schedule:run`** dùng **cùng user đó** (hoặc cùng group + quyền thư mục phù hợp); đảm bảo **`storage/`** và **`bootstrap/cache/`** (và thư mục upload nếu có) **ghi được** bởi cả hai.

---

## Import Client (chunk) — `Permission denied` trong `storage/framework/cache/data/...`

### Triệu chứng (staging)

Lỗi kiểu:

`file_put_contents(.../storage/framework/cache/data/...): Failed to open stream: Permission denied`

Stack trace thường qua `Illuminate\Filesystem\Filesystem::put` → **file cache** — trong code có **`StoresImportBatchMetrics::mergeImportBatchMetrics`** (`Cache::put` / lock file) khi job **`ImportClientChunkJob`** chạy.

**Driver:** `CACHE_DRIVER=file` (mặc định hoặc `.env`) → mỗi key cache tạo file (và thư mục con băm) dưới `storage/framework/cache/data/`.

### Có phải “cứ có file/cache mới là phải chmod lại”?

**Không** — nếu cấu hình đúng **một lần**:

- Thư mục cha **`storage/`** (và **`storage/framework/cache`**) thuộc đúng owner/group (thường **`www-data`**) **và**
- Worker queue chạy **cùng user** với FPM (**`www-data`**) **hoặc**
- Đã bật **default ACL** (`setfacl -dR`) trên `storage/` và `bootstrap/cache/` để mọi file/thư mục **mới** sinh ra vẫn cho phép **cả** FPM **và** user deploy ghi (xem `scripts/upload_staging.ps1` — khối `setfacl` sau deploy).

Khi đó Laravel tạo thêm thư mục `data/d3/da/...` **không** cần chỉnh quyền tay từng file.

### Vì sao “hôm qua ổn, hôm nay lại lỗi”?

Các nguyên nhân hay gặp **lặp lại** sau khi đã “sửa quyền”:

| Nguyên nhân                                                    | Giải thích ngắn                                                                                                                                                                                                                                                                         |
| -------------------------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Worker ≠ FPM user**                                          | `queue:work` / Supervisor chạy user **`ubuntu`**, **`deploy`**, hoặc **`root`**, trong khi web ghi cache bằng **`www-data`** (hoặc ngược lại) → thư mục/file do một bên tạo, bên kia **không ghi được**.                                                                                |
| **`php artisan cache:clear` / `optimize:clear` chạy sai user** | Chạy **`sudo php artisan ...`** (mặc định **root**) hoặc user khác `www-data` → Laravel tạo lại cây `storage/framework/cache` **owner root** → FPM/worker **www-data** báo _Permission denied_. **Luôn ưu tiên:** `sudo -u www-data php artisan cache:clear` (và worker cũng www-data). |
| **Deploy / `git pull` / unzip bằng root**                      | File hoặc thư mục mới trong `bootstrap/cache` hoặc vài path dưới `storage` bị **`root:root`** nếu có lệnh tạo file không qua `www-data`.                                                                                                                                                |
| **Chưa có default ACL**                                        | Sau `chown` một lần, nếu không có **`setfacl -dR`**, vẫn có thể ổn **cho đến khi** có thao tác ở trên làm đổi owner cây cache.                                                                                                                                                          |
| **Đổi `current` symlink (release mới)**                        | Nếu mỗi release là thư mục mới và **chưa** `chown`/`setfacl` cho `storage` của release đó (ít gặp nếu `storage` là shared mount; hay gặp nếu copy tree mới thiếu bước).                                                                                                                 |

### Kiểm tra nhanh trên server

```bash
APP=/var/www/craveva-staging/current/craveva
# User của PHP-FPM (thường www-data)
ps aux | grep 'php-fpm: pool' | grep -v grep | head -3
# User của queue worker
ps aux | grep 'queue:work' | grep -v grep
# Quyền cây cache
namei -l "$APP/storage" "$APP/storage/framework/cache" 2>/dev/null | tail -20
ls -la "$APP/storage/framework/cache/data" 2>/dev/null | head -5
```

Hai dòng **`ps`** phải thấy **cùng user** (lý tưởng: `www-data`) cho pool FPM và `queue:work`.

### Sửa nhanh (staging path chuẩn — chỉnh nếu đường dẫn khác)

```bash
APP=/var/www/craveva-staging/current/craveva
sudo chown -R www-data:www-data "$APP/storage" "$APP/bootstrap/cache"
sudo chmod -R ug+rwx "$APP/storage" "$APP/bootstrap/cache"
DEPLOY_USER=$(whoami)
sudo setfacl -R  -m u:www-data:rwX,u:$DEPLOY_USER:rwX "$APP/storage" "$APP/bootstrap/cache" 2>/dev/null || true
sudo setfacl -dR -m u:www-data:rwX,u:$DEPLOY_USER:rwX "$APP/storage" "$APP/bootstrap/cache" 2>/dev/null || true
sudo systemctl reload php8.3-fpm
```

Sau đó xóa cache **bằng www-data** (tránh tạo lại file root):

```bash
sudo -u www-data php "$APP/artisan" cache:clear
```

### Giảm phụ thuộc file cache (tuỳ chọn)

Nếu trên staging/hub đã có **Redis**, có thể đặt **`CACHE_DRIVER=redis`** trong `.env` để metric import (và cache chung) không ghi xuống `storage/framework/cache` — vẫn cần quyền ghi **`storage/logs`** và các luồng khác; quyền FPM/worker vẫn nên đồng bộ.

---

## Liên quan

- Staging / import đứng 0% — nguyên nhân queue + permission: `FUNC_LOGIC/ORDER_HISTORY_IMPROVE_PLAN.MD`
- Vận hành staging: `docs/STAGING_OPERATIONS.md`
