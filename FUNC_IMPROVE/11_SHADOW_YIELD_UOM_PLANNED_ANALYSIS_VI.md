# Phân tích: Shadow mode, Yield/UOM và `planned_quantity` (chưa triển khai bừa bãi)

| Thuộc tính                           | Giá trị                                                                                                                                                                                         |
| ------------------------------------ | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Mục đích**                         | Gom phân tích kỹ thuật/nghiệp vụ về `planned_quantity_shadow`, đổi UOM, hệ số yield — tách khỏi tài liệu mapping proposal để tránh hiểu nhầm là đã “go-live” mandatory.                         |
| **Tham chiếu proposal**              | `PROJECT BIOMIXING/2-4-2026_Biomixing_Proposal_CravevaERP_Formatted.pdf`                                                                                                                        |
| **Trạng thái vận hành (2026-05-07)** | **Phase 1 + Phase 2 của Biomixing được chốt trên `planned_quantity` hiện tại** (định mức × số lượng TP kế hoạch từ BOM snapshot). Shadow/Yield/UOM chỉ bật sau khi có **xác nhận có chủ đích**. |

---

## Lưu ý bắt buộc (governance)

1. **Không tự ý bật** `production.phase2.yield_uom_shadow_enabled = true` trên staging/production cho đến khi PM + Tech Lead (hoặc chủ dự án được ủy quyền) **ghi nhận bằng văn bản** sau khi đánh giá rủi ro tồn kho và UAT.
2. Shadow mode là **công cụ đội triển khai** (so sánh old/new), **không** phải yêu cầu bắt buộc trong proposal dưới dạng tên cột cụ thể.
3. Việc **enforce** công thức mới làm mặc định cho post kho (`planned_quantity` thay thế hoàn toàn) là **change lớn**, cần cutover plan + rollback flag; **không làm** trong scope “đóng Phase 1–2” nếu chưa được chốt.

---

## 1) `planned_quantity` (cách đang dùng — chuẩn hiện tại)

- Một Production Order chỉ chọn **một BOM**.
- `planned_quantity` trên dòng tiêu hao RM = `quantity_per_fg_unit` (trong snapshot) × `bom_snapshot_planned_quantity`.
- Đây là con số dùng cho **vận hành thật**: gán lô, post outbound RM, cân đối với FG policy, v.v.
- **Lưu ý (2026-05-20):** Post outbound phải **quy đổi ĐVT** qua `unit_id` — gap riêng, **không** do shadow: [`15_PRODUCTION_OUTBOUND_UOM_GAP_VI.md`](./15_PRODUCTION_OUTBOUND_UOM_GAP_VI.md).

---

## 2) `planned_quantity_shadow` và “công thức song song”

- **Không phải** hai BOM khác nhau; là **hai cách tính planned** từ **cùng một BOM**:
    - Cũ/`planned_quantity`: nhân đơn giản như trên.
    - Mới/`planned_quantity_shadow` (khi bật flag): đổi về đơn vị cơ sở + điều chỉnh yield, rồi nhân số TP kế hoạch — chủ yếu để **đối chiếu**, tránh bật thẳng lên post kho khi mapping UOM/yield chưa ổn định.

---

## 3) Proposal có nhắc Yield/UOM không?

- **Có** ý nghĩa nghiệp vụ: tính đúng định mức theo đơn vị và hao hụt.
- **Không** bắt buộc phải đi theo đúng tên kỹ thuật `planned_quantity_shadow` / `yield_uom_shadow_enabled`; đó là **quyết định triển khai nội bộ**.

---

## 4) Vì sao không bật thẳng logic mới?

1. Bảo toàn luồng B2B: `SO → DO → Invoice`, `PO → GRN → Bill`.
2. Tránh sai lệch tồn kho trên nhiều tenant khi dữ liệu quy đổi chưa chuẩn.
3. Cho phép PM/QA so sánh old/new trước khi enforce.

### 4.1 Ví dụ A — ảnh hưởng `PO → GRN → Bill`

- Định mức cũ: 100 kg RM; công thức mới (Yield/UOM) ra 120 kg nhưng mapping chưa ổn; hệ thống enforce ngay số mới.
- Hậu quả: trừ RM quá tay → tồn ảo thấp → PO mua bù → GRN/Bill đúng kỹ thuật nhưng quyết định sai.

### 4.2 Ví dụ B — ảnh hưởng `SO → DO → Invoice`

- FG receipt/tồn sai do UOM/yield → Sales thấy tồn sai → ship chậm/chặn sai hoặc commit giao không đúng thực tế → trễ invoice.

**Kết luận:** Rủi ro nằm ở **số tồn đầu vào**, không phải “route hỏng”.

---

## 5) Điều kiện nên cân nhắc chuyển từ shadow sang enforce (chỉ khi đã được phê duyệt)

- Test High/Critical trên staging (matrix: `FUNC_TEST/01_BIOMIXING_TEST_MATRIX_VI.md`).
- Chênh lệch old/new trong ngưỡng business chấp nhận.
- UAT có biên bản signed-off.
- Rollback bằng feature flag và quy trình vận hành đã rehearse.

---

## 6) Gợi ý một đoạn trình bày với stakeholder

“Phần định mức đa đơn vị và hao hụt nằm trong hướng chuẩn hóa dài hạn. Hiện tại hệ thống đang chạy chuẩn `planned_quantity` theo BOM đã duyệt. Nếu cần bật so sánh song song (shadow), bên kỹ thuật sẽ chỉ bật sau khi PM/kho xác nhận dữ liệu UOM/yield đã sẵn sàng và UAT đạt.”

---

## 7) Tham chiếu cấu hình

- File: `Modules/Production/Config/config.php`
- Key: `production.phase2.yield_uom_shadow_enabled` — **mặc định repo: `false`** (chỉ bật `true` khi có xác nhận).
