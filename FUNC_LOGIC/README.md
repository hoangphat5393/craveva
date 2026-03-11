# FUNC_LOGIC – Tài liệu logic & flow (Craveva)

Thư mục lưu tài liệu mô tả **lệnh**, **flow** và **thư viện** dùng trong dự án.

| File                              | Nội dung                                                                       |
| --------------------------------- | ------------------------------------------------------------------------------ |
| **Login_Flow.md**                 | Flow đăng nhập (Fortify, session, cache, troubleshooting).                     |
| **Package_Modules_Commands.md**   | Lệnh `packages:modules` (list, activate-all, activate) và cách dùng.           |
| **Package_Modules_Flow.md**       | Flow Package → module_in_package → Company → module_settings, observer, cache. |
| **Libraries_And_Module_Names.md** | Thư viện Composer liên quan Package/Module và tên module trong app.            |

---

## Lệnh nhanh (Package & Module)

```bash
# Xem trạng thái module từng package
php artisan packages:modules list

# Bật toàn bộ module trong gói (packages + module_settings)
php artisan packages:modules activate-all
php artisan packages:modules activate-all --package=9

# Bật một module trong gói
php artisan packages:modules activate --module=clients
php artisan packages:modules activate --module=products --package=9

# Bật toàn bộ Custom Modules (toggle ON trên trang Settings > Module Settings > Custom Modules)
php artisan packages:modules enable-custom
```
