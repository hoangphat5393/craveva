# Live Hub L11 Upload & Upgrade Checklist

Muc tieu: luu quy trinh an toan de deploy code va nang cap Laravel 11 tren server hub (live), tranh loi DB va downtime.

## 1) Chuan bi truoc khi deploy

- Xac nhan code da merge/push day du len branch deploy.
- Chot commit can deploy:
    - `git log -1 --oneline`
    - ghi lai SHA vao ticket/deploy note.
- Xac nhan da test tren staging:
    - `php artisan migrate --pretend --force`
    - `php artisan migrate --force`
    - smoke test luong chinh: login, order, invoice, warehouse.
- Kiem tra khong con migration DBAL-phu-thuoc:
    - `rg "->change\\(|->renameColumn\\(" database/migrations Modules -g "*.php"`

## 2) Backup bat buoc (live)

- Snapshot VM/volume hoac backup filesystem.
- Backup DB truoc deploy (full dump):
    - `mysqldump ... > backup_before_l11_YYYYMMDD_HHMM.sql`
- Kiem tra backup file ton tai va dung luong hop ly.

## 3) Maintenance window

- Thong bao maintenance cho team/user.
- Chon khung gio it tai (off-peak).
- Chuan bi 1 nguoi theo doi log trong suot qua trinh.

## 4) Deploy code len live

- SSH vao live:
    - `cd /var/www/<live-path>/current/craveva`
- Kiem tra nhanh trang thai:
    - `git status`
    - `git rev-parse --short HEAD`
- Pull dung commit:
    - `git fetch --all --prune`
    - `git checkout <deploy-branch>`
    - `git pull --ff-only`
- Xac nhan HEAD trung SHA da chot.

## 5) Composer + cache (live)

- Cai dependency production:
    - `composer install --no-dev --optimize-autoloader`
- Khong chay `composer update` tren live.
- Clear/build cache:
    - `php artisan optimize:clear`
    - `php artisan config:cache`
    - `php artisan route:cache`
    - `php artisan view:cache`

## 6) Migration an toan

- Preflight SQL (khong ghi DB):
    - `php artisan migrate --pretend --force --no-interaction`
- Neu output hop ly, migrate that:
    - `php artisan migrate --force --no-interaction`
- Xac nhan:
    - `php artisan migrate:status`
    - khong con `Pending`.

## 7) Health checks sau deploy

- App health:
    - `curl -I -sS https://<live-domain>`
    - ky vong `HTTP 200`.
- Service health:
    - `systemctl is-active nginx`
    - `systemctl is-active php8.3-fpm`
- Log check:
    - `journalctl -u nginx -p err -n 50 --no-pager`
    - `journalctl -u php8.3-fpm -p err -n 50 --no-pager`
    - `storage/logs/laravel-YYYY-MM-DD.log` (khong co error moi nghiêm trong).

## 8) Smoke test toi thieu (manual)

- Dang nhap (admin + user thuong).
- Tao/chinh sua order.
- Tao invoice va xem tong tien.
- Kiem tra warehouse transfer / stock adjustment.
- Kiem tra 1 luong import hoac module nhay cam (neu co).

## 9) Rollback plan (neu co su co)

- Uu tien rollback code ve commit truoc:
    - `git checkout <last-good-sha>`
    - `composer install --no-dev --optimize-autoloader`
    - `php artisan optimize:clear` + build cache lai.
- Neu loi do schema/data:
    - dung backup DB da tao truoc deploy de restore.
- Ghi lai incident + root cause sau khi on dinh.

## 10) Deploy log mau

Luu thong tin sau moi lan deploy:

- Thoi gian bat dau/ket thuc
- Nguoi deploy
- Commit SHA
- Lenh da chay
- Ket qua migrate
- Ket qua health check
- Su co va cach xu ly (neu co)

---

Ghi chu: Tuyet doi tranh cac script xoa file hang loat tren live. Uu tien `git pull` + quy trinh co backup va verify tung buoc.
