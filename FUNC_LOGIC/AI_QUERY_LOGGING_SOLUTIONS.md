# GIẢI PHÁP GHI LOG QUERY CHO AI AGENT TRONG LARAVEL

**Mục đích:** Theo dõi và kiểm soát các câu lệnh SQL mà AI Agent bên thứ 3 thực thi trên hệ thống để đảm bảo tính chính xác và bảo mật.

---

## 1. TRIỂN KHAI TRONG LARAVEL

Sử dụng `DB::listen` để bắt mọi câu lệnh SQL. Bạn nên đăng ký trong `AppServiceProvider.php`.

### Mã nguồn mẫu:
```php
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

public function boot()
{
    // Chỉ bật log khi ở môi trường local hoặc khi cần debug AI
    if (config('app.env') === 'local' || env('LOG_AI_QUERIES', false)) {
        DB::listen(function ($query) {
            // Nhận diện request từ AI Agent qua Header hoặc URL
            if (request()->header('X-AI-Agent') || request()->is('api/ai/*')) {
                Log::channel('daily')->info('--- AI QUERY START ---');
                Log::channel('daily')->info('SQL: ' . $query->sql);
                Log::channel('daily')->info('Bindings: ' . json_encode($query->bindings));
                Log::channel('daily')->info('Time: ' . $query->time . 'ms');
                Log::channel('daily')->info('--- AI QUERY END ---');
            }
        });
    }
}
```

### Cách xem log:
- File log được lưu tại: `storage/logs/laravel-YYYY-MM-DD.log`.
- Có thể xem qua giao diện web tại route: `/log-viewer`.

---

## 2. CÁCH XEM LOG TRỰC TIẾP TRÊN HEIDISQL

Nếu bạn muốn xem các câu lệnh SQL đang chạy trực tiếp trên **HeidiSQL**, bạn có thể sử dụng tính năng **Query History** hoặc **Processlist**.

### Cách 1: Xem lịch sử query của phiên làm việc (Query History)
- Ở bảng điều khiển phía dưới cùng của HeidiSQL (phần log màu trắng), bạn sẽ thấy các câu lệnh SQL mà chính HeidiSQL thực hiện.
- Để xem các câu lệnh do AI thực hiện (kết nối từ bên ngoài), bạn cần bật **General Log** của MySQL.

### Cách 2: Xem các tiến trình đang chạy (Processlist)
1. Trong HeidiSQL, nhấn vào menu **Tools** -> **Show processlist** (hoặc nhấn phím tắt `F6`).
2. Một cửa sổ hiện ra liệt kê tất cả các kết nối đang hoạt động tới Database.
3. Khi AI thực hiện một câu lệnh phức tạp, bạn sẽ thấy nó xuất hiện trong cột **Info** cùng với thời gian thực thi.

### Cách 3: Bật General Log để lưu vào bảng (Dùng khi cần kiểm tra sâu)
Bạn có thể chạy các lệnh sau trong tab **Query** của HeidiSQL:

```sql
-- 1. Bật tính năng log
SET GLOBAL general_log = 'ON';

-- 2. Yêu cầu MySQL lưu log vào bảng hệ thống thay vì file
SET GLOBAL log_output = 'TABLE';

-- 3. Xem các query mà AI vừa thực hiện
SELECT * FROM mysql.general_log 
WHERE user_host LIKE '%tên_user_ai%' 
ORDER BY event_time DESC;
```

**Lưu ý:** Sau khi kiểm tra xong, hãy nhớ tắt log bằng lệnh `SET GLOBAL general_log = 'OFF';` để tránh làm chậm hệ thống và đầy dung lượng ổ cứng.

---
*Tài liệu này được lưu để phục vụ việc giám sát AI Agent.*
