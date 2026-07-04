# Staging — SSH, GCP metadata & `upload_staging.ps1` (lỗi + cách xử)

**Cập nhật:** 2026-05-13  
**Phạm vi:** VM `craveva-staging` (GCP), SSH từ Windows, metadata `ssh-keys`, script deploy `scripts/upload_staging.ps1`.  
**Runbook chung:** `docs/SERVER_RUNBOOK.md`, `docs/STAGING_OPERATIONS.md`.  
**SSH / deploy (canonical):** file này. Incident cũ đã retire — `git log -- FUNC_BUG/STAGING_INCIDENTS_ARCHIVE.md`.

---

## 1. Bối cảnh tên user (tránh nhầm)

| User              | Vai trò thường gặp                                                                                                         |
| ----------------- | -------------------------------------------------------------------------------------------------------------------------- |
| **hoangphat5393** | User Linux **owner** repo/app trên VM (`/var/www/craveva-staging/...`), `sudo -u hoangphat5393 git ...`, quyền ghi `.git`. |
| **Admin**         | User đăng nhập SSH khi dùng key **`google_compute_engine`** / luồng gcloud/OS Login — **không** nhất thiết là owner git.   |

Prompt `Admin@craveva-staging` hay `hoangphat5393@craveva-staging` phụ thuộc **cặp User + IdentityFile** trong `%USERPROFILE%\.ssh\config` và **metadata `ssh-keys`** trên VM.

---

## 2. `hoangphat5393@35.240.198.61: Permission denied (publickey)`

### Nguyên nhân thường gặp

1. **`~/.ssh/config`** đặt `User hoangphat5393` + `IdentityFile id_rsa_gcp`, nhưng trên metadata VM **chưa có** dòng **`hoangphat5393:`** + **đúng** public key tương ứng private key đó (đôi khi key chỉ được gắn cho **`Admin:`**).
2. Trong GCP Console dán public key **không** có prefix **`username:`** — guest agent không map đúng user Linux mong muốn.
3. **VM mới / image mới:** user `hoangphat5393` chỉ xuất hiện sau khi metadata có dòng `hoangphat5393:...` (guest agent); có thể cần vài phút hoặc restart VM.

### Định dạng đúng trên metadata `ssh-keys`

Mỗi dòng (xuống dòng phân tách):

```text
hoangphat5393:ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAAB... (một dòng đầy đủ)
```

Tương tự cho ECDSA nếu dùng loại đó. **Không** tách username ra khỏi dòng key nếu dùng form trên.

### Cách xử (đã dùng trong repo)

1. **Script đồng bộ pubkey** (đọc `%USERPROFILE%\.ssh\id_rsa_gcp.pub`, nối vào metadata, **không** xóa key cũ):  
   `.\scripts\gcloud_sync_staging_ssh_key.ps1`  
   (cần `gcloud` đã login + quyền chỉnh instance.)

2. **Đồng bộ block SSH do gcloud tạo:**  
   `gcloud compute config-ssh --project=craveva-org-55934-project`

3. **Sửa alias ngắn** `%USERPROFILE%\.ssh\config` — `Host craveva-staging`:  
   `User hoangphat5393`, `IdentityFile` trỏ **`id_rsa_gcp`**, `IdentitiesOnly yes`.  
   Mẫu: `scripts/ssh_config/craveva-staging.sshconfig.example`.

4. **SSH không phụ thuộc alias:**  
   `.\scripts\ssh_staging.ps1` (gọi `gcloud compute ssh`).

---

## 3. Vẫn thấy `Admin@craveva-staging` sau khi thêm key trên Console

- OpenSSH **bắt buộc** theo `User` trong config: nếu còn `User Admin` thì prompt luôn là Admin.
- Sau khi metadata đã có **`hoangphat5393:`** + đúng pubkey, đổi `User` + `IdentityFile` như mục 2.

---

## 4. `error: cannot open .git/FETCH_HEAD: Permission denied`

### Nguyên nhân

Đang chạy `git pull` bằng user **không** có quyền ghi thư mục `.git` (vd. Admin trong repo của hoangphat5393).

### Cách xử

```bash
cd /var/www/craveva-staging/current/craveva
sudo -u hoangphat5393 git pull origin main
```

Hoặc deploy từ máy dev: `.\scripts\upload_staging.ps1` (remote đã dùng `sudo -u hoangphat5393 git ...`).

---

## 5. `scripts/upload_staging.ps1` — lỗi remote bash & luồng git

### 5.1 Bảng lỗi đã gặp và hướng xử lý trong script

| Hiện tượng                                                  | Nguyên nhân                                                                                     | Hướng xử (trong repo)                                                                                                                                                                |
| ----------------------------------------------------------- | ----------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| `bash: line 9: Bearer: command not found`                   | CR đơn / CRLF trong payload; hoặc biến `header="...Bearer..."` + escape `bash -lc` làm gãy dòng | Chuẩn hóa CR/LF; **bỏ** biến `header`; gọi trực tiếp `git -c "http.extraHeader=AUTHORIZATION: Bearer ${GITHUB_DEPLOY_TOKEN}"`                                                        |
| `Bearer: -c: line 11: syntax error: unexpected end of file` | Nối chuỗi bash kiểu `'"'"'` + escape toàn script làm vỡ quote                                   | Không dùng nối `'"$VAR` cho header; build remote script bằng **placeholder + `.Replace()`** thay vì PowerShell **`-f`** (vì `find ... {{}}` bị `-f` ăn nhầm thành `{}` sai ngữ cảnh) |
| `find ... chmod g+s` sai                                    | Template dùng `{{}}` chỉ để thoát `-f`                                                          | Không dùng `-f` thì dùng **`{}`** đúng chuẩn `find`                                                                                                                                  |
| Deploy “xong” nhưng server vẫn code cũ                      | `ssh` exit ≠ 0 nhưng PowerShell không throw                                                     | Sau `ssh`, kiểm tra `$LASTEXITCODE` và `throw` nếu lỗi                                                                                                                               |

### 5.2 Mặc định không push từ máy dev

- Tham số **`[bool]$SkipLocalGit = $true`**: không chạy `git add/commit/push` local; chỉ SSH lên server pull + migrate.
- Cần **code đã có trên `origin`** (push từ máy khác/CI, hoặc chạy script với **`-SkipLocalGit:$false`** khi muốn push từ máy hiện tại).

---

## 6. Bảng tham chiếu nhanh (script trong repo)

| Mục                                                            | Đường dẫn / lệnh                                       |
| -------------------------------------------------------------- | ------------------------------------------------------ |
| Bật VM + set project                                           | `.\scripts\gcloud_start_staging_vm.ps1`                   |
| Đồng bộ pubkey `hoangphat5393` + `id_rsa_gcp.pub` vào metadata | `.\scripts\gcloud_sync_staging_ssh_key.ps1`            |
| SSH qua gcloud                                                 | `.\scripts\ssh_staging.ps1`                            |
| Deploy staging                                                 | `.\scripts\upload_staging.ps1`                         |
| Mẫu `Host craveva-staging`                                     | `scripts/ssh_config/craveva-staging.sshconfig.example` |

---

## 7. IP / zone / project (có thể đổi)

- **Project:** `craveva-org-55934-project`
- **Zone:** `asia-southeast1-a`
- **Instance:** `craveva-staging`
- **IP ngoài** từng được dùng: **35.240.198.61** — sau stop/start hoặc đổi VM cần `gcloud compute instances describe ... --format='value(networkInterfaces[0].accessConfigs[0].natIP)'` và cập nhật DNS / SSH config nếu đổi.
