# Quy trình: CI Pest an toàn (không tác động Staging / Hub)

Tài liệu này mô tả **đã triển khai gì trong repo** và **làm sao để không làm hỏng môi trường Staging hay kho code (GitHub)**.

---

## 1. Đã thêm file gì?

| Thành phần                                      | Mục đích                                                                                                                             |
| ----------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------ |
| **`.github/workflows/pest-mysql-manual.yml`**   | Chạy **`composer install` → `migrate` → `pest`** trên **GitHub Actions**, với **MySQL chỉ trong máy ảo CI** (container `mysql:8.0`). |
| **`docs/CI_PEST.md`**                           | Hướng dẫn kỹ thuật (lệnh, biến môi trường, ví dụ YAML).                                                                              |
| **`docs/PROCEDURE_CI_PEST_SAFE.md`** (file này) | Quy trình an toàn + phân biệt CI vs Staging.                                                                                         |

**Không** sửa workflow deploy hiện có (`deploy-craveva-staging.yml`) — deploy Staging vẫn do team điều khiển riêng.

---

## 2. Có làm hỏng Staging không?

**Không**, với thiết kế hiện tại:

- Workflow **chỉ bật khi** bạn vào GitHub → **Actions** → chọn **“Pest (MySQL, manual only)”** → **Run workflow** (`workflow_dispatch`).
- **Không** chạy tự động mỗi lần push/PR → **không** thay đổi hành vi merge, **không** gọi server Staging.
- Job chạy trên **máy ảo của GitHub**; MySQL là **DB rỗng tạm** trong runner, **không** trỏ tới host Staging/production.

→ Staging chỉ bị ảnh hưởng nếu sau này ai đó **cố ý** sửa workflow để `DB_HOST=` trỏ ra server thật — **không nên** làm vậy trên CI công khai.

---

## 3. “Hub” / GitHub có bị gì không?

- **GitHub** chỉ lưu file YAML và chạy workflow khi bạn **Run workflow**.
- **Không** có bước xóa repo, không tự merge, không đụng **Secrets** trừ khi bạn tự thêm vào workflow sau này.

---

## 4. Cách chạy thử (sau khi push lên GitHub)

1. Push nhánh có file `.github/workflows/pest-mysql-manual.yml`.
2. Vào repository trên GitHub → **Actions**.
3. Chọn workflow **“Pest (MySQL, manual only)”**.
4. **Run workflow** → chọn branch → Confirm.

**Lưu ý:** Lần đầu, Pest **có thể fail** nếu test cần **dữ liệu/seed** mà DB trong CI chỉ mới `migrate` (chưa có user, company, …). Đó là **giới hạn dữ liệu test**, không phải lỗi Staging. Xử lý: thêm seed tối thiểu, hoặc chỉnh test/`.env.testing` theo chuẩn nội bộ (xem `docs/CI_PEST.md`).

---

## 5. Khi nào nên bật thêm `push` / `pull_request`?

Chỉ khi team **đã chạy manual ổn định** và muốn kiểm tra tự động mỗi PR. Khi đó thêm trigger vào cùng file hoặc tách file mới — **nên review** để vẫn **không** trỏ DB production.

---

## 6. Liên quan nâng cấp Laravel 11

- **Nâng L11 không bắt buộc chỉnh tay schema DB** — chỉ cần migration trong code khi có package mới (Cashier, Sanctum, …).
- CI ở đây chỉ là **chạy test tự động**; không thay thế bước deploy hay cấu hình Staging.

---

_Xem thêm: `docs/LARAVEL_11_UPGRADE_GUIDE.md` (mục CI / §7.6)._
