# Kế hoạch prototype Production (Biomixing) — tách khỏi demo “chỉ dữ liệu”

**Mục đích:** Trả lời: hệ thống **đang thiếu module Production**; khách cần **chức năng** (không chỉ import file demo). Đây là **phạm vi prototype** (POC) và **ước lượng thời gian**, bám `BIOMIXING_PRODUCTION_DEVELOPMENT_PLAN.md` nhưng **cắt scope** so với MVP/Phase 1 đầy đủ.

**Trạng thái:** Kế hoạch nội bộ — chỉnh lại khi chốt scope với khách và sau spike kỹ thuật.

**Tham chiếu:** `BIOMIXING_PRODUCTION_DEVELOPMENT_PLAN.md`, `2-4-2026_BIOMIXIN_DEMO_PREP_CHECKLIST.md` (demo = dữ liệu + kịch bản; **không** thay cho build module).

---

## 1) Bối cảnh (sau khi đã gửi tài liệu cho PM)

| Thực tế                                                                                          | Ý nghĩa                                                                              |
| ------------------------------------------------------------------------------------------------ | ------------------------------------------------------------------------------------ |
| Hub hiện có **Sales / Order**, **Purchase**, **Warehouse** (đa kho, batch tồn), **Project/Task** | Đủ bán, mua, tồn; **chưa** có domain **lệnh sản xuất / BOM / tiêu RM nhận FG** chuẩn |
| Khách (Biomixing) cần **nhìn thấy luồng sản xuất trong ERP**, không chỉ slide                    | Cần **prototype chức năng** (dù mỏng) hoặc roadmap + demo lai ghép                   |
| File checklist gửi PM là **chuẩn bị dữ liệu demo**                                               | Tách biệt với **dự án build** Production                                             |

---

## 2) Luồng chuẩn Craveva — Production đứng ở đâu? (làm rõ “SO → Product → DO”)

**Không** nên hiểu theo nghiệp vụ là: _Sales Order → Product → Delivery Order_ như một chuỗi thời gian đơn giản.

- **Product** là **danh mục master** (SKU), **không** phải một “bước” giữa SO và DO.
- Luồng **bán** điển hình trong Craveva: **Order (SO)** → (tuỳ cấu hình) **Sales Shipment / DO bán** hoặc **Invoice** → **xuất kho** FG. Chi tiết: `FUNC_LOGIC/QUY_TRINH_PO_DO_SO_INVOICE_WAREHOUSE_VI.md`.

**Chỗ thiếu cho manufacturing:** trước khi **xuất FG** cho khách, cần một lớp **sản xuất**:

1. **Mua RM** (PO) → nhập kho RM (đã có).
2. **Lệnh sản xuất / batch:** đọc **BOM** → **tiêu thụ RM** (theo lô nếu có) → **nhận FG** vào kho (chưa có module riêng — đang mô phỏng bằng Project/task hoặc ngoài hệ thống).
3. **Order (SO)** yêu cầu FG → **DO bán** trừ tồn FG (đã có).

**Tóm lại:** Cần bổ sung **Production (BOM + lệnh SX + movement RM→FG)** giữa **tồn RM** và **giao FG** — không thay thế SO/DO, mà **nối** chúng với thực tế xưởng.

---

## 3) “Prototype” nghĩa là gì trong tài liệu này?

| Khái niệm                         | Mô tả                                                                                                                                                               |
| --------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Demo chỉ dữ liệu** (checklist)  | Import master + Story Pack, chạy kịch bản trên module **có sẵn** (Project task giả lập SX)                                                                          |
| **Prototype chức năng** (bản này) | Có **code mới** (ít nhất: skeleton **Production** hoặc tương đương), **một happy path** có thể bấm được: tạo lệnh SX → tiêu RM / nhận FG (có thể chưa đủ CCP/HACCP) |
| **MVP Production** (theo plan §6) | Phase 0 + 1 đầy đủ hơn: BOM + batch + production order + tích hợp kho **ổn định** — **~10–15 tuần** (+ buffer) trong plan                                           |

Prototype = **cắt** khỏi MVP: ít SKU, ít màn hình, ít validation, **không** CCP cứng, **không** receiving QC đầy đủ, có thể **một tenant / một pilot**.

---

## 4) Phạm vi đề xuất cho prototype (gói “làm được để demo khách”)

**Trong phạm vi (nên có để nói “đã có Production trong ERP”):**

| #   | Hạng mục                                                                                     | Ghi chú                                                                            |
| --- | -------------------------------------------------------------------------------------------- | ---------------------------------------------------------------------------------- |
| P1  | **BOM tối thiểu** (1 FG ↔ N dòng RM, tỷ lệ cố định, có thể 1 version)                        | Có thể 2–3 bảng + UI đơn giản hoặc import                                          |
| P2  | **Production Order / Batch** (1 entity, trạng thái: draft → released → completed)            | Link **order_id** (tuỳ chọn) hoặc chỉ link **product FG + qty**                    |
| P3  | **Tiêu RM + nhận FG** qua **Warehouse** hiện có (`warehouse_product_batches` nếu dùng batch) | Gọi service movement — **không** nhét hết vào `WarehouseController` (theo plan §3) |
| P4  | **Màn hình hoặc flow** đủ để demo 15–20 phút (tạo lệnh → complete → thấy tồn đổi)            | Có thể bỏ báo cáo đẹp                                                              |

**Ngoài phạm vi prototype (để Phase 1–2 sau):**

- CCP gate cứng, rework đầy đủ, receiving QC, quality lock DO, sampling/COA, PRP, audit export — như **§4 Phase 2+** trong `BIOMIXING_PRODUCTION_DEVELOPMENT_PLAN.md`.

---

## 5) Ước lượng thời gian (wall-clock) — prototype

**Giả định:** 1 dev backend **full-time** (hoặc 1.5 nếu có FE nhẹ), PM/BA phản hồi nhanh; scope **một flow pilot** (ví dụ manual mix 250KG) đã chốt; không đổi BOM mỗi tuần.

| Kịch bản                                     | Nội dung                                                                                                                   | Thời gian (lịch)                                              |
| -------------------------------------------- | -------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------- |
| **A — Prototype “mỏng” (nội bộ / kỹ thuật)** | Skeleton `Modules/Production`, BOM + lệnh SX + 1 luồng complete → stock; UI tối thiểu; test happy path                     | **~4–7 tuần**                                                 |
| **B — Prototype “demo được cho khách”**      | Thêm chỉnh UI/readable, gắn **Order** hoặc **Project** cho kịch bản, xử lý vài edge case (thiếu tồn, hủy lệnh), UAT nội bộ | **~7–11 tuần**                                                |
| **So sánh với MVP đầy đủ (plan §6)**         | Phase 0 + 1 **MVP Production** (BOM + batch MVP + production order + tích hợp kho **đủ dùng pilot**)                       | **~10–15 tuần** (+ **2–3 tuần** buffer UAT/deploy trong plan) |

**Giải thích:** Prototype **A/B** ngắn hơn MVP vì **bỏ** phần lớn hoàn thiện sản phẩm (đa version BOM, báo cáo, cứng hóa batch end-to-end, nhiều tenant). Nếu team **0.5 dev** hoặc scope **nhảy**, nhân đôi gần đúng số tuần.

**Buffer an toàn cho PM báo khách:** cộng **+1–2 tuần** cho prototype (UAT/fix), **+2–3 tuần** nếu gần bằng MVP.

---

## 6) Rủi ro làm trễ prototype

- Master **Product** lộn xộn (trùng SKU, thiếu UOM) — trùng với rủi ro trong plan §6.
- **Chưa chốt** một flow pilot (250KG) → dev làm đi làm lại.
- Tích hợp **batch** RM/FG với quy tắc tenant hiện tại cần spike (ít gặp nếu chỉ 1 kho pilot).

---

## 7) Kết luận ngắn (để nói với PM / khách)

1. Hệ thống **thiếu module Production** là đúng; **SO / DO** không thiếu — thiếu **lớp sản xuất** giữa tồn RM và giao FG.
2. **Demo checklist** = chuẩn bị **dữ liệu**; **prototype** = **code** (dù mỏng).
3. **Prototype chức năng** (phạm vi §4): thực tế **~4–11 tuần** tùy độ “demo được”; **MVP đầy đủ** theo plan **~10–15 tuần** (+ buffer) — khác nhau rõ ràng.
4. Sau prototype, roadmap **Phase 2+** (CCP, QC, …) vẫn theo `BIOMIXING_PRODUCTION_DEVELOPMENT_PLAN.md`.

---

_Cập nhật khi chốt scope prototype với khách và sau spike BOM/movement._
