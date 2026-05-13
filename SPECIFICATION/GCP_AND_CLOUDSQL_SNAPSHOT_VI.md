# GCP & Cloud SQL — snapshot tài nguyên (gộp)

> **Gộp (2026-05-12):** `GCP_RESOURCE_INVENTORY_2026-04-06.md` + `CLOUDSQL_HUB_STAGING_FIREWALL_SETTINGS.md`. Snapshot **lịch sử** — đối chiếu GCP Console / `gcloud` khi nghi ngờ lệch.
>
> **SSH / PHP-FPM / RAM từng máy:** `SPECIFICATION/STAGING_HUB_SERVER_INFO_2026-04-06.md`  
> **Runbook vận hành:** `docs/SERVER_RUNBOOK_VI.md`  
> **Bản GCP tóm tắt khác (có thể mới hơn):** `docs/GCP_INFRA_INVENTORY_SUMMARY.md` — khi mâu thuẫn IP/zone, xác minh trên cloud rồi cập nhật đúng file.

---

## Phần A — GCP resource inventory (2026-04-06)

**Status:** Live measurements via gcloud CLI

### 1. Compute Engine Instances (VMs)

| Instance Name | Zone | Machine Type | CPU/RAM | External IP | Status |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **craveva-ai** | asia-southeast1-a | custom-8-16384 | 8 vCPU / 16 GB | 136.110.35.154 | ✅ RUNNING |
| **craveva-hub-server** | asia-southeast1-a | e2-standard-4 | 4 vCPU / 16 GB | 34.126.124.196 | ✅ RUNNING |
| **craveva-staging** | asia-southeast1-b | e2-standard-2 | 2 vCPU / 8 GB | 34.21.162.115 | ✅ RUNNING |

### 2. Cloud SQL Instances (Databases)

| Instance Name | Version | Tier | Primary IP | Status | Role |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **craveva-hub-server** | MySQL 8.0.41 | db-g1-small | 35.240.193.168 | ✅ RUNNABLE | Production DB |
| **craveva-staging-db** | MySQL 8.0 | db-g1-small | 136.110.52.19 | ✅ RUNNABLE | Staging DB |
| **craveva-ai-db** | MySQL 8.0 | db-g1-small | 34.158.38.112 | ✅ RUNNABLE | AI Module DB |
| **craveva-ai-pgvector** | PostgreSQL 15 | db-custom-1-3840 | 136.110.25.28 | ✅ RUNNABLE | Vector DB for AI |
| **craveva-deerpos-db** | MySQL 8.0 | db-g1-small | 34.124.130.134 | ✅ RUNNABLE | POS DB |
| **craveva-whatsapp-db**| MySQL 8.0 | db-g1-small | 34.143.225.95 | 🟥 STOPPED | WhatsApp Module |

### 3. Network & Connectivity

- **Region:** `asia-southeast1` (Singapore)
- **Primary Zones:** `a` and `b`
- **Database Access:** Managed via Cloud SQL Auth Proxy and Authorized Networks (Firewall).

### 4. Maintenance Notes

- **Hub Server:** Upgraded to 16GB RAM to support large ERP data processing.
- **Staging Server:** Upgraded to 8GB RAM for development and testing parity.
- **Cost Optimization:** `craveva-whatsapp-db` is currently stopped to save resources.

---

## Phần B — Cloud SQL hub & staging: cấu hình + authorized networks (2026-03-31)

**Project:** `craveva-org-55934-project`

### 1) `craveva-hub-server` (Cloud SQL)

- **Engine:** `MYSQL_8_0_41`
- **Region:** `asia-southeast1`
- **State:** `RUNNABLE`
- **Public IP:** `35.240.193.168`
- **Private IP:** `10.249.0.4`
- **Public access enabled (`ipv4Enabled`):** `true`
- **Require SSL (`requireSsl`):** `false`
- **Automated backup enabled:** `true`

#### Authorized networks

- `183.81.86.0`
- `34.126.124.196/32`
- `116.108.126.47/32`
- `35.198.237.131/32`
- `35.240.158.191/32`
- `136.110.35.154/32`
- `35.240.153.233/32`

### 2) `craveva-staging-db` (Cloud SQL)

- **Engine:** `MYSQL_8_0`
- **Region:** `asia-southeast1`
- **State:** `RUNNABLE`
- **Public IP:** `136.110.52.19`
- **Private IP:** `10.249.0.12`
- **Public access enabled (`ipv4Enabled`):** `true`
- **Require SSL (`requireSsl`):** `false`
- **Automated backup enabled:** `false`

#### Authorized networks

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
