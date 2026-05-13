# Kế hoạch thống nhất Batch cho B2B + Production

## 1) Bối cảnh và mục tiêu

Tài liệu này tổng hợp sau khi rà soát:

- `FUNC_IMPROVE/06_INVENTORY_BUSINESS_IMPROVE.md`
- `FUNC_IMPROVE/04_WH_RUNBOOK_UPGRADE_VI.md`
- `FUNC_IMPROVE/05_SO_DO_PO_GRN_REFACTOR_VI.md`
- `FUNC_IMPROVE/BIOMIXING_PLAYBOOK_P0P1_VI.md`
- Trạng thái code hiện tại ở `Modules/Warehouse/*` và `Modules/Production/*`.

Mục tiêu là trả lời 3 câu hỏi:

1. Luồng B2B và Production có xung đột chức năng không?
2. Có hợp lý khi phát triển `Warehouse Batch` trong module Warehouse và `Production Batch` trong module Production không?
3. Kế hoạch triển khai tiếp theo trong `FUNC_IMPROVE` là gì?

---

## 2) Kết luận nhanh (Executive Summary)

### 2.1 Xung đột B2B vs Production

**Không có xung đột kiến trúc cốt lõi**, nhưng có rủi ro vận hành nếu không chuẩn hóa hiển thị + đối soát:

- B2B (SO/DO/GRN/Bill) đang dùng kho theo snapshot + movement.
- Production đang dùng batch-level cho RM consumption/FG receipt.
- Cả hai cùng ghi qua `StockMovementService` là hướng đúng.

**Rủi ro chính:** lệch nhận thức giữa snapshot (`warehouse_product_stock`) và batch rows (`warehouse_product_batches`) nếu thiếu màn đối soát và trace rõ.

### 2.2 Thiết kế module

**Nên giữ 2 module/2 view riêng:**

- `Warehouse Batch` (module Warehouse): lô tồn kho thực tế, dùng chung B2B + Production.
- `Production Batch` (module Production): mẻ sản xuất, trạng thái và tiêu hao/nhập FG.

**Không nên gộp 1 list batch duy nhất** cho toàn hệ thống vì sẽ lẫn ngữ nghĩa nghiệp vụ.

### 2.3 Recheck trạng thái triển khai P0 (doi chieu code 2026-05-09)

| Hạng mục P0 trong tài liệu này                   | Trạng thái thực tế | Bằng chứng nhanh                                                                                                                                                   |
| ------------------------------------------------ | ------------------ | ------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| Batch naming chuẩn UI (Lô tồn kho / Mẻ sản xuất) | **Mot phan**       | Production `production.batches.*`; Warehouse da co menu/route `warehouse.product-batches.*` (MVP list/detail batch ton kho).                                       |
| Thêm màn Warehouse Batch List                    | **Da lam (MVP)**   | Route `warehouse-product-batches`, `WarehouseProductBatchController`, test `WarehouseProductBatchRoutesTest`.                                                      |
| Trace link 2 chiều Warehouse <-> Production      | **Mot phan**       | Warehouse→Production: detail batch + movement ref; Production→Warehouse: `production.batches.trace` co link `warehouse.product-batches.show`. UAT 2 chieu con lai. |
| Reconciliation widget (batch sum vs snapshot)    | **Mot phan**       | Widget tren `warehouse.stock.index` + method `WarehouseReconciliationService::inventorySnapshotVsBatchTotals`; command/report van dung.                            |

---

## 3) Ma trận trách nhiệm dữ liệu (Source of Truth)

### 3.1 Warehouse (inventory lot truth)

- Bảng chính: `warehouse_product_batches`, `warehouse_product_stock`, `stock_movements`.
- Chịu trách nhiệm:
    - on-hand / reserved / available / sellable
    - FEFO/FIFO theo policy
    - trace inbound/outbound theo chứng từ tham chiếu

### 3.2 Production (manufacturing process truth)

- Bảng chính: `production_orders`, `production_batches`, `production_batch_consumptions`, `production_batch_outputs`.
- Chịu trách nhiệm:
    - lệnh sản xuất, trạng thái mẻ
    - RM lines được chọn
    - FG output lines

### 3.3 Điểm nối giữa 2 miền

- Production post RM/FG phải ghi Warehouse qua `StockMovementService`.
- `reference_type/reference_id` là trục trace ngược.
- Không được cập nhật tồn kho trực tiếp ngoài Warehouse service.

---

## 4) Đánh giá xung đột chi tiết

## 4.1 Xung đột logic nghiệp vụ

- **SO/DO/GRN** và **Production posting** không xung đột trigger nếu giữ canonical:
    - B2B outbound tại DO shipped.
    - B2B inbound tại GRN received.
    - Production outbound/inbound tại action post của Production.
- Mỗi dòng movement phải có `reference_type` khác nhau để tách context.

## 4.2 Xung đột dữ liệu hiển thị

- Snapshot có thể hiển thị 59, nhưng batch rows chỉ thấy 10 nếu dữ liệu lệch.
- Đây không phải xung đột module mà là **thiếu reconciliation UX + report**.

## 4.3 Xung đột UX naming

- `Production Batch` có thể khó hiểu với người dùng vận hành.
- Đề nghị tên hiển thị:
    - Warehouse: **Lô tồn kho**
    - Production: **Mẻ sản xuất**

---

## 5) Quyết định kiến trúc đề xuất

### 5.1 Quyết định chính

1. Giữ 2 thực thể và 2 list view:
    - Warehouse Batch list (inventory-first).
    - Production Batch list (process-first).
2. Dùng chung inventory backend ở Warehouse.
3. Liên kết chéo 2 chiều bằng trace link:
    - từ Production Batch -> Stock batches consumed/created
    - từ Warehouse Batch -> source refs (GRN/DO/Production)

### 5.2 Quy định bắt buộc

- Mọi thao tác thay đổi tồn kho qua `StockMovementService`.
- Không cập nhật `warehouse_product_stock` thủ công ngoài sync nội bộ service.
- Áp company scope chặt cho mọi query batch.

---

## 6) Backlog triển khai đề xuất

## P0 — Chốt model và anti-conflict guard (ưu tiên cao)

1. **Batch naming chuẩn UI**
    - đổi label theo ngữ cảnh (Lô tồn kho / Mẻ sản xuất).
2. **Thêm màn Warehouse Batch List**
    - filter theo product/warehouse/batch_number/expiry.
3. **Trace link 2 chiều**
    - từ Warehouse batch sang movement refs và Production batch (nếu có).
4. **Reconciliation widget**
    - so sánh batch sum vs snapshot theo product+warehouse.

## P1 — Đồng bộ B2B + Production ở mức vận hành

1. **Batch Breakdown trong Adjust Stock**
    - mở rộng row để xem các batch rows tạo nên snapshot.
2. **Production Batch index menu**
    - thêm submenu `Mẻ sản xuất`.
3. **Chuẩn hóa reference_type taxonomy**
    - tách rõ `sales_do_*`, `grn_*`, `production_*`.
4. **Báo cáo Trace Center**
    - FG batch -> RM batches -> movement chain.

## P2 — Governance và kiểm soát chất lượng dữ liệu

1. **Policy theo cờ sản phẩm**
    - `is_batch_tracked`, `is_expiry_tracked` áp nhất quán inbound/outbound.
2. **Đối soát định kỳ**
    - command/report lệch snapshot vs batch.
3. **Aging/near-expiry dashboard**
    - hỗ trợ B2B + Production planning.

---

## 7) Checklist xác nhận trước khi triển khai lớn

- [ ] Chốt thuật ngữ UI với business owner.
- [ ] Chốt canonical trigger matrix (DO/GRN/Production post).
- [ ] Chốt reference_type naming convention.
- [ ] Chốt acceptance cho reconciliation (ngưỡng lệch = 0 hoặc policy rõ).
- [ ] Chốt permission matrix cho Warehouse Batch list vs Production Batch list.

---

## 8) Kết luận cuối

Phát triển **Warehouse Batch trong Warehouse module** là hợp lý.  
Phát triển **Production Batch trong Production module** cũng hợp lý.  
Giải pháp tối ưu là **2 view chuyên biệt + một inventory source-of-truth chung**, không gộp một list duy nhất.

Điểm cần làm ngay không phải đổi kiến trúc, mà là:

1. minh bạch ngữ nghĩa (naming + UI),
2. mở màn batch list ở Warehouse,
3. thêm reconciliation/trace để loại bỏ hiểu nhầm và lệch dữ liệu.
