# Tối ưu hiệu năng DataTable Client (100 dòng load chậm)

**Triệu chứng:** Trang danh sách Client load ~100 dòng nhưng thời gian phản hồi khá lâu.

**File liên quan:** `app/DataTables/ClientsDataTable.php`, `app/Http/Controllers/ClientController.php`, `app/Models/CustomField.php`, `app/Models/CustomFieldGroup.php`, `resources/views/components/client.blade.php`

---

## 1. Nguyên nhân chính

### 1.1 CustomFieldGroup – `info()` log mỗi request

**File:** `app/Models/CustomFieldGroup.php` (khoảng dòng 87)

```php
info($customFieldsDataMerge);  // ← Ghi log mỗi lần getColumns()
```

- `customFieldsDataMerge` được gọi trong `getColumns()` – chạy **mỗi request** DataTable.
- `info()` ghi file log → I/O ổ cứng, chậm khi nhiều request.

**Đề xuất:** Xóa hoặc comment dòng `info($customFieldsDataMerge)`.

---

### 1.2 CustomField – load toàn bộ custom_fields_data

**File:** `app/Models/CustomField.php`, method `customFieldData()` (khoảng dòng 97)

```php
$fieldData = DB::table('custom_fields_data')
    ->where('model', $model)
    ->whereIn('custom_field_id', $customFieldsId)
    ->select('id', 'custom_field_id', 'model_id', 'value')
    ->get();  // ← Lấy TẤT CẢ bản ghi Client, không phân trang
```

- Với nhiều client, `custom_fields_data` có thể hàng nghìn dòng.
- Toàn bộ được load vào bộ nhớ rồi `filter()` trong vòng lặp cho từng dòng → chậm và tốn RAM.

**Đề xuất:** Chỉ lấy dữ liệu cho các `model_id` thuộc trang hiện tại (ids từ kết quả DataTable).

**✅ Đã sửa:** Thêm tham số `$idsQuery`, `$modelIdColumn` vào `CustomField::customFieldData()`.

**✅ Đã sửa (lần 2):** Thêm tham số `$orderColumnMap` (map column index → cột ORDER BY). Khi request có `order[0][column]` và `order[0][dir]`, áp dụng cùng order vào query lấy ids → đúng bộ ids với trang DataTable. ClientsDataTable truyền `$query`, `client_details.id`, và `$clientOrderMap`.

---

### 1.3 ClientController::index – load toàn bộ Client để đếm

**File:** `app/Http/Controllers/ClientController.php` (khoảng dòng 84–89)

```php
$this->clients = User::allClients(active: false);
// ...
$this->totalClients = count($this->clients);  // ← Đếm collection trong PHP
```

- `allClients()` load **toàn bộ** client vào collection.
- `count($this->clients)` đếm trong PHP thay vì dùng truy vấn COUNT.
- Với 10.000+ client → rất chậm.

**Đề xuất:** Dùng query riêng để đếm, ví dụ:

```php
$this->totalClients = User::allClients(active: false)->count();
```

Hoặc tối ưu hơn: tạo scope/count query riêng, không cần load từng model.

**✅ Đã sửa:** Thêm tham số `$execute = true` vào `User::allClients()`. Khi `$execute = false` trả về query builder. ClientController dùng query để `count()` trước, rồi `get()` cho dropdown.

---

### 1.4 ClientsDataTable – Eager load `session` cho 100 user

**File:** `app/DataTables/ClientsDataTable.php` (dòng 116)

```php
->with('session:id', 'clientDetails.addedBy:id,name,image', 'clientDetails.company:id,logo,company_name')
```

- `session` dùng cho trạng thái “online” trong `client.blade.php`.
- Bảng `sessions` thường rất lớn, join/eager load nhiều session có thể chậm.

**Đề xuất:** Chỉ load session khi cần (ví dụ: khi có cột “Online”), hoặc tách thành tải sau (lazy) cho từng hàng nếu UX cho phép.

---

### 1.5 DATE() trong điều kiện – không dùng index

**File:** `app/DataTables/ClientsDataTable.php` (dòng 127, 133)

```php
->where(DB::raw('DATE(users.`created_at`)'), '>=', $startDate)
->where(DB::raw('DATE(users.`created_at`)'), '<=', $endDate)
```

- `DATE(column)` ngăn MySQL dùng index trên `created_at`.

**Đề xuất:** Dùng range:

```php
->where('users.created_at', '>=', $startDate . ' 00:00:00')
->where('users.created_at', '<=', $endDate . ' 23:59:59')
```

(Điều chỉnh format datetime cho phù hợp với DB và timezone.)

---

### 1.6 whereHas – subquery thay vì JOIN

**File:** `app/DataTables/ClientsDataTable.php` (dòng 154–171)

```php
->whereHas('projects', ...)
->whereHas('contracts', ...)
->whereHas('country', ...)
```

- `whereHas` tạo EXISTS subquery → thường chậm hơn JOIN khi dataset lớn.

**Đề xuất:** Ưu tiên `join` khi có thể, ví dụ:

```php
->join('projects', 'projects.client_id', '=', 'users.id')
->where('projects.id', $request->project_id)
```

(Và tương tự cho `contracts`, `country` nếu logic cho phép.)

---

### 1.7 Component client – dùng nhiều relationship

**File:** `resources/views/components/client.blade.php`

- Dùng `$user->session`, `$user->clientDetails`, `$user->company` (timezone).
- Nếu không eager load đủ → gây N+1.

**Đề xuất:** Đảm bảo DataTable query đã `with()` đủ các relation được dùng trong component, hoặc hạn chế gọi relation không cần thiết.

---

### 1.8 Duplicate `addIndexColumn()`

**File:** `app/DataTables/ClientsDataTable.php` (dòng 42 và 98)

- `addIndexColumn()` bị gọi 2 lần.

**Đề xuất:** Chỉ gọi 1 lần.

---

## 2. Thứ tự ưu tiên sửa

| Ưu tiên | Việc                                            | Mức ảnh hưởng               |
| ------- | ----------------------------------------------- | --------------------------- |
| 1       | Xóa `info()` trong CustomFieldGroup             | Cao – mọi request DataTable |
| 2       | Giới hạn custom_fields_data theo trang hiện tại | Cao – khi có nhiều client   |
| 3       | Không load toàn bộ client để đếm trong index    | Cao – khi client > 1000     |
| 4       | Đổi DATE() sang range cho created_at            | Trung bình                  |
| 5       | Giảm/bỏ eager load `session`                    | Trung bình                  |
| 6       | Thay whereHas bằng join (nếu có filter)         | Trung bình                  |
| 7       | Bỏ duplicate addIndexColumn                     | Thấp                        |

---

## 3. Gợi ý chỉnh sửa cụ thể

### 3.1 CustomFieldGroup – xóa log

```php
// Xóa dòng:
info($customFieldsDataMerge);
```

### 3.2 ClientController – đếm client

```php
// Thay:
$this->totalClients = count($this->clients);

// Bằng (nếu cần totalClients):
$this->totalClients = User::withoutGlobalScope(ActiveScope::class)
    ->join('role_user', ...)
    ->join('roles', ...)
    ->join('client_details', ...)
    ->where('roles.name', 'client')
    ->whereNull('users.is_client_contact')
    // ... các điều kiện tương tự allClients
    ->count();
```

Hoặc bỏ `totalClients` nếu không dùng.

### 3.3 ClientsDataTable – điều kiện ngày

```php
// Thay DB::raw DATE bằng:
$users = $users->where('users.created_at', '>=', $startDate . ' 00:00:00');
$users = $users->where('users.created_at', '<=', $endDate . ' 23:59:59');
```

(Cần kiểm tra timezone và format ngày của `companyToDateString()`.)

### 3.4 ClientsDataTable – bỏ session khỏi with (tùy chọn)

```php
// Bỏ 'session:id' nếu không cần online status:
->with('clientDetails.addedBy:id,name,image', 'clientDetails.company:id,logo,company_name')
```

Và sửa `client.blade.php` để không dùng `$user->session` (hoặc dùng fallback khi null).

### 3.5 ClientsDataTable – xóa addIndexColumn trùng

```php
// Xóa một trong hai dòng addIndexColumn()
```

### 3.6 CustomField::customFieldData – lọc theo trang

- Cần truy cập danh sách `model_id` của trang hiện tại (ví dụ qua closure/callback của DataTable).
- Chỉ query `custom_fields_data` với `model_id IN (...)` cho các id đó.
- Có thể đòi hỏi sửa signature của `customFieldData()` để nhận ids hoặc query builder.

---

## 4. Index database gợi ý

Đảm bảo có index cho:

- `users(company_id, created_at)`
- `client_details(user_id, category_id, added_by)`
- `role_user(user_id)`, `roles(name)`
- `custom_fields_data(model, custom_field_id, model_id)`

Chạy `EXPLAIN` trên query chính của DataTable để kiểm tra sử dụng index.
