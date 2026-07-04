# Prompt (copy-paste) — migrate toàn bộ sang axios + gom logic tái sử dụng

Dùng prompt dưới đây trong Cursor / ChatGPT khi bạn muốn agent thực hiện migration **theo đúng chuẩn repo** `craveva-staging`.

---

## Prompt tiếng Việt (khuyến nghị)

```
Bạn là senior frontend + Laravel engineer trên repo Craveva ERP (Laravel, Blade, jQuery, nwidart modules).

BỐI CẢNH:
- Client HTTP chuẩn: `resources/js/http/apiClient.js` → `window.apiHttp` (axios), đã được `require` trong `resources/js/main.js`.
- Legacy: `$.easyAjax` trong `public/vendor/helper/helper.js`; nhiều Blade vẫn gọi easyAjax.
- Không sửa `helper.js` toàn cục trong cùng một PR; chỉ thay từng view/module trong scope.
- Sau đổi JS: chạy `pnpm run production` (hoặc `npm run production`) để cập nhật `public/js/main.js`.

ĐỌC TRƯỚC:
- `docs/axios-migration/README.md`
- `docs/axios-migration/AJAX_AUDIT.md`

PHẠM VI (bắt buộc ghi rõ — một PR một vùng):
- [GHI PATH HOẶC MODULE, ví dụ: `resources/views/clients/**` hoặc `Modules/Purchase/Resources/views/**`]

NHIỆM VỤ:

1) MIGRATION easyAjax → apiHttp (axios)
- Với mỗi file Blade trong scope: thay `$.easyAjax({...})` bằng `window.apiHttp.get/post/postForm/postUrlEncoded/delete` (đúng convention trong README).
- Giữ `$.easyBlockUI` / `$.easyUnblockUI` nếu đã có, cho đến khi có overlay thay thế.
- Xử lý lỗi: `catch` dùng `$.handleApiFormError(err)` hoặc pattern tương đương đã có trong codebase.
- Không đổi URL route, không đổi controller trừ khi bắt buộc để fix bug.

2) GOM LOGIC LẶP — TRÁNH LẶP SCRIPT TRÊN MỖI VIEW
- Các pattern lặp (poll import progress, quick action delete, quick action status, serialize form + POST):
  đưa vào **một** (hoặc vài) file trong `resources/js/`:
  - Ví dụ: `resources/js/http/importProgressPoll.js` — export hàm `startImportProgressPoll({ importClassName, getProgressUrl, getExceptionUrl })`.
  - Hoặc `resources/js/erp/quickActions.js` — hàm dùng chung cho bảng.
- Trong Blade: chỉ còn `data-*` attributes + **một** dòng `init` gọi module (hoặc `@push('scripts')` gọi `window.ErpImportPoll.start(...)`).
- Mục tiêu: không nhân bản hàng trăm dòng `<script>` giống nhau → giảm token AI, giảm bundle trùng, dễ test.

3) BUILD
- Đảm bảo import trong `main.js` (hoặc file entry đúng) để các hàm shared được bundle vào `public/js/main.js`.

4) KIỂM TRA
- Liệt kê file đã đổi; smoke test: upload/import, một form POST, một GET modal.

5) CẬP NHẬT TÀI LIỆU TRACKING
- Cập nhật bảng trạng thái trong `docs/axios-migration/README.md` và changelog ngắn nếu có wave mới.

RÀNG BUỘC:
- Không refactor unrelated files.
- Không xóa file trừ khi user yêu cầu rõ ràng.
- Không đụng `vendor/`, `node_modules/`, `bootstrap/cache/`, `storage/framework/`, `storage/logs/`, `app/Exceptions/` (theo rule repo).
```

---

## Prompt tiếng Anh (ngắn)

```
You are migrating a Laravel Blade + jQuery ERP from `$.easyAjax` to `window.apiHttp` (axios) in `resources/js/http/apiClient.js`, bundled via `resources/js/main.js`.

Rules:
- Follow `docs/axios-migration/README.md` conventions (CSRF, postForm for multipart, postUrlEncoded for serialize).
- Do not rewrite `public/vendor/helper/helper.js` globally.
- Extract repeated patterns (import progress polling, quick actions) into `resources/js/**` modules; Blade should only pass `data-*` and call one initializer — avoid duplicating large `<script>` blocks per view.
- Run `pnpm run production` after JS changes.
- Update the status table in `docs/axios-migration/README.md`.

Scope: [INSERT MODULE OR PATH HERE]
```

---

## Gợi ý scope từng lần chạy

- **Một module:** `Modules/Purchase/Resources/views/**`
- **Một khu vực core:** `resources/views/clients/**`
- **Một luồng dùng chung:** chỉ `resources/views/import/process-form.blade.php` + Recruit copy nếu có

Tránh một prompt “một lần hết repo” — dễ vượt context và khó review.
