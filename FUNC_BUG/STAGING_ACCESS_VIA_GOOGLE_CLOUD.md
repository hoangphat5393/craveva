# Vào server staging khi không SSH được (qua Google Cloud)

Khi `ssh user@staging.craveva.com` hoặc `ssh craveva-staging` bị timeout/refused, dùng một trong các cách dưới (server staging có IP 35.240.234.226, thường là VM Google Compute Engine).

---

## Cách 1: SSH qua trình duyệt (Google Cloud Console) – Nên dùng trước

SSH qua Console **không cần mở port 22** từ mạng của bạn, không phụ thuộc SSH từ máy tính.

1. Đăng nhập: https://console.cloud.google.com/
2. Chọn đúng **Project** (project chứa VM staging).
3. Vào **Compute Engine** → **VM instances** (hoặc menu ☰ → Compute Engine → VM instances).
4. Tìm VM có IP **35.240.234.226** (hoặc tên bạn đặt cho staging).
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
    Xem cột `NAME` và `ZONE` của VM có IP 35.240.234.226.

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

## Trỏ domain staging.craveva.com về IP mới (35.240.234.226)

Sau khi VM staging chuyển zone, **External IP** đổi thành **35.240.234.226**. Cần sửa bản ghi DNS cho `staging.craveva.com` trỏ về IP này.

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
        - **35.240.234.226**
    - Bấm **Save** / biểu tượng tick / **Save All Changes**.

5. **Đợi cập nhật**
    - Thường 5–30 phút. Có thể kiểm tra bằng:
        ```powershell
        nslookup staging.craveva.com
        ```
        Khi thấy `Address: 35.240.234.226` là đã trỏ đúng.

**Nếu chưa có bản ghi A cho staging:**

- Bấm **Add New Record**.
- **Type:** A
- **Host:** `staging`
- **Value:** `35.240.234.226`
- **TTL:** Automatic (hoặc 300).
- **Save**.

---

### Nếu DNS nằm trên Google Cloud (Cloud DNS)

1. Vào https://console.cloud.google.com/ → chọn project (ví dụ **craveva-org-55934-project**).
2. **Network services** → **Cloud DNS** (hoặc tìm "DNS" trong menu).
3. Chọn zone của domain **craveva.com**.
4. Tìm bản ghi **A** có tên `staging` (hoặc `staging.craveva` tùy cấu hình).
5. **Edit** → đổi giá trị (IPv4) thành **35.240.234.226** → **Save**.

---

### Nếu DNS ở nhà cung cấp khác (Cloudflare, GoDaddy, …)

1. Đăng nhập vào trang quản lý DNS của domain **craveva.com**.
2. Tìm bản ghi **A** cho subdomain **staging** (Host = `staging`).
3. Đổi **Value / Points to / Answer** thành **35.240.234.226**.
4. Lưu. Đợi vài phút rồi kiểm tra bằng `nslookup staging.craveva.com`.

Sau khi đổi xong, `staging.craveva.com` sẽ trỏ về VM staging mới (zone `asia-southeast1-a` sau migrate 2026-05).

---

## Đã trỏ domain (staging.craveva.com) nhưng vẫn không vào được (timeout)

DNS đúng (35.240.234.226) nhưng trình duyệt timeout thường do **trên VM**: Nginx hoặc PHP-FPM chưa chạy sau khi VM mới boot.

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

Nginx báo **504** khi PHP-FPM / Laravel không trả về response trong thời gian quy định. Trên staging, Laravel kết nối DB tại **136.110.52.19**. VM staging mới có IP **35.240.234.226** (khác IP cũ 35.240.158.191).

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
    - **Network:** `35.240.234.226`
8. Bấm **Done** (hoặc **Add**) rồi **Save** để lưu thay đổi.
9. Đợi vài phút cho cấu hình áp dụng, sau đó mở lại **https://staging.craveva.com/**.

**Lệnh gcloud** (nếu bạn có quyền và biết tên instance, ví dụ `craveva-db`):

```bash
gcloud sql instances patch TEN_INSTANCE --authorized-networks=35.240.234.226/32 --project=craveva-org-55934-project
```

(Thay `TEN_INSTANCE` bằng tên instance Cloud SQL thực tế.)

### Nếu DB là VM/MySQL trên server 136.110.52.19 (không phải Cloud SQL)

Trên server đó: - Mở firewall cho port **3306** từ IP **35.240.234.226**, hoặc - Trong MySQL: `CREATE USER ...@'35.240.234.226'` hoặc cấp quyền cho user hiện tại từ host `35.240.234.226`; `FLUSH PRIVILEGES;` 3. Sau khi cho phép IP mới, thử lại https://staging.craveva.com/

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
