# P0 — Mini UAT 3 luồng gốc (template biên bản)

Ngày: **\_\_\_\_**  
Tenant / công ty pilot: **\_\_\_\_**  
Người thực hiện: **\_\_\_\_**  
Môi trường: `local` / `staging` / URL: **\*\***\_\_\_\_**\*\***

Hướng dẫn: đánh dấu **Pass / Fail / N/A**; ghi **ISS-xxx** nếu lỗi; mức độ **S1–S3** (S1 = chặn go-live).

**Trước khi chạy UI (Dev):** smoke route + wiring hai chiều trace — `php artisan test --compact tests/Feature/P0BiomixingAutomatedEvidenceTest.php` (không thay biên bản Pass/Fail thủ công dưới đây).

---

## Luồng A — Estimate → Sales Order

| Bước | Mô tả ngắn                                        | Kết quả | Ghi chú / Issue |
| ---- | ------------------------------------------------- | ------- | --------------- |
| A1   | Mở báo giá / estimate, điền dòng hàng             |         |                 |
| A2   | Duyệt nội bộ (nếu có) + chuyển trạng thái phù hợp |         |                 |
| A3   | Chuyển / tạo Sales Order từ báo giá               |         |                 |
| A4   | Kiểm tra SO hiển thị đúng số dòng, giá, khách     |         |                 |

**Kết luận luồng A:** Pass / Fail — Ngày: **\_\_\_\_**

---

## Luồng B — Sales Order → DO → Invoice

| Bước | Mô tả ngắn                                                             | Kết quả | Ghi chú / Issue |
| ---- | ---------------------------------------------------------------------- | ------- | --------------- |
| B1   | Từ SO tạo / xác nhận Delivery Order (theo luồng công ty)               |         |                 |
| B2   | Reserve / ship / invoice theo cấu hình warehouse (shipment vs invoice) |         |                 |
| B3   | Kiểm tra trừ tồn đúng, không double outbound                           |         |                 |
| B4   | Invoice khớp phần giao (số lượng / dòng)                               |         |                 |

**Kết luận luồng B:** Pass / Fail — Ngày: **\_\_\_\_**

---

## Luồng C — PO → GRN → Bill

| Bước | Mô tả ngắn                                                   | Kết quả | Ghi chú / Issue |
| ---- | ------------------------------------------------------------ | ------- | --------------- |
| C1   | Tạo PO, nhận hàng (GRN / DO nhập theo tên màn hình thực tế)  |         |                 |
| C2   | Kiểm tra nhập kho + batch (nếu dùng)                         |         |                 |
| C3   | Tạo bill / hóa đơn mua khớp PO/GRN                           |         |                 |
| C4   | Không nhập đôi khi cấu hình inbound conflict (nếu test được) |         |                 |

**Kết luận luồng C:** Pass / Fail — Ngày: **\_\_\_\_**

---

## Tổng kết

| Mục                                     | Giá trị    |
| --------------------------------------- | ---------- |
| Số issue mở                             |            |
| Lỗi S1 (chặn)                           |            |
| Đủ điều kiện pilot Biomixing phase 1–2? | Có / Không |

Chữ ký BA: **\_\_\_\_** Chữ ký QA/PM: **\_\_\_\_**
