# Cloud SQL Allowlist Status Report (Hub & Staging)

**Date:** 2026-04-27  
**Operator:** AI agent via SSH + `gcloud`  
**Project:** `craveva-org-55934-project`

## Requested requirement (from customer guide)

- Allow source IP: **`136.110.35.154/32`**
- Allow protocol/port: **TCP `3306`**
- Apply for database connectivity used by **Hub** and **Staging**

## Live verification performed

### 1) SSH access to both servers

- `ssh craveva-staging "hostname && whoami && date"` -> success
- `ssh craveva-hub-server "hostname && whoami && date"` -> success

### 2) App DB target settings on each server

- **Staging app** (`/var/www/craveva-staging/current/craveva/.env`)
    - `DB_HOST=136.110.52.19`
    - `DB_PORT=3306`
    - `DB_DATABASE=craveva_staging`
- **Hub app** (`/var/www/hub.craveva.com/.env`)
    - `DB_HOST=35.240.193.168`
    - `DB_PORT=3306`
    - `DB_DATABASE=hub.craveva.com`

### 3) Cloud SQL authorized networks

- `gcloud sql instances describe craveva-staging-db ...` shows **`136.110.35.154/32`** present.
- `gcloud sql instances describe craveva-hub-server ...` shows **`136.110.35.154/32`** present.

### 4) TCP port reachability from servers to DB host:3306

- `ssh craveva-staging "timeout 15 bash -lc 'cat < /dev/null > /dev/tcp/136.110.52.19/3306'"` -> **OK**
- `ssh craveva-hub-server "timeout 15 bash -lc 'cat < /dev/null > /dev/tcp/35.240.193.168/3306'"` -> **OK**

## Conclusion

The requested allowlist requirement is already implemented on both targets:

- **Staging Cloud SQL (`craveva-staging-db`)** has `136.110.35.154/32`
- **Hub Cloud SQL (`craveva-hub-server`)** has `136.110.35.154/32`
- Both application DB connections use **port `3306`**
- Live TCP checks from both servers to their DB endpoints are successful

No immediate allowlist action is required for this specific requirement.
