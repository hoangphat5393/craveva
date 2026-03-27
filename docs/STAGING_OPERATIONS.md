# Staging — vận hành & deploy (Craveva)

**Host SSH:** `craveva-staging`  
**App:** `/var/www/craveva-staging/current/craveva`  
**URL:** `https://staging.craveva.com`

Tài liệu này gộp quy trình chuẩn; log sự cố theo ngày vẫn giữ riêng trong repo (xem mục _Tài liệu liên quan_).

---

## 1. Ngôn ngữ: dùng `en`, không dùng `eng`

- **Chuẩn mã ngôn ngữ:** **`en`** (ISO 639-1), khớp `APP_LOCALE` / `LanguageSetting.language_code`.
- **`eng`** là tên cũ; DB đã có migration chuẩn hóa `eng` → `en` (`database/migrations/2026_03_13_100000_standardize_language_code_eng_to_en.php`).
- File dịch Laravel đọc theo locale hiện tại: với `locale=en` cần có **`lang/en/`** hoặc **`Modules/.../Resources/lang/en/`**. Thư mục chỉ có `eng/` sẽ **không** được dùng tự động.
- **Đồng bộ module với Language Pack (nguồn):** `Modules/LanguagePack/Languages/modules/<Module>/en/` → copy/publish sang `Modules/<Module>/Resources/lang/en/` khi cần (hoặc `php artisan languagepack:publish-translation` với quyền và DB đúng).

---

## 2. Cách deploy khuyến nghị: Git

Ưu tiên **`git pull`** trên server (không xóa toàn bộ tree như gói zip).

```bash
cd /var/www/craveva-staging/current/craveva
git fetch origin main
git pull --ff-only origin main
```

Sau đó (production):

```bash
APP_ENV=production composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist
sudo -u www-data php artisan migrate --force
sudo -u www-data php artisan optimize:clear
```

**Không** chạy `composer update` trên staging (chỉ `install` theo `composer.lock` đã commit). Chi tiết: `docs/STAGING_PHP83_L11_DEPLOY_PROGRESS.md`.

**Trước khi pull:** trên server thường có file `M` dưới `storage/` — có thể cần `git stash` hoặc chỉ checkout phần code, tránh kẹt merge.

---

## 3. Script `scripts/upload_staging.ps1` (zip từng phần)

- **Mục đích:** đóng gói **danh sách file/thư mục** cục bộ → zip → `scp` → giải nén lên staging.
- **Rủi ro:** lệnh remote có đoạn **`find ... -maxdepth 1 ... -exec rm -rf`** (xóa gần như mọi thứ trong app **trừ** `.env`, `storage`, `.git`). Chỉ dùng khi hiểu rõ hậu quả; **ưu tiên Git deploy** (mục 2).
- Danh sách file trong script có thể **trùng lặp**; script đã gom **`Select-Object -Unique`** trước khi copy để gọn và dễ đọc.
- Thư mục copy nguyên khối: ví dụ `Modules/LanguagePack/Languages/modules`, `resources/lang` (xem biến `$DirsToCopy` trong script).

---

## 4. Ổ đĩa & khôi phục nhanh

- Đầy disk / `git pull` lỗi: **`docs/STAGING_RECOVERY_LATEST.md`** (stub) và log chi tiết **`docs/STAGING_DISK_RECOVERY_2026-03-27.md`**.
- Không xóa `vendor/**/.git` lẻ tẻ; nếu vendor hỏng: `rm -rf vendor` + `composer install --no-dev` theo lock.

---

## 5. Kiểm tra nhanh sau deploy (SSH)

```bash
ssh craveva-staging "df -h /"
ssh craveva-staging "cd /var/www/craveva-staging/current/craveva && sudo -u www-data php artisan about | head -25"
ssh craveva-staging "curl -sI -o /dev/null -w '%{http_code}\n' https://staging.craveva.com/"
```

Mục tiêu: root còn **> ~2 GB** trống; HTTP **200**; Laravel boot không lỗi.

---

## 6. Tài liệu liên quan (không gộp nội dung)

| File                                        | Nội dung                                      |
| ------------------------------------------- | --------------------------------------------- |
| `docs/STAGING_DISK_RECOVERY_2026-03-27.md`  | Nhật ký incident ổ đĩa (2026-03-27)           |
| `docs/STAGING_PHP83_L11_DEPLOY_PROGRESS.md` | Tiến độ PHP 8.3 / L11, composer, cache        |
| `docs/STAGING_RECOVERY_LATEST.md`           | Stub trỏ về tài liệu này + recovery tối thiểu |
| `scripts/download_staging_logs.ps1`         | Tải log Nginx/PHP từ staging về máy local     |

---

_Cập nhật: 2026-03-27 — gộp hướng dẫn deploy, `en`/`eng`, upload zip và kiểm tra._
