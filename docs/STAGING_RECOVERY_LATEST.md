# Staging Recovery - Latest Runbook

Last updated: `2026-03-27`
Target host: `craveva-staging`
App path: `/var/www/craveva-staging/current/craveva`

## Purpose

Quick operational runbook to:

- diagnose `No space left on device` on staging
- recover disk safely (without DB/schema changes)
- preserve rollback assets before cleanup
- ensure `git pull --ff-only` can complete

Detailed incident log (date-based):

- `docs/STAGING_DISK_RECOVERY_2026-03-27.md`

## Safety rules

- Do not run destructive DB commands (`migrate:fresh`, `db:wipe`, drop/truncate).
- Backup first, cleanup second.
- Prefer reversible actions (cache/log/temp cleanup).
- If DB restore is needed, require explicit approval.

## Standard recovery procedure

### 1) Quick health check

```bash
ssh craveva-staging "df -h"
ssh craveva-staging "sudo du -xhd1 / | sort -h"
ssh craveva-staging "sudo du -xhd1 /var | sort -h"
ssh craveva-staging "sudo du -xhd1 /var/www | sort -h"
ssh craveva-staging "sudo du -xhd1 /home | sort -h"
```

### 2) Preserve backups to local first

```powershell
$date = Get-Date -Format "yyyyMMdd"
$local = "staging-backups-$date"
mkdir -Force $local | Out-Null
scp "craveva-staging:/home/hoangphat5393/backups/*" "$local/"
```

Verify local files exist before deleting remote backups.

### 3) Safe cleanup on staging

```bash
ssh craveva-staging "
  rm -f /home/hoangphat5393/backups/* &&
  sudo apt-get clean &&
  sudo journalctl --vacuum-time=7d &&
  sudo find /var/log -type f \( -name '*.gz' -o -name '*.1' \) -delete
"
```

Laravel app temp cleanup:

```bash
ssh craveva-staging "
  cd /var/www/craveva-staging/current/craveva &&
  sudo -u www-data php artisan optimize:clear || true &&
  sudo find storage/framework/cache -type f -delete 2>/dev/null || true &&
  sudo find storage/framework/sessions -type f -mtime +2 -delete 2>/dev/null || true &&
  sudo find storage/logs -type f -name '*.log' -mtime +7 -delete 2>/dev/null || true
"
```

**Không** xóa thư mục `.git` lồng trong `vendor/**` để giải phóng dĩa. Việc này làm Composer báo lỗi *commit-deps* / thiếu `.git` khi `composer install`, và có thể phá cài đặt kiểu source.

Nếu đã lỡ xóa hoặc `vendor` hỏng: cài lại sạch theo `composer.lock` (không dùng `composer update`):

```bash
ssh craveva-staging "
  cd /var/www/craveva-staging/current/craveva &&
  rm -rf vendor &&
  APP_ENV=production composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist
"
```

### 3b) Lỗi 500: `IdeHelperServiceProvider` không tìm thấy

`barryvdh/laravel-ide-helper` là **require-dev**; staging dùng `--no-dev` nên class không có. Code trong `AppServiceProvider` phải chỉ `register` khi `class_exists(...)` (đã sửa trên `main`).

Sau khi pull code mới:

```bash
ssh craveva-staging "
  cd /var/www/craveva-staging/current/craveva &&
  sudo chown -R www-data:www-data storage bootstrap/cache &&
  sudo chmod -R 775 storage bootstrap/cache &&
  sudo -u www-data php artisan optimize:clear
"
```

### 4) Pull verification

```bash
ssh craveva-staging "
  cd /var/www/craveva-staging/current/craveva &&
  git pull --ff-only
"
```

### 5) Post-check

```bash
ssh craveva-staging "df -h /"
ssh craveva-staging "ls -lah /home/hoangphat5393/backups"
```

Target after recovery:

- root filesystem has free space > 2GB
- pull completes without pack/index errors

## Rollback guide (when needed)

Use local archived files from latest backup folder (for example `staging-backups-YYYYMMDD`).

Code rollback (preferred first):

```bash
cd /var/www/craveva-staging/current
mkdir -p rollback_restore_TIMESTAMP
tar -xzf /path/to/craveva-code-*.tar.gz -C rollback_restore_TIMESTAMP
```

DB rollback (destructive, approval required):

```bash
mysql -u <user> -p <database_name> < /path/to/craveva-staging-*.sql
```

## Operational prevention

- Keep only last 3-5 backup sets on staging.
- Move long-term backups to external storage.
- Run periodic cleanup (logs/cache/sessions).
- Alert when disk usage crosses 85%.
- Check free disk before large pull/deploy.
