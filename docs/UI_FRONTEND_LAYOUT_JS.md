# Frontend UI — Layouts & Loaded JS (Local Notes)

_Tên file: `UI_FRONTEND_LAYOUT_JS.md` (trước đây: `FRONTEND_UI.md`)._

Tài liệu này ghi chú nhanh cấu trúc giao diện (Blade layouts) và các file JS/CSS được load theo từng nhóm màn hình trong Craveva.

---

## 1. Nhóm giao diện Public / Landing / Signup (Super Admin)

Trong project hiện có 2 “theme/layout” public khác nhau cho trang landing/signup:

### 1.1 SaaS theme

- **Layout:** `resources/views/super-admin/layouts/saas-app.blade.php`
- **Signup page:** `resources/views/super-admin/saas/register.blade.php`
- **JS thường được load bởi layout:**
    - `public/saas/js/main.js` (theme js)
    - `public/front/plugin/helper/helper.js` (các hàm jQuery tiện ích: easyAjax/easyBlockUI/easyUnblockUI/...)

### 1.2 Front theme

- **Layout:** `resources/views/super-admin/layouts/front-app.blade.php`
- **Signup page:** `resources/views/super-admin/front/register.blade.php`
- **JS thường được load bởi layout:**
    - `public/front/js/...` (theme js)
    - `public/front/plugin/helper/helper.js` (các hàm jQuery tiện ích: easyAjax/easyBlockUI/easyUnblockUI/...)

### 1.3 Ghi chú quan trọng (vì sao signup từng “đứng”)

- Một số template signup trước đây gọi `window.apiHttp.postUrlEncoded(...)`.
- `window.apiHttp` được khai báo từ bundle `public/js/main.js` (build từ `resources/js/main.js`).
- Các layout public (SaaS/Front) thường **không load** `public/js/main.js` để tránh xung đột với `saas/js/main.js` hoặc `front/js/...`.
- Vì vậy nếu signup page gọi `window.apiHttp` thì JS sẽ lỗi, UI bị kẹt ở trạng thái Loading.

Giải pháp đang áp dụng ở local: sử dụng `$.easyAjax(...)` (có sẵn trong `public/front/plugin/helper/helper.js`) cho các form signup.

---

## 2. Nhóm giao diện Auth (Login / Forgot / ... dùng component)

- **Component layout:** `resources/views/components/auth.blade.php`
- **JS được load:**
    - `public/vendor/jquery/jquery.min.js`
    - `public/vendor/bootstrap/js/bootstrap.bundle.min.js`
    - `public/js/main.js` (bundle app)
    - `public/vendor/helper/helper.js` (easyAjax + helpers)

Lưu ý: Component này có sẵn `document.loading = '@lang('app.loading')'` để hiển thị spinner text nhất quán.

---

## 3. Nhóm giao diện App (sau khi login)

Các trang trong hệ thống (account/admin) sẽ có layout riêng (không ghi đầy đủ ở đây). Nguyên tắc chung:

- Thường dùng bundle `public/js/main.js` (axios / api client / app behaviors)
- Và/hoặc `public/vendor/helper/helper.js` tùy màn hình

---

## 4. Khi gặp lỗi Signup nhưng “không biết lỗi gì”

Hãy mở DevTools → tab Network → request `signup` và xem Response/Preview.

Ví dụ lỗi thường gặp:

- `The email has already been taken.` (email đã tồn tại)
- `The password confirmation does not match.` (password và confirm password không khớp)

Khi gặp các lỗi này, cần:

- dùng email chưa tồn tại trong DB
- nhập password và confirm password giống nhau

---

## 5. Đồng nhất form / bảng / trạng thái (Hub)

Đặc tả UI/UX khi phát triển tính năng mới (list/create/edit, trạng thái nhị phân **và** trạng thái đa giá trị inline kiểu Orders): **`UI_BACKEND_UX_STANDARD.md`**.

Khi có yêu cầu _đồng nhất UI/UX_ trên view mới hoặc refactor, bám file đó trước khi tự đặt class/màu riêng lẻ.
