# Tăng timeout Nginx trên staging (tránh 504 khi import client)

Khi import client, request **poll progress** chạy tối đa 50 job trong một request HTTP. Nếu vượt quá thời gian chờ mặc định của Nginx (thường 60s) → **504 Gateway Time-out**. Cách xử lý: tăng `fastcgi_read_timeout` và `proxy_read_timeout` lên 300s (5 phút) trong cấu hình Nginx trên staging.

---

## Cách 1: Chạy script có sẵn (khuyến nghị)

### Bước 1: SSH vào staging

```bash
ssh craveva-staging
```

(Nếu dùng gcloud: xem **FUNC_BUG/STAGING_ACCESS_VIA_GOOGLE_CLOUD.md**.)

### Bước 2: Đưa script lên server (nếu chưa có)

Từ **máy local** (PowerShell, trong thư mục project):

```powershell
scp FUNC_BUG/apply_nginx_timeout_staging.sh craveva-staging:/tmp/
```

### Bước 3: Trên staging, chạy script

```bash
sudo bash /tmp/apply_nginx_timeout_staging.sh
```

Script sẽ:

- Backup config hiện tại thành `/etc/nginx/sites-available/staging.bak.timeout.YYYYMMDD`
- Thêm hoặc cập nhật `fastcgi_read_timeout 300s` và `proxy_read_timeout 300s` trong block `server { }`
- Chạy `nginx -t` để kiểm tra cấu hình
- Reload Nginx

### Bước 4: Thử lại import client

Mở lại trang import client trên staging, upload file và bấm xử lý. Request progress có thể chờ tối đa **300 giây** (5 phút) trước khi Nginx trả 504.

---

## Cách 2: Sửa tay trên staging

1. SSH vào staging: `ssh craveva-staging`
2. Backup: `sudo cp /etc/nginx/sites-available/staging /etc/nginx/sites-available/staging.bak.$(date +%Y%m%d)`
3. Mở file: `sudo nano /etc/nginx/sites-available/staging`
4. Trong block **`server {`**, thêm hai dòng (ngay sau `server {` hoặc sau `client_max_body_size` nếu có):

    ```nginx
    fastcgi_read_timeout 300s;
    proxy_read_timeout 300s;
    ```

5. Lưu (Ctrl+O, Enter) và thoát (Ctrl+X).
6. Kiểm tra: `sudo nginx -t`
7. Áp dụng: `sudo systemctl reload nginx`

---

## Hoàn tác (khôi phục config cũ)

```bash
sudo cp /etc/nginx/sites-available/staging.bak.timeout.YYYYMMDD /etc/nginx/sites-available/staging
sudo nginx -t && sudo systemctl reload nginx
```

(Thay `YYYYMMDD` bằng ngày đã backup, ví dụ `20260312`.)
