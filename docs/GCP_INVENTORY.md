# GCP Infrastructure Inventory Summary

**Generated at:** 2026-03-31  
**Updated at:** 2026-05-14 (Cloud SQL staging backup policy documented)  
**Project:** `craveva-org-55934-project`  
**Scope:** Active `gcloud` account/project context on this machine

**Sao lưu Cloud SQL staging (`craveva-staging-db`):** lịch backup hằng ngày, giữ 7 bản, xoay vòng và PITR 7 ngày — xem [`CLOUD_SQL_BACKUP.md`](CLOUD_SQL_BACKUP.md).

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

## Firewall / Cloud SQL allowlist

Nguồn gộp từ tài liệu firewall cũ, cập nhật gần nhất 2026-05-05.

### `craveva-hub-server`

- **Zone:** `asia-southeast1-a`
- **Network:** `craveva-vpc` / subnet `craveva-subnet-singapore`
- **Private IP:** `10.1.0.5`
- **Public IP:** `34.126.124.196`
- **Network tags:** `http-server`, `https-server`, `iap-ssh`, `panel`, `ssh-server`
- **UFW / firewalld trong VM:** inactive tại thời điểm audit.

### `craveva-staging-db`

- **Public IP:** `136.110.52.19`
- **Private IP:** `10.249.0.12`
- **IPv4 enabled:** `true`
- **Require SSL:** `false`
- **Authorized Networks quan trọng:**
    - `35.240.198.61/32` — staging VM current IP
    - `35.240.234.226/32` — staging VM old IP
    - `34.126.124.196/32` — hub
    - `136.110.35.154/32` — ai

**Lưu ý bảo mật:** không có `0.0.0.0/0` trong allowlist. Vì public IP đang bật và `requireSsl=false`, khi hardening nên cân nhắc bật SSL requirement và tiếp tục giữ allowlist IP chặt.

## Cloud SQL general log

Nguồn gộp từ tài liệu `general_log` cũ, bật cho staging ngày 2026-03-31:

- **Instance:** `craveva-staging-db` (`136.110.52.19`)
- **Flags:** `general_log=on`, `log_output=FILE`, giữ `cloudsql_iam_authentication=on`
- **Quyền xem log:** cần `roles/logging.viewer` hoặc quyền project tương đương.
- **Tác động:** tăng I/O và dung lượng log; chỉ nên bật trên staging khi điều tra.

Query Logs Explorer cơ bản:

```text
resource.type="cloudsql_database"
resource.labels.database_id="craveva-org-55934-project:craveva-staging-db"
```

Tìm câu lệnh nguy hiểm như `DROP`:

```text
resource.type="cloudsql_database"
resource.labels.database_id="craveva-org-55934-project:craveva-staging-db"
textPayload=~"DROP"
```

Tắt general log sau khi điều tra:

```bash
gcloud sql instances patch craveva-staging-db --project=craveva-org-55934-project --quiet \
  "--database-flags=cloudsql_iam_authentication=on,general_log=off,log_output=FILE"
```

## AI.Craveva -> MySQL connectivity checklist

Checklist này dùng khi AI.Craveva kết nối tới `136.110.52.19:3306` bị timeout hoặc kết nối chập chờn:

1. Xác nhận chính xác rule đã đổi: firewall, security group, VPN, iptables/firewalld, source CIDR, destination, port, protocol, direction.
2. Xác nhận egress IP có cố định không. Nếu chỉ allowlist một IP, kiểm tra IP hiện tại vẫn đúng.
3. Xác nhận Cloud SQL `craveva-staging-db` allowlist TCP `3306` từ đúng source IP/CIDR.
4. Kiểm tra rule có bị IaC, cron, policy hoặc admin khác ghi đè không.
5. Nếu đi qua VPN/private link, kiểm tra tunnel/route còn ổn định.
6. Kiểm tra MySQL vẫn listen `3306` và bind address không đổi.
7. Lấy bằng chứng từ đúng network path: connect OK/timeout, firewall log SYN, hoặc Cloud SQL log theo timestamp.

---

## Commands Used

```bash
gcloud config get-value project
gcloud compute instances list
gcloud sql instances list
gcloud compute firewall-rules list --project=craveva-org-55934-project
gcloud sql instances describe craveva-staging-db --project=craveva-org-55934-project
```
