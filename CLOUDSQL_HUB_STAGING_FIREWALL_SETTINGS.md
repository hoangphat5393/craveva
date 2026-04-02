# Cloud SQL DB + Firewall Settings (Hub & Staging)

**Project:** `craveva-org-55934-project`  
**Captured:** 2026-03-31

---

## 1) `craveva-hub-server` (Cloud SQL)

- **Engine:** `MYSQL_8_0_41`
- **Region:** `asia-southeast1`
- **State:** `RUNNABLE`
- **Public IP:** `35.240.193.168`
- **Private IP:** `10.249.0.4`
- **Public access enabled (`ipv4Enabled`):** `true`
- **Require SSL (`requireSsl`):** `false`
- **Automated backup enabled:** `true`

### Authorized networks

- `183.81.86.0`
- `0.0.0.0/32`
- `34.126.124.196/32`
- `116.108.126.47/32`
- `35.198.237.131/32`
- `35.240.158.191/32`
- `136.110.35.154/32`
- `35.240.153.233/32`

---

## 2) `craveva-staging-db` (Cloud SQL)

- **Engine:** `MYSQL_8_0`
- **Region:** `asia-southeast1`
- **State:** `RUNNABLE`
- **Public IP:** `136.110.52.19`
- **Private IP:** `10.249.0.12`
- **Public access enabled (`ipv4Enabled`):** `true`
- **Require SSL (`requireSsl`):** `false`
- **Automated backup enabled:** `false`

### Authorized networks

- `136.110.35.154/32`
- `35.240.153.233/32`
- `34.126.124.196/32`
- `116.108.126.47/32`
- `35.240.234.226/32`
- `35.240.158.191/32`
- `123.20.159.147/32`
- `14.224.214.181/32`
- `116.102.45.168/32`
- `35.198.237.131/32`
