# AI Global Settings - lỗi `The MAC is invalid`

**Mã:** `AUTH-AI-002` - **Trạng thái:** Fixed 2026-07-04

## Triệu chứng

- Đăng nhập thành công nhưng dashboard lỗi `DecryptException: The MAC is invalid`.
- Stack dừng tại `resources/views/layouts/app.blade.php` khi đọc
  `global_settings.ai_assistant_widget_api_key`.
- Trang AI Workspace cũng có thể lỗi khi đọc `ai_workspace_api_key`.

## Nguyên nhân

Hai field dùng Eloquent cast `encrypted`, nhưng ciphertext được tạo bằng APP_KEY
khác với APP_KEY local hiện tại. `global_setting()` trước đây chỉ kiểm tra hai
field Google, nên ciphertext AI hỏng vẫn được cache và được Blade đọc trực tiếp.

## Fix

- `GlobalSetting::aiWorkspaceApiKey()` và
  `GlobalSetting::aiAssistantWidgetApiKey()` trả `null` khi optional ciphertext
  không thể giải mã.
- Layout và AI Workspace không đọc trực tiếp encrypted cast nữa.
- Hai ciphertext AI không thể sử dụng trong DB local được reset về `NULL`; embed
  code và các cấu hình không liên quan được giữ nguyên.

Không log hoặc hiển thị raw ciphertext/API key. Khi cần dùng API-key mode, nhập
lại key bằng APP_KEY hiện tại hoặc chuyển sang embed code đang được UI hỗ trợ.

## Kiểm tra

```powershell
php artisan test --compact tests/Unit/GlobalSettingEncryptedAttributeTest.php
php artisan test --compact tests/Feature/AiWorkspacePageTest.php tests/Feature/AiAssistantWidgetGlobalSettingTest.php
```

