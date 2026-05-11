# Production và “tích hợp theo domain” — Giải thích + Module bật/tắt (Craveva)

**Mục đích:** Làm rõ khái niệm _domain_, _tích hợp theo domain_, và câu hỏi: _“Module chưa bật thì gắn vào làm gì?”_ trong bối cảnh ERP Craveva (Laravel, module theo tenant).

---

## 1. “Domain” ở đây là gì? (không phải thuật ngữ hàn lâm)

Trong tài liệu trước, **domain** dùng theo nghĩa **phân chia trách nhiệm nghiệp vụ**:

| Domain (vùng trách nhiệm)    | Ví dụ trong Craveva             | Việc chính                             |
| ---------------------------- | ------------------------------- | -------------------------------------- |
| **Kho (Inventory)**          | Module **Warehouse**            | Tồn, lô/batch, nhập/xuất/chuyển kho    |
| **Mua (Procurement)**        | Module **Purchase**             | PO, nhận hàng từ NCC                   |
| **Bán / đơn hàng**           | Sales / **Orders**              | Nhu cầu, cam kết giao                  |
| **Sản xuất (Manufacturing)** | Module **Production** (đề xuất) | Lệnh sản xuất, batch, bước/CCP, rework |
| **Giao hàng**                | **Delivery Order** (core)       | Xuất giao, khóa theo QC (khi cấu hình) |

**“Tích hợp theo domain”** nghĩa là:

- Code **Production** chỉ giữ logic **thuộc sản xuất** (tạo batch, ghi bước, CCP, rework).
- Khi cần **trừ tồn / nhận thành phẩm**, Production **gọi** lớp dịch vụ thuộc **Warehouse** (một API nội bộ / service đã thống nhất), **không** copy công thức tính tồn vào trong module Production.

→ Đây là **cách tổ chức mã nguồn** để tránh “một controller làm hết mọi thứ”, **không** có nghĩa là “module không bật vẫn tự chạy”.

---

## 2. Module **chưa bật** (inactive) — gắn vào làm gì?

Trên Craveva, mỗi **company (tenant)** có thể **bật/tắt** từng module trong cấu hình.

**Quy tắc thực tế:**

| Tình huống             | Ý nghĩa                                                                                                                                                                                                |
| ---------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| **Warehouse chưa bật** | Không có (hoặc không dùng) nghiệp vụ kho đầy đủ → Production **không thể** hoàn chỉnh luồng “tiêu RM / nhận FG” theo chuẩn ERP. Gắn “link” vào Warehouse **không có tác dụng vận hành** cho tenant đó. |
| **Purchase chưa bật**  | Vẫn có thể nhập tồn bằng cách khác (tùy sản phẩm), nhưng **không** có PO → receiving QC chuẩn thường gắn với Purchase → một phần luồng Biomixing **thiếu**.                                            |
| **Module bật**         | Khi đó “tích hợp theo domain” mới **có chỗ đứng**: Production gọi đúng service của module đang active.                                                                                                 |

**Kết luận ngắn:**  
**Không** thiết kế Production “gắn vào module đang tắt” như thể vẫn chạy được. Thiết kế đúng là:

1. **Khai báo phụ thuộc (dependency):** Ví dụ: _Để dùng Production đầy đủ, tenant phải bật **Warehouse** (và thường là **Purchase** nếu mua nguyên liệu trong hệ thống)._
2. **Khi cài / bật Production:** Kiểm tra (hoặc cảnh báo) các module liên quan; nếu thiếu thì **ẩn** tính năng hoặc **chặn** thao tác (ví dụ không cho “Complete batch” nếu không có Warehouse).

---

## 3. “Tích hợp theo domain” vs “Module Laravel”

| Khái niệm                                   | Giải thích                                                                                                                                    |
| ------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------- |
| **Module Laravel** (`Modules/Warehouse`, …) | Gói code + route + migration — **có thể** cài nhưng **tenant chưa bật** thì không vào menu / không dùng được đầy đủ.                          |
| **Domain layer**                            | Cách **chia trách nhiệm** trong code: `Production` không chứa SQL cập nhật `warehouse_product_stock` trực tiếp mà gọi `StockMovementService`. |

→ **Bật module** = điều kiện **vận hành** + **UI**.  
→ **Tích hợp theo domain** = điều kiện **mã sạch, dễ bảo trì**.

Hai thứ **bổ sung** nhau, không thay thế.

---

## 4. Bảng gợi ý: Module liên quan Production

| Module / thành phần    | Cần bật cho tenant?               | Vai trò với Production                                                                |
| ---------------------- | --------------------------------- | ------------------------------------------------------------------------------------- |
| **Warehouse**          | **Bắt buộc** (MVP có tồn theo lô) | Trừ RM, cộng FG, batch — Production chỉ **điều phối**, không tự làm sổ kho song song. |
| **Purchase**           | **Rất nên** (nếu RM từ PO)        | Receiving, (sau này) receiving QC; link lô nhập → lô dùng trong batch.                |
| **Orders**             | Tùy quy trình                     | Nguồn nhu cầu: tạo lệnh sản xuất từ order.                                            |
| **Projects**           | **Không bắt buộc**                | Có thể chỉ là “vỏ” PM / task song song; Production có thể độc lập nếu không cần.      |
| **Delivery / Invoice** | Theo luồng bán                    | Xuất TP sau khi batch + QC (và quality lock) — thường đã có ở core.                   |

---

## 5. Trả lời thẳng câu hỏi: _“Chức năng module domain không bật, kéo vào làm gì?”_

- **Không “kéo” module đang tắt để hy vọng chạy.**
- **Thiết kế đúng:** Production **phụ thuộc** vào các domain đã **bật**; nếu tenant không bật Warehouse thì **không** nên go-live Production kiểu “có lệnh sản xuất nhưng không có kho” (trừ chế độ demo rất hạn chế).
- **“Tích hợp theo domain”** là chỉ **cách viết code** (gọi service kho, không nhân đôi logic), **không** có nghĩa bỏ qua việc module phải **active** trên Hub.

---

## 6. Gợi ý cho PM / triển khai Hub

1. **Checklist tenant:** Bật **Warehouse** (+ **Purchase** nếu dùng PO) **trước** hoặc **cùng lúc** bật Production.
2. **Tài liệu cấu hình:** Một trang “Điều kiện tiên quyết module Production”.
3. **Kỹ thuật:** Trong `ProductionServiceProvider` hoặc middleware: `abort_if(!module_enabled('warehouse'), …)` (pseudo) — tùy convention Craveva thực tế.

---

**Tham chiếu:** `BIOMIXING_PRODUCTION_DEVELOPMENT_PLAN.md` (kiến trúc module Production), `BIOMIXING_FLOW_CRACEVA_GAP.md` (flow khách hàng), **`BIOMIXING_PRODUCTION_BASELINE_AND_PREP_2026_VI.md`** (nền SO/PO/DO/kho 2026), `BIOMIXING_DOC_STALE_AUDIT_AND_REPLACEMENTS_2026_VI.md`, `FUNC_LOGIC/WAREHOUSE_INDEX.md`.
