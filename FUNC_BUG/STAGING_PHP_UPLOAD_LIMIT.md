# Staging: Chỉnh upload_max_filesize và post_max_size (50M)

## Bước 1: SSH vào staging

```bash
ssh craveva-staging
# hoặc: ssh user@<ip-staging>
```

## Bước 2: Kiểm tra PHP hiện tại

```bash
# PHP CLI (xem php.ini đang dùng)
php -i | grep -E "Loaded Configuration File|upload_max_filesize|post_max_size"

# Nếu dùng PHP-FPM (web), xem file cấu hình pool
php -i | grep "Loaded Configuration File"
```

Ghi lại đường dẫn **Loaded Configuration File** (vd. `/etc/php/8.2/fpm/php.ini` hoặc `/etc/php/8.2/cli/php.ini`). Trên server thường có 2 file: **cli** và **fpm**; cần sửa **fpm** để web upload đúng.

## Bước 3: Tìm file php.ini của PHP-FPM

```bash
# Ví dụ Ubuntu/Debian
ls /etc/php/*/fpm/php.ini
# hoặc
ls /etc/php/8.*/fpm/php.ini
```

## Bước 4: Chỉnh upload_max_filesize và post_max_size

**Cách 1: Sửa trực tiếp (cần quyền sudo)**

```bash
# Thay 8.2 bằng đúng version PHP của bạn
sudo sed -i 's/^upload_max_filesize = .*/upload_max_filesize = 50M/' /etc/php/8.2/fpm/php.ini
sudo sed -i 's/^post_max_size = .*/post_max_size = 50M/' /etc/php/8.2/fpm/php.ini
```

**Cách 2: Sửa tay**

```bash
sudo nano /etc/php/8.2/fpm/php.ini
```

Tìm và đặt:

```ini
upload_max_filesize = 50M
post_max_size = 50M
```

(Lưu ý: không có khoảng trắng quanh `=` hoặc dùng 1 khoảng trắng tùy chuẩn file hiện tại.)

## Bước 5: Khởi động lại PHP-FPM

```bash
# Ubuntu/Debian
sudo systemctl restart php8.2-fpm

# Hoặc nếu version khác
sudo systemctl restart php-fpm
```

## Bước 6: Kiểm tra lại (qua CLI)

```bash
php -i | grep -E "upload_max_filesize|post_max_size"
```

Nếu dùng **pool riêng** (vd. www.conf), có thể có override trong `/etc/php/8.2/fpm/pool.d/www.conf`:

```bash
grep -E "php_admin_value|upload_max_filesize|post_max_size" /etc/php/8.2/fpm/pool.d/www.conf
```

Nếu có dòng `php_admin_value` ghi đè thì sửa hoặc comment trong file pool đó.

## Tóm tắt lệnh (copy nguyên block)

```bash
# 1. Kiểm tra version PHP và file config
php -v
php -i | grep "Loaded Configuration File"

# 2. Sửa (thay 8.2 bằng version thực tế, ví dụ 8.1, 8.3)
sudo sed -i 's/^upload_max_filesize = .*/upload_max_filesize = 50M/' /etc/php/8.2/fpm/php.ini
sudo sed -i 's/^post_max_size = .*/post_max_size = 50M/' /etc/php/8.2/fpm/php.ini

# 3. Restart PHP-FPM
sudo systemctl restart php8.2-fpm

# 4. Kiểm tra
php -i | grep -E "upload_max_filesize|post_max_size"
```

Sau khi xong, thử upload lại file import trên staging. Nếu vẫn 413 thì cần tăng thêm **Nginx** `client_max_body_size` (xem tài liệu 413 trước đó).
