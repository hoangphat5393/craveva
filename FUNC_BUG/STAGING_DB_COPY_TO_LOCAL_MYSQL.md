# Copy DB từ server cũ → MySQL local trên staging mới (thay vì mở IP Cloud SQL)

Nếu không thể thêm IP **35.240.234.226** vào Cloud SQL **136.110.52.19**, có thể chạy MySQL ngay trên VM staging mới, copy dữ liệu từ DB cũ vào rồi trỏ Laravel sang `127.0.0.1`.

---

## Điều kiện

- Có **file dump** (mysqldump) của database staging từ DB tại 136.110.52.19.  
  Dump phải được tạo từ máy **có quyền kết nối** tới 136.110.52.19 (ví dụ: hub server, máy dev đã được whitelist, hoặc admin export giúp).

---

## Bước 1: Trên máy có kết nối tới DB 136.110.52.19 (hub / máy khác)

Lấy tên DB, user, password từ `.env` staging (DB_DATABASE, DB_USERNAME, DB_PASSWORD). Rồi chạy:

```bash
mysqldump -h 136.110.52.19 -u DB_USERNAME -p DB_DATABASE > staging_backup.sql
```

(Thay `DB_USERNAME`, `DB_DATABASE` bằng giá trị thật; nhập password khi hỏi.)

Nếu dùng SSL/Cloud SQL:

```bash
mysqldump -h 136.110.52.19 -u DB_USERNAME -p --ssl-mode=REQUIRED DB_DATABASE > staging_backup.sql
```

Copy file `staging_backup.sql` lên VM staging mới (scp, sftp, hoặc upload qua Console).

---

## Bước 2: Trên VM staging mới (SSH vào craveva-staging)

### 2.1 Cài MySQL (nếu chưa có)

```bash
sudo apt update
sudo apt install -y mysql-server
sudo systemctl start mysql
sudo systemctl enable mysql
```

### 2.2 Tạo user và database (khớp với .env staging)

```bash
sudo mysql -e "
  CREATE DATABASE IF NOT EXISTS craveva_staging CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
  CREATE USER IF NOT EXISTS 'staging_user'@'localhost' IDENTIFIED BY 'MAT_KHAU_LAY_TU_ENV';
  GRANT ALL ON craveva_staging.* TO 'staging_user'@'localhost';
  FLUSH PRIVILEGES;
"
```

(Thay `craveva_staging`, `staging_user`, `MAT_KHAU_LAY_TU_ENV` bằng DB_DATABASE, DB_USERNAME, DB_PASSWORD trong `.env` trên server.)

### 2.3 Import dump

```bash
cd /var/www/craveva-staging/current/craveva
# Giả sử file dump đã copy vào thư mục này hoặc /tmp
sudo mysql -u staging_user -p craveva_staging < staging_backup.sql
# hoặc: zcat staging_backup.sql.gz | sudo mysql -u staging_user -p craveva_staging
```

### 2.4 Sửa .env trỏ về MySQL local

```bash
cd /var/www/craveva-staging/current/craveva
sudo -u www-data nano .env
# hoặc: sudo sed -i 's/^DB_HOST=.*/DB_HOST=127.0.0.1/' .env
```

Đặt:

- `DB_HOST=127.0.0.1`
- Giữ nguyên `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` (đã dùng khi tạo user và import).

### 2.5 Clear cache Laravel

```bash
cd /var/www/craveva-staging/current/craveva
php artisan config:clear
php artisan cache:clear
sudo systemctl reload php8.2-fpm
```

Sau đó thử lại https://staging.craveva.com/

---

## Lưu ý

- **Backup định kỳ:** MySQL chạy local trên VM thì cần tự backup (cron mysqldump, hoặc snapshot disk).
- **Đồng bộ:** Dữ liệu từ lúc copy trở đi không tự đồng bộ lại với DB 136.110.52.19; staging chạy độc lập.
- **Lấy dump từ đâu:** Nếu không có máy nào kết nối được 136.110.52.19 thì cần nhờ admin export dump và gửi file, rồi làm từ Bước 2.
