# Sao lưu Cloud SQL — `craveva-staging-db` (Staging)

**Mục đích:** ghi rõ _khi nào_ GCP tạo bản sao lưu, _giữ bao nhiêu bản_, và _bản cũ bị xóa / hết hiệu lực khi nào_ — để vận hành và khôi phục thống nhất với runbook.

**Tham chiếu runbook:** [`SERVER_RUNBOOK_VI.md`](SERVER_RUNBOOK_VI.md) · [`STAGING_OPERATIONS.md`](STAGING_OPERATIONS.md) · [`GCP_INFRA_INVENTORY_SUMMARY.md`](GCP_INFRA_INVENTORY_SUMMARY.md)

**Cập nhật gần nhất:** 2026-05-14 (sau khi bật lại automated backup + binary log trên instance)

---

## 1. Instance & project

| Thuộc tính                    | Giá trị                                                     |
| ----------------------------- | ----------------------------------------------------------- |
| **GCP project**               | `craveva-org-55934-project`                                 |
| **Cloud SQL instance**        | `craveva-staging-db`                                        |
| **Engine**                    | MySQL 8 (`MYSQL_8_0` trong inventory)                       |
| **Region**                    | `asia-southeast1`                                           |
| **Database ứng dụng staging** | `craveva_staging` (tên schema; không nhầm với tên instance) |

---

## 2. Sao lưu tự động (automated backup)

| Câu hỏi                                 | Trả lời (theo cấu hình hiện tại)                                                                                                                                                                                                                              |
| --------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Có bật không?**                       | **Có** (`backupConfiguration.enabled: true`).                                                                                                                                                                                                                 |
| **Tần suất**                            | **Một bản automated backup mỗi ngày** (hành vi chuẩn Cloud SQL cho MySQL).                                                                                                                                                                                    |
| **“Giờ backup” nghĩa là gì?**           | Trường **`startTime: 15:00`** là **thời điểm bắt đầu cửa sổ backup hằng ngày** của instance (theo **múi giờ của instance Cloud SQL**, thường trùng với region). Google lên lịch backup **trong** cửa sổ đó, không phải “đúng giây 15:00:00” cố định mỗi ngày. |
| **Giữ bao nhiêu bản automated backup?** | **`7` bản** (`retainedBackups: 7`, `retentionUnit: COUNT`).                                                                                                                                                                                                   |

### 2.1 Bản backup cũ bị xóa khi nào?

- Cloud SQL **xoay vòng theo số lượng (count)**: hệ thống luôn cố gắng giữ **tối đa 7 bản automated backup gần nhất** (các bản `SUCCESSFUL`).
- Khi **bản automated backup thứ 8** (mới) được tạo thành công, bản **cũ nhất trong nhóm 7 bản trước đó** sẽ **bị gỡ khỏi danh sách backup có thể restore** (xoay vòng). Đây là hành vi **tự động** của GCP, không cần thao tác tay.
- **Không** phải lịch “mỗi tháng xóa một lần”: việc xóa/xoay phụ thuộc **số bản đã tích lũy** sau mỗi ngày có backup thành công.

### 2.2 Binary log & khôi phục theo thời điểm (PITR)

| Thuộc tính                          | Giá trị                                                                                                                                                                                           |
| ----------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Binary log**                      | **Bật** (`binaryLogEnabled: true`) — phục vụ **point-in-time recovery** trong phạm vi log còn giữ.                                                                                                |
| **Giữ transaction log**             | **`7` ngày** (`transactionLogRetentionDays: 7`).                                                                                                                                                  |
| **Log cũ “hết dùng được” khi nào?** | Phần log (và khả năng PITR) **không kéo dài quá 7 ngày** theo chính sách retention của instance. Muốn cửa sổ PITR dài hơn phải **tăng** `transactionLogRetentionDays` (cân nhắc chi phí lưu trữ). |

---

## 3. Xem backup thực tế trên GCP

**Console:** SQL → chọn `craveva-staging-db` → tab **Backups** (và **Operations** nếu cần xem lịch sử tác vụ).

**CLI (máy đã `gcloud auth` đúng project):**

```bash
gcloud sql backups list --instance=craveva-staging-db --project=craveva-org-55934-project --limit=20
```

**Xem lại cấu hình backup trên instance:**

```bash
gcloud sql instances describe craveva-staging-db --project=craveva-org-55934-project --format="yaml(settings.backupConfiguration)"
```

---

## 4. Ghi chú vận hành

- **Sao lưu Cloud SQL ≠ dump `mysqldump` tay:** automated backup là lớp bảo vệ **cấp instance**; vẫn nên có **kế hoạch export** (GCS / máy chủ) trước thay đổi lớn nếu policy nội bộ yêu cầu.
- **Bật/tắt backup hoặc đổi giờ:** dùng `gcloud sql instances patch` (hoặc chỉnh trong Console). Sau khi đổi, **theo dõi** tab Backups vài ngày để chắc chắn có bản `SUCCESSFUL` đều.
- **Khôi phục (restore/clone):** luôn đọc tài liệu Google Cloud SQL cho MySQL (restore có thể tạo instance mới hoặc ghi đè — cần quy trình nội bộ).

---

## 5. Lịch sử thay đổi ngắn (repo)

| Ngày       | Việc làm                                                                                                                                                      |
| ---------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| 2026-05-14 | Bật `enabled: true`, `binaryLogEnabled: true`, `retainedBackups: 7`, `transactionLogRetentionDays: 7`, `startTime: 15:00` (ghi nhận trong repo tại file này). |
