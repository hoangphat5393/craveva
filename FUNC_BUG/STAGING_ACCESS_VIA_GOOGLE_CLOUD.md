# Vào server staging khi không SSH được (qua Google Cloud)

Khi `ssh user@staging.craveva.com` hoặc `ssh craveva-staging` bị timeout/refused, dùng một trong các cách dưới (server staging có IP 35.240.158.191, thường là VM Google Compute Engine).

---

## Cách 1: SSH qua trình duyệt (Google Cloud Console) – Nên dùng trước

SSH qua Console **không cần mở port 22** từ mạng của bạn, không phụ thuộc SSH từ máy tính.

1. Đăng nhập: https://console.cloud.google.com/
2. Chọn đúng **Project** (project chứa VM staging).
3. Vào **Compute Engine** → **VM instances** (hoặc menu ☰ → Compute Engine → VM instances).
4. Tìm VM có IP **35.240.158.191** (hoặc tên bạn đặt cho staging).
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
    Xem cột `NAME` và `ZONE` của VM có IP 35.240.158.191.

### Bước 3: SSH vào VM

```bash
gcloud compute ssh TEN_INSTANCE --zone=ZONE --project=PROJECT_ID
```

Ví dụ (thay cho đúng):

```bash
gcloud compute ssh craveva-staging --zone=asia-southeast1-b --project=my-project-123
```

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
