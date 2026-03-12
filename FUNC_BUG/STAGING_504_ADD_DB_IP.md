# Sửa lỗi 504 staging: thêm IP VM staging vào database

Staging (https://staging.craveva.com/) bị **504** vì VM mới có IP **35.240.234.226** chưa được phép kết nối tới MySQL tại **136.110.52.19**.

---

## Cần làm (chỉ 1 việc)

**Thêm IP sau vào “Authorized networks” của Cloud SQL (instance có IP 136.110.52.19):**

- **IP:** `35.240.234.226`
- **Tên (Name):** `staging-vm` (tùy chọn)

---

## Cách làm trong Google Cloud Console

1. Vào https://console.cloud.google.com/ → chọn project (craveva-org-55934-project hoặc project chứa DB).
2. Menu ☰ → **SQL** (hoặc tìm "SQL").
3. Chọn instance có IP **136.110.52.19** (bấm vào tên instance).
4. Tab **Connections** → phần **Authorized networks** → **Add network**.
5. **Network:** `35.240.234.226` — **Name:** `staging-vm` → **Done** → **Save**.
6. Đợi 1–2 phút, sau đó thử lại https://staging.craveva.com/

---

## Nếu dùng lệnh (gcloud)

Thay `TEN_INSTANCE` bằng tên instance Cloud SQL thực tế:

```bash
gcloud sql instances patch TEN_INSTANCE --authorized-networks=35.240.234.226/32 --project=craveva-org-55934-project
```

(Nếu đã có danh sách authorized networks, cần thêm IP vào danh sách có sẵn thay vì ghi đè — xem tài liệu `gcloud sql instances patch`.)
