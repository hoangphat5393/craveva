# GCP Infrastructure Inventory Summary

**Generated at:** 2026-03-31  
**Updated at:** 2026-05-14 (Cloud SQL staging backup policy documented)  
**Project:** `craveva-org-55934-project`  
**Scope:** Active `gcloud` account/project context on this machine

**Sao lưu Cloud SQL staging (`craveva-staging-db`):** lịch backup hằng ngày, giữ 7 bản, xoay vòng và PITR 7 ngày — xem [`STAGING_CLOUD_SQL_BACKUP_POLICY_VI.md`](STAGING_CLOUD_SQL_BACKUP_POLICY_VI.md).

---

## Totals

- **Compute Engine VM instances:** `3`
- **Cloud SQL instances:** `6`

---

## Compute Engine VMs

| Name                 | Zone                | Status    | Machine Type        | Internal IP   | External IP      |
| -------------------- | ------------------- | --------- | ------------------- | ------------- | ---------------- |
| `craveva-ai`         | `asia-southeast1-a` | `RUNNING` | `e2-custom-8-16384` | `10.148.0.7`  | `136.110.35.154` |
| `craveva-hub-server` | `asia-southeast1-a` | `RUNNING` | `e2-highcpu-4`      | `10.1.0.5`    | `34.126.124.196` |
| `craveva-staging`    | `asia-southeast1-a` | `RUNNING` | `n2-standard-2`     | `10.148.0.16` | `35.240.198.61`  |

---

## Cloud SQL Instances

| Name                  | Engine         | Region            | Status     | IP Addresses                     |
| --------------------- | -------------- | ----------------- | ---------- | -------------------------------- |
| `craveva-whatsapp-db` | `MYSQL_8_0`    | `asia-southeast1` | `STOPPED`  | `34.143.225.95`, `10.249.0.8`    |
| `craveva-ai-db`       | `MYSQL_8_0`    | `asia-southeast1` | `RUNNABLE` | `34.158.38.112`, `10.249.0.6`    |
| `craveva-hub-server`  | `MYSQL_8_0_41` | `asia-southeast1` | `RUNNABLE` | `35.240.193.168`, `10.249.0.4`   |
| `craveva-deerpos-db`  | `MYSQL_8_0`    | `asia-southeast1` | `RUNNABLE` | `34.124.130.134`, `10.249.0.10`  |
| `craveva-ai-pgvector` | `POSTGRES_15`  | `asia-southeast1` | `RUNNABLE` | `136.110.25.28`, `34.126.81.138` |
| `craveva-staging-db`  | `MYSQL_8_0`    | `asia-southeast1` | `RUNNABLE` | `136.110.52.19`, `10.249.0.12`   |

---

## Commands Used

```bash
gcloud config get-value project
gcloud compute instances list
gcloud sql instances list
```
