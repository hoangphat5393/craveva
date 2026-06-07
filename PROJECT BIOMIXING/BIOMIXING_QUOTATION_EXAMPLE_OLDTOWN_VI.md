# Ví dụ báo giá gia công — Oldtown cà phê 3-in-1

_Ví dụ đơn giản minh họa **báo giá gia công + công thức (BOM) + dòng bán B2B** trên cùng một Estimate. Dùng cho onboarding dev/BA/PM._

**Liên quan:** [`BIOMIXING_COMPANY_PROFILE_VI.md`](./BIOMIXING_COMPANY_PROFILE_VI.md) · [`PM_YEU_CAU_TONG_HOP_VI.md`](./PM_YEU_CAU_TONG_HOP_VI.md) §2

---

## 1. Khách cần gì (gia công)

| Hạng mục           | Giá trị                                                   |
| ------------------ | --------------------------------------------------------- |
| Khách              | Oldtown White Coffee                                      |
| Yêu cầu            | Làm hộ **3.000 gói** cà phê 3-in-1, nhãn/spec của Oldtown |
| Giá bán thỏa thuận | **2,50 USD / gói**                                        |

Đây là đơn **gia công** vì Biomixing **sản xuất hộ** theo yêu cầu khách — không chỉ bán sản phẩm có sẵn trong kho (ví dụ EHPurge).

---

## 2. Trên báo giá có hai phần (cùng một Estimate)

### Phần A — Công thức (BOM): cho **1 gói 150g**

Trả lời câu hỏi: _“Làm 1 gói tốn bao nhiêu?”_ (lưu ở `estimate_bom_lines` trên Hub)

| Nguyên liệu      | Số lượng / 1 gói | Đơn giá vốn | Thành tiền   |
| ---------------- | ---------------- | ----------- | ------------ |
| Đường            | 50 g             | 0,02 USD    | 0,02 USD     |
| Kem bột          | 30 g             | 0,03 USD    | 0,03 USD     |
| Cà phê           | 70 g             | 0,05 USD    | 0,05 USD     |
| Bao bì           | 1 cái            | 0,04 USD    | 0,04 USD     |
| **Tổng / 1 gói** |                  |             | **0,14 USD** |

Có thể kèm thông tin recipe header: số lượng đặt tối thiểu, quy cách bao bì, mã sản phẩm theo đơn đặt hàng của khách.

### Phần B — Dòng bán B2B: khách **đặt bao nhiêu × giá bán**

Trả lời câu hỏi: _“Khách trả bao nhiêu?”_ (lưu ở `estimate_items` — phần chuyển sang đơn bán)

| Mô tả              | Số lượng  | Đơn giá bán | Thành tiền |
| ------------------ | --------- | ----------- | ---------- |
| Cà phê 3-in-1 150g | 3.000 gói | 2,50 USD    | 7.500 USD  |

**Hai phần không thay nhau:** BOM = cách làm + cost; dòng bán = thương mại với khách.

---

## 3. Nội bộ tính nhanh (margin tham khảo)

| Chỉ tiêu                                          | Tính                            |
| ------------------------------------------------- | ------------------------------- |
| Chi phí nguyên liệu                               | 0,14 × 3.000 = **420 USD**      |
| Doanh thu (tổng phụ bán)                          | **7.500 USD**                   |
| Lãi gộp (đơn giản, chưa gồm công/điện/vận chuyển) | (7.500 − 420) / 7.500 ≈ **94%** |

- **Phó tổng giám đốc giá:** margin có đủ ngưỡng công ty không → duyệt hoặc yêu cầu tăng giá.
- **Tổng giám đốc:** có làm được 3.000 gói, đúng spec Oldtown không → duyệt công thức / rủi ro.

---

## 4. Sau khi chốt báo giá

```text
Báo giá (Estimate) — đủ duyệt
    → Đơn bán (3.000 gói × 2,50 USD)
    → Lệnh sản xuất (trộn theo BOM, đóng gói)
    → Giao hàng cho Oldtown
```

Khách **không** tạo lệnh sản xuất — khách chỉ chốt **đơn mua**; xưởng Biomixing mới lập lệnh sản xuất nội bộ.

---

## 5. So với báo giá B2B thường

|                  | B2B thường (EHPurge có sẵn) | Ví dụ Oldtown (gia công) |
| ---------------- | --------------------------- | ------------------------ |
| Sản phẩm         | SKU catalogue               | Theo spec khách          |
| BOM trên báo giá | Không cần                   | **Có** (phần A)          |
| Dòng bán         | Có                          | **Có** (phần B)          |
| Duyệt 2 cấp      | Thường không                | Có (khi bật Phase 1)     |
| Sau chốt         | Giao từ tồn                 | Sản xuất rồi giao        |

---

## 6. Nguyên liệu cho cả đơn 3.000 gói (tham khảo)

| Nguyên liệu | Tổng cần  |
| ----------- | --------- |
| Đường       | 150 kg    |
| Kem bột     | 90 kg     |
| Cà phê      | 210 kg    |
| Bao bì      | 3.000 cái |

(Nguồn: [`PM_YEU_CAU_TONG_HOP_VI.md`](./PM_YEU_CAU_TONG_HOP_VI.md) §2)
