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

/var/www/craveva-staging/current/craveva/storage
/var/www/craveva-staging/current/craveva/bootstrap/cache

_(Nếu FPM không dùng `www-data`, thay `www-data` bằng user/group trong pool.)_

---

## Tóm tắt một dòng

Xác định user **PHP-FPM** trên server; cho **worker + cron `schedule:run`** dùng **cùng user đó** (hoặc cùng group + quyền thư mục phù hợp); đảm bảo **`storage/`** và **`bootstrap/cache/`** (và thư mục upload nếu có) **ghi được** bởi cả hai.

---

## Liên quan

- Staging / import đứng 0% — nguyên nhân queue + permission: `FUNC_LOGIC/ORDER_HISTORY_IMPROVE_PLAN.MD`
- Vận hành staging: `docs/STAGING_OPERATIONS.md`
