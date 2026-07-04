# Laravel 11 — Hướng dẫn ngắn (không cần rành kỹ thuật)

Tài liệu này giúp bạn **biết việc gì đã làm** và **việc gì còn lại** sau khi nâng cấp, **không** cần đọc code.

---

## Đã làm trong code (team kỹ thuật)

| Việc                               | Ý nghĩa đơn giản                                                            |
| ---------------------------------- | --------------------------------------------------------------------------- |
| Nâng Laravel lên **11**            | Framework mới hơn, bảo mật & hiệu năng tốt hơn.                             |
| Sửa migration / thanh toán / test  | Cho hệ thống **chạy ổn** với bản mới, tránh lỗi khi cài mới hoặc chạy lệnh. |
| Tài liệu trong thư mục **`docs/`** | Ghi lại quy trình, an toàn thanh toán, CI test.                             |

**Bạn không cần** tự chạy lệnh phức tạp — kỹ sư / hosting sẽ deploy như thường (`composer install`, `migrate`, v.v.).

---

## Việc bạn / nghiệp vụ nên làm (sau khi lên Staging)

1. **Đăng nhập & dùng thử** các chức năng hay dùng (hóa đơn, đơn hàng, nhân sự, …).
2. **Thanh toán / Stripe / Mollie:** chỉ thử trên **môi trường test** (key test, số tiền nhỏ). Xem **`docs/LARAVEL_11_UPGRADE.md` §7.7** và lệnh `php artisan payment:stripe-verify` (do kỹ thuật chạy).
3. Nếu thấy **lỗi** — ghi lại: màn hình nào, thao tác gì, ảnh chụp / thời gian → gửi team kỹ thuật.

---

## Không lo về “chỉnh DB” vì nâng L11

- Nâng bản Laravel **không** có nghĩa phải sửa tay từng bảng trong MySQL.
- Thay đổi cấu trúc DB (nếu có) sẽ qua **migration** do kỹ thuật chạy khi deploy — giống mọi lần cập nhật hệ thống.

---

## File nào đọc thêm (nếu muốn)

| File                                            | Nội dung                                                                        |
| ----------------------------------------------- | ------------------------------------------------------------------------------- |
| **`docs/LARAVEL_11_UPGRADE.md`**          | Chi tiết kỹ thuật & checklist.                                                  |
| **`docs/CI_PEST_SAFE.md`**            | Chạy test tự động trên GitHub (không ảnh hưởng Staging nếu làm đúng hướng dẫn). |
| **`docs/LARAVEL_11_UPGRADE.md`** | Ghi chú an toàn khi chạy lệnh hệ thống.                                         |

---

_Nếu cần hỗ trợ: nhắc team mở **`docs/LARAVEL_11_UPGRADE.md` §7.6–7.7** (checklist & QA thanh toán)._
