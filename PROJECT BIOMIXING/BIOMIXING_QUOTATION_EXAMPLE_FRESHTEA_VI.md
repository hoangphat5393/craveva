# Ví dụ báo giá gia công — FreshTea BioMix Detox 350ml

_Ví dụ minh họa **báo giá gia công + công thức (BOM) + dòng bán + duyệt 2 cấp + cam kết COA / nhãn lô** trên cùng một Estimate. Dùng cho onboarding dev/BA/PM._

**Bối cảnh giả định** — không phải hợp đồng hay khách hàng thật của Biomixing.

**Liên quan:** [`BIOMIXING_COMPANY_PROFILE_VI.md`](./BIOMIXING_COMPANY_PROFILE_VI.md) · [`PHASE_BUSINESS_CONTEXT_EXAMPLE.md`](./PHASE_BUSINESS_CONTEXT_EXAMPLE.md) §6 · [`BIOMIXING_QUOTATION_EXAMPLE_OLDTOWN_VI.md`](./BIOMIXING_QUOTATION_EXAMPLE_OLDTOWN_VI.md)

---

## 1. Khách cần gì (gia công)

| Hạng mục         | Giá trị                                                                 |
| ---------------- | ----------------------------------------------------------------------- |
| Khách            | Chuỗi đồ uống **FreshTea** (tên ví dụ trong doc)                        |
| Yêu cầu          | Làm hộ **2.000 chai** **BioMix Detox** 350ml, công thức chuẩn Biomixing |
| Giao hàng        | **10 ngày** kể từ ngày chốt đơn bán (thống nhất trong thương mại)       |
| Yêu cầu đặc biệt | **COA** (giấy phân tích kèm hàng) + **nhãn lô** truy xuất được          |
| Giá bán đề xuất  | **45.000 đ / chai** (sale draft)                                        |

Đây là đơn **gia công** có **áp lực thời gian** và **cam kết chất lượng/chứng từ** — khác với bán SKU catalogue có sẵn (ví dụ EHPurge từ kho).

---

## 2. Trên báo giá có hai phần (cùng một Estimate)

### Phần A — Công thức (BOM): cho **1 chai 350ml**

Trả lời câu hỏi: _“Làm 1 chai tốn bao nhiêu?”_ (lưu ở `estimate_bom_lines` trên Hub)

| Nguyên liệu / bao bì          | Số lượng / 1 chai | Đơn giá vốn | Thành tiền  |
| ----------------------------- | ----------------- | ----------- | ----------- |
| Premix detox (probiotic)      | 15 ml             | 2.500 đ     | 2.500 đ     |
| Nền đồ uống (pha loãng)       | 320 ml            | 500 đ       | 500 đ       |
| Chai PET 350ml                | 1 chai            | 3.000 đ     | 3.000 đ     |
| Nắp + nhãn trống              | 1 bộ              | 2.000 đ     | 2.000 đ     |
| **Tổng NL + bao bì / 1 chai** |                   |             | **8.000 đ** |

Có thể kèm recipe header: số lượng đặt tối thiểu, quy cách bao bì, mã sản phẩm theo đơn đặt hàng của khách, ghi chú **COA + nhãn lô** trên báo giá.

### Phần B — Dòng bán B2B: khách **đặt bao nhiêu × giá bán**

Trả lời câu hỏi: _“Khách trả bao nhiêu?”_ (lưu ở `estimate_items` — phần chuyển sang đơn bán)

| Mô tả              | Số lượng   | Đơn giá bán | Thành tiền   |
| ------------------ | ---------- | ----------- | ------------ |
| BioMix Detox 350ml | 2.000 chai | 45.000 đ    | 90.000.000 đ |

**Hai phần không thay nhau:** BOM = cách làm + cost; dòng bán = thương mại với khách.

---

## 3. Duyệt 2 cấp — hai câu hỏi khác nhau

Trên Hub (khi bật Phase 1): **Tổng giám đốc** duyệt trước, **Phó tổng giám đốc giá** duyệt sau (`president_review_*` → `vp_pricing_review_*`).

| Cửa duyệt                 | Câu hỏi                                                     | Ví dụ FreshTea                                                                                                                                                                                                                          |
| ------------------------- | ----------------------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Tổng giám đốc**         | Deal này **có nên làm không?** (rush, uy tín, COA, nhãn lô) | Giao **10 ngày** + COA + nhãn lô: chấp nhận ca đêm / rủi ro trễ không? Khách chuỗi lớn — lỗi lô ảnh hưởng thương hiệu ra sao? _Không_ quyết giá 45.000 vs 46.500 đ/chai.                                                                |
| **Phó tổng giám đốc giá** | Giá này **có đủ biên không?**                               | COGS NL ~8.000 đ/chai; cộng phân bổ rush + QC/COA ~2.500 đ → ~10.500 đ. Với **45.000 đ/chai**, biên gộp đơn giản ~77% — nhưng policy công ty có thể yêu cầu tối thiểu **22%** sau mọi chi phí. Có bỏ chiết khấu 3% hoặc tăng giá không? |

**Ví dụ quyết định:**

- Tổng giám đốc: _“Chấp nhận rush + COA cho FreshTea; cho phép Phó tổng giám đốc giá chốt số.”_
- Phó tổng giám đốc giá: _“Giữ 45.000 đ/chai nhưng bỏ chiết khấu 3%”_ hoặc _“Tăng 46.500 đ/chai mới đủ biên.”_

---

## 4. Nội bộ tính nhanh (margin tham khảo)

| Chỉ tiêu                    | Tính                             |
| --------------------------- | -------------------------------- |
| Chi phí NL + bao bì         | 8.000 × 2.000 = **16.000.000 đ** |
| Phân bổ rush + QC/COA (ước) | ~2.500 × 2.000 = **5.000.000 đ** |
| Tổng cost tham khảo         | **~21.000.000 đ**                |
| Doanh thu (tổng phụ bán)    | **90.000.000 đ**                 |
| Lãi gộp (đơn giản)          | (90 − 21) / 90 ≈ **77%**         |

Số trên **minh họa** — Phó tổng giám đốc giá so với **policy margin** thực tế của tenant, không chỉ BOM nguyên liệu.

---

## 5. Luồng trên Hub (trước đơn bán)

```text
(1) Sale tạo Estimate — BOM + 2.000 chai + ghi chú COA/nhãn lô + deadline 10 ngày
(2) Tổng giám đốc duyệt (approve / reject + ghi chú)
(3) Phó tổng giám đốc giá duyệt (chỉnh giá nếu cần)
(4) Convert Estimate → Đơn bán (Sales Order)
```

Khách **chưa** có đơn bán khi báo giá còn draft; SO chỉ sau khi đủ duyệt theo policy.

---

## 6. Sau khi chốt báo giá (xưởng + QC)

```text
Báo giá (Estimate) — đủ duyệt
    → Đơn bán (2.000 chai × 45.000 đ)
    → Kế hoạch: kiểm BOM, tồn, mua bổ sung nếu thiếu
    → Lệnh sản xuất / chạy lô (2.000 chai)
    → QC → cấp COA đúng cam kết
    → In nhãn lô (batch label) → giao FreshTea
```

**Lưu ý pilot:** module Production đã có lô / lệnh SX; **COA đầy đủ trên Hub** và **quality lock** chặn giao khi chưa QC là **roadmap** (xem [`BIOMIXING_COMPANY_PROFILE_VI.md`](./BIOMIXING_COMPANY_PROFILE_VI.md) §4).

Khách **không** tạo lệnh sản xuất — xưởng Biomixing vận hành nội bộ sau SO.

---

## 7. So với Oldtown và B2B thường

|                       | B2B thường    | Oldtown       | FreshTea (ví dụ này)             |
| --------------------- | ------------- | ------------- | -------------------------------- |
| Sản phẩm              | SKU catalogue | Cà phê custom | Đồ uống detox custom             |
| BOM trên báo giá      | Không cần     | Có            | Có                               |
| Dòng bán              | Có            | Có            | Có                               |
| Duyệt 2 cấp           | Thường không  | Có            | Có — **nhấn mạnh** rush + COA    |
| Cam kết COA / nhãn lô | Hiếm          | Không nêu     | **Có**                           |
| Sau chốt              | Giao tồn      | SX → giao     | SX → **QC/COA** → nhãn lô → giao |

---

## 8. Nguyên liệu cho cả đơn 2.000 chai (tham khảo)

| Hạng mục       | Tổng cần             |
| -------------- | -------------------- |
| Premix detox   | 30 l (15 ml × 2.000) |
| Nền đồ uống    | 640 l                |
| Chai PET 350ml | 2.000 chai           |
| Nắp + nhãn     | 2.000 bộ             |

(Nguồn khung: [`PHASE_BUSINESS_CONTEXT_EXAMPLE.md`](./PHASE_BUSINESS_CONTEXT_EXAMPLE.md) §6.1–6.4)
