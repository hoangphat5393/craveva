# Recruit – Settings Source: lỗi `Array to string conversion` (Windows)

**Cập nhật:** 2026-04-06

---

## 1. Triệu chứng

- Vào **Recruit → Settings** (hoặc trang cấu hình source), Blade báo lỗi kiểu **`Array to string conversion`** tại dòng dùng `@lang('Source')` hoặc tương đương.

---

## 2. Nguyên nhân

- Trên **Windows**, hệ thống file **không phân biệt hoa thường**; Laravel khi resolve key dịch có thể **trùng nhóm** giữa:
    - file ngôn ngữ dạng `source.php` (trả về **mảng**), và
    - key/string `Source` ở nơi khác.
- Khi Blade cố in kết quả `@lang('Source')` nhưng thực tế nhận **array** → lỗi chuyển sang chuỗi.

---

## 3. Cách sửa

- **Không** dùng key mơ hồ `@lang('Source')` trên trang Recruit.
- Dùng key **đầy đủ theo namespace module**, ví dụ:

```blade
@lang('recruit::modules.sourceSetting.source')
```

- File đã chỉnh: `Modules/Recruit/Resources/views/recruit-setting/index.blade.php`.

**Test:** `tests/Feature/RecruitSourceSettingTranslationTest.php`.

---

## 4. Phòng ngừa

- Với module, ưu tiên prefix `recruit::...` hoặc key cụ thể, tránh một từ trùng tên file lang (`source`, `menu`, …) trên môi trường case-insensitive.
