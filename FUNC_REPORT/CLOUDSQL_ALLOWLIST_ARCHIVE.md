# Cloud SQL Allowlist — archive (2026-04-27 / 2026-04-28)

**Project:** `craveva-org-55934-project`  
**Instances:** `craveva-staging-db`, `craveva-hub-server`

## Kết luận (2026-04-27)

Yêu cầu allow **`136.110.35.154/32`** TCP **3306** cho Hub + Staging: **đã có** trên cả hai instance. TCP từ app server tới DB host: **OK**.

| Server  | DB host (.env)        | Database          |
| ------- | --------------------- | ----------------- |
| Staging | `136.110.52.19:3306`  | `craveva_staging` |
| Hub     | `35.240.193.168:3306` | `hub.craveva.com` |

## Snapshot authorized networks (2026-04-28)

### craveva-staging-db (IP `136.110.52.19`)

Gồm (trích): `136.110.35.154/32`, `136.110.52.19/32`, `116.102.45.168/32`, `14.224.214.181/32`, …

### craveva-hub-server (IP `35.240.193.168`)

Gồm (trích): `136.110.35.154/32`, `136.110.52.19/32`, `116.108.126.47/32`, `183.81.86.0`, …

**Regenerate:** `scripts/export_sql_allowlist.ps1` → `FUNC_REPORT/CLOUDSQL_ALLOWLIST_STATUS_<timestamp>.md`

_Gộp từ `CLOUDSQL_ALLOWLIST_AUDIT_2026-04-27.md` + `CLOUDSQL_ALLOWLIST_STATUS_20260428.md` (pass 4, 2026-05-27)._
