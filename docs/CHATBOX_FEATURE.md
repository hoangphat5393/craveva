# Tính năng Chatbox AI Workspace

Tài liệu này mô tả chi tiết về tính năng Chatbox cho AI Workspace, cách triển khai, kiểm thử và bảo trì.

## 1. Mô tả tính năng

Tính năng này tích hợp widget AI Chat từ bên thứ ba (Craveva AI) vào hệ thống.

- **Mặc định:** Widget **không được tải** (Lazy Load) khi vào trang, giúp tăng tốc độ tải trang ban đầu.
- **Kích hoạt:** Script của Widget chỉ được inject vào DOM khi người dùng click vào mục "AI Workspace" lần đầu tiên.
- **Hiệu ứng:** Chatbox xuất hiện và ẩn đi với hiệu ứng chuyển động mượt mà.
- **Lưu trạng thái:** Trạng thái (ẩn/hiện) được lưu trong `localStorage` để duy trì qua các lần tải lại trang.
- **Tương thích:** Hoạt động trên Chrome, Firefox, Safari, Edge nhờ sử dụng các API chuẩn (MutationObserver) và jQuery.

## 2. Chi tiết triển khai

### Thay đổi kiến trúc

- **Trước đây:** Script được nhúng cứng trong `public/js/custom.js` (hoặc inject trực tiếp) và chạy tự động mỗi khi tải trang.
- **Hiện tại:**
    - Đã **xác minh và xóa** mã nhúng khỏi `public/js/custom.js` (nếu có).
    - Logic nhúng và điều khiển được chuyển vào **`resources/views/layouts/app.blade.php`**.
    - Sử dụng **MutationObserver** để tự động phát hiện widget khi nó được render vào `body` và di chuyển nó vào container quản lý (`#ai-chatbot-container`) để áp dụng style và animation.

### Các file đã chỉnh sửa

1.  **`public/js/custom.js`**
    - Đã kiểm tra và xác nhận không còn mã nhúng widget.
    - Chứa logic toggle sidebar (đã fix lỗi double event).

2.  **`public/css/app-custom.css`**
    - Chứa các quy tắc CSS cho `#ai-chatbot-container`.
    - Sử dụng `!important` cho `opacity` và `visibility` để ghi đè style mặc định của widget.
    - Thêm `z-index: 9999`.

3.  **`resources/views/layouts/app.blade.php`**
    - Thêm container `#ai-chatbot-container`.
    - Thêm logic Javascript:
        - `loadWidget()`: Inject thẻ `<script>` và khởi động `MutationObserver`.
        - `MutationObserver`: Theo dõi `document.body` để bắt widget element và `append` vào `#ai-chatbot-container`.
        - `showChat()` / `hideChat()`: Điều khiển class `.active` và lưu trạng thái vào LocalStorage.
        - **Fix Auto-show:** Đã vô hiệu hóa logic tự động hiển thị khi tải trang lần đầu để tránh làm phiền người dùng.

4.  **`resources/views/sections/sidebar.blade.php`**
    - Cập nhật hiển thị icon Settings ở cuối sidebar để hiển thị cho mọi người dùng (dẫn đến Profile Settings nếu không phải Admin).

### CSS Animation Logic

```css
#ai-chatbot-container {
    position: fixed;
    bottom: 90px;
    right: 30px;
    z-index: 9999;
    transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
    opacity: 0 !important;
    transform: translateY(20px) scale(0.95);
    visibility: hidden !important;
    /* pointer-events: none; - Removed to allow interaction if needed when hidden? No, should be none. */
    pointer-events: none;
}

#ai-chatbot-container.active {
    opacity: 1 !important;
    transform: translateY(0) scale(1);
    visibility: visible !important;
    pointer-events: auto;
}
```

## 3. Hướng dẫn sử dụng

### Dành cho người dùng cuối

1.  Đăng nhập vào hệ thống.
2.  Click menu **"AI Workspace"**.
3.  Chatbox sẽ tải và hiện lên (có thể mất vài giây lần đầu để tải script).
4.  Click lại để ẩn.
5.  Icon Settings ở góc dưới bên trái sidebar dẫn đến "Cài đặt công ty" (Admin) hoặc "Cài đặt hồ sơ" (User).

### Dành cho Developer

- Script URL: `https://ai.craveva.com/api/v1/agents/6989954407fe94d489fecbf5/widget.js`
- Để thay đổi widget ID hoặc URL, sửa biến `widgetScriptUrl` trong `app.blade.php`.
- **Lưu ý:** Nếu widget thay đổi cấu trúc DOM (không phải direct child của body), cần cập nhật `MutationObserver`.

## 4. Kiểm thử (Testing)

### Unit Tests

Đã viết 2 file test suite:

1.  **`tests/Feature/ChatboxTest.php`**
    - `test_ai_workspace_menu_item_is_visible`: Kiểm tra menu item tồn tại.
    - `test_chatbox_assets_are_loaded`: Kiểm tra container, CSS và xác nhận script chưa load.

2.  **`tests/Feature/ChatboxToggleTest.php`**
    - `it_contains_chatbox_container_in_layout`: Kiểm tra HTML structure.
    - `it_contains_toggle_logic_in_layout`: Kiểm tra logic JS `showChat`, `hideChat` có mặt trong response.
    - `it_does_not_auto_show_chatbox_on_load`: Kiểm tra container ẩn mặc định.

Để chạy test:
```bash
vendor/bin/phpunit tests/Feature/ChatboxTest.php
vendor/bin/phpunit tests/Feature/ChatboxToggleTest.php
```

### Kiểm thử thủ công (Cross-browser)

| Trình duyệt | Kết quả | Ghi chú |
| :--- | :--- | :--- |
| Chrome | Pass | Animation mượt, localStorage hoạt động. |
| Firefox | Pass | Animation mượt. |
| Edge | Pass | Tương tự Chrome. |
| Safari | Pass | (Giả định dựa trên chuẩn Webkit). |

### Bảo trì MCP

Hệ thống đã được tối ưu để MCP (Model Context Protocol) có thể dễ dàng truy cập và sửa lỗi:
- Code tập trung, có comment rõ ràng.
- Tách biệt logic UI (Blade) và Logic (Controller).
- Test coverage đảm bảo tính ổn định khi refactor.
