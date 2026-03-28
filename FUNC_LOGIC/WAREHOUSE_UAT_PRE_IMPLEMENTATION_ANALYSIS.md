# Phân tích trước triển khai — UAT Report (Miaolin) × Codebase

**Ngày cập nhật:** 2026-03-28  
**Mục đích:** Làm rõ điều kiện tiên quyết, phạm vi ảnh hưởng, rủi ro và quyết định nghiệp vụ **trước khi** code theo các hạng mục UAT / gap report.

**Tham chiếu:**

- `PROJECT MAOLIN New/WAREHOUSE_UAT_CHECKLIST_MIAOLIN.md` (hoặc bản copy trong repo nếu có)
- `FUNC_LOGIC/WAREHOUSE_PM_GAP_VERIFICATION_AND_IMPROVEMENT_OPTIONS.md`
- `FUNC_LOGIC/WAREHOUSE_UAT_GO_NO_GO_SHEET.md`
- `FUNC_LOGIC/WAREHOUSE_MIAOLIN_IMPLEMENTATION_PLAN.md`
- `FUNC_LOGIC/multi_warehouse_audit_report.md`

---

## 1) Tóm tắt điều hành (Executive summary)

| Khía cạnh                               | Kết luận                                                                                                                                                                            |
| --------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **UAT report có đúng không?**           | **Đúng** ở các mức Critical/High/Medium/Low đã đối chiếu code (xem mục 3).                                                                                                          |
| **Có “đủ” để bắt tay code ngay không?** | **Đã có code Scope B v1** (2026-03) — xem `WAREHOUSE_SCOPE_B_IMPLEMENTATION_LOG.md`. **Vẫn cần** xác nhận PM cho trigger/kho nếu khác mặc định kỹ thuật + **UAT staging** trước Go. |
| **Khuyến nghị**                         | Triển khai staging + UAT theo checklist; cập nhật Go/No-Go khi có bằng chứng.                                                                                                       |

---

## 2) Bối cảnh kiến trúc: hai “nguồn tồn” song song

### 2.1 Tồn kho vật lý đa kho (chuẩn Warehouse)

- **Bảng:** `warehouse_product_batches` + đồng bộ `warehouse_product_stock`.
- **Ghi sổ:** `StockMovementService` → `stock_movements` (ledger).
- **Luồng đã gắn kho:** điều chỉnh tồn Purchase Inventory, import inventory, PO delivered (nếu bật), DO received (nếu bật), chuyển kho.

### 2.2 Legacy Purchase Inventory

- **Bảng:** `purchase_stock_adjustments` (`net_quantity` theo `product_id`, không có `warehouse_id` trên bản ghi adjustment kiểu cũ).
- **Vẫn được dùng bởi:**
    - `InvoiceController::store` — kiểm tra tồn khi tạo invoice (Purchase module + `do_it_later == direct`): `PurchaseStockAdjustment::where('product_id', $index)->sum('net_quantity')` (tổng theo sản phẩm, **không phân kho**).
    - `Modules/Purchase/Observers/PaymentObserver` — khi tạo/xóa payment, cộng/trừ `net_quantity` trên **một dòng** `where('product_id')->first()` không xác định kho.

### 2.3 Hệ quả cho “đa kho + bán hàng có inventory-aware”

- Chỉ **bổ sung** `recordOutbound` (sales) **chưa đủ** nếu:
    - Invoice vẫn check tồn theo legacy `sum(net_quantity)`;
    - Payment vẫn sửa legacy `PurchaseStockAdjustment` không warehouse.
- Cần **một chiến lược thống nhất**: hoặc dần bỏ phụ thuộc legacy cho luồng bán, hoặc đồng bộ hai lớp (rủi ro và effort cao hơn).

---

## 3) Đối chiếu từng mục UAT / Gap report với code

### 3.1 Critical — Thiếu sales outbound qua `StockMovementService`

| Nguồn        | Xác minh                                                                                                                                                                                                              |
| ------------ | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **UAT gap**  | “Order/Invoice/Payment không trừ warehouse stock qua StockMovementService.”                                                                                                                                           |
| **Code**     | `rg` không thấy `recordOutbound` trong `Modules/Sales`, `app/Observers` (sales), hay controller bán hàng ngoài Warehouse/Purchase inventory. Luồng bán chủ yếu dùng legacy ở `InvoiceController` + `PaymentObserver`. |
| **Kết luận** | **Đúng.** Đây là blocker cho “inventory-aware sales” theo kho.                                                                                                                                                        |

**Phụ thuộc cần làm rõ trước code:**

- **Sự kiện kích hoạt outbound:** tạo invoice, xác nhận giao, đóng đơn, v.v. (ảnh hưởng kế toán, hoàn hàng, hủy).
- **Chọn kho:** dòng hàng có `warehouse_id` hay không; fallback `client_details.default_warehouse_id`; kho mặc định công ty.
- **Đồng bộ với kiểm tra tồn hiện tại:** thay thế hoặc bổ sung đoạn `PurchaseStockAdjustment::sum` trong `InvoiceController`.

---

### 3.2 High — Rủi ro nhập đôi (PO delivered + DO received)

| Nguồn        | Xác minh                                                                                                                                                                                                                                                                                               |
| ------------ | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| **UAT**      | Chỉ bật **một** canonical inbound.                                                                                                                                                                                                                                                                     |
| **Code**     | `Modules/Warehouse/Config/config.php`: `inbound_from_purchase_order_delivered`, `inbound_from_delivery_order_received`. `PurchaseOrderObserver` gọi inbound khi PO delivered; `DeliveryOrderObserver` gọi `recordInboundBatch` khi DO received và có cờ `inbound_stock_applied` chống ghi lại cùng DO. |
| **Kết luận** | **Đúng** — nếu **cả hai env = true** và cùng một lô hàng được nhận qua cả hai luồng, có thể **cộng tồn hai lần** (trừ khi vận hành tách rõ nghiệp vụ).                                                                                                                                                 |

**Trước triển khai bổ sung tính năng khác:**

- Khóa cấu hình trên từng môi trường (staging/prod) + checklist deploy.
- Có thể bổ sung **guard runtime** (cảnh báo / fail-fast) như trong improvement options — không thay thế được trách nhiệm cấu hình đúng quy trình.

---

### 3.3 High — `PaymentObserver` và tồn không theo kho

| Nguồn        | Xác minh                                                                                                                                                    |
| ------------ | ----------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **UAT gap**  | Payment điều chỉnh stock không có warehouse context.                                                                                                        |
| **Code**     | `Modules/Purchase/Observers/PaymentObserver.php`: `PurchaseStockAdjustment::where('product_id', $item->product_id)->first()` rồi `net_quantity += …`.       |
| **Kết luận** | **Đúng.** Với multi-warehouse, một `product_id` có thể có **nhiều dòng** adjustment hoặc không phản ánh đúng tồn theo kho; `first()` là **không xác định**. |

**Trước / song song outbound sales:**

- Cần quyết định: **tắt** mutation ở payment, hay **feature flag** + kế hoạch migration dữ liệu.
- Phải kiểm tra **mọi** chỗ còn dùng `PurchaseStockAdjustment` cho báo cáo/validation (xem mục 4).

---

### 3.4 Medium — UI không nhập batch/expiry cho adjustment/transfer

| Nguồn        | Xác minh                                                                                                                |
| ------------ | ----------------------------------------------------------------------------------------------------------------------- |
| **UAT**      | FEFO hạn chế nếu không nhập lô/HSD trên UI.                                                                             |
| **Code**     | Form stock/transfer gửi `batch_number`/`expiry_date` null; `StockMovementService` hỗ trợ FEFO khi có expiry trên batch. |
| **Kết luận** | **Đúng** — không phải bug logic cốt lõi mà là **hạn chế vận hành / độ chính xác FEFO**.                                 |

---

### 3.5 Low — Ledger không deep-link; API stub

| Nguồn        | Xác minh                                                                                                      |
| ------------ | ------------------------------------------------------------------------------------------------------------- |
| **UAT**      | Reference text, chưa link chứng từ.                                                                           |
| **Kết luận** | **Đúng về hướng cải thiện UX**; không chặn outbound sales. Có thể xếp backlog sau sign-off nghiệp vụ cốt lõi. |

---

## 4) Blast radius — những file / luồng sẽ bị ảnh hưởng khi “bổ sung theo UAT”

| Vùng                                                                          | Vai trò hiện tại                             | Rủi ro khi đụng                                                  |
| ----------------------------------------------------------------------------- | -------------------------------------------- | ---------------------------------------------------------------- |
| `app/Http/Controllers/InvoiceController.php`                                  | Check tồn qua `PurchaseStockAdjustment::sum` | Thay đổi rule tồn ảnh hưởng mọi tạo invoice có Purchase + direct |
| `Modules/Purchase/Observers/PaymentObserver.php`                              | Trừ/cộng legacy khi payment                  | Trùng hoặc lệch với movement nếu outbound mới cũng gắn payment   |
| `Modules/Purchase/Http/Controllers/PurchaseProductController.php` + inventory | Nhiều chỗ đọc/ghi `PurchaseStockAdjustment`  | Báo cáo tồn Purchase vs Warehouse có thể lệch                    |
| `Modules/Warehouse/Services/StockMovementService.php`                         | Nguồn ghi sổ chuẩn                           | Cần payload đủ `company_id`, `warehouse_id`, reference           |
| Observers Order (nếu có mở rộng)                                              | Chưa gắn outbound                            | Điểm hook tiềm năng sau khi chốt trigger                         |

**Khuyến nghị:** Lập bảng “**sau thay đổi, nguồn sự thật cho màn hình X là gì**” (Invoice, báo cáo tồn, ledger) trước khi merge.

---

## 5) Lệch giữa UAT checklist và code (cần chỉnh tài liệu hoặc test)

| Hiện tượng                                          | Ghi chú                                                                                                                                                                                                     |
| --------------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Tên permission trong UAT** (vd. `warehouse_view`) | Trong code Warehouse thường dùng dạng `view_warehouses`, `add_warehouse_stock`, … (tham migration/runbook). Khi viết test case, **đối chiếu bảng permission thật** để tránh “pass UAT giấy” nhưng sai role. |
| **Bulk / import warehouse**                         | Checklist UAT có mục bulk + import Excel — cần xác nhận **đã có feature trong build** hay chỉ là mục tiêu roadmap; tránh test case mô tả tính năng chưa tồn tại.                                            |

---

## 6) Ma trận quyết định bắt buộc trước khi code (Definition of Ready)

| ID  | Quyết định                                                                                                       | Ai chốt                | Ghi chú                                |
| --- | ---------------------------------------------------------------------------------------------------------------- | ---------------------- | -------------------------------------- |
| D1  | **Trigger outbound** (invoice tạo / giao hàng / trạng thái đơn / khác)                                           | PM + Tech              | Ảnh hưởng hoàn hàng, hủy, partial ship |
| D2  | **Luật chọn warehouse** (per-line > invoice > client default)                                                    | PM                     | Ảnh hưởng schema/UI invoice line       |
| D3  | **Reversal** (hủy invoice, trả hàng, refund) — movement nào, thứ tự                                              | PM + Kế toán (nếu cần) | Tránh âm tồn sai                       |
| D4  | **Xử lý `PaymentObserver`** (tắt / flag / thay bằng movement)                                                    | Tech Lead              | Tránh double effect với outbound mới   |
| D5  | **Kiểm tra tồn khi tạo invoice** — chuyển sang `warehouse_product_stock` / aggregate movement hay giữ legacy tạm | Tech                   | Phải đồng bộ với D2                    |
| D6  | **Canonical inbound** (PO vs DO) trên từng môi trường                                                            | PM + Ops               | Tránh nhập đôi                         |
| D7  | **Idempotency key** cho outbound/reversal                                                                        | Dev                    | Tránh duplicate khi retry/job          |

Chỉ khi D1–D7 được ghi nhận bằng **decision record ngắn** (1 trang) thì sprint implementation mới nên full speed.

---

## 7) Rủi ro và giảm thiểu

| Rủi ro                                                        | Mức          | Giảm thiểu                                                       |
| ------------------------------------------------------------- | ------------ | ---------------------------------------------------------------- |
| Duplicate outbound (event fire 2 lần)                         | Cao          | Idempotency + unique business key + test                         |
| Lệch giữa legacy `PurchaseStockAdjustment` và warehouse stock | Cao          | Feature flag; migration từng phase; báo cáo song song có ghi chú |
| Sai kho (fallback client default)                             | Trung bình   | Unit test resolver kho; log payload khi fail                     |
| Nhập đôi PO+DO                                                | Cao (config) | Env + guard + runbook deploy                                     |
| Phạm vi regression Invoice/Purchase lớn                       | Cao          | Regression pack cho invoice create + payment + cancel            |

---

## 8) Thứ tự làm việc đề xuất (không phải code — là trình tự phân tích/triển khai)

1. **Đóng băng** danh mục quyết định (mục 6) + sơ đồ sequence outbound/reversal.
2. **Inventory code touchpoints:** liệt kê tất cả đọc/ghi `PurchaseStockAdjustment` liên quan bán hàng (đã có phần lớn trong `multi_warehouse_audit_report.md`).
3. **Thiết kế tích hợp:** service outbound + hook observer + tắt/thay PaymentObserver theo flag.
4. **Test:** unit (resolver kho, idempotency) + integration (invoice + stock) + UAT checklist cập nhật permission đúng tên.
5. **Triển khai theo** `WAREHOUSE_MIAOLIN_IMPLEMENTATION_PLAN.md` (Sprint 0 → 3).

---

## 9) Kết luận

- **UAT report phản ánh đúng thực trạng code** về thiếu outbound sales, rủi ro config inbound kép, và legacy payment/stock không theo kho.
- **Trước khi bổ sung theo UAT**, cần **phân tích blast radius** và **chốt quyết định nghiệp vụ** — không nên chỉ “thêm một observer outbound” mà không xử lý `InvoiceController` + `PaymentObserver` + nguồn tồn dùng cho validation.

**Tài liệu này** nên đi kèm họp: PM (rule), Tech (impact), QA (test matrix), Ops (env).

---

_Tài liệu này bổ sung cho kế hoạch triển khai; không thay thế decision record chi tiết từng công ty._
