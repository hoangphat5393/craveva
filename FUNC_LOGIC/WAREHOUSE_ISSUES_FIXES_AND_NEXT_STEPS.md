# Multi-warehouse — lỗi đã phát hiện, đã sửa, và bước tiếp theo

Tài liệu ghi lại các vấn đề liên quan MAOLIN / multi-warehouse trong quá trình triển khai và review (staging). Cập nhật khi có thay đổi lớn.

---

## 1. Lỗi / nguy cơ đã phát hiện

### 1.1 UI — Edit sản phẩm (purchase-products)

| Hiện tượng                                                                                                            | Nguyên nhân gốc                                                                                                                                                                                                                                                                                              |
| --------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| `500` — **Array to string conversion** tại `resources/views/components/forms/label.blade.php`                         | `data-label="{{ $fieldRequired }}"` nhận giá trị không phải scalar (ví dụ mảng) trong edge case.                                                                                                                                                                                                             |
| `500` — **`htmlspecialchars(): ... array given`** tại `resources/views/components/forms/text.blade.php` (placeholder) | Gọi `__('Certification')` (và các key tương tự) trong khi tồn tại file ngôn ngữ `lang/en/Certification.php` trả về **cả nhóm** → Laravel trả về **mảng**, không phải chuỗi. Cùng kiểu rủi ro với các file nhóm: `Wholesale_Price`, `Price_Per_Box`, `Employee_Price`, `Storage_Condition`, `Inventory_Type`. |

### 1.2 Logic kho (đã xem xét, không phải bug mới)

| Chủ đề                                         | Ghi chú                                                                                                                                                                                                                            |
| ---------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Inbound trùng PO + DO**                      | Có thể double-count nếu bật đồng thời `WAREHOUSE_INBOUND_FROM_PO_DELIVERED` và `WAREHOUSE_INBOUND_FROM_DO_RECEIVED`. Đã có cờ trong `Modules/Warehouse/Config/config.php` và comment trong observers — cần cấu hình vận hành đúng. |
| **Transaction lồng nhau** (trước khi refactor) | `recordTransfer` gọi `recordOutbound` + `recordInbound`, mỗi hàm bọc `DB::transaction` → savepoint lồng nhau; hành vi đúng nhưng khó bảo trì.                                                                                      |

---

## 2. Đã sửa (tham chiếu code)

### 2.1 Component form

- **`app/View/Components/Forms/Label.php`** — Chuẩn hóa `fieldRequired`, `fieldLabel`, `popover` trước khi render (boolean → `'true'`/`'false'`, mảng → an toàn).
- **`app/View/Components/Forms/Text.php`** — Chuẩn hóa placeholder, value, id, name, help, v.v. sang chuỗi hiển thị an toàn.
- **`app/View/Components/Forms/Number.php`** — Tương tự cho các thuộc tính dùng trong view.

### 2.2 Blade — khóa dịch Laravel (`group.key`)

Thay `__('Certification')` bằng `__('Certification.Certification')` (và tương tự cho các nhóm ở `lang/en/*.php`), tại các view:

- `Modules/Purchase/Resources/views/purchase-products/ajax/edit.blade.php`
- `Modules/Purchase/Resources/views/purchase-products/ajax/create.blade.php`
- `Modules/Purchase/Resources/views/purchase-products/ajax/overview.blade.php`
- `resources/views/products/ajax/create.blade.php`
- `resources/views/products/ajax/edit.blade.php`

### 2.3 Warehouse — refactor an toàn

- **`Modules/Warehouse/Concerns/ScopesWarehouseProductBatchQuery.php`** — Trait DRY cho điều kiện “định danh một dòng batch” (lock inbound / reservation).
- **`Modules/Warehouse/Services/StockMovementService.php`**
    - `executeOutboundMovement()` — logic outbound dùng chung.
    - `recordTransfer()` — **một** `DB::transaction`, gọi `executeOutboundMovement` + `applyInboundOnce` (tránh transaction lồng từ `recordOutbound`/`recordInbound`).
- **`Modules/Warehouse/Services/StockReservationService.php`** — Dùng cùng trait cho `lockBatch`.

### 2.4 Multi-warehouse hardening pass (2026-03-24)

- **`Modules/Purchase/Resources/views/purchase-inventory/ajax/create.blade.php`**
    - Bật lại dropdown `warehouse_id` (trước đó bị comment).
    - Khi đổi kho sẽ reset item rows để tránh lấy tồn sai kho.
- **`Modules/Purchase/Http/Controllers/PurchaseInventoryController.php`**
    - Validate bắt buộc kho khi module Warehouse bật.
    - Với adjustment kiểu `quantity`, đồng bộ chênh lệch tồn tuyệt đối sang `StockMovementService` (inbound/outbound), giữ tương thích bảng cũ.
- **`Modules/Purchase/Jobs/ImportInventoryJob.php`**
    - Khôi phục resolver kho từ `warehouse_code`/`warehouse_name`.
    - Đồng bộ movement theo delta tồn tuyệt đối trong import quantity.
- **`Modules/Purchase/DataTables/PurchaseInventoryDataTable.php`** + `PurchaseInventory` model + `overview.blade.php`
    - Hiển thị lại cột/tóm tắt `warehouse_name` để vận hành dễ đối soát.
- **Client default warehouse (core field)**
    - `resources/views/clients/ajax/create.blade.php` + `edit.blade.php`: thêm chọn `default_warehouse_id`.
    - `ClientController` load danh sách kho active theo company.
    - `Store/UpdateClientRequest` validate `default_warehouse_id`.
    - `ClientImport` + `ClientImportProcessor`: thêm mapping import `designated_warehouse_code/name` -> `client_details.default_warehouse_id`.

---

## 3. Việc nên làm tiếp theo (ưu tiên gợi ý)

1. **Kiểm thử tích hợp (DB)**  
   Viết test (SQLite/MySQL) cho: inbound đơn, `recordInboundBatch`, outbound FEFO, `recordTransfer`, và (khi cần) `StockReservationService::reserve` / `release`.

2. **DRY form (tùy chọn)**  
   Gom logic `toDisplayString` / normalize từ `Text` + `Number` (và có thể `Label`) vào helper chung (ví dụ `App\Support\FormString`) để giảm trùng lặp — chỉ khi team đồng ý phạm vi.

3. **Rà soát ngôn ngữ**  
   Quét codebase các pattern `__('SomeKey')` trùng tên file trong `lang/en/SomeKey.php` để tránh trả về mảng tương tự.

4. **Tài liệu vận hành**  
   Giữ `WAREHOUSE_UI_OPERATIONS_GUIDE.md` và `WAREHOUSE_ANALYSIS_AND_PLAN.md` đồng bộ khi đổi cờ `.env` hoặc luồng PO/DO.

5. **Staging / production**  
   Sau deploy: smoke test — Edit sản phẩm, điều chỉnh tồn kho, chuyển kho, và một kịch bản PO delivered hoặc DO received (theo cấu hình thật).

---

## 4. Liên kết nhanh

| Tài liệu / vùng code                                   | Mục đích                    |
| ------------------------------------------------------ | --------------------------- |
| `FUNC_LOGIC/WAREHOUSE_ANALYSIS_AND_PLAN.md`            | Kế hoạch tổng thể           |
| `FUNC_LOGIC/WAREHOUSE_UI_OPERATIONS_GUIDE.md`          | Hướng dẫn UI / URL / `.env` |
| `Modules/Warehouse/Config/config.php`                  | Cờ inbound PO/DO, âm tồn    |
| `Modules/Purchase/Observers/DeliveryOrderObserver.php` | Inbound khi DO `received`   |
| `Modules/Purchase/Observers/PurchaseOrderObserver.php` | Inbound khi PO `delivered`  |

---

_File này được tạo theo yêu cầu ghi chú lỗi đã phát hiện, đã sửa, và backlog bước tiếp._
