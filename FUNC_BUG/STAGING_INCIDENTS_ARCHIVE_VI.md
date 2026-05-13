# FUNC_BUG — Staging incidents & ops (archive)

> **Gộp (2026-05-12):** tám file `STAGING_*.md` cũ. **Canonical vận hành:** `docs/SERVER_RUNBOOK_VI.md`, `docs/STAGING_OPERATIONS.md`. File này giữ chi tiết incident / lệnh từng case.

---

## STAGING_INCIDENT_CHECK_COMMANDS

Chạy từng khối lệnh sau **trên server staging** (sau khi đã SSH vào). Mục đích: xác định server bị restart do admin hay do thao tác trước đó (Nginx reload, v.v.).

---

## 1. Thời điểm server khởi động lại (nếu có)

```bash
uptime
```

→ Cho biết server đang chạy từ lúc nào (vd. "up 2 days" = không reboot; "up 10 min" = vừa reboot).

```bash
who -b
```

→ In thời điểm **last system boot** (lần boot gần nhất).

```bash
last reboot | head -10
```

→ Danh sách các lần reboot gần đây (thời gian, có thể có user nếu ghi nhận).

---

## 2. Lịch sử boot (journalctl)

```bash
sudo journalctl --list-boots
```

→ Liệt kê các lần boot (index, thời điểm bắt đầu). Nếu có 2 dòng trở lên với boot gần đây thì server đã reboot.

```bash
sudo journalctl -b -1 -n 50 --no-pager
```

→ 50 dòng log **của lần boot trước** (trước khi tắt/reboot). Xem có dòng "Stopped", "shutdown", "reboot", "Out of memory" (OOM) không.

---

## 3. Ai/cái gì gây shutdown/reboot

```bash
sudo journalctl -b 0 | grep -iE "shutdown|reboot|poweroff|initiated|Stopping" | tail -20
```

→ Trong boot hiện tại, tìm log liên quan shutdown/reboot (có thể thấy "Stopping..." các service trước khi tắt).

```bash
sudo journalctl --list-boots -n 3
sudo journalctl -b -1 --no-pager | grep -iE "shutdown|reboot|poweroff|Stopping|Received" | tail -30
```

→ Log của **boot trước đó** (trước khi server “sập”). Thường sẽ thấy:

- `Stopping User Manager for UID 1000` / `Stopping Session...` → có user đăng nhập và thoát/reboot.
- `Received SIGTERM` / `Initiating system shutdown` → hệ thống đang tắt theo lệnh.
- `Out of memory` / `oom-killer` → có thể do hết RAM (không phải do Nginx config).

---

## 4. Nginx – có crash hay không

```bash
sudo systemctl status nginx
```

→ Trạng thái hiện tại (active/inactive), có "active (running)" là đang chạy.

```bash
sudo journalctl -u nginx -n 50 --no-pager
```

→ 50 dòng log gần nhất của Nginx. Xem có "failed", "error", "signal" không.

```bash
sudo tail -100 /var/log/nginx/error.log
```

→ Lỗi Nginx ghi ra file. Nếu có "emerg", "alert" có thể liên quan cấu hình hoặc crash.

---

## 5. PHP-FPM (web)

```bash
sudo systemctl status php8.2-fpm
sudo journalctl -u php8.2-fpm -n 30 --no-pager
```

→ Xem PHP-FPM có bị dừng hay crash không.

---

## 6. Kết luận gợi ý (sau khi chạy xong)

| Kết quả kiểm tra                                                                         | Ý nghĩa có thể                                                                                 |
| ---------------------------------------------------------------------------------------- | ---------------------------------------------------------------------------------------------- |
| `uptime` chỉ vài phút / vài giờ, `last reboot` có thời điểm trùng lúc bạn không vào được | Server **đã reboot** trong khoảng thời gian sự cố.                                             |
| Trong `journalctl -b -1` có "Stopping Session", "shutdown", "Initiating"                 | Tắt máy **có chủ đích** (admin hoặc script), không phải treo đơn thuần.                        |
| Có "Out of memory" / "oom-killer"                                                        | Khả năng **hết RAM** (process nào đó ăn nhiều RAM), không phải do chỉnh Nginx.                 |
| Nginx log có "failed" / "signal" ngay trước lúc sự cố                                    | Có thể Nginx **crash** (cần đối chiếu thời gian với lúc bạn reload).                           |
| Chỉ có "reload" trong log Nginx, không có failed                                         | **Reload Nginx** thường không làm server shutdown; cần xem thêm log hệ thống (boot, shutdown). |

---

**Lưu ý:** Toàn bộ lệnh trên **chỉ đọc log và trạng thái**, không thay đổi cấu hình hay restart service. Sau khi chạy, bạn có thể copy kết quả (hoặc phần liên quan) để phân tích tiếp.

---

## STAGING_ACCESS_VIA_GOOGLE_CLOUD

> **Cập nhật 2026-05-13:** Lỗi `Permission denied (publickey)` với `User hoangphat5393`, metadata `ssh-keys`, `Admin` vs owner git, `.git/FETCH_HEAD`, và lỗi bash trong `upload_staging.ps1` — xem **`FUNC_BUG/STAGING_SSH_GCLOUD_METADATA_AND_DEPLOY_SCRIPT_VI.md`** (canonical ngắn). Mục dưới đây giữ nguyên làm archive (Console, `gcloud compute ssh`, DNS).

Khi `ssh user@staging.craveva.com` hoặc `ssh craveva-staging` bị timeout/refused, dùng một trong các cách dưới (server staging có IP 35.240.198.61, thường là VM Google Compute Engine).

---

## Cách 1: SSH qua trình duyệt (Google Cloud Console) – Nên dùng trước

SSH qua Console **không cần mở port 22** từ mạng của bạn, không phụ thuộc SSH từ máy tính.

1. Đăng nhập: https://console.cloud.google.com/
2. Chọn đúng **Project** (project chứa VM staging).
3. Vào **Compute Engine** → **VM instances** (hoặc menu ☰ → Compute Engine → VM instances).
4. Tìm VM có IP **35.240.198.61** (hoặc tên bạn đặt cho staging).
5. Ở cột **Connect**, bấm nút **SSH** (hoặc mũi tên ▼ bên cạnh → **Open in browser window**).
6. Cửa sổ terminal mở trong trình duyệt → bạn đã vào shell trên VM (user thường là tên user Google hoặc user đã cấu hình trên VM).

Sau khi vào, chạy các lệnh kiểm tra Nginx/PHP (xem cuối file).

---

## Cách 2: gcloud compute ssh (từ máy bạn)

Cần cài **Google Cloud SDK** (gcloud) và đăng nhập. Dùng khi bạn đã biết **project**, **zone**, **tên instance**.

### Bước 1: Cài và đăng nhập (nếu chưa có)

- Tải: https://cloud.google.com/sdk/docs/install
- Đăng nhập: `gcloud auth login`
- Đặt project: `gcloud config set project PROJECT_ID`

### Bước 2: Tìm tên instance và zone

- Trong Cloud Console: **Compute Engine** → **VM instances** → xem cột **Name** và **Zone**.
- Hoặc chạy (thay `PROJECT_ID` bằng project của bạn):
    ```bash
    gcloud compute instances list --project=PROJECT_ID
    ```
    Xem cột `NAME` và `ZONE` của VM có IP 35.240.198.61.

### Bước 3: SSH vào VM

Dùng **gcloud compute ssh** (không dùng `ssh craveva-staging` trực tiếp) vì VM mới chưa có public key của bạn trong metadata; gcloud sẽ tự thêm key và kết nối:

```powershell
gcloud config set project craveva-org-55934-project
gcloud compute ssh craveva-staging --zone=asia-southeast1-a --project=craveva-org-55934-project
```

Lần đầu có thể hỏi "Store key in cache? (y/n)" → gõ **y** rồi Enter.

**Để lần sau gõ `ssh craveva-staging` được** (qua gcloud), chạy một lần:

```powershell
gcloud compute config-ssh
```

Sau đó trong file `~/.ssh/config` sẽ có entry cho các VM GCP; lệnh `ssh craveva-staging` sẽ dùng gcloud làm proxy và vào được.

### Gõ ngắn `ssh craveva-staging` (Windows / OpenSSH)

1. Chạy một lần (đúng project):

    ```powershell
    gcloud compute config-ssh --project=craveva-org-55934-project
    ```

2. Mở `%USERPROFILE%\.ssh\config`, tìm block do gcloud tạo cho instance staging (dòng `Host` dạng `craveva-staging.asia-southeast1-a.craveva-org-55934-project` — có `ProxyCommand` / `IdentityFile` trỏ tới `google_compute_engine`).

3. **Cách ổn định:** thêm **cùng tên ngắn** vào dòng `Host` đầu block đó (giữ nguyên toàn bộ dòng còn lại), ví dụ:

    ```sshconfig
    Host craveva-staging craveva-staging.asia-southeast1-a.craveva-org-55934-project
      ...
    ```

    Sau đó `ssh craveva-staging` dùng **đúng** proxy + key như gcloud (không bị `Permission denied` như khi `ssh` thẳng tới IP).

4. **User đăng nhập:** trên server chạy `whoami` (trong phiên gcloud đang mở). Nếu ra `Admin` mà vẫn lỗi key, thêm vào block đó: `User Admin` (một số máy dùng user Windows / tài khoản OS khác `hoangphat5393`).

**Không nên** chỉ sửa `HostName` trong `Host craveva-staging` thành IP public (`35.240...`) nếu không kèm **đúng** `IdentityFile` (thường `%USERPROFILE%\.ssh\google_compute_engine`) và **đúng** `User` — vì vậy `ssh craveva-staging` trước đó báo `Permission denied (publickey)`.

Nếu VM dùng user khác (không phải user Google):

```bash
gcloud compute ssh TEN_INSTANCE --zone=ZONE --project=PROJECT_ID --ssh-flag="-l USERNAME"
```

---

## Cách 3: Serial console (khi SSH hoàn toàn không dùng được)

1. Console Cloud → **Compute Engine** → **VM instances**.
2. Chọn VM → **Edit** (hoặc click tên VM).
3. Trên trang chi tiết VM, bên trái hoặc trên: **Serial console** / **Connect to serial console**.
4. Mở serial console → có shell (đôi khi cần nhấn Enter để ra prompt). Serial console không cần network, chỉ cần VM đang chạy.

---

## Trỏ domain staging.craveva.com về IP mới (35.240.198.61)

Sau khi VM staging chuyển zone, **External IP** đổi thành **35.240.198.61**. Cần sửa bản ghi DNS cho `staging.craveva.com` trỏ về IP này.

**DNS craveva.com hiện dùng:** `dns1.registrar-servers.com` / `dns2.registrar-servers.com` → thường là **Namecheap**.

---

### Hướng dẫn chi tiết – Namecheap (hoặc trang có “Advanced DNS”)

1. **Đăng nhập**
    - Vào https://www.namecheap.com/ (hoặc trang nơi bạn mua domain **craveva.com**).
    - Đăng nhập tài khoản.

2. **Mở DNS của domain**
    - Vào **Domain List** → chọn **craveva.com**.
    - Bấm **Manage** (hoặc **Manage Domain**).
    - Chọn tab **Advanced DNS**.

3. **Tìm bản ghi subdomain staging**
    - Trong bảng **HOST RECORDS** (hoặc **DNS Records**), tìm dòng:
        - **Type:** A
        - **Host:** `staging` (hoặc `staging.craveva.com` / `@` nếu chỉ có 1 record cho staging).
    - Nếu có nhiều bản ghi A, chọn đúng dòng cho **staging**.

4. **Đổi IP**
    - Ở cột **Value** (hoặc **Points to**, **Answer**): đổi IP hiện tại (35.240.158.191 hoặc bất kỳ) thành:
        - **35.240.198.61**
    - Bấm **Save** / biểu tượng tick / **Save All Changes**.

5. **Đợi cập nhật**
    - Thường 5–30 phút. Có thể kiểm tra bằng:
        ```powershell
        nslookup staging.craveva.com
        ```
        Khi thấy `Address: 35.240.198.61` là đã trỏ đúng.

**Nếu chưa có bản ghi A cho staging:**

- Bấm **Add New Record**.
- **Type:** A
- **Host:** `staging`
- **Value:** `35.240.198.61`
- **TTL:** Automatic (hoặc 300).
- **Save**.

---

### Nếu DNS nằm trên Google Cloud (Cloud DNS)

1. Vào https://console.cloud.google.com/ → chọn project (ví dụ **craveva-org-55934-project**).
2. **Network services** → **Cloud DNS** (hoặc tìm "DNS" trong menu).
3. Chọn zone của domain **craveva.com**.
4. Tìm bản ghi **A** có tên `staging` (hoặc `staging.craveva` tùy cấu hình).
5. **Edit** → đổi giá trị (IPv4) thành **35.240.198.61** → **Save**.

---

### Nếu DNS ở nhà cung cấp khác (Cloudflare, GoDaddy, …)

1. Đăng nhập vào trang quản lý DNS của domain **craveva.com**.
2. Tìm bản ghi **A** cho subdomain **staging** (Host = `staging`).
3. Đổi **Value / Points to / Answer** thành **35.240.198.61**.
4. Lưu. Đợi vài phút rồi kiểm tra bằng `nslookup staging.craveva.com`.

Sau khi đổi xong, `staging.craveva.com` sẽ trỏ về VM staging mới (zone `asia-southeast1-a` sau migrate 2026-05).

---

## Đã trỏ domain (staging.craveva.com) nhưng vẫn không vào được (timeout)

DNS đúng (35.240.198.61) nhưng trình duyệt timeout thường do **trên VM**: Nginx hoặc PHP-FPM chưa chạy sau khi VM mới boot.

**Cách xử lý:** SSH vào VM (Console hoặc `ssh craveva-staging` / `.\ssh_staging.ps1`), chạy:

```bash
# Khởi động lại Nginx và PHP-FPM
sudo systemctl start nginx
sudo systemctl start php8.2-fpm

# Cho chạy lúc boot
sudo systemctl enable nginx php8.2-fpm

# Kiểm tra đã listen 80/443 chưa
sudo ss -tlnp | grep -E ':80|:443'
```

Nếu vẫn lỗi: xem `sudo tail -50 /var/log/nginx/error.log` và `sudo systemctl status nginx php8.2-fpm`.

**GCP Firewall:** Rule `default-allow-http` / `default-allow-https` chỉ áp dụng cho VM có **tag** `http-server` và `https-server`. VM mới tạo (sau khi migrate) có thể chưa có tag → bị chặn 80/443. Cách sửa (chạy trên máy có gcloud):

```powershell
gcloud compute instances add-tags craveva-staging --zone=asia-southeast1-a --project=craveva-org-55934-project --tags=http-server
gcloud compute instances add-tags craveva-staging --zone=asia-southeast1-a --project=craveva-org-55934-project --tags=https-server
```

Sau khi thêm tag, đợi vài giây rồi thử lại https://staging.craveva.com/

---

## 504 Gateway Time-out – Laravel không trả lời kịp (thường do Database)

Nginx báo **504** khi PHP-FPM / Laravel không trả về response trong thời gian quy định. Trên staging, Laravel kết nối DB tại **136.110.52.19**. VM staging mới có IP **35.240.198.61** (khác IP cũ 35.240.158.191).

**Nguyên nhân thường gặp:** MySQL/Cloud SQL chỉ cho phép kết nối từ một số IP; IP mới của VM chưa được thêm → kết nối DB timeout → Laravel treo → 504.

**Cách xử lý:**

### Thêm IP staging vào Cloud SQL (GCP) – từng bước

1. Mở trình duyệt, vào: **https://console.cloud.google.com/**
2. Đăng nhập bằng tài khoản có quyền **Cloud SQL** (owner hoặc admin project).
3. Ở thanh tìm kiếm trên cùng (hoặc menu ☰), gõ **SQL** → chọn **SQL** (hoặc **Databases** → **SQL**).
4. Trong danh sách **Cloud SQL instances**, chọn instance có địa chỉ **136.110.52.19** (bấm vào **tên** instance).
5. Trên trang chi tiết instance, mở tab **Connections** (hoặc **Connectivity** / **Connections**).
6. Trong phần **Authorized networks** (Mạng được ủy quyền), bấm **Add network** (hoặc **+ ADD NETWORK**).
7. Điền:
    - **Name:** `staging-vm` (hoặc tên bất kỳ)
    - **Network:** `35.240.198.61`
8. Bấm **Done** (hoặc **Add**) rồi **Save** để lưu thay đổi.
9. Đợi vài phút cho cấu hình áp dụng, sau đó mở lại **https://staging.craveva.com/**.

**Lệnh gcloud** (nếu bạn có quyền và biết tên instance, ví dụ `craveva-db`):

```bash
gcloud sql instances patch TEN_INSTANCE --authorized-networks=35.240.198.61/32 --project=craveva-org-55934-project
```

(Thay `TEN_INSTANCE` bằng tên instance Cloud SQL thực tế.)

### Nếu DB là VM/MySQL trên server 136.110.52.19 (không phải Cloud SQL)

Trên server đó: - Mở firewall cho port **3306** từ IP **35.240.198.61**, hoặc - Trong MySQL: `CREATE USER ...@'35.240.198.61'` hoặc cấp quyền cho user hiện tại từ host `35.240.198.61`; `FLUSH PRIVILEGES;` 3. Sau khi cho phép IP mới, thử lại https://staging.craveva.com/

Kiểm tra nhanh từ trong VM staging: `timeout 3 bash -c 'echo >/dev/tcp/136.110.52.19/3306'` — nếu không kết nối được thì cần mở/whitelist IP như trên.

---

## Sau khi vào được shell – kiểm tra Nginx/PHP

Chạy lần lượt:

```bash
# Trạng thái Nginx và PHP-FPM
sudo systemctl status nginx
sudo systemctl status php8.2-fpm

# Nếu inactive: khởi động lại
sudo systemctl start nginx
sudo systemctl start php8.2-fpm

# Xem lỗi Nginx gần nhất
sudo tail -50 /var/log/nginx/error.log
```

Nếu muốn **hoàn tác** thay đổi cấu hình Nginx (bỏ client_max_body_size đã thêm):

```bash
sudo cp /etc/nginx/sites-available/staging.bak.413 /etc/nginx/sites-available/staging
sudo nginx -t && sudo systemctl reload nginx
```

---

## Tóm tắt

| Cách                               | Khi nào dùng                                           |
| ---------------------------------- | ------------------------------------------------------ |
| **SSH trong trình duyệt (Cách 1)** | Ưu tiên: không cần port 22, không cần gcloud trên máy. |
| **gcloud compute ssh (Cách 2)**    | Khi đã cài gcloud và biết project/zone/instance.       |
| **Serial console (Cách 3)**        | Khi SSH và web đều không vào được, cần debug từ xa.    |

---

## Nhật ký xử lý thực tế (2026-05-05)

### Bối cảnh

- VM `craveva-staging` chuyển từ `asia-southeast1-b` sang `asia-southeast1-a`.
- IP public mới: `35.240.198.61`.
- DNS `staging.craveva.com` đã trỏ về IP mới.

### Triệu chứng đã gặp

- `gcloud compute instances start` báo thiếu resource ở zone cũ (`ZONE_RESOURCE_POOL_EXHAUSTED`).
- `ssh craveva-staging` bị `Permission denied (publickey)` do user/key không khớp.
- Có thời điểm SSH bị treo với lỗi `Connection timed out during banner exchange`.
- Web `https://staging.craveva.com` timeout sau khi đổi VM/IP.

### Các bước đã xử lý

1. Xác nhận account và project:
    - `gcloud auth list`
    - `gcloud config set project craveva-org-55934-project`
2. Bật lại VM staging và xác nhận IP hiện tại:
    - `gcloud compute instances start craveva-staging --zone=asia-southeast1-a`
3. Chuẩn hóa SSH local (`~/.ssh/config`) cho `craveva-staging`:
    - `HostName 35.240.198.61`
    - `User Admin`
    - `IdentityFile` trỏ key đang dùng (`id_rsa_gcp` hoặc `google_compute_engine`)
    - `IdentitiesOnly yes`
4. Thêm public key vào metadata VM:
    - thêm `Admin:<public-key>` cho instance `craveva-staging`.
5. Cập nhật Cloud SQL allowlist cho IP staging mới:
    - thêm `35.240.198.61/32` vào `craveva-staging-db` (`authorized networks`).
6. Khi SSH bị treo (banner exchange), reset VM:
    - `gcloud compute instances reset craveva-staging --zone=asia-southeast1-a`
7. Test lại SSH:
    - `ssh craveva-staging "whoami && hostname"` trả về `Admin` và `craveva-staging`.

### Kết quả

- SSH alias `ssh craveva-staging` hoạt động lại.
- VM staging chạy ở zone `asia-southeast1-a`, IP `35.240.198.61`.
- Cloud SQL đã có whitelist IP mới của staging.

---

## STAGING_CHECK_WHY_SERVER_DOWN

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

SSH vào staging (Console “SSH” hoặc `gcloud compute ssh craveva-staging --zone=asia-southeast1-a --project=craveva-org-55934-project`), rồi chạy các lệnh trong mục **STAGING_INCIDENT_CHECK_COMMANDS** ở đầu file này:

- **Uptime / last boot:** `uptime`, `who -b`, `last reboot`
- **Log trước lúc tắt:** `sudo journalctl -b -1`, grep shutdown/oom
- **Nginx / PHP-FPM:** `systemctl status nginx php8.2-fpm`, journalctl -u nginx, error.log

→ Giúp phân biệt: **VM bị stop từ GCP** (admin/script) hay **VM crash/reboot** (OOM, kernel panic).

---

## Tóm tắt nhanh

| Muốn biết                        | Làm gì                                                                                                 |
| -------------------------------- | ------------------------------------------------------------------------------------------------------ |
| Ai stop VM, lúc nào              | GCP Console → **Activity** / **Logging** (filter Stop instance); hoặc `gcloud compute operations list` |
| VM crash hay bị tắt có chủ đích  | Trong VM: `last reboot`, `journalctl -b -1` (OOM, shutdown)                                            |
| Nginx/PHP có chết trước đó không | Trong VM: `journalctl -u nginx`, `journalctl -u php8.2-fpm`, `/var/log/nginx/error.log`                |

Chi tiết lệnh chạy **trong VM** xem mục **STAGING_INCIDENT_CHECK_COMMANDS** ở đầu file này.

---

## STAGING_DB_COPY_TO_LOCAL_MYSQL

Nếu không thể thêm IP **35.240.234.226** vào Cloud SQL **136.110.52.19**, có thể chạy MySQL ngay trên VM staging mới, copy dữ liệu từ DB cũ vào rồi trỏ Laravel sang `127.0.0.1`.

---

## Điều kiện

- Có **file dump** (mysqldump) của database staging từ DB tại 136.110.52.19.  
  Dump phải được tạo từ máy **có quyền kết nối** tới 136.110.52.19 (ví dụ: hub server, máy dev đã được whitelist, hoặc admin export giúp).

---

## Bước 1: Trên máy có kết nối tới DB 136.110.52.19 (hub / máy khác)

Lấy tên DB, user, password từ `.env` staging (DB_DATABASE, DB_USERNAME, DB_PASSWORD). Rồi chạy:

```bash
mysqldump -h 136.110.52.19 -u DB_USERNAME -p DB_DATABASE > staging_backup.sql
```

(Thay `DB_USERNAME`, `DB_DATABASE` bằng giá trị thật; nhập password khi hỏi.)

Nếu dùng SSL/Cloud SQL:

```bash
mysqldump -h 136.110.52.19 -u DB_USERNAME -p --ssl-mode=REQUIRED DB_DATABASE > staging_backup.sql
```

Copy file `staging_backup.sql` lên VM staging mới (scp, sftp, hoặc upload qua Console).

---

## Bước 2: Trên VM staging mới (SSH vào craveva-staging)

### 2.1 Cài MySQL (nếu chưa có)

```bash
sudo apt update
sudo apt install -y mysql-server
sudo systemctl start mysql
sudo systemctl enable mysql
```

### 2.2 Tạo user và database (khớp với .env staging)

```bash
sudo mysql -e "
  CREATE DATABASE IF NOT EXISTS craveva_staging CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
  CREATE USER IF NOT EXISTS 'staging_user'@'localhost' IDENTIFIED BY 'MAT_KHAU_LAY_TU_ENV';
  GRANT ALL ON craveva_staging.* TO 'staging_user'@'localhost';
  FLUSH PRIVILEGES;
"
```

(Thay `craveva_staging`, `staging_user`, `MAT_KHAU_LAY_TU_ENV` bằng DB_DATABASE, DB_USERNAME, DB_PASSWORD trong `.env` trên server.)

### 2.3 Import dump

```bash
cd /var/www/craveva-staging/current/craveva
# Giả sử file dump đã copy vào thư mục này hoặc /tmp
sudo mysql -u staging_user -p craveva_staging < staging_backup.sql
# hoặc: zcat staging_backup.sql.gz | sudo mysql -u staging_user -p craveva_staging
```

### 2.4 Sửa .env trỏ về MySQL local

```bash
cd /var/www/craveva-staging/current/craveva
sudo -u www-data nano .env
# hoặc: sudo sed -i 's/^DB_HOST=.*/DB_HOST=127.0.0.1/' .env
```

Đặt:

- `DB_HOST=127.0.0.1`
- Giữ nguyên `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` (đã dùng khi tạo user và import).

### 2.5 Clear cache Laravel

```bash
cd /var/www/craveva-staging/current/craveva
php artisan config:clear
php artisan cache:clear
sudo systemctl reload php8.2-fpm
```

Sau đó thử lại https://staging.craveva.com/

---

## Lưu ý

- **Backup định kỳ:** MySQL chạy local trên VM thì cần tự backup (cron mysqldump, hoặc snapshot disk).
- **Đồng bộ:** Dữ liệu từ lúc copy trở đi không tự đồng bộ lại với DB 136.110.52.19; staging chạy độc lập.
- **Lấy dump từ đâu:** Nếu không có máy nào kết nối được 136.110.52.19 thì cần nhờ admin export dump và gửi file, rồi làm từ Bước 2.

---

## STAGING_IMPORT_SERVER_SHUTDOWN

**Triệu chứng:** Khi upload file Excel rồi nhấn nút bắt đầu import (Start import / Process), server staging “tắt” hoặc không phản hồi, phải vào Google Cloud Console reset VM mới SSH lại được.

---

OOM - Out of Memory - Hết bộ nhớ

## 1. Nguyên nhân gốc (trong code)

Luồng hiện tại khi bạn nhấn **Start import** (request `import/process`):

1. PHP nhận request và gọi `importJobProcessChunked()` (trong `ImportExcel` trait).
2. Trong đó có **`Excel::import($importInstance, $filePath)`** → đọc **toàn bộ** file Excel vào memory (PhpSpreadsheet dùng `ToArray` = load hết sheet).
3. **`getProcessedData()`** trả về mảng toàn bộ dòng → PHP giữ cả file (có thể **hàng trăm MB** với file 9k–17k dòng) **trong một request**.
4. Sau đó mới chunk và dispatch job queue; nhưng lúc đó PHP đã dùng rất nhiều RAM.
5. **Hệ quả:** PHP-FPM worker (hoặc cả hệ thống) hết RAM → **OOM killer** kill process hoặc VM treo → mất SSH / phải reset.

**Kết luận:** Server shutdown không phải do Nginx hay `client_max_body_size`, mà do **request xử lý import load cả file Excel vào RAM** → tràn bộ nhớ.

---

## 2. Kiểm tra trên server (sau khi SSH lại được)

Chạy **trên VM staging** để xác nhận OOM hoặc PHP-FPM crash.

### 2.1. Kernel / OOM killer (hết RAM)

```bash
# Có process nào bị OOM kill gần đây không
sudo dmesg -T | grep -i "out of memory\|oom\|killed process"

# Hoặc
sudo journalctl -k -b -1 --no-pager | grep -i "out of memory\|oom\|killed"
```

Nếu thấy `Out of memory: Killed process ... (php-fpm)` (hoặc tương tự) → đúng là **hết RAM**.

### 2.2. PHP-FPM crash

```bash
sudo journalctl -u php8.2-fpm -n 100 --no-pager
sudo tail -100 /var/log/php8.2-fpm.log
```

(Nếu có file log PHP-FPM khác trong `/var/log`, xem thêm.)

### 2.3. Nginx upstream (PHP-FPM mất)

```bash
sudo tail -100 /var/log/nginx/error.log
```

Tìm dòng kiểu: `connect() to unix:/run/php/php8.2-fpm.sock failed (2: No such file or directory)` → PHP-FPM đã tắt/crash.

### 2.4. Laravel / ứng dụng

```bash
cd /var/www/craveva-staging/current/craveva  # hoặc đường dẫn deploy thực tế
tail -200 storage/logs/laravel.log
```

Xem có `Allowed memory size of ... bytes exhausted` hoặc exception lúc import không.

### 2.5. Boot / shutdown (xem mục **STAGING_INCIDENT_CHECK_COMMANDS** ở đầu file này)

```bash
uptime
last reboot | head -5
sudo journalctl -b -1 --no-pager | grep -iE "shutdown|oom|killed|php" | tail -30
```

---

## 3. Giảm thiểu ngay (trên server)

Mục tiêu: cho request import/process **đủ RAM** để load xong file, chunk và dispatch job, rồi trả response (sau đó queue worker xử lý từng chunk, ít tốn RAM hơn mỗi job).

### 3.1. Tăng `memory_limit` cho PHP-FPM

Chỉnh **php.ini** của FPM (không phải CLI):

```bash
# Backup
sudo cp -a /etc/php/8.2/fpm/php.ini /etc/php/8.2/fpm/php.ini.bak.memory.$(date +%Y%m%d)

# Xem giá trị hiện tại
grep -E '^memory_limit' /etc/php/8.2/fpm/php.ini

# Đặt 512M (hoặc 768M nếu file rất lớn)
sudo sed -i 's/^memory_limit = .*/memory_limit = 512M/' /etc/php/8.2/fpm/php.ini
# Nếu chưa có dòng memory_limit, thêm vào [PHP]:
# echo "memory_limit = 512M" | sudo tee -a /etc/php/8.2/fpm/php.ini

# Restart PHP-FPM
sudo systemctl restart php8.2-fpm
sudo systemctl status php8.2-fpm
```

Lưu ý: Nếu VM staging **ít RAM** (vd. 1–2 GB), đặt 512M có thể vẫn gây OOM khi nhiều request. Khi đó nên kết hợp giới hạn file size phía app hoặc chỉ import file nhỏ hơn.

### 3.2. Kiểm tra RAM VM

```bash
free -h
```

Nếu **Mem** tổng chỉ ~1–2G thì ngoài tăng `memory_limit` nên:

- Tránh import file quá lớn (ví dụ giới hạn &lt; 10k dòng) cho đến khi có fix đọc chunk; hoặc
- Nâng cấp VM (thêm RAM).

### 3.3. Swap nên dùng cho file ~17k dòng / ~5MB

| File                        | Gợi ý swap (VM ~2GB RAM)                                                                                                                                      |
| --------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| ~17k dòng, ~5MB Excel       | **1GB swap** (nên dùng). Request load file có thể cần 200–500MB+ trong PHP (PhpSpreadsheet mở rộng trong RAM). 1GB swap giúp tránh OOM khi đỉnh RAM vượt 2GB. |
| File nhỏ hơn (&lt; 5k dòng) | 512MB–1GB swap vẫn an toàn.                                                                                                                                   |

Swap **không chiếm RAM**, chỉ chiếm **dung lượng đĩa**. Tạo swap 1GB:

```bash
sudo fallocate -l 1G /swapfile
sudo chmod 600 /swapfile
sudo mkswap /swapfile
sudo swapon /swapfile
echo '/swapfile none swap sw 0 0' | sudo tee -a /etc/fstab
```

---

## 4. QUEUE_CONNECTION trong .env – có tránh được OOM không?

**Câu hỏi:** Nếu trong `.env` **không** có `QUEUE_CONNECTION=database` thì có phải sẽ **không** bị tình trạng OOM/server shutdown?

**Trả lời: Không.** Ngược lại, **phải có** `QUEUE_CONNECTION=database` thì mới giảm được rủi ro.

| Cấu hình                                                  | Điều gì xảy ra                                                                                                                                                                                                                                                              |
| --------------------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **QUEUE_CONNECTION=database**                             | Request `import/process` chỉ: load file → chunk → **đẩy job vào DB** → trả response. Các job xử lý từng chunk chạy ở **worker** (process riêng). Request kết thúc sớm, nhưng **bước load file** vẫn tốn rất nhiều RAM trong request đó → vẫn có thể OOM nếu thiếu RAM/swap. |
| **QUEUE_CONNECTION=sync** (hoặc không set, mặc định sync) | Request `import/process`: load file → chunk → **chạy luôn tất cả job trong cùng request**. Một request vừa giữ cả file trong RAM, vừa xử lý hết 17k dòng → **tốn RAM và thời gian hơn rất nhiều** → **dễ OOM và timeout hơn**.                                              |

**Kết luận:** Cần **giữ** `QUEUE_CONNECTION=database` và đảm bảo có **queue worker** chạy (`php artisan queue:work` hoặc supervisor). Nếu không dùng queue (sync), tình trạng OOM/server shutdown sẽ **tệ hơn**, không phải tốt hơn.

---

## 6. Hướng xử lý lâu dài (code)

Để **không** load cả file trong một request:

- Đọc Excel **theo chunk** (vd. dùng `Maatwebsite\Excel\Concerns\WithChunkReading` hoặc đọc từng phần) trong request `import/process`.
- Request chỉ đọc từng đoạn (vd. 500–1000 dòng), dispatch từng batch job, rồi đọc tiếp (hoặc dispatch một job “reader” đọc chunk và tạo nhiều job con).

Như vậy request sẽ không giữ cả file 17k dòng trong RAM → giảm mạnh nguy cơ OOM và server shutdown khi nhấn Upload/Start import.

---

## 7. Tóm tắt

| Việc                | Ý nghĩa                                                                                                                              |
| ------------------- | ------------------------------------------------------------------------------------------------------------------------------------ |
| **Nguyên nhân**     | Request import/process load **toàn bộ** Excel vào RAM → PHP (và có thể cả VM) hết RAM → OOM / PHP-FPM chết → server “tắt” / mất SSH. |
| **Xác nhận**        | `dmesg` / `journalctl` tìm OOM, log PHP-FPM và Nginx (upstream failed), Laravel log (memory exhausted).                              |
| **Giảm thiểu ngay** | Tăng `memory_limit` (vd. 512M) cho PHP-FPM, restart FPM; kiểm tra RAM VM.                                                            |
| **Lâu dài**         | Đổi import sang đọc file theo chunk, không load hết trong một request.                                                               |

Sau khi tăng `memory_limit` và restart PHP-FPM, thử lại upload + Start import; nếu vẫn shutdown thì gửi kết quả `dmesg` / `journalctl` (OOM, php-fpm) và `free -h` để xem tiếp.

---

## 8. Đã xác nhận trên staging (2026-03-11)

**journalctl php8.2-fpm:**

- `Mar 11 05:58:49 php8.2-fpm.service: A process of this unit has been killed by the OOM killer.`
- `Mar 11 06:20:18` — lần thứ hai OOM kill.

**/var/log/php8.2-fpm.log:**

- `[pool www] child 628025 exited on signal 9 (SIGKILL)` — SIGKILL = OOM killer.
- `child 628617 exited on signal 9 (SIGKILL)` — tương tự.

**Nginx error.log:**

- Trước 05:58: `upstream timed out` cho `POST .../purchase-products/import` → PHP xử lý quá lâu (đang load file).
- 05:58:49, 06:20:19: `connect() to unix:...php8.2-fpm.sock failed (2: No such file or directory)` → PHP-FPM vừa bị OOM kill.
- 10:55: `upstream timed out` cho `POST .../import/process` → request Start import vẫn chạy quá lâu (load cả file vào RAM).

**Kết luận:** Nguyên nhân là OOM: request import/process load toàn bộ Excel → PHP-FPM hết RAM → bị kill → site down. Cần tăng `memory_limit` (vd. 512M) và cân nhắc tăng `fastcgi_read_timeout` cho Nginx nếu request vẫn bị timeout.

=== RAM ===
total used free shared buff/cache available
Mem: 3.8Gi 2.3Gi 670Mi 266Mi 931Mi 1.1Gi
Swap: 0B 0B 0B
=== DISK ===
Filesystem Size Used Avail Use% Mounted on
/dev/root 194G 49G 145G 26% /

=== RAM ===
total used free shared buff/cache available
Mem: 15Gi 1.4Gi 11Gi 824Ki 3.3Gi 14Gi
Swap: 2.0Gi 53Mi 1.9Gi
=== DISK ===
Filesystem Size Used Avail Use% Mounted on
/dev/root 97G 67G 31G 69% /

---

## STAGING_NGINX_TIMEOUT_IMPORT

Khi import client, request **poll progress** chạy tối đa 50 job trong một request HTTP. Nếu vượt quá thời gian chờ mặc định của Nginx (thường 60s) → **504 Gateway Time-out**. Cách xử lý: tăng `fastcgi_read_timeout` và `proxy_read_timeout` lên 300s (5 phút) trong cấu hình Nginx trên staging.

---

## Cách 1: Chạy script có sẵn (khuyến nghị)

### Bước 1: SSH vào staging

```bash
ssh craveva-staging
```

(Nếu dùng gcloud: xem mục **STAGING_ACCESS_VIA_GOOGLE_CLOUD** bên dưới.)

### Bước 2: Đưa script lên server (nếu chưa có)

Từ **máy local** (PowerShell, trong thư mục project):

```powershell
scp FUNC_BUG/apply_nginx_timeout_staging.sh craveva-staging:/tmp/
```

### Bước 3: Trên staging, chạy script

```bash
sudo bash /tmp/apply_nginx_timeout_staging.sh
```

Script sẽ:

- Backup config hiện tại thành `/etc/nginx/sites-available/staging.bak.timeout.YYYYMMDD`
- Thêm hoặc cập nhật `fastcgi_read_timeout 300s` và `proxy_read_timeout 300s` trong block `server { }`
- Chạy `nginx -t` để kiểm tra cấu hình
- Reload Nginx

### Bước 4: Thử lại import client

Mở lại trang import client trên staging, upload file và bấm xử lý. Request progress có thể chờ tối đa **300 giây** (5 phút) trước khi Nginx trả 504.

---

## Cách 2: Sửa tay trên staging

1. SSH vào staging: `ssh craveva-staging`
2. Backup: `sudo cp /etc/nginx/sites-available/staging /etc/nginx/sites-available/staging.bak.$(date +%Y%m%d)`
3. Mở file: `sudo nano /etc/nginx/sites-available/staging`
4. Trong block **`server {`**, thêm hai dòng (ngay sau `server {` hoặc sau `client_max_body_size` nếu có):

    ```nginx
    fastcgi_read_timeout 300s;
    proxy_read_timeout 300s;
    ```

5. Lưu (Ctrl+O, Enter) và thoát (Ctrl+X).
6. Kiểm tra: `sudo nginx -t`
7. Áp dụng: `sudo systemctl reload nginx`

---

## Hoàn tác (khôi phục config cũ)

```bash
sudo cp /etc/nginx/sites-available/staging.bak.timeout.YYYYMMDD /etc/nginx/sites-available/staging
sudo nginx -t && sudo systemctl reload nginx
```

(Thay `YYYYMMDD` bằng ngày đã backup, ví dụ `20260312`.)

---

## STAGING_PHP_UPLOAD_LIMIT

## Bước 1: SSH vào staging

```bash
ssh craveva-staging
# hoặc: ssh user@<ip-staging>
```

## Bước 2: Kiểm tra PHP hiện tại

```bash
# PHP CLI (xem php.ini đang dùng)
php -i | grep -E "Loaded Configuration File|upload_max_filesize|post_max_size"

# Nếu dùng PHP-FPM (web), xem file cấu hình pool
php -i | grep "Loaded Configuration File"
```

Ghi lại đường dẫn **Loaded Configuration File** (vd. `/etc/php/8.2/fpm/php.ini` hoặc `/etc/php/8.2/cli/php.ini`). Trên server thường có 2 file: **cli** và **fpm**; cần sửa **fpm** để web upload đúng.

## Bước 3: Tìm file php.ini của PHP-FPM

```bash
# Ví dụ Ubuntu/Debian
ls /etc/php/*/fpm/php.ini
# hoặc
ls /etc/php/8.*/fpm/php.ini
```

## Bước 4: Chỉnh upload_max_filesize và post_max_size

**Cách 1: Sửa trực tiếp (cần quyền sudo)**

```bash
# Thay 8.2 bằng đúng version PHP của bạn
sudo sed -i 's/^upload_max_filesize = .*/upload_max_filesize = 50M/' /etc/php/8.2/fpm/php.ini
sudo sed -i 's/^post_max_size = .*/post_max_size = 50M/' /etc/php/8.2/fpm/php.ini
```

**Cách 2: Sửa tay**

```bash
sudo nano /etc/php/8.2/fpm/php.ini
```

Tìm và đặt:

```ini
upload_max_filesize = 50M
post_max_size = 50M
```

(Lưu ý: không có khoảng trắng quanh `=` hoặc dùng 1 khoảng trắng tùy chuẩn file hiện tại.)

## Bước 5: Khởi động lại PHP-FPM

```bash
# Ubuntu/Debian
sudo systemctl restart php8.2-fpm

# Hoặc nếu version khác
sudo systemctl restart php-fpm
```

## Bước 6: Kiểm tra lại (qua CLI)

```bash
php -i | grep -E "upload_max_filesize|post_max_size"
```

Nếu dùng **pool riêng** (vd. www.conf), có thể có override trong `/etc/php/8.2/fpm/pool.d/www.conf`:

```bash
grep -E "php_admin_value|upload_max_filesize|post_max_size" /etc/php/8.2/fpm/pool.d/www.conf
```

Nếu có dòng `php_admin_value` ghi đè thì sửa hoặc comment trong file pool đó.

## Tóm tắt lệnh (copy nguyên block)

```bash
# 1. Kiểm tra version PHP và file config
php -v
php -i | grep "Loaded Configuration File"

# 2. Sửa (thay 8.2 bằng version thực tế, ví dụ 8.1, 8.3)
sudo sed -i 's/^upload_max_filesize = .*/upload_max_filesize = 50M/' /etc/php/8.2/fpm/php.ini
sudo sed -i 's/^post_max_size = .*/post_max_size = 50M/' /etc/php/8.2/fpm/php.ini

# 3. Restart PHP-FPM
sudo systemctl restart php8.2-fpm

# 4. Kiểm tra
php -i | grep -E "upload_max_filesize|post_max_size"
```

Sau khi xong, thử upload lại file import trên staging. Nếu vẫn 413 thì cần tăng thêm **Nginx** `client_max_body_size` (xem tài liệu 413 trước đó).

---

## STAGING_PRODUCTION_MODULE_MISSING_AFTER_PULL_VI

Ngay cap nhat: 2026-05-05

## Trieu chung

- Super Admin > Module Settings khong co module `Production`.
- `php artisan module:list` tren staging khong thay `Production`.

## Nguyen nhan goc

- Staging dang o commit cu (`79ba46ac`), chua chua code `Modules/Production`.
- Chi `git pull` bang user SSH khong dung owner repo nen loi permission `.git/index.lock` / `FETCH_HEAD`.
- Chua dong bo entitlement module theo flow Craveva (`packages.module_in_package`, `module_settings`) va custom modules (`storage/app/modules_statuses.json`).

## Cach xu ly da ap dung (thuc te)

### 1) Kiem tra trang thai tren staging

```bash
cd /var/www/craveva-staging/current/craveva
php artisan module:list --no-ansi
```

### 2) Pull dung user so huu repo

```bash
sudo -u hoangphat5393 /bin/bash -c '
cd /var/www/craveva-staging/current/craveva &&
(git status --porcelain | grep -q . && git stash push -u -m auto-stash-before-production-sync || true) &&
git checkout main &&
git pull origin main
'
```

### 3) Chay migrate + dong bo module

```bash
cd /var/www/craveva-staging/current/craveva
sudo -u www-data php artisan migrate --force
sudo -u www-data php artisan packages:modules activate --module=production
sudo -u www-data php artisan packages:modules enable-custom
sudo -u www-data php artisan optimize:clear
```

### 4) Verify

```bash
sudo -u www-data php artisan module:list --no-ansi
sudo -u www-data php artisan route:list --name=production.
sudo -u www-data php artisan packages:modules list
```

Ky vong:

- `module:list` co dong `Production` va trang thai `[Enabled]`.
- `route:list --name=production.` co cac route `/account/production/...`.
- `packages:modules list` cho thay `production` nam trong module cua cac package.

## Ket qua lan xu ly nay

- Staging da pull len commit `9bdbe6c1`.
- Da co thu muc `Modules/Production`.
- `Production` da enabled trong nwidart va da co route.
- Da dong bo package/module settings cho module `production`.

## Luu y de tranh lap lai

- Luon deploy bang script `scripts/upload_staging.ps1` hoac chay git tren server bang user owner repo (`hoangphat5393`), khong pull bang user khong co quyen ghi `.git`.
- Trong Craveva, co 2 lop can dong bo:
    - Nwidart custom modules (`modules_statuses.json`) -> `packages:modules enable-custom`
    - Business entitlement (`packages.module_in_package` + `module_settings`) -> `packages:modules activate --module=production` (hoac `activate-all-full`)
