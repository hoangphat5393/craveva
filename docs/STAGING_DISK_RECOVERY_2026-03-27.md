# Staging Disk Recovery Log - 2026-03-27

Environment:

- Host: `craveva-staging`
- App path: `/var/www/craveva-staging/current/craveva`
- Goal: free disk safely so `git pull` works
- Safety policy: no destructive DB actions, no schema/data changes

## 1) Incident summary

Initial failure during pull:

```text
fatal: write error: No space left on device
fatal: fetch-pack: invalid index-pack output
```

Initial disk state:

```text
/dev/root 20G used, 0 avail, 100%
```

## 2) Root cause findings

Top usage on server:

- `/var` ~14G
- `/var/www` ~7.6G
- `/var/lib` ~4.8G
- `/var/log` ~1.2G
- `/home/hoangphat5393/backups` ~1.1G

Important note:

- `storage` in app is only ~180M, so it is not the primary source of disk full in this incident.

## 3) Backup preservation before cleanup

Downloaded remote backup files to local machine first, then removed remote copies.

Local archive folder:

- `e:\web\craveva-staging\staging-backups-20260327`

Downloaded files:

- `craveva-code-2026-03-23-094935.tar.gz` (~509MB)
- `craveva-staging-2026-03-23-094935.sql` (~526MB)
- `craveva-staging-2026-03-23-094935.gitsha`

## 4) Commands executed (safe cleanup)

### 4.1 Audit

```bash
ssh craveva-staging "df -h"
ssh craveva-staging "sudo du -xhd1 / | sort -h"
ssh craveva-staging "sudo du -xhd1 /var | sort -h"
ssh craveva-staging "sudo du -xhd1 /var/www | sort -h"
ssh craveva-staging "sudo du -xhd1 /home | sort -h"
```

### 4.2 Download remote backups to local

```powershell
mkdir -Force "staging-backups-20260327" | Out-Null
scp "craveva-staging:/home/hoangphat5393/backups/*" "staging-backups-20260327/"
```

### 4.3 Cleanup on server (after local backup success)

```bash
rm -f /home/hoangphat5393/backups/*
sudo apt-get clean
sudo journalctl --vacuum-time=7d
sudo find /var/log -type f \( -name '*.gz' -o -name '*.1' \) -delete
```

Laravel-safe cache/session/log cleanup:

```bash
cd /var/www/craveva-staging/current/craveva
sudo -u www-data php artisan optimize:clear
sudo find storage/framework/cache -type f -delete
sudo find storage/framework/sessions -type f -mtime +2 -delete
sudo find storage/logs -type f -name '*.log' -mtime +7 -delete
```

Vendor cleanup (remove nested package git metadata only):

```bash
cd /var/www/craveva-staging/current/craveva
sudo find vendor -type d -name .git -prune -exec rm -rf {} +
```

## 5) Pull verification

Command:

```bash
cd /var/www/craveva-staging/current/craveva
git pull --ff-only
```

Result:

- Pull successful (fast-forward to newer `main`).

## 6) Final state

Disk after cleanup:

```text
/dev/root 20G total, 17G used, 3.2G avail, 84%
```

Remote backup directory after cleanup:

```text
/home/hoangphat5393/backups is empty
```

## 7) Rollback plan

If rollback is needed, use the local files in:

- `e:\web\craveva-staging\staging-backups-20260327`

### 7.1 Restore code snapshot (optional)

```bash
# Example flow on staging (choose destination carefully)
cd /var/www/craveva-staging/current
mkdir -p rollback_restore_20260327
tar -xzf /path/to/craveva-code-2026-03-23-094935.tar.gz -C rollback_restore_20260327
```

### 7.2 Restore DB snapshot (only if explicitly approved)

```bash
# DANGEROUS on active environment - confirm target DB first
mysql -u <user> -p <database_name> < /path/to/craveva-staging-2026-03-23-094935.sql
```

Notes:

- DB restore is destructive to current DB state and must be approved before running.
- Prefer code-only rollback first when possible.

## 8) Prevention checklist for next deploy

- Keep only last N backups in `~/backups` (or move backups to object storage).
- Add periodic cleanup for `/var/log` and Laravel temporary data.
- Keep `git pull --ff-only` workflow and check `df -h` before large pulls.
- Monitor `/dev/root` and alert when usage >85%.

## 9) Lesson learned (vendor)

**Do not** run bulk deletion of `vendor/**/.git` to free disk. That breaks Composer when packages were installed from source, causing errors like _The .git directory is missing from vendor/laravel/framework_.

**Recovery:** `rm -rf vendor` then `APP_ENV=production composer install --no-dev --optimize-autoloader --prefer-dist` (never `composer update` on staging).

**IdeHelper on staging:** `AppServiceProvider` must register `IdeHelperServiceProvider` only when `class_exists(...)` because `laravel-ide-helper` is `require-dev` and not installed with `--no-dev`.
