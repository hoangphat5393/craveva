# Staging — recovery nhanh (ổ đĩa / git / vendor)

**Đã gộp hướng dẫn đầy đủ:** xem **`docs/STAGING_OPERATIONS.md`** (deploy, `en`/`eng`, upload zip, kiểm tra).

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

_Stub: nội dung dài trước đây đã chuyển vào `STAGING_OPERATIONS.md`._
