# Kiểm tra nguyên nhân sự cố staging (chỉ đọc, không sửa)

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
