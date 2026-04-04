# Kế hoạch nâng axios (0.21 → 1.x) — an toàn, không làm vỡ gọi HTTP

Mục tiêu: nâng `axios` trên **build Laravel Mix** (`package.json` gốc) lên **1.x** để giảm rủi ro bảo mật, đồng thời giữ ổn định mọi luồng gọi API phía trình duyệt.

## 1) Hiện trạng trong repo (đã rà soát)

| Vị trí                           | Vai trò                                                                                                                                         |
| -------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------- |
| `package.json` (root)            | `axios` nằm trong **`devDependencies`** (^0.21.4) — dùng khi **webpack/mix** bundle `resources/js/*.js`.                                        |
| `resources/js/bootstrap.js`      | `require('axios')`, gán `window.axios`, set header `X-Requested-With`. Đây là **điểm tích hợp chính** với Mix.                                  |
| `resources/js/app.js`            | `require('./bootstrap')` — kéo axios vào bundle `public/js/app.js` sau khi build.                                                               |
| `Modules/Warehouse/package.json` | **Đã có** `axios` ^1.x (Vite) — **kênh build riêng**, không trùng lockfile root trừ khi team đồng bộ có chủ đích.                               |
| Blade / `resources`              | Không thấy chuỗi `axios` / `window.axios` trong `.blade.php` (có thể một phần UI dùng `$.ajax` hoặc `fetch`); vẫn cần **smoke test** sau build. |

Kết luận: rủi ro chính là **bundle đã compile** (`public/js/app.js`) và mọi script global dùng `window.axios` (nếu có trong asset cũ hoặc inline không match grep).

## 2) Vì sao nâng 1.x thường ít “vỡ” hơn so với đổi framework

- API công khai của axios (`get/post/put/delete`, `defaults`, interceptors, `then/catch`) **giữ tương thích rộng** từ 0.2x sang 1.x cho code thông thường.
- Phần dễ lệch: **TypeScript types**, **ESM default export**, hoặc phụ thuộc vào hành vi nội bộ đã bỏ (hiếm trong code chỉ `require` + gọi chuẩn).

## 3) Phiên bản mục tiêu

- Đặt trong `package.json` (root, `devDependencies`): **`"axios": "^1.7.0"`** (hoặc `^1.6.8` nếu muốn bảo thủ — cùng dòng 1.6+ vẫn nhận patch bảo mật).
- Không ép lên 2.x (chưa cần; 1.x là LTS thực tế của hệ sinh thái Laravel/Mix).

## 4) Quy trình thực hiện (theo phase)

### Phase A — Chuẩn bị (local / nhánh feature)

1. Tạo nhánh: `feature/axios-1-upgrade` (hoặc tương đương).
2. Chụp baseline:
    - `pnpm list axios`
    - `pnpm why axios`
3. (Tùy chọn) Tìm mọi chỗ dùng axios/global:
    - `rg "axios|window\\.axios" resources public Modules --glob "*.{js,vue,blade.php}"`
4. Đảm bảo build hiện tại chạy được: `pnpm run production` (hoặc `development`) **trước** khi đổi version — để so sánh diff bundle nếu cần.

### Phase B — Đổi dependency

1. Sửa `package.json` (root): `devDependencies` → `"axios": "^1.7.0"`.
2. Chạy: `pnpm install` (cập nhật `pnpm-lock.yaml`).
3. Kiểm tra peer/duplicate:
    - `pnpm why axios`
    - Nếu có **hai phiên bản axios** trong một bundle, cân nhắc `pnpm.overrides` (chỉ khi thực sự conflict — thường Mix chỉ cần một tree).

### Phase C — Build và kiểm tra tĩnh

1. `pnpm run production` (bắt buộc cho staging/live vì đây là asset đã minify).
2. Kiểm tra file output tồn tại và thời gian sửa đổi mới: `public/js/app.js` (và các entry khác trong `webpack.mix.js` nếu import bootstrap).
3. Mở nhanh `public/js/app.js` (bản minify): tìm chuỗi `axios` hoặc version marker nếu cần (xác nhận bundle mới).

### Phase D — Kiểm thử chức năng (bắt buộc)

Ưu tiên các màn **có gọi API async** sau khi load trang (Echo, form AJAX, module dùng global):

1. Đăng nhập / logout.
2. Một màn có submit không reload full page (nếu có).
3. Bất kỳ tính năng biết dùng `window.axios` (nếu team xác nhận).
4. Module **Warehouse** (Vite): build riêng `pnpm run build` trong module nếu deploy script có bước đó — đảm bảo không ghi đè nhầm dependency.

### Phase E — Staging trước live

1. Deploy code + `pnpm-lock.yaml` + asset build mới (hoặc build trên CI rồi deploy artifact — theo quy trình team).
2. Trên staging: hard refresh (Ctrl+F5), kiểm tra Console không lỗi `axios is not defined`.
3. Xem log Laravel nếu có lỗi 419/401 do CSRF (ít khi đổi do axios vẫn gửi header chuẩn nếu giữ `bootstrap.js`).

### Phase F — Live (hub)

1. Backup + maintenance window (theo `docs/SERVER_RUNBOOK_VI.md` mục hub go-live nếu áp dụng).
2. Deploy giống staging; smoke test lại tối thiểu 15 phút.

## 5) Dấu hiệu cần rollback

- Lỗi console: `axios is not a function`, `Cannot read properties of undefined`.
- Hàng loạt request fail sau deploy (so với staging cùng commit).
- **Rollback**: khôi phục commit trước + deploy lại asset cũ (`public/js/app.js` từ bản build trước nếu team lưu artifact).

## 6) Ghi chú đồng bộ với module Warehouse

- `Modules/Warehouse` đã dùng axios 1.x trong `package.json` riêng — **không bắt buộc** trùng patch với root, nhưng nên **ghi rõ trong README** phiên bản axios từng kênh (Mix vs Vite) để tránh nhầm khi audit bảo mật.

## 7) Checklist nhanh (copy khi làm việc)

- [ ] Đổi `axios` trong `package.json` root → `^1.7.0`
- [ ] `pnpm install`
- [ ] `pnpm run production`
- [ ] Smoke test trình duyệt (login + 1–2 màn AJAX)
- [ ] Deploy staging → smoke test
- [ ] Deploy live → smoke test

---

## 8) Đã thực hiện (local)

- `package.json` (root): `axios` ^0.21.4 → **^1.7.0** (pnpm resolve **1.13.6**).
- `pnpm install` + **`pnpm run production`** → Mix **Compiled Successfully**; cập nhật `public/js/app.js` (bundle có axios 1.x).

## 9) Push GitHub → staging

Cần commit và push các file sau (để staging `git pull` khớp đúng asset đã build):

- `package.json`
- `pnpm-lock.yaml`
- `public/js/app.js` (và các file JS/CSS public đã đổi sau `mix --production` nếu có)

Trên staging sau khi pull:

- Nếu **không** build trên server: chỉ cần pull + `php artisan optimize:clear` (tùy policy).
- Nếu **có** build trên server: `pnpm install` + `pnpm run production` (đảm bảo cùng phiên bản Node/pnpm với local).

---

_Cập nhật lần đầu: theo rà soát source và cấu trúc Mix/Warehouse hiện tại._
