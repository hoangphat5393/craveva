# Staging khong thay module Production sau khi git pull

Ngay cap nhat: 2026-05-05

## Trieu chung
- Super Admin > Module Settings khong co module `Production`.
- `php artisan module:list` tren staging khong thay `Production`.

## Nguyen nhan goc
- Staging dang o commit cu (`79ba46ac`), chua chua code `Modules/Production`.
- Chi `git pull` bang user SSH khong dung owner repo nen loi permission `.git/index.lock` / `FETCH_HEAD`.
- Chua dong bo entitlement module theo flow Craveva (`packages.module_in_package`, `module_settings`) va custom modules (`storage/app/modules_statuses.json`).

## Cach xu ly da ap dung (thuc te)

### 1) Kiem tra trang thai tren staging
```bash
cd /var/www/craveva-staging/current/craveva
php artisan module:list --no-ansi
```

### 2) Pull dung user so huu repo
```bash
sudo -u hoangphat5393 /bin/bash -c '
cd /var/www/craveva-staging/current/craveva &&
(git status --porcelain | grep -q . && git stash push -u -m auto-stash-before-production-sync || true) &&
git checkout main &&
git pull origin main
'
```

### 3) Chay migrate + dong bo module
```bash
cd /var/www/craveva-staging/current/craveva
sudo -u www-data php artisan migrate --force
sudo -u www-data php artisan packages:modules activate --module=production
sudo -u www-data php artisan packages:modules enable-custom
sudo -u www-data php artisan optimize:clear
```

### 4) Verify
```bash
sudo -u www-data php artisan module:list --no-ansi
sudo -u www-data php artisan route:list --name=production.
sudo -u www-data php artisan packages:modules list
```

Ky vong:
- `module:list` co dong `Production` va trang thai `[Enabled]`.
- `route:list --name=production.` co cac route `/account/production/...`.
- `packages:modules list` cho thay `production` nam trong module cua cac package.

## Ket qua lan xu ly nay
- Staging da pull len commit `9bdbe6c1`.
- Da co thu muc `Modules/Production`.
- `Production` da enabled trong nwidart va da co route.
- Da dong bo package/module settings cho module `production`.

## Luu y de tranh lap lai
- Luon deploy bang script `scripts/upload_staging.ps1` hoac chay git tren server bang user owner repo (`hoangphat5393`), khong pull bang user khong co quyen ghi `.git`.
- Trong Craveva, co 2 lop can dong bo:
  - Nwidart custom modules (`modules_statuses.json`) -> `packages:modules enable-custom`
  - Business entitlement (`packages.module_in_package` + `module_settings`) -> `packages:modules activate --module=production` (hoac `activate-all-full`)
