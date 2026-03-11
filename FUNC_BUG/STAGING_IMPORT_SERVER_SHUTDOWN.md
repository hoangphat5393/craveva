# Staging: Server shutdown / mất SSH khi nhấn Upload/Start Import

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

### 2.5. Boot / shutdown (xem STAGING_INCIDENT_CHECK_COMMANDS.md)

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
