# Staging — recovery nhanh (ổ đĩa / git / vendor)

**Runbook deploy/quyền/queue:** **`docs/SERVER_RUNBOOK_VI.md`**. Rehearsal Phase 3, zip upload, kiểm tra nhanh: **`docs/STAGING_OPERATIONS.md`**.

**Log incident chi tiết (2026-03-27):** `docs/STAGING_DISK_RECOVERY_2026-03-27.md`

---

## Lệnh tóm tắt (giữ lại để copy nhanh)

Target: `craveva-staging`, app: `/var/www/craveva-staging/current/craveva`

```bash
ssh craveva-staging "df -h"
```

Dọn an toàn + Laravel (xem OPERATIONS để đủ ngữ cảnh):

```bash
ssh craveva-staging "
  cd /var/www/craveva-staging/current/craveva &&
  sudo -u www-data php artisan optimize:clear || true
"
```

Vendor hỏng:

```bash
ssh craveva-staging "
  cd /var/www/craveva-staging/current/craveva &&
  rm -rf vendor &&
  APP_ENV=production composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist
"
```

---

_Stub: runbook chính ở `SERVER_RUNBOOK_VI.md`; tác vụ dài staging ở `STAGING_OPERATIONS.md`._
