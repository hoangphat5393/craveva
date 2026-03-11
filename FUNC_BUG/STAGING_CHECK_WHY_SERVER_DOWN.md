# Kiểm tra nguyên nhân server (VM) staging down – dùng GCP + trong VM

Bạn đã có full quyền GCP. Có thể kiểm tra từ **Google Cloud** (Console hoặc gcloud) và từ **trong VM** (SSH).

---

## A. Từ Google Cloud (không cần SSH)

### A1. Console – Xem lịch sử VM (stop/start)

1. Vào [Google Cloud Console](https://console.cloud.google.com) → **Compute Engine** → **VM instances**.
2. Chọn project **craveva-org-55934-project**.
3. Click tên VM **craveva-staging**.
4. Tab **Details** / **VM instance details**:
   - **Last stop time** / **Last start time** (nếu có) → biết lần tắt/bật gần nhất.
5. Tab **Logs** (hoặc **Serial port output** nếu có):
   - Xem log gần thời điểm down (shutdown, OOM, panic).

### A2. Console – Cloud Logging (nguyên nhân stop/restart)

1. Vào **Logging** → **Logs Explorer** (hoặc **Logging** trong menu).
2. Chọn project **craveva-org-55934-project**.
3. Trong ô query, thử:

```
resource.type="gce_instance"
resource.labels.instance_id="<INSTANCE_ID>"
```

(Lấy **Instance ID** từ trang chi tiết VM craveva-staging – dãy số.)

Hoặc đơn giản filter theo tên:

```
resource.type="gce_instance"
jsonPayload.message=~"stop|shutdown|terminate|Stopping"
```

4. Chọn **Time range** (vd. Last 7 days) để xem event stop/restart.
5. Xem **Admin Activity** / **Data Access** (nếu bật): ai đã stop/start instance (user, IP, thời gian).

### A3. Console – Activity / Audit log (ai stop VM)

1. Vào **IAM & Admin** → **Activity** (hoặc **Logging** → **Logs Explorer**).
2. Filter:
   - **Resource type:** GCE Instance  
   - **Resource name:** craveva-staging (hoặc zone `asia-southeast1-a`)
3. Tìm event **Stop instance** / **Start instance** → xem **Principal** (user/account) và **Time**.

### A4. gcloud – Lịch sử thao tác instance

Chạy trên máy bạn (PowerShell/CMD, đã `gcloud auth login`):

```bash
gcloud config set project craveva-org-55934-project
```

**Xem thông tin VM (trạng thái, lần start):**

```bash
gcloud compute instances describe craveva-staging --zone=asia-southeast1-a --format="yaml(status,lastStartTimestamp,lastStopTimestamp)"
```

**Xem operations gần đây (ai stop/start):**

```bash
gcloud compute operations list --filter="targetLink:craveva-staging" --zones=asia-southeast1-a --limit=20
```

**Xem log từ serial port (nếu bật):**

```bash
gcloud compute instances get-serial-port-output craveva-staging --zone=asia-southeast1-a
```

(Khi VM đang chạy mới xem được; có thể thấy kernel/panic/OOM trước lúc tắt.)

---

## B. Từ trong VM (sau khi SSH vào)

SSH vào staging (Console “SSH” hoặc `gcloud compute ssh craveva-staging --zone=asia-southeast1-a --project=craveva-org-55934-project`), rồi chạy các lệnh trong **FUNC_BUG/STAGING_INCIDENT_CHECK_COMMANDS.md**:

- **Uptime / last boot:** `uptime`, `who -b`, `last reboot`
- **Log trước lúc tắt:** `sudo journalctl -b -1`, grep shutdown/oom
- **Nginx / PHP-FPM:** `systemctl status nginx php8.2-fpm`, journalctl -u nginx, error.log

→ Giúp phân biệt: **VM bị stop từ GCP** (admin/script) hay **VM crash/reboot** (OOM, kernel panic).

---

## Tóm tắt nhanh

| Muốn biết | Làm gì |
|-----------|--------|
| Ai stop VM, lúc nào | GCP Console → **Activity** / **Logging** (filter Stop instance); hoặc `gcloud compute operations list` |
| VM crash hay bị tắt có chủ đích | Trong VM: `last reboot`, `journalctl -b -1` (OOM, shutdown) |
| Nginx/PHP có chết trước đó không | Trong VM: `journalctl -u nginx`, `journalctl -u php8.2-fpm`, `/var/log/nginx/error.log` |

Chi tiết lệnh chạy **trong VM** xem **STAGING_INCIDENT_CHECK_COMMANDS.md**.
