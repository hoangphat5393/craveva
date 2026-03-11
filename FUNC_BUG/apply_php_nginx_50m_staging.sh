#!/bin/bash
# Chạy trên server staging (sau khi ssh craveva-staging): bash apply_php_nginx_50m_staging.sh
# Hoặc upload file này lên server rồi: chmod +x apply_php_nginx_50m_staging.sh && sudo bash apply_php_nginx_50m_staging.sh
set -e
SUFFIX=$(date +%Y%m%d 2>/dev/null || echo "50m")
echo "=== 1. Backup ==="
sudo cp -a /etc/php/8.2/fpm/php.ini "/etc/php/8.2/fpm/php.ini.bak.50m.${SUFFIX}"
sudo cp -a /etc/nginx/sites-available/staging "/etc/nginx/sites-available/staging.bak.50m.${SUFFIX}"
echo "=== 2. PHP: upload_max_filesize + post_max_size = 50M ==="
sudo sed -i 's/^;\?upload_max_filesize = .*/upload_max_filesize = 50M/' /etc/php/8.2/fpm/php.ini
sudo sed -i 's/^;\?post_max_size = .*/post_max_size = 50M/' /etc/php/8.2/fpm/php.ini
grep -E '^upload_max_filesize|^post_max_size' /etc/php/8.2/fpm/php.ini || true
echo "=== 3. Nginx: client_max_body_size 50M ==="
if sudo grep -q 'client_max_body_size' /etc/nginx/sites-available/staging; then
  echo "Da co client_max_body_size, bo qua them."
else
  sudo sed -i '/server {/a\    client_max_body_size 50M;' /etc/nginx/sites-available/staging
fi
echo "=== 4. Test Nginx ==="
sudo nginx -t
echo "=== 5. Restart PHP-FPM + reload Nginx ==="
sudo systemctl restart php8.2-fpm
sudo systemctl reload nginx
echo "=== 6. Verify ==="
sudo nginx -T 2>/dev/null | grep client_max_body_size
echo "=== Xong. Thu upload file tren staging. ==="
