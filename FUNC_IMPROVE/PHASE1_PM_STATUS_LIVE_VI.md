# Báo giá gia công (Biomixing) — Tiến độ triển khai

_Cập nhật: **20/05/2026** · Spec đầy đủ: [`PROJECT BIOMIXING/PM_YEU_CAU_TONG_HOP_VI.md`](../PROJECT%20BIOMIXING/PM_YEU_CAU_TONG_HOP_VI.md)_

> **Đọc phần “Tóm tắt dễ hiểu” trước.** Phần dưới là bảng chi tiết cho dev — có thể bỏ qua nếu chỉ cần biết đang làm tới đâu.

---

## Tóm tắt dễ hiểu (cho PM / nội bộ)

### Hệ thống đang làm gì?

Với công ty **gia công** (ví dụ Biomixing), trên màn **Báo giá** nhân viên bán hàng phải:

1. Ghi **công thức** (đường, kem, cà phê… cho **một gói** thành phẩm).
2. Ghi **khách mua bao nhiêu × giá bao nhiêu** (bảng dưới — khác với công thức).
3. Bấm **Gửi duyệt**.
4. **Tổng giám đốc** duyệt công thức (có làm được không).
5. **Phó tổng phụ trách giá** duyệt giá và lãi.
6. Chỉ khi đủ duyệt mới được tạo **Đơn bán hàng**, sau đó mới sang **Sản xuất**.

Công ty **chỉ bán hàng** (không gia công): **tắt** chế độ này trong **Cài đặt → Module Settings** → mục **“Duyệt báo giá gia công (2 cấp)”** → Báo giá vẫn như trước.

**Không làm:** trí tuệ nhân tạo tự kiểm tra công thức; không đổi tên menu “Báo giá” cho cả hệ thống.

---

### Tiến độ chung

**Khoảng 90%** so với yêu cầu Phase 1 (báo giá gia công).

```text
[██████████████████░░] ~90%
```

| Phần việc                            | Mức độ | Giải thích ngắn                                                                         |
| ------------------------------------ | ------ | --------------------------------------------------------------------------------------- |
| Báo giá thường (tạo, gửi, đơn bán)   | ~95%   | Đủ cho công ty chỉ bán hàng                                                             |
| Duyệt Tổng giám đốc + Phó tổng giá   | ~85%   | Gửi duyệt, hai cấp, chặn đơn bán, thông báo, quyền riêng (không fallback “sửa báo giá”) |
| Công thức / nguyên liệu trên báo giá | ~85%   | BOM, sao chép Production, tìm báo giá tương tự, % lãi                                   |
| Một trang gọn (4 khối thông tin)     | ~75%   | Workspace OEM trên chi tiết báo giá (công thức / duyệt / tiền tách cột)                 |

---

### Đã làm được

- Nhập nguyên liệu, Gửi duyệt, Tổng giám đốc / Phó tổng giá, chặn đơn bán, sao chép Production BOM.
- Trạng thái giai đoạn (badge), timeline sự kiện, **Cần sửa lại** + gửi duyệt lại.
- **Bật/tắt theo công ty** trên **Module Settings** (mặc định tắt nếu chưa cấu hình; công ty đã pilot duyệt được giữ bật sau migrate).
- **% lãi tối thiểu** trên **Cài đặt → Finance / Invoice settings** (tab General).
- **Tìm báo giá tương tự** (cùng nguyên liệu).
- **Thông báo** khi gửi duyệt / duyệt / từ chối.
- **Dịch** quyền President / VP trên Roles & Permissions.

---

### Còn nhỏ (không chặn go-live Phase 1)

- ~~Polish PDF báo giá có BOM~~ — **đã có** (partial `pdf-bom-lines`, 2026-05-20).
- Email template riêng đẹp hơn (hiện dùng layout mail chung).
- Intake Estimate Request mở rộng thêm field OEM.

**Sản xuất & kho** = **Phase 2** — xem kế hoạch: [`PHASE2_PM_PLAN_VI.md`](./PHASE2_PM_PLAN_VI.md).

**Chốt Phase 1:** UAT báo giá → duyệt → SO trên tenant Biomixing, rồi mở Phase 2.

---

### Cấu hình nhanh (Admin)

| Việc                                  | Đường dẫn                                                       |
| ------------------------------------- | --------------------------------------------------------------- |
| Bật duyệt 2 cấp cho công ty gia công  | **Settings → Module Settings** → bật **Duyệt báo giá gia công** |
| % lãi tối thiểu Phó tổng giá          | **Settings → Finance settings → General**                       |
| Ai duyệt Tổng giám đốc / Phó tổng giá | **Settings → Roles & Permissions** → Estimates → More           |

Gợi ý: **Admin** = quyền **Duyệt báo giá (Tổng giám đốc)**; **Vice President** = **Duyệt báo giá (Phó tổng giá)**.

---

### Xem thử (local)

`https://craveva-staging.test` · `admin@example.com` / `12345678` · `/account/estimates/{id}`

---

## Chi tiết kỹ thuật (dev)

**Ký hiệu:** ✅ xong · 🟡 một phần · ⬜ chưa

| #         | Yêu cầu                                  |                           |
| --------- | ---------------------------------------- | ------------------------- |
| 1–3, 8    | BOM, submit, copy Production             | ✅                        |
| 4–5       | Margin panel + VP rule + **Settings UI** | ✅                        |
| 6–7, 9–10 | Stage, events, revision, permissions     | ✅                        |
| 11–12     | Similar search + workspace 4 vùng        | ✅ (show); list vẫn badge |
| —         | Module toggle UI                         | ✅                        |
| —         | Notifications                            | ✅                        |
| —         | Lang pack permissions                    | ✅                        |

**Sau migrate:** `php artisan migrate` · bật module cho Biomixing trên Module Settings nếu công ty mới.

**Test local (tiết kiệm token AI):** `.\scripts\test.ps1 phase1` (~20s) hoặc `.\scripts\test.ps1` (full). Chỉ gửi agent phần **FAIL** nếu có.
