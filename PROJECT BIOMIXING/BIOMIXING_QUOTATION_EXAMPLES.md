# Ví dụ báo giá gia công — Oldtown & FreshTea

_File này gộp hai ví dụ onboarding dev/BA/PM về **báo giá gia công + công thức (BOM) + dòng bán B2B** trên cùng một Estimate._

**Bối cảnh:** ví dụ minh họa nghiệp vụ, không phải hợp đồng hay khách hàng thật của Biomixing.

**Liên quan:** [`BIOMIXING_COMPANY_PROFILE.md`](./BIOMIXING_COMPANY_PROFILE.md) · [`PM_YEU_CAU_TONG_HOP.md`](./PM_YEU_CAU_TONG_HOP.md) §2 · [`PHASE_BUSINESS_CONTEXT_EXAMPLE.md`](./PHASE_BUSINESS_CONTEXT_EXAMPLE.md) §6

---

## 1. Nguyên tắc chung

Trên một báo giá gia công, có hai phần song song:

| Phần | Lưu ở đâu | Trả lời câu hỏi |
| --- | --- | --- |
| **Công thức / BOM** | `estimate_bom_lines` | Làm một đơn vị sản phẩm tốn nguyên liệu / bao bì bao nhiêu? |
| **Dòng bán B2B** | `estimate_items` | Khách đặt bao nhiêu và trả giá bán bao nhiêu? |

**Hai phần không thay nhau:** BOM = cách làm + cost; dòng bán = thương mại với khách. Sau khi báo giá đủ duyệt, hệ thống mới convert sang Sales Order. Khách không tạo Production Order trực tiếp; xưởng Biomixing lập lệnh sản xuất nội bộ sau SO.

---

## 2. Ví dụ Oldtown — đơn gia công đơn giản

### 2.1 Khách cần gì

| Hạng mục | Giá trị |
| --- | --- |
| Khách | Oldtown White Coffee |
| Yêu cầu | Làm hộ **3.000 gói** cà phê 3-in-1, nhãn/spec của Oldtown |
| Giá bán thỏa thuận | **2,50 USD / gói** |

Đây là đơn **gia công** vì Biomixing sản xuất hộ theo yêu cầu khách, không chỉ bán sản phẩm có sẵn trong kho.

### 2.2 BOM cho 1 gói 150g

| Nguyên liệu | Số lượng / 1 gói | Đơn giá vốn | Thành tiền |
| --- | --- | --- | --- |
| Đường | 50 g | 0,02 USD | 0,02 USD |
| Kem bột | 30 g | 0,03 USD | 0,03 USD |
| Cà phê | 70 g | 0,05 USD | 0,05 USD |
| Bao bì | 1 cái | 0,04 USD | 0,04 USD |
| **Tổng / 1 gói** | | | **0,14 USD** |

### 2.3 Dòng bán B2B

| Mô tả | Số lượng | Đơn giá bán | Thành tiền |
| --- | --- | --- | --- |
| Cà phê 3-in-1 150g | 3.000 gói | 2,50 USD | 7.500 USD |

### 2.4 Margin tham khảo

| Chỉ tiêu | Tính |
| --- | --- |
| Chi phí nguyên liệu | 0,14 × 3.000 = **420 USD** |
| Doanh thu | **7.500 USD** |
| Lãi gộp đơn giản | (7.500 − 420) / 7.500 ≈ **94%** |

- **Phó tổng giám đốc giá:** margin có đủ ngưỡng công ty không.
- **Tổng giám đốc:** có làm được 3.000 gói đúng spec Oldtown không.

### 2.5 Sau khi chốt báo giá

```text
Báo giá (Estimate) — đủ duyệt
    -> Đơn bán (3.000 gói × 2,50 USD)
    -> Lệnh sản xuất (trộn theo BOM, đóng gói)
    -> Giao hàng cho Oldtown
```

### 2.6 Nguyên liệu cho cả đơn

| Nguyên liệu | Tổng cần |
| --- | --- |
| Đường | 150 kg |
| Kem bột | 90 kg |
| Cà phê | 210 kg |
| Bao bì | 3.000 cái |

---

## 3. Ví dụ FreshTea — đơn gia công có COA / nhãn lô

### 3.1 Khách cần gì

| Hạng mục | Giá trị |
| --- | --- |
| Khách | Chuỗi đồ uống **FreshTea** |
| Yêu cầu | Làm hộ **2.000 chai** **BioMix Detox** 350ml, công thức chuẩn Biomixing |
| Giao hàng | **10 ngày** kể từ ngày chốt đơn bán |
| Yêu cầu đặc biệt | **COA** + **nhãn lô** truy xuất được |
| Giá bán đề xuất | **45.000 đ / chai** |

Đây là đơn gia công có áp lực thời gian và cam kết chất lượng/chứng từ.

### 3.2 BOM cho 1 chai 350ml

| Nguyên liệu / bao bì | Số lượng / 1 chai | Đơn giá vốn | Thành tiền |
| --- | --- | --- | --- |
| Premix detox (probiotic) | 15 ml | 2.500 đ | 2.500 đ |
| Nền đồ uống (pha loãng) | 320 ml | 500 đ | 500 đ |
| Chai PET 350ml | 1 chai | 3.000 đ | 3.000 đ |
| Nắp + nhãn trống | 1 bộ | 2.000 đ | 2.000 đ |
| **Tổng NL + bao bì / 1 chai** | | | **8.000 đ** |

Có thể kèm recipe header: số lượng đặt tối thiểu, quy cách bao bì, mã sản phẩm theo đơn đặt hàng của khách, ghi chú COA + nhãn lô.

### 3.3 Dòng bán B2B

| Mô tả | Số lượng | Đơn giá bán | Thành tiền |
| --- | --- | --- | --- |
| BioMix Detox 350ml | 2.000 chai | 45.000 đ | 90.000.000 đ |

### 3.4 Duyệt 2 cấp

Trên Hub, khi bật Phase 1: **Tổng giám đốc** duyệt trước, **Phó tổng giám đốc giá** duyệt sau (`president_review_*` -> `vp_pricing_review_*`).

| Cửa duyệt | Câu hỏi | Ví dụ FreshTea |
| --- | --- | --- |
| **Tổng giám đốc** | Deal này có nên làm không? | Giao 10 ngày + COA + nhãn lô: chấp nhận rủi ro trễ, ca đêm, ảnh hưởng thương hiệu không? |
| **Phó tổng giám đốc giá** | Giá này có đủ biên không? | COGS NL ~8.000 đ/chai; thêm rush + QC/COA ~2.500 đ. Với 45.000 đ/chai, biên gộp đơn giản ~77%; so với policy margin thực tế của tenant. |

**Ví dụ quyết định:**

- Tổng giám đốc: chấp nhận rush + COA cho FreshTea, cho phép VP chốt giá.
- Phó tổng giám đốc giá: giữ 45.000 đ/chai nhưng bỏ chiết khấu 3%, hoặc tăng 46.500 đ/chai nếu policy yêu cầu.

### 3.5 Margin tham khảo

| Chỉ tiêu | Tính |
| --- | --- |
| Chi phí NL + bao bì | 8.000 × 2.000 = **16.000.000 đ** |
| Phân bổ rush + QC/COA | ~2.500 × 2.000 = **5.000.000 đ** |
| Tổng cost tham khảo | **~21.000.000 đ** |
| Doanh thu | **90.000.000 đ** |
| Lãi gộp đơn giản | (90 − 21) / 90 ≈ **77%** |

### 3.6 Luồng trước Sales Order

```text
(1) Sales tạo Estimate — BOM + 2.000 chai + ghi chú COA/nhãn lô + deadline 10 ngày
(2) Tổng giám đốc duyệt hoặc reject + ghi chú
(3) Phó tổng giám đốc giá duyệt hoặc chỉnh giá
(4) Convert Estimate -> Sales Order
```

### 3.7 Sau khi chốt báo giá

```text
Báo giá (Estimate) — đủ duyệt
    -> Đơn bán (2.000 chai × 45.000 đ)
    -> Kế hoạch: kiểm BOM, tồn, mua bổ sung nếu thiếu
    -> Lệnh sản xuất / chạy lô
    -> QC -> cấp COA đúng cam kết
    -> In nhãn lô -> giao FreshTea
```

**Lưu ý pilot:** module Production đã có lô/lệnh SX. COA đầy đủ trên Hub và quality lock chặn giao khi chưa QC là roadmap.

### 3.8 Nguyên liệu cho cả đơn

| Hạng mục | Tổng cần |
| --- | --- |
| Premix detox | 30 l |
| Nền đồ uống | 640 l |
| Chai PET 350ml | 2.000 chai |
| Nắp + nhãn | 2.000 bộ |

---

## 4. So sánh B2B thường, Oldtown và FreshTea

| Nội dung | B2B thường | Oldtown | FreshTea |
| --- | --- | --- | --- |
| Sản phẩm | SKU catalogue | Cà phê custom | Đồ uống detox custom |
| BOM trên báo giá | Không cần | Có | Có |
| Dòng bán | Có | Có | Có |
| Duyệt 2 cấp | Thường không | Có khi bật Phase 1 | Có, nhấn mạnh rush + COA |
| Cam kết COA / nhãn lô | Hiếm | Không nêu | Có |
| Sau chốt | Giao từ tồn | Sản xuất rồi giao | Sản xuất -> QC/COA -> nhãn lô -> giao |
