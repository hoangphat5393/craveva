# Staging Deploy Progress - PHP 8.3 + Laravel 11

Environment: `craveva-staging` (`/var/www/craveva-staging/current/craveva`)  
Policy: **No destructive DB actions** (`migrate:fresh`, `db:wipe`, drop/truncate are forbidden).

## Overall Status

- **Current:** Deploy steps aligned with Git `composer.lock` + safe migrate + caches.
- **Final:** **SUCCESS** (last verified run on staging via SSH)

---

## Quan trọng: `composer update` trên staging

- **`composer update`** thay đổi `composer.lock` và có thể kéo **dev** (Pest, PHPUnit, …) → **không khớp** repo / môi trường production.
- **Chuẩn an toàn sau khi pull Git:**
    1. `git checkout HEAD -- composer.lock` _(hoặc `git restore composer.lock`)_ — **khôi phục lock đúng với commit đang checkout**
    2. `APP_ENV=production composer install --no-dev --optimize-autoloader`
- Chỉ chạy `composer update` trên máy dev / nhánh riêng, commit `composer.lock`, rồi deploy.

---

## Step Log (đã thực hiện)

### Backup (trước đó)

- SQL dump + tar code + git tag `pre-l11-php83-*` trong `~/backups/` (xem phiên trước).

### PHP 8.3 + Nginx

- PHP CLI/FPM 8.3, nginx trỏ `php8.3-fpm.sock`.

### Đồng bộ code & Composer (bước vừa xử lý)

Commands:

```bash
cd /var/www/craveva-staging/current/craveva
git checkout HEAD -- composer.lock   # bỏ thay đổi lock do composer update
git pull --ff-only origin main
APP_ENV=production composer install --no-dev --optimize-autoloader --no-interaction
```

Kết quả: vendor khớp `composer.lock` trên Git; gói **dev** đã gỡ (Pest/PHPUnit/…) khi dùng `--no-dev`.

### Migration + cache (an toàn)

```bash
APP_ENV=production php artisan migrate --force
APP_ENV=production php artisan config:cache
APP_ENV=production php artisan route:cache
APP_ENV=production php artisan view:cache
```

- **Migrate:** `Nothing to migrate` (DB đã áp dụng migrations hiện có).
- **Laravel:** 11.50.0 | **PHP:** 8.3.30 | Config/Routes/Views: cached.

### Lưu ý `.env` staging

- `php artisan about` có thể hiển thị **Debug ENABLED** nếu `APP_DEBUG=true` — nên tắt trên môi trường thật (`APP_DEBUG=false`) khi policy cho phép.

---

## Risk: `scripts/upload_staging.ps1`

Remote cleanup `find ... -exec rm -rf` — rủi ro filesystem; nên chuyển dần sang release/symlink.

---

## Rollback

1. Restore code: tag/tar backup hoặc `git checkout <tag>`.
2. Restore DB: `mysql <db> < ~/backups/<dump>.sql` (chỉ khi cần).

---

## Update 2026-03-24 (Phase 1 verify)

### What was done safely

```bash
cd /var/www/craveva-staging/current/craveva
git fetch origin main
git pull --ff-only origin main
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
APP_ENV=production php artisan --version
APP_ENV=production php artisan migrate:status
APP_ENV=production php artisan config:cache
APP_ENV=production php artisan route:cache
APP_ENV=production php artisan view:cache
```

### Results

- Pull success to latest `main`.
- Laravel version check success: `11.50.0`.
- HTTP health check: `200 OK` via nginx.
- No destructive DB command executed.

### DB safety status

- **Not executed intentionally:** `php artisan migrate --force` (requires explicit approval because it changes DB schema/data).
- Current pending migrations on staging:
    - `2026_03_23_120100_add_default_warehouse_id_to_client_details_table`
    - `2026_03_23_120200_add_multi_warehouse_indexes_to_stock_movements_table`
    - `2026_03_24_100000_add_inbound_stock_applied_to_delivery_orders_table`
    - `2026_03_24_100100_add_delivery_order_item_id_to_invoice_items_table`

### Note

- Staging disk is still critically high usage. Temporary safe cleanup was applied (cache/log zip cleanup), but a fuller disk-capacity cleanup plan is recommended soon.

---

_Last updated: 2026-03-24_
