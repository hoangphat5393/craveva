# Cloud SQL staging — `general_log` (MySQL 8)

**Instance:** `craveva-staging-db` (`136.110.52.19`)  
**Enabled (2026-03-31):** `general_log=on`, `log_output=FILE` (logs integrated with **Cloud Logging**). Existing flag preserved: `cloudsql_iam_authentication=on`.

## Quyền xem log (quan trọng)

- **`gcloud logging read`** cần ít nhất **`roles/logging.viewer`** (hoặc Owner/Editor phù hợp) trên project **`craveva-org-55934-project`**.
- Nếu báo **`Permission denied for all log views`**: đăng nhập Console bằng tài khoản **admin project**, hoặc nhờ admin gán **`Logging Viewer`** cho tài khoản bạn dùng với `gcloud`.

## View logs — Logs Explorer (khuyến nghị)

1. Console: **https://console.cloud.google.com/logs/query** — chọn project **`craveva-org-55934-project`**.
2. Dán **một trong các query** dưới đây (tab **Query**), chỉnh **thời gian** (ví dụ 7 ngày / custom range).

### A) Mọi log gắn instance Cloud SQL staging

```
resource.type="cloudsql_database"
resource.labels.database_id="craveva-org-55934-project:craveva-staging-db"
```

### B) Tìm dòng có chữ `DROP` (DROP DATABASE / DROP TABLE, …)

```
resource.type="cloudsql_database"
resource.labels.database_id="craveva-org-55934-project:craveva-staging-db"
textPayload=~"DROP"
```

### C) Tìm chữ `Query` hoặc `Connect` (tuỳ format general log)

```
resource.type="cloudsql_database"
resource.labels.database_id="craveva-org-55934-project:craveva-staging-db"
(textPayload=~"Query" OR textPayload=~"Connect" OR textPayload=~"Access denied")
```

### D) Log stream tên `mysql-general` (khi bật `general_log` + `log_output=FILE`)

Nếu (A) quá rộng, thử hẹp theo log name (Google có thể dùng biến thể tên):

```
log_id("cloudsql.googleapis.com/mysql-general")
resource.labels.database_id="craveva-org-55934-project:craveva-staging-db"
```

Nếu không ra kết quả, bỏ dòng `log_id(...)` và chỉ dùng query (A), rồi trong bảng log xem cột **Log name** để chỉnh filter cho đúng instance.

### Đọc kết quả

- **general_log** thường có dòng **Connect** (user/host) và **Query** (câu SQL). **Host** đôi khi là **hostname** hoặc chuỗi do client gửi, không luôn là IP — cần **ghép thời gian** với **VPC Flow Logs** (port **3306**) nếu cần IP nguồn chắc chắn.

## View logs — CLI (khi đã có quyền)

```bash
gcloud logging read \
  'resource.type="cloudsql_database" AND resource.labels.database_id="craveva-org-55934-project:craveva-staging-db"' \
  --project=craveva-org-55934-project --limit=50 --format="table(timestamp,textPayload)"
```

Thêm điều kiện tìm `DROP`:

```bash
gcloud logging read \
  'resource.type="cloudsql_database" AND resource.labels.database_id="craveva-org-55934-project:craveva-staging-db" AND textPayload=~"DROP"' \
  --project=craveva-org-55934-project --limit=30
```

## Test database

- **`test_drop_audit`** — created for experiments. **Drop only in a controlled test** (avoid production data).

## Impact

- **I/O + storage** for log volume; **staging** only — monitor disk. Turn off when finished investigating.

## Turn off general log

```bash
gcloud sql instances patch craveva-staging-db --project=craveva-org-55934-project --quiet ^
  "--database-flags=cloudsql_iam_authentication=on,general_log=off,log_output=FILE"
```

(PowerShell: use `'--database-flags=...'` quoting as needed.)

## Limits

- **general_log** does **not** guarantee a single field “attacker IP” for every `DROP`; you may need **VPC Flow Logs** for IP + logs for statement. **general_log** helps show **what** ran and often **which MySQL user/host string** was used.
