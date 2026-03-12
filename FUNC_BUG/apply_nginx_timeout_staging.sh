#!/bin/bash
# Tăng timeout Nginx cho upstream PHP (tránh 504 khi import client progress poll)
# Chạy trên server staging: ssh craveva-staging, rồi chạy script này (hoặc upload lên server rồi chạy)
# Cần quyền sudo.

set -e
CONFIG="/etc/nginx/sites-available/staging"
SUFFIX=$(date +%Y%m%d 2>/dev/null || echo "timeout")
TIMEOUT=300

echo "=== 1. Backup Nginx config ==="
sudo cp -a "$CONFIG" "${CONFIG}.bak.timeout.${SUFFIX}"
echo "Backup: ${CONFIG}.bak.timeout.${SUFFIX}"

echo "=== 2. Thêm/cập nhật fastcgi_read_timeout ${TIMEOUT}s và proxy_read_timeout ${TIMEOUT}s ==="
# Đảm bảo có dòng trong server { } (ngay sau server { hoặc sau client_max_body_size nếu có)
if sudo grep -q 'fastcgi_read_timeout' "$CONFIG"; then
  sudo sed -i "s/fastcgi_read_timeout *[^;]*/fastcgi_read_timeout ${TIMEOUT}s/" "$CONFIG"
  echo "  Da co fastcgi_read_timeout, da cap nhat thanh ${TIMEOUT}s."
else
  sudo sed -i "0,/server {/s/server {/server {\n    fastcgi_read_timeout ${TIMEOUT}s;/" "$CONFIG"
  echo "  Da them fastcgi_read_timeout ${TIMEOUT}s."
fi

if sudo grep -q 'proxy_read_timeout' "$CONFIG"; then
  sudo sed -i "s/proxy_read_timeout *[^;]*/proxy_read_timeout ${TIMEOUT}s/" "$CONFIG"
  echo "  Da co proxy_read_timeout, da cap nhat thanh ${TIMEOUT}s."
else
  sudo sed -i "0,/server {/s/server {/server {\n    proxy_read_timeout ${TIMEOUT}s;/" "$CONFIG"
  echo "  Da them proxy_read_timeout ${TIMEOUT}s."
fi

echo "=== 3. Test cấu hình Nginx ==="
sudo nginx -t

echo "=== 4. Reload Nginx ==="
sudo systemctl reload nginx

echo "=== 5. Kiểm tra ==="
sudo nginx -T 2>/dev/null | grep -E 'fastcgi_read_timeout|proxy_read_timeout' || true
echo "=== Xong. Request import progress co the cho toi ${TIMEOUT}s truoc khi Nginx tra 504. ==="
